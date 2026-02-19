<?php

declare(strict_types=1);

namespace App\Service\Health;

use App\DTO\Health\HealthMetricDTO;
use App\DTO\Health\HealthRiskDTO;
use App\DTO\Health\HealthRiskFactorDTO;
use App\DTO\Health\HealthRiskTier;
use App\DTO\Health\HealthStatisticsDTO;

final class HealthRiskEngineService
{
    // Risk thresholds
    private const GLYCEMIA_HIGH = 1.4; // g/L - concerning level
    private const GLYCEMIA_VERY_HIGH = 1.8; // g/L - dangerous level
    
    private const BP_SYSTOLIC_HIGH = 140;
    private const BP_DIASTOLIC_HIGH = 90;
    
    private const SLEEP_POOR = 5; // hours
    
    private const SYMPTOM_INTENSITY_HIGH = 5;
    private const SYMPTOM_INTENSITY_VERY_HIGH = 8;
    
    // Weights for overall risk calculation
    private const SEVERITY_GLYCEMIA = 0.35;
    private const SEVERITY_BP = 0.30;
    private const SEVERITY_SLEEP = 0.20;
    private const SEVERITY_SYMPTOMS = 0.15;

    public function __construct(
        private readonly HealthAnalyticsService $analyticsService,
    ) {}

    /**
     * Analyze risk based on metrics
     */
    public function analyzeRisk(HealthMetricDTO $metrics): HealthRiskDTO
    {
        if ($metrics->isEmpty()) {
            return new HealthRiskDTO(
                tier: HealthRiskTier::UNKNOWN,
                summary: 'Insufficient data for risk assessment',
            );
        }
        
        $stats = $this->analyticsService->calculateStatistics($metrics);
        
        return $this->performRiskAssessment($stats);
    }
    
    /**
     * Analyze risk based on statistics
     */
    public function analyzeRiskFromStatistics(HealthStatisticsDTO $stats): HealthRiskDTO
    {
        if (null === $stats->startDate) {
            return new HealthRiskDTO(
                tier: HealthRiskTier::UNKNOWN,
                summary: 'Insufficient data for risk assessment',
            );
        }
        
        return $this->performRiskAssessment($stats);
    }
    
    /**
     * Quick risk check for a single entry
     */
    public function quickRiskCheck(
        ?float $glycemia,
        ?int $bloodPressure,
        ?int $sleep,
        int $symptomIntensity
    ): HealthRiskDTO {
        $riskFactors = [];
        $overallScore = 0.0;
        
        // Check glycemia
        if (null !== $glycemia && $glycemia > 0) {
            if ($glycemia >= self::GLYCEMIA_VERY_HIGH) {
                $riskFactors[] = new HealthRiskFactorDTO(
                    name: 'Critical Hyperglycemia',
                    description: sprintf('Very high blood glucose level: %.1f g/L', $glycemia),
                    severity: 1.0,
                    triggeringConditions: ['glycemia >= 1.8 g/L'],
                );
                $overallScore += self::SEVERITY_GLYCEMIA;
            } elseif ($glycemia >= self::GLYCEMIA_HIGH) {
                $riskFactors[] = new HealthRiskFactorDTO(
                    name: 'Elevated Blood Glucose',
                    description: sprintf('High blood glucose level: %.1f g/L', $glycemia),
                    severity: 0.7,
                    triggeringConditions: ['glycemia >= 1.4 g/L'],
                );
                $overallScore += self::SEVERITY_GLYCEMIA * 0.7;
            }
        }
        
        // Check blood pressure
        if (null !== $bloodPressure && $bloodPressure > 0) {
            if ($bloodPressure >= self::BP_SYSTOLIC_HIGH) {
                $riskFactors[] = new HealthRiskFactorDTO(
                    name: 'Hypertension',
                    description: sprintf('Elevated blood pressure: %d mmHg', $bloodPressure),
                    severity: 0.8,
                    triggeringConditions: ['systolic >= 140 mmHg'],
                );
                $overallScore += self::SEVERITY_BP * 0.8;
            }
        }
        
        // Check sleep
        if (null !== $sleep && $sleep < self::SLEEP_POOR) {
            $riskFactors[] = new HealthRiskFactorDTO(
                name: 'Sleep Deprivation',
                description: sprintf('Insufficient sleep: %d hours', $sleep),
                severity: 0.6,
                triggeringConditions: ['sleep < 5 hours'],
            );
            $overallScore += self::SEVERITY_SLEEP;
        }
        
        // Check symptoms
        if ($symptomIntensity >= self::SYMPTOM_INTENSITY_VERY_HIGH) {
            $riskFactors[] = new HealthRiskFactorDTO(
                name: 'Severe Symptom Intensity',
                description: sprintf('High symptom intensity score: %d', $symptomIntensity),
                severity: 0.9,
                triggeringConditions: ['symptom intensity >= 8'],
            );
            $overallScore += self::SEVERITY_SYMPTOMS;
        } elseif ($symptomIntensity >= self::SYMPTOM_INTENSITY_HIGH) {
            $riskFactors[] = new HealthRiskFactorDTO(
                name: 'Elevated Symptom Intensity',
                description: sprintf('Elevated symptom intensity score: %d', $symptomIntensity),
                severity: 0.5,
                triggeringConditions: ['symptom intensity >= 5'],
            );
            $overallScore += self::SEVERITY_SYMPTOMS * 0.5;
        }
        
        return $this->buildRiskDTO($riskFactors, $overallScore);
    }

    // ============================================
    // PRIVATE ASSESSMENT METHODS
    // ============================================
    
    private function performRiskAssessment(HealthStatisticsDTO $stats): HealthRiskDTO
    {
        $riskFactors = [];
        $overallScore = 0.0;
        
        // ========================================
        // GLYCEMIA RISK FACTORS
        // ========================================
        if ($stats->avgGlycemia >= self::GLYCEMIA_VERY_HIGH) {
            $riskFactors[] = new HealthRiskFactorDTO(
                name: 'Chronic Severe Hyperglycemia',
                description: sprintf(
                    'Average blood glucose %.1f g/L is in dangerous range (>1.8 g/L)',
                    $stats->avgGlycemia
                ),
                severity: 1.0,
                triggeringConditions: ['avg glycemia > 1.8 g/L'],
            );
            $overallScore += self::SEVERITY_GLYCEMIA;
        } elseif ($stats->avgGlycemia >= self::GLYCEMIA_HIGH) {
            $riskFactors[] = new HealthRiskFactorDTO(
                name: 'Elevated Average Blood Glucose',
                description: sprintf(
                    'Average blood glucose %.1f g/L is above target (>1.4 g/L)',
                    $stats->avgGlycemia
                ),
                severity: 0.7,
                triggeringConditions: ['avg glycemia > 1.4 g/L'],
            );
            $overallScore += self::SEVERITY_GLYCEMIA * 0.7;
        }
        
        // Glycemia variability risk
        if ($stats->maxGlycemia > 0 && $stats->avgGlycemia > 0) {
            $variability = ($stats->maxGlycemia - $stats->minGlycemia) / $stats->avgGlycemia;
            if ($variability > 0.5) {
                $riskFactors[] = new HealthRiskFactorDTO(
                    name: 'High Blood Glucose Variability',
                    description: sprintf(
                        'Blood glucose swings significantly (variability: %.1f%%)',
                        $variability * 100
                    ),
                    severity: 0.6,
                    triggeringConditions: ['variability > 50%'],
                );
                $overallScore += self::SEVERITY_GLYCEMIA * 0.3;
            }
        }
        
        // ========================================
        // BLOOD PRESSURE RISK FACTORS
        // ========================================
        if ($stats->avgSystolic >= self::BP_SYSTOLIC_HIGH) {
            $riskFactors[] = new HealthRiskFactorDTO(
                name: 'Hypertension',
                description: sprintf(
                    'Average systolic BP %d mmHg indicates hypertension (>=140 mmHg)',
                    $stats->avgSystolic
                ),
                severity: 0.8,
                triggeringConditions: ['avg systolic >= 140 mmHg'],
            );
            $overallScore += self::SEVERITY_BP;
        }
        
        // Metabolic + Cardiovascular combined risk
        if ($stats->avgGlycemia >= self::GLYCEMIA_HIGH && $stats->avgSystolic >= 130) {
            $riskFactors[] = new HealthRiskFactorDTO(
                name: 'Metabolic-Cardiovascular Risk',
                description: 'Combined elevated blood glucose and blood pressure indicate metabolic syndrome',
                severity: 0.9,
                triggeringConditions: ['high glycemia + high BP'],
            );
            $overallScore += self::SEVERITY_BP * 0.5;
        }
        
        // ========================================
        // SLEEP RISK FACTORS
        // ========================================
        if ($stats->avgSleep < self::SLEEP_POOR && $stats->avgSleep > 0) {
            $riskFactors[] = new HealthRiskFactorDTO(
                name: 'Chronic Sleep Deprivation',
                description: sprintf(
                    'Average sleep %.1f hours is below healthy threshold (<5 hours)',
                    $stats->avgSleep
                ),
                severity: 0.7,
                triggeringConditions: ['avg sleep < 5 hours'],
            );
            $overallScore += self::SEVERITY_SLEEP;
        }
        
        // Poor sleep + high blood pressure risk
        if ($stats->avgSleep < 6 && $stats->avgSystolic >= self::BP_SYSTOLIC_HIGH) {
            $riskFactors[] = new HealthRiskFactorDTO(
                name: 'Cardiovascular Stress',
                description: 'Poor sleep combined with hypertension increases cardiovascular risk significantly',
                severity: 0.85,
                triggeringConditions: ['poor sleep + high BP'],
            );
            $overallScore += self::SEVERITY_SLEEP * 0.5;
        }
        
        // ========================================
        // SYMPTOM RISK FACTORS
        // ========================================
        if ($stats->avgIntensity >= self::SYMPTOM_INTENSITY_VERY_HIGH) {
            $riskFactors[] = new HealthRiskFactorDTO(
                name: 'High Symptom Burden',
                description: sprintf(
                    'Average symptom intensity %.1f indicates severe symptom load',
                    $stats->avgIntensity
                ),
                severity: 0.9,
                triggeringConditions: ['avg intensity >= 8'],
            );
            $overallScore += self::SEVERITY_SYMPTOMS;
        } elseif ($stats->avgIntensity >= self::SYMPTOM_INTENSITY_HIGH) {
            $riskFactors[] = new HealthRiskFactorDTO(
                name: 'Moderate Symptom Load',
                description: sprintf(
                    'Average symptom intensity %.1f indicates elevated symptom load',
                    $stats->avgIntensity
                ),
                severity: 0.5,
                triggeringConditions: ['avg intensity >= 5'],
            );
            $overallScore += self::SEVERITY_SYMPTOMS * 0.5;
        }
        
        // High symptoms + poor sleep
        if ($stats->avgIntensity >= self::SYMPTOM_INTENSITY_HIGH && $stats->avgSleep < 6) {
            $riskFactors[] = new HealthRiskFactorDTO(
                name: 'Exhaustion Risk',
                description: 'High symptom intensity combined with poor sleep indicates severe exhaustion',
                severity: 0.8,
                triggeringConditions: ['high symptoms + poor sleep'],
            );
            $overallScore += self::SEVERITY_SYMPTOMS * 0.3;
        }
        
        return $this->buildRiskDTO($riskFactors, $overallScore);
    }
    
    /**
     * @param array<HealthRiskFactorDTO> $riskFactors
     */
    private function buildRiskDTO(array $riskFactors, float $overallScore): HealthRiskDTO
    {
        // Determine tier based on score and factors
        $tier = match (true) {
            $overallScore >= 0.7 => HealthRiskTier::HIGH_RISK,
            $overallScore >= 0.4 => HealthRiskTier::WARNING,
            $overallScore >= 0.1 => HealthRiskTier::STABLE,
            $overallScore > 0 => HealthRiskTier::STABLE,
            default => HealthRiskTier::EXCELLENT,
        };
        
        // Check for immediate attention
        $requiresImmediate = false;
        foreach ($riskFactors as $factor) {
            if ($factor->severity >= 0.9) {
                $requiresImmediate = true;
                break;
            }
        }
        
        // Generate summary
        $summary = $this->generateSummary($tier, $riskFactors, $overallScore);
        
        // Generate recommendations
        $recommendations = $this->generateRecommendations($riskFactors);
        
        return new HealthRiskDTO(
            tier: $tier,
            riskFactors: $riskFactors,
            overallRiskScore: round($overallScore, 3),
            summary: $summary,
            recommendations: $recommendations,
            requiresImmediateAttention: $requiresImmediate,
        );
    }
    
    /**
     * @param array<HealthRiskFactorDTO> $riskFactors
     */
    private function generateSummary(
        HealthRiskTier $tier,
        array $riskFactors,
        float $score
    ): string {
        $count = count($riskFactors);
        
        return match ($tier) {
            HealthRiskTier::EXCELLENT => 'Your health metrics are within excellent ranges. Keep up the good work!',
            HealthRiskTier::STABLE => sprintf(
                'Your health status is stable with %d minor factor%s to monitor.',
                $count,
                $count !== 1 ? 's' : ''
            ),
            HealthRiskTier::WARNING => sprintf(
                'Attention needed: %d risk factor%s require monitoring. Consider lifestyle adjustments.',
                $count,
                $count !== 1 ? 's' : ''
            ),
            HealthRiskTier::HIGH_RISK => sprintf(
                'High risk detected: %d serious factor%s require immediate attention. Please consult a healthcare professional.',
                $count,
                $count !== 1 ? 's' : ''
            ),
            HealthRiskTier::UNKNOWN => 'Unable to assess risk due to insufficient data.',
        };
    }
    
    /**
     * @param array<HealthRiskFactorDTO> $riskFactors
     * @return array<int, string>
     */
    private function generateRecommendations(array $riskFactors): array
    {
        $recommendations = [];
        $addedRecommendations = [];
        
        foreach ($riskFactors as $factor) {
            $recommendation = match ($factor->name) {
                'Critical Hyperglycemia', 'Chronic Severe Hyperglycemia', 'Elevated Average Blood Glucose' =>
                    'Monitor blood glucose levels more frequently and consider dietary adjustments. Consult your doctor about medication review.',
                
                'High Blood Glucose Variability' =>
                    'Identify triggers for blood glucose swings. Maintain consistent meal timing and carbohydrate intake.',
                
                'Hypertension' =>
                    'Reduce sodium intake, increase physical activity, and monitor blood pressure regularly. Consider stress management techniques.',
                
                'Metabolic-Cardiovascular Risk' =>
                    'Urgent: Combined elevated glucose and blood pressure require comprehensive lifestyle changes and medical supervision.',
                
                'Chronic Sleep Deprivation' =>
                    'Prioritize sleep hygiene. Aim for 7-9 hours of quality sleep. Avoid screens before bedtime.',
                
                'Cardiovascular Stress' =>
                    'Address both sleep quality and blood pressure. This combination significantly increases cardiovascular risk.',
                
                'High Symptom Burden', 'Severe Symptom Intensity', 'Elevated Symptom Load' =>
                    'Keep a detailed symptom diary and discuss patterns with your healthcare provider.',
                
                'Exhaustion Risk' =>
                    'Rest is essential. Consider taking time off work and prioritizing recovery. Seek medical support if persistent.',
                
                default => 'Consult with your healthcare provider for personalized advice.',
            };
            
            if (!in_array($recommendation, $addedRecommendations)) {
                $recommendations[] = $recommendation;
                $addedRecommendations[] = $recommendation;
            }
        }
        
        // Add general recommendation if none specific
        if (empty($recommendations)) {
            $recommendations[] = 'Maintain current healthy habits and continue regular health monitoring.';
        }
        
        return $recommendations;
    }
}
