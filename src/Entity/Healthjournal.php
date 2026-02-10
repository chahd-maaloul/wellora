<?php

namespace App\Entity;

use App\Repository\HealthjournalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HealthjournalRepository::class)]
class Healthjournal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $datedebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $datefin = null;

    #[ORM\OneToMany(targetEntity: Healthentry::class, mappedBy: "journal")]
    private Collection $entries;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatedebut(): ?\DateTime
    {
        return $this->datedebut;
    }

    public function setDatedebut(\DateTime $datedebut): static
    {
        $this->datedebut = $datedebut;

        return $this;
    }

    public function getDatefin(): ?\DateTime
    {
        return $this->datefin;
    }

    public function setDatefin(\DateTime $datefin): static
    {
        $this->datefin = $datefin;

        return $this;
    }

    /**
     * @return Collection<int, Healthentry>
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }
}
