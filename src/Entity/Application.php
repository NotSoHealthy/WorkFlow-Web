<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ApplicationRepository;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ORM\Table(name: 'application')]
class Application
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

    #[ORM\ManyToOne(targetEntity: JobOffer::class, inversedBy: 'applications')]
    #[ORM\JoinColumn(name: 'job', referencedColumnName: 'id')]
    private ?JobOffer $jobOffer = null;

    public function getJobOffer(): ?JobOffer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?JobOffer $jobOffer): self
    {
        $this->jobOffer = $jobOffer;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $CV = null;

    public function getCV(): ?string
    {
        return $this->CV;
    }

    public function setCV(string $CV): self
    {
        $this->CV = $CV;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $Cover_Letter = null;

    public function getCover_Letter(): ?string
    {
        return $this->Cover_Letter;
    }

    public function setCover_Letter(?string $Cover_Letter): self
    {
        $this->Cover_Letter = $Cover_Letter;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $Submission_Date = null;

    public function getSubmission_Date(): ?\DateTimeInterface
    {
        return $this->Submission_Date;
    }

    public function setSubmission_Date(\DateTimeInterface $Submission_Date): self
    {
        $this->Submission_Date = $Submission_Date;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $status = null;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'applications')]
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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $First_Name = null;

    public function getFirst_Name(): ?string
    {
        return $this->First_Name;
    }

    public function setFirst_Name(?string $First_Name): self
    {
        $this->First_Name = $First_Name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $Last_Name = null;

    public function getLast_Name(): ?string
    {
        return $this->Last_Name;
    }

    public function setLast_Name(?string $Last_Name): self
    {
        $this->Last_Name = $Last_Name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $mail = null;

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): self
    {
        $this->mail = $mail;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Interview::class, mappedBy: 'application')]
    private Collection $interviews;

    public function __construct()
    {
        $this->interviews = new ArrayCollection();
    }

    /**
     * @return Collection<int, Interview>
     */
    public function getInterviews(): Collection
    {
        if (!$this->interviews instanceof Collection) {
            $this->interviews = new ArrayCollection();
        }
        return $this->interviews;
    }

    public function addInterview(Interview $interview): self
    {
        if (!$this->getInterviews()->contains($interview)) {
            $this->getInterviews()->add($interview);
        }
        return $this;
    }

    public function removeInterview(Interview $interview): self
    {
        $this->getInterviews()->removeElement($interview);
        return $this;
    }

    public function getCoverLetter(): ?string
    {
        return $this->Cover_Letter;
    }

    public function setCoverLetter(?string $Cover_Letter): static
    {
        $this->Cover_Letter = $Cover_Letter;

        return $this;
    }

    public function getSubmissionDate(): ?\DateTimeInterface
    {
        return $this->Submission_Date;
    }

    public function setSubmissionDate(\DateTimeInterface $Submission_Date): static
    {
        $this->Submission_Date = $Submission_Date;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->First_Name;
    }

    public function setFirstName(?string $First_Name): static
    {
        $this->First_Name = $First_Name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->Last_Name;
    }

    public function setLastName(?string $Last_Name): static
    {
        $this->Last_Name = $Last_Name;

        return $this;
    }
}
