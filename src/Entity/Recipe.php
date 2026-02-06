<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[ORM\Table(name: 'recipes')]
#[ORM\Index(name: 'idx_recipe_user', columns: ['user_id'])]
class Recipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'recipes')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $sourceUrl = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $sourceWebsite = null;

    #[ORM\Column(type: 'integer')]
    private int $prepTime = 0;

    #[ORM\Column(type: 'integer')]
    private int $cookTime = 0;

    #[ORM\Column(type: 'integer')]
    private int $servings = 4;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $difficulty = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $calories = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $caloriesPerServing = 0;

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

    #[ORM\OneToMany(targetEntity: FoodEntry::class, mappedBy: 'recipe')]
    private Collection $foodEntries;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $ingredients = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $instructions = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $tags = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $dietaryInfo = null;

    #[ORM\Column(type: 'decimal', precision: 3, scale: 2, nullable: true)]
    private ?float $rating = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $favoriteCount = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isFavorite = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPublic = false;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $timesMade = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $estimatedCost = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $points = null;

    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private DateTime $updatedAt;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isDeleted = false;

    public function __construct()
    {
        $this->foodEntries = new ArrayCollection();
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
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

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(?string $sourceUrl): self
    {
        $this->sourceUrl = $sourceUrl;
        return $this;
    }

    public function getSourceWebsite(): ?string
    {
        return $this->sourceWebsite;
    }

    public function setSourceWebsite(?string $sourceWebsite): self
    {
        $this->sourceWebsite = $sourceWebsite;
        return $this;
    }

    public function getPrepTime(): int
    {
        return $this->prepTime;
    }

    public function setPrepTime(int $prepTime): self
    {
        $this->prepTime = $prepTime;
        return $this;
    }

    public function getCookTime(): int
    {
        return $this->cookTime;
    }

    public function setCookTime(int $cookTime): self
    {
        $this->cookTime = $cookTime;
        return $this;
    }

    public function getTotalTime(): int
    {
        return $this->prepTime + $this->cookTime;
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

    public function getDifficulty(): ?int
    {
        return $this->difficulty;
    }

    public function setDifficulty(?int $difficulty): self
    {
        $this->difficulty = $difficulty;
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

    public function getCaloriesPerServing(): float
    {
        return $this->caloriesPerServing;
    }

    public function setCaloriesPerServing(float $caloriesPerServing): self
    {
        $this->caloriesPerServing = $caloriesPerServing;
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
            $foodEntry->setRecipe($this);
        }
        return $this;
    }

    public function removeFoodEntry(FoodEntry $foodEntry): self
    {
        if ($this->foodEntries->removeElement($foodEntry)) {
            if ($foodEntry->getRecipe() === $this) {
                $foodEntry->setRecipe(null);
            }
        }
        return $this;
    }

    public function getIngredients(): ?array
    {
        return $this->ingredients;
    }

    public function setIngredients(?array $ingredients): self
    {
        $this->ingredients = $ingredients;
        return $this;
    }

    public function getInstructions(): ?array
    {
        return $this->instructions;
    }

    public function setInstructions(?array $instructions): self
    {
        $this->instructions = $instructions;
        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    public function getDietaryInfo(): ?array
    {
        return $this->dietaryInfo;
    }

    public function setDietaryInfo(?array $dietaryInfo): self
    {
        $this->dietaryInfo = $dietaryInfo;
        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getFavoriteCount(): int
    {
        return $this->favoriteCount;
    }

    public function setFavoriteCount(int $favoriteCount): self
    {
        $this->favoriteCount = $favoriteCount;
        return $this;
    }

    public function getIsFavorite(): bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(bool $isFavorite): self
    {
        $this->isFavorite = $isFavorite;
        return $this;
    }

    public function getIsPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    public function getTimesMade(): int
    {
        return $this->timesMade;
    }

    public function setTimesMade(int $timesMade): self
    {
        $this->timesMade = $timesMade;
        return $this;
    }

    public function incrementTimesMade(): self
    {
        $this->timesMade++;
        return $this;
    }

    public function getEstimatedCost(): ?float
    {
        return $this->estimatedCost;
    }

    public function setEstimatedCost(?float $estimatedCost): self
    {
        $this->estimatedCost = $estimatedCost;
        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): self
    {
        $this->points = $points;
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
     * Calculate macros per serving
     */
    public function getMacrosPerServing(): array
    {
        return [
            'calories' => $this->caloriesPerServing,
            'proteins' => $this->servings > 0 ? round($this->proteins / $this->servings, 1) : 0,
            'carbohydrates' => $this->servings > 0 ? round($this->carbohydrates / $this->servings, 1) : 0,
            'fats' => $this->servings > 0 ? round($this->fats / $this->servings, 1) : 0,
            'fiber' => $this->servings > 0 ? round($this->fiber / $this->servings, 1) : 0,
            'sugar' => $this->servings > 0 ? round($this->sugar / $this->servings, 1) : 0,
            'sodium' => $this->servings > 0 ? round($this->sodium / $this->servings, 1) : 0,
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

    /**
     * Get difficulty label
     */
    public function getDifficultyLabel(): string
    {
        if ($this->difficulty === null) {
            return 'Not specified';
        }
        
        return match ($this->difficulty) {
            1 => 'Easy',
            2 => 'Medium',
            3 => 'Hard',
            default => 'Not specified',
        };
    }

    /**
     * Check if recipe is vegetarian
     */
    public function isVegetarian(): bool
    {
        return $this->dietaryInfo && in_array('vegetarian', $this->dietaryInfo, true);
    }

    /**
     * Check if recipe is vegan
     */
    public function isVegan(): bool
    {
        return $this->dietaryInfo && in_array('vegan', $this->dietaryInfo, true);
    }

    /**
     * Check if recipe is gluten-free
     */
    public function isGlutenFree(): bool
    {
        return $this->dietaryInfo && in_array('gluten-free', $this->dietaryInfo, true);
    }

    /**
     * Calculate cost per serving
     */
    public function getCostPerServing(): ?float
    {
        if ($this->estimatedCost === null || $this->servings === 0) {
            return null;
        }
        return round($this->estimatedCost / $this->servings, 2);
    }
}
