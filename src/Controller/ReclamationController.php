<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reclamation')]
final class ReclamationController extends AbstractController
{
    #[Route(name: 'app_reclamation_index', methods: ['GET'])]
    public function index(Request $request, ReclamationRepository $reclamationRepository): Response
  {
        // Get query parameters
        $searchTerm = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'newest');
        $status = $request->query->get('status', '');

        // Validate sort parameter
        $sort = in_array($sort, ['newest', 'oldest']) ? $sort : 'newest';

        // Fetch filtered and sorted reclamations
        $reclamations = $reclamationRepository->findFilteredAndSorted($searchTerm, $sort, $status);

        // Calculate types and categories for stats
        $types = [];
        $categories = [];
        foreach ($reclamations as $rec) {
            $type = $rec->getType();
            $category = $rec->getCategory();
            $types[$type] = ($types[$type] ?? 0) + 1;
            $categories[$category] = ($categories[$category] ?? 0) + 1;
        }

        // Check if the request is AJAX
        if ($request->query->get('ajax') === '1') {
            // Render only the reclamation grid partial
            $reclamationsHtml = $this->renderView('reclamation/_grid.html.twig', [
                'reclamations' => $reclamations,
            ]);

            return $this->json([
                'reclamationsHtml' => $reclamationsHtml,
                'types' => $types,
                'categories' => $categories,
            ]);
        }

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'types' => $types,
            'categories' => $categories,
            'searchTerm' => $searchTerm,
            'sort' => $sort,
            'status' => $status,
        ]);
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $attachedFile = $form->get('attachedfile')->getData();
            if ($attachedFile) {
                $newFilename = uniqid() . '.' . $attachedFile->guessExtension();
                $attachedFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
            $reclamation->setAttachedfile($newFilename);}

            $timezone = new \DateTimeZone('+01:00');
            $now = new \DateTime('now', $timezone);
            $reclamation->setUser($this->getUser());
            $reclamation->setDate($now);
            $reclamation->setHeure($now);
     
            $reclamation->setResponsable(null);
            $reclamation->setEtat('Ouvert');

            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

   

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
      
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $attachedFile = $form->get('attachedfile')->getData();
        if ($attachedFile) {
            if ($reclamation->getAttachedfile()) {
                $oldFilePath = $this->getParameter('uploads_directory') . '/' . $reclamation->getAttachedfile();
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }
            $newFilename = uniqid() . '.' . $attachedFile->guessExtension();
            $attachedFile->move(
                $this->getParameter('uploads_directory'),
                $newFilename
            );
            $reclamation->setAttachedfile($newFilename);
        }
            $entityManager->flush();
            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/update-status', name: 'app_reclamation_update_status', methods: ['POST'])]
public function updateStatus(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
{
    $data = json_decode($request->getContent(), true);
    $status = $data['status'] ?? null;
    
    // Map the status to your internal values if needed
    $statusMapping = [
        'ouvert' => 'Ouvert',
        'enc_cours' => 'En cours',
        'en_attente' => 'En attente',
        'ferme' => 'Ferme',
        'rejete' => 'Rejete'
    ];
    
    if ($status && isset($statusMapping[$status])) {
        $reclamation->setEtat($statusMapping[$status]);
        $entityManager->flush();
        return $this->json(['success' => true]);
    }
    return $this->json(['success' => false], 400);
}

#[Route('/{id}/resign', name: 'app_reclamation_resign', methods: ['POST'])]
public function resign(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
{
    $reclamation->setResponsable(null);
    $entityManager->flush();
    return $this->json(['success' => true]);
}

#[Route('/{id}/take-ownership', name: 'app_reclamation_take_ownership', methods: ['POST'])]
public function takeOwnership(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    if ($reclamation->getResponsable()) {
        return $this->json([
            'success' => false,
            
        ]);
    }
    
    $reclamation->setResponsable($user);
    $entityManager->flush();
    
    return $this->json([
        'success' => true,
    
    ]);
}
}
