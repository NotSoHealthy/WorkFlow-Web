<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Service\MeetMailer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use App\Repository\EventRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/event')]
final class ReservationController extends AbstractController
{
    #[Route('/reserver',name: 'app_showreservation')]
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findByUser($this->getUser()),
        ]);
    }
    #[Route('/reserver/{id}', name: 'app_add_reservation')]
    public function new(Request $request, Event $event, EntityManagerInterface $entityManager,EventRepository $er): Response
    {
        $reservation = new Reservation();
        $reservation->setEvent($event);
        $reservation->setUser($this->getUser());
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //dump($request->request->all());exit;
            $typePrice = (float) $request->request->all('reservation')['Type'];
            $qr_url = $request->request->all('reservation')['qr_url'];
            $places = $reservation->getNombreDePlaces();
            if($typePrice == 40){
                $type = 'Accès-Normal';
            } elseif($typePrice == 50){
                $type = 'Semi-VIP';
            } else {
                $type = 'VIP';
            }
            $reservation->setType($type);
            $reservation->setQrUrl($qr_url);	
            $totalPrice = $typePrice * $places;
            $reservation->setPrice($totalPrice);
            $entityManager->persist($reservation);
            $entityManager->flush();
            $er->decreaseNumberOfPlaces($event->getId(), $reservation->getNombreDePlaces());
            return $this->redirectToRoute('app_google_calendar_authorize', [
                'id' => $reservation->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event, 
        ]);
    }
    #[Route('/delete/{id}', name: 'app_delete_reservation', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager,EventRepository $er): Response
    {
        $entityManager->remove($reservation);
        $entityManager->flush();
        $er->increaseNumberOfPlaces($reservation->getEvent()->getId(), $reservation->getNombreDePlaces());
        return $this->redirectToRoute('app_showreservation', [], Response::HTTP_SEE_OTHER);
    }
}
