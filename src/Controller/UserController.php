<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DepartmentRepository;
use App\Repository\UserRepository;
use App\Service\DelegationService;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function editProfile(DelegationService $delegationService,Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            /** @var User $user */
            $user = $this->getUser();
            $firstName = $request->request->get('prenom');
            $lastName = $request->request->get('nom');
            $email = $request->request->get('email');
            $number = $request->request->get('number');
            $gouvernorat = $request->request->get('gouvernorat');
            $address = $request->request->get('address');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirmation');
            $imageUrl = $request->request->get('image_url'); // Retrieve the image URL

            $emailExist = $userRepository->findOneBy(['email' => $email]);
            if ($emailExist && $user->getEmail() != $email) {
                $this->addFlash('edit_profile_error', 'Email deja utilisé');
                return $this->redirectToRoute('app_user_edit_profile');
            }
            $numberExist = $userRepository->findOneBy(['number' => $number]);
            if ($numberExist && $user->getNumber() != $number) {
                $this->addFlash('edit_profile_error', 'Numéro deja utilisé');
                return $this->redirectToRoute('app_user_edit_profile');
            }
            if (!empty($password) && $password != $confirmPassword) {
                $this->addFlash('edit_profile_error', 'Les mots de passe ne correspondent pas');
                return $this->redirectToRoute('app_user_edit_profile');
            };
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);
            $user->setNumber($number);
            if ($gouvernorat != ""){
                $user->setGouvernorat($gouvernorat);
            }
            if ($address != ""){
                $user->setAddress($address);
            }
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
        return $this->render('user/edit_profile.html.twig', ["gouvernorats" => $delegationService->getDelegations()]);
    }

    #[Route('/enable-2fa', name: 'enable_2fa', methods: ['POST'])]
    public function enable2FA(EntityManagerInterface $entityManager, GoogleAuthenticatorInterface $googleAuthenticator): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isGoogleAuthenticatorEnabled()) {
            $secret = $googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $entityManager->flush();

            $qrCodeUrl = $googleAuthenticator->getQRContent($user);
            dump($qrCodeUrl);
            return new JsonResponse(['qrCodeUrl' => $qrCodeUrl]);
        }
        return new JsonResponse(['error' => '2FA already enabled'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/disable-2fa', name: 'disable_2fa', methods: ['POST'])]
    public function disable2FA(EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->isGoogleAuthenticatorEnabled()) {
            $user->setGoogleAuthenticatorSecret(null);
            $entityManager->flush();
            return new JsonResponse(['success' => '2FA disabled successfully']);
        }
        return new JsonResponse(['error' => '2FA is not enabled'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_user_profile')]
    public function profile(User $user, EntityManagerInterface $entityManager, DelegationService $delegationService, DepartmentRepository $departmentRepository, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $department = $request->request->get('departement');
            $role = $request->request->get('role');
            $user->setDepartment($departmentRepository->find($department));
            $user->setRoles([$role === 'responsable' ? 'ROLE_RESPONSABLE' : 'ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_profile', ['id' => $user->getId()]);
        }

        return $this->render('user/profile.html.twig', [
            "user" => $user,
            "gouvernorats" => $delegationService->getDelegations(),
            "departments" => $departmentRepository->findAll(),
        ]);
    }
}
