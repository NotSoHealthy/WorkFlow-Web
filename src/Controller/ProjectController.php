<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/project')]
final class ProjectController extends AbstractController
{
    #[Route(name: 'project_index')]
public function index(ProjectRepository $projectRepository): Response
{
    $projects = $projectRepository->findAll();

    // 💥 If any of these return null, it will break!
    $total = count($projects);
    $enCours = 0;
    $termine = 0;
    $annule = 0;
    $budgetTotal = 0;

    foreach ($projects as $project) {
        $state = strtolower($project->getState() ?? '');
        
        if ($state === 'en cours') $enCours++;
        elseif ($state === 'terminé') $termine++;
        elseif ($state === 'annulé') $annule++;

        $budgetTotal += $project->getBudget() ?? 0;
    }

    return $this->render('project/index.html.twig', [
        'projects' => $projects,
        'stats' => [
            'total' => $total,
            'en_cours' => $enCours,
            'termine' => $termine,
            'annule' => $annule,
            'budget_total' => $budgetTotal,
        ],
    ]);
}
    #[Route('/new', name: 'project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'project_delete', methods: ['POST'])]
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();
        }

        return $this->redirectToRoute('project_index', [], Response::HTTP_SEE_OTHER);
    }

    private function checkEndDateNotifications(array $projects): void
    {
        $today = new \DateTime();
        foreach ($projects as $project) {
            $endDate = $project->getEndDate();
            if ($endDate) {
                $interval = $today->diff($endDate);
                $daysUntilEnd = $interval->days * ($interval->invert ? -1 : 1);
                if ($daysUntilEnd <= 7 && $daysUntilEnd >= 0) {
                    $this->addFlash('warning', sprintf(
                        "Project '%s' ends on %s (in %d days). Please review!",
                        $project->getName(),
                        $endDate->format('Y-m-d'),
                        $daysUntilEnd
                    ));
                } elseif ($daysUntilEnd < 0) {
                    $this->addFlash('danger', sprintf(
                        "Project '%s' ended on %s and is overdue by %d days!",
                        $project->getName(),
                        $endDate->format('Y-m-d'),
                        abs($daysUntilEnd)
                    ));
                }
            }
        }
    }
    #[Route('/search', name: 'project_search', methods: ['GET'])]
    public function search(Request $request, ProjectRepository $projectRepository): Response
    {
        $q = $request->query->get('q', '');
        $sort = $request->query->get('sort', '');
    
        $qb = $projectRepository->createQueryBuilder('p');
    
        if ($q) {
            $qb->andWhere('p.Name LIKE :search')
               ->setParameter('search', '%' . $q . '%');
        }
    
        switch ($sort) {
            case 'name_asc':
                $qb->orderBy('p.Name', 'ASC');
                break;
            case 'name_desc':
                $qb->orderBy('p.Name', 'DESC');
                break;
            case 'date_asc':
                $qb->orderBy('p.Start_Date', 'ASC');
                break;
            case 'date_desc':
                $qb->orderBy('p.Start_Date', 'DESC');
                break;
            case 'budget_asc':
                $qb->orderBy('p.Budget', 'ASC');
                break;
            case 'budget_desc':
                $qb->orderBy('p.Budget', 'DESC');
                break;
            default:
                $qb->orderBy('p.Name', 'ASC');
        }
    
        $projects = $qb->getQuery()->getResult();
    
        return $this->render('project/_list.html.twig', [
            'projects' => $projects,
        ]);
    }
}