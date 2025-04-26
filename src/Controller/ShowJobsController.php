<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\JobOffer;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/apply')]
class ShowJobsController extends AbstractController
{
    private $logger;
    private $entityManager;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    #[Route('/{id}', name: 'app_show_jobs_apply', methods: ['GET', 'POST'])]
    public function apply(int $id, Request $request): Response
    {
        $jobOffer = $this->entityManager->getRepository(JobOffer::class)->find($id);
        if (!$jobOffer) {
            throw $this->createNotFoundException('Job offer not found');
        }

        // Get IP address with simple, reliable method
        $userIp = $request->server->get('REMOTE_ADDR') ?: 'unknown';
        if ($userIp === 'unknown') {
            $this->logger->warning('Could not determine client IP address', [
                'remote_addr' => $request->server->get('REMOTE_ADDR'),
                'x_forwarded_for' => $request->headers->get('X-Forwarded-For'),
            ]);
        }
        $this->logger->debug('IP address set', ['ip' => $userIp]);

        // Check for duplicate application before rendering form
        $existingApplication = $this->entityManager->getRepository(Application::class)->findOneBy([
            'jobOffer' => $jobOffer,
            'ipAddress' => $userIp,
        ]);

        if ($existingApplication) {
            $this->addFlash('warning', 'Vous avez déjà postulé pour cette offre.');
            return $this->redirectToRoute('job_offers_public');
        }

        // Create new application
        $application = new Application();
        $application->setJobOffer($jobOffer);
        $application->setStatus('open');
        $application->setSubmissionDate(new \DateTime());
        $application->setIpAddress($userIp);
        $application->setAlreadyApplied(false);
        $application->setUser($jobOffer->getUser()); // Link to job offer creator

        // Create form
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get email from form
            $email = $application->getMail();

            // Double-check for email-based duplicates
            $emailDuplicate = $this->entityManager->getRepository(Application::class)->findOneBy([
                'jobOffer' => $jobOffer,
                'mail' => $email,
            ]);

            if ($emailDuplicate) {
                $this->addFlash('warning', 'Vous avez déjà postulé pour cette offre.');
                return $this->redirectToRoute('job_offers_public');
            }

            // Handle CV file upload
            $cvFile = $form->get('CV')->getData();
            if ($cvFile) {
                $cvFileName = uniqid() . '.' . $cvFile->guessExtension();
                $cvFile->move($this->getParameter('uploads_directory'), $cvFileName);
                $application->setCv('/uploads/applications/' . $cvFileName);
            }

            // Handle cover letter file upload
            $coverLetterFile = $form->get('Cover_Letter')->getData();
            if ($coverLetterFile) {
                $coverLetterFileName = uniqid() . '.' . $cvFile->guessExtension();
                $coverLetterFile->move($this->getParameter('uploads_directory'), $coverLetterFileName);
                $application->setCoverLetter('/uploads/applications/' . $coverLetterFileName);
            }

            // Final IP validation
            if (!$application->getIpAddress()) {
                $this->logger->error('IP address is null before persisting', ['previousIp' => $userIp]);
                $application->setIpAddress('unknown');
            }

            // Log state before persisting
            $this->logger->debug('Application state', [
                'ipAddress' => $application->getIpAddress(),
                'email' => $email,
                'user' => $jobOffer->getUser() ? $jobOffer->getUser()->getId() : null,
            ]);

            // Persist and flush
            $this->entityManager->persist($application);
            $application->setAlreadyApplied(true);
            $this->entityManager->flush();

            $this->logger->info('Application saved', [
                'id' => $application->getId(),
                'ipAddress' => $application->getIpAddress(),
            ]);

            $this->addFlash('success', 'Candidature soumise avec succès.');
            return $this->redirectToRoute('job_offers_public');
        }

        return $this->render('application/indexApplication.html.twig', [
            'form' => $form->createView(),
            'jobOfferId' => $id,
            'joboffer' => $jobOffer,
        ]);
    }
}
