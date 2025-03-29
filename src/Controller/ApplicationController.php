<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\JobOffer;
use App\Form\ApplicationType;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        // Retrieve the JobOffer entity by its ID
        $jobOffer = $entityManager->getRepository(JobOffer::class)->find($id);
        if (!$jobOffer) {
            throw $this->createNotFoundException('Job offer not found');
        }

        // Create a new Application and link it to the JobOffer
        $application = new Application();
        $application->setJobOffer($jobOffer);

        // Pre-set default values (status and submission date)
        $application->setStatus('open');
        $application->setSubmissionDate(new \DateTime());

        // Create the form. Ensure ApplicationType does NOT include fields for user, status, or submissionDate.
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Re-set defaults in case form handling altered them
            $application->setSubmissionDate(new \DateTime());
            $application->setStatus('open');

            // Instead of setting the currently logged in user, assign the user who created the job offer.
            // This assumes the JobOffer entity has a "user" property for its creator.
            $application->setUser($jobOffer->getUser());

            // Process CV file upload if provided
            $cvFile = $form->get('CV')->getData();
            if ($cvFile) {
                $cvFileName = uniqid() . '.' . $cvFile->guessExtension();
                $cvFile->move($this->getParameter('uploads_directory'), $cvFileName);
                $application->setCv('/uploads/applications/' . $cvFileName);
            }

            // Process Cover Letter file upload if provided
            $coverLetterFile = $form->get('Cover_Letter')->getData();
            if ($coverLetterFile) {
                $coverLetterFileName = uniqid() . '.' . $coverLetterFile->guessExtension();
                $coverLetterFile->move($this->getParameter('uploads_directory'), $coverLetterFileName);
                $application->setCoverLetter('/uploads/applications/' . $coverLetterFileName);
            }

            // Persist and flush the application to the database
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
            // If needed, update submissionDate or status here.
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
}
