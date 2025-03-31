<?php

namespace App\Controller;
use App\Entity\Event;
use App\Form\EvenementType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/event')]
final class EvenementController extends AbstractController
{
    #[Route(name: 'app_showevent')]
    public function events(eventRepository $eventRepository): Response
    {
        return $this->render('evenement/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
    #[Route('/addevent', name: 'app_addevent')]
    public function addevent(Request $request,EntityManagerInterface $entityManager): Response
    {
        $errors = null;
        $query=null;
        $event = new Event();
        $event->setUser($this->getUser());
        if ($event->getDateAndTime() === null) {
            $event->setDateAndTime(new \DateTime()); // Default to the current date and time
        }
        $form = $this->createForm(EvenementType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted()&& $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();
            return $this->redirectToRoute('app_showevent');
        }
        return $this->render('evenement/addevent.html.twig', [
            'form' => $form->createView(),
            'query' => $query
        ]);
    }
    #[Route('/{id}', name: 'app_delete_event', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($event);
        $entityManager->flush();
        return $this->redirectToRoute('app_showevent', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/edit', name: 'app_edit_event', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_showevent', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/editevent.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
    #[Route('/searchevent', name: 'app_search_events')]
    public function search(Request $request, EventRepository $eventRepository)
    {
        $query = $request->query->get('q', '');

        $events = $eventRepository->findByTitle($query);

        return $this->render('evenement/index.html.twig', [
            'events' => $events,
            'query' => $query
        ]);
    }
}
