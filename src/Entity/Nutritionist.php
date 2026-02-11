<?php

namespace App\Entity;

use App\Repository\NutritionistRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NutritionistRepository::class)]
#[ORM\Table(name: 'nutritionist')]
class Nutritionist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $nomNutritioniste;

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomNutritioniste(): string
    {
        return $this->nomNutritioniste;
    }

    public function setNomNutritioniste(string $nomNutritioniste): self
    {
        $this->nomNutritioniste = $nomNutritioniste;
        return $this;
    }
}
