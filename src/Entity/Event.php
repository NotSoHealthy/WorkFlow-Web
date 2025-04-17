<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\EventRepository;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
class Event
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
    #[Assert\NotBlank(message: 'le titre ne doit pas être vide')]
    #[Assert\Regex(
        pattern: "/^[A-Za-z\s]+$/",
        message: "le titre doit etres lettres seulement"
    )]
    private ?string $Title = null;

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): self
    {
        $this->Title = $Title;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'la description ne doit pas être vide')]
    #[Assert\Regex(
        pattern: "/^[A-Za-z\s]+$/",
        message: "la description doit etres lettres seulement"
    )]
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

    #[ORM\Column(name: "DateAndTime",type: 'datetime', nullable: false)]
    #[Assert\NotBlank(message: 'choisir une date et une heure')]
    #[Assert\GreaterThan(
        "today",
        message: "choisir une date et une heure supérieures à la date actuelle",
    )]
    private ?\DateTimeInterface $DateAndTime = null;

    public function getDateAndTime(): ?\DateTimeInterface
    {
        return $this->DateAndTime;
    }

    public function setDateAndTime(\DateTimeInterface $DateAndTime): self
    {
        $this->DateAndTime = $DateAndTime;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: ' adresse ne doit pas être vide')]
    private ?string $Location = null;

    public function getLocation(): ?string
    {
        return $this->Location;
    }

    public function setLocation(string $Location): self
    {
        $this->Location = $Location;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'choisir un type')]
    #[Assert\NotEqualTo(
        value: "select a type",
        message: "choisir un type valide",
    )]
    private ?string $Type = null;

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(string $Type): self
    {
        $this->Type = $Type;
        return $this;
    }

    #[ORM\Column(name: "NumberOfPlaces",type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: 'nombre de places ne doit pas être vide')]
    #[Assert\Regex(
        pattern: "/^[0-9]+$/",
        message: "le nombre de places doit etres chiffres seulement"
    )]
    #[Assert\Positive(message: 'le nombre de places doit etres positif')]
    private ?int $NumberOfPlaces = null;

    public function getNumberOfPlaces(): ?int
    {
        return $this->NumberOfPlaces;
    }

    public function setNumberOfPlaces(int $NumberOfPlaces): self
    {
        $this->NumberOfPlaces = $NumberOfPlaces;
        return $this;
    }

    #[ORM\Column(name: "isOnline",type: 'boolean', nullable: false)]
    private ?bool $isOnline = null;

 

    public function setIsOnline(bool $isOnline): self
    {
        $this->isOnline = $isOnline;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'events')]
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

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'event')]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        if (!$this->reservations instanceof Collection) {
            $this->reservations = new ArrayCollection();
        }
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->getReservations()->contains($reservation)) {
            $this->getReservations()->add($reservation);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        $this->getReservations()->removeElement($reservation);
        return $this;
    }

    public function isOnline(): ?bool
    {
        return $this->isOnline;
    }
    

}
