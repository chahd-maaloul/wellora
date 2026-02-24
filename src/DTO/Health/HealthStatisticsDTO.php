<?php

declare(strict_types=1);

namespace App\DTO\Health;

use Symfony\Component\Serializer\Annotation\Groups;

final class HealthStatisticsDTO
{
    public function __construct(
        // Glycemia statistics
        #[Groups(['health_stats', 'health_export'])]
        public float $avgGlycemia = 0.0,
        
        #[Groups(['health_stats', 'health_export'])]
        public float $minGlycemia = 0.0,
        
        #[Groups(['health_stats', 'health_export'])]
        public float $maxGlycemia = 0.0,
        
        // Blood pressure statistics
        #[Groups(['health_stats', 'health_export'])]
        public int $avgSystolic = 0,
        
        #[Groups(['health_stats', 'health_export'])]
        public int $avgDiastolic = 0,
        
        // Sleep statistics
        #[Groups(['health_stats', 'health_export'])]
        public float $avgSleep = 0.0,
        
        // Weight statistics
        #[Groups(['health_stats', 'health_export'])]
        public float $currentWeight = 0.0,
        
        #[Groups(['health_stats', 'health_export'])]
        public float $weightVariation = 0.0,
        
        // Symptom statistics
        #[Groups(['health_stats', 'health_export'])]
        public float $avgIntensity = 0.0,
        
        #[Groups(['health_stats', 'health_export'])]
        public int $totalSymptomIntensity = 0,
        
        // Date range
        #[Groups(['health_stats', 'health_export'])]
        public ?\DateTimeInterface $startDate = null,
        
        #[Groups(['health_stats', 'health_export'])]
        public ?\DateTimeInterface $endDate = null,
    ) {}
}
