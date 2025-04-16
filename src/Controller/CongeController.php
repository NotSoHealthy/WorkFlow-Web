<?php

namespace App\Controller;

use App\Entity\Conge;
use App\Form\CongeType;
use App\Repository\CongeRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\DynamoDbHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/conge')]
final class CongeController extends AbstractController
{
    #[Route(name: 'app_conge_index', methods: ['GET','POST'])]
    public function index(CongeRepository $congeRepository, Request $request, EntityManagerInterface $entityManager): Response
    {

        $conge = new Conge();
        $conge->setUser($this->getUser());
        $conge->setStatus('pending');
        $conge->setRequestDate(new DateTime('today'));
        $form = $this->createForm(CongeType::class, $conge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $startDate = $form->get('start_date')->getData();
            $endDate = $form->get('end_date')->getData();
            $requestDate = $form->get('request_date')->getData();

            if ($startDate > $endDate) {
                $this->addFlash('conge_form_error', 'La date de début doit être avant la date de fin.');
                return $this->redirectToRoute('app_conge_index');
            }
            if ($requestDate > $startDate) {
                $this->addFlash('conge_form_error', 'La date de demande ne peut pas être avant à la date de début.');
                return $this->redirectToRoute('app_conge_index');
            }

            $entityManager->persist($conge);
            $entityManager->flush();

            return $this->redirectToRoute('app_conge_index');
        }

        if (!$this->isGranted('ROLE_RESPONSABLE')) {
            $congeList = $congeRepository->findBy(['user' => $this->getUser()]);
        }
        else{
            $congeList = $congeRepository->findAll();
        }

        return $this->render('conge/index.html.twig', [
            'conges' => $congeList,
            'form' => $form,
        ]);
    }

    #[Route('/update-status/{id}', name: 'update_conge_status', methods: ['POST'])]
    public function updateStatus(Conge $conge, Request $request, EntityManagerInterface $entityManager): Response
    {
        $status = $request->request->get('status');

        $conge->setStatus($status);
        $entityManager->flush();

        return $this->redirectToRoute('app_conge_index');
    }
}
