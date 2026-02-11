<?php

namespace App\Entity;

use App\Repository\NutritionGoalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NutritionGoalRepository::class)]
#[ORM\Table(name: 'nutrition_goal')]
class NutritionGoal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Nutritionist::class)]
    #[ORM\JoinColumn(name: 'nutritionist_id', referencedColumnName: 'id', nullable: false)]
    private Nutritionist $nutritionist;

    #[ORM\Column(type: 'string', length: 255)]
    private string $goalType;

    #[ORM\Column(type: 'integer')]
    private int $dailyCalories;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $proteinPercent;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $fatPercent;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $carbPercent;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $targetDate;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: true)]
    private ?Patient $patient = null;

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNutritionist(): Nutritionist
    {
        return $this->nutritionist;
    }

    public function setNutritionist(Nutritionist $nutritionist): self
    {
        $this->nutritionist = $nutritionist;
        return $this;
    }

    public function getGoalType(): string
    {
        return $this->goalType;
    }

    public function setGoalType(string $goalType): self
    {
        $this->goalType = $goalType;
        return $this;
    }

    public function getDailyCalories(): int
    {
        return $this->dailyCalories;
    }

    public function setDailyCalories(int $dailyCalories): self
    {
        $this->dailyCalories = $dailyCalories;
        return $this;
    }

    public function getProteinPercent(): string
    {
        return $this->proteinPercent;
    }

    public function setProteinPercent(string $proteinPercent): self
    {
        $this->proteinPercent = $proteinPercent;
        return $this;
    }

    public function getFatPercent(): string
    {
        return $this->fatPercent;
    }

    public function setFatPercent(string $fatPercent): self
    {
        $this->fatPercent = $fatPercent;
        return $this;
    }

    public function getCarbPercent(): string
    {
        return $this->carbPercent;
    }

    public function setCarbPercent(string $carbPercent): self
    {
        $this->carbPercent = $carbPercent;
        return $this;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getTargetDate(): \DateTimeInterface
    {
        return $this->targetDate;
    }

    public function setTargetDate(\DateTimeInterface $targetDate): self
    {
        $this->targetDate = $targetDate;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
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
