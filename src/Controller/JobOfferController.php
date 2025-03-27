<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Form\JobOfferType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JobOfferController extends AbstractController
{
    #[Route('/joboffer', name: 'job_offer_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // 1) Get all Job Offers
        $jobOffers = $em->getRepository(JobOffer::class)->findAll();

        // 2) Prepare an empty JobOffer for the form
        $jobOffer = new JobOffer();

        // 3) Check if there's an "edit_id" in the request (from the hidden field) to know if we are editing
        $editId = $request->request->get('edit_id');
        if ($editId) {
            $existingOffer = $em->getRepository(JobOffer::class)->find($editId);
            if ($existingOffer) {
                $jobOffer = $existingOffer;
            }
        }

        // 4) Create the form using JobOfferType
        $form = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        // 5) Handle form submission (create/update)
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the publication date if creating a new offer
            if (!$jobOffer->getPublicationDate()) {
                $jobOffer->setPublicationDate(new \DateTime());
            }

            // Optionally assign the current user to the job offer
            $jobOffer->setUser($this->getUser());

            $em->persist($jobOffer);
            $em->flush();

            $this->addFlash('success', 'Job offer saved successfully!');
            return $this->redirectToRoute('job_offer_index');
        }

        return $this->render('job_offer/index.html.twig', [
            'jobOffers' => $jobOffers,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/joboffer/{id}/delete', name: 'job_offer_delete', methods: ['POST'])]
    public function delete(JobOffer $jobOffer, EntityManagerInterface $em): Response
    {
        $em->remove($jobOffer);
        $em->flush();

        $this->addFlash('success', 'Job offer deleted!');
        return $this->redirectToRoute('job_offer_index');
    }
}
