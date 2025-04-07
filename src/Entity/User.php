<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;

#[ORM\Table(name: 'user')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
    */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }
    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

   /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $first_name = null;

    public function getFirst_name(): ?string
    {
        return $this->first_name;
    }

    public function setFirst_name(string $first_name): self
    {
        $this->first_name = $first_name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $last_name = null;

    public function getLast_name(): ?string
    {
        return $this->last_name;
    }

    public function setLast_name(string $last_name): self
    {
        $this->last_name = $last_name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $number = null;

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;
        return $this;
    }

    #[ORM\Column(length: 255, options: ["default" => "https://i.ibb.co/S5rbLng/default-profile.jpg"])]
    private ?string $image_url = "https://i.ibb.co/S5rbLng/default-profile.jpg";

    public function getImage_url(): ?string
    {
        return $this->image_url;
    }

    public function setImage_url(string $image_url): self
    {
        $this->image_url = $image_url;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: false, options: ["default" => false])]
    private ?bool $is_verified = false;

    public function is_verified(): ?bool
    {
        return $this->is_verified;
    }

    public function setIs_verified(bool $is_verified): self
    {
        $this->is_verified = $is_verified;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Department::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'department', referencedColumnName: 'id')]
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

    #[ORM\OneToMany(targetEntity: Application::class, mappedBy: 'user')]
    private Collection $applications;

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

    #[ORM\OneToMany(targetEntity: Attendance::class, mappedBy: 'user')]
    private Collection $attendances;

    /**
     * @return Collection<int, Attendance>
     */
    public function getAttendances(): Collection
    {
        if (!$this->attendances instanceof Collection) {
            $this->attendances = new ArrayCollection();
        }
        return $this->attendances;
    }

    public function addAttendance(Attendance $attendance): self
    {
        if (!$this->getAttendances()->contains($attendance)) {
            $this->getAttendances()->add($attendance);
        }
        return $this;
    }

    public function removeAttendance(Attendance $attendance): self
    {
        $this->getAttendances()->removeElement($attendance);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Conge::class, mappedBy: 'user')]
    private Collection $conges;

    /**
     * @return Collection<int, Conge>
     */
    public function getConges(): Collection
    {
        if (!$this->conges instanceof Collection) {
            $this->conges = new ArrayCollection();
        }
        return $this->conges;
    }

    public function addConge(Conge $conge): self
    {
        if (!$this->getConges()->contains($conge)) {
            $this->getConges()->add($conge);
        }
        return $this;
    }

    public function removeConge(Conge $conge): self
    {
        $this->getConges()->removeElement($conge);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Department::class, mappedBy: 'user')]
    private Collection $departments;

    /**
     * @return Collection<int, Department>
     */
    public function getDepartments(): Collection
    {
        if (!$this->departments instanceof Collection) {
            $this->departments = new ArrayCollection();
        }
        return $this->departments;
    }

    public function addDepartment(Department $department): self
    {
        if (!$this->getDepartments()->contains($department)) {
            $this->getDepartments()->add($department);
        }
        return $this;
    }

    public function removeDepartment(Department $department): self
    {
        $this->getDepartments()->removeElement($department);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'user')]
    private Collection $events;

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        if (!$this->events instanceof Collection) {
            $this->events = new ArrayCollection();
        }
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->getEvents()->contains($event)) {
            $this->getEvents()->add($event);
        }
        return $this;
    }

    public function removeEvent(Event $event): self
    {
        $this->getEvents()->removeElement($event);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Formation::class, mappedBy: 'user')]
    private Collection $formations;

    /**
     * @return Collection<int, Formation>
     */
    public function getFormations(): Collection
    {
        if (!$this->formations instanceof Collection) {
            $this->formations = new ArrayCollection();
        }
        return $this->formations;
    }

    public function addFormation(Formation $formation): self
    {
        if (!$this->getFormations()->contains($formation)) {
            $this->getFormations()->add($formation);
        }
        return $this;
    }

    public function removeFormation(Formation $formation): self
    {
        $this->getFormations()->removeElement($formation);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'user')]
    private Collection $inscriptions;

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

    #[ORM\OneToMany(targetEntity: Interview::class, mappedBy: 'user')]
    private Collection $interviews;

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

    #[ORM\OneToMany(targetEntity: JobOffer::class, mappedBy: 'user')]
    private Collection $jobOffers;

    /**
     * @return Collection<int, JobOffer>
     */
    public function getJobOffers(): Collection
    {
        if (!$this->jobOffers instanceof Collection) {
            $this->jobOffers = new ArrayCollection();
        }
        return $this->jobOffers;
    }

    public function addJobOffer(JobOffer $jobOffer): self
    {
        if (!$this->getJobOffers()->contains($jobOffer)) {
            $this->getJobOffers()->add($jobOffer);
        }
        return $this;
    }

    public function removeJobOffer(JobOffer $jobOffer): self
    {
        $this->getJobOffers()->removeElement($jobOffer);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Reclamation::class, mappedBy: 'responsable')]
    private Collection $reclamationsResponsable;

    /**
     * @return Collection<int, Reclamation>
     */
    public function getReclamationsResponsable(): Collection
    {
        if (!$this->reclamationsResponsable instanceof Collection) {
            $this->reclamationsResponsable = new ArrayCollection();
        }
        return $this->reclamationsResponsable;
    }

    public function addReclamationsResponsable(Reclamation $reclamationsResponsable): self
    {
        if (!$this->getReclamationsResponsable()->contains($reclamationsResponsable)) {
            $this->getReclamationsResponsable()->add($reclamationsResponsable);
        }
        return $this;
    }

    public function removeReclamationsResponsable(Reclamation $reclamationsResponsable): self
    {
        $this->getReclamationsResponsable()->removeElement($reclamationsResponsable);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Reclamation::class, mappedBy: 'user')]
    private Collection $reclamations;

    /**
     * @return Collection<int, Reclamation>
     */
    public function getReclamations(): Collection
    {
        if (!$this->reclamations instanceof Collection) {
            $this->reclamations = new ArrayCollection();
        }
        return $this->reclamations;
    }

    public function addReclamation(Reclamation $reclamation): self
    {
        if (!$this->getReclamations()->contains($reclamation)) {
            $this->getReclamations()->add($reclamation);
        }
        return $this;
    }

    public function removeReclamation(Reclamation $reclamation): self
    {
        $this->getReclamations()->removeElement($reclamation);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'user')]
    private Collection $reservations;

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

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'user')]
    private Collection $tasks;

    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->attendances = new ArrayCollection();
        $this->conges = new ArrayCollection();
        $this->departments = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->formations = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
        $this->interviews = new ArrayCollection();
        $this->jobOffers = new ArrayCollection();
        $this->reclamationsResponsable = new ArrayCollection();
        $this->reclamations = new ArrayCollection();
        $this->reservations = new ArrayCollection();
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

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(string $image_url): static
    {
        $this->image_url = $image_url;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->is_verified;
    }

    public function setIsVerified(bool $is_verified): static
    {
        $this->is_verified = $is_verified;

        return $this;
    }

}
