<?php

namespace App\Entity;

use App\Repository\SymptomRepository;
use App\Entity\Healthentry;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SymptomRepository::class)]
class Symptom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $intensite = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_observation = null;

    #[ORM\ManyToOne(targetEntity: Healthentry::class, inversedBy: "symptoms")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Healthentry $entry = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function setTypeSymptom(string $typeSymptom): static
    {
        return $this->setType($typeSymptom);
    }

    public function getIntensite(): ?int
    {
        return $this->intensite;
    }

    public function setIntensite(int $intensite): static
    {
        $this->intensite = $intensite;

        return $this;
    }

    public function getDateObservation(): ?\DateTime
    {
        return $this->date_observation;
    }

    public function setDateObservation(\DateTime $date_observation): static
    {
        $this->date_observation = $date_observation;

        return $this;
    }

    public function setDateSymptom(\DateTime $dateSymptom): static
    {
        return $this->setDateObservation($dateSymptom);
    }

    public function getEntry(): ?Healthentry
    {
        return $this->entry;
    }

    public function setEntry(?Healthentry $entry): static
    {
        $this->entry = $entry;

        return $this;
    }
}
