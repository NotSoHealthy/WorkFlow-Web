<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\JobOffer;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/apply')]
class ShowJobsController extends AbstractController
{
    #[Route('/{id}', name: 'app_show_jobs_apply', methods: ['GET', 'POST'])]
    public function apply(int $id, Request $request, EntityManagerInterface $em): Response
    {
        // Find the job offer by its ID
        $jobOffer = $em->getRepository(JobOffer::class)->find($id);
        if (!$jobOffer) {
            throw $this->createNotFoundException('Job offer not found');
        }

        // Create a new Application and link it to the JobOffer
        $application = new Application();
        $application->setJobOffer($jobOffer);

        // Create the form for application submission.
        // (Ensure that ApplicationType does not include fields for user, status, or submissionDate.)
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the submission date and status
            $application->setSubmissionDate(new \DateTime());
            $application->setStatus('open');

            // Set the user to the creator of the job offer
            $application->setUser($jobOffer->getUser());

            // Handle CV file upload if provided
            $cvFile = $form->get('CV')->getData();
            if ($cvFile) {
                $cvFileName = uniqid() . '.' . $cvFile->guessExtension();
                $cvFile->move($this->getParameter('uploads_directory'), $cvFileName);
                $application->setCv('/uploads/applications/' . $cvFileName);
            }

            // Handle Cover Letter file upload if provided
            $coverLetterFile = $form->get('Cover_Letter')->getData();
            if ($coverLetterFile) {
                $coverLetterFileName = uniqid() . '.' . $coverLetterFile->guessExtension();
                $coverLetterFile->move($this->getParameter('uploads_directory'), $coverLetterFileName);
                $application->setCoverLetter('/uploads/applications/' . $coverLetterFileName);
            }

            // Persist and flush the new application
            $em->persist($application);
            $em->flush();

            return $this->redirectToRoute('job_offers_public');
        }

        return $this->render('application/indexApplication.html.twig', [
            'form' => $form->createView(),
            'jobOfferId' => $id,
            'joboffer' => $jobOffer
        ]);
    }
}
