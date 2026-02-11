<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'water_log')]
#[ORM\UniqueConstraint(name: 'unique_log_date', columns: ['log_date'])]
class WaterLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $logDate;

    #[ORM\Column(type: 'integer')]
    private int $glasses;

    #[ORM\ManyToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: true)]
    private ?Patient $patient = null;

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogDate(): \DateTimeInterface
    {
        return $this->logDate;
    }

    public function setLogDate(\DateTimeInterface $logDate): self
    {
        $this->logDate = $logDate;
        return $this;
    }

    public function getGlasses(): int
    {
        return $this->glasses;
    }

    public function setGlasses(int $glasses): self
    {
        $this->glasses = $glasses;
        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;
        return $this;
    }
}
