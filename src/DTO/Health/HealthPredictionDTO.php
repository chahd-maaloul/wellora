<?php

namespace App\DTO\Health;

/**
 * Data Transfer Object for health predictions.
 * Contains predicted health values with metadata about the prediction.
 */
class HealthPredictionDTO
{
    /**
     * The predicted value (e.g., glycemia prediction)
     */
    private ?float $predictedValue = null;

    /**
     * Number of entries used for training the model
     */
    private int $entriesUsed = 0;

    /**
     * Confidence level of the prediction (high, medium, low)
     */
    private string $confidenceLevel = 'low';

    /**
     * Optional message for edge cases or errors
     */
    private ?string $message = null;

    /**
     * Whether the prediction was successful
     */
    private bool $isSuccessful = false;

    public function __construct(
        ?float $predictedValue = null,
        int $entriesUsed = 0,
        string $confidenceLevel = 'low',
        ?string $message = null,
        bool $isSuccessful = false
    ) {
        $this->predictedValue = $predictedValue;
        $this->entriesUsed = $entriesUsed;
        $this->confidenceLevel = $confidenceLevel;
        $this->message = $message;
        $this->isSuccessful = $isSuccessful;
    }

    /**
     * Create a successful prediction result
     */
    public static function success(float $predictedValue, int $entriesUsed, string $confidenceLevel): self
    {
        return new self(
            predictedValue: $predictedValue,
            entriesUsed: $entriesUsed,
            confidenceLevel: $confidenceLevel,
            message: null,
            isSuccessful: true
        );
    }

    /**
     * Create a failed prediction result with message
     */
    public static function failure(string $message): self
    {
        return new self(
            predictedValue: null,
            entriesUsed: 0,
            confidenceLevel: 'low',
            message: $message,
            isSuccessful: false
        );
    }

    public function getPredictedValue(): ?float
    {
        return $this->predictedValue;
    }

    public function getEntriesUsed(): int
    {
        return $this->entriesUsed;
    }

    public function getConfidenceLevel(): string
    {
        return $this->confidenceLevel;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    /**
     * Check if prediction has enough confidence
     */
    public function hasEnoughConfidence(): bool
    {
        return $this->isSuccessful && in_array($this->confidenceLevel, ['high', 'medium'], true);
    }
}
