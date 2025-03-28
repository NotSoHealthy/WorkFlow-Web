<?php

namespace App\Controller;

use App\Repository\AttendanceRepository;
use App\Repository\CongeRepository;
use App\Repository\EventRepository;
use App\Repository\InscriptionRepository;
use App\Repository\ReclamationRepository;
use App\Repository\ReservationRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(TaskRepository $taskRepository, AttendanceRepository $attendanceRepository, CongeRepository $congeRepository, ReclamationRepository $reclamationRepository, ReservationRepository $reservationRepository, InscriptionRepository $inscriptionRepository, EventRepository $eventRepository): Response
    {
        $tasks = $taskRepository->findBy(['user' => $this->getUser()]);
        $attendances = $attendanceRepository->findBy(['user' => $this->getUser()],['date' => 'DESC'],7);
        $attendances = array_reverse($attendances);
        $conges = array_filter(
            $congeRepository->findBy(['user' => $this->getUser(), 'status' => 'approved']),
            function ($conge) {
                return $conge->getStartDate()->format('Y') == date('Y');
            }
        );
        $reclamations = $reclamationRepository->findBy(['user' => $this->getUser(), 'etat' => 'pending']);
        $reservations = array_filter(
            $reservationRepository->findBy(['user' => $this->getUser()]),
            function ($reservation) {
                return $reservation->getEvent()->getDateAndTime() >= new \DateTime();
            }
        );
        $inscriptions = array_filter(
            $inscriptionRepository->findBy(['user' => $this->getUser()]),
            function ($inscription) {
                return $inscription->getFormation()->getDate_end() >= new \DateTime();
            }
        );
        $events = array_filter(
            $eventRepository->findAll(),
            function ($event) {
                return $event->getDateAndTime() >= new \DateTime();
            }
        );
        
        return $this->render('dashboard/index.html.twig', [
            'tasks' => $tasks,
            'attendances' => $attendances,
            'conges' => $conges,
            'reclamations' => $reclamations,
            'reservations' => $reservations,
            'inscriptions' => $inscriptions,
            'events' => $events,
        ]);
    }
}
