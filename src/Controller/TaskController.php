<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\Project;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/task')]
class TaskController extends AbstractController
{
    #[Route('/{id}', name: 'task_index', methods: ['GET', 'POST'])]
    public function index(Project $project, Request $request, EntityManagerInterface $entityManager, TaskRepository $taskRepository): Response
    {
        $user = $this->getUser();

        // Form logic
        $task = new Task();
        $task->setProject($project);
        $task->setUser($user);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setCreatedAt(new \DateTime());
            $entityManager->persist($task);
            $entityManager->flush();

            // Redirect to refresh the project view
            return $this->redirectToRoute('project_my_department');
        }

        // You don’t need this page rendered directly unless debugging
        return $this->render('task/index.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
        ]);
    }

    #[Route('/edit/{id}', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(Task $task, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $task);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('project_my_department');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/delete/{id}', name: 'task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        //$this->denyAccessUnlessGranted('DELETE', $task);

        //if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->request->get('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
        //}

        return $this->redirectToRoute('project_my_department');
    }

    #[Route('/update-status', name: 'task_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, EntityManagerInterface $entityManager, TaskRepository $taskRepository): Response
    {
        $data = json_decode($request->getContent(), true);
    
        if (!$data || !isset($data['id']) || !isset($data['status'])) {
            return $this->json(['error' => 'Invalid data'], 400);
        }
    
        $task = $taskRepository->find($data['id']);
    
        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }
    
        $task->setStatus($data['status']);
        $task->setUpdatedAt(new \DateTime());
        $entityManager->persist($task);
        $entityManager->flush();
    
        return $this->json(['success' => true, 'updatedStatus' => $task->getStatus()]);
    }

}
