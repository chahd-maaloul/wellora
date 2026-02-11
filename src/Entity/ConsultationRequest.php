<?php

namespace App\Entity;

use App\Repository\ConsultationRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultationRequestRepository::class)]
#[ORM\Table(name: 'consultation_request')]
class ConsultationRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Nutritionist::class)]
    #[ORM\JoinColumn(name: 'nutritionist_id', referencedColumnName: 'id', nullable: false)]
    private Nutritionist $nutritionist;

    #[ORM\Column(type: 'string', length: 255)]
    private string $patientName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $patientEmail = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $requestedAt;

    #[ORM\Column(type: 'integer')]
    private int $durationMinutes;

    #[ORM\Column(type: 'string', length: 255)]
    private string $status;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNutritionist(): Nutritionist
    {
        return $this->nutritionist;
    }

    public function setNutritionist(Nutritionist $nutritionist): self
    {
        $this->nutritionist = $nutritionist;
        return $this;
    }

    public function getPatientName(): string
    {
        return $this->patientName;
    }

    public function setPatientName(string $patientName): self
    {
        $this->patientName = $patientName;
        return $this;
    }

    public function getPatientEmail(): ?string
    {
        return $this->patientEmail;
    }

    public function setPatientEmail(?string $patientEmail): self
    {
        $this->patientEmail = $patientEmail;
        return $this;
    }

    public function getRequestedAt(): \DateTimeInterface
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeInterface $requestedAt): self
    {
        $this->requestedAt = $requestedAt;
        return $this;
    }

    public function getDurationMinutes(): int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(int $durationMinutes): self
    {
        $this->durationMinutes = $durationMinutes;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
