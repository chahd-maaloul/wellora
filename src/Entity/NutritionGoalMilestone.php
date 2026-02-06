<?php

namespace App\Entity;

use App\Repository\NutritionGoalMilestoneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NutritionGoalMilestoneRepository::class)]
#[ORM\Table(name: 'nutrition_goal_milestones')]
class NutritionGoalMilestone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: NutritionGoal::class, inversedBy: 'milestones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?NutritionGoal $nutritionGoal = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * Milestone type: WEIGHT, MEASUREMENT, MACRO, HABIT, HEALTH_MARKER
     */
    #[ORM\Column(length: 30)]
    private ?string $milestoneType = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetValue = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $unit = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $targetDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $completedDate = null;

    #[ORM\Column]
    private ?bool $isCompleted = false;

    #[ORM\Column(nullable: true)]
    private ?int $order = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $progressPercentage = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public const TYPE_WEIGHT = 'WEIGHT';
    public const TYPE_MEASUREMENT = 'MEASUREMENT';
    public const TYPE_MACRO = 'MACRO';
    public const TYPE_HABIT = 'HABIT';
    public const TYPE_HEALTH_MARKER = 'HEALTH_MARKER';

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->isCompleted = false;
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

    public function setMilestoneType(string $milestoneType): static
    {
        $this->milestoneType = $milestoneType;
        return $this;
    }

    public function getTargetValue(): ?float
    {
        return $this->targetValue;
    }

    public function setTargetValue(?float $targetValue): static
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

    public function getTargetDate(): ?\DateTimeInterface
    {
        return $this->targetDate;
    }

    public function setTargetDate(?\DateTimeInterface $targetDate): static
    {
        $this->targetDate = $targetDate;
        return $this;
    }

    public function getCompletedDate(): ?\DateTimeInterface
    {
        return $this->completedDate;
    }

    public function setCompletedDate(?\DateTimeInterface $completedDate): static
    {
        $this->completedDate = $completedDate;
        return $this;
    }

    public function isCompleted(): ?bool
    {
        return $this->isCompleted;
    }

    public function setCompleted(bool $isCompleted): static
    {
        $this->isCompleted = $isCompleted;
        if ($isCompleted && !$this->completedDate) {
            $this->completedDate = new \DateTime();
        }
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

    public function getProgressPercentage(): ?string
    {
        return $this->progressPercentage;
    }

    public function setProgressPercentage(?string $progressPercentage): static
    {
        $this->progressPercentage = $progressPercentage;
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
     * Get milestone type display name
     */
    public function getMilestoneTypeDisplayName(): string
    {
        $types = [
            self::TYPE_WEIGHT => 'Poids',
            self::TYPE_MEASUREMENT => 'Mensurations',
            self::TYPE_MACRO => 'Macronutriments',
            self::TYPE_HABIT => 'Habit',
            self::TYPE_HEALTH_MARKER => 'Marqueur de santé',
        ];

        return $types[$this->milestoneType] ?? $this->milestoneType;
    }

    /**
     * Check if milestone is overdue
     */
    public function isOverdue(): bool
    {
        if ($this->isCompleted || !$this->targetDate) {
            return false;
        }

        return new \DateTime() > $this->targetDate;
    }

    /**
     * Get days remaining until target date
     */
    public function getDaysRemaining(): ?int
    {
        if (!$this->targetDate) {
            return null;
        }

        $diff = (new \DateTime())->diff($this->targetDate);
        return $diff->invert ? 0 : $diff->days;
    }
}
