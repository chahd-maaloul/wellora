<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'grocery_item')]
class GroceryItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GroceryList::class)]
    #[ORM\JoinColumn(name: 'grocery_list_id', referencedColumnName: 'id', nullable: false)]
    private GroceryList $groceryList;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => '1.00'])]
    private string $quantity;

    #[ORM\Column(type: 'string', length: 50, options: ['default' => 'pcs'])]
    private string $unit;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $checked;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $estimatedPrice = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $position;

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroceryList(): GroceryList
    {
        return $this->groceryList;
    }

    public function setGroceryList(GroceryList $groceryList): self
    {
        $this->groceryList = $groceryList;
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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getQuantity(): string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): self
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

    public function isChecked(): bool
    {
        return $this->checked;
    }

    public function setChecked(bool $checked): self
    {
        $this->checked = $checked;
        return $this;
    }

    public function getEstimatedPrice(): ?string
    {
        return $this->estimatedPrice;
    }

    public function setEstimatedPrice(?string $estimatedPrice): self
    {
        $this->estimatedPrice = $estimatedPrice;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }
}
