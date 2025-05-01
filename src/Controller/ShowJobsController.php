<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\JobOffer;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/apply')]
class ShowJobsController extends AbstractController
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger        = $logger;
        $this->entityManager = $entityManager;
    }

    #[Route('/{id}', name: 'app_show_jobs_apply', methods: ['GET', 'POST'])]
    public function apply(int $id, Request $request): Response
    {
        // 1) Fetch the JobOffer
        $jobOffer = $this->entityManager
            ->getRepository(JobOffer::class)
            ->find($id);

        if (!$jobOffer) {
            throw $this->createNotFoundException('Job offer not found');
        }

        // 2) Early duplicate check by IP
        $userIp = $request->server->get('REMOTE_ADDR') ?: 'unknown';
        if ('unknown' === $userIp) {
            $this->logger->warning('Could not determine client IP', [
                'REMOTE_ADDR'     => $request->server->get('REMOTE_ADDR'),
                'X-Forwarded-For' => $request->headers->get('X-Forwarded-For'),
            ]);
        }

        $duplicateByIp = $this->entityManager
            ->getRepository(Application::class)
            ->findOneBy([
                'jobOffer'  => $jobOffer,
                'ipAddress' => $userIp,
            ]);

        if ($duplicateByIp) {
            $this->addFlash('warning', 'Vous avez déjà postulé pour cette offre.');
            return $this->redirectToRoute('job_offers_public');
        }

        // 3) Build the new Application entity
        $application = (new Application())
            ->setJobOffer($jobOffer)
            ->setStatus('open')
            ->setSubmissionDate(new \DateTime())
            ->setIpAddress($userIp)
            ->setAlreadyApplied(false)
            ->setUser($jobOffer->getUser());

        // 4) Create & handle the form
        $form = $this->createForm(ApplicationType::class, $application, [
            // ensure file fields trigger multipart/form-data
            'attr'      => ['enctype' => 'multipart/form-data'],
        ]);
        $form->handleRequest($request);

        // 5) If submitted & valid, process
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $application->getMail();

            // 5a) Email‐based duplicate check
            $duplicateByEmail = $this->entityManager
                ->getRepository(Application::class)
                ->findOneBy([
                    'jobOffer' => $jobOffer,
                    'mail'     => $email,
                ]);

            if ($duplicateByEmail) {
                $this->addFlash('warning', 'Vous avez déjà postulé pour cette offre.');
                return $this->redirectToRoute('job_offers_public');
            }

            // 5b) File uploads with try/catch
            /** @var UploadedFile|null $cvFile */
            $cvFile = $form->get('CV')->getData();
            if ($cvFile) {
                try {
                    $cvFilename = uniqid() . '_' . $id . '.' . $cvFile->guessExtension();
                    $cvFile->move(
                        $this->getParameter('uploads_directory'),
                        $cvFilename
                    );
                    $application->setCv('/uploads/applications/' . $cvFilename);
                } catch (FileException $e) {
                    $this->logger->error('CV upload failed', [
                        'error' => $e->getMessage(),
                    ]);
                    $this->addFlash('error', 'Le téléchargement du CV a échoué.');
                    return $this->redirectToRoute('app_show_jobs_apply', ['id' => $id]);
                }
            }

            /** @var UploadedFile|null $coverFile */
            $coverFile = $form->get('Cover_Letter')->getData();
            if ($coverFile) {
                try {
                    $coverFilename = uniqid() . '_' . $id . '.' . $coverFile->guessExtension();
                    $coverFile->move(
                        $this->getParameter('uploads_directory'),
                        $coverFilename
                    );
                    $application->setCoverLetter('/uploads/applications/' . $coverFilename);
                } catch (FileException $e) {
                    $this->logger->error('Cover letter upload failed', [
                        'error' => $e->getMessage(),
                    ]);
                    $this->addFlash('error', 'Le téléchargement de la lettre de motivation a échoué.');
                    return $this->redirectToRoute('app_show_jobs_apply', ['id' => $id]);
                }
            }

            // 5c) Final persist & flush
            $this->entityManager->persist($application);
            $application->setAlreadyApplied(true);
            $this->entityManager->flush();

            $this->logger->info('New application persisted', [
                'application_id' => $application->getId(),
                'job_offer_id'   => $jobOffer->getId(),
                'ip'             => $userIp,
            ]);

            $this->addFlash('success', 'Candidature soumise avec succès.');
            return $this->redirectToRoute('job_offers_public');
        }

        // 6) Render the form (GET or invalid POST)
        return $this->render('application/indexApplication.html.twig', [
            'form'       => $form->createView(),
            'jobOfferId' => $jobOffer->getId(),
            'joboffer'   => $jobOffer,
        ]);
    }
}
