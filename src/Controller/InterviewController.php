<?php

namespace App\Controller;

use App\Entity\Interview;
use App\Form\InterviewType;
use App\Repository\InterviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Application;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/interview')]
final class InterviewController extends AbstractController
{
    #[Route(name: 'app_interview_index', methods: ['GET'])]
    public function index(InterviewRepository $interviewRepository): Response
    {
        return $this->render('interview/index.html.twig', [
            'interviews' => $interviewRepository->findAll(),
        ]);
    }

    #[Route('/new/{applicationId}', name: 'app_interview_new', methods: ['GET', 'POST'], defaults: ['applicationId' => null])]
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, ?int $applicationId = null): Response
    {
        $interview = new Interview();

        // Set the application if an applicationId is provided
        if ($applicationId) {
            $application = $entityManager->getRepository(Application::class)->find($applicationId);
            if (!$application) {
                throw $this->createNotFoundException('Application not found');
            }
            $interview->setApplication($application);
        }

        // Set the user to the currently logged-in user
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to schedule an interview.');
        }
        $interview->setUser($user);

        // Create the form with an explicit action
        $form = $this->createForm(InterviewType::class, $interview, [
            'action' => $this->generateUrl('app_interview_new', ['applicationId' => $applicationId], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($interview);
                $entityManager->flush();

                // Send email to the candidate if application exists
                $application = $interview->getApplication();
                if ($application && $application->getMail()) {
                    $email = (new Email())
                        ->from('aminekerfai222@gmail.com')
                        ->to($application->getMail())
                        ->subject('Interview Scheduled')
                        ->html('<p>Dear ' . $application->getFirstName() . ',<br>Your interview is scheduled on ' . $interview->getInterviewDate()->format('Y-m-d H:i') . ' at ' . $interview->getLocation() . '.</p><p>Best regards,<br>Your Team</p>');
                    $mailer->send($email);
                } else {
                    $this->addFlash('warning', 'Interview saved, but email could not be sent due to missing application or email.');
                }

                if ($request->isXmlHttpRequest()) {
                    return $this->json(['success' => true]);
                }
                return $this->redirectToRoute('app_interview_index', [], Response::HTTP_SEE_OTHER);
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
                return $this->redirectToRoute('app_interview_index', [], Response::HTTP_SEE_OTHER);
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
        if ($this->isCsrfTokenValid('delete' . $interview->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($interview);
            $entityManager->flush();
            $this->addFlash('success', 'Interview deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Unable to delete the interview.');
        }

        return $this->redirectToRoute('app_interview_index', [], Response::HTTP_SEE_OTHER);
    }
}
