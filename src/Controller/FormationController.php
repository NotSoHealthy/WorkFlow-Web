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
        $yearlyStats = $formationRepository->getFormationsCountPerYear();
        $search = trim($request->query->get('search', ''));
        
        $filterSort = $request->query->get('filter_sort', '');
        
        $formations = $formationRepository->searchAndSortFormations($search, $filterSort);
        
        /** @var User $user */
        $user = $this->getUser();
        $formationsToday = [];
        foreach ($formationRepository->findBy(['date_begin' => new \DateTimeImmutable('today')]) as $formation) {
            foreach ($formation->getInscriptions() as $inscription) {
                if ($inscription->getUser() === $user && $inscription->getStatus() === 'approuver') {
                    $formationsToday[] = $formation;
                }
            }
        }
        $pagination = $paginator->paginate(
            $formations,
            $request->query->getInt('page', 1),
            3
        );
        if ($request->isXmlHttpRequest()) {
            return $this->render('formation/_list.html.twig', [
                'pagination' => $pagination,
                'user_inscriptions' => $user?->getInscriptions()
            ]);
        }
        return $this->render('formation/list.html.twig', [
            'pagination' => $pagination,
            'user_inscriptions' => $user?->getInscriptions(),
            'search' => $search,
            'filter_sort' => $filterSort,
            'yearlyStats' => $yearlyStats,
            'formations_today' => $formationsToday
            
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
