<?php

namespace App\Entity;

use App\Repository\ParcoursDeSanteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParcoursDeSanteRepository::class)]
class ParcoursDeSante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The parcours name is required')]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'The name must be at least 5 characters',
        maxMessage: 'The name cannot exceed 255 characters'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s]+$/',
        message: 'The name can only contain letters and spaces'
    )]
    private ?string $nomParcours = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The location is required')]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'The location must be at least 5 characters',
        maxMessage: 'The location cannot exceed 255 characters'
    )]
    private ?string $localisationParcours = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: 'Latitude is required')]
    #[Assert\Range(
        min: -90,
        max: 90,
        notInRangeMessage: 'Latitude must be between -90 and 90'
    )]
    private ?float $latitudeParcours = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: 'Longitude is required')]
    #[Assert\Range(
        min: -180,
        max: 180,
        notInRangeMessage: 'Longitude must be between -180 and 180'
    )]
    private ?float $longitudeParcours = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'The distance is required')]
    #[Assert\Range(
        min: 0,
        max: 20,
        notInRangeMessage: 'The distance must be between 0 and 20 km'
    )]
    private ?float $distanceParcours = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'The creation date is required')]
    #[Assert\Type('DateTime', message: 'The date must be a valid date')]
    #[Assert\LessThanOrEqual(
        value: 'today',
        message: 'The creation date cannot be in the future'
    )]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageParcours = null;

    #[ORM\ManyToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(name: 'owner_patient_uuid', referencedColumnName: 'uuid', nullable: true, onDelete: 'SET NULL')]
    private ?Patient $ownerPatient = null;

    /**
     * @var Collection<int, PublicationParcours>
     */
    #[ORM\OneToMany(targetEntity: PublicationParcours::class, mappedBy: 'ParcoursDeSante')]
    private Collection $PublicationParcours;

    public function __construct()
    {
        $this->PublicationParcours = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomParcours(): ?string
    {
        return $this->nomParcours;
    }

    public function setNomParcours(?string $nomParcours): static
    {
        $this->nomParcours = $nomParcours;

        return $this;
    }

    public function getLocalisationParcours(): ?string
    {
        return $this->localisationParcours;
    }

    public function setLocalisationParcours(?string $localisationParcours): static
    {
        $this->localisationParcours = $localisationParcours;

        return $this;
    }

    public function getDistanceParcours(): ?float
    {
        return $this->distanceParcours;
    }

    public function setDistanceParcours(?float $distanceParcours): static
    {
        $this->distanceParcours = $distanceParcours;

        return $this;
    }

    public function getLatitudeParcours(): ?float
    {
        return $this->latitudeParcours;
    }

    public function setLatitudeParcours(?float $latitudeParcours): static
    {
        $this->latitudeParcours = $latitudeParcours;

        return $this;
    }

    public function getLongitudeParcours(): ?float
    {
        return $this->longitudeParcours;
    }

    public function setLongitudeParcours(?float $longitudeParcours): static
    {
        $this->longitudeParcours = $longitudeParcours;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getImageParcours(): ?string
    {
        return $this->imageParcours;
    }

    public function setImageParcours(?string $imageParcours): static
    {
        $this->imageParcours = $imageParcours;

        return $this;
    }

    public function getOwnerPatient(): ?Patient
    {
        return $this->ownerPatient;
    }

    public function setOwnerPatient(?Patient $ownerPatient): static
    {
        $this->ownerPatient = $ownerPatient;

        return $this;
    }

    /**
     * @return Collection<int, PublicationParcours>
     */
    public function getPublicationParcours(): Collection
    {
        return $this->PublicationParcours;
    }

    public function addPublicationParcour(PublicationParcours $publicationParcour): static
    {
        if (!$this->PublicationParcours->contains($publicationParcour)) {
            $this->PublicationParcours->add($publicationParcour);
            $publicationParcour->setParcoursDeSante($this);
        }

        return $this;
    }

    public function removePublicationParcour(PublicationParcours $publicationParcour): static
    {
        if ($this->PublicationParcours->removeElement($publicationParcour)) {
            // set the owning side to null (unless already changed)
            if ($publicationParcour->getParcoursDeSante() === $this) {
                $publicationParcour->setParcoursDeSante(null);
            }
        }

        return $this;
    }
}
