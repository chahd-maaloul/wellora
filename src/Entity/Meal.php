<?php

namespace App\Entity;

use App\Repository\MealRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MealRepository::class)]
#[ORM\Table(name: 'meals')]
#[ORM\Index(name: 'idx_meal_user_date', columns: ['user_id', 'meal_date'])]
class Meal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'meals')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $mealType = 'snack';

    #[ORM\Column(type: 'date')]
    private DateTime $mealDate;

    #[ORM\Column(type: 'time')]
    private DateTime $scheduledTime;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?DateTime $actualTime = null;

    #[ORM\OneToMany(targetEntity: FoodEntry::class, mappedBy: 'meal', cascade: ['persist', 'remove'])]
    private Collection $foodEntries;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $servings = 1;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $hungerLevel = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $mood = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isCompleted = false;

    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private DateTime $updatedAt;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isDeleted = false;

    public function __construct()
    {
        $this->foodEntries = new ArrayCollection();
        $this->mealDate = new DateTime();
        $this->scheduledTime = new DateTime();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getMealType(): string
    {
        return $this->mealType;
    }

    public function setMealType(string $mealType): self
    {
        $this->mealType = $mealType;
        return $this;
    }

    public function getMealDate(): DateTime
    {
        return $this->mealDate;
    }

    public function setMealDate(DateTime $mealDate): self
    {
        $this->mealDate = $mealDate;
        return $this;
    }

    public function getScheduledTime(): DateTime
    {
        return $this->scheduledTime;
    }

    public function setScheduledTime(DateTime $scheduledTime): self
    {
        $this->scheduledTime = $scheduledTime;
        return $this;
    }

    public function getActualTime(): ?DateTime
    {
        return $this->actualTime;
    }

    public function setActualTime(?DateTime $actualTime): self
    {
        $this->actualTime = $actualTime;
        return $this;
    }

    /**
     * @return Collection|FoodEntry[]
     */
    public function getFoodEntries(): Collection
    {
        return $this->foodEntries;
    }

    public function addFoodEntry(FoodEntry $foodEntry): self
    {
        if (!$this->foodEntries->contains($foodEntry)) {
            $this->foodEntries[] = $foodEntry;
            $foodEntry->setMeal($this);
        }
        return $this;
    }

    public function removeFoodEntry(FoodEntry $foodEntry): self
    {
        if ($this->foodEntries->removeElement($foodEntry)) {
            if ($foodEntry->getMeal() === $this) {
                $foodEntry->setMeal(null);
            }
        }
        return $this;
    }

    public function getServings(): int
    {
        return $this->servings;
    }

    public function setServings(int $servings): self
    {
        $this->servings = $servings;
        return $this;
    }

    public function getHungerLevel(): ?string
    {
        return $this->hungerLevel;
    }

    public function setHungerLevel(?string $hungerLevel): self
    {
        $this->hungerLevel = $hungerLevel;
        return $this;
    }

    public function getMood(): ?string
    {
        return $this->mood;
    }

    public function setMood(?string $mood): self
    {
        $this->mood = $mood;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
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

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getIsCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): self
    {
        $this->isCompleted = $isCompleted;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getIsDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;
        return $this;
    }

    /**
     * Calculate total calories for the meal
     */
    public function getTotalCalories(): float
    {
        $total = 0;
        foreach ($this->foodEntries as $entry) {
            $total += $entry->getCalories() * $this->servings;
        }
        return $total;
    }

    /**
     * Calculate total macros for the meal
     */
    public function getTotalMacros(): array
    {
        $macros = [
            'calories' => 0,
            'proteins' => 0,
            'carbohydrates' => 0,
            'fats' => 0,
            'fiber' => 0,
            'sugar' => 0,
            'sodium' => 0,
        ];

        foreach ($this->foodEntries as $entry) {
            $macros['calories'] += $entry->getCalories();
            $macros['proteins'] += $entry->getProteins();
            $macros['carbohydrates'] += $entry->getCarbohydrates();
            $macros['fats'] += $entry->getFats();
            $macros['fiber'] += $entry->getFiber();
            $macros['sugar'] += $entry->getSugar();
            $macros['sodium'] += $entry->getSodium();
        }

        return $macros;
    }

    /**
     * Get the number of food items in the meal
     */
    public function getFoodItemCount(): int
    {
        return $this->foodEntries->count();
    }

    /**
     * Get formatted meal time
     */
    public function getFormattedTime(): string
    {
        return $this->actualTime ? $this->actualTime->format('H:i') : $this->scheduledTime->format('H:i');
    }
}
