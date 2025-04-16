<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ReservationRepository;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
class Reservation
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

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $Price = null;

    public function getPrice(): ?float
    {
        return $this->Price;
    }

    public function setPrice(float $Price): self
    {
        $this->Price = $Price;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
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

    #[ORM\Column(name:'NombreDePlaces', type: 'integer', nullable: false)]
    private ?int $NombreDePlaces = null;

    public function getNombreDePlaces(): ?int
    {
        return $this->NombreDePlaces;
    }

    public function setNombreDePlaces(int $NombreDePlaces): self
    {
        $this->NombreDePlaces = $NombreDePlaces;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservations')]
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

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private ?Event $event = null;

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $qr_url = null;

    public function getQr_url(): ?string
    {
        return $this->qr_url;
    }

    public function setQr_url(?string $qr_url): self
    {
        $this->qr_url = $qr_url;
        return $this;
    }

    public function getQrUrl(): ?string
    {
        return $this->qr_url;
    }

    public function setQrUrl(?string $qr_url): static
    {
        $this->qr_url = $qr_url;

        return $this;
    }

}
