<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\DelegationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route(path: '/edit-profile', name: 'app_profile_edit_profile')]
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
                return $this->redirectToRoute('app_profile_edit_profile');
            }
            $numberExist = $userRepository->findOneBy(['number' => $number]);
            if ($numberExist && $user->getNumber() != $number) {
                $this->addFlash('edit_profile_error', 'Numéro deja utilisé');
                return $this->redirectToRoute('app_profile_edit_profile');
            }
            if (!empty($password) && $password != $confirmPassword) {
                $this->addFlash('edit_profile_error', 'Les mots de passe ne correspondent pas');
                return $this->redirectToRoute('app_profile_edit_profile');
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
            return $this->redirectToRoute('app_profile_edit_profile');
            
        }
        return $this->render('profile/edit_profile.html.twig', ["gouvernorats" => $delegationService->getDelegations()]);
    }
}
