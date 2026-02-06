<?php

namespace App\Entity;

use App\Repository\NutritionGoalAchievementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NutritionGoalAchievementRepository::class)]
#[ORM\Table(name: 'nutrition_goal_achievements')]
class NutritionGoalAchievement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: NutritionGoal::class, inversedBy: 'achievements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?NutritionGoal $nutritionGoal = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * Achievement type: STREAK, MILESTONE, CONSISTENCY, GOAL_COMPLETED, SPECIAL
     */
    #[ORM\Column(length: 30)]
    private ?string $achievementType = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $tier = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $points = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $criteria = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $earnedAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isShared = false;

    #[ORM\Column(nullable: true)]
    private ?int $sharesCount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    public const TYPE_STREAK = 'STREAK';
    public const TYPE_MILESTONE = 'MILESTONE';
    public const TYPE_CONSISTENCY = 'CONSISTENCY';
    public const TYPE_GOAL_COMPLETED = 'GOAL_COMPLETED';
    public const TYPE_SPECIAL = 'SPECIAL';

    public const TIER_BRONZE = 'BRONZE';
    public const TIER_SILVER = 'SILVER';
    public const TIER_GOLD = 'GOLD';
    public const TIER_PLATINUM = 'PLATINUM';
    public const TIER_DIAMOND = 'DIAMOND';

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->earnedAt = new \DateTime();
        $this->isShared = false;
        $this->sharesCount = 0;
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

    public function getAchievementType(): ?string
    {
        return $this->achievementType;
    }

    public function setAchievementType(string $achievementType): static
    {
        $this->achievementType = $achievementType;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function getTier(): ?string
    {
        return $this->tier;
    }

    public function setTier(?string $tier): static
    {
        $this->tier = $tier;
        return $this;
    }

    public function getPoints(): ?string
    {
        return $this->points;
    }

    public function setPoints(?string $points): static
    {
        $this->points = $points;
        return $this;
    }

    public function getCriteria(): ?string
    {
        return $this->criteria;
    }

    public function setCriteria(?string $criteria): static
    {
        $this->criteria = $criteria;
        return $this;
    }

    public function getEarnedAt(): ?\DateTimeInterface
    {
        return $this->earnedAt;
    }

    public function setEarnedAt(\DateTimeInterface $earnedAt): static
    {
        $this->earnedAt = $earnedAt;
        return $this;
    }

    public function isShared(): ?bool
    {
        return $this->isShared;
    }

    public function setShared(bool $isShared): static
    {
        $this->isShared = $isShared;
        return $this;
    }

    public function getSharesCount(): ?int
    {
        return $this->sharesCount;
    }

    public function setSharesCount(?int $sharesCount): static
    {
        $this->sharesCount = $sharesCount;
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
     * Get achievement type display name
     */
    public function getAchievementTypeDisplayName(): string
    {
        $types = [
            self::TYPE_STREAK => 'Série',
            self::TYPE_MILESTONE => 'Jalon',
            self::TYPE_CONSISTENCY => 'Consistance',
            self::TYPE_GOAL_COMPLETED => 'Objectif atteint',
            self::TYPE_SPECIAL => 'Spécial',
        ];

        return $types[$this->achievementType] ?? $this->achievementType;
    }

    /**
     * Get tier display name
     */
    public function getTierDisplayName(): string
    {
        $tiers = [
            self::TIER_BRONZE => 'Bronze',
            self::TIER_SILVER => 'Argent',
            self::TIER_GOLD => 'Or',
            self::TIER_PLATINUM => 'Platine',
            self::TIER_DIAMOND => 'Diamant',
        ];

        return $tiers[$this->tier] ?? $this->tier;
    }

    /**
     * Get tier color
     */
    public function getTierColor(): string
    {
        $colors = [
            self::TIER_BRONZE => '#CD7F32',
            self::TIER_SILVER => '#C0C0C0',
            self::TIER_GOLD => '#FFD700',
            self::TIER_PLATINUM => '#E5E4E2',
            self::TIER_DIAMOND => '#B9F2FF',
        ];

        return $colors[$this->tier] ?? '#808080';
    }

    /**
     * Get tier icon class
     */
    public function getTierIconClass(): string
    {
        $classes = [
            self::TIER_BRONZE => 'fa-medal text-amber-600',
            self::TIER_SILVER => 'fa-medal text-gray-400',
            self::TIER_GOLD => 'fa-medal text-yellow-500',
            self::TIER_PLATINUM => 'fa-crown text-indigo-300',
            self::TIER_DIAMOND => 'fa-gem text-blue-300',
        ];

        return $classes[$this->tier] ?? 'fa-award';
    }

    /**
     * Get formatted earned date
     */
    public function getFormattedEarnedDate(): string
    {
        return $this->earnedAt->format('d/m/Y H:i');
    }

    /**
     * Check if achievement can be shared
     */
    public function canShare(): bool
    {
        return $this->isShared === false;
    }
}
