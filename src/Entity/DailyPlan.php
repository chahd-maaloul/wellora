<?php

namespace App\Entity;

use App\Repository\DailyPlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DailyPlanRepository::class)]
class DailyPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $notes = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column]
    private ?int $calorirs = null;

    #[ORM\Column]
    private ?int $duree_min = null;

    #[ORM\ManyToOne(inversedBy: 'dailyplan')]
    private ?Goal $goal = null;

    /**
     * @var Collection<int, Exercises>
     */
    #[ORM\ManyToMany(targetEntity: Exercises::class, inversedBy: 'dailyPlans')]
    private Collection $exercices;

    public function __construct()
    {
        $this->exercices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getCalorirs(): ?int
    {
        return $this->calorirs;
    }

    public function setCalorirs(int $calorirs): static
    {
        $this->calorirs = $calorirs;

        return $this;
    }

    public function getDureeMin(): ?int
    {
        return $this->duree_min;
    }

    public function setDureeMin(int $duree_min): static
    {
        $this->duree_min = $duree_min;

        return $this;
    }

    public function getGoal(): ?Goal
    {
        return $this->goal;
    }

    public function setGoal(?Goal $goal): static
    {
        $this->goal = $goal;

        return $this;
    }

    /**
     * @return Collection<int, Exercises>
     */
    public function getExercices(): Collection
    {
        return $this->exercices;
    }

    public function addExercice(Exercises $exercice): static
    {
        if (!$this->exercices->contains($exercice)) {
            $this->exercices->add($exercice);
        }

        return $this;
    }

    public function removeExercice(Exercises $exercice): static
    {
        $this->exercices->removeElement($exercice);

        return $this;
    }
}
