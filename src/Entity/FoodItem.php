<?php

namespace App\Entity;

use App\Repository\FoodItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FoodItemRepository::class)]
#[ORM\Table(name: 'food_items')]
class FoodItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'foodItems', targetEntity: FoodLog::class)]
    #[ORM\JoinColumn(name: 'food_log_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?FoodLog $foodLog = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $quantity = '1.0';

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $unit = 'serving';

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $calories = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 1, nullable: true)]
    private ?string $protein = '0.0';

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 1, nullable: true)]
    private ?string $carbs = '0.0';

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 1, nullable: true)]
    private ?string $fats = '0.0';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 1, nullable: true)]
    private ?string $fiber = '0.0';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 1, nullable: true)]
    private ?string $sugar = '0.0';

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $sodium = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $loggedAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isRecipe = false;

    public function __construct()
    {
        $this->loggedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFoodLog(): ?FoodLog
    {
        return $this->foodLog;
    }

    public function setFoodLog(?FoodLog $foodLog): static
    {
        $this->foodLog = $foodLog;

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

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(?string $quantity): static
    {
        $this->quantity = $quantity;

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

    public function getCalories(): ?int
    {
        return $this->calories;
    }

    public function setCalories(int $calories): static
    {
        $this->calories = $calories;

        return $this;
    }

    public function getProtein(): ?string
    {
        return $this->protein;
    }

    public function setProtein(?string $protein): static
    {
        $this->protein = $protein;

        return $this;
    }

    public function getCarbs(): ?string
    {
        return $this->carbs;
    }

    public function setCarbs(?string $carbs): static
    {
        $this->carbs = $carbs;

        return $this;
    }

    public function getFats(): ?string
    {
        return $this->fats;
    }

    public function setFats(?string $fats): static
    {
        $this->fats = $fats;

        return $this;
    }

    public function getFiber(): ?string
    {
        return $this->fiber;
    }

    public function setFiber(?string $fiber): static
    {
        $this->fiber = $fiber;

        return $this;
    }

    public function getSugar(): ?string
    {
        return $this->sugar;
    }

    public function setSugar(?string $sugar): static
    {
        $this->sugar = $sugar;

        return $this;
    }

    public function getSodium(): ?int
    {
        return $this->sodium;
    }

    public function setSodium(?int $sodium): static
    {
        $this->sodium = $sodium;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getLoggedAt(): ?\DateTimeInterface
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(?\DateTimeInterface $loggedAt): static
    {
        $this->loggedAt = $loggedAt;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function isRecipe(): bool
    {
        return $this->isRecipe;
    }

    public function setIsRecipe(bool $isRecipe): static
    {
        $this->isRecipe = $isRecipe;

        return $this;
    }
}
