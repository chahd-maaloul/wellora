<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\CoachRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoachRepository::class)]
class Coach extends User
{
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lot = null;

    public function getLot(): ?string
    {
        return $this->lot;
    }

    public function setLot(?string $lot): self
    {
        $this->lot = $lot;
        return $this;
    }

    public function getDiscriminatorValue(): string
    {
        return UserRole::COACH->value;
    }
}
