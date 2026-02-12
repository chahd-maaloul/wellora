<?php

namespace App\Entity;

use App\Repository\DailyPlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: DailyPlanRepository::class)]
class DailyPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "Date is required")]
    private ?\DateTime $date = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Status is required")]
    #[Assert\Choice(['Planned', 'Completed', 'Missed'])]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255, maxMessage: "Notes cannot be longer than {{ limit }} characters")]
    private ?string $notes = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Title is required")]
    #[Assert\Length(max: 255, maxMessage: "Title cannot be longer than {{ limit }} characters")]
    private ?string $titre = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero(message: "Calories must be zero or positive")]
    private ?int $calories = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero(message: "Duration in minutes must be zero or positive")]
    private ?int $duree_min = null;

    #[ORM\ManyToOne(inversedBy: 'dailyplan')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Goal $goal = null;

    /**
     * @var Collection<int, Exercises>
     */
    #[ORM\ManyToMany(targetEntity: Exercises::class, inversedBy: 'dailyPlans')]
    #[ORM\JoinTable(name: 'daily_plan_exercises')]
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

    public function getCalories(): ?int
    {
        return $this->calories;
    }

    public function setCalories(int $calories): static
    {
        $this->calories = $calories;

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
