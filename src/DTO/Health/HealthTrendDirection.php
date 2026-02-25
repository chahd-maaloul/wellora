<?php

declare(strict_types=1);

namespace App\DTO\Health;

enum HealthTrendDirection: string
{
    case IMPROVING = 'improving';
    case STABLE = 'stable';
    case DETERIORATING = 'deteriorating';
    case UNKNOWN = 'unknown';
}
