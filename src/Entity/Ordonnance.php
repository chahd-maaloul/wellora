<?php

namespace App\Entity;

use App\Repository\OrdonnanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrdonnanceRepository::class)]
class Ordonnance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // CHAMPS EXISTANTS
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_ordonnance = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['clinical_note'], message: 'Le nom du medicament est obligatoire.')]
    private ?string $medicament = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['clinical_note'], message: 'Le dosage est obligatoire.')]
    #[Assert\Regex(
        pattern: '/\d/',
        message: 'Le dosage doit contenir au moins un chiffre.',
        groups: ['clinical_note']
    )]
    private ?string $dosage = null;

    #[ORM\Column(length: 255)]
    private ?string $forme = null;

    #[ORM\Column(length: 255)]
    private ?string $duree_traitement = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $instructions = null;

    // NOUVEAUX CHAMPS POUR PRESCRIPTIONS DÉTAILLÉES
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $frequency = null; // 1x/jour, 2x/jour, etc.

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $diagnosis_code = null; // Code diagnostic associé

    // RELATION EXISTANTE
    #[ORM\ManyToOne(inversedBy: 'genere')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Consultation $id_consultation = null;

    public function __construct()
    {
        $this->date_ordonnance = new \DateTime();
    }

    // GETTERS ET SETTERS EXISTANTS
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateOrdonnance(): ?\DateTime
    {
        return $this->date_ordonnance;
    }

    public function setDateOrdonnance(\DateTime $date_ordonnance): static
    {
        $this->date_ordonnance = $date_ordonnance;
        return $this;
    }

    public function getMedicament(): ?string
    {
        return $this->medicament;
    }

    public function setMedicament(string $medicament): static
    {
        $this->medicament = trim(preg_replace('/\s+/', ' ', $medicament));
        return $this;
    }

    public function getDosage(): ?string
    {
        return $this->dosage;
    }

    public function setDosage(string $dosage): static
    {
        $this->dosage = trim(preg_replace('/\s+/', ' ', $dosage));
        return $this;
    }

    public function getForme(): ?string
    {
        return $this->forme;
    }

    public function setForme(string $forme): static
    {
        $this->forme = $forme;
        return $this;
    }

    public function getDureeTraitement(): ?string
    {
        return $this->duree_traitement;
    }

    public function setDureeTraitement(string $duree_traitement): static
    {
        $this->duree_traitement = $duree_traitement;
        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): static
    {
        $this->instructions = $instructions === null ? null : trim(preg_replace('/\s+/', ' ', $instructions));
        return $this;
    }

    public function getIdConsultation(): ?Consultation
    {
        return $this->id_consultation;
    }

    public function setIdConsultation(?Consultation $id_consultation): static
    {
        $this->id_consultation = $id_consultation;
        return $this;
    }

    // NOUVEAUX GETTERS ET SETTERS
    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setFrequency(?string $frequency): static
    {
        $this->frequency = $frequency;
        return $this;
    }

    public function getDiagnosisCode(): ?string
    {
        return $this->diagnosis_code;
    }

    public function setDiagnosisCode(?string $diagnosis_code): static
    {
        $this->diagnosis_code = $diagnosis_code;
        return $this;
    }
}
