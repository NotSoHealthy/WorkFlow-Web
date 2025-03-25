<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DepartmentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/employe')]
final class UserController extends AbstractController
{
    #[Route('/liste', name: 'app_user_list')]
    public function index(UserRepository $userRepository, DepartmentRepository $departmentRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'departments' => $departmentRepository->findAll(),
        ]);
    }

    #[Route('/accept/{id}', name: 'app_user_accept')]
    public function accept(User $user, EntityManagerInterface $entityManager, Request $request, DepartmentRepository $departmentRepository): Response
    {
        $department = $departmentRepository->findOneById($request->request->get('department'));
        $role = $request->request->get('role');
        $user->setIs_verified(true);
        $user->setDepartment($department);
        if ($role == 'ROLE_RESPONSABLE') {
            $user->setRoles(['ROLE_RESPONSABLE']);
        }
        $entityManager->flush();

        return $this->redirectToRoute('app_user_list');
    }

    #[Route('/deny/{id}', name: 'app_user_deny')]
    public function deny(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_user_list');
    }
}
