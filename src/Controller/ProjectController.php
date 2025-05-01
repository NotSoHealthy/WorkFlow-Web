<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Task;
use App\Form\TaskType;

#[Route('/project')]
final class ProjectController extends AbstractController
{
    #[Route(name: 'project_index')]
public function index(ProjectRepository $projectRepository): Response
{
    $projects = $projectRepository->findAll();

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
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
    $this->client = $client;
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
    #[Route('/myprojects', name: 'project_my_department')]
    public function myDepartmentProjects(ProjectRepository $projectRepository): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
    $user = $this->getUser();
    /** @var User $user */

    if (!$user || !$user->getDepartment()) {
        $this->addFlash('warning', 'Aucun département associé à votre compte.');
        return $this->redirectToRoute('project_index');
    }

    $projects = $projectRepository->findBy(['department' => $user->getDepartment()]);

    return $this->render('project/myprojects.html.twig', [
        'projects' => $projects,
        'add_task_form' => $form->createView(),
        'current_project' => $projects[0] ?? null,
    ]);
}
#[Route('/gemini/chat', name: 'gemini_chat', methods: ['POST'])]
public function geminiChat(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $message = $data['message'] ?? '';

    // 1. Check if message is related to projects
    if (!$this->isProjectRelated($message)) {
        return new JsonResponse([
            'reply' => "❌ Désolé, je ne peux répondre qu'aux questions concernant les projets."
        ]);
    }

    // 2. If yes, send to Gemini
    $apiKey = $this->getParameter('gemini_api_key');
    $response = $this->client->request('POST', 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro-002:generateContent?key=' . $apiKey, [
        'json' => [
            'contents' => [[
                'parts' => [[ 'text' => $message ]]
            ]]
        ],
    ]);

    $result = $response->toArray(false);
    $reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? "Aucune réponse de Gemini.";

    return new JsonResponse(['reply' => $reply]);
}
private function isProjectRelated(string $message): bool
{
    $keywords = [
        'project', 'projet', 'initiative', 'programme', 'planification', 'planning', 'gestion', 'management', 
        'scope', 'portée', 'livrable', 'deliverable', 'objectif', 'objective', 'goal', 'milestone', 'jalon', 
        'tâche', 'task', 'étape', 'phase', 'progression', 'progress', 'workflow', 'processus', 'process', 
        'timeline', 'échéancier', 'deadline', 'due date',
        'équipe', 'team', 'collaborateurs', 'members', 'employé', 'employee', 'manager', 'gestionnaire', 
        'chef de projet', 'project manager', 'responsable', 'stakeholder', 'intervenant', 'scrum master', 
        'product owner', 'consultant', 'client',
        'ressources', 'resources', 'budget', 'finance', 'coût', 'outil', 'tool', 'logiciel', 'software', 
        'temps', 'time', 'allocation', 'planning tool', 'jira', 'trello', 'notion', 'asana',
        'kpi', 'indicateurs', 'indicators', 'metrics', 'résultats', 'results', 'rapport', 'report', 
        'retour', 'feedback', 'réunion', 'meeting', 'synthèse', 'bilan',
        'risque', 'risk', 'incident', 'bug', 'problem', 'issue', 'blocker', 'obstacle', 'frein', 
        'mitigation', 'contingency', 'plan b', 'retard', 'delay',
        'agile', 'scrum', 'kanban', 'lean', 'waterfall', 'cycle en v', 'sprint', 'stand-up', 
        'daily', 'retro', 'backlog', 'story', 'epic', 'user story', 'planning poker'
    ];

    $message = strtolower($message);

    foreach ($keywords as $keyword) {
        if (strpos($message, $keyword) !== false) {
            return true;
        }
    }

    return false;
}
#[Route('/{id}/tasks/json', name: 'project_tasks_json', methods: ['GET'])]
public function projectTasksJson(Project $project, TaskRepository $taskRepository): JsonResponse
{
    $tasks = $taskRepository->findBy(['project' => $project]);

    $taskData = [];

    foreach ($tasks as $task) {
        $taskData[] = [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'priority' => $task->getPriority(),
        ];
    }

    return $this->json($taskData);
}


}