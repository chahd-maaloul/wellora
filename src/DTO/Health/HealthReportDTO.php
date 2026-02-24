<?php

declare(strict_types=1);

namespace App\DTO\Health;

use App\Entity\Healthentry;

/**
 * Data Transfer Object for Medical Health Reports.
 * Contains all intelligent health analysis data for generating reports.
 */
class HealthReportDTO
{
    /**
     * @param array<Healthentry> $entries
     * @param array<string, string> $scores
     */
    public function __construct(
        public string $patientName = 'Patient',
        
        public float $globalScore = 0.0,
        
        public ?float $predictedGlycemia = null,
        
        public string $riskLevel = 'unknown',
        
        public string $riskLevelLabel = 'Inconnu',
        
        public string $recommendationText = '',
        
        public array $entries = [],
        
        public ?\DateTime $periodStart = null,
        
        public ?\DateTime $periodEnd = null,
        
        public array $scores = [],
        
        public string $reportTitle = 'Rapport de Santé',
        
        public ?\DateTime $generatedAt = null,
        
        public int $totalEntries = 0,
        
        public ?float $avgGlycemia = null,
        
        public ?float $avgBloodPressure = null,
        
        public ?float $avgSleep = null,
        
        public ?float $avgWeight = null,
    ) {
        $this->generatedAt = $this->generatedAt ?? new \DateTime();
    }

    /**
     * Get color class for global score
     */
    public function getScoreColorClass(): string
    {
        return match (true) {
            $this->globalScore >= 80 => 'text-green-600',
            $this->globalScore >= 60 => 'text-blue-600',
            $this->globalScore >= 40 => 'text-yellow-600',
            $this->globalScore >= 20 => 'text-orange-600',
            default => 'text-red-600',
        };
    }

    /**
     * Get background color class for global score
     */
    public function getScoreBgClass(): string
    {
        return match (true) {
            $this->globalScore >= 80 => 'bg-green-100',
            $this->globalScore >= 60 => 'bg-blue-100',
            $this->globalScore >= 40 => 'bg-yellow-100',
            $this->globalScore >= 20 => 'bg-orange-100',
            default => 'bg-red-100',
        };
    }

    /**
     * Get label for global score
     */
    public function getScoreLabel(): string
    {
        return match (true) {
            $this->globalScore >= 80 => 'Excellent',
            $this->globalScore >= 60 => 'Bon',
            $this->globalScore >= 40 => 'Moyen',
            $this->globalScore >= 20 => 'Faible',
            $this->globalScore > 0 => 'Très Faible',
            default => 'N/A',
        };
    }

    /**
     * Get risk level color class
     */
    public function getRiskColorClass(): string
    {
        return match ($this->riskLevel) {
            'excellent', 'stable' => 'text-green-600',
            'warning' => 'text-yellow-600',
            'high_risk' => 'text-red-600',
            default => 'text-gray-600',
        };
    }

    /**
     * Check if prediction is available
     */
    public function hasPrediction(): bool
    {
        return $this->predictedGlycemia !== null;
    }

    /**
     * Get formatted period string
     */
    public function getPeriodString(): string
    {
        if ($this->periodStart === null || $this->periodEnd === null) {
            return 'N/A';
        }

        return sprintf(
            'Du %s au %s',
            $this->periodStart->format('d/m/Y'),
            $this->periodEnd->format('d/m/Y')
        );
    }

    /**
     * Convert entries to array for serialization
     * @return array<int, array<string, mixed>>
     */
    public function entriesToArray(): array
    {
        return array_map(function (Healthentry $entry) {
            return [
                'date' => $entry->getDate()?->format('d/m/Y'),
                'glycemie' => $entry->getGlycemie(),
                'poids' => $entry->getPoids(),
                'tension' => $entry->getTension(),
                'sommeil' => $entry->getSommeil(),
                'symptoms' => array_map(fn($s) => [
                    'type' => $s->getType(),
                    'intensity' => $s->getIntensity(),
                ], $entry->getSymptoms()->toArray()),
            ];
        }, $this->entries);
    }
}
