<?php

namespace App\Controller;

use App\Entity\JobOffer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainPageController extends AbstractController
{
    #[Route('/main', name: 'main_page_index')]
    public function index(): Response
    {
        return $this->render('mainPage.html.twig');
    }

    #[Route('/job-offers', name: 'job_offers_public')]
    public function jobOffers(EntityManagerInterface $em): Response
    {
        $jobOffers = $em->getRepository(JobOffer::class)->findAll();
        return $this->render('job-offers.html.twig', [
            'jobOffers' => $jobOffers,
        ]);
    }
}
