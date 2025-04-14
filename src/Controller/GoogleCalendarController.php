<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Service\GoogleCalendarService;
use App\Service\MeetMailer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GoogleCalendarController extends AbstractController
{
    public function __construct(private MeetMailer $meetMailer)
    {
    }
    #[Route('/google/calendar/authorize/{id}', name: 'app_google_calendar_authorize')]
    public function authorize(
        Reservation $reservation,
        GoogleCalendarService $googleCalendarService
    ): Response {
        // Get the authorization URL
        $authUrl = $googleCalendarService->getAuthUrl($reservation->getId());
        
        // Redirect to Google's authorization page
        return $this->redirect($authUrl);
    }

    #[Route('/google/calendar/callback', name: 'app_google_calendar_callback')]
    public function callback(
        Request $request,
        GoogleCalendarService $googleCalendarService,
        ReservationRepository $reservationRepository,
        SessionInterface $session
    ): Response {
        $code = $request->query->get('code');
        
        if (!$code) {
            $this->addFlash('error', 'Authorization failed. No authorization code received.');
            return $this->redirectToRoute('app_showreservation');
        }
        
        // Exchange authorization code for access token
        $success = $googleCalendarService->handleCallback($code);
        
        if (!$success) {
            $this->addFlash('error', 'Failed to authorize Google Calendar access.');
            return $this->redirectToRoute('app_showreservation');
        }
        
        // Get the reservation ID from session
        $reservationId = $session->get('reservation_id');
        if (!$reservationId) {
            $this->addFlash('error', 'Reservation information lost during authorization.');
            return $this->redirectToRoute('app_showreservation');
        }
        
        // Get the reservation
        $reservation = $reservationRepository->find($reservationId);
        if (!$reservation) {
            $this->addFlash('error', 'Reservation not found.');
            return $this->redirectToRoute('app_showreservation');
        }
        
        // Add event to Google Calendar
        $result = $googleCalendarService->addEventToCalendar($reservation);
        
        if ($result['success']) {
            $this->addFlash('success', 'Event has been added to your Google Calendar!');
            
            // If this is an online event and we have a Meet link
            if ($reservation->getEvent()->isOnline() && isset($result['meetLink'])) {
                $user= $this->getUser();
                $this->meetMailer->sendMeet($user,(string) $result['meetLink'],$reservation->getEvent()->getTitle(),
                    (new TemplatedEmail())
                        ->from(new Address('noreply@demomailtrap.co', 'WorkFlow'))
                        ->to((string) $user->getEmail())
                        ->subject($reservation->getEvent()->getTitle())
                        ->htmlTemplate('reservation/meet_link.html.twig')
                );
            }
        } else {
            $this->addFlash('error', 'Failed to add event to your Google Calendar: ' . 
                (isset($result['error']) ? $result['error'] : 'Unknown error'));
        }
        
        return $this->redirectToRoute('app_showreservation');
    }
}