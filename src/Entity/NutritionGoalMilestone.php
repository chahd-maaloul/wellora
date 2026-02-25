<?php

namespace App\Entity;

use App\Repository\NutritionGoalMilestoneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NutritionGoalMilestoneRepository::class)]
#[ORM\Table(name: 'nutrition_goal_milestones')]
class NutritionGoalMilestone
{
    public const TYPE_WEIGHT = 'WEIGHT';
    public const TYPE_CALORIES = 'CALORIES';
    public const TYPE_PROTEIN = 'PROTEIN';
    public const TYPE_MEAL_FREQUENCY = 'MEAL_FREQUENCY';
    public const TYPE_WATER_INTAKE = 'WATER_INTAKE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: NutritionGoal::class, inversedBy: 'milestones')]
    #[ORM\JoinColumn(name: 'goal_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?NutritionGoal $goal = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $milestoneType = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $targetWeight = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $targetValue = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $unit = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $targetDays = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $targetCalories = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $targetDate = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $completed = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $completedAt = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $order = null;

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

    /**
     * Alias for getGoal for compatibility
     */
    public function getNutritionGoal(): ?NutritionGoal
    {
        return $this->getGoal();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getMilestoneType(): ?string
    {
        return $this->milestoneType;
    }

    public function setMilestoneType(?string $milestoneType): static
    {
        $this->milestoneType = $milestoneType;
        return $this;
    }

    public function getTargetWeight(): ?string
    {
        return $this->targetWeight;
    }

    public function setTargetWeight(?string $targetWeight): static
    {
        $this->targetWeight = $targetWeight;
        return $this;
    }

    public function getTargetValue(): ?string
    {
        return $this->targetValue;
    }

    public function setTargetValue(?string $targetValue): static
    {
        $this->targetValue = $targetValue;
        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): static
    {
        $this->unit = $unit;
        return $this;
    }

    public function getTargetDays(): ?int
    {
        return $this->targetDays;
    }

    public function setTargetDays(?int $targetDays): static
    {
        $this->targetDays = $targetDays;
        return $this;
    }

    public function getTargetCalories(): ?int
    {
        return $this->targetCalories;
    }

    public function setTargetCalories(?int $targetCalories): static
    {
        $this->targetCalories = $targetCalories;
        return $this;
    }

    public function getTargetDate(): ?\DateTimeInterface
    {
        return $this->targetDate;
    }

    public function setTargetDate(?\DateTimeInterface $targetDate): static
    {
        $this->targetDate = $targetDate;
        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): static
    {
        $this->completed = $completed;
        if ($completed && !$this->completedAt) {
            $this->completedAt = new \DateTime();
        }
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(?int $order): static
    {
        $this->order = $order;
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
