<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\ReclamationRepository;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
#[ORM\Table(name: 'reclamation')]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Le titre est obligatoire')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Le titre est trop court')]
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $titre = null;


    #[Assert\NotBlank(message: 'La description est obligatoire')]
    #[Assert\Length(min: 10, minMessage: 'La description est trop courte')]
    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $category = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $type = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $attachedfile = null;

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'time', nullable: false)]
    private ?\DateTimeInterface $heure = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $etat = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_resolution = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reclamationsResponsable')]
    #[ORM\JoinColumn(name: 'responsable', referencedColumnName: 'id')]
    private ?User $responsable = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reclamations')]
    #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id')]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'reclamation')]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
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

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getAttachedfile(): ?string
    {
        return $this->attachedfile;
    }

    public function setAttachedfile(?string $attachedfile): self
    {
        $this->attachedfile = $attachedfile;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): self
    {
        $this->heure = $heure;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;
        return $this;
    }

    public function getDateResolution(): ?\DateTimeInterface
    {
        return $this->date_resolution;
    }

    public function setDateResolution(?\DateTimeInterface $date_resolution): self
    {
        $this->date_resolution = $date_resolution;
        return $this;
    }

    public function getResponsable(): ?User
    {
        return $this->responsable;
    }

    public function setResponsable(?User $responsable): self
    {
        $this->responsable = $responsable;
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
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        if (!$this->messages instanceof Collection) {
            $this->messages = new ArrayCollection();
        }
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->getMessages()->contains($message)) {
            $this->getMessages()->add($message);
            $message->setReclamation($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): self
    {
        $this->getMessages()->removeElement($message);
        return $this;
    }
}
