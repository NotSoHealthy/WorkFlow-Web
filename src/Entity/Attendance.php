<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\AttendanceRepository;

#[ORM\Entity(repositoryClass: AttendanceRepository::class)]
#[ORM\Table(name: 'attendance')]
class Attendance
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'attendances')]
    #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id')]
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

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date = null;

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    #[ORM\Column(type: 'time', nullable: false)]
    private ?string $entry_time = null;

    public function getEntry_time(): ?string
    {
        return $this->entry_time;
    }

    public function setEntry_time(string $entry_time): self
    {
        $this->entry_time = $entry_time;
        return $this;
    }

    #[ORM\Column(type: 'time', nullable: false)]
    private ?string $exit_time = null;

    public function getExit_time(): ?string
    {
        return $this->exit_time;
    }

    public function setExit_time(string $exit_time): self
    {
        $this->exit_time = $exit_time;
        return $this;
    }

    public function getEntryTime(): ?\DateTimeInterface
    {
        return $this->entry_time;
    }

    public function setEntryTime(\DateTimeInterface $entry_time): static
    {
        $this->entry_time = $entry_time;

        return $this;
    }

    public function getExitTime(): ?\DateTimeInterface
    {
        return $this->exit_time;
    }

    public function setExitTime(\DateTimeInterface $exit_time): static
    {
        $this->exit_time = $exit_time;

        return $this;
    }

}
