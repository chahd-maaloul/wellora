<?php

namespace App\Entity;

use App\Repository\CommentairePublicationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentairePublicationRepository::class)]
class CommentairePublication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateCommentaire = null;

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
}
