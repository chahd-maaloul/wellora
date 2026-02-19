<?php

declare(strict_types=1);

namespace App\DTO\Health;

enum HealthRiskTier: string
{
    case EXCELLENT = 'excellent';
    case STABLE = 'stable';
    case WARNING = 'warning';
    case HIGH_RISK = 'high_risk';
    case UNKNOWN = 'unknown';
}
