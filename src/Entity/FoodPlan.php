<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\Table(name: 'food_plan')]
class FoodPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: NutritionGoal::class)]
    #[ORM\JoinColumn(name: 'nutrition_goal_id', referencedColumnName: 'id', nullable: false)]
    private NutritionGoal $nutritionGoal;

    #[ORM\Column(type: 'string', length: 255)]
    private string $nomPlan;

    #[ORM\Column(type: 'integer')]
    private int $calories;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $protein;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $fat;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $carbs;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $planDate;

    #[ORM\Column(type: 'string', length: 50)]
    private string $mealType;

    #[ORM\ManyToMany(targetEntity: FoodItem::class)]
    #[ORM\JoinTable(name: 'food_plan_food_item')]
    #[ORM\JoinColumn(name: 'food_plan_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'food_item_id', referencedColumnName: 'id')]
    private Collection $foodItems;

    public function __construct()
    {
        $this->foodItems = new ArrayCollection();
    }

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNutritionGoal(): NutritionGoal
    {
        return $this->nutritionGoal;
    }

    public function setNutritionGoal(NutritionGoal $nutritionGoal): self
    {
        $this->nutritionGoal = $nutritionGoal;
        return $this;
    }

    public function getNomPlan(): string
    {
        return $this->nomPlan;
    }

    public function setNomPlan(string $nomPlan): self
    {
        $this->nomPlan = $nomPlan;
        return $this;
    }

    public function getCalories(): int
    {
        return $this->calories;
    }

    public function setCalories(int $calories): self
    {
        $this->calories = $calories;
        return $this;
    }

    public function getProtein(): string
    {
        return $this->protein;
    }

    public function setProtein(string $protein): self
    {
        $this->protein = $protein;
        return $this;
    }

    public function getFat(): string
    {
        return $this->fat;
    }

    public function setFat(string $fat): self
    {
        $this->fat = $fat;
        return $this;
    }

    public function getCarbs(): string
    {
        return $this->carbs;
    }

    public function setCarbs(string $carbs): self
    {
        $this->carbs = $carbs;
        return $this;
    }

    public function getPlanDate(): \DateTimeInterface
    {
        return $this->planDate;
    }

    public function setPlanDate(\DateTimeInterface $planDate): self
    {
        $this->planDate = $planDate;
        return $this;
    }

    public function getMealType(): string
    {
        return $this->mealType ?? 'snacks';
    }

    public function setMealType(string $mealType): self
    {
        $this->mealType = $mealType;
        return $this;
    }

    public function getFoodItems(): Collection
    {
        return $this->foodItems;
    }

    public function addFoodItem(FoodItem $foodItem): self
    {
        if (!$this->foodItems->contains($foodItem)) {
            $this->foodItems->add($foodItem);
        }
        return $this;
    }

    public function removeFoodItem(FoodItem $foodItem): self
    {
        $this->foodItems->removeElement($foodItem);
        return $this;
    }
}
