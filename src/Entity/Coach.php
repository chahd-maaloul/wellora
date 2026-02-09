<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\CoachRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoachRepository::class)]
class Coach extends User
{
    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le numéro de licence est obligatoire')]
    private ?string $licenseNumber = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(name: 'years_of_experience')]
    #[Assert\PositiveOrZero(message: 'Les années d\'expérience doivent être positives')]
    private int $yearsOfExperience = 0;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: 'L\'URL du diplôme doit être valide')]
    private ?string $diplomaUrl = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $specialite = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lot = null;

    #[ORM\Column]
    private bool $isVerifiedByAdmin = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $verificationDate = null;

    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setLicenseNumber(string $licenseNumber): self
    {
        $this->licenseNumber = $licenseNumber;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
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

    public function getLot(): ?string
    {
        return $this->lot;
    }

    public function setLot(?string $lot): self
    {
        $this->lot = $lot;
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
        return UserRole::COACH->value;
    }
}
