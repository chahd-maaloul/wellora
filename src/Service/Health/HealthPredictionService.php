<?php

namespace App\Service\Health;

use App\DTO\Health\HealthPredictionDTO;
use App\Entity\Healthentry;
use App\Entity\Healthjournal;
use App\Entity\User;
use App\Repository\HealthentryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Service for AI-based health predictions using PHP-ML.
 * Implements Linear Regression for glycemia prediction based on historical data.
 * 
 * Requirements: composer require php-ai/php-ml
 * 
 * Features: sommeil, systolic, diastolic, poids
 * Target: glycemie
 */
class HealthPredictionService
{
    private const MIN_ENTRIES_REQUIRED = 5;
    private const LAST_DAYS_FOR_AVG = 7;
    private const CONFIDENCE_THRESHOLDS = [
        'high' => 20,
        'medium' => 10,
        'low' => 0,
    ];

    public function __construct(
        private readonly HealthentryRepository $healthentryRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ?LoggerInterface $logger = null,
        private readonly ?Security $security = null
    ) {
    }

    /**
     * Predict glycemia for a given health journal.
     * Uses entries from the specified user for prediction.
     * 
     * @param Healthjournal|null $journal The health journal (optional, for display purposes)
     * @param User|null $user The current user for filtering entries
     * @return HealthPredictionDTO Prediction result with metadata
     */
    public function predictGlycemia(?Healthjournal $journal = null, ?User $user = null): HealthPredictionDTO
    {
        // Check if PHP-ML is installed
        if (!$this->isPhpMlInstalled()) {
            return HealthPredictionDTO::failure(
                'PHP-ML library is not installed. Run: composer require php-ai/php-ml'
            );
        }

        try {
            // Fetch historical entries filtered by user
            $entries = $this->getAllHistoricalEntries($user);

            // Check minimum entries requirement
            if (count($entries) < self::MIN_ENTRIES_REQUIRED) {
                return HealthPredictionDTO::failure(
                    sprintf(
                        'Pas assez de données. Besoin d\'au moins %d entrées, actuellement %d disponibles.',
                        self::MIN_ENTRIES_REQUIRED,
                        count($entries)
                    )
                );
            }

            // Prepare training dataset
            $dataset = $this->prepareDataset($entries);
            
            if (empty($dataset['samples']) || empty($dataset['targets'])) {
                return HealthPredictionDTO::failure(
                    'Données insuffisantes pour l\'entraînement. Les entrées doivent contenir des valeurs valides.'
                );
            }
            
            // Check if we have enough valid samples (need at least as many as features + extra for training)
            if (count($dataset['samples']) < 10) {
                return HealthPredictionDTO::failure(
                    sprintf(
                        'Pas assez de données variées. Besoin d\'au moins 10 entrées avec des données complètes (poids, sommeil, tension, glycémie), actuellement %d disponibles.',
                        count($dataset['samples'])
                    )
                );
            }
            
            // Check if samples have variance (different values)
            if (!$this->hasDataVariance($dataset['samples'])) {
                return HealthPredictionDTO::failure(
                    'Les données doivent varier pour permettre une prédiction. Les entrées semblent trop similaires.'
                );
            }

            // Train Linear Regression model
            $model = $this->trainModel($dataset['samples'], $dataset['targets']);

            // Prepare prediction input (last 7 days averages)
            $predictionInput = $this->preparePredictionInput($entries);

            if ($predictionInput === null) {
                return HealthPredictionDTO::failure(
                    'Impossible de calculer les données de prédiction. Vérifiez les entrées récentes.'
                );
            }

            // Make prediction
            $predictedGlycemia = $model->predict($predictionInput);

            // Calculate confidence based on training data size
            $confidenceLevel = $this->calculateConfidenceLevel(count($entries));

            // Clamp prediction to valid range
            $predictedGlycemia = $this->clampGlycemia($predictedGlycemia);

            return HealthPredictionDTO::success(
                predictedValue: (float) $predictedGlycemia,
                entriesUsed: count($entries),
                confidenceLevel: $confidenceLevel
            );

        } catch (\Throwable $e) {
            $this->logError('Glycemia prediction failed', $e);
            
            return HealthPredictionDTO::failure(
                'Une erreur est survenue lors de la prédiction: ' . $e->getMessage()
            );
        }
    }

    /**
     * Check if PHP-ML library is installed.
     * 
     * @return bool
     */
    private function isPhpMlInstalled(): bool
    {
        return class_exists(\Phpml\Regression\LeastSquares::class);
    }

    /**
     * Fetch all historical health entries filtered by user.
     *
     * @param User|null $user The user to filter entries by
     * @return array<int, Healthentry>
     */
    private function getAllHistoricalEntries(?User $user = null): array
    {
        if ($user) {
            return $this->healthentryRepository->createQueryBuilder('e')
                ->join('e.journal', 'j')
                ->andWhere('j.user = :user')
                ->setParameter('user', $user)
                ->orderBy('e.date', 'ASC')
                ->getQuery()
                ->getResult();
        }
        
        return $this->healthentryRepository->findBy(
            [],
            ['date' => 'ASC']
        );
    }

    /**
     * Fetch historical health entries for a journal ordered by date.
     *
     * @param Healthjournal $journal
     * @return array<int, Healthentry>
     */
    private function getHistoricalEntries(Healthjournal $journal): array
    {
        return $this->healthentryRepository->findBy(
            ['journal' => $journal],
            ['date' => 'ASC']
        );
    }

    /**
     * Prepare training dataset from health entries.
     * Features: sommeil, systolic, diastolic, poids
     * Target: glycemie
     *
     * @param array<int, Healthentry> $entries
     * @return array{samples: array, targets: array}
     */
    private function prepareDataset(array $entries): array
    {
        $samples = [];
        $targets = [];

        foreach ($entries as $entry) {
            // Extract features with null safety
            $sommeil = $this->safeFloatCast($entry->getSommeil());
            $poids = $this->safeFloatCast($entry->getPoids());
            $glycemie = $this->safeFloatCast($entry->getGlycemie());
            
            // Parse tension (blood pressure) to get systolic and diastolic
            $bloodPressure = $this->parseBloodPressure($entry->getTension());

            // Skip entries with missing required data
            if ($sommeil === null || $poids === null || $glycemie === null) {
                continue;
            }

            // Create sample with all features
            $sample = [
                $sommeil,
                $bloodPressure['systolic'],
                $bloodPressure['diastolic'],
                $poids,
            ];

            // Validate all features are valid numbers
            if ($this->isValidSample($sample)) {
                $samples[] = $sample;
                $targets[] = $glycemie;
            }
        }

        return [
            'samples' => $samples,
            'targets' => $targets,
        ];
    }

    /**
     * Train Linear Regression model using PHP-ML.
     *
     * @param array $samples
     * @param array $targets
     * @return \Phpml\Regression\LeastSquares
     */
    private function trainModel(array $samples, array $targets): \Phpml\Regression\LeastSquares
    {
        try {
            $model = new \Phpml\Regression\LeastSquares();
            $model->train($samples, $targets);
            
            return $model;
        } catch (\Exception $e) {
            // Handle matrix-related errors
            if (strpos($e->getMessage(), 'Matrix') !== false || 
                strpos($e->getMessage(), 'singular') !== false ||
                strpos($e->getMessage(), 'invertible') !== false) {
                throw new \RuntimeException(
                    'Données insuffisantes pour la prédiction. Les entrées doivent contenir des données variées (poids, sommeil, tension) pour permettre l\'entraînement du modèle.'
                );
            }
            throw $e;
        }
    }

    /**
     * Prepare prediction input based on last 7 days averages.
     *
     * @param array<int, Healthentry> $entries
     * @return array|null
     */
    private function preparePredictionInput(array $entries): ?array
    {
        // Get last 7 days of entries
        $recentEntries = array_slice($entries, -self::LAST_DAYS_FOR_AVG);

        if (empty($recentEntries)) {
            return null;
        }

        // Calculate averages
        $sommeilSum = 0;
        $poidsSum = 0;
        $systolicSum = 0;
        $diastolicSum = 0;
        $validCount = 0;

        foreach ($recentEntries as $entry) {
            $sommeil = $this->safeFloatCast($entry->getSommeil());
            $poids = $this->safeFloatCast($entry->getPoids());
            $bloodPressure = $this->parseBloodPressure($entry->getTension());

            if ($sommeil !== null && $poids !== null) {
                $sommeilSum += $sommeil;
                $poidsSum += $poids;
                $systolicSum += $bloodPressure['systolic'];
                $diastolicSum += $bloodPressure['diastolic'];
                $validCount++;
            }
        }

        if ($validCount === 0) {
            return null;
        }

        return [
            $sommeilSum / $validCount,
            $systolicSum / $validCount,
            $diastolicSum / $validCount,
            $poidsSum / $validCount,
        ];
    }

    /**
     * Parse blood pressure string to systolic and diastolic values.
     * Expected format: "systolic/diastolic" or just systolic value
     *
     * @param string|null $tension
     * @return array{systolic: float, diastolic: float}
     */
    private function parseBloodPressure(?string $tension): array
    {
        if ($tension === null || $tension === '') {
            return ['systolic' => 80.0, 'diastolic' => 120.0]; // Default values
        }

        // Try to parse "systolic/diastolic" format
        if (str_contains($tension, '/')) {
            $parts = explode('/', $tension);
            $systolic = isset($parts[0]) ? (float) trim($parts[0]) : 80.0;
            $diastolic = isset($parts[1]) ? (float) trim($parts[1]) : 120.0;
            
            return [
                'systolic' => is_nan($systolic) ? 80.0 : $systolic,
                'diastolic' => is_nan($diastolic) ? 120.0 : $diastolic,
            ];
        }

        // If only one value, assume it's diastolic (common in some systems)
        // and derive systolic using a typical ratio
        $value = (float) $tension;
        if (is_nan($value)) {
            return ['systolic' => 80.0, 'diastolic' => 120.0];
        }

        // Assume the value is diastolic and calculate systolic
        // Using typical ratio: systolic ≈ diastolic + 40
        return [
            'systolic' => $value + 40,
            'diastolic' => $value,
        ];
    }

    /**
     * Calculate confidence level based on number of training samples.
     *
     * @param int $sampleCount
     * @return string
     */
    private function calculateConfidenceLevel(int $sampleCount): string
    {
        foreach (self::CONFIDENCE_THRESHOLDS as $level => $threshold) {
            if ($sampleCount >= $threshold) {
                return $level;
            }
        }

        return 'low';
    }

    /**
     * Clamp glycemia value to valid range (0.5 - 3 g/l).
     *
     * @param float $glycemia
     * @return float
     */
    private function clampGlycemia(float $glycemia): float
    {
        return max(0.5, min(3.0, $glycemia));
    }

    /**
     * Safely cast a value to float, handling nulls and non-numeric values.
     *
     * @param mixed $value
     * @return float|null
     */
    private function safeFloatCast(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $floatValue = (float) $value;

        return is_nan($floatValue) ? null : $floatValue;
    }

    /**
     * Validate that all values in a sample are valid numbers.
     *
     * @param array $sample
     * @return bool
     */
    private function isValidSample(array $sample): bool
    {
        foreach ($sample as $value) {
            if (!is_numeric($value) || is_nan($value) || is_infinite($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the data has variance (different values across samples).
     * This helps avoid "Matrix is singular" errors.
     *
     * @param array $samples
     * @return bool
     */
    private function hasDataVariance(array $samples): bool
    {
        if (count($samples) < 2) {
            return false;
        }

        // Check each column for variance
        $numFeatures = count($samples[0]);
        for ($col = 0; $col < $numFeatures; $col++) {
            $values = array_column($samples, $col);
            $uniqueValues = array_unique($values);
            
            // If all values are the same, no variance
            if (count($uniqueValues) < 2) {
                return false;
            }
        }

        return true;
    }

    /**
     * Log error with context.
     *
     * @param string $message
     * @param \Throwable $exception
     * @return void
     */
    private function logError(string $message, \Throwable $exception): void
    {
        if ($this->logger !== null) {
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }
}
