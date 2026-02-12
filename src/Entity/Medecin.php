<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\MedecinRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MedecinRepository::class)]
class Medecin extends User
{
    public const SPECIALTY_CARDIOLOGY = 'CARDIOLOGY';
    public const SPECIALTY_DERMATOLOGY = 'DERMATOLOGY';
    public const SPECIALTY_ENDOCRINOLOGY = 'ENDOCRINOLOGY';
    public const SPECIALTY_NEUROLOGY = 'NEUROLOGY';
    public const SPECIALTY_PSYCHIATRY = 'PSYCHIATRY';
    public const SPECIALTY_PHYSIOTHERAPY = 'PHYSIOTHERAPY';
    public const SPECIALTY_PEDIATRICS = 'PEDIATRICS';
    public const SPECIALTY_GYNECOLOGY = 'GYNECOLOGY';
    public const SPECIALTY_OPHTHALMOLOGY = 'OPHTHALMOLOGY';
    public const SPECIALTY_OTHER = 'OTHER';

    public const MEDICAL_SPECIALTIES = [
        self::SPECIALTY_CARDIOLOGY => 'Cardiologie',
        self::SPECIALTY_DERMATOLOGY => 'Dermatologie',
        self::SPECIALTY_ENDOCRINOLOGY => 'Endocrinologie',
        self::SPECIALTY_NEUROLOGY => 'Neurologie',
        self::SPECIALTY_PSYCHIATRY => 'Psychiatrie',
        self::SPECIALTY_PHYSIOTHERAPY => 'Physiothérapie',
        self::SPECIALTY_PEDIATRICS => 'Pédiatrie',
        self::SPECIALTY_GYNECOLOGY => 'Gynécologie',
        self::SPECIALTY_OPHTHALMOLOGY => 'Ophtalmologie',
        self::SPECIALTY_OTHER => 'Autre',
    ];

    #[ORM\Column(type: Types::STRING, length: 50)]
    private ?string $specialite = null;

    #[ORM\Column(name: 'years_of_experience')]
    #[Assert\PositiveOrZero(message: 'Les années d\'expérience doivent être positives')]
    private int $yearsOfExperience = 0;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: 'L\'URL du diplôme doit être valide')]
    private ?string $diplomaUrl = null;

    #[ORM\Column]
    private bool $isVerifiedByAdmin = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $verificationDate = null;

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(?string $specialite): self
    {
        $this->specialite = $specialite;
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

    public function getDiscriminatorValue(): string
    {
        return UserRole::MEDECIN->value;
    }
}
