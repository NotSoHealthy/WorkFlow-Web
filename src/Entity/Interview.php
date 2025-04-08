<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\InterviewRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InterviewRepository::class)]
#[ORM\Table(name: 'interview')]
class Interview
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Application::class, inversedBy: 'interviews')]
    #[ORM\JoinColumn(name: 'application', referencedColumnName: 'id')]
    private ?Application $application = null;

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Assert\NotNull(message: 'Interview date must not be null.')]
    private ?\DateTimeInterface $Interview_Date = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'The location cannot be longer than {{ limit }} characters.'
    )]
    private ?string $Location = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $Feedback = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Assert\Choice(
        choices: ['Pending', 'Completed', 'Cancelled'],
        message: 'Choose a valid status: pending, completed, or cancelled.'
    )]
    private ?string $Status = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'interviews')]
    #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id')]
    private ?User $user = null;



    // Getters and setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): self
    {
        $this->application = $application;
        return $this;
    }

    public function getInterviewDate(): ?\DateTimeInterface
    {
        return $this->Interview_Date;
    }

    public function setInterviewDate(\DateTimeInterface $Interview_Date): self
    {
        $this->Interview_Date = $Interview_Date;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->Location;
    }

    public function setLocation(?string $Location): self
    {
        $this->Location = $Location;
        return $this;
    }

    public function getFeedback(): ?string
    {
        return $this->Feedback;
    }

    public function setFeedback(?string $Feedback): self
    {
        $this->Feedback = $Feedback;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->Status;
    }

    public function setStatus(?string $Status): self
    {
        $this->Status = $Status;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
