<?php

declare(strict_types=1);

namespace App\Service\Health;

use App\DTO\Health\HealthMetricDTO;
use App\DTO\Health\HealthScoreDTO;
use App\DTO\Health\HealthStatisticsDTO;
use App\Entity\Healthentry;
use App\Entity\Healthjournal;
use App\Repository\HealthentryRepository;
use App\Repository\HealthjournalRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class HealthAnalyticsService
{
    private const GLYCEMIA_IDEAL = 1.0; // g/L
    private const SLEEP_TARGET_HOURS = 8;
    
    // Weight configuration (can be customized per patient)
    private const BMI_LOWER_BOUND = 18.5;
    private const BMI_UPPER_BOUND = 25.0;
    private const BMI_ACCEPTABLE_UPPER = 30.0;
    
    // Score weights for global calculation
    private const WEIGHT_GLYCEMIC = 0.25;
    private const WEIGHT_BLOOD_PRESSURE = 0.25;
    private const WEIGHT_SLEEP = 0.20;
    private const WEIGHT_SYMPTOM = 0.15;
    private const WEIGHT_WEIGHT = 0.15;
    
    // Blood pressure thresholds (ideal)
    private const BP_SYSTOLIC_IDEAL_MIN = 90;
    private const BP_SYSTOLIC_IDEAL_MAX = 120;
    private const BP_DIASTOLIC_IDEAL_MIN = 60;
    private const BP_DIASTOLIC_IDEAL_MAX = 80;
    
    // Blood pressure thresholds (acceptable)
    private const BP_SYSTOLIC_ACCEPTABLE_MIN = 70;
    private const BP_SYSTOLIC_ACCEPTABLE_MAX = 130;
    private const BP_DIASTOLIC_ACCEPTABLE_MIN = 50;
    private const BP_DIASTOLIC_ACCEPTABLE_MAX = 85;

    public function __construct(
        private readonly HealthentryRepository $entryRepository,
        private readonly HealthjournalRepository $journalRepository,
        #[Autowire('%health_analytics.default_bmi%')]
        private readonly float $defaultBmi = 23.2,
    ) {}

    /**
     * Get all metrics for a journal
     */
    public function getMetricsForJournal(Healthjournal $journal): HealthMetricDTO
    {
        // First, get ALL entries for this journal
        $entries = $this->entryRepository->findBy(
            ['journal' => $journal],
            ['date' => 'ASC']
        );
        
        // If we have entries, check if we should filter by date range
        $datedebut = $journal->getDatedebut();
        $datefin = $journal->getDatefin();
        
        // If journal has a valid date range in database, filter entries
        if (null !== $datedebut && null !== $datefin) {
            $entries = array_filter($entries, function($entry) use ($datedebut, $datefin) {
                $entryDate = $entry->getDate();
                return $entryDate >= $datedebut && $entryDate <= $datefin;
            });
            $entries = array_values($entries);
        } else {
            // Try to parse date range from journal name (e.g., "mars 2026")
            $dateRange = $this->parseDateRangeFromJournalName($journal->getName());
            if (null !== $dateRange) {
                $entries = array_filter($entries, function($entry) use ($dateRange) {
                    $entryDate = $entry->getDate();
                    return $entryDate >= $dateRange['start'] && $entryDate <= $dateRange['end'];
                });
                $entries = array_values($entries);
            }
        }
        
        return $this->buildMetricsFromEntries($entries);
    }
    
    /**
     * Parse date range from journal name (e.g., "mars 2026", "February 2026")
     * @return array{start: \DateTime, end: \DateTime}|null
     */
    private function parseDateRangeFromJournalName(?string $name): ?array
    {
        if (null === $name) {
            return null;
        }
        
        $frenchMonths = [
            'janvier' => 1, 'février' => 2, 'mars' => 3, 'avril' => 4,
            'mai' => 5, 'juin' => 6, 'juillet' => 7, 'août' => 8,
            'septembre' => 9, 'octobre' => 10, 'novembre' => 11, 'décembre' => 12,
        ];
        
        $englishMonths = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
        ];
        
        $nameLower = strtolower($name);
        $month = null;
        
        // Check French months
        foreach ($frenchMonths as $monthName => $monthNum) {
            if (str_contains($nameLower, $monthName)) {
                $month = $monthNum;
                break;
            }
        }
        
        // Check English months if not found
        if (null === $month) {
            foreach ($englishMonths as $monthName => $monthNum) {
                if (str_contains($nameLower, $monthName)) {
                    $month = $monthNum;
                    break;
                }
            }
        }
        
        // Extract year
        $year = (int) date('Y');
        if (preg_match('/\b(19|20)\d{2}\b/', $name, $matches)) {
            $year = (int) $matches[0];
        }
        
        if (null === $month) {
            return null;
        }
        
        $startDate = new \DateTime(sprintf('%d-%02d-01', $year, $month));
        $endDate = (clone $startDate)->modify('last day of this month');
        
        return ['start' => $startDate, 'end' => $endDate];
    }
    
    /**
     * Get metrics for a specific date range
     */
    public function getMetricsForDateRange(
        Healthjournal $journal,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): HealthMetricDTO {
        $entries = $this->entryRepository->findByJournalAndDateRange(
            $journal,
            $startDate,
            $endDate
        );
        
        return $this->buildMetricsFromEntries($entries);
    }
    
    /**
     * Get metrics for a specific journal by ID
     */
    public function getMetricsForJournalId(int $journalId): HealthMetricDTO
    {
        $journal = $this->journalRepository->find($journalId);
        
        if (null === $journal) {
            return new HealthMetricDTO();
        }
        
        return $this->getMetricsForJournal($journal);
    }
    
    /**
     * Build metrics DTO from entries
     * @param array<Healthentry> $entries
     */
    public function buildMetricsFromEntries(array $entries): HealthMetricDTO
    {
        $glycemia = [];
        $bpSystolic = [];
        $bpDiastolic = [];
        $sleep = [];
        $weight = [];
        $symptomIntensity = [];
        $dates = [];
        
        foreach ($entries as $entry) {
            $glycemia[] = $entry->getGlycemie() ?? 0.0;
            
            $tension = $entry->getTension();
            if (null !== $tension && '' !== $tension) {
                $tensionValue = (float) $tension;
                $bpDiastolic[] = $tensionValue;
                $bpSystolic[] = $tensionValue + 40; // Common approximation
            } else {
                $bpSystolic[] = 0.0;
                $bpDiastolic[] = 0.0;
            }
            
            $sleep[] = $entry->getSommeil() ?? 0;
            $weight[] = $entry->getPoids() ?? 0.0;
            $dates[] = $entry->getDate();
            
            $totalIntensity = $this->calculateSymptomIntensity($entry);
            $symptomIntensity[] = $totalIntensity;
        }
        
        return new HealthMetricDTO(
            glycemia: $glycemia,
            bloodPressureSystolic: $bpSystolic,
            bloodPressureDiastolic: $bpDiastolic,
            sleep: $sleep,
            weight: $weight,
            symptomIntensity: $symptomIntensity,
            dates: $dates,
        );
    }
    
    /**
     * Calculate statistics from metrics
     */
    public function calculateStatistics(HealthMetricDTO $metrics): HealthStatisticsDTO
    {
        if ($metrics->isEmpty()) {
            return new HealthStatisticsDTO();
        }
        
        $glycemia = array_filter($metrics->glycemia, fn($v) => $v > 0);
        $sleep = array_filter($metrics->sleep, fn($v) => $v > 0);
        $weight = array_filter($metrics->weight, fn($v) => $v > 0);
        $bpSystolic = array_filter($metrics->bloodPressureSystolic, fn($v) => $v > 0);
        $bpDiastolic = array_filter($metrics->bloodPressureDiastolic, fn($v) => $v > 0);
        
        return new HealthStatisticsDTO(
            avgGlycemia: !empty($glycemia) ? array_sum($glycemia) / count($glycemia) : 0.0,
            minGlycemia: !empty($glycemia) ? min($glycemia) : 0.0,
            maxGlycemia: !empty($glycemia) ? max($glycemia) : 0.0,
            avgSystolic: !empty($bpSystolic) ? (int) round(array_sum($bpSystolic) / count($bpSystolic)) : 0,
            avgDiastolic: !empty($bpDiastolic) ? (int) round(array_sum($bpDiastolic) / count($bpDiastolic)) : 0,
            avgSleep: !empty($sleep) ? array_sum($sleep) / count($sleep) : 0.0,
            currentWeight: !empty($weight) ? end($weight) : 0.0,
            weightVariation: $this->calculateWeightVariation($weight),
            avgIntensity: !empty($metrics->symptomIntensity) 
                ? round(array_sum($metrics->symptomIntensity) / count($metrics->symptomIntensity))
                : 0,
            totalSymptomIntensity: array_sum($metrics->symptomIntensity),
            startDate: !empty($metrics->dates) ? $metrics->dates[0] : null,
            endDate: !empty($metrics->dates) ? end($metrics->dates) : null,
        );
    }
    
    /**
     * Calculate all scores from metrics
     */
    public function calculateScores(HealthMetricDTO $metrics): HealthScoreDTO
    {
        if ($metrics->isEmpty()) {
            return new HealthScoreDTO();
        }
        
        $stats = $this->calculateStatistics($metrics);
        
        $glycemicScore = $this->calculateGlycemicScore($stats->avgGlycemia);
        $bpScore = $this->calculateBloodPressureScore($stats->avgSystolic, $stats->avgDiastolic);
        $sleepScore = $this->calculateSleepScore($stats->avgSleep);
        $symptomScore = $this->calculateSymptomScore($stats->avgIntensity);
        $weightScore = $this->calculateWeightScore($this->defaultBmi);
        
        $globalScore = $this->calculateGlobalScore(
            $glycemicScore,
            $bpScore,
            $sleepScore,
            $symptomScore,
            $weightScore
        );
        
        $grade = $this->determineGrade($globalScore);
        
        return new HealthScoreDTO(
            glycemicScore: $glycemicScore,
            bloodPressureScore: $bpScore,
            sleepScore: $sleepScore,
            symptomScore: $symptomScore,
            weightScore: $weightScore,
            globalScore: $globalScore,
            globalScoreGrade: $grade,
        );
    }
    
    /**
     * Get comprehensive analytics for a journal
     * @return array{metrics: HealthMetricDTO, statistics: HealthStatisticsDTO, scores: HealthScoreDTO}
     */
    public function getAnalyticsForJournal(Healthjournal $journal): array
    {
        $metrics = $this->getMetricsForJournal($journal);
        
        if ($metrics->isEmpty()) {
            return [
                'metrics' => $metrics,
                'statistics' => new HealthStatisticsDTO(),
                'scores' => new HealthScoreDTO(),
            ];
        }
        
        $statistics = $this->calculateStatistics($metrics);
        $scores = $this->calculateScores($metrics);
        
        return [
            'metrics' => $metrics,
            'statistics' => $statistics,
            'scores' => $scores,
        ];
    }

    // ============================================
    // PRIVATE CALCULATION METHODS
    // ============================================
    
    private function calculateSymptomIntensity(Healthentry $entry): int
    {
        $total = 0;
        foreach ($entry->getSymptoms() as $symptom) {
            // Skip symptoms with null type (not fully filled)
            if (null === $symptom->getType() || '' === $symptom->getType()) {
                continue;
            }
            $total += $symptom->getIntensite() ?? 0;
        }
        return $total;
    }
    
    private function calculateWeightVariation(array $weight): float
    {
        if (count($weight) < 2) {
            return 0.0;
        }
        
        return end($weight) - $weight[0];
    }
    
    private function calculateGlycemicScore(float $avgGlycemia): float
    {
        if ($avgGlycemia <= 0) {
            return 50.0; // Neutral score for missing data
        }
        
        $deviation = abs($avgGlycemia - self::GLYCEMIA_IDEAL);
        $score = max(0.0, min(100.0, 100.0 - ($deviation * 100)));
        
        return round($score, 2);
    }
    
    private function calculateBloodPressureScore(int $systolic, int $diastolic): float
    {
        if ($systolic <= 0 || $diastolic <= 0) {
            return 50.0;
        }
        
        // Ideal range
        if ($systolic >= self::BP_SYSTOLIC_IDEAL_MIN 
            && $systolic <= self::BP_SYSTOLIC_IDEAL_MAX
            && $diastolic >= self::BP_DIASTOLIC_IDEAL_MIN
            && $diastolic <= self::BP_DIASTOLIC_IDEAL_MAX
        ) {
            return 100.0;
        }
        
        // Acceptable range
        if ($systolic >= self::BP_SYSTOLIC_ACCEPTABLE_MIN
            && $systolic <= self::BP_SYSTOLIC_ACCEPTABLE_MAX
            && $diastolic >= self::BP_DIASTOLIC_ACCEPTABLE_MIN
            && $diastolic <= self::BP_DIASTOLIC_ACCEPTABLE_MAX
        ) {
            return 75.0;
        }
        
        return 50.0;
    }
    
    private function calculateSleepScore(float $avgSleep): float
    {
        if ($avgSleep <= 0) {
            return 50.0;
        }
        
        return min(100.0, ($avgSleep / self::SLEEP_TARGET_HOURS) * 100);
    }
    
    private function calculateSymptomScore(float $avgIntensity): float
    {
        if ($avgIntensity <= 0) {
            return 100.0; // No symptoms = perfect score
        }
        
        return max(0.0, 100.0 - ($avgIntensity * 10));
    }
    
    private function calculateWeightScore(float $bmi): float
    {
        if ($bmi >= self::BMI_LOWER_BOUND && $bmi <= self::BMI_UPPER_BOUND) {
            return 100.0;
        }
        
        if ($bmi >= 17.0 && $bmi <= self::BMI_ACCEPTABLE_UPPER) {
            return 75.0;
        }
        
        return 50.0;
    }
    
    private function calculateGlobalScore(
        float $glycemic,
        float $bp,
        float $sleep,
        float $symptom,
        float $weight
    ): float {
        // Step 1: Calculate weighted score
        $weighted = (
            ($glycemic * self::WEIGHT_GLYCEMIC) +
            ($bp * self::WEIGHT_BLOOD_PRESSURE) +
            ($sleep * self::WEIGHT_SLEEP) +
            ($symptom * self::WEIGHT_SYMPTOM) +
            ($weight * self::WEIGHT_WEIGHT)
        );
        
        // Step 2: Apply Clinical Override Rules (Hybrid Medical Scoring Model)
        // Critical Override: If glycemic OR BP is severely compromised, global score is capped
        
        // Rule 1: If glycemicScore < 50 OR bpScore < 50 → global score cannot exceed 59
        if ($glycemic < 50 || $bp < 50) {
            $weighted = min($weighted, 59);
        }
        
        // Rule 2: If glycemicScore < 40 AND bpScore < 40 → global score cannot exceed 49
        if ($glycemic < 40 && $bp < 40) {
            $weighted = min($weighted, 49);
        }
        
        // Step 3: Ensure score stays within valid range (0-100)
        return round(min(max($weighted, 0), 100), 2);
    }
    
    private function determineGrade(float $globalScore): string
    {
        return match (true) {
            $globalScore >= 90 => 'A',
            $globalScore >= 80 => 'B',
            $globalScore >= 70 => 'C',
            $globalScore >= 60 => 'D',
            $globalScore >= 50 => 'E',
            default => 'F',
        };
    }
}
