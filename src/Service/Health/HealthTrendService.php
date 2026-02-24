<?php

declare(strict_types=1);

namespace App\Service\Health;

use App\DTO\Health\HealthMetricDTO;
use App\DTO\Health\HealthScoreDTO;
use App\DTO\Health\HealthTrendDTO;
use App\DTO\Health\HealthTrendDirection;
use App\Entity\Healthjournal;
use App\Entity\User;
use App\Repository\HealthjournalRepository;
use App\Repository\HealthentryRepository;
use Symfony\Bundle\SecurityBundle\Security;

final class HealthTrendService
{
    private const EVOLUTION_IMPROVEMENT_THRESHOLD = 5.0; // % points
    private const EVOLUTION_DETERIORATION_THRESHOLD = -5.0; // % points

    public function __construct(
        private readonly HealthjournalRepository $journalRepository,
        private readonly HealthentryRepository $entryRepository,
        private readonly HealthAnalyticsService $analyticsService,
        private readonly ?Security $security = null
    ) {}

    /**
     * Compare current journal with previous journal
     * 
     * @param User|null $user The current user for filtering journals
     */
    public function compareWithPrevious(?Healthjournal $currentJournal, ?User $user = null): HealthTrendDTO
    {
        if (null === $currentJournal) {
            return new HealthTrendDTO();
        }
        
        // Get all journals for the user sorted by date
        $queryBuilder = $this->journalRepository->createQueryBuilder('j')
            ->orderBy('j.datedebut', 'ASC');
        
        if ($user) {
            $queryBuilder->andWhere('j.user = :user')
                ->setParameter('user', $user);
        }
        
        $allJournals = $queryBuilder->getQuery()->getResult();
        
        if (empty($allJournals)) {
            return new HealthTrendDTO();
        }
        
        // Find current journal index
        $currentIndex = $this->findJournalIndex($allJournals, $currentJournal);
        
        if (false === $currentIndex) {
            return new HealthTrendDTO();
        }
        
        // Get current metrics and scores
        $currentMetrics = $this->analyticsService->getMetricsForJournal($currentJournal);
        
        if ($currentMetrics->isEmpty()) {
            return new HealthTrendDTO();
        }
        
        $currentScores = $this->analyticsService->calculateScores($currentMetrics);
        
        // No previous journal to compare
        if ($currentIndex <= 0) {
            return new HealthTrendDTO(
                currentScore: $currentScores,
                hasPreviousData: false,
                globalDirection: HealthTrendDirection::UNKNOWN,
            );
        }
        
        // Get previous journal
        $previousJournal = $allJournals[$currentIndex - 1];
        $previousMetrics = $this->analyticsService->getMetricsForJournal($previousJournal);
        
        if ($previousMetrics->isEmpty()) {
            return new HealthTrendDTO(
                currentScore: $currentScores,
                hasPreviousData: false,
                globalDirection: HealthTrendDirection::UNKNOWN,
            );
        }
        
        $previousScores = $this->analyticsService->calculateScores($previousMetrics);
        
        // Calculate evolutions
        $globalEvolution = $this->calculateEvolution(
            $previousScores->globalScore,
            $currentScores->globalScore
        );
        
        $metricEvolutions = $this->calculateMetricEvolutions(
            $previousMetrics,
            $currentMetrics
        );
        
        $direction = $this->determineDirection($globalEvolution);
        
        return new HealthTrendDTO(
            currentScore: $currentScores,
            previousScore: $previousScores,
            globalEvolutionPercentage: $globalEvolution,
            globalDirection: $direction,
            metricEvolutions: $metricEvolutions,
            hasPreviousData: true,
            previousPeriodStart: $previousJournal->getDatedebut(),
            previousPeriodEnd: $previousJournal->getDatefin(),
        );
    }
    
    /**
     * Compare current journal with the one from same period last year
     */
    public function compareYearOverYear(?Healthjournal $currentJournal): HealthTrendDTO
    {
        if (null === $currentJournal) {
            return new HealthTrendDTO();
        }
        
        $currentStart = $currentJournal->getDatedebut();
        if (null === $currentStart) {
            return new HealthTrendDTO();
        }
        
        // Find journal from same month last year
        $lastYearStart = (new \DateTime())->setTimestamp($currentStart->getTimestamp())
            ->modify('-1 year');
        
        $lastYearJournal = $this->journalRepository->createQueryBuilder('j')
            ->where('j.datedebut >= :start')
            ->andWhere('j.datedebut < :end')
            ->setParameter('start', $lastYearStart->format('Y-m-01'))
            ->setParameter('end', $lastYearStart->format('Y-m-01 +1 month'))
            ->getQuery()
            ->getOneOrNullResult();
        
        if (null === $lastYearJournal) {
            return $this->compareWithPrevious($currentJournal);
        }
        
        // Calculate year-over-year comparison
        $currentMetrics = $this->analyticsService->getMetricsForJournal($currentJournal);
        $previousMetrics = $this->analyticsService->getMetricsForJournal($lastYearJournal);
        
        if ($currentMetrics->isEmpty() || $previousMetrics->isEmpty()) {
            return new HealthTrendDTO();
        }
        
        $currentScores = $this->analyticsService->calculateScores($currentMetrics);
        $previousScores = $this->analyticsService->calculateScores($previousMetrics);
        
        $globalEvolution = $this->calculateEvolution(
            $previousScores->globalScore,
            $currentScores->globalScore
        );
        
        return new HealthTrendDTO(
            currentScore: $currentScores,
            previousScore: $previousScores,
            globalEvolutionPercentage: $globalEvolution,
            globalDirection: $this->determineDirection($globalEvolution),
            hasPreviousData: true,
            previousPeriodStart: $lastYearJournal->getDatedebut(),
            previousPeriodEnd: $lastYearJournal->getDatefin(),
        );
    }
    
    /**
     * Get trend summary for multiple journals (last N journals)
     * @return array<int, array{journal: Healthjournal, scores: HealthScoreDTO, metrics: HealthMetricDTO}>
     */
    public function getTrendSummary(?Healthjournal $currentJournal, int $maxJournals = 3): array
    {
        $allJournals = $this->journalRepository->findBy(
            [],
            ['datedebut' => 'ASC']
        );
        
        if (empty($allJournals)) {
            return [];
        }
        
        $currentIndex = $currentJournal 
            ? $this->findJournalIndex($allJournals, $currentJournal) 
            : count($allJournals) - 1;
        
        if (false === $currentIndex) {
            return [];
        }
        
        // Get last N journals up to current
        $startIndex = max(0, $currentIndex - $maxJournals + 1);
        $journalsToAnalyze = array_slice($allJournals, $startIndex, $currentIndex - $startIndex + 1);
        
        $trendData = [];
        
        foreach ($journalsToAnalyze as $journal) {
            $metrics = $this->analyticsService->getMetricsForJournal($journal);
            
            if ($metrics->isEmpty()) {
                continue;
            }
            
            $scores = $this->analyticsService->calculateScores($metrics);
            $stats = $this->analyticsService->calculateStatistics($metrics);
            
            $trendData[] = [
                'journal' => $journal,
                'scores' => $scores,
                'statistics' => $stats,
                'metrics' => $metrics,
            ];
        }
        
        return $trendData;
    }

    // PRIVATE HELPER METHODS
    // ============================================
    
    /**
     * @param array<int, Healthjournal> $journals
     */
    private function findJournalIndex(array $journals, Healthjournal $target): int|false
    {
        foreach ($journals as $index => $journal) {
            if ($journal->getId() === $target->getId()) {
                return $index;
            }
        }
        return false;
    }
    
    private function calculateEvolution(float $previous, float $current): float
    {
        if ($previous <= 0) {
            return 0.0;
        }
        
        return round((($current - $previous) / $previous) * 100, 2);
    }
    
    private function determineDirection(float $evolutionPercentage): HealthTrendDirection
    {
        if ($evolutionPercentage > self::EVOLUTION_IMPROVEMENT_THRESHOLD) {
            return HealthTrendDirection::IMPROVING;
        }
        
        if ($evolutionPercentage < self::EVOLUTION_DETERIORATION_THRESHOLD) {
            return HealthTrendDirection::DETERIORATING;
        }
        
        return HealthTrendDirection::STABLE;
    }
    
    private function calculateMetricEvolutions(
        HealthMetricDTO $previous,
        HealthMetricDTO $current
    ): array {
        $evolutions = [];
        
        // Glycemia evolution
        $evolutions['glycemia'] = $this->calculateMetricEvolution(
            $previous->glycemia,
            $current->glycemia,
            lowerIsBetter: true
        );
        
        // Blood pressure evolution
        $evolutions['bloodPressure'] = $this->calculateMetricEvolution(
            $previous->bloodPressureSystolic,
            $current->bloodPressureSystolic,
            lowerIsBetter: true
        );
        
        // Sleep evolution
        $evolutions['sleep'] = $this->calculateMetricEvolution(
            $previous->sleep,
            $current->sleep,
            lowerIsBetter: false
        );
        
        // Weight evolution
        $evolutions['weight'] = $this->calculateMetricEvolution(
            $previous->weight,
            $current->weight,
            lowerIsBetter: true
        );
        
        // Symptom evolution (lower is better)
        $evolutions['symptoms'] = $this->calculateMetricEvolution(
            $previous->symptomIntensity,
            $current->symptomIntensity,
            lowerIsBetter: true
        );
        
        return $evolutions;
    }
    
    /**
     * @param array<int, float|int> $previous
     * @param array<int, float|int> $current
     */
    private function calculateMetricEvolution(
        array $previous,
        array $current,
        bool $lowerIsBetter
    ): ?float {
        $previousFiltered = array_filter($previous, fn($v) => $v > 0);
        $currentFiltered = array_filter($current, fn($v) => $v > 0);
        
        if (empty($previousFiltered) || empty($currentFiltered)) {
            return null;
        }
        
        $previousAvg = array_sum($previousFiltered) / count($previousFiltered);
        $currentAvg = array_sum($currentFiltered) / count($currentFiltered);
        
        if ($previousAvg <= 0) {
            return null;
        }
        
        $evolution = (($currentAvg - $previousAvg) / $previousAvg) * 100;
        
        // Invert if lower is better
        if ($lowerIsBetter) {
            $evolution = -$evolution;
        }
        
        return round($evolution, 2);
    }
}
