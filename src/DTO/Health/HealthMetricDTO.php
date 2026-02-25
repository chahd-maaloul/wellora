<?php

declare(strict_types=1);

namespace App\DTO\Health;

use Symfony\Component\Serializer\Annotation\Groups;

final class HealthMetricDTO
{
    /**
     * @param array<int, float> $glycemia
     * @param array<int, float> $bloodPressureSystolic
     * @param array<int, float> $bloodPressureDiastolic
     * @param array<int, float> $sleep
     * @param array<int, float> $weight
     * @param array<int, int> $symptomIntensity
     * @param array<int, \DateTimeInterface> $dates
     */
    public function __construct(
        #[Groups(['health_metrics', 'health_export'])]
        public array $glycemia = [],
        
        #[Groups(['health_metrics', 'health_export'])]
        public array $bloodPressureSystolic = [],
        
        #[Groups(['health_metrics', 'health_export'])]
        public array $bloodPressureDiastolic = [],
        
        #[Groups(['health_metrics', 'health_export'])]
        public array $sleep = [],
        
        #[Groups(['health_metrics', 'health_export'])]
        public array $weight = [],
        
        #[Groups(['health_metrics', 'health_export'])]
        public array $symptomIntensity = [],
        
        #[Groups(['health_metrics', 'health_export'])]
        public array $dates = [],
    ) {}
    
    public function isEmpty(): bool
    {
        return empty($this->glycemia) 
            && empty($this->bloodPressureSystolic)
            && empty($this->sleep)
            && empty($this->weight)
            && empty($this->symptomIntensity);
    }
    
    public function count(): int
    {
        return count($this->dates);
    }
}
