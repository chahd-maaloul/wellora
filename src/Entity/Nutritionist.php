<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\NutritionistRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NutritionistRepository::class)]
class Nutritionist extends User
{
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $rating = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lot = null;

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

    public function getDiscriminatorValue(): string
    {
        return UserRole::NUTRITIONIST->value;
    }
}
