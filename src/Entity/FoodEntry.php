<?php

namespace App\Entity;

use App\Repository\FoodEntryRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FoodEntryRepository::class)]
#[ORM\Table(name: 'food_entries')]
#[ORM\Index(name: 'idx_food_entry_meal_date', columns: ['meal_type', 'entry_date'])]
#[ORM\Index(name: 'idx_food_entry_user_date', columns: ['user_id', 'entry_date'])]
class FoodEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'foodEntries')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $foodName;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $brand = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $quantity = 1;

    #[ORM\Column(type: 'string', length: 50)]
    private string $unit = 'serving';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $calories = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $proteins = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $carbohydrates = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $fats = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $fiber = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $sugar = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $sodium = 0;

    #[ORM\Column(type: 'string', length: 50)]
    private string $mealType = 'snack';

    #[ORM\Column(type: 'date')]
    private DateTime $entryDate;

    #[ORM\Column(type: 'time')]
    private DateTime $entryTime;

    #[ORM\ManyToOne(targetEntity: BarcodeProduct::class, inversedBy: 'foodEntries')]
    #[ORM\JoinColumn(name: 'barcode_product_id', referencedColumnName: 'id', nullable: true)]
    private ?BarcodeProduct $barcodeProduct = null;

    #[ORM\ManyToOne(targetEntity: Recipe::class, inversedBy: 'foodEntries')]
    #[ORM\JoinColumn(name: 'recipe_id', referencedColumnName: 'id', nullable: true)]
    private ?Recipe $recipe = null;

    #[ORM\ManyToOne(targetEntity: Meal::class, inversedBy: 'foodEntries')]
    #[ORM\JoinColumn(name: 'meal_id', referencedColumnName: 'id', nullable: true)]
    private ?Meal $meal = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $hungerLevel = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $mood = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $preparationMethod = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $voiceInputHash = null;

    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private DateTime $updatedAt;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isDeleted = false;

    public function __construct()
    {
        $this->entryDate = new DateTime();
        $this->entryTime = new DateTime();
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

    public function getFoodName(): string
    {
        return $this->foodName;
    }

    public function setFoodName(string $foodName): self
    {
        $this->foodName = $foodName;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): self
    {
        $this->unit = $unit;
        return $this;
    }

    public function getCalories(): float
    {
        return $this->calories;
    }

    public function setCalories(float $calories): self
    {
        $this->calories = $calories;
        return $this;
    }

    public function getProteins(): float
    {
        return $this->proteins;
    }

    public function setProteins(float $proteins): self
    {
        $this->proteins = $proteins;
        return $this;
    }

    public function getCarbohydrates(): float
    {
        return $this->carbohydrates;
    }

    public function setCarbohydrates(float $carbohydrates): self
    {
        $this->carbohydrates = $carbohydrates;
        return $this;
    }

    public function getFats(): float
    {
        return $this->fats;
    }

    public function setFats(float $fats): self
    {
        $this->fats = $fats;
        return $this;
    }

    public function getFiber(): float
    {
        return $this->fiber;
    }

    public function setFiber(float $fiber): self
    {
        $this->fiber = $fiber;
        return $this;
    }

    public function getSugar(): float
    {
        return $this->sugar;
    }

    public function setSugar(float $sugar): self
    {
        $this->sugar = $sugar;
        return $this;
    }

    public function getSodium(): float
    {
        return $this->sodium;
    }

    public function setSodium(float $sodium): self
    {
        $this->sodium = $sodium;
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

    public function getEntryDate(): DateTime
    {
        return $this->entryDate;
    }

    public function setEntryDate(DateTime $entryDate): self
    {
        $this->entryDate = $entryDate;
        return $this;
    }

    public function getEntryTime(): DateTime
    {
        return $this->entryTime;
    }

    public function setEntryTime(DateTime $entryTime): self
    {
        $this->entryTime = $entryTime;
        return $this;
    }

    public function getBarcodeProduct(): ?BarcodeProduct
    {
        return $this->barcodeProduct;
    }

    public function setBarcodeProduct(?BarcodeProduct $barcodeProduct): self
    {
        $this->barcodeProduct = $barcodeProduct;
        return $this;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;
        return $this;
    }

    public function getMeal(): ?Meal
    {
        return $this->meal;
    }

    public function setMeal(?Meal $meal): self
    {
        $this->meal = $meal;
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getPreparationMethod(): ?string
    {
        return $this->preparationMethod;
    }

    public function setPreparationMethod(?string $preparationMethod): self
    {
        $this->preparationMethod = $preparationMethod;
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

    public function getVoiceInputHash(): ?string
    {
        return $this->voiceInputHash;
    }

    public function setVoiceInputHash(?string $voiceInputHash): self
    {
        $this->voiceInputHash = $voiceInputHash;
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
     * Calculate total macros for the entry
     */
    public function getTotalMacros(): array
    {
        return [
            'calories' => $this->calories,
            'proteins' => $this->proteins,
            'carbohydrates' => $this->carbohydrates,
            'fats' => $this->fats,
            'fiber' => $this->fiber,
            'sugar' => $this->sugar,
            'sodium' => $this->sodium,
        ];
    }

    /**
     * Get macro percentages
     */
    public function getMacroPercentages(): array
    {
        $total = $this->proteins * 4 + $this->carbohydrates * 4 + $this->fats * 9;
        if ($total === 0) {
            return ['proteins' => 0, 'carbohydrates' => 0, 'fats' => 0];
        }

        return [
            'proteins' => round(($this->proteins * 4 / $total) * 100, 1),
            'carbohydrates' => round(($this->carbohydrates * 4 / $total) * 100, 1),
            'fats' => round(($this->fats * 9 / $total) * 100, 1),
        ];
    }
}
