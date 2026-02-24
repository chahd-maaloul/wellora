<?php

declare(strict_types=1);

namespace App\DTO\Health;

use Symfony\Component\Serializer\Annotation\Groups;

final class HealthScoreDTO
{
    public function __construct(
        #[Groups(['health_scores', 'health_export'])]
        public float $glycemicScore = 0.0,
        
        #[Groups(['health_scores', 'health_export'])]
        public float $bloodPressureScore = 0.0,
        
        #[Groups(['health_scores', 'health_export'])]
        public float $sleepScore = 0.0,
        
        #[Groups(['health_scores', 'health_export'])]
        public float $symptomScore = 0.0,
        
        #[Groups(['health_scores', 'health_export'])]
        public float $weightScore = 0.0,
        
        #[Groups(['health_scores', 'health_export'])]
        public float $globalScore = 0.0,
        
        #[Groups(['health_scores', 'health_export'])]
        public string $globalScoreGrade = 'N/A',
    ) {}
    
    /**
     * Get all individual scores as associative array
     * @return array<string, float|string>
     */
    public function toArray(): array
    {
        return [
            'glycemic' => $this->glycemicScore,
            'bloodPressure' => $this->bloodPressureScore,
            'sleep' => $this->sleepScore,
            'symptom' => $this->symptomScore,
            'weight' => $this->weightScore,
            'global' => $this->globalScore,
            'grade' => $this->globalScoreGrade,
        ];
    }
}
