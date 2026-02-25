<?php

namespace App\Entity;

use App\Repository\NutritionGoalAdjustmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NutritionGoalAdjustmentRepository::class)]
#[ORM\Table(name: 'nutrition_goal_adjustments')]
class NutritionGoalAdjustment
{
    public const TYPE_MANUAL = 'MANUAL';
    public const TYPE_AUTO = 'AUTO';
    public const TYPE_ALGORITHM = 'ALGORITHM';
    public const TYPE_MILESTONE = 'MILESTONE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: NutritionGoal::class, inversedBy: 'adjustments')]
    #[ORM\JoinColumn(name: 'goal_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?NutritionGoal $goal = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $adjustmentType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $previousCalories = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $newCalories = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $previousProtein = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $newProtein = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $previousCarbs = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $newCarbs = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $previousFats = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $newFats = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $recommendations = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $daysUntilNextReview = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $effectiveFrom = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isActive = true;

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

    public function getAdjustmentType(): ?string
    {
        return $this->adjustmentType;
    }

    public function setAdjustmentType(?string $adjustmentType): static
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

    public function getPreviousCalories(): ?int
    {
        return $this->previousCalories;
    }

    public function setPreviousCalories(?int $previousCalories): static
    {
        $this->previousCalories = $previousCalories;
        return $this;
    }

    public function getNewCalories(): ?int
    {
        return $this->newCalories;
    }

    public function setNewCalories(?int $newCalories): static
    {
        $this->newCalories = $newCalories;
        return $this;
    }

    public function getPreviousProtein(): ?int
    {
        return $this->previousProtein;
    }

    public function setPreviousProtein(?int $previousProtein): static
    {
        $this->previousProtein = $previousProtein;
        return $this;
    }

    public function getNewProtein(): ?int
    {
        return $this->newProtein;
    }

    public function setNewProtein(?int $newProtein): static
    {
        $this->newProtein = $newProtein;
        return $this;
    }

    public function getPreviousCarbs(): ?int
    {
        return $this->previousCarbs;
    }

    public function setPreviousCarbs(?int $previousCarbs): static
    {
        $this->previousCarbs = $previousCarbs;
        return $this;
    }

    public function getNewCarbs(): ?int
    {
        return $this->newCarbs;
    }

    public function setNewCarbs(?int $newCarbs): static
    {
        $this->newCarbs = $newCarbs;
        return $this;
    }

    public function getPreviousFats(): ?int
    {
        return $this->previousFats;
    }

    public function setPreviousFats(?int $previousFats): static
    {
        $this->previousFats = $previousFats;
        return $this;
    }

    public function getNewFats(): ?int
    {
        return $this->newFats;
    }

    public function setNewFats(?int $newFats): static
    {
        $this->newFats = $newFats;
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

    public function getDaysUntilNextReview(): ?int
    {
        return $this->daysUntilNextReview;
    }

    public function setDaysUntilNextReview(?int $daysUntilNextReview): static
    {
        $this->daysUntilNextReview = $daysUntilNextReview;
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

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
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
