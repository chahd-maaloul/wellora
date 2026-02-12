<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\NutritionistRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NutritionistRepository::class)]
class Nutritionist extends User
{
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

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $rating = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lot = null;

    #[ORM\Column]
    private bool $isVerifiedByAdmin = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $verificationDate = null;

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

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(?string $rating): self
    {
        $this->rating = $rating;
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
        return UserRole::NUTRITIONIST->value;
    }
}
