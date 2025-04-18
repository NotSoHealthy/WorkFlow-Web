<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\CongeRepository;

#[ORM\Entity(repositoryClass: CongeRepository::class)]
#[ORM\Table(name: 'conge')]
class Conge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'conges')]
    #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id')]
    #[Assert\NotBlank(message: "L'utilisateur ne peut pas être vide.")]
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\Type(type: \DateTimeInterface::class, message: "La date de début doit être une date valide.")]
    #[Assert\LessThanOrEqual("today", message: "La date de demande ne peut pas être dans le futur.")]
    private ?\DateTimeInterface $request_date = null;

    public function getRequest_date(): ?\DateTimeInterface
    {
        return $this->request_date;
    }

    public function setRequest_date(?\DateTimeInterface $request_date): self
    {
        $this->request_date = $request_date;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\Type(type: \DateTimeInterface::class, message: "La date de début doit être une date valide.")]
    #[Assert\NotNull(message: "La date de début est obligatoire.")]
    #[Assert\GreaterThanOrEqual("today", message: "La date de début ne peut pas être dans le passé.")]
    private ?\DateTimeInterface $start_date = null;

    public function getStart_date(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStart_date(\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\Type(type: \DateTimeInterface::class, message: "La date de début doit être une date valide.")]
    #[Assert\NotNull(message: "La date de fin doit être une date valide.")]
    #[Assert\GreaterThanOrEqual(propertyPath: "start_date", message: "La date de fin doit être après la date de début.")]
    private ?\DateTimeInterface $end_date = null;

    public function getEnd_date(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEnd_date(\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: "Une raison est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "La raison ne peut pas dépasser 255 caractères.")]
    private ?string $reason = null;

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Le statut ne peut pas être vide.")]
    #[Assert\Choice(choices: ["pending", "approved", "rejected"], message: "Le statut doit être l'un des suivants : 'pending', 'approved', ou 'rejected'.")]
    private ?string $status = null;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getRequestDate(): ?\DateTimeInterface
    {
        return $this->request_date;
    }

    public function setRequestDate(?\DateTimeInterface $request_date): static
    {
        $this->request_date = $request_date;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(?\DateTimeInterface $start_date): static
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(?\DateTimeInterface $end_date): static
    {
        $this->end_date = $end_date;

        return $this;
    }

}
