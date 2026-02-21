<?php

namespace App\Entity;

use App\Repository\NutritionGoalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NutritionGoalRepository::class)]
#[ORM\Table(name: 'nutrition_goals')]
class NutritionGoal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $userId = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $caloriesTarget = 2000;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $waterTarget = 8;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $proteinTarget = 120;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $carbsTarget = 200;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $fatsTarget = 65;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $fiberTarget = 25;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $sugarTarget = 25;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $sodiumTarget = 2300;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $weightTarget = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $currentWeight = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $startWeight = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $activityLevel = 'moderate';

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getCaloriesTarget(): ?int
    {
        return $this->caloriesTarget;
    }

    public function setCaloriesTarget(?int $caloriesTarget): static
    {
        $this->caloriesTarget = $caloriesTarget;

        return $this;
    }

    public function getWaterTarget(): ?int
    {
        return $this->waterTarget;
    }

    public function setWaterTarget(?int $waterTarget): static
    {
        $this->waterTarget = $waterTarget;

        return $this;
    }

    public function getProteinTarget(): ?int
    {
        return $this->proteinTarget;
    }

    public function setProteinTarget(?int $proteinTarget): static
    {
        $this->proteinTarget = $proteinTarget;

        return $this;
    }

    public function getCarbsTarget(): ?int
    {
        return $this->carbsTarget;
    }

    public function setCarbsTarget(?int $carbsTarget): static
    {
        $this->carbsTarget = $carbsTarget;

        return $this;
    }

    public function getFatsTarget(): ?int
    {
        return $this->fatsTarget;
    }

    public function setFatsTarget(?int $fatsTarget): static
    {
        $this->fatsTarget = $fatsTarget;

        return $this;
    }

    public function getFiberTarget(): ?int
    {
        return $this->fiberTarget;
    }

    public function setFiberTarget(?int $fiberTarget): static
    {
        $this->fiberTarget = $fiberTarget;

        return $this;
    }

    public function getSugarTarget(): ?int
    {
        return $this->sugarTarget;
    }

    public function setSugarTarget(?int $sugarTarget): static
    {
        $this->sugarTarget = $sugarTarget;

        return $this;
    }

    public function getSodiumTarget(): ?int
    {
        return $this->sodiumTarget;
    }

    public function setSodiumTarget(?int $sodiumTarget): static
    {
        $this->sodiumTarget = $sodiumTarget;

        return $this;
    }

    public function getWeightTarget(): ?string
    {
        return $this->weightTarget;
    }

    public function setWeightTarget(?string $weightTarget): static
    {
        $this->weightTarget = $weightTarget;

        return $this;
    }

    public function getCurrentWeight(): ?string
    {
        return $this->currentWeight;
    }

    public function setCurrentWeight(?string $currentWeight): static
    {
        $this->currentWeight = $currentWeight;

        return $this;
    }

    public function getStartWeight(): ?string
    {
        return $this->startWeight;
    }

    public function setStartWeight(?string $startWeight): static
    {
        $this->startWeight = $startWeight;

        return $this;
    }

    public function getActivityLevel(): ?string
    {
        return $this->activityLevel;
    }

    public function setActivityLevel(?string $activityLevel): static
    {
        $this->activityLevel = $activityLevel;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
