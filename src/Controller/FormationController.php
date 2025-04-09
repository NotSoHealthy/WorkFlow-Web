<?php

namespace App\Controller;
use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
#[Route('/formation')]
final class FormationController extends AbstractController
{
    #[Route(name: 'app_formation_list')]
    public function ListFormation(Request $request,FormationRepository $formationRepository,PaginatorInterface $paginator,EntityManagerInterface $em): Response
    {
        //search
        $search = trim($request->query->get('search', ''));

        $formations = $formationRepository->searchFormations($search);

        //current user
        $user = $this->getUser();

        $pagination = $paginator->paginate(
            $formations,
            $request->query->getInt('page', 1),
            3
        );
        return $this->render('formation/list.html.twig', [
            'pagination' => $pagination,
            'user_inscriptions' => $user?->getInscriptions(),
            'search' => $search,
        ]);
    }

    #[Route('/new',name: 'app_formation_add')]
    public function addFormation(Request $request,EntityManagerInterface $em): Response
    {
        $formation = new Formation();
        $formation->setUser($this->getUser());
        $form = $this->createForm(FormationType::class, $formation,['attr' => ['novalidate' => 'novalidate']]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($formation);
            $em->flush();
    
            return $this->redirectToRoute('app_formation_list');
        }
    
        return $this->render('formation/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{id}/edit', name: 'app_formation_edit')]
    public function edit(Request $request, Formation $formation, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(FormationType::class, $formation,['attr' => ['novalidate' => 'novalidate']]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_formation_list');
        }

        return $this->render('formation/edit.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,
        ]);
    }
    #[Route('/{id}/delete', name: 'app_formation_delete')]
    public function delete(Formation $formation, EntityManagerInterface $em): Response
    {
        $em->remove($formation);
        $em->flush();
        return $this->redirectToRoute('app_formation_list');
    }
}
