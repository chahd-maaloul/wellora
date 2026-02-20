<?php

declare(strict_types=1);

namespace App\Service\Health;

use App\DTO\Health\HealthReportDTO;
use App\Entity\Healthentry;
use App\Entity\Healthjournal;
use App\Repository\HealthentryRepository;
use App\Repository\HealthjournalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Service for generating Medical Health Reports.
 * Handles data processing, recommendations, and PDF generation.
 */
final class HealthReportService
{
    private const DAYS_LOOKBACK = 30;
    
    // Score thresholds
    private const SCORE_EXCELLENT = 80;
    private const SCORE_GOOD = 60;
    private const SCORE_AVERAGE = 40;
    private const SCORE_LOW = 20;

    public function __construct(
        private readonly HealthentryRepository $entryRepository,
        private readonly HealthjournalRepository $journalRepository,
        private readonly HealthAnalyticsService $analyticsService,
        private readonly HealthRiskEngineService $riskEngineService,
        private readonly HealthPredictionService $predictionService,
        private readonly EntityManagerInterface $entityManager,
        private readonly Environment $twig,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    /**
     * Generate a health report for a specific journal.
     */
    public function generateReport(Healthjournal $journal): HealthReportDTO
    {
        // Get entries from last 30 days
        $entries = $this->getLast30DaysEntries($journal);
        
        // Calculate period
        $periodStart = $this->calculatePeriodStart($entries);
        $periodEnd = $this->calculatePeriodEnd($entries);
        
        // Get analytics data
        $analyticsData = $this->getAnalyticsData($journal);
        
        // Get prediction
        $prediction = $this->predictionService->predictGlycemia($journal);
        
        // Calculate averages
        $averages = $this->calculateAverages($entries);
        
        // Get risk level using new calculation method
        $entryCount = count($entries);
        $globalScore = $analyticsData['scores']->globalScore;
        $predictedGlycemia = $prediction->isSuccessful() ? $prediction->getPredictedValue() : null;
        
        $riskLevel = $this->riskEngineService->calculateRiskLevel(
            $entryCount,
            $globalScore,
            $predictedGlycemia
        );
        
        // Generate advanced medical analysis
        $advancedAnalysis = $this->generateAdvancedAnalysis(
            $entries,
            $averages,
            $predictedGlycemia,
            $globalScore
        );
        
        // Generate recommendations
        $recommendations = $this->generateRecommendations(
            $analyticsData['scores'],
            $riskLevel,
            $prediction
        );
        
        // Build DTO
        return new HealthReportDTO(
            patientName: $this->getPatientName($journal),
            globalScore: $globalScore,
            predictedGlycemia: $predictedGlycemia,
            riskLevel: $riskLevel['tier'],
            riskLevelLabel: $riskLevel['label'],
            recommendationText: $advancedAnalysis . "\n\n" . $recommendations,
            entries: $entries,
            periodStart: $periodStart,
            periodEnd: $periodEnd,
            scores: $analyticsData['scores']->toArray(),
            totalEntries: $entryCount,
            avgGlycemia: $averages['glycemia'],
            avgBloodPressure: $averages['bloodPressure'],
            avgSleep: $averages['sleep'],
            avgWeight: $averages['weight'],
        );
    }

    /**
     * Generate PDF from report DTO.
     */
    public function generatePdf(HealthReportDTO $report): string
    {
        // Configure Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Arial');
        
        $dompdf = new Dompdf($options);
        
        // Render Twig template to HTML
        $html = $this->twig->render('health/report_pdf.html.twig', [
            'report' => $report,
        ]);
        
        // Load HTML content
        $dompdf->loadHtml($html);
        
        // Set paper size (A4)
        $dompdf->setPaper('A4', 'portrait');
        
        // Render PDF
        $dompdf->render();
        
        // Return PDF content
        return $dompdf->output();
    }

    /**
     * Get journal by ID or return default.
     */
    public function getJournal(?int $journalId): ?Healthjournal
    {
        if ($journalId === null) {
            return $this->getDefaultJournal();
        }
        
        return $this->journalRepository->find($journalId);
    }

    /**
     * Get list of all journals for selection.
     */
    public function getAllJournals(): array
    {
        return $this->journalRepository->findBy([], ['datedebut' => 'DESC']);
    }

    /**
     * Get journals with at least 5 entries (eligible for reports).
     *
     * @return array<int, array{journal: Healthjournal, entryCount: int}>
     */
    public function getJournalsWithEnoughEntries(): array
    {
        $journals = $this->journalRepository->findBy([], ['datedebut' => 'DESC']);
        $result = [];
        
        foreach ($journals as $journal) {
            $entryCount = $this->entryRepository->count(['journal' => $journal]);
            if ($entryCount >= 5) {
                $result[] = [
                    'journal' => $journal,
                    'entryCount' => $entryCount,
                ];
            }
        }
        
        return $result;
    }

    // ============================================
    // PRIVATE HELPER METHODS
    // ============================================

    /**
     * Get entries from last 30 days.
     *
     * @return array<int, Healthentry>
     */
    private function getLast30DaysEntries(Healthjournal $journal): array
    {
        $startDate = new \DateTime('-30 days');
        $endDate = new \DateTime();
        
        return $this->entryRepository->createQueryBuilder('e')
            ->andWhere('e.journal = :journal')
            ->andWhere('e.date >= :startDate')
            ->andWhere('e.date <= :endDate')
            ->setParameter('journal', $journal)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get default/latest journal.
     */
    private function getDefaultJournal(): ?Healthjournal
    {
        $journals = $this->journalRepository->findBy([], ['datedebut' => 'DESC']);
        
        return !empty($journals) ? $journals[0] : null;
    }

    /**
     * Calculate period start from entries.
     */
    private function calculatePeriodStart(array $entries): ?\DateTime
    {
        if (empty($entries)) {
            return new \DateTime('-30 days');
        }
        
        $oldestEntry = end($entries);
        return $oldestEntry->getDate();
    }

    /**
     * Calculate period end from entries.
     */
    private function calculatePeriodEnd(array $entries): ?\DateTime
    {
        if (empty($entries)) {
            return new \DateTime();
        }
        
        $latestEntry = reset($entries);
        return $latestEntry->getDate();
    }

    /**
     * Get analytics data for journal.
     */
    private function getAnalyticsData(Healthjournal $journal): array
    {
        try {
            $analyticsData = $this->analyticsService->getAnalyticsForJournal($journal);
            
            return [
                'metrics' => $analyticsData['metrics'],
                'statistics' => $analyticsData['statistics'],
                'scores' => $analyticsData['scores'],
            ];
        } catch (\Throwable $e) {
            // Return default values on error
            return [
                'metrics' => null,
                'statistics' => null,
                'scores' => new \App\DTO\Health\HealthScoreDTO(),
            ];
        }
    }

    /**
     * Calculate risk from entries.
     */
    private function calculateRisk(array $entries): array
    {
        if (empty($entries)) {
            return [
                'tier' => 'unknown',
                'score' => 0.0,
            ];
        }
        
        try {
            // Get metrics from entries
            $metrics = $this->extractMetrics($entries);
            $risk = $this->riskEngineService->analyzeRisk($metrics);
            
            return [
                'tier' => $risk->tier->value,
                'score' => $risk->overallRiskScore,
                'recommendations' => $risk->recommendations,
            ];
        } catch (\Throwable $e) {
            return [
                'tier' => 'unknown',
                'score' => 0.0,
            ];
        }
    }

    /**
     * Extract metrics from entries for risk analysis.
     */
    private function extractMetrics(array $entries): \App\DTO\Health\HealthMetricDTO
    {
        $glycemia = [];
        $bpSystolic = [];
        $bpDiastolic = [];
        $sleep = [];
        $weight = [];
        $dates = [];
        $symptomIntensity = [];
        
        foreach ($entries as $entry) {
            $glycemia[] = $entry->getGlycemie() ?? 0;
            $sleep[] = $entry->getSommeil() ?? 0;
            $weight[] = $entry->getPoids() ?? 0;
            $dates[] = $entry->getDate() ?? new \DateTime();
            
            // Parse blood pressure
            $tension = $entry->getTension();
            if ($tension && str_contains($tension, '/')) {
                $parts = explode('/', $tension);
                $bpSystolic[] = (float) trim($parts[0]);
                $bpDiastolic[] = isset($parts[1]) ? (float) trim($parts[1]) : 0;
            } else {
                $bpSystolic[] = 80;
                $bpDiastolic[] = 120;
            }
            
            // Calculate symptom intensity
            $intensity = 0;
            foreach ($entry->getSymptoms() as $symptom) {
                $intensity += $symptom->getIntensity() ?? 0;
            }
            $symptomIntensity[] = $intensity;
        }
        
        return new \App\DTO\Health\HealthMetricDTO(
            glycemia: $glycemia,
            bloodPressureSystolic: $bpSystolic,
            bloodPressureDiastolic: $bpDiastolic,
            sleep: $sleep,
            weight: $weight,
            dates: $dates,
            symptomIntensity: $symptomIntensity,
        );
    }

    /**
     * Calculate averages from entries.
     */
    private function calculateAverages(array $entries): array
    {
        if (empty($entries)) {
            return [
                'glycemia' => null,
                'bloodPressure' => null,
                'sleep' => null,
                'weight' => null,
            ];
        }
        
        $glycemiaSum = 0;
        $bpSum = 0;
        $sleepSum = 0;
        $weightSum = 0;
        $count = count($entries);
        
        foreach ($entries as $entry) {
            $glycemiaSum += $entry->getGlycemie() ?? 0;
            $sleepSum += $entry->getSommeil() ?? 0;
            $weightSum += $entry->getPoids() ?? 0;
            
            $tension = $entry->getTension();
            if ($tension && str_contains($tension, '/')) {
                $parts = explode('/', $tension);
                $bpSum += isset($parts[1]) ? (float) trim($parts[1]) : 80;
            }
        }
        
        return [
            'glycemia' => $count > 0 ? $glycemiaSum / $count : null,
            'bloodPressure' => $count > 0 ? $bpSum / $count : null,
            'sleep' => $count > 0 ? $sleepSum / $count : null,
            'weight' => $count > 0 ? $weightSum / $count : null,
        ];
    }

    /**
     * Get patient name from journal.
     */
    private function getPatientName(Healthjournal $journal): string
    {
        // For now, return a default name
        // In a real app, this would come from the User entity
        return 'Patient';
    }

    /**
     * Get risk label in French.
     */
    private function getRiskLabel(string $tier): string
    {
        return match ($tier) {
            'excellent' => 'Excellent',
            'stable' => 'Stable',
            'warning' => 'Attention',
            'high_risk' => 'Risque Élevé',
            default => 'Inconnu',
        };
    }

    /**
     * Generate recommendations based on scores, risk, and prediction.
     */
    private function generateRecommendations(
        \App\DTO\Health\HealthScoreDTO $scores,
        array $risk,
        \App\DTO\Health\HealthPredictionDTO $prediction
    ): string {
        $recommendations = [];
        
        // Global score recommendations
        if ($scores->globalScore >= self::SCORE_EXCELLENT) {
            $recommendations[] = 'Félicitations ! Votre santé globale est excellente. Continuez vos bonnes habitudes.';
        } elseif ($scores->globalScore >= self::SCORE_GOOD) {
            $recommendations[] = 'Votre état de santé est bon. Maintenez vos efforts actuels.';
        } elseif ($scores->globalScore >= self::SCORE_AVERAGE) {
            $recommendations[] = 'Votre santé nécessite une attention particulière. Envisagez des adjustments dans votre mode de vie.';
        } else {
            $recommendations[] = 'Il est recommandé de consulter un professionnel de santé pour évaluer votre état.';
        }
        
        // Glycemia recommendations
        if ($scores->glycemicScore < self::SCORE_AVERAGE) {
            $recommendations[] = 'Surveillez votre glycémie de près. Réduisez les aliments sucrés et faites de l\'exercice régulièrement.';
        }
        
        // Blood pressure recommendations
        if ($scores->bloodPressureScore < self::SCORE_AVERAGE) {
            $recommendations[] = 'Votre tension artérielle mérite attention. Limitez le sel et pratiquez une activité physique.';
        }
        
        // Sleep recommendations
        if ($scores->sleepScore < self::SCORE_AVERAGE) {
            $recommendations[] = 'Visez 7-8 heures de sommeil par nuit. Établissez une routine de sommeil régulière.';
        }
        
        // Risk-based recommendations
        if ($risk['tier'] === 'warning') {
            $recommendations[] = 'Attention : certains indicateurs nécessitent une surveillance.';
        } elseif ($risk['tier'] === 'high_risk') {
            $recommendations[] = 'Urgent : Veuillez consulter un professionnel de santé rapidement.';
        }
        
        // Prediction-based recommendations
        if ($prediction->isSuccessful() && $prediction->getPredictedValue() !== null) {
            $predictedValue = $prediction->getPredictedValue();
            
            if ($predictedValue > 1.2) {
                $recommendations[] = sprintf(
                    'La prédiction de glycémie (%.2f g/L) suggère une surveillance accrue.',
                    $predictedValue
                );
            } elseif ($predictedValue < 0.7) {
                $recommendations[] = sprintf(
                    'La glycémie prédite (%.2f g/L) est basse. Surveillez les symptômes d\'hypoglycémie.',
                    $predictedValue
                );
            }
        }
        
        // General recommendations if no specific issues
        if (empty($recommendations)) {
            $recommendations[] = 'Continuez à enregistrer vos données quotidiennement pour un suivi optimal.';
        }
        
        return implode("\n\n", $recommendations);
    }

    /**
     * Generate advanced medical-style analysis text.
     * 
     * Analyzes:
     * - Glycemia spikes (>1.8 g/L)
     * - Increasing trend over last entries
     * - Unstable tension values
     * - Insufficient sleep average (<6h)
     */
    private function generateAdvancedAnalysis(
        array $entries,
        array $averages,
        ?float $predictedGlycemia,
        float $globalScore
    ): string {
        $analysis = [];
        
        if (empty($entries)) {
            return 'Aucune donnée disponible pour générer une analyse approfondie.';
        }

        // Glycemia spikes detection (>1.8 g/L)
        $spikes = $this->detectGlycemiaSpikes($entries);
        if ($spikes['hasSpikes']) {
            $analysis[] = sprintf(
                'La glycémie présente des pics occasionnels au-dessus des valeurs normales (max: %.2f g/L). Une surveillance régulière est recommandée afin de prévenir toute complication métabolique.',
                $spikes['max']
            );
        }
        
        // Glycemia trend detection
        $trend = $this->detectGlycemiaTrend($entries);
        if ($trend === 'increasing') {
            $analysis[] = 'Une tendance progressive à la hausse de la glycémie est observée sur la période analysée. Il est conseillé de consulter un professionnel pour évaluer les causes.';
        } elseif ($trend === 'decreasing') {
            $analysis[] = 'Une tendance à la baisse de la glycémie est observée, indiquant une amélioration de votre contrôle glycémique.';
        }
        
        // Unstable blood pressure detection
        $unstableBP = $this->detectUnstableBloodPressure($entries);
        if ($unstableBP['isUnstable']) {
            $analysis[] = sprintf(
                'Les valeurs de tension artérielle présentent une variabilité importante (écart-type: %.1f mmHg). Une stabilisation est souhaitable pour réduire les risques cardiovasculaires.',
                $unstableBP['stdDev']
            );
        }
        
        // Insufficient sleep detection (<6h)
        if (isset($averages['sleep']) && $averages['sleep'] !== null && $averages['sleep'] < 6) {
            $analysis[] = sprintf(
                'La durée moyenne de sommeil (%.1f heures) est inférieure aux recommandations standards, ce qui peut impacter l\'équilibre glycémique et la tension artérielle.',
                $averages['sleep']
            );
        }
        
        // Prediction-based analysis
        if ($predictedGlycemia !== null) {
            if ($predictedGlycemia > 1.8) {
                $analysis[] = sprintf(
                    'Les projections IA indiquent une glycémie potentiellement élevée (prédiction: %.2f g/L). Une vigilance accrue et une consultation médicale sont recommandées.',
                    $predictedGlycemia
                );
            } elseif ($predictedGlycemia < 0.7) {
                $analysis[] = sprintf(
                    'Les projections suggèrent une tendance à l\'hypoglycémie (prédiction: %.2f g/L). Surveillez les symptômes et consultez si nécessaire.',
                    $predictedGlycemia
                );
            }
        }
        
        // Global assessment
        if (empty($analysis)) {
            if ($globalScore >= 70) {
                $analysis[] = 'L\'état général de santé apparaît stable sur la période étudiée. Les indicateurs majeurs se situent dans les plages cibles.';
            } else {
                $analysis[] = 'Malgré quelques fluctuations, l\'état général reste acceptable. Une attention particulière aux habitudes de vie est conseillée.';
            }
        }
        
        return implode("\n\n", $analysis);
    }

    /**
     * Detect glycemia spikes (>1.8 g/L)
     * 
     * @return array{hasSpikes: bool, max: float, count: int}
     */
    private function detectGlycemiaSpikes(array $entries): array
    {
        $max = 0.0;
        $count = 0;
        
        foreach ($entries as $entry) {
            $glycemia = $entry->getGlycemie();
            if ($glycemia !== null && $glycemia > 1.8) {
                $count++;
                if ($glycemia > $max) {
                    $max = $glycemia;
                }
            }
        }
        
        return [
            'hasSpikes' => $count > 0,
            'max' => $max,
            'count' => $count,
        ];
    }

    /**
     * Detect glycemia trend over last entries.
     * 
     * @return string 'increasing'|'stable'|'decreasing'
     */
    private function detectGlycemiaTrend(array $entries): string
    {
        if (count($entries) < 5) {
            return 'stable';
        }
        
        // Get last 10 entries sorted by date ascending
        $recentEntries = array_slice($entries, -10);
        
        $glycemiaValues = [];
        foreach ($recentEntries as $entry) {
            $glycemia = $entry->getGlycemie();
            if ($glycemia !== null) {
                $glycemiaValues[] = $glycemia;
            }
        }
        
        if (count($glycemiaValues) < 5) {
            return 'stable';
        }
        
        // Calculate linear regression slope
        $n = count($glycemiaValues);
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumX += $i;
            $sumY += $glycemiaValues[$i];
            $sumXY += $i * $glycemiaValues[$i];
            $sumX2 += $i * $i;
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        
        // Determine trend based on slope significance
        if ($slope > 0.05) {
            return 'increasing';
        } elseif ($slope < -0.05) {
            return 'decreasing';
        }
        
        return 'stable';
    }

    /**
     * Detect unstable blood pressure.
     * 
     * @return array{isUnstable: bool, stdDev: float}
     */
    private function detectUnstableBloodPressure(array $entries): array
    {
        $bpValues = [];
        
        foreach ($entries as $entry) {
            $tension = $entry->getTension();
            if ($tension && str_contains($tension, '/')) {
                $parts = explode('/', $tension);
                $diastolic = isset($parts[1]) ? (float) trim($parts[1]) : null;
                if ($diastolic !== null) {
                    $bpValues[] = $diastolic;
                }
            }
        }
        
        if (count($bpValues) < 3) {
            return ['isUnstable' => false, 'stdDev' => 0.0];
        }
        
        // Calculate standard deviation
        $mean = array_sum($bpValues) / count($bpValues);
        $variance = 0;
        foreach ($bpValues as $value) {
            $variance += pow($value - $mean, 2);
        }
        $stdDev = sqrt($variance / count($bpValues));
        
        return [
            'isUnstable' => $stdDev > 15,
            'stdDev' => round($stdDev, 1),
        ];
    }
}
