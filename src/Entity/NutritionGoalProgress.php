<?php

namespace App\Entity;

use App\Repository\NutritionGoalProgressRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NutritionGoalProgressRepository::class)]
#[ORM\Table(name: 'nutrition_goal_progress')]
class NutritionGoalProgress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: NutritionGoal::class, inversedBy: 'progressRecords')]
    #[ORM\JoinColumn(nullable: false)]
    private ?NutritionGoal $nutritionGoal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(nullable: true)]
    private ?float $weight = null;

    #[ORM\Column(nullable: true)]
    private ?float $bodyFat = null;

    #[ORM\Column(nullable: true)]
    private ?float $waistCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $chestCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $hipCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $armCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $thighCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $caloriesConsumed = null;

    #[ORM\Column(nullable: true)]
    private ?float $proteinConsumed = null;

    #[ORM\Column(nullable: true)]
    private ?float $carbsConsumed = null;

    #[ORM\Column(nullable: true)]
    private ?float $fatsConsumed = null;

    #[ORM\Column(nullable: true)]
    private ?float $waterIntake = null;

    #[ORM\Column(nullable: true)]
    private ?float $fiberIntake = null;

    #[ORM\Column(nullable: true)]
    private ?float $sugarIntake = null;

    #[ORM\Column(nullable: true)]
    private ?float $sodiumIntake = null;

    #[ORM\Column(nullable: true)]
    private ?float $bloodGlucose = null;

    #[ORM\Column(nullable: true)]
    private ?float $bloodPressureSystolic = null;

    #[ORM\Column(nullable: true)]
    private ?float $bloodPressureDiastolic = null;

    #[ORM\Column(nullable: true)]
    private ?float $cholesterol = null;

    #[ORM\Column(nullable: true)]
    private ?int $mealsLogged = null;

    #[ORM\Column(nullable: true)]
    private ?int $mealsPrepped = null;

    #[ORM\Column(nullable: true)]
    private ?bool $goalsMet = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $adherenceScore = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNutritionGoal(): ?NutritionGoal
    {
        return $this->nutritionGoal;
    }

    public function setNutritionGoal(?NutritionGoal $nutritionGoal): static
    {
        $this->nutritionGoal = $nutritionGoal;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    public function getBodyFat(): ?float
    {
        return $this->bodyFat;
    }

    public function setBodyFat(?float $bodyFat): static
    {
        $this->bodyFat = $bodyFat;
        return $this;
    }

    public function getWaistCircumference(): ?float
    {
        return $this->waistCircumference;
    }

    public function setWaistCircumference(?float $waistCircumference): static
    {
        $this->waistCircumference = $waistCircumference;
        return $this;
    }

    public function getChestCircumference(): ?float
    {
        return $this->chestCircumference;
    }

    public function setChestCircumference(?float $chestCircumference): static
    {
        $this->chestCircumference = $chestCircumference;
        return $this;
    }

    public function getHipCircumference(): ?float
    {
        return $this->hipCircumference;
    }

    public function setHipCircumference(?float $hipCircumference): static
    {
        $this->hipCircumference = $hipCircumference;
        return $this;
    }

    public function getArmCircumference(): ?float
    {
        return $this->armCircumference;
    }

    public function setArmCircumference(?float $armCircumference): static
    {
        $this->armCircumference = $armCircumference;
        return $this;
    }

    public function getThighCircumference(): ?float
    {
        return $this->thighCircumference;
    }

    public function setThighCircumference(?float $thighCircumference): static
    {
        $this->thighCircumference = $thighCircumference;
        return $this;
    }

    public function getCaloriesConsumed(): ?float
    {
        return $this->caloriesConsumed;
    }

    public function setCaloriesConsumed(?float $caloriesConsumed): static
    {
        $this->caloriesConsumed = $caloriesConsumed;
        return $this;
    }

    public function getProteinConsumed(): ?float
    {
        return $this->proteinConsumed;
    }

    public function setProteinConsumed(?float $proteinConsumed): static
    {
        $this->proteinConsumed = $proteinConsumed;
        return $this;
    }

    public function getCarbsConsumed(): ?float
    {
        return $this->carbsConsumed;
    }

    public function setCarbsConsumed(?float $carbsConsumed): static
    {
        $this->carbsConsumed = $carbsConsumed;
        return $this;
    }

    public function getFatsConsumed(): ?float
    {
        return $this->fatsConsumed;
    }

    public function setFatsConsumed(?float $fatsConsumed): static
    {
        $this->fatsConsumed = $fatsConsumed;
        return $this;
    }

    public function getWaterIntake(): ?float
    {
        return $this->waterIntake;
    }

    public function setWaterIntake(?float $waterIntake): static
    {
        $this->waterIntake = $waterIntake;
        return $this;
    }

    public function getFiberIntake(): ?float
    {
        return $this->fiberIntake;
    }

    public function setFiberIntake(?float $fiberIntake): static
    {
        $this->fiberIntake = $fiberIntake;
        return $this;
    }

    public function getSugarIntake(): ?float
    {
        return $this->sugarIntake;
    }

    public function setSugarIntake(?float $sugarIntake): static
    {
        $this->sugarIntake = $sugarIntake;
        return $this;
    }

    public function getSodiumIntake(): ?float
    {
        return $this->sodiumIntake;
    }

    public function setSodiumIntake(?float $sodiumIntake): static
    {
        $this->sodiumIntake = $sodiumIntake;
        return $this;
    }

    public function getBloodGlucose(): ?float
    {
        return $this->bloodGlucose;
    }

    public function setBloodGlucose(?float $bloodGlucose): static
    {
        $this->bloodGlucose = $bloodGlucose;
        return $this;
    }

    public function getBloodPressureSystolic(): ?float
    {
        return $this->bloodPressureSystolic;
    }

    public function setBloodPressureSystolic(?float $bloodPressureSystolic): static
    {
        $this->bloodPressureSystolic = $bloodPressureSystolic;
        return $this;
    }

    public function getBloodPressureDiastolic(): ?float
    {
        return $this->bloodPressureDiastolic;
    }

    public function setBloodPressureDiastolic(?float $bloodPressureDiastolic): static
    {
        $this->bloodPressureDiastolic = $bloodPressureDiastolic;
        return $this;
    }

    public function getCholesterol(): ?float
    {
        return $this->cholesterol;
    }

    public function setCholesterol(?float $cholesterol): static
    {
        $this->cholesterol = $cholesterol;
        return $this;
    }

    public function getMealsLogged(): ?int
    {
        return $this->mealsLogged;
    }

    public function setMealsLogged(?int $mealsLogged): static
    {
        $this->mealsLogged = $mealsLogged;
        return $this;
    }

    public function getMealsPrepped(): ?int
    {
        return $this->mealsPrepped;
    }

    public function setMealsPrepped(?int $mealsPrepped): static
    {
        $this->mealsPrepped = $mealsPrepped;
        return $this;
    }

    public function isGoalsMet(): ?bool
    {
        return $this->goalsMet;
    }

    public function setGoalsMet(?bool $goalsMet): static
    {
        $this->goalsMet = $goalsMet;
        return $this;
    }

    public function getAdherenceScore(): ?string
    {
        return $this->adherenceScore;
    }

    public function setAdherenceScore(?string $adherenceScore): static
    {
        $this->adherenceScore = $adherenceScore;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Calculate total macros consumed
     */
    public function getTotalMacrosCalories(): float
    {
        $protein = $this->proteinConsumed ?? 0;
        $carbs = $this->carbsConsumed ?? 0;
        $fats = $this->fatsConsumed ?? 0;

        return ($protein * 4) + ($carbs * 4) + ($fats * 9);
    }

    /**
     * Get blood pressure reading as string
     */
    public function getBloodPressureReading(): string
    {
        if ($this->bloodPressureSystolic && $this->bloodPressureDiastolic) {
            return sprintf('%d/%d', $this->bloodPressureSystolic, $this->bloodPressureDiastolic);
        }
        return 'N/A';
    }
}
