<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Form\RegistrationFormType;
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
    public function editProfile(DelegationService $delegationService, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $originalUser = clone $user;

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_profile_edit_profile');
        } else {
            $user->setFirstName($originalUser->getFirstName());
            $user->setLastName($originalUser->getLastName());
            $user->setEmail($originalUser->getEmail());
            $user->setNumber($originalUser->getNumber());
            $user->setGouvernorat($originalUser->getGouvernorat());
            $user->setAddress($originalUser->getAddress());
            $user->setImageUrl($originalUser->getImageUrl());
        }


        return $this->render('profile/edit_profile.html.twig', ["form" => $form->createView(), "gouvernorats" => $delegationService->getDelegations()]);
    }
}
