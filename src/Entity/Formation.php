<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Repository\FormationRepository;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
#[ORM\Table(name: 'formation')]
class Formation
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
    #[Assert\NotBlank(message: 'Le titre ne doit pas être vide')]
    private ?string $title = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'La description ne doit pas être vide')]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date_begin = null;

    public function getDate_begin(): ?\DateTimeInterface
    {
        return $this->date_begin;
    }

    public function setDate_begin(\DateTimeInterface $date_begin): self
    {
        $this->date_begin = $date_begin;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date_end = null;

    public function getDate_end(): ?\DateTimeInterface
    {
        return $this->date_end;
    }

    public function setDate_end(\DateTimeInterface $date_end): self
    {
        $this->date_end = $date_end;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: 'Le nombre de participants ne doit pas être vide')]
    #[Assert\Type(type: 'integer', message: 'Le nombre de participants doit être un nombre entier')]
    #[Assert\Positive(message: 'Le nombre de participants doit être supérieur à zéro')]
    private ?int $Participants_Max = null;

    public function getParticipants_Max(): ?int
    {
        return $this->Participants_Max;
    }

    public function setParticipants_Max(int $Participants_Max): self
    {
        $this->Participants_Max = $Participants_Max;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'formations')]
    #[ORM\JoinColumn(name: 'responsable', referencedColumnName: 'id')]
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

    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'formation')]
    private Collection $inscriptions;

    public function __construct()
    {
        $this->date_begin = new \DateTime();
        $this->date_end = (new \DateTime())->add(new \DateInterval('P7D'));
        $this->inscriptions = new ArrayCollection();
    }

    /**
     * @return Collection<int, Inscription>
     */
    public function getInscriptions(): Collection
    {
        if (!$this->inscriptions instanceof Collection) {
            $this->inscriptions = new ArrayCollection();
        }
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): self
    {
        if (!$this->getInscriptions()->contains($inscription)) {
            $this->getInscriptions()->add($inscription);
        }
        return $this;
    }

    public function removeInscription(Inscription $inscription): self
    {
        $this->getInscriptions()->removeElement($inscription);
        return $this;
    }

    public function getDateBegin(): ?\DateTimeInterface
    {
        return $this->date_begin;
    }

    public function setDateBegin(\DateTimeInterface $date_begin): static
    {
        $this->date_begin = $date_begin;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->date_end;
    }

    public function setDateEnd(\DateTimeInterface $date_end): static
    {
        $this->date_end = $date_end;

        return $this;
    }

    public function getParticipantsMax(): ?int
    {
        return $this->Participants_Max;
    }

    public function setParticipantsMax(int $Participants_Max): static
    {
        $this->Participants_Max = $Participants_Max;

        return $this;
    }
    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        if ($this->date_begin && $this->date_end && $this->date_begin > $this->date_end) {
            $context->buildViolation('La date de début doit être avant la date de fin.')
                ->atPath('date_begin')
                ->addViolation();
        }
    }

}
