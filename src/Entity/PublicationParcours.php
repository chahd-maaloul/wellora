<?php

namespace App\Entity;

use App\Repository\PublicationParcoursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicationParcoursRepository::class)]
class PublicationParcours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $imagePublication = null;

    #[ORM\Column]
    private ?int $ambiance = null;

    #[ORM\Column]
    private ?int $securite = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $datePublication = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImagePublication(): ?string
    {
        return $this->imagePublication;
    }

    public function setImagePublication(string $imagePublication): static
    {
        $this->imagePublication = $imagePublication;

        return $this;
    }

    public function getAmbiance(): ?int
    {
        return $this->ambiance;
    }

    public function setAmbiance(int $ambiance): static
    {
        $this->ambiance = $ambiance;

        return $this;
    }

    public function getSecurite(): ?int
    {
        return $this->securite;
    }

    public function setSecurite(int $securite): static
    {
        $this->securite = $securite;

        return $this;
    }

    public function getDatePublication(): ?\DateTime
    {
        return $this->datePublication;
    }

    public function setDatePublication(\DateTime $datePublication): static
    {
        $this->datePublication = $datePublication;

        return $this;
    }
}
