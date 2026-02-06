<?php

namespace App\Entity;

use App\Repository\NutritionGoalAdjustmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NutritionGoalAdjustmentRepository::class)]
#[ORM\Table(name: 'nutrition_goal_adjustments')]
class NutritionGoalAdjustment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: NutritionGoal::class, inversedBy: 'adjustments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?NutritionGoal $nutritionGoal = null;

    /**
     * Adjustment type: AUTOMATIC, MANUAL, PLATEAU, MILESTONE, USER_REQUESTED
     */
    #[ORM\Column(length: 30)]
    private ?string $adjustmentType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(nullable: true)]
    private ?float $previousCalories = null;

    #[ORM\Column(nullable: true)]
    private ?float $newCalories = null;

    #[ORM\Column(nullable: true)]
    private ?float $previousProtein = null;

    #[ORM\Column(nullable: true)]
    private ?float $newProtein = null;

    #[ORM\Column(nullable: true)]
    private ?float $previousCarbs = null;

    #[ORM\Column(nullable: true)]
    private ?float $newCarbs = null;

    #[ORM\Column(nullable: true)]
    private ?float $previousFats = null;

    #[ORM\Column(nullable: true)]
    private ?float $newFats = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $adjustmentPercentage = null;

    #[ORM\Column(nullable: true)]
    private ?int $daysUntilNextReview = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $recommendations = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $adjustmentDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $effectiveFrom = null;

    #[ORM\Column]
    private ?bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public const TYPE_AUTOMATIC = 'AUTOMATIC';
    public const TYPE_MANUAL = 'MANUAL';
    public const TYPE_PLATEAU = 'PLATEAU';
    public const TYPE_MILESTONE = 'MILESTONE';
    public const TYPE_USER_REQUESTED = 'USER_REQUESTED';

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->adjustmentDate = new \DateTime();
        $this->isActive = true;
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

    public function getAdjustmentType(): ?string
    {
        return $this->adjustmentType;
    }

    public function setAdjustmentType(string $adjustmentType): static
    {
        $this->adjustmentType = $adjustmentType;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): static
    {
        $this->reason = $reason;
        return $this;
    }

    public function getPreviousCalories(): ?float
    {
        return $this->previousCalories;
    }

    public function setPreviousCalories(?float $previousCalories): static
    {
        $this->previousCalories = $previousCalories;
        return $this;
    }

    public function getNewCalories(): ?float
    {
        return $this->newCalories;
    }

    public function setNewCalories(?float $newCalories): static
    {
        $this->newCalories = $newCalories;
        return $this;
    }

    public function getPreviousProtein(): ?float
    {
        return $this->previousProtein;
    }

    public function setPreviousProtein(?float $previousProtein): static
    {
        $this->previousProtein = $previousProtein;
        return $this;
    }

    public function getNewProtein(): ?float
    {
        return $this->newProtein;
    }

    public function setNewProtein(?float $newProtein): static
    {
        $this->newProtein = $newProtein;
        return $this;
    }

    public function getPreviousCarbs(): ?float
    {
        return $this->previousCarbs;
    }

    public function setPreviousCarbs(?float $previousCarbs): static
    {
        $this->previousCarbs = $previousCarbs;
        return $this;
    }

    public function getNewCarbs(): ?float
    {
        return $this->newCarbs;
    }

    public function setNewCarbs(?float $newCarbs): static
    {
        $this->newCarbs = $newCarbs;
        return $this;
    }

    public function getPreviousFats(): ?float
    {
        return $this->previousFats;
    }

    public function setPreviousFats(?float $previousFats): static
    {
        $this->previousFats = $previousFats;
        return $this;
    }

    public function getNewFats(): ?float
    {
        return $this->newFats;
    }

    public function setNewFats(?float $newFats): static
    {
        $this->newFats = $newFats;
        return $this;
    }

    public function getAdjustmentPercentage(): ?string
    {
        return $this->adjustmentPercentage;
    }

    public function setAdjustmentPercentage(?string $adjustmentPercentage): static
    {
        $this->adjustmentPercentage = $adjustmentPercentage;
        return $this;
    }

    public function getDaysUntilNextReview(): ?int
    {
        return $this->daysUntilNextReview;
    }

    public function setDaysUntilNextReview(?int $daysUntilNextReview): static
    {
        $this->daysUntilNextReview = $daysUntilNextReview;
        return $this;
    }

    public function getRecommendations(): ?string
    {
        return $this->recommendations;
    }

    public function setRecommendations(?string $recommendations): static
    {
        $this->recommendations = $recommendations;
        return $this;
    }

    public function getAdjustmentDate(): ?\DateTimeInterface
    {
        return $this->adjustmentDate;
    }

    public function setAdjustmentDate(\DateTimeInterface $adjustmentDate): static
    {
        $this->adjustmentDate = $adjustmentDate;
        return $this;
    }

    public function getEffectiveFrom(): ?\DateTimeInterface
    {
        return $this->effectiveFrom;
    }

    public function setEffectiveFrom(?\DateTimeInterface $effectiveFrom): static
    {
        $this->effectiveFrom = $effectiveFrom;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;
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
     * Get adjustment type display name
     */
    public function getAdjustmentTypeDisplayName(): string
    {
        $types = [
            self::TYPE_AUTOMATIC => 'Automatique',
            self::TYPE_MANUAL => 'Manuel',
            self::TYPE_PLATEAU => 'Plateau détecté',
            self::TYPE_MILESTONE => 'Après jalon',
            self::TYPE_USER_REQUESTED => 'Demandé par l\'utilisateur',
        ];

        return $types[$this->adjustmentType] ?? $this->adjustmentType;
    }

    /**
     * Calculate net calorie change
     */
    public function getNetCalorieChange(): ?float
    {
        return $this->newCalories - $this->previousCalories;
    }

    /**
     * Get summary of changes
     */
    public function getChangesSummary(): array
    {
        return [
            'calories' => [
                'from' => $this->previousCalories,
                'to' => $this->newCalories,
                'change' => $this->getNetCalorieChange(),
            ],
            'protein' => [
                'from' => $this->previousProtein,
                'to' => $this->newProtein,
            ],
            'carbs' => [
                'from' => $this->previousCarbs,
                'to' => $this->newCarbs,
            ],
            'fats' => [
                'from' => $this->previousFats,
                'to' => $this->newFats,
            ],
        ];
    }
}
