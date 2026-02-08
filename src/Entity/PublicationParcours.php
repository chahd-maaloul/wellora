<?php

namespace App\Entity;

use App\Repository\PublicationParcoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\ManyToOne(inversedBy: 'PublicationParcours')]
    private ?ParcoursDeSante $ParcoursDeSante = null;

    /**
     * @var Collection<int, CommentairePublication>
     */
    #[ORM\OneToMany(targetEntity: CommentairePublication::class, mappedBy: 'PublicationParcours')]
    private Collection $commentairePublications;

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
}
