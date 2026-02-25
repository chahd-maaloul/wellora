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

    #[ORM\ManyToOne(targetEntity: NutritionGoal::class, inversedBy: 'progress')]
    #[ORM\JoinColumn(name: 'goal_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?NutritionGoal $goal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $weight = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $caloriesConsumed = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $caloriesBurned = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $proteinConsumed = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $carbsConsumed = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $fatsConsumed = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $waterIntake = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $steps = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $adherence = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGoal(): ?NutritionGoal
    {
        return $this->goal;
    }

    public function setGoal(?NutritionGoal $goal): static
    {
        $this->goal = $goal;
        return $this;
    }

    /**
     * Alias for setGoal for compatibility
     */
    public function setNutritionGoal(?NutritionGoal $goal): static
    {
        return $this->setGoal($goal);
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

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(?string $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    public function getCaloriesConsumed(): ?int
    {
        return $this->caloriesConsumed;
    }

    public function setCaloriesConsumed(?int $caloriesConsumed): static
    {
        $this->caloriesConsumed = $caloriesConsumed;
        return $this;
    }

    public function getCaloriesBurned(): ?int
    {
        return $this->caloriesBurned;
    }

    public function setCaloriesBurned(?int $caloriesBurned): static
    {
        $this->caloriesBurned = $caloriesBurned;
        return $this;
    }

    public function getProteinConsumed(): ?int
    {
        return $this->proteinConsumed;
    }

    public function setProteinConsumed(?int $proteinConsumed): static
    {
        $this->proteinConsumed = $proteinConsumed;
        return $this;
    }

    public function getCarbsConsumed(): ?int
    {
        return $this->carbsConsumed;
    }

    public function setCarbsConsumed(?int $carbsConsumed): static
    {
        $this->carbsConsumed = $carbsConsumed;
        return $this;
    }

    public function getFatsConsumed(): ?int
    {
        return $this->fatsConsumed;
    }

    public function setFatsConsumed(?int $fatsConsumed): static
    {
        $this->fatsConsumed = $fatsConsumed;
        return $this;
    }

    public function getWaterIntake(): ?int
    {
        return $this->waterIntake;
    }

    public function setWaterIntake(?int $waterIntake): static
    {
        $this->waterIntake = $waterIntake;
        return $this;
    }

    public function getSteps(): ?int
    {
        return $this->steps;
    }

    public function setSteps(?int $steps): static
    {
        $this->steps = $steps;
        return $this;
    }

    public function getAdherence(): ?int
    {
        return $this->adherence;
    }

    public function setAdherence(?int $adherence): static
    {
        $this->adherence = $adherence;
        return $this;
    }

    public function setAdherenceScore(?int $adherenceScore): static
    {
        $this->adherence = $adherenceScore;
        return $this;
    }

    public function setBodyFat(?float $bodyFat): static
    {
        // Body fat would need a separate field, storing in notes for now
        $this->notes = ($this->notes ? $this->notes . ' | ' : '') . 'Body Fat: ' . $bodyFat . '%';
        return $this;
    }

    public function setGoalsMet(?bool $goalsMet): static
    {
        // This could be stored as a JSON field or separate field
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

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
