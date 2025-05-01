<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\JobOffer;
use App\Form\ApplicationType;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/application')]
final class ApplicationController extends AbstractController
{
    private $logger;
    private $entityManager;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    #[Route(name: 'app_application_index', methods: ['GET'])]
    public function index(ApplicationRepository $applicationRepository): Response
    {
        return $this->render('application/index.html.twig', [
            'applications' => $applicationRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_application_new', methods: ['GET', 'POST'])]
    public function new(int $id, Request $request): Response
    {
        $jobOffer = $this->entityManager->getRepository(JobOffer::class)->find($id);
        if (!$jobOffer) {
            throw $this->createNotFoundException('Job offer not found');
        }

        // Get IP address with simple, reliable method
        $userIp = $this->getClientIp($request);
        $userIp = $userIp ?: 'unknown';
        $this->logger->debug('IP address set', ['ip' => $userIp]);

        // Create new application
        $application = new Application();
        $application->setJobOffer($jobOffer);
        $application->setStatus('open');
        $application->setSubmissionDate(new \DateTime());
        $application->setIpAddress($userIp);
        $application->setAlreadyApplied(false);
        $application->setUser(null); // No logged-in user

        // Create form, explicitly excluding ipAddress
        $form = $this->createForm(ApplicationType::class, $application, [
            'mapped' => [
                'ipAddress' => false,
                'alreadyApplied' => false,
                'submissionDate' => false,
                'status' => false,
                'jobOffer' => false,
                'user' => false,
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get email from form
            $email = $application->getMail();

            // Check for duplicate application
            $existingApplication = $this->entityManager->getRepository(Application::class)->findOneBy([
                'jobOffer' => $jobOffer,
                'mail' => $email,
            ]) ?? $this->entityManager->getRepository(Application::class)->findOneBy([
                'jobOffer' => $jobOffer,
                'ipAddress' => $userIp,
            ]);

            if ($existingApplication) {
                $this->addFlash('warning', 'Vous avez déjà postulé pour cette offre.');
                return $this->redirectToRoute('app_application_index');
            }

            // Handle file uploads
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

            // Ensure ipAddress is not null
            if (!$application->getIpAddress()) {
                $this->logger->error('IP address is null before persisting', ['previousIp' => $userIp]);
                $application->setIpAddress('unknown');
            }

            // Log state before persisting
            $this->logger->debug('Application state', [
                'ipAddress' => $application->getIpAddress(),
                'email' => $email,
            ]);

            // Persist and flush
            $this->entityManager->persist($application);
            $application->setAlreadyApplied(true); // Set after validation
            $this->entityManager->flush();

            $this->logger->info('Application saved', ['id' => $application->getId(), 'ipAddress' => $application->getIpAddress()]);

            $this->addFlash('success', 'Candidature soumise avec succès.');
            return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('application/indexApplication.html.twig', [
            'application' => $application,
            'form' => $form->createView(),
            'jobOfferId' => $id,
        ]);
    }

    #[Route('/list/{id}', name: 'app_application_list', methods: ['GET'])]
    public function listByJobOffer(int $id): Response
    {
        $jobOffer = $this->entityManager->getRepository(JobOffer::class)->find($id);
        if (!$jobOffer) {
            throw $this->createNotFoundException("Offre d'emploi non trouvée");
        }

        $applications = $this->entityManager->getRepository(Application::class)->findBy([
            'jobOffer' => $jobOffer,
        ]);

        return $this->render('application/list_by_job_offer.html.twig', [
            'jobOffer' => $jobOffer,
            'applications' => $applications,
        ]);
    }

    #[Route('/update-status/{id}', name: 'app_application_update_status', methods: ['POST'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $application = $this->entityManager->getRepository(Application::class)->find($id);
        if (!$application) {
            return new JsonResponse(['success' => false, 'message' => 'Candidature non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $newStatus = $data['status'] ?? null;
        if (!in_array($newStatus, ['open', 'in progress', 'closed'])) {
            return new JsonResponse(['success' => false, 'message' => 'Statut invalide'], 400);
        }

        $application->setStatus($newStatus);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Statut mis à jour']);
    }

    #[Route('/{id}', name: 'app_application_show', methods: ['GET'])]
    public function show(Application $application): Response
    {
        return $this->render('application/show.html.twig', [
            'application' => $application,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_application_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Application $application): Response
    {
        $form = $this->createForm(ApplicationType::class, $application, [
            'mapped' => [
                'ipAddress' => false,
                'alreadyApplied' => false,
                'submissionDate' => false,
                'status' => false,
                'jobOffer' => false,
                'user' => false,
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('application/edit.html.twig', [
            'application' => $application,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_application_delete', methods: ['POST'])]
    public function delete(Request $request, Application $application): Response
    {
        if ($this->isCsrfTokenValid('delete' . $application->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($application);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/stats/{id}', name: 'app_application_stats', methods: ['GET'])]
    public function stats(int $id): JsonResponse
    {
        $jobOffer = $this->entityManager->getRepository(JobOffer::class)->find($id);
        if (!$jobOffer) {
            throw $this->createNotFoundException('Job offer not found');
        }

        $applications = $this->entityManager->getRepository(Application::class)->findBy([
            'jobOffer' => $jobOffer,
        ]);

        $stats = [
            'total' => count($applications),
            'open' => 0,
            'inProgress' => 0,
            'closed' => 0,
        ];

        foreach ($applications as $app) {
            switch ($app->getStatus()) {
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

    /**
     * Get the client IP address with simple handling.
     *
     * @param Request $request
     * @return string|null
     */
    private function getClientIp(Request $request): ?string
    {
        // Prioritize REMOTE_ADDR for simplicity
        $ipAddress = $request->server->get('REMOTE_ADDR');
        if ($ipAddress && filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return $ipAddress;
        }

        // Fallback to X-Forwarded-For
        $ipAddress = $request->headers->get('X-Forwarded-For');
        if ($ipAddress) {
            $ipArray = explode(',', $ipAddress);
            $ipAddress = trim($ipArray[0]);
            if (filter_var($ipAddress, FILTER_VALIDATE_IP)) {
                return $ipAddress;
            }
        }

        return null;
    }
}
