<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'role', type: 'string')]
#[ORM\DiscriminatorMap([
    'ROLE_PATIENT' => Patient::class,
    'ROLE_MEDECIN' => Medecin::class,
    'ROLE_COACH' => Coach::class,
    'ROLE_NUTRITIONIST' => Nutritionist::class,
    'ROLE_ADMIN' => Administrator::class,
])]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse email est déjà utilisée')]
#[UniqueEntity(fields: ['licenseNumber'], message: 'Ce numéro de licence est déjà utilisé', groups: ['Professional'])]
abstract class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, length: 36)]
    private ?string $uuid = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'Veuillez entrer une adresse email valide')]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le prénom doit contenir au moins 2 caractères')]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le nom doit contenir au moins 2 caractères')]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: '/^[+]?[0-9\s\-()]+$/', message: 'Le numero de telephone doit contenir uniquement des chiffres et les caracteres + - ( )')]
    private ?string $phone = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: 'L\'avatar doit être une URL valide')]
    private ?string $avatarUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'Le numéro de licence est obligatoire', groups: ['Professional'])]
    private ?string $licenseNumber = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $resetTokenExpiresAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLoginAt = null;

    #[ORM\Column]
    private int $loginAttempts = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lockedUntil = null;

    #[ORM\Column]
    private bool $isEmailVerified = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailVerificationToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $emailVerificationExpiresAt = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $lastSessionId = null;

    // Professional properties shared across professional users
    #[ORM\Column(name: 'years_of_experience')]
    #[Assert\PositiveOrZero(message: 'Les années d\'expérience doivent être positives')]
    private int $yearsOfExperience = 0;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: 'L\'URL du diplôme doit être valide')]
    private ?string $diplomaUrl = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $specialite = null;

    #[ORM\Column]
    private bool $isVerifiedByAdmin = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $verificationDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $about = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $education = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $certifications = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hospitalAffiliations = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $awards = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $specializations = [];

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $consultationPrice = 120;

    /**
     * @var Collection<int, Consultation>
     */
    #[ORM\OneToMany(targetEntity: Consultation::class, mappedBy: 'medecin')]
    private Collection $consultations;

    /**
     * @var Collection<int, Ordonnance>
     */
    #[ORM\OneToMany(targetEntity: Ordonnance::class, mappedBy: 'prescribedBy')]
    private Collection $ordonnances;

    /**
     * @var Collection<int, Examens>
     */
    #[ORM\OneToMany(targetEntity: Examens::class, mappedBy: 'prescribedBy')]
    private Collection $examens;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->uuid = Uuid::v4()->toRfc4122();
        $this->consultations = new ArrayCollection();
        $this->ordonnances = new ArrayCollection();
        $this->examens = new ArrayCollection();
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * Returns the user identifier (UUID).
     * Alias for getUuid() for compatibility.
     */
    public function getId(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): self
    {
        $this->avatarUrl = $avatarUrl;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setLicenseNumber(?string $licenseNumber): self
    {
        $this->licenseNumber = $licenseNumber;
        return $this;
    }

    public function isIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getResetTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->resetTokenExpiresAt;
    }

    public function setResetTokenExpiresAt(?\DateTimeInterface $resetTokenExpiresAt): self
    {
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;
        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeInterface
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;
        return $this;
    }

    public function getLoginAttempts(): int
    {
        return $this->loginAttempts;
    }

    public function setLoginAttempts(int $loginAttempts): self
    {
        $this->loginAttempts = $loginAttempts;
        return $this;
    }

    public function incrementLoginAttempts(): self
    {
        $this->loginAttempts++;
        return $this;
    }

    public function resetLoginAttempts(): self
    {
        $this->loginAttempts = 0;
        return $this;
    }

    public function getLockedUntil(): ?\DateTimeInterface
    {
        return $this->lockedUntil;
    }

    public function setLockedUntil(?\DateTimeInterface $lockedUntil): self
    {
        $this->lockedUntil = $lockedUntil;
        return $this;
    }

    public function isLocked(): bool
    {
        return $this->lockedUntil !== null && $this->lockedUntil > new \DateTime();
    }

    public function isEmailVerified(): bool
    {
        return $this->isEmailVerified;
    }

    public function setIsEmailVerified(bool $isEmailVerified): self
    {
        $this->isEmailVerified = $isEmailVerified;
        return $this;
    }

    public function getEmailVerificationToken(): ?string
    {
        return $this->emailVerificationToken;
    }

    public function setEmailVerificationToken(?string $emailVerificationToken): self
    {
        $this->emailVerificationToken = $emailVerificationToken;
        return $this;
    }

    public function getEmailVerificationExpiresAt(): ?\DateTimeInterface
    {
        return $this->emailVerificationExpiresAt;
    }

    public function setEmailVerificationExpiresAt(?\DateTimeInterface $emailVerificationExpiresAt): self
    {
        $this->emailVerificationExpiresAt = $emailVerificationExpiresAt;
        return $this;
    }

    public function getYearsOfExperience(): int
    {
        return $this->yearsOfExperience;
    }

    public function setYearsOfExperience(int $yearsOfExperience): self
    {
        $this->yearsOfExperience = $yearsOfExperience;
        return $this;
    }

    public function getDiplomaUrl(): ?string
    {
        return $this->diplomaUrl;
    }

    public function setDiplomaUrl(?string $diplomaUrl): self
    {
        $this->diplomaUrl = $diplomaUrl;
        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(?string $specialite): self
    {
        $this->specialite = $specialite;
        return $this;
    }

    public function isVerifiedByAdmin(): bool
    {
        return $this->isVerifiedByAdmin;
    }

    public function setVerifiedByAdmin(bool $isVerifiedByAdmin): self
    {
        $this->isVerifiedByAdmin = $isVerifiedByAdmin;
        return $this;
    }

    public function getVerificationDate(): ?\DateTimeInterface
    {
        return $this->verificationDate;
    }

    public function setVerificationDate(?\DateTimeInterface $verificationDate): self
    {
        $this->verificationDate = $verificationDate;
        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $about): self
    {
        $this->about = $about;
        return $this;
    }

    public function getEducation(): ?string
    {
        return $this->education;
    }

    public function setEducation(?string $education): self
    {
        $this->education = $education;
        return $this;
    }

    public function getCertifications(): ?string
    {
        return $this->certifications;
    }

    public function setCertifications(?string $certifications): self
    {
        $this->certifications = $certifications;
        return $this;
    }

    public function getHospitalAffiliations(): ?string
    {
        return $this->hospitalAffiliations;
    }

    public function setHospitalAffiliations(?string $hospitalAffiliations): self
    {
        $this->hospitalAffiliations = $hospitalAffiliations;
        return $this;
    }

    public function getAwards(): ?string
    {
        return $this->awards;
    }

    public function setAwards(?string $awards): self
    {
        $this->awards = $awards;
        return $this;
    }

    public function getSpecializations(): ?array
    {
        return $this->specializations;
    }

    public function setSpecializations(?array $specializations): self
    {
        $this->specializations = $specializations;
        return $this;
    }

    public function getConsultationPrice(): ?int
    {
        return $this->consultationPrice;
    }

    public function setConsultationPrice(?int $consultationPrice): self
    {
        $this->consultationPrice = $consultationPrice;
        return $this;
    }

    public function getLastSessionId(): ?string
    {
        return $this->lastSessionId;
    }

    public function setLastSessionId(?string $lastSessionId): self
    {
        $this->lastSessionId = $lastSessionId;
        return $this;
    }

    /**
     * @return Collection<int, Consultation>
     */
    public function getConsultations(): Collection
    {
        return $this->consultations;
    }

    public function addConsultation(Consultation $consultation): self
    {
        if (!$this->consultations->contains($consultation)) {
            $this->consultations->add($consultation);
            $consultation->setMedecin($this);
        }

        return $this;
    }

    public function removeConsultation(Consultation $consultation): self
    {
        if ($this->consultations->removeElement($consultation)) {
            if ($consultation->getMedecin() === $this) {
                $consultation->setMedecin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ordonnance>
     */
    public function getOrdonnances(): Collection
    {
        return $this->ordonnances;
    }

    public function addOrdonnance(Ordonnance $ordonnance): self
    {
        if (!$this->ordonnances->contains($ordonnance)) {
            $this->ordonnances->add($ordonnance);
            $ordonnance->setPrescribedBy($this);
        }

        return $this;
    }

    public function removeOrdonnance(Ordonnance $ordonnance): self
    {
        if ($this->ordonnances->removeElement($ordonnance)) {
            if ($ordonnance->getPrescribedBy() === $this) {
                $ordonnance->setPrescribedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Examens>
     */
    public function getExamens(): Collection
    {
        return $this->examens;
    }

    public function addExamen(Examens $examen): self
    {
        if (!$this->examens->contains($examen)) {
            $this->examens->add($examen);
            $examen->setPrescribedBy($this);
        }

        return $this;
    }

    public function removeExamen(Examens $examen): self
    {
        if ($this->examens->removeElement($examen)) {
            if ($examen->getPrescribedBy() === $this) {
                $examen->setPrescribedBy(null);
            }
        }

        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // Symfony Security Interface Methods
    public function getRoles(): array
    {
        return [$this->getDiscriminatorValue() ?? 'ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // Clear any temporary sensitive data
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    abstract public function getDiscriminatorValue(): string;
}
