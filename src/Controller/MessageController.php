<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Reclamation;



#[Route('/message')]
final class MessageController extends AbstractController
{

    #[Route(name: 'app_message_index', methods: ['GET'])]
    public function index(MessageRepository $messageRepository): Response
    {
        return $this->render('message/index.html.twig', [
            'messages' => $messageRepository->findAll(),
        ]);
    }

    #[Route('/{id}/details', name: 'app_reclamation_details', methods: ['GET', 'POST'])]
    public function details(Request $request, EntityManagerInterface $entityManager, Reclamation $reclamation): Response
    {

       
        $editMessageId = $request->request->get('edit_message_id');
        $message = $editMessageId 
            ? $entityManager->getRepository(Message::class)->find($editMessageId)
            : new Message();

        if ($editMessageId && $message->getReclamation() !== $reclamation) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(MessageType::class, $message, [
            'reclamation' => $reclamation
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $timezone = new \DateTimeZone('+01:00');
            $now = new \DateTime('now', $timezone);

            if (!$editMessageId) {
                $message->setUser($this->getUser()->getId());
                $message->setDate($now);
                $message->setHeure($now);
                $message->setReclamation($reclamation);
            }

            $entityManager->persist($message);
            $entityManager->flush();

            // Always return partial view for both AJAX and regular requests
            // This allows the modal to be updated dynamically
        }

        $searchQuery = $request->query->get('search', '');
        $messages = $searchQuery 
            ? $entityManager->getRepository(Message::class)->createQueryBuilder('m')
                ->where('m.reclamation = :reclamation')
                ->andWhere('m.contenu LIKE :search')
                ->setParameter('reclamation', $reclamation)
                ->setParameter('search', '%' . $searchQuery . '%')
                ->getQuery()
                ->getResult()
            : $reclamation->getMessages();

        $users = [];
        foreach ($messages as $message) {
            $users[$message->getId()] = $entityManager->getRepository(User::class)->find($message->getUser());
        }

        // Always render just the _details.html.twig partial
        return $this->render('reclamation/_details.html.twig', [
            'reclamation' => $reclamation,
     
            'messages' => $messages,
            'form' => $form->createView(),
            'users' => $users,
            'edit_message_id' => $editMessageId,
            'search_query' => $searchQuery
        ]);
    }
    #[Route('/{id}', name: 'app_message_delete', methods: ['POST'])]
public function delete(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        $isAjax = $request->isXmlHttpRequest();
        $searchQuery = $request->query->get('search', '');

        if (!$message) {
            if ($isAjax) {
                return new JsonResponse(['error' => 'Message not found'], 404);
            }
            throw new NotFoundHttpException('Message not found');
        }

        $reclamation = $message->getReclamation();

        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($message);
            $entityManager->flush();
        } else {
            if ($isAjax) {
                return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
            }
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        $messages = $searchQuery 
            ? $entityManager->getRepository(Message::class)->createQueryBuilder('m')
                ->where('m.reclamation = :reclamation')
                ->andWhere('m.contenu LIKE :search')
                ->setParameter('reclamation', $reclamation)
                ->setParameter('search', '%' . $searchQuery . '%')
                ->getQuery()
                ->getResult()
            : $reclamation->getMessages();

        $users = [];
        foreach ($messages as $msg) {
            $user = $entityManager->getRepository(User::class)->find($msg->getUser());
            if ($user) {
                $users[$msg->getId()] = $user;
            }
        }

        if ($isAjax) {
            return $this->render('reclamation/_details.html.twig', [
                'reclamation' => $reclamation,
                'messages' => $messages,
                'edit_message_id' => null,
                'users' => $users,
                'form' => $this->createForm(MessageType::class, new Message(), [
                    'reclamation' => $reclamation
                ])->createView(),
                'search_query' => $searchQuery
            ]);
        }

        return $this->redirectToRoute('app_reclamation_details', ['id' => $reclamation->getId()]);
    }
}
