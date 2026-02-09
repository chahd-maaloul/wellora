<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\PatientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PatientRepository::class)]
class Patient extends User
{
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lot = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $token = null;

    public function getLot(): ?string
    {
        return $this->lot;
    }

    public function setLot(?string $lot): self
    {
        $this->lot = $lot;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getDiscriminatorValue(): string
    {
        return UserRole::PATIENT->value;
    }
}
