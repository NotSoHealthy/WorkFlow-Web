<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ProjectRepository;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'project')]
class Project
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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $Name = null;

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $Description = null;

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): self
    {
        $this->Description = $Description;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $Start_Date = null;

    public function getStart_Date(): ?\DateTimeInterface
    {
        return $this->Start_Date;
    }

    public function setStart_Date(\DateTimeInterface $Start_Date): self
    {
        $this->Start_Date = $Start_Date;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $End_Date = null;

    public function getEnd_Date(): ?\DateTimeInterface
    {
        return $this->End_Date;
    }

    public function setEnd_Date(?\DateTimeInterface $End_Date): self
    {
        $this->End_Date = $End_Date;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $State = null;

    public function getState(): ?string
    {
        return $this->State;
    }

    public function setState(?string $State): self
    {
        $this->State = $State;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $Budget = null;

    public function getBudget(): ?float
    {
        return $this->Budget;
    }

    public function setBudget(float $Budget): self
    {
        $this->Budget = $Budget;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(name: 'manager', referencedColumnName: 'id', nullable: false)]
    private ?User $Manager = null;

    public function getManager(): ?User
    {
        return $this->Manager;
    }

    public function setManager(?User $Manager): self
    {
        $this->Manager = $Manager;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Department::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(name: 'Department', referencedColumnName: 'id')]
    private ?Department $department = null;

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'project')]
    private Collection $tasks;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        if (!$this->tasks instanceof Collection) {
            $this->tasks = new ArrayCollection();
        }
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->getTasks()->contains($task)) {
            $this->getTasks()->add($task);
        }
        return $this;
    }

    public function removeTask(Task $task): self
    {
        $this->getTasks()->removeElement($task);
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->Start_Date;
    }

    public function setStartDate(\DateTimeInterface $Start_Date): static
    {
        $this->Start_Date = $Start_Date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->End_Date;
    }

    public function setEndDate(?\DateTimeInterface $End_Date): static
    {
        $this->End_Date = $End_Date;

        return $this;
    }

}
