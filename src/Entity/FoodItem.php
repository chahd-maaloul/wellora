<?php

namespace App\Entity;

use App\Repository\FoodItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FoodItemRepository::class)]
#[ORM\Table(name: 'food_item')]
class FoodItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $nomItem;

    #[ORM\Column(type: 'integer')]
    private int $calories;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $protein;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $fat;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $carbs;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $loggedAt = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $mealType = null;

    #[ORM\ManyToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: true)]
    private ?Patient $patient = null;

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomItem(): string
    {
        return $this->nomItem;
    }

    public function setNomItem(string $nomItem): self
    {
        $this->nomItem = $nomItem;
        return $this;
    }

    public function getCalories(): int
    {
        return $this->calories;
    }

    public function setCalories(int $calories): self
    {
        $this->calories = $calories;
        return $this;
    }

    public function getProtein(): string
    {
        return $this->protein;
    }

    public function setProtein(string $protein): self
    {
        $this->protein = $protein;
        return $this;
    }

    public function getFat(): string
    {
        return $this->fat;
    }

    public function setFat(string $fat): self
    {
        $this->fat = $fat;
        return $this;
    }

    public function getCarbs(): string
    {
        return $this->carbs;
    }

    public function setCarbs(string $carbs): self
    {
        $this->carbs = $carbs;
        return $this;
    }

    public function getLoggedAt(): ?\DateTimeInterface
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(?\DateTimeInterface $loggedAt): self
    {
        $this->loggedAt = $loggedAt;
        return $this;
    }

    public function getMealType(): ?string
    {
        return $this->mealType;
    }

    public function setMealType(?string $mealType): self
    {
        $this->mealType = $mealType;
        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;
        return $this;
    }
}
