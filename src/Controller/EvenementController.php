<?php

namespace App\Controller;
use App\Entity\Event;
use App\Form\EvenementType;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/event')]
final class EvenementController extends AbstractController
{
    private LoggerInterface $eventLogger;
    
    
    public function __construct(LoggerInterface $eventLogger)
    {
        $this->eventLogger = $eventLogger;
    }

    #[Route(name: 'app_showevent')]
    public function events(EventRepository $eventRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $request->query->get('q');
        $sortByDate = $request->query->get('sort_by_date');
        $eventType = $request->query->get('event_type', '');
        
        // Use search query if provided, otherwise get all events
        if ($query || $sortByDate || $eventType) {
            $queryBuilder = $eventRepository->findBySearchQuery($query, $sortByDate, $eventType);
        } else {
            $queryBuilder = $eventRepository->findAll();
        }
        
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            3 // Number of items per page
        );
        
        $eventStats = [
            'conference' => $eventRepository->countByType('conference'),
            'workshop' => $eventRepository->countByType('workshop'),
            'webinar' => $eventRepository->countByType('webinar'),
            'meetup' => $eventRepository->countByType('meetup')
        ];

        $mostPopular= $eventRepository->findMostPopularEvent();
        $closestEvent = $eventRepository->findClosestUpcomingEvent();
        $mostCommonType = array_keys($eventStats, max($eventStats))[0];
        $eventRecommendations = [
            'most_popular' => $mostPopular ? $mostPopular['Title'] : 'Aucun événement',
            'most_popular_count' => $mostPopular ? $mostPopular['reservationCount'] : 0,
            'most_popular_id' => $mostPopular ? $mostPopular['id'] : 0,
            'closest_event' => $closestEvent ? $closestEvent->getTitle() : 'Aucun événement à venir',
            'closest_event_date' => $closestEvent ? $closestEvent->getDateAndTime()->format('d/m/Y') : 'N/A',
            'most_common_type' => ucfirst($mostCommonType),
            'most_common_type_count' => $eventStats[$mostCommonType]
        ];
        // Check if this is an AJAX request
        if ($request->query->has('ajax') || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            // Return only the events partial for AJAX requests
            $this->eventLogger->info('Evenement Affiché', [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'user_id' => $this->getUser()->getId(),
            ]);
            return $this->render('evenement/_events_list.html.twig', [
                'events' => $pagination,
                'query' => $query,
            ]);
        }
        
        return $this->render('evenement/index.html.twig', [
            'pagination' => $pagination,
            'query' => $query,
            'event_stats' => $eventStats,
            'event_recommendations' => $eventRecommendations
        ]);
    }
    
    #[Route('/addevent', name: 'app_addevent')]
    public function addevent(Request $request, EntityManagerInterface $entityManager): Response
    {
        $errors = null;
        $query = null;
        $event = new Event();
        $event->setUser($this->getUser());
        if ($event->getDateAndTime() === null) {
            $event->setDateAndTime(new \DateTime()); // Default to the current date and time
        }
        $form = $this->createForm(EvenementType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();
            $this->eventLogger->info('Evenement Ajouté', [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'user_id' => $this->getUser()->getId(),
            ]);
            return $this->redirectToRoute('app_showevent');
        }
        return $this->render('evenement/addevent.html.twig', [
            'form' => $form->createView(),
            'query' => $query
        ]);
    }
    
    #[Route('/{id}', name: 'app_delete_event', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager, EventRepository $eventRepository, PaginatorInterface $paginator): Response
    {
        $this->eventLogger->info('Evenement supprimé', [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'user_id' => $this->getUser()->getId(),
        ]);
        $entityManager->remove($event);
        $entityManager->flush();
        
        // Get paginated events after deletion
        $pagination = $paginator->paginate(
            $eventRepository->findAll(),
            $request->query->getInt('page', 1),
            3 // Number of items per page
        );
        
        return $this->render('evenement/_events_list.html.twig', [
            'events' => $pagination,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'app_edit_event', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->eventLogger->info('Evenement Mis a jour', [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'user_id' => $this->getUser()->getId(),
            ]);
            return $this->redirectToRoute('app_showevent', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/editevent.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
    
    #[Route('/searchevent', name: 'app_event_list')]
    public function search(Request $request, EventRepository $eventRepository, PaginatorInterface $paginator): Response
    {
        $query = $request->query->get('q');
        $sortByDate = $request->query->get('sort_by_date');
        $eventType = $request->query->get('event_type', '');
    
        $events = $eventRepository->findBySearchQuery($query, $sortByDate, $eventType);
        $pagination = $paginator->paginate(
            $events,
            $request->query->getInt('page', 1),
            3 // Number of items per page
        );
        
        // Check if this is an AJAX request
        if ($request->query->has('ajax') || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            // Return only the events partial for AJAX requests
            $this->eventLogger->info('Evenement recherché', [
                'query' => $query,
                'sort_by_date' => $sortByDate,
                'event_type' => $eventType,
                'user_id' => $this->getUser()->getId(),
            ]);
            return $this->render('evenement/_events_list.html.twig', [
                'events' => $pagination,
            ]);
        }
    
        // Return the full page for standard requests
        return $this->render('evenement/index.html.twig', [
            'pagination' => $pagination,
            'query' => $query,
        ]);
    }
}