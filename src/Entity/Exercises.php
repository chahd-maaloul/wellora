<?php

namespace App\Entity;

use App\Repository\ExercisesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ExercisesRepository::class)]
#[Vich\Uploadable]
class Exercises
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Exercise name is required")]
    #[Assert\Length(max: 255, maxMessage: "Name cannot be longer than {{ limit }} characters")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Description is required")]
    #[Assert\Length(max: 255, maxMessage: "Description cannot be longer than {{ limit }} characters")]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Category is required")]
    #[Assert\Choice([
        'Strength', 
        'Cardio', 
        'Flexibility', 
        'Balance', 
        'Core', 
        'Warm-up', 
        'Cool-down'
    ])]
    private ?string $category = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Difficulty level is required")]
    #[Assert\Choice(['Beginner', 'Intermediate', 'Advanced'])]
    private ?string $difficulty_level = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Default unit is required")]
    #[Assert\Choice(['reps', 'seconds', 'minutes', 'meters', 'km', 'calories', 'steps'])]
    private ?string $defaultUnit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $videoUrl = null;

    #[ORM\Column(nullable: true)] // Rendre nullable pour les vidéos uploadées
    private ?string $videoFileName = null;

   #[Vich\UploadableField(mapping: 'exercise_video', fileNameProperty: 'videoFileName')]
    #[Assert\File(
        maxSize: '100M',
        mimeTypes: ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/webm'],
        mimeTypesMessage: 'Please upload a valid video file (MP4, AVI, MOV, WMV, WebM)'
    )]
    private ?File $videoFile = null;

    #[ORM\Column]
    private ?bool $isActive = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: 'integer', message: 'Duration must be a number')]
    #[Assert\PositiveOrZero(message: 'Duration must be zero or positive')]
    private ?int $duration = null; // en minutes

    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: 'integer', message: 'Calories must be a number')]
    #[Assert\PositiveOrZero(message: 'Calories must be zero or positive')]
    private ?int $calories = null; // calories brûlées par minute

    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: 'integer', message: 'Sets must be a number')]
    #[Assert\PositiveOrZero(message: 'Sets must be zero or positive')]
    private ?int $sets = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: 'integer', message: 'Reps must be a number')]
    #[Assert\PositiveOrZero(message: 'Reps must be zero or positive')]
    private ?int $reps = null;

    /**
     * @var Collection<int, DailyPlan>
     */
    #[ORM\ManyToMany(targetEntity: DailyPlan::class, mappedBy: 'exercices')]
    private Collection $dailyPlans;

    #[ORM\ManyToOne(inversedBy: 'exercises')]
    #[ORM\JoinColumn(referencedColumnName: 'uuid')]
    private ?User $User = null;

    public function __construct()
    {
        $this->dailyPlans = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->isActive = true;
    }

    // ... vos getters et setters existants ...

    // AJOUTEZ CES NOUVEAUX GETTERS ET SETTERS POUR LA VIDEO

    public function getVideoFileName(): ?string
    {
        return $this->videoFileName;
    }

    public function setVideoFileName(?string $videoFileName): static
    {
        $this->videoFileName = $videoFileName;
        return $this;
    }

    public function getVideoFile(): ?File
    {
        return $this->videoFile;
    }

    public function setVideoFile(?File $videoFile = null): static
    {
        $this->videoFile = $videoFile;

        // Seulement changer updatedAt si un fichier est téléchargé
        if ($videoFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // Méthode utilitaire pour obtenir l'URL de la vidéo (uploadée ou externe)
    public function getVideoPath(): ?string
    {
        if ($this->videoFileName) {
            return '/uploads/exercises/videos/' . $this->videoFileName;
        }
        
        return $this->videoUrl;
    }

    // Méthode utilitaire pour vérifier si une vidéo est uploadée
    public function hasUploadedVideo(): bool
    {
        return !empty($this->videoFileName);
    }

    // ... le reste de vos getters et setters existants ...

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getDifficultyLevel(): ?string
    {
        return $this->difficulty_level;
    }

    public function setDifficultyLevel(string $difficulty_level): static
    {
        $this->difficulty_level = $difficulty_level;
        return $this;
    }

    public function getDefaultUnit(): ?string
    {
        return $this->defaultUnit;
    }

    public function setDefaultUnit(string $defaultUnit): static
    {
        $this->defaultUnit = $defaultUnit;
        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    public function getCalories(): ?int
    {
        return $this->calories;
    }

    public function setCalories(?int $calories): static
    {
        $this->calories = $calories;
        return $this;
    }

    public function getSets(): ?int
    {
        return $this->sets;
    }

    public function setSets(?int $sets): static
    {
        $this->sets = $sets;
        return $this;
    }

    public function getReps(): ?int
    {
        return $this->reps;
    }

    public function setReps(?int $reps): static
    {
        $this->reps = $reps;
        return $this;
    }

    /**
     * @return Collection<int, DailyPlan>
     */
    public function getDailyPlans(): Collection
    {
        return $this->dailyPlans;
    }

    public function addDailyPlan(DailyPlan $dailyPlan): static
    {
        if (!$this->dailyPlans->contains($dailyPlan)) {
            $this->dailyPlans->add($dailyPlan);
            $dailyPlan->addExercice($this);
        }

        return $this;
    }

    public function removeDailyPlan(DailyPlan $dailyPlan): static
    {
        if ($this->dailyPlans->removeElement($dailyPlan)) {
            $dailyPlan->removeExercice($this);
        }

        return $this;
    }

    // Méthode toString pour l'affichage
    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }
}