<?php
// src/Security/UserChecker.php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getStatus() !== 'approved') {
            throw new CustomUserMessageAccountStatusException("Votre compte n'est pas encore approuvé.");
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // You can leave this empty if not needed
    }
}
