<?php

namespace App\Entity;

use App\Repository\CommentairePublicationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentairePublicationRepository::class)]
class CommentairePublication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Comment cannot be empty.')]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateCommentaire = null;

    #[ORM\ManyToOne(inversedBy: 'commentairePublications')]
    private ?PublicationParcours $PublicationParcours = null;

    #[ORM\ManyToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(name: 'owner_patient_uuid', referencedColumnName: 'uuid', nullable: true, onDelete: 'SET NULL')]
    private ?Patient $ownerPatient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getDateCommentaire(): ?\DateTime
    {
        return $this->dateCommentaire;
    }

    public function setDateCommentaire(\DateTime $dateCommentaire): static
    {
        $this->dateCommentaire = $dateCommentaire;

        return $this;
    }

    public function getPublicationParcours(): ?PublicationParcours
    {
        return $this->PublicationParcours;
    }

    public function setPublicationParcours(?PublicationParcours $PublicationParcours): static
    {
        $this->PublicationParcours = $PublicationParcours;

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
}
