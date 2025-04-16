<?php

namespace App\Controller;

use App\Entity\Interview;
use App\Entity\Application;
use App\Form\InterviewType;
use App\Repository\InterviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\File as MimeFile; // Correct import
use Symfony\Component\Filesystem\Filesystem;

#[Route('/interview')]
final class InterviewController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route(name: 'app_interview_index', methods: ['GET'])]
    public function index(InterviewRepository $interviewRepository): Response
    {
        return $this->render('interview/index.html.twig', [
            'interviews' => $interviewRepository->findAll(),
        ]);
    }

    #[Route('/new/{applicationId}', name: 'app_interview_new', methods: ['GET', 'POST'], defaults: ['applicationId' => null])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        ?int $applicationId = null
    ): Response {
        $interview = new Interview();

        $this->logger->info('Processing request for /interview/new/{applicationId}', [
            'method' => $request->getMethod(),
            'applicationId' => $applicationId,
            'isAjax' => $request->isXmlHttpRequest(),
        ]);

        if ($applicationId) {
            $application = $entityManager->getRepository(Application::class)->find($applicationId);
            if (!$application) {
                $this->logger->warning('Application not found for ID: ' . $applicationId);
                return $this->json(['error' => 'Application not found'], Response::HTTP_NOT_FOUND);
            }
            $interview->setApplication($application);
        }

        $user = $this->getUser();
        if (!$user) {
            $this->logger->error('No logged-in user found');
            return $this->json(['error' => 'You must be logged in'], Response::HTTP_FORBIDDEN);
        }
        $interview->setUser($user);

        $form = $this->createForm(InterviewType::class, $interview, [
            'action' => $this->generateUrl('app_interview_new', ['applicationId' => $applicationId], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($interview);
                $entityManager->flush();

                // Generate QR code and save it in the public/images directory
                $qrCodeTempFile = null;
                try {
                    // Ensure the public/images directory exists
                    $filesystem = new Filesystem();
                    $publicDir = $this->getParameter('kernel.project_dir') . '/public/images';
                    if (!$filesystem->exists($publicDir)) {
                        $filesystem->mkdir($publicDir);
                    }

                    // Create a unique filename for the QR code
                    $qrCodeFileName = 'qr_' . uniqid() . '.svg';
                    $qrCodePath = $publicDir . '/' . $qrCodeFileName;

                    // Create a message with interview date, location, and a small message
                    $interviewDate = $interview->getInterviewDate()->format('Y-m-d H:i');
                    $interviewLocation = $interview->getLocation();
                    $message = "Date: {$interviewDate}\nLocation: {$interviewLocation}\nMessage: Interview Scheduled";

                    // Generate QR code as SVG
                    $result = Builder::create()
                        ->writer(new SvgWriter()) // SvgWriter instance
                        ->data($message)
                        ->encoding(new Encoding('UTF-8'))
                        ->build();

                    file_put_contents($qrCodePath, $result->getString());

                    $qrCodeTempFile = $qrCodePath;
                } catch (\Exception $e) {
                    $this->logger->error('QR code generation failed: ' . $e->getMessage());
                    $this->addFlash('warning', 'Interview created, but QR code could not be generated.');
                }

                $meetingRoom = 'Interview-' . uniqid();
                $meetingLink = 'https://meet.jit.si/' . $meetingRoom;

                // Send email with QR code attachment and meeting link
                $application = $interview->getApplication();
                if ($application && $application->getMail()) {
                    $emailBody = $this->renderView('emails/interview_scheduled.html.twig', [
                        'interview' => $interview,
                        'application' => $application,
                        'meetingLink' => $meetingLink,
                    ]);

                    $email = (new TemplatedEmail())
                        ->from('your-email@example.com')
                        ->to($application->getMail())
                        ->subject('Interview Scheduled')
                        ->html($emailBody);

                    if ($qrCodeTempFile && file_exists($qrCodeTempFile)) {
                        $attachment = new MimeFile($qrCodeTempFile);
                        $email->attach($attachment);
                    }

                    // Send the email
                    $mailer->send($email);
                    $this->addFlash('success', 'Interview created and email sent successfully.');
                } else {
                    $this->addFlash('warning', 'Interview saved, but email could not be sent due to missing application or email.');
                }

                if ($request->isXmlHttpRequest()) {
                    return $this->json(['success' => true]);
                }

                return $this->redirectToRoute('app_interview_index');
            } elseif ($request->isXmlHttpRequest()) {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                return $this->json(['success' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('interview/_form.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $this->render('interview/new.html.twig', [
            'interview' => $interview,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_interview_show', methods: ['GET'])]
    public function show(Interview $interview): Response
    {
        return $this->render('interview/show.html.twig', [
            'interview' => $interview,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_interview_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Interview $interview, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InterviewType::class, $interview);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->flush();

                if ($request->isXmlHttpRequest()) {
                    return $this->json(['success' => true]);
                }

                return $this->redirectToRoute('app_interview_index');
            } elseif ($request->isXmlHttpRequest()) {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                return $this->json(['success' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
            }
        }

        return $this->render('interview/edit.html.twig', [
            'interview' => $interview,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_interview_delete', methods: ['POST'])]
    public function delete(Request $request, Interview $interview, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $interview->getId(), $request->request->get('_token'))) {
            $entityManager->remove($interview);
            $entityManager->flush();
            $this->addFlash('success', 'Interview deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Unable to delete the interview.');
        }

        return $this->redirectToRoute('app_interview_index');
    }
}
