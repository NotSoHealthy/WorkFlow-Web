<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\ApplicationRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ORM\Table(name: 'application')]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'boolean')]
    private bool $alreadyApplied = false;

    #[ORM\Column(type: 'string', length: 45, nullable: false)]
    private string $ipAddress;

    #[ORM\ManyToOne(targetEntity: JobOffer::class, inversedBy: 'applications')]
    #[ORM\JoinColumn(name: 'job', referencedColumnName: 'id')]
    private ?JobOffer $jobOffer = null;

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $cv = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $coverLetter = null;

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $submissionDate = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $status = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'applications')]
    #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id')]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'First name cannot be blank.')]
    private ?string $firstName = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'Last name cannot be blank.')]
    private ?string $lastName = null;

    #[ORM\Column(type: 'string', length: 180, nullable: true)]
    #[Assert\NotBlank(message: 'Email cannot be blank.')]
    private ?string $mail = null;

    #[ORM\OneToMany(targetEntity: Interview::class, mappedBy: 'application')]
    private Collection $interviews;

    public function __construct()
    {
        $this->interviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobOffer(): ?JobOffer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?JobOffer $jobOffer): self
    {
        $this->jobOffer = $jobOffer;
        return $this;
    }

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(string $cv): self
    {
        $this->cv = $cv;
        return $this;
    }

    public function getCoverLetter(): ?string
    {
        return $this->coverLetter;
    }

    public function setCoverLetter(?string $coverLetter): self
    {
        $this->coverLetter = $coverLetter;
        return $this;
    }

    public function getSubmissionDate(): ?\DateTimeInterface
    {
        return $this->submissionDate;
    }

    public function setSubmissionDate(\DateTimeInterface $submissionDate): self
    {
        $this->submissionDate = $submissionDate;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): self
    {
        $this->mail = $mail;
        return $this;
    }

    /**
     * @return Collection<int, Interview>
     */
    public function getInterviews(): Collection
    {
        return $this->interviews;
    }

    public function addInterview(Interview $interview): self
    {
        if (!$this->interviews->contains($interview)) {
            $this->interviews->add($interview);
            $interview->setApplication($this);
        }
        return $this;
    }

    public function removeInterview(Interview $interview): self
    {
        if ($this->interviews->removeElement($interview)) {
            if ($interview->getApplication() === $this) {
                $interview->setApplication(null);
            }
        }
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function isAlreadyApplied(): bool
    {
        return $this->alreadyApplied;
    }

    public function setAlreadyApplied(bool $alreadyApplied): self
    {
        $this->alreadyApplied = $alreadyApplied;
        return $this;
    }
}
