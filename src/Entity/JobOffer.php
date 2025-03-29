<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\JobOfferRepository;

#[ORM\Entity(repositoryClass: JobOfferRepository::class)]
#[ORM\Table(name: 'job_offer')]
class JobOffer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $Title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $Description = null;

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $Publication_Date = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $Expiration_Date = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $Contract_Type = null;

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $Salary = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'jobOffers')]
    #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id')]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: Application::class, mappedBy: 'jobOffer')]
    private Collection $applications;

    public function __construct()
    {
        $this->applications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): self
    {
        $this->Title = $Title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): self
    {
        $this->Description = $Description;
        return $this;
    }

    public function getPublication_Date(): ?\DateTimeInterface
    {
        return $this->Publication_Date;
    }

    public function setPublication_Date(\DateTimeInterface $Publication_Date): self
    {
        $this->Publication_Date = $Publication_Date;
        return $this;
    }

    public function getExpiration_Date(): ?\DateTimeInterface
    {
        return $this->Expiration_Date;
    }

    public function setExpiration_Date(?\DateTimeInterface $Expiration_Date): self
    {
        $this->Expiration_Date = $Expiration_Date;
        return $this;
    }

    public function getContract_Type(): ?string
    {
        return $this->Contract_Type;
    }

    public function setContract_Type(?string $Contract_Type): self
    {
        $this->Contract_Type = $Contract_Type;
        return $this;
    }

    public function getSalary(): ?float
    {
        return $this->Salary;
    }

    public function setSalary(?float $Salary): self
    {
        $this->Salary = $Salary;
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

    /**
     * @return Collection<int, Application>
     */
    public function getApplications(): Collection
    {
        if (!$this->applications instanceof Collection) {
            $this->applications = new ArrayCollection();
        }
        return $this->applications;
    }

    public function addApplication(Application $application): self
    {
        if (!$this->getApplications()->contains($application)) {
            $this->getApplications()->add($application);
        }
        return $this;
    }

    public function removeApplication(Application $application): self
    {
        $this->getApplications()->removeElement($application);
        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->Publication_Date;
    }

    public function setPublicationDate(\DateTimeInterface $Publication_Date): static
    {
        $this->Publication_Date = $Publication_Date;
        return $this;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->Expiration_Date;
    }

    public function setExpirationDate(?\DateTimeInterface $Expiration_Date): static
    {
        $this->Expiration_Date = $Expiration_Date;
        return $this;
    }

    public function getContractType(): ?string
    {
        return $this->Contract_Type;
    }

    public function setContractType(?string $Contract_Type): static
    {
        $this->Contract_Type = $Contract_Type;
        return $this;
    }

    /**
     * Convert the JobOffer object to a string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->Title;
    }
}
