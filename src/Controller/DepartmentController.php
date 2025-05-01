<?php

namespace App\Controller;

use App\Entity\Department;
use App\Entity\User;
use App\Form\DepartmentType;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/department')]
final class DepartmentController extends AbstractController
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route( name: 'department_index')]
    public function index(DepartmentRepository $departmentRepo,PaginatorInterface $paginator, Request $request): Response
    {

    $departments = $departmentRepo->findAll();

    // Chart data: array of dept names + budgets
    $deptNames = [];
    $deptBudgets = [];
    $pagination = $paginator->paginate(
        $departments,
        $request->query->getInt('page', 1),
        3
    );
    foreach ($departments as $dept) {
        $deptNames[] = $dept->getName(); // or getNom() if using French field
        $deptBudgets[] = $dept->getYearBudget();
    }

    return $this->render('department/index.html.twig', [
        'departments' => $pagination,
        'chartData' => [
            'labels' => $deptNames,
            'budgets' => $deptBudgets
        ]
    ]);
}

    #[Route('/new', name: 'department_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $department = new Department();
        $form = $this->createForm(DepartmentType::class, $department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($department);
            $entityManager->flush();

            $this->addFlash('success', 'Département créé avec succès !');

            return $this->redirectToRoute('department_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('department/new.html.twig', [
            'department' => $department,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'department_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Department $department, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DepartmentType::class, $department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Département modifié avec succès !');

            return $this->redirectToRoute('department_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('department/edit.html.twig', [
            'department' => $department,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'department_delete', methods: ['POST'])]
    public function delete(Request $request, Department $department, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$department->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($department);
            $entityManager->flush();

            $this->addFlash('success', 'Département supprimé avec succès !');
        }

        return $this->redirectToRoute('department_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/search', name: 'department_search', methods: ['GET'])]
    public function search(Request $request, DepartmentRepository $repo): Response
    {
        $q = $request->query->get('q');
        $sort = $request->query->get('sort');
        $qb = $repo->createQueryBuilder('d');
        if ($q) {
            $qb->andWhere('LOWER(d.Name) LIKE :q')
                ->setParameter('q', '%' . strtolower($q) . '%');
        }
        switch ($sort) {
            case 'name_asc':
                $qb->orderBy('d.Name', 'ASC');
                break;
            case 'name_desc':
                $qb->orderBy('d.Name', 'DESC');
                break;
            case 'budget_asc':
                $qb->orderBy('d.Year_Budget', 'ASC');
                break;
            case 'budget_desc':
                $qb->orderBy('d.Year_Budget', 'DESC');
                break;
        }
        $departments = $qb->getQuery()->getResult();
        return $this->render('department/_list.html.twig', [
            'departments' => $departments,
        ]);
    }
    #[Route('/{id}/ai-suggest', name: 'department_ai_suggest')]
    public function suggestToolsWithGemini(Department $department): Response
    {
        $type = $department->getName() ?? 'technology';
        $prompt = "Donne-moi une liste de 5 outils modernes utilisés dans un département de $type Réponds en français, en utilisant ce format :
* **NomDeLoutil:** Courte description de son utilité. Ajoute un émoji pertinent pour chaque outil.";
        $apiKey = $this->getParameter('gemini_api_key');
        $response = $this->client->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey, [
            'json' => [
                'contents' => [[
                    'parts' => [[ 'text' => $prompt ]]
                ]]
            ],
        ]);
        $data = $response->toArray(false);
        $suggestion = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No suggestion received from Gemini.';
        $lines = preg_split('/\r\n|\r|\n/', $suggestion);
        return $this->render('department/ai_suggest.html.twig', [
            'department' => $department,
            'tools' => $lines,
        ]);
    }
    #[Route('/my-department', name: 'department_my', methods: ['GET'])]
    public function myDepartment(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user || !$user->getDepartment()) {
            $this->addFlash('warning', 'Aucun département associé à votre compte.');
            return $this->redirectToRoute('department_index');
        }
        $department = $user->getDepartment();
        return $this->render('department/my_department.html.twig', [
            'department' => $department,
        ]);
    }
}