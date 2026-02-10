<?php

namespace App\Entity;

use App\Repository\PublicationParcoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[Assert\NotBlank(message: 'The ambiance rating is required')]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: 'The ambiance rating must be between 1 and 5'
    )]
    private ?int $ambiance = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'The security rating is required')]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: 'The security rating must be between 1 and 5'
    )]
    private ?int $securite = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'The publication date is required')]
    #[Assert\LessThanOrEqual(
        value: 'today',
        message: 'The publication date cannot be in the future'
    )]
    private ?\DateTime $datePublication = null;

    #[ORM\ManyToOne(inversedBy: 'PublicationParcours')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'The parcours is required')]
    private ?ParcoursDeSante $ParcoursDeSante = null;

    /**
     * @var Collection<int, CommentairePublication>
     */
    #[ORM\OneToMany(targetEntity: CommentairePublication::class, mappedBy: 'PublicationParcours')]
    private Collection $commentairePublications;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'The publication text is required')]
    #[Assert\Length(
        min: 10,
        max: 5000,
        minMessage: 'The text must be at least 10 characters',
        maxMessage: 'The text cannot exceed 5000 characters'
    )]
    private ?string $textPublication = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'The experience is required')]
    #[Assert\Choice(
        choices: ['Bad', 'good', 'excellent'],
        message: 'The experience must be one of: Bad, good, excellent'
    )]
    private ?string $experience = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'The publication type is required')]
    #[Assert\Choice(
        choices: ['opinion', 'event'],
        message: 'The publication type must be one of: opinion, event'
    )]
    private ?string $typePublication = null;

    public function __construct()
    {
        $this->commentairePublications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImagePublication(): ?string
    {
        return $this->imagePublication;
    }

    public function setImagePublication(?string $imagePublication): static
    {
        $this->imagePublication = $imagePublication;

        return $this;
    }

    public function getAmbiance(): ?int
    {
        return $this->ambiance;
    }

    public function setAmbiance(?int $ambiance): static
    {
        $this->ambiance = $ambiance;

        return $this;
    }

    public function getSecurite(): ?int
    {
        return $this->securite;
    }

    public function setSecurite(?int $securite): static
    {
        $this->securite = $securite;

        return $this;
    }

    public function getDatePublication(): ?\DateTime
    {
        return $this->datePublication;
    }

    public function setDatePublication(?\DateTime $datePublication): static
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function getParcoursDeSante(): ?ParcoursDeSante
    {
        return $this->ParcoursDeSante;
    }

    public function setParcoursDeSante(?ParcoursDeSante $ParcoursDeSante): static
    {
        $this->ParcoursDeSante = $ParcoursDeSante;

        return $this;
    }

    /**
     * @return Collection<int, CommentairePublication>
     */
    public function getCommentairePublications(): Collection
    {
        return $this->commentairePublications;
    }

    public function addCommentairePublication(CommentairePublication $commentairePublication): static
    {
        if (!$this->commentairePublications->contains($commentairePublication)) {
            $this->commentairePublications->add($commentairePublication);
            $commentairePublication->setPublicationParcours($this);
        }

        return $this;
    }

    public function removeCommentairePublication(CommentairePublication $commentairePublication): static
    {
        if ($this->commentairePublications->removeElement($commentairePublication)) {
            // set the owning side to null (unless already changed)
            if ($commentairePublication->getPublicationParcours() === $this) {
                $commentairePublication->setPublicationParcours(null);
            }
        }

        return $this;
    }

    public function getTextPublication(): ?string
    {
        return $this->textPublication;
    }

    public function setTextPublication(?string $textPublication): static
    {
        $this->textPublication = $textPublication;

        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(?string $experience): static
    {
        $this->experience = $experience;

        return $this;
    }

    public function getTypePublication(): ?string
    {
        return $this->typePublication;
    }

    public function setTypePublication(?string $typePublication): static
    {
        $this->typePublication = $typePublication;

        return $this;
    }
}
