<?php

namespace App\Entity;

use App\Repository\BarcodeProductRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BarcodeProductRepository::class)]
#[ORM\Table(name: 'barcode_products')]
#[ORM\Index(name: 'idx_barcode_product_barcode', columns: ['barcode'])]
class BarcodeProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $barcode;

    #[ORM\Column(type: 'string', length: 255)]
    private string $productName;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $brand = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $servingSize = 100;

    #[ORM\Column(type: 'string', length: 50)]
    private string $servingUnit = 'g';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $calories = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $proteins = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $carbohydrates = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $fats = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $saturatedFats = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $transFats = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $fiber = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $sugar = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $sodium = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $cholesterol = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $potassium = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $vitaminA = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $vitaminC = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $calcium = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $iron = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $vitaminD = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $vitaminB12 = 0;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $ingredients = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $allergens = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $originCountry = null;

    #[ORM\OneToMany(targetEntity: FoodEntry::class, mappedBy: 'barcodeProduct')]
    private Collection $foodEntries;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $scanCount = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isVerified = false;

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

    public function getBarcode(): string
    {
        return $this->barcode;
    }

    public function setBarcode(string $barcode): self
    {
        $this->barcode = $barcode;
        return $this;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;
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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getServingSize(): float
    {
        return $this->servingSize;
    }

    public function setServingSize(float $servingSize): self
    {
        $this->servingSize = $servingSize;
        return $this;
    }

    public function getServingUnit(): string
    {
        return $this->servingUnit;
    }

    public function setServingUnit(string $servingUnit): self
    {
        $this->servingUnit = $servingUnit;
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

    public function getSaturatedFats(): float
    {
        return $this->saturatedFats;
    }

    public function setSaturatedFats(float $saturatedFats): self
    {
        $this->saturatedFats = $saturatedFats;
        return $this;
    }

    public function getTransFats(): float
    {
        return $this->transFats;
    }

    public function setTransFats(float $transFats): self
    {
        $this->transFats = $transFats;
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

    public function getCholesterol(): float
    {
        return $this->cholesterol;
    }

    public function setCholesterol(float $cholesterol): self
    {
        $this->cholesterol = $cholesterol;
        return $this;
    }

    public function getPotassium(): float
    {
        return $this->potassium;
    }

    public function setPotassium(float $potassium): self
    {
        $this->potassium = $potassium;
        return $this;
    }

    public function getVitaminA(): float
    {
        return $this->vitaminA;
    }

    public function setVitaminA(float $vitaminA): self
    {
        $this->vitaminA = $vitaminA;
        return $this;
    }

    public function getVitaminC(): float
    {
        return $this->vitaminC;
    }

    public function setVitaminC(float $vitaminC): self
    {
        $this->vitaminC = $vitaminC;
        return $this;
    }

    public function getCalcium(): float
    {
        return $this->calcium;
    }

    public function setCalcium(float $calcium): self
    {
        $this->calcium = $calcium;
        return $this;
    }

    public function getIron(): float
    {
        return $this->iron;
    }

    public function setIron(float $iron): self
    {
        $this->iron = $iron;
        return $this;
    }

    public function getVitaminD(): float
    {
        return $this->vitaminD;
    }

    public function setVitaminD(float $vitaminD): self
    {
        $this->vitaminD = $vitaminD;
        return $this;
    }

    public function getVitaminB12(): float
    {
        return $this->vitaminB12;
    }

    public function setVitaminB12(float $vitaminB12): self
    {
        $this->vitaminB12 = $vitaminB12;
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

    public function getIngredients(): ?string
    {
        return $this->ingredients;
    }

    public function setIngredients(?string $ingredients): self
    {
        $this->ingredients = $ingredients;
        return $this;
    }

    public function getAllergens(): ?string
    {
        return $this->allergens;
    }

    public function setAllergens(?string $allergens): self
    {
        $this->allergens = $allergens;
        return $this;
    }

    public function getOriginCountry(): ?string
    {
        return $this->originCountry;
    }

    public function setOriginCountry(?string $originCountry): self
    {
        $this->originCountry = $originCountry;
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
            $foodEntry->setBarcodeProduct($this);
        }
        return $this;
    }

    public function removeFoodEntry(FoodEntry $foodEntry): self
    {
        if ($this->foodEntries->removeElement($foodEntry)) {
            if ($foodEntry->getBarcodeProduct() === $this) {
                $foodEntry->setBarcodeProduct(null);
            }
        }
        return $this;
    }

    public function getScanCount(): int
    {
        return $this->scanCount;
    }

    public function setScanCount(int $scanCount): self
    {
        $this->scanCount = $scanCount;
        return $this;
    }

    public function incrementScanCount(): self
    {
        $this->scanCount++;
        return $this;
    }

    public function getIsVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
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
     * Get nutrition facts per serving
     */
    public function getNutritionFactsPerServing(): array
    {
        return [
            'servingSize' => $this->servingSize . ' ' . $this->servingUnit,
            'calories' => $this->calories,
            'proteins' => $this->proteins,
            'carbohydrates' => $this->carbohydrates,
            'fats' => $this->fats,
            'saturatedFats' => $this->saturatedFats,
            'transFats' => $this->transFats,
            'fiber' => $this->fiber,
            'sugar' => $this->sugar,
            'sodium' => $this->sodium,
            'cholesterol' => $this->cholesterol,
        ];
    }

    /**
     * Get allergens as array
     */
    public function getAllergensList(): array
    {
        if (empty($this->allergens)) {
            return [];
        }
        return array_map('trim', explode(',', $this->ingredients));
    }

    /**
     * Get ingredients as array
     */
    public function getIngredientsList(): array
    {
        if (empty($this->ingredients)) {
            return [];
        }
        return array_map('trim', explode(',', $this->ingredients));
    }
}
