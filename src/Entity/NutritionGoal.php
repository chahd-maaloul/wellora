<?php

namespace App\Entity;

use App\Repository\NutritionGoalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'nutritionGoals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * Goal Type: WEIGHT_LOSS, WEIGHT_GAIN, MAINTENANCE, BODY_RECOMPOSITION,
     * BLOOD_SUGAR, CHOLESTEROL, BLOOD_PRESSURE, DIGESTIVE_HEALTH,
     * ATHLETIC_PERFORMANCE, ENDURANCE, MUSCLE_RECOVERY, ENERGY_OPTIMIZATION,
     * HEALTHY_EATING, MEAL_PREP, WATER_INTAKE, REDUCED_PROCESSED_FOOD
     */
    #[ORM\Column(length: 50)]
    private ?string $goalType = null;

    #[ORM\Column]
    private ?float $currentWeight = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetWeight = null;

    #[ORM\Column(nullable: true)]
    private ?float $currentBodyFat = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetBodyFat = null;

    #[ORM\Column(nullable: true)]
    private ?float $currentWaistCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetWaistCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $currentChestCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetChestCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $currentHipCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetHipCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $currentArmCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetArmCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $currentThighCircumference = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetThighCircumference = null;

    #[ORM\Column]
    private ?float $currentCalories = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetCalories = null;

    #[ORM\Column(nullable: true)]
    private ?float $bmr = null;

    #[ORM\Column(nullable: true)]
    private ?float $tdee = null;

    /**
     * Activity Level: SEDENTARY, LIGHTLY_ACTIVE, MODERATELY_ACTIVE, VERY_ACTIVE, EXTRA_ACTIVE
     */
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $activityLevel = null;

    #[ORM\Column]
    private ?float $targetProteinGrams = null;

    #[ORM\Column]
    private ?float $targetCarbGrams = null;

    #[ORM\Column]
    private ?float $targetFatGrams = null;

    #[ORM\Column]
    private ?float $targetProteinPercent = null;

    #[ORM\Column]
    private ?float $targetCarbPercent = null;

    #[ORM\Column]
    private ?float $targetFatPercent = null;

    #[ORM\Column]
    private ?int $targetMealFrequency = null;

    #[ORM\Column(nullable: true)]
    private ?int $targetWaterIntake = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetFiberIntake = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetSugarIntake = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetSodiumIntake = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetCholesterol = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetBloodGlucose = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetBloodPressureSystolic = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetBloodPressureDiastolic = null;

    /**
     * Priority: HIGH, MEDIUM, LOW
     */
    #[ORM\Column(length: 20)]
    private ?string $priority = self::PRIORITY_MEDIUM;

    /**
     * Status: DRAFT, ACTIVE, PAUSED, COMPLETED, CANCELLED
     */
    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_DRAFT;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $targetDate = null;

    #[ORM\Column(nullable: true)]
    private ?float $weeklyWeightChangeTarget = null;

    #[ORM\Column(nullable: true)]
    private ?float $expectedWeightChangePerWeek = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $adherenceRate = null;

    #[ORM\Column(nullable: true)]
    private ?int $currentStreak = null;

    #[ORM\Column(nullable: true)]
    private ?int $longestStreak = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'nutritionGoal', targetEntity: NutritionGoalProgress::class, cascade: ['persist', 'remove'])]
    private Collection $progressRecords;

    #[ORM\OneToMany(mappedBy: 'nutritionGoal', targetEntity: NutritionGoalMilestone::class, cascade: ['persist', 'remove'])]
    private Collection $milestones;

    #[ORM\OneToMany(mappedBy: 'nutritionGoal', targetEntity: NutritionGoalAdjustment::class, cascade: ['persist', 'remove'])]
    private Collection $adjustments;

    #[ORM\OneToMany(mappedBy: 'nutritionGoal', targetEntity: NutritionGoalAchievement::class, cascade: ['persist', 'remove'])]
    private Collection $achievements;

    // Constants
    public const PRIORITY_HIGH = 'HIGH';
    public const PRIORITY_MEDIUM = 'MEDIUM';
    public const PRIORITY_LOW = 'LOW';

    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_PAUSED = 'PAUSED';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const GOAL_TYPE_WEIGHT_LOSS = 'WEIGHT_LOSS';
    public const GOAL_TYPE_WEIGHT_GAIN = 'WEIGHT_GAIN';
    public const GOAL_TYPE_MAINTENANCE = 'MAINTENANCE';
    public const GOAL_TYPE_BODY_RECOMPOSITION = 'BODY_RECOMPOSITION';
    public const GOAL_TYPE_BLOOD_SUGAR = 'BLOOD_SUGAR';
    public const GOAL_TYPE_CHOLESTEROL = 'CHOLESTEROL';
    public const GOAL_TYPE_BLOOD_PRESSURE = 'BLOOD_PRESSURE';
    public const GOAL_TYPE_DIGESTIVE_HEALTH = 'DIGESTIVE_HEALTH';
    public const GOAL_TYPE_ATHLETIC_PERFORMANCE = 'ATHLETIC_PERFORMANCE';
    public const GOAL_TYPE_ENDURANCE = 'ENDURANCE';
    public const GOAL_TYPE_MUSCLE_RECOVERY = 'MUSCLE_RECOVERY';
    public const GOAL_TYPE_ENERGY_OPTIMIZATION = 'ENERGY_OPTIMIZATION';
    public const GOAL_TYPE_HEALTHY_EATING = 'HEALTHY_EATING';
    public const GOAL_TYPE_MEAL_PREP = 'MEAL_PREP';
    public const GOAL_TYPE_WATER_INTAKE = 'WATER_INTAKE';
    public const GOAL_TYPE_REDUCED_PROCESSED_FOOD = 'REDUCED_PROCESSED_FOOD';

    public const ACTIVITY_SEDENTARY = 'SEDENTARY';
    public const ACTIVITY_LIGHTLY_ACTIVE = 'LIGHTLY_ACTIVE';
    public const ACTIVITY_MODERATELY_ACTIVE = 'MODERATELY_ACTIVE';
    public const ACTIVITY_VERY_ACTIVE = 'VERY_ACTIVE';
    public const ACTIVITY_EXTRA_ACTIVE = 'EXTRA_ACTIVE';

    public function __construct()
    {
        $this->progressRecords = new ArrayCollection();
        $this->milestones = new ArrayCollection();
        $this->adjustments = new ArrayCollection();
        $this->achievements = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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

    public function getGoalType(): ?string
    {
        return $this->goalType;
    }

    public function setGoalType(string $goalType): static
    {
        $this->goalType = $goalType;
        return $this;
    }

    public function getCurrentWeight(): ?float
    {
        return $this->currentWeight;
    }

    public function setCurrentWeight(float $currentWeight): static
    {
        $this->currentWeight = $currentWeight;
        return $this;
    }

    public function getTargetWeight(): ?float
    {
        return $this->targetWeight;
    }

    public function setTargetWeight(?float $targetWeight): static
    {
        $this->targetWeight = $targetWeight;
        return $this;
    }

    public function getWeightToLose(): ?float
    {
        if ($this->targetWeight && $this->currentWeight) {
            return $this->currentWeight - $this->targetWeight;
        }
        return null;
    }

    public function getWeightToGain(): ?float
    {
        if ($this->targetWeight && $this->currentWeight) {
            return $this->targetWeight - $this->currentWeight;
        }
        return null;
    }

    public function getCurrentBodyFat(): ?float
    {
        return $this->currentBodyFat;
    }

    public function setCurrentBodyFat(?float $currentBodyFat): static
    {
        $this->currentBodyFat = $currentBodyFat;
        return $this;
    }

    public function getTargetBodyFat(): ?float
    {
        return $this->targetBodyFat;
    }

    public function setTargetBodyFat(?float $targetBodyFat): static
    {
        $this->targetBodyFat = $targetBodyFat;
        return $this;
    }

    public function getCurrentWaistCircumference(): ?float
    {
        return $this->currentWaistCircumference;
    }

    public function setCurrentWaistCircumference(?float $currentWaistCircumference): static
    {
        $this->currentWaistCircumference = $currentWaistCircumference;
        return $this;
    }

    public function getTargetWaistCircumference(): ?float
    {
        return $this->targetWaistCircumference;
    }

    public function setTargetWaistCircumference(?float $targetWaistCircumference): static
    {
        $this->targetWaistCircumference = $targetWaistCircumference;
        return $this;
    }

    public function getCurrentChestCircumference(): ?float
    {
        return $this->currentChestCircumference;
    }

    public function setCurrentChestCircumference(?float $currentChestCircumference): static
    {
        $this->currentChestCircumference = $currentChestCircumference;
        return $this;
    }

    public function getTargetChestCircumference(): ?float
    {
        return $this->targetChestCircumference;
    }

    public function setTargetChestCircumference(?float $targetChestCircumference): static
    {
        $this->targetChestCircumference = $targetChestCircumference;
        return $this;
    }

    public function getCurrentHipCircumference(): ?float
    {
        return $this->currentHipCircumference;
    }

    public function setCurrentHipCircumference(?float $currentHipCircumference): static
    {
        $this->currentHipCircumference = $currentHipCircumference;
        return $this;
    }

    public function getTargetHipCircumference(): ?float
    {
        return $this->targetHipCircumference;
    }

    public function setTargetHipCircumference(?float $targetHipCircumference): static
    {
        $this->targetHipCircumference = $targetHipCircumference;
        return $this;
    }

    public function getCurrentArmCircumference(): ?float
    {
        return $this->currentArmCircumference;
    }

    public function setCurrentArmCircumference(?float $currentArmCircumference): static
    {
        $this->currentArmCircumference = $currentArmCircumference;
        return $this;
    }

    public function getTargetArmCircumference(): ?float
    {
        return $this->targetArmCircumference;
    }

    public function setTargetArmCircumference(?float $targetArmCircumference): static
    {
        $this->targetArmCircumference = $targetArmCircumference;
        return $this;
    }

    public function getCurrentThighCircumference(): ?float
    {
        return $this->currentThighCircumference;
    }

    public function setCurrentThighCircumference(?float $currentThighCircumference): static
    {
        $this->currentThighCircumference = $currentThighCircumference;
        return $this;
    }

    public function getTargetThighCircumference(): ?float
    {
        return $this->targetThighCircumference;
    }

    public function setTargetThighCircumference(?float $targetThighCircumference): static
    {
        $this->targetThighCircumference = $targetThighCircumference;
        return $this;
    }

    public function getCurrentCalories(): ?float
    {
        return $this->currentCalories;
    }

    public function setCurrentCalories(float $currentCalories): static
    {
        $this->currentCalories = $currentCalories;
        return $this;
    }

    public function getTargetCalories(): ?float
    {
        return $this->targetCalories;
    }

    public function setTargetCalories(?float $targetCalories): static
    {
        $this->targetCalories = $targetCalories;
        return $this;
    }

    public function getCalorieDeficit(): ?float
    {
        if ($this->targetCalories && $this->currentCalories) {
            return $this->currentCalories - $this->targetCalories;
        }
        return null;
    }

    public function getCalorieSurplus(): ?float
    {
        if ($this->targetCalories && $this->currentCalories) {
            return $this->targetCalories - $this->currentCalories;
        }
        return null;
    }

    public function getBmr(): ?float
    {
        return $this->bmr;
    }

    public function setBmr(?float $bmr): static
    {
        $this->bmr = $bmr;
        return $this;
    }

    public function getTdee(): ?float
    {
        return $this->tdee;
    }

    public function setTdee(?float $tdee): static
    {
        $this->tdee = $tdee;
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

    public function getTargetProteinGrams(): ?float
    {
        return $this->targetProteinGrams;
    }

    public function setTargetProteinGrams(float $targetProteinGrams): static
    {
        $this->targetProteinGrams = $targetProteinGrams;
        return $this;
    }

    public function getTargetCarbGrams(): ?float
    {
        return $this->targetCarbGrams;
    }

    public function setTargetCarbGrams(float $targetCarbGrams): static
    {
        $this->targetCarbGrams = $targetCarbGrams;
        return $this;
    }

    public function getTargetFatGrams(): ?float
    {
        return $this->targetFatGrams;
    }

    public function setTargetFatGrams(float $targetFatGrams): static
    {
        $this->targetFatGrams = $targetFatGrams;
        return $this;
    }

    public function getTargetProteinPercent(): ?float
    {
        return $this->targetProteinPercent;
    }

    public function setTargetProteinPercent(float $targetProteinPercent): static
    {
        $this->targetProteinPercent = $targetProteinPercent;
        return $this;
    }

    public function getTargetCarbPercent(): ?float
    {
        return $this->targetCarbPercent;
    }

    public function setTargetCarbPercent(float $targetCarbPercent): static
    {
        $this->targetCarbPercent = $targetCarbPercent;
        return $this;
    }

    public function getTargetFatPercent(): ?float
    {
        return $this->targetFatPercent;
    }

    public function setTargetFatPercent(float $targetFatPercent): static
    {
        $this->targetFatPercent = $targetFatPercent;
        return $this;
    }

    public function getTargetMealFrequency(): ?int
    {
        return $this->targetMealFrequency;
    }

    public function setTargetMealFrequency(int $targetMealFrequency): static
    {
        $this->targetMealFrequency = $targetMealFrequency;
        return $this;
    }

    public function getTargetWaterIntake(): ?int
    {
        return $this->targetWaterIntake;
    }

    public function setTargetWaterIntake(?int $targetWaterIntake): static
    {
        $this->targetWaterIntake = $targetWaterIntake;
        return $this;
    }

    public function getTargetFiberIntake(): ?float
    {
        return $this->targetFiberIntake;
    }

    public function setTargetFiberIntake(?float $targetFiberIntake): static
    {
        $this->targetFiberIntake = $targetFiberIntake;
        return $this;
    }

    public function getTargetSugarIntake(): ?float
    {
        return $this->targetSugarIntake;
    }

    public function setTargetSugarIntake(?float $targetSugarIntake): static
    {
        $this->targetSugarIntake = $targetSugarIntake;
        return $this;
    }

    public function getTargetSodiumIntake(): ?float
    {
        return $this->targetSodiumIntake;
    }

    public function setTargetSodiumIntake(?float $targetSodiumIntake): static
    {
        $this->targetSodiumIntake = $targetSodiumIntake;
        return $this;
    }

    public function getTargetCholesterol(): ?float
    {
        return $this->targetCholesterol;
    }

    public function setTargetCholesterol(?float $targetCholesterol): static
    {
        $this->targetCholesterol = $targetCholesterol;
        return $this;
    }

    public function getTargetBloodGlucose(): ?float
    {
        return $this->targetBloodGlucose;
    }

    public function setTargetBloodGlucose(?float $targetBloodGlucose): static
    {
        $this->targetBloodGlucose = $targetBloodGlucose;
        return $this;
    }

    public function getTargetBloodPressureSystolic(): ?float
    {
        return $this->targetBloodPressureSystolic;
    }

    public function setTargetBloodPressureSystolic(?float $targetBloodPressureSystolic): static
    {
        $this->targetBloodPressureSystolic = $targetBloodPressureSystolic;
        return $this;
    }

    public function getTargetBloodPressureDiastolic(): ?float
    {
        return $this->targetBloodPressureDiastolic;
    }

    public function setTargetBloodPressureDiastolic(?float $targetBloodPressureDiastolic): static
    {
        $this->targetBloodPressureDiastolic = $targetBloodPressureDiastolic;
        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
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

    public function getWeeklyWeightChangeTarget(): ?float
    {
        return $this->weeklyWeightChangeTarget;
    }

    public function setWeeklyWeightChangeTarget(?float $weeklyWeightChangeTarget): static
    {
        $this->weeklyWeightChangeTarget = $weeklyWeightChangeTarget;
        return $this;
    }

    public function getExpectedWeightChangePerWeek(): ?float
    {
        return $this->expectedWeightChangePerWeek;
    }

    public function setExpectedWeightChangePerWeek(?float $expectedWeightChangePerWeek): static
    {
        $this->expectedWeightChangePerWeek = $expectedWeightChangePerWeek;
        return $this;
    }

    public function getAdherenceRate(): ?string
    {
        return $this->adherenceRate;
    }

    public function setAdherenceRate(?string $adherenceRate): static
    {
        $this->adherenceRate = $adherenceRate;
        return $this;
    }

    public function getCurrentStreak(): ?int
    {
        return $this->currentStreak;
    }

    public function setCurrentStreak(?int $currentStreak): static
    {
        $this->currentStreak = $currentStreak;
        return $this;
    }

    public function getLongestStreak(): ?int
    {
        return $this->longestStreak;
    }

    public function setLongestStreak(?int $longestStreak): static
    {
        $this->longestStreak = $longestStreak;
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getProgressRecords(): Collection
    {
        return $this->progressRecords;
    }

    public function addProgressRecord(NutritionGoalProgress $progressRecord): static
    {
        if (!$this->progressRecords->contains($progressRecord)) {
            $this->progressRecords->add($progressRecord);
            $progressRecord->setNutritionGoal($this);
        }
        return $this;
    }

    public function removeProgressRecord(NutritionGoalProgress $progressRecord): static
    {
        if ($this->progressRecords->removeElement($progressRecord)) {
            if ($progressRecord->getNutritionGoal() === $this) {
                $progressRecord->setNutritionGoal(null);
            }
        }
        return $this;
    }

    public function getMilestones(): Collection
    {
        return $this->milestones;
    }

    public function addMilestone(NutritionGoalMilestone $milestone): static
    {
        if (!$this->milestones->contains($milestone)) {
            $this->milestones->add($milestone);
            $milestone->setNutritionGoal($this);
        }
        return $this;
    }

    public function removeMilestone(NutritionGoalMilestone $milestone): static
    {
        if ($this->milestones->removeElement($milestone)) {
            if ($milestone->getNutritionGoal() === $this) {
                $milestone->setNutritionGoal(null);
            }
        }
        return $this;
    }

    public function getAdjustments(): Collection
    {
        return $this->adjustments;
    }

    public function addAdjustment(NutritionGoalAdjustment $adjustment): static
    {
        if (!$this->adjustments->contains($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setNutritionGoal($this);
        }
        return $this;
    }

    public function removeAdjustment(NutritionGoalAdjustment $adjustment): static
    {
        if ($this->adjustments->removeElement($adjustment)) {
            if ($adjustment->getNutritionGoal() === $this) {
                $adjustment->setNutritionGoal(null);
            }
        }
        return $this;
    }

    public function getAchievements(): Collection
    {
        return $this->achievements;
    }

    public function addAchievement(NutritionGoalAchievement $achievement): static
    {
        if (!$this->achievements->contains($achievement)) {
            $this->achievements->add($achievement);
            $achievement->setNutritionGoal($this);
        }
        return $this;
    }

    public function removeAchievement(NutritionGoalAchievement $achievement): static
    {
        if ($this->achievements->removeElement($achievement)) {
            if ($achievement->getNutritionGoal() === $this) {
                $achievement->setNutritionGoal(null);
            }
        }
        return $this;
    }

    /**
     * Calculate estimated completion date based on weight change rate
     */
    public function getEstimatedCompletionDate(): ?\DateTimeInterface
    {
        if (!$this->targetWeight || !$this->currentWeight || !$this->expectedWeightChangePerWeek || $this->expectedWeightChangePerWeek == 0) {
            return null;
        }

        $weightDifference = abs($this->targetWeight - $this->currentWeight);
        $weeksNeeded = $weightDifference / abs($this->expectedWeightChangePerWeek);
        $daysNeeded = (int)($weeksNeeded * 7);

        $estimatedDate = clone $this->startDate;
        $estimatedDate->modify("+{$daysNeeded} days");

        return $estimatedDate;
    }

    /**
     * Calculate goal progress percentage
     */
    public function getProgressPercentage(): float
    {
        if (!$this->targetWeight || !$this->currentWeight || $this->targetWeight == $this->currentWeight) {
            return 100.0;
        }

        $totalChange = $this->targetWeight - $this->currentWeight;
        if ($totalChange == 0) {
            return 100.0;
        }

        // For weight loss, progress is negative change
        $actualChange = $this->targetWeight - $this->currentWeight;
        $progress = ($actualChange / $totalChange) * 100;

        return min(100, max(0, $progress));
    }

    /**
     * Check if goal is on track
     */
    public function isOnTrack(): bool
    {
        $progressPercentage = $this->getProgressPercentage();
        $today = new \DateTime();
        $daysElapsed = $this->startDate->diff($today)->days;
        $totalDays = $this->startDate->diff($this->targetDate)->days;

        if ($totalDays == 0) {
            return true;
        }

        $expectedProgress = ($daysElapsed / $totalDays) * 100;
        $tolerance = 15; // 15% tolerance

        return abs($progressPercentage - $expectedProgress) <= $tolerance;
    }

    /**
     * Get goal type display name
     */
    public function getGoalTypeDisplayName(): string
    {
        $types = [
            self::GOAL_TYPE_WEIGHT_LOSS => 'Perte de poids',
            self::GOAL_TYPE_WEIGHT_GAIN => 'Prise de masse',
            self::GOAL_TYPE_MAINTENANCE => 'Maintien du poids',
            self::GOAL_TYPE_BODY_RECOMPOSITION => 'Recomposition corporelle',
            self::GOAL_TYPE_BLOOD_SUGAR => 'Gestion du taux de sucre dans le sang',
            self::GOAL_TYPE_CHOLESTEROL => 'Réduction du cholestérol',
            self::GOAL_TYPE_BLOOD_PRESSURE => 'Contrôle de la tension artérielle',
            self::GOAL_TYPE_DIGESTIVE_HEALTH => 'Amélioration de la digestion',
            self::GOAL_TYPE_ATHLETIC_PERFORMANCE => 'Performance sportive',
            self::GOAL_TYPE_ENDURANCE => 'Amélioration de l\'endurance',
            self::GOAL_TYPE_MUSCLE_RECOVERY => 'Récupération musculaire',
            self::GOAL_TYPE_ENERGY_OPTIMIZATION => 'Optimisation de l\'énergie',
            self::GOAL_TYPE_HEALTHY_EATING => 'Habitudes alimentaires saines',
            self::GOAL_TYPE_MEAL_PREP => 'Consistance de la préparation des repas',
            self::GOAL_TYPE_WATER_INTAKE => 'Hydratation',
            self::GOAL_TYPE_REDUCED_PROCESSED_FOOD => 'Réduction des aliments transformés',
        ];

        return $types[$this->goalType] ?? $this->goalType;
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName(): string
    {
        $statuses = [
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_ACTIVE => 'Actif',
            self::STATUS_PAUSED => 'En pause',
            self::STATUS_COMPLETED => 'Terminé',
            self::STATUS_CANCELLED => 'Annulé',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        $colors = [
            self::STATUS_DRAFT => 'gray',
            self::STATUS_ACTIVE => 'green',
            self::STATUS_PAUSED => 'yellow',
            self::STATUS_COMPLETED => 'blue',
            self::STATUS_CANCELLED => 'red',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Get priority display name
     */
    public function getPriorityDisplayName(): string
    {
        $priorities = [
            self::PRIORITY_HIGH => 'Haute',
            self::PRIORITY_MEDIUM => 'Moyenne',
            self::PRIORITY_LOW => 'Basse',
        ];

        return $priorities[$this->priority] ?? $this->priority;
    }

    /**
     * Get days remaining until target date
     */
    public function getDaysRemaining(): ?int
    {
        if (!$this->targetDate) {
            return null;
        }

        $today = new \DateTime();
        $diff = $today->diff($this->targetDate);

        return $diff->invert ? 0 : $diff->days;
    }

    /**
     * Get weeks remaining until target date
     */
    public function getWeeksRemaining(): ?float
    {
        $days = $this->getDaysRemaining();
        if ($days === null) {
            return null;
        }

        return $days / 7;
    }

    /**
     * Get macro distribution as array
     */
    public function getMacroDistribution(): array
    {
        return [
            'protein' => [
                'grams' => $this->targetProteinGrams,
                'percent' => $this->targetProteinPercent,
                'calories' => $this->targetProteinGrams * 4,
            ],
            'carbs' => [
                'grams' => $this->targetCarbGrams,
                'percent' => $this->targetCarbPercent,
                'calories' => $this->targetCarbGrams * 4,
            ],
            'fats' => [
                'grams' => $this->targetFatGrams,
                'percent' => $this->targetFatPercent,
                'calories' => $this->targetFatGrams * 9,
            ],
        ];
    }
}
