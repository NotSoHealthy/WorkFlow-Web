<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;

class MeetMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function sendMeet( User $user,string $MeetLink,string $Title,TemplatedEmail $email): void
    {
        $context = $email->getContext();
        $context['url'] = $MeetLink;
        $context['title'] = $Title;
        $context['UserName'] = $user->getFirst_name() . ' ' . $user->getLast_name();

        $email->context($context);

        $this->mailer->send($email);
    }
}