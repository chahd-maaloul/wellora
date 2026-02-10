<?php

namespace App\Entity;

use App\Repository\HealthentryRepository;
use App\Entity\Symptom;
use App\Entity\Healthjournal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HealthentryRepository::class)]
class Healthentry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column]
    private ?float $poids = null;

    #[ORM\Column]
    private ?float $glycemie = null;

    #[ORM\Column]
    private ?float $tension = null;

    #[ORM\Column]
    private ?int $sommeil = null;

    #[ORM\ManyToOne(targetEntity: Healthjournal::class, inversedBy: "entries")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Healthjournal $journal = null;

    #[ORM\OneToMany(mappedBy: "entry", targetEntity: Symptom::class, cascade: ["persist", "remove"])]
    private Collection $symptoms;


    public function __construct()
    {
        $this->symptoms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJournal(): ?Healthjournal
    {
        return $this->journal;
    }

    public function setJournal(?Healthjournal $journal): static
    {
        $this->journal = $journal;

        return $this;
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

    public function setDateEntry(\DateTime $dateEntry): static
    {
        return $this->setDate($dateEntry);
    }

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(float $poids): static
    {
        $this->poids = $poids;

        return $this;
    }

    public function getGlycemie(): ?float
    {
        return $this->glycemie;
    }

    public function setGlycemie(float $glycemie): static
    {
        $this->glycemie = $glycemie;

        return $this;
    }

    public function getTension(): ?float
    {
        return $this->tension;
    }

    public function setTension(float $tension): static
    {
        $this->tension = $tension;

        return $this;
    }

    public function getSommeil(): ?int
    {
        return $this->sommeil;
    }

    public function setSommeil(int $sommeil): static
    {
        $this->sommeil = $sommeil;

        return $this;
    }

    /**
     * @return Collection<int, Symptom>
     */
    public function getSymptoms(): Collection
    {
        return $this->symptoms;
    }

    public function addSymptom(Symptom $symptom): static
    {
        if (!$this->symptoms->contains($symptom)) {
            $this->symptoms->add($symptom);
            $symptom->setEntry($this);
        }

        return $this;
    }

    public function removeSymptom(Symptom $symptom): static
    {
        if ($this->symptoms->removeElement($symptom)) {
            if ($symptom->getEntry() === $this) {
                $symptom->setEntry(null);
            }
        }

        return $this;
    }
}
