<?php

declare(strict_types=1);

namespace App\Service\Health;

use App\DTO\Health\HealthScoreDTO;
use App\Entity\Healthentry;
use App\Entity\Healthjournal;
use App\Repository\HealthentryRepository;
use App\Repository\HealthjournalRepository;

/**
 * Service for generating FullCalendar events from health entries.
 * 
 * Provides health score calculation and color-coded event formatting
 * for calendar visualization.
 */
final class HealthCalendarService
{
    // Color thresholds for health score visualization
    private const SCORE_THRESHOLD_GREEN = 70;
    private const SCORE_THRESHOLD_ORANGE = 40;

    // Color codes for FullCalendar events
    private const COLOR_GREEN = '#22c55e';
    private const COLOR_ORANGE = '#f97316';
    private const COLOR_RED = '#ef4444';
    private const COLOR_GRAY = '#6b7280';

    public function __construct(
        private readonly HealthentryRepository $entryRepository,
        private readonly HealthjournalRepository $journalRepository,
        private readonly HealthAnalyticsService $analyticsService,
    ) {}

    /**
     * Get FullCalendar events for a specific journal.
     * 
     * @param int $journalId The ID of the health journal
     * @return array Array of FullCalendar event objects
     * @throws \InvalidArgumentException If journal not found
     */
    public function getEventsForJournal(int $journalId): array
    {
        $journal = $this->journalRepository->find($journalId);

        if (null === $journal) {
            throw new \InvalidArgumentException(sprintf(
                'Health journal with ID %d not found',
                $journalId
            ));
        }

        return $this->generateEventsFromJournal($journal);
    }

    /**
     * Get FullCalendar events for a journal entity.
     * 
     * @param Healthjournal $journal The health journal
     * @return array Array of FullCalendar event objects
     */
    public function getEventsForJournalEntity(Healthjournal $journal): array
    {
        return $this->generateEventsFromJournal($journal);
    }

    /**
     * Get FullCalendar events for ALL journals.
     * 
     * Aggregates entries from every journal and groups them by date.
     * When multiple journals have entries on the same date, all entries
     * are combined to compute a single daily score.
     * 
     * @return array Array of FullCalendar event objects
     */
    public function getEventsForAllJournals(): array
    {
        $journals = $this->journalRepository->findAll();

        if (empty($journals)) {
            return [];
        }

        // Collect ALL entries across all journals
        $allEntries = [];
        foreach ($journals as $journal) {
            $entries = $this->entryRepository->findBy(
                ['journal' => $journal],
                ['date' => 'ASC']
            );
            foreach ($entries as $entry) {
                $allEntries[] = $entry;
            }
        }

        if (empty($allEntries)) {
            return [];
        }

        // Group entries by date
        $entriesByDate = $this->groupEntriesByDate($allEntries);

        // Generate events for each date
        $events = [];
        foreach ($entriesByDate as $date => $dayEntries) {
            $score = $this->calculateDailyScore($dayEntries);
            $events[] = $this->formatEvent($date, $score);
        }

        return $events;
    }

    /**
     * Generate FullCalendar events from journal entries.
     * 
     * @param Healthjournal $journal The health journal
     * @return array Array of FullCalendar event objects
     */
    private function generateEventsFromJournal(Healthjournal $journal): array
    {
        // Get date range from journal
        $startDate = $journal->getDatedebut();
        $endDate = $journal->getDatefin();

        if (null === $startDate || null === $endDate) {
            // Fallback to fetching all entries if no date range
            $entries = $this->entryRepository->findBy(
                ['journal' => $journal],
                ['date' => 'ASC']
            );
        } else {
            // Fetch entries within the journal's date range
            $entries = $this->entryRepository->findByJournalAndDateRange(
                $journal,
                $startDate,
                $endDate
            );
        }

        // Group entries by date
        $entriesByDate = $this->groupEntriesByDate($entries);

        // Generate events for each date with entries
        $events = [];
        foreach ($entriesByDate as $date => $dayEntries) {
            $score = $this->calculateDailyScore($dayEntries);
            $events[] = $this->formatEvent($date, $score);
        }

        return $events;
    }

    /**
     * Group health entries by their date.
     * 
     * @param array<Healthentry> $entries Array of health entries
     * @return array<string, array<Healthentry>> Entries grouped by date string
     */
    private function groupEntriesByDate(array $entries): array
    {
        $grouped = [];

        foreach ($entries as $entry) {
            $date = $entry->getDate();
            if (null === $date) {
                continue;
            }

            $dateKey = $date->format('Y-m-d');

            if (!isset($grouped[$dateKey])) {
                $grouped[$dateKey] = [];
            }

            $grouped[$dateKey][] = $entry;
        }

        return $grouped;
    }

    /**
     * Calculate the health score for a single day.
     * 
     * If multiple entries exist for a day, calculates the average score.
     * 
     * @param array<Healthentry> $entries Entries for a specific date
     * @return HealthScoreDTO The calculated health score
     */
    private function calculateDailyScore(array $entries): HealthScoreDTO
    {
        if (empty($entries)) {
            return new HealthScoreDTO();
        }

        // Build metrics from entries for this day
        $metrics = $this->analyticsService->buildMetricsFromEntries($entries);

        // Calculate scores
        return $this->analyticsService->calculateScores($metrics);
    }

    /**
     * Format a single event for FullCalendar.
     * 
     * @param string $dateString The date in Y-m-d format
     * @param HealthScoreDTO $score The health score for this date
     * @return array FullCalendar event object
     */
    private function formatEvent(string $dateString, HealthScoreDTO $score): array
    {
        $globalScore = $score->globalScore;
        $color = $this->determineColor($globalScore);

        return [
            'title' => sprintf('Score: %d', (int) $globalScore),
            'start' => $dateString,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'textColor' => '#ffffff',
            'extendedProps' => [
                'score' => $globalScore,
                'grade' => $score->globalScoreGrade,
                'glycemicScore' => $score->glycemicScore,
                'bloodPressureScore' => $score->bloodPressureScore,
                'sleepScore' => $score->sleepScore,
                'symptomScore' => $score->symptomScore,
                'weightScore' => $score->weightScore,
            ],
        ];
    }

    /**
     * Determine the background color based on health score.
     * 
     * @param float $score The health score (0-100)
     * @return string Hex color code
     */
    private function determineColor(float $score): string
    {
        if ($score <= 0) {
            return self::COLOR_GRAY;
        }

        if ($score > self::SCORE_THRESHOLD_GREEN) {
            return self::COLOR_GREEN;
        }

        if ($score >= self::SCORE_THRESHOLD_ORANGE) {
            return self::COLOR_ORANGE;
        }

        return self::COLOR_RED;
    }

    /**
     * Get the color configuration for reference.
     * 
     * @return array<string, string> Color configuration with thresholds
     */
    public function getColorConfig(): array
    {
        return [
            'green_threshold' => self::SCORE_THRESHOLD_GREEN,
            'orange_threshold' => self::SCORE_THRESHOLD_ORANGE,
            'green_color' => self::COLOR_GREEN,
            'orange_color' => self::COLOR_ORANGE,
            'red_color' => self::COLOR_RED,
            'gray_color' => self::COLOR_GRAY,
        ];
    }
}
