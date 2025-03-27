<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Form\JobOfferType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JobOfferController extends AbstractController
{
    #[Route('/joboffer', name: 'job_offer_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $jobOffers = $em->getRepository(JobOffer::class)->findAll();
        return $this->render('job_offer/index.html.twig', [
            'jobOffers' => $jobOffers,
        ]);
    }

    #[Route('/job-offer/wizard/{id?}', name: 'job_offer_wizard', methods: ['GET'])]
    public function wizard(EntityManagerInterface $em, Request $request, JobOffer $jobOffer = null): Response
    {
        if (!$jobOffer) {
            $jobOffer = new JobOffer();
        }
        $form = $this->createForm(JobOfferType::class, $jobOffer);
        return $this->render('job_offer/wizard.html.twig', [
            'form'     => $form->createView(),
            'jobOffer' => $jobOffer,
        ]);
    }

    #[Route('/job-offer/create', name: 'job_offer_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $jobOffer = new JobOffer();
        $form = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$jobOffer->getPublicationDate()) {
                $jobOffer->setPublicationDate(new \DateTime());
            }
            $jobOffer->setUser($this->getUser());
            $em->persist($jobOffer);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'job'     => [
                    'id'              => $jobOffer->getId(),
                    'title'           => $jobOffer->getTitle(),
                    'description'     => $jobOffer->getDescription(),
                    'publicationDate' => $jobOffer->getPublicationDate()->format('Y-m-d'),
                    'expirationDate'  => $jobOffer->getExpirationDate() ? $jobOffer->getExpirationDate()->format('Y-m-d') : '',
                    'contractType'    => $jobOffer->getContractType(),
                    'salary'          => $jobOffer->getSalary(),
                ]
            ]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        return new JsonResponse(['success' => false, 'message' => implode("\n", $errors)], 400);
    }

    #[Route('/job-offer/update/{id}', name: 'job_offer_update', methods: ['POST'])]
    public function update(Request $request, JobOffer $jobOffer, EntityManagerInterface $em): JsonResponse
    {
        $form = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$jobOffer->getPublicationDate()) {
                $jobOffer->setPublicationDate(new \DateTime());
            }
            $jobOffer->setUser($this->getUser());
            $em->persist($jobOffer);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'job'     => [
                    'id'              => $jobOffer->getId(),
                    'title'           => $jobOffer->getTitle(),
                    'description'     => $jobOffer->getDescription(),
                    'publicationDate' => $jobOffer->getPublicationDate()->format('Y-m-d'),
                    'expirationDate'  => $jobOffer->getExpirationDate() ? $jobOffer->getExpirationDate()->format('Y-m-d') : '',
                    'contractType'    => $jobOffer->getContractType(),
                    'salary'          => $jobOffer->getSalary(),
                ]
            ]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        return new JsonResponse(['success' => false, 'message' => implode("\n", $errors)], 400);
    }

    #[Route('/job-offer/delete/{id}', name: 'job_offer_delete', methods: ['DELETE'])]
    public function delete(JobOffer $jobOffer, EntityManagerInterface $em): JsonResponse
    {
        if (!$jobOffer) {
            return new JsonResponse(['success' => false, 'message' => 'Offer not found'], 404);
        }
        $em->remove($jobOffer);
        $em->flush();
        return new JsonResponse(['success' => true]);
    }
}
