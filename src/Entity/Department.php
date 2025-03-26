<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\DepartmentRepository;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
#[ORM\Table(name: 'department')]
class Department
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

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $Year_Budget = null;

    public function getYear_Budget(): ?float
    {
        return $this->Year_Budget;
    }

    public function setYear_Budget(float $Year_Budget): self
    {
        $this->Year_Budget = $Year_Budget;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'departments')]
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

    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'department')]
    private Collection $projects;

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        if (!$this->projects instanceof Collection) {
            $this->projects = new ArrayCollection();
        }
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->getProjects()->contains($project)) {
            $this->getProjects()->add($project);
        }
        return $this;
    }

    public function removeProject(Project $project): self
    {
        $this->getProjects()->removeElement($project);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'department')]
    private Collection $users;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        if (!$this->users instanceof Collection) {
            $this->users = new ArrayCollection();
        }
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->getUsers()->contains($user)) {
            $this->getUsers()->add($user);
        }
        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->getUsers()->removeElement($user);
        return $this;
    }

    public function getYearBudget(): ?float
    {
        return $this->Year_Budget;
    }

    public function setYearBudget(float $Year_Budget): static
    {
        $this->Year_Budget = $Year_Budget;

        return $this;
    }

}
