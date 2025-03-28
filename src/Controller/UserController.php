<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DepartmentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

use function PHPUnit\Framework\isEmpty;

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

    #[Route('/edit-profile', name: 'app_user_edit_profile')]
    public function editProfile(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            /** @var User $user */
            $user = $this->getUser();
            $firstName = $request->request->get('prenom');
            $lastName = $request->request->get('nom');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirmation');
            $imageUrl = $request->request->get('image_url'); // Retrieve the image URL

            $emailExist = $userRepository->findOneBy(['email' => $email]);
            if ($emailExist && $user->getEmail() != $email) {
                $this->addFlash('error', 'Email deja utilisé');
                return $this->redirectToRoute('app_user_edit_profile');
            }
            if (!empty($password) && $password != $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas');
                return $this->redirectToRoute('app_user_edit_profile');
            };
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);
            if (!empty($password)) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $password));
            }
            if (!empty($imageUrl) && $imageUrl != $user->getImageUrl()) {
                $user->setImageUrl($imageUrl);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Profil modifié avec succès');
            return $this->redirectToRoute('app_user_edit_profile');
            
        }
        return $this->render('user/edit_profile.html.twig');
    }
}
