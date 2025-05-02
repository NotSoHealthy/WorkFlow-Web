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
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/task')]
class TaskController extends AbstractController
{
    #[Route('/{id}', name: 'task_index', methods: ['GET', 'POST'])]
    public function index(Project $project, Request $request, EntityManagerInterface $entityManager, TaskRepository $taskRepository, NotifierInterface $notifier): Response
    {
        $user = $this->getUser();

        $task = new Task();
        $task->setProject($project);
        $task->setUser($user);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $task->setCreatedAt(new \DateTime());
                $entityManager->persist($task);
                $entityManager->flush();

                // Send browser notification
                $notification = (new Notification('Task Created'))
                    ->content('✅ Task added successfully!')
                    ->importance(Notification::IMPORTANCE_HIGH);
                $notifier->send($notification, new Recipient($user->getEmail()));

                return $this->redirectToRoute('project_my_department');
            } else {
                // Send error notification
                $notification = (new Notification('Task Creation Failed'))
                    ->content('❌ Error creating the task.')
                    ->importance(Notification::IMPORTANCE_HIGH);
                $notifier->send($notification, new Recipient($user->getEmail()));
            }
        }

        return $this->render('task/index.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
        ]);
    }

    #[Route('/edit/{id}', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(Task $task, Request $request, EntityManagerInterface $entityManager, NotifierInterface $notifier): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $task);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $task->setUpdatedAt(new \DateTime());
                $entityManager->flush();

                // Send browser notification
                $notification = (new Notification('Task Updated'))
                    ->content('✅ Task modified successfully!')
                    ->importance(Notification::IMPORTANCE_HIGH);
                $notifier->send($notification, new Recipient($this->getUser()->getEmail()));

                return $this->redirectToRoute('project_my_department');
            } else {
                // Send error notification
                $notification = (new Notification('Task Update Failed'))
                    ->content('❌ Error modifying the task.')
                    ->importance(Notification::IMPORTANCE_HIGH);
                $notifier->send($notification, new Recipient($this->getUser()->getEmail()));
            }
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/delete/{id}', name: 'task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager, NotifierInterface $notifier): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $task);

        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->request->get('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();

            // Send browser notification
            $notification = (new Notification('Task Deleted'))
                ->content('✅ Task deleted successfully!')
                ->importance(Notification::IMPORTANCE_HIGH);
            $notifier->send($notification, new Recipient($this->getUser()->getEmail()));
        } else {
            // Send error notification
            $notification = (new Notification('Task Deletion Failed'))
                ->content('❌ Invalid CSRF token.')
                ->importance(Notification::IMPORTANCE_HIGH);
            $notifier->send($notification, new Recipient($this->getUser()->getEmail()));
        }

        return $this->redirectToRoute('project_my_department');
    }

    #[Route('/update-status', name: 'task_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, EntityManagerInterface $entityManager, TaskRepository $taskRepository, NotifierInterface $notifier): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['id']) || !isset($data['status'])) {
            // Send error notification
            $notification = (new Notification('Invalid Data'))
                ->content('❌ Invalid data.')
                ->importance(Notification::IMPORTANCE_HIGH);
            $notifier->send($notification, new Recipient($this->getUser()->getEmail()));

            return $this->json(['error' => 'Invalid data'], 400);
        }

        $task = $taskRepository->find($data['id']);

        if (!$task) {
            // Send error notification
            $notification = (new Notification('Task Not Found'))
                ->content('❌ Task not found.')
                ->importance(Notification::IMPORTANCE_HIGH);
            $notifier->send($notification, new Recipient($this->getUser()->getEmail()));

            return $this->json(['error' => 'Task not found'], 404);
        }

        $task->setStatus($data['status']);
        $task->setUpdatedAt(new \DateTime());
        $entityManager->persist($task);
        $entityManager->flush();

        // Send browser notification
        $notification = (new Notification('Task Status Updated'))
            ->content('✅ Task status updated!')
            ->importance(Notification::IMPORTANCE_HIGH);
        $notifier->send($notification, new Recipient($this->getUser()->getEmail()));

        return $this->json(['success' => true, 'updatedStatus' => $task->getStatus()]);
    }
}