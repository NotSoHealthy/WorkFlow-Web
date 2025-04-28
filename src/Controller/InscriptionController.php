<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Formation;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\SmsSender;

#[Route('/inscription')]
class InscriptionController extends AbstractController
{
    #[Route(name: 'app_inscription_list')]
    public function list(Request $request,EntityManagerInterface $em,InscriptionRepository $inscriptionRepository,PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $stats = $inscriptionRepository->getInscriptionsCountPerFormation();
        $inscription = $em->getRepository(Inscription::class)->findAll();
        $sort = $request->query->get('filter_sort', 'all');

        if($sort=="all")
        {
            $inscription = $em->getRepository(Inscription::class)->findAll();
        }
        else
        {
            $inscription = $inscriptionRepository->sortInscriptions($sort);
        }
        
        $pagination = $paginator->paginate(
            $inscription,
            $request->query->getInt('page', 1),
            3
        );
        if ($request->isXmlHttpRequest()) {
            return $this->render('inscription/_list.html.twig', [
                'pagination' => $pagination,
                'user' => $user
            ]);
        }
        return $this->render('inscription/list.html.twig', [
            'pagination' => $pagination,
            'user' => $user,
            'stats' => $stats,
            'filter_sort' => $sort
        ]);
    }

    #[Route('/{id}/new', name: 'app_inscription_new')]
    public function new(Formation $formation, EntityManagerInterface $em): Response
    {
        $user = $this->getUser(); 
        $existing = $em->getRepository(Inscription::class)->findOneBy([
            'formation' => $formation,
            'user' => $user,
        ]);

        if ($existing) {
            $this->addFlash('warning', 'You are already registered for this formation.');
            return $this->redirectToRoute('app_formation_list');
        }
        $inscription = new Inscription();
        $inscription->setDateRegistration(new \DateTime());
        $inscription->setStatus('en attente');
        $inscription->setFormation($formation);
        $inscription->setUser($user);

        $em->persist($inscription);
        $em->flush();
        return $this->redirectToRoute('app_formation_list');
    }
    #[Route('/{id}/edit', name: 'app_inscription_edit', methods: ['POST'])] 
    public function updateStatus(Request $request,SmsSender $smsSender, Inscription $inscription, EntityManagerInterface $em): Response
    {
        $status = $request->request->get('status');
        $formation= $inscription->getFormation();
        $titre=$formation->getTitle();
        $employee = $inscription->getUser();
        $fullName = $employee->getFirst_name() . ' ' . $employee->getLast_name();

        
        if ($status === 'refuser' && $inscription->getStatus() === 'approuver') {
            $formation->setParticipantsMax($formation->getParticipantsMax() + 1);
        }
        if ($status === 'refuser') {

            $message = "Bonjour $fullName, nous vous informons que votre inscription à la formation, $titre, a été refusée.";
            $smsSender->sendSms("+21698264250", $message);
            $em->remove($inscription);
            $em->flush();

        } else {
            $inscription->setStatus($status);
            if ($status === 'approuver') {
                $message = "Bonjour $fullName, nous vous informons que votre inscription à la formation, $titre, a été acceptée.";
                $smsSender->sendSms("+21698264250", $message);
                if ($formation->getParticipantsMax() > 0) {

                    $formation->setParticipantsMax($formation->getParticipantsMax() - 1);
                }
            }
            $em->flush();
        }
        return $this->redirectToRoute('app_inscription_list');
    } 
}
