<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\JobOffer;
use App\Form\ApplicationType;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/application')]
final class ApplicationController extends AbstractController
{
    #[Route(name: 'app_application_index', methods: ['GET'])]
    public function index(ApplicationRepository $applicationRepository): Response
    {
        return $this->render('application/index.html.twig', [
            'applications' => $applicationRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_application_new', methods: ['GET', 'POST'])]
    public function new(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $jobOffer = $entityManager->getRepository(JobOffer::class)->find($id);
        if (!$jobOffer) {
            throw $this->createNotFoundException('Job offer not found');
        }

        $application = new Application();
        $application->setJobOffer($jobOffer);

        $application->setStatus('open');
        $application->setSubmissionDate(new \DateTime());

        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $application->setSubmissionDate(new \DateTime());
            $application->setStatus('open');

            $application->setUser($jobOffer->getUser());

            $cvFile = $form->get('CV')->getData();
            if ($cvFile) {
                $cvFileName = uniqid() . '.' . $cvFile->guessExtension();
                $cvFile->move($this->getParameter('uploads_directory'), $cvFileName);
                $application->setCv('/uploads/applications/' . $cvFileName);
            }

            $coverLetterFile = $form->get('Cover_Letter')->getData();
            if ($coverLetterFile) {
                $coverLetterFileName = uniqid() . '.' . $coverLetterFile->guessExtension();
                $coverLetterFile->move($this->getParameter('uploads_directory'), $coverLetterFileName);
                $application->setCoverLetter('/uploads/applications/' . $coverLetterFileName);
            }

            $entityManager->persist($application);
            $entityManager->flush();

            return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('application/indexApplication.html.twig', [
            'application' => $application,
            'form' => $form->createView(),
            'jobOfferId' => $id,
        ]);
    }

    #[Route('/list/{id}', name: 'app_application_list', methods: ['GET'])]
    public function listByJobOffer(int $id, EntityManagerInterface $entityManager): Response
    {
        $jobOffer = $entityManager->getRepository(JobOffer::class)->find($id);
        if (!$jobOffer) {
            throw $this->createNotFoundException("Offre d'emploi non trouvée");
        }
        $applications = $entityManager->getRepository(Application::class)->findBy(['jobOffer' => $jobOffer]);

        return $this->render('application/list_by_job_offer.html.twig', [
            'jobOffer' => $jobOffer,
            'applications' => $applications,
        ]);
    }

    #[Route('/update-status/{id}', name: 'app_application_update_status', methods: ['POST'])]
    public function updateStatus(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $application = $entityManager->getRepository(Application::class)->find($id);
        if (!$application) {
            return new JsonResponse(['success' => false, 'message' => 'Candidature non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $newStatus = $data['status'] ?? null;

        if (!in_array($newStatus, ['open', 'in progress', 'closed'])) {
            return new JsonResponse(['success' => false, 'message' => 'Statut invalide'], 400);
        }

        $application->setStatus($newStatus);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Statut mis à jour avec succès']);
    }

    #[Route('/{id}', name: 'app_application_show', methods: ['GET'])]
    public function show(Application $application): Response
    {
        return $this->render('application/show.html.twig', [
            'application' => $application,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_application_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Application $application, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('application/edit.html.twig', [
            'application' => $application,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_application_delete', methods: ['POST'])]
    public function delete(Request $request, Application $application, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $application->getId(), $request->request->get('_token'))) {
            $entityManager->remove($application);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/stats/{id}', name: 'app_application_stats', methods: ['GET'])]
    public function stats(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $jobOffer = $entityManager->getRepository(JobOffer::class)->find($id);
        if (!$jobOffer) {
            throw $this->createNotFoundException('Job offer not found');
        }

        $applications = $entityManager->getRepository(Application::class)->findBy(['jobOffer' => $jobOffer]);

        $stats = [
            'total' => count($applications),
            'open' => 0,
            'inProgress' => 0,
            'closed' => 0,
        ];

        foreach ($applications as $application) {
            switch ($application->getStatus()) {
                case 'open':
                    $stats['open']++;
                    break;
                case 'in progress':
                    $stats['inProgress']++;
                    break;
                case 'closed':
                    $stats['closed']++;
                    break;
            }
        }

        return new JsonResponse($stats);
    }
}
