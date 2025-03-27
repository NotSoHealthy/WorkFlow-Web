<?php

namespace App\Controller;
use App\Entity\Event;
use App\Form\EvenementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/event')]
final class EvenementController extends AbstractController
{
    #[Route('/addevent', name: 'app_addevent')]
    public function addevent(Request $request,EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $event->setUser($this->getUser());
        $form = $this->createForm(EvenementType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $entityManager->persist($event);
            $entityManager->flush();
        }
        return $this->render('evenement/addevent.html.twig', [
            'form' => $form,
        ]);
    }
}
