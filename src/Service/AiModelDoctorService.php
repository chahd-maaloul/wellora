<?php

namespace App\Service;

use App\Repository\ConsultationRepository;
use App\Repository\MedecinRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Service pour communiquer avec l'API AI Model Doctor (Flask/Python)
 * Fournit des prédictions et recommandations basées sur l'IA pour les médecins
 */
class AiModelDoctorService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private ConsultationRepository $consultationRepository;
    private MedecinRepository $medecinRepository;
    private string $apiBaseUrl;
    private bool $enabled;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        ConsultationRepository $consultationRepository,
        MedecinRepository $medecinRepository,
        string $apiBaseUrl = 'http://localhost:5000',
        bool $enabled = true
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->consultationRepository = $consultationRepository;
        $this->medecinRepository = $medecinRepository;
        $this->apiBaseUrl = rtrim($apiBaseUrl, '/');
        $this->enabled = $enabled;
    }

    /**
     * Vérifie si l'API est disponible et opérationnelle
     */
    public function isAvailable(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $response = $this->httpClient->request('GET', $this->apiBaseUrl . '/api/health', [
                'timeout' => 5,
            ]);

            $data = $response->toArray();
            return isset($data['status']) && $data['status'] === 'ok';
        } catch (\Exception $e) {
            $this->logger->warning('AI Model Doctor API non disponible: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Prédit l'activité d'un médecin spécifique pour les 7 prochains jours
     * 
     * @param int $doctorId ID du médecin
     * @return array|null Prédictions ou null si erreur
     */
    public function predictDoctorActivity(string $doctorId): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $features = $this->buildDoctorFeatures($doctorId);
            if ($features === null) {
                return null;
            }

            $response = $this->httpClient->request('POST', $this->apiBaseUrl . '/api/predict/doctor-features', [
                'json' => [
                    'doctor_id' => $doctorId,
                    'features' => $features,
                ],
                'timeout' => 10,
            ]);

            if ($response->getStatusCode() === 200) {
                return $response->toArray();
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur prediction medecin: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Predicts activity for all doctors.
     */
    public function predictAllDoctors(): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $doctors = $this->buildAllDoctorFeatures();
            if (count($doctors) === 0) {
                return null;
            }

            $response = $this->httpClient->request('POST', $this->apiBaseUrl . '/api/predict/batch', [
                'json' => [
                    'doctors' => $doctors,
                ],
                'timeout' => 15,
            ]);

            if ($response->getStatusCode() === 200) {
                return $response->toArray();
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur prediction tous medecins: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get cluster info (legacy endpoint).
     */
    public function getClusterInfo(int $clusterId): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', $this->apiBaseUrl . "/api/cluster/{$clusterId}", [
                'timeout' => 10,
            ]);

            if ($response->getStatusCode() === 200) {
                return $response->toArray();
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur recuperation cluster: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Build recommendations from DB stats.
     */
    public function getDoctorRecommendations(string $doctorId): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        $stats = $this->buildDoctorStats();
        $doctor = $stats['by_uuid'][$doctorId] ?? null;
        $recommendations = [];

        if ($doctor) {
            if ($doctor['popularity_score'] < 0.5) {
                $recommendations[] = [
                    'type' => 'popularity',
                    'title' => 'Augmenter la visibilite',
                    'description' => "Envisagez d'activer les consultations en ligne pour attirer plus de patients",
                ];
            }

            if ($doctor['emergency_count'] > $stats['avg_emergency']) {
                $recommendations[] = [
                    'type' => 'emergency',
                    'title' => 'Optimiser les urgences',
                    'description' => "Vous avez plus d'urgences que la moyenne. Prevoyez des creneaux dedies.",
                ];
            }

            if ($doctor['recent_count'] < ($stats['avg_recent'] * 0.8)) {
                $recommendations[] = [
                    'type' => 'activity',
                    'title' => "Augmenter l'activite",
                    'description' => "Votre activite est inferieure a la moyenne. Essayez d'ouvrir plus de creneaux en soiree.",
                ];
            }
        }

        $profit = $this->getDoctorProfitPredictions($doctorId);
        if ($profit && $profit['trend'] < -0.05) {
            $recommendations[] = [
                'type' => 'profit',
                'title' => 'Stabiliser les revenus',
                'description' => "Les revenus recents sont en baisse. Mettez en avant vos disponibilites et reduisez les annulations.",
            ];
        }

        if ($profit && $profit['avg_fee'] < 30) {
            $recommendations[] = [
                'type' => 'profit',
                'title' => 'Revoir les honoraires',
                'description' => "Les honoraires moyens sont faibles. Verifiez la tarification et les actes factures.",
            ];
        }

        $scheduleRecs = $this->buildScheduleRecommendations($doctorId);
        foreach ($scheduleRecs as $rec) {
            $recommendations[] = $rec;
        }

        if (count($recommendations) === 0) {
            $recommendations[] = [
                'type' => 'general',
                'title' => 'Maintenir le cap',
                'description' => 'Votre activite est dans la moyenne. Continuez ainsi !',
            ];
        }

        return [
            'doctor_id' => $doctorId,
            'cluster' => 0,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Get dashboard data.
     */
    public function getDashboardData(?string $doctorId = null): array
    {
        $dashboardData = [
            'api_available' => $this->isAvailable(),
            'predictions' => null,
            'recommendations' => null,
            'all_predictions' => null,
            'profit_predictions' => null,
            'revenue_weekly' => null,
            'revenue_monthly' => null,
            'profit_alerts' => null,
            'treatment_effectiveness' => null,
        ];

        // Always get treatment effectiveness data from database when doctorId is provided
        if ($doctorId !== null) {
            $dashboardData['treatment_effectiveness'] = $this->getTreatmentEffectivenessData($doctorId);
        }

        if (!$dashboardData['api_available']) {
            return $dashboardData;
        }

        $dashboardData['all_predictions'] = $this->predictAllDoctors();

        if ($doctorId !== null) {
            $dashboardData['predictions'] = $this->predictDoctorActivity($doctorId);
            $dashboardData['recommendations'] = $this->getDoctorRecommendations($doctorId);
            $dashboardData['profit_predictions'] = $this->getDoctorProfitPredictions($doctorId);
            $dashboardData['revenue_weekly'] = $this->getDoctorRevenueWeekly($doctorId);
            $dashboardData['revenue_monthly'] = $this->getDoctorRevenueMonthly($doctorId);
            $dashboardData['profit_alerts'] = $this->getDoctorProfitAlerts($doctorId);
        }

        return $dashboardData;
    }

    public function getDoctorProfitPredictions(string $doctorId): ?array
    {
        $since = new \DateTimeImmutable('-120 days');
        $series = $this->consultationRepository->getDoctorRevenueSeries($doctorId, $since);

        if (count($series) === 0) {
            return null;
        }

        $cutoff30 = (new \DateTimeImmutable('-30 days'))->format('Y-m-d');
        $cutoff90 = (new \DateTimeImmutable('-90 days'))->format('Y-m-d');
        $last30 = 0.0;
        $prev30 = 0.0;
        $last90 = 0.0;
        $prev90 = 0.0;
        $count30 = 0;

        foreach ($series as $row) {
            $day = $row['day'] instanceof \DateTimeInterface ? $row['day']->format('Y-m-d') : (string) $row['day'];
            $revenue = (float) ($row['revenue'] ?? 0);
            $count = (int) ($row['count'] ?? 0);

            if ($day >= $cutoff30) {
                $last30 += $revenue;
                $count30 += $count;
            } else {
                $prev30 += $revenue;
            }

            if ($day >= $cutoff90) {
                $last90 += $revenue;
            } else {
                $prev90 += $revenue;
            }
        }

        $trendBase = $prev90 > 0 ? ($last90 - $prev90) / $prev90 : 0.0;
        $trend = $prev30 > 0 ? ($last30 - $prev30) / $prev30 : $trendBase;
        $trend = max(-0.3, min(0.3, $trend));
        $next30 = $last30 > 0 ? $last30 * (1 + $trend) : $prev30;
        $avgFee = $count30 > 0 ? $last30 / $count30 : 0.0;

        $monthlyForecast = $this->buildMonthlyForecast($last30, $trend, 3);

        return [
            'last_30' => round($last30, 2),
            'prev_30' => round($prev30, 2),
            'next_30' => round($next30, 2),
            'avg_fee' => round($avgFee, 2),
            'trend' => round($trend, 3),
            'monthly_forecast' => $monthlyForecast,
            'generated_at' => date('c'),
        ];
    }

    public function getDoctorRevenueWeekly(string $doctorId): ?array
    {
        $since = new \DateTimeImmutable('-84 days');
        $series = $this->consultationRepository->getDoctorRevenueSeries($doctorId, $since);
        if (count($series) === 0) {
            return null;
        }

        $byWeek = [];
        foreach ($series as $row) {
            $date = $row['day'] instanceof \DateTimeInterface ? $row['day'] : new \DateTimeImmutable((string) $row['day']);
            $weekStart = $date->modify('monday this week')->format('Y-m-d');
            if (!isset($byWeek[$weekStart])) {
                $byWeek[$weekStart] = 0.0;
            }
            $byWeek[$weekStart] += (float) ($row['revenue'] ?? 0);
        }

        ksort($byWeek);
        $labels = [];
        $values = [];
        foreach ($byWeek as $weekStart => $revenue) {
            $labels[] = $weekStart;
            $values[] = round($revenue, 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    public function getDoctorRevenueMonthly(string $doctorId): ?array
    {
        $since = new \DateTimeImmutable('-365 days');
        $series = $this->consultationRepository->getDoctorRevenueSeries($doctorId, $since);
        if (count($series) === 0) {
            return null;
        }

        $byMonth = [];
        foreach ($series as $row) {
            $date = $row['day'] instanceof \DateTimeInterface ? $row['day'] : new \DateTimeImmutable((string) $row['day']);
            $month = $date->format('Y-m');
            if (!isset($byMonth[$month])) {
                $byMonth[$month] = 0.0;
            }
            $byMonth[$month] += (float) ($row['revenue'] ?? 0);
        }

        ksort($byMonth);
        $labels = array_keys($byMonth);
        $values = array_map(fn ($v) => round($v, 2), array_values($byMonth));

        if (count($labels) > 12) {
            $labels = array_slice($labels, -12);
            $values = array_slice($values, -12);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getDoctorProfitAlerts(string $doctorId): array
    {
        $alerts = [];
        $profit = $this->getDoctorProfitPredictions($doctorId);
        if (!$profit) {
            return $alerts;
        }

        $next30 = (float) ($profit['next_30'] ?? 0);
        $last30 = (float) ($profit['last_30'] ?? 0);
        $trend = (float) ($profit['trend'] ?? 0);
        $avgFee = (float) ($profit['avg_fee'] ?? 0);

        $threshold = $last30 * 0.8;
        if ($last30 > 0 && $next30 < $threshold) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Baisse de revenus prévue',
                'message' => "Les revenus prévus sont inférieurs à 80% des 30 derniers jours.",
            ];
        }

        if ($trend < -0.1) {
            $alerts[] = [
                'type' => 'critical',
                'title' => 'Tendance négative',
                'message' => "La tendance mensuelle est en baisse notable. Ajustez le planning et la disponibilité.",
            ];
        }

        if ($avgFee > 0 && $avgFee < 30) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Honoraires faibles',
                'message' => "Les honoraires moyens sont bas. Vérifiez les tarifs ou la facturation des actes.",
            ];
        }

        return $alerts;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildMonthlyForecast(float $last30, float $trend, int $months): array
    {
        $result = [];
        $base = $last30;
        $current = (new \DateTimeImmutable('first day of next month'))->setTime(0, 0, 0);

        for ($i = 0; $i < $months; $i++) {
            $base = $base * (1 + $trend);
            $result[] = [
                'month' => $current->format('Y-m'),
                'revenue' => round($base, 2),
            ];
            $current = $current->modify('+1 month');
        }

        return $result;
    }
    /**
     * Suggestions pour ameliorer le planning a partir des consultations.
     *
     * @return array<int, array<string, string>>
     */
    private function buildScheduleRecommendations(string $doctorId): array
    {
        $since = new \DateTimeImmutable('-14 days');
        $consultations = $this->consultationRepository->findByMedecinSinceOrderedByDateTime($doctorId, $since);
        if (count($consultations) === 0) {
            return [[
                'type' => 'planning',
                'title' => 'Optimiser le planning',
                'description' => "Peu de données récentes. Pensez à répartir les consultations sur la semaine et à proposer des créneaux en fin de journée.",
            ]];
        }

        $byDay = [];
        foreach ($consultations as $consultation) {
            $date = $consultation->getDateConsultation();
            if (!$date) {
                continue;
            }
            $key = $date->format('Y-m-d');
            if (!isset($byDay[$key])) {
                $byDay[$key] = [];
            }
            $time = $consultation->getTimeConsultation();
            if ($time) {
                $start = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $key . ' ' . $time->format('H:i:s'));
                if ($start) {
                    $patient = $consultation->getPatient();
                    $patientName = $patient ? trim(($patient->getFirstName() ?? '') . ' ' . ($patient->getLastName() ?? '')) : 'Patient';
                    $byDay[$key][] = [
                        'start' => $start,
                        'duration' => (int) $consultation->getDuration(),
                        'patient' => $patientName !== '' ? $patientName : 'Patient',
                    ];
                }
            }
        }

        $dayCounts = [];
        $gaps = [];
        foreach ($byDay as $day => $entries) {
            $dayCounts[] = count($entries);
            usort($entries, fn ($a, $b) => $a['start'] <=> $b['start']);
            for ($i = 0; $i < count($entries) - 1; $i++) {
                $currentEnd = $entries[$i]['start']->modify('+' . max(10, $entries[$i]['duration']) . ' minutes');
                $gap = $entries[$i + 1]['start']->getTimestamp() - $currentEnd->getTimestamp();
                if ($gap > 0) {
                    $gaps[] = $gap / 60;
                }
            }
        }

        $avgPerDay = count($dayCounts) > 0 ? array_sum($dayCounts) / count($dayCounts) : 0;
        $maxPerDay = count($dayCounts) > 0 ? max($dayCounts) : 0;
        $avgGap = count($gaps) > 0 ? array_sum($gaps) / count($gaps) : 0;

        $recs = [];
        if ($avgGap > 90) {
            $recs[] = [
                'type' => 'planning',
                'title' => 'Réduire les creux',
                'description' => "Vous avez des créneaux vides importants. Regroupez les consultations ou ouvrez des créneaux ciblés.",
            ];
        } elseif ($avgGap > 0 && $avgGap < 15) {
            $recs[] = [
                'type' => 'planning',
                'title' => 'Ajouter des marges',
                'description' => "Les consultations sont très rapprochées. Ajoutez des marges pour éviter les retards.",
            ];
        }

        if ($avgPerDay > 0 && $maxPerDay > ($avgPerDay * 1.6)) {
            $recs[] = [
                'type' => 'planning',
                'title' => 'Répartir la charge',
                'description' => "Certaines journées sont surchargées. Répartissez les rendez-vous sur la semaine.",
            ];
        }

        $shiftRec = $this->buildConsultationShiftSuggestion($byDay);
        if ($shiftRec) {
            $recs[] = $shiftRec;
        }

        if (count($recs) === 0) {
            $recs[] = [
                'type' => 'planning',
                'title' => 'Optimiser le planning',
                'description' => "Aucune anomalie détectée. Vous pouvez tester des créneaux matinaux ou en fin de journée pour augmenter la flexibilité.",
            ];
        }

        return $recs;
    }

    /**
     * Suggest a time change based on overloaded vs lighter days.
     */
    private function buildConsultationShiftSuggestion(array $byDay): ?array
    {
        if (count($byDay) < 2) {
            return null;
        }

        $dayCounts = [];
        foreach ($byDay as $day => $entries) {
            $dayCounts[$day] = count($entries);
        }

        $maxDay = array_key_first($dayCounts);
        $minDay = array_key_first($dayCounts);
        foreach ($dayCounts as $day => $count) {
            if ($count > ($dayCounts[$maxDay] ?? 0)) {
                $maxDay = $day;
            }
            if ($count < ($dayCounts[$minDay] ?? 0)) {
                $minDay = $day;
            }
        }

        if ($maxDay === $minDay || ($dayCounts[$maxDay] - $dayCounts[$minDay]) < 2) {
            return null;
        }

        $maxEntries = $byDay[$maxDay] ?? [];
        $minEntries = $byDay[$minDay] ?? [];
        usort($maxEntries, fn ($a, $b) => $a['start'] <=> $b['start']);
        usort($minEntries, fn ($a, $b) => $a['start'] <=> $b['start']);

        $candidate = $maxEntries[count($maxEntries) - 1] ?? null;
        if (!$candidate) {
            return null;
        }

        $options = [];
        foreach ($minEntries as $entry) {
            $time = $entry['start']->format('H:i');
            if (!in_array($time, $options, true)) {
                $options[] = $time;
            }
            if (count($options) >= 3) {
                break;
            }
        }

        if (count($options) === 0) {
            return null;
        }

        $suggestedFrom = $candidate['start'];
        $duration = max(10, (int) ($candidate['duration'] ?? 20));
        $patientName = $candidate['patient'] ?? 'Patient';
        $optionsText = implode(', ', $options);

        return [
            'type' => 'planning',
            'title' => 'Proposer un décalage d\'horaire',
            'description' => sprintf(
                "Le %s est plus chargé. Déplacer le rendez-vous de %s (le %s à %s-%s) vers %s à l'un de ces horaires: %s.",
                (new \DateTimeImmutable($maxDay))->format('d/m'),
                $patientName,
                (new \DateTimeImmutable($maxDay))->format('d/m'),
                $suggestedFrom->format('H:i'),
                $suggestedFrom->modify('+' . $duration . ' minutes')->format('H:i'),
                (new \DateTimeImmutable($minDay))->format('d/m'),
                $optionsText
            ),
        ];
    }

    private function buildDoctorStats(): array
    {
        $since = new \DateTimeImmutable('-30 days');
        $rawStats = $this->consultationRepository->getDoctorStats($since);
        $byUuid = [];
        foreach ($rawStats as $row) {
            $uuid = (string) ($row['doctor_uuid'] ?? '');
            if ($uuid === '') {
                continue;
            }
            $byUuid[$uuid] = [
                'total_count' => (int) ($row['total_count'] ?? 0),
                'avg_duration' => (float) ($row['avg_duration'] ?? 0),
                'emergency_count' => (int) ($row['emergency_count'] ?? 0),
                'recent_count' => (int) ($row['recent_count'] ?? 0),
            ];
        }

        $doctors = $this->medecinRepository->findAll();
        $maxRecent = 0;
        foreach ($doctors as $doctor) {
            $uuid = $doctor->getId();
            $recent = $byUuid[$uuid]['recent_count'] ?? 0;
            if ($recent > $maxRecent) {
                $maxRecent = $recent;
            }
        }
        if ($maxRecent <= 0) {
            $maxRecent = 1;
        }

        $sumRecent = 0;
        $sumEmergency = 0;
        $doctorCount = max(1, count($doctors));

        $statsByUuid = [];
        foreach ($doctors as $doctor) {
            $uuid = $doctor->getId();
            $raw = $byUuid[$uuid] ?? [
                'total_count' => 0,
                'avg_duration' => 0,
                'emergency_count' => 0,
                'recent_count' => 0,
            ];

            $recent = (int) $raw['recent_count'];
            $popularity = $recent / $maxRecent;

            $sumRecent += $recent;
            $sumEmergency += (int) $raw['emergency_count'];

            $statsByUuid[$uuid] = [
                'doctor_id' => $uuid,
                'specialty' => (string) ($doctor->getSpecialite() ?? ''),
                'total_count' => (int) $raw['total_count'],
                'avg_duration' => (float) $raw['avg_duration'],
                'emergency_count' => (int) $raw['emergency_count'],
                'recent_count' => $recent,
                'popularity_score' => $popularity,
            ];
        }

        return [
            'by_uuid' => $statsByUuid,
            'avg_recent' => $sumRecent / $doctorCount,
            'avg_emergency' => $sumEmergency / $doctorCount,
        ];
    }

    private function buildDoctorFeatures(string $doctorId): ?array
    {
        $stats = $this->buildDoctorStats();
        if (!isset($stats['by_uuid'][$doctorId])) {
            return null;
        }

        $doctor = $stats['by_uuid'][$doctorId];
        return [
            'avg_consultation_time' => (float) $doctor['avg_duration'],
            'popularity_score' => (float) $doctor['popularity_score'],
            'actual_consultations' => (int) $doctor['recent_count'],
        ];
    }

    private function buildAllDoctorFeatures(): array
    {
        $stats = $this->buildDoctorStats();
        $doctors = [];

        foreach ($stats['by_uuid'] as $doctor) {
            $doctors[] = [
                'doctor_id' => $doctor['doctor_id'],
                'specialty' => $doctor['specialty'],
                'features' => [
                    'avg_consultation_time' => (float) $doctor['avg_duration'],
                    'popularity_score' => (float) $doctor['popularity_score'],
                    'actual_consultations' => (int) $doctor['recent_count'],
                ],
            ];
        }

        return $doctors;
    }

    /**
     * Get treatment effectiveness data based on real consultations.
     *
     * @return array<string, mixed>
     */
    public function getTreatmentEffectivenessData(?string $doctorId): array
    {
        $since = new \DateTimeImmutable('-42 days');
        $consultations = $doctorId !== null
            ? $this->consultationRepository->findByMedecinSinceOrderedByDateTime($doctorId, $since)
            : [];

        $byPatient = [];
        foreach ($consultations as $consultation) {
            $patient = $consultation->getPatient();
            if (!$patient) {
                continue;
            }
            $pid = $patient->getId();
            $vitals = is_array($consultation->getVitals()) ? $consultation->getVitals() : [];
            $score = $this->computeHealthScoreFromVitals($vitals);
            $date = $consultation->getDateConsultation();
            if (!isset($byPatient[$pid])) {
                $byPatient[$pid] = [
                    'name' => trim(($patient->getFirstName() ?? '') . ' ' . ($patient->getLastName() ?? '')),
                    'scores' => [],
                ];
            }
            if ($date && $score !== null) {
                $byPatient[$pid]['scores'][$date->format('Y-m-d')] = $score;
            }
        }

        $labels = [];
        for ($i = 5; $i >= 0; $i--) {
            $weekStart = (new \DateTimeImmutable())->modify("-$i weeks")->modify('monday this week');
            $labels[] = 'S-' . $weekStart->format('W');
        }

        $datasets = [];
        $colors = [
            ['border' => '#00A790', 'bg' => 'rgba(0, 167, 144, 0.1)'],
            ['border' => '#ef4444', 'bg' => 'rgba(239, 68, 68, 0.1)'],
            ['border' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)'],
            ['border' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.1)'],
            ['border' => '#8b5cf6', 'bg' => 'rgba(139, 92, 246, 0.1)'],
        ];

        $patientIndex = 0;
        foreach ($byPatient as $pid => $data) {
            if (count($data['scores']) === 0) {
                continue;
            }
            $weeklyScores = [];
            for ($i = 5; $i >= 0; $i--) {
                $weekStart = (new \DateTimeImmutable())->modify("-$i weeks")->modify('monday this week');
                $weekEnd = $weekStart->modify('+6 days');
                $weekScores = [];
                foreach ($data['scores'] as $dateStr => $score) {
                    $date = new \DateTimeImmutable($dateStr);
                    if ($date >= $weekStart && $date <= $weekEnd) {
                        $weekScores[] = $score;
                    }
                }
                $weeklyScores[] = count($weekScores) > 0 ? round(array_sum($weekScores) / count($weekScores)) : null;
            }

            $color = $colors[$patientIndex % count($colors)];
            $datasets[] = [
                'label' => $data['name'] ?: 'Patient ' . ($patientIndex + 1),
                'data' => $weeklyScores,
                'borderColor' => $color['border'],
                'backgroundColor' => $color['bg'],
                'fill' => false,
                'tension' => 0.4,
            ];
            $patientIndex++;
            if ($patientIndex >= 5) {
                break;
            }
        }

        if (count($datasets) === 0) {
            return $this->getSimulatedTreatmentEffectivenessData();
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * Compute health score from vitals array.
     */
    private function computeHealthScoreFromVitals(array $vitals): ?int
    {
        if (empty($vitals)) {
            return null;
        }

        $score = 100;

        $bp = is_array($vitals['bloodPressure'] ?? null) ? $vitals['bloodPressure'] : [];
        $systolic = (int) ($bp['systolic'] ?? $vitals['bloodPressureSystolic'] ?? 0);
        $diastolic = (int) ($bp['diastolic'] ?? $vitals['bloodPressureDiastolic'] ?? 0);

        if ($systolic > 0 && $diastolic > 0) {
            if ($systolic > 140 || $diastolic > 90) {
                $score -= 15;
            } elseif ($systolic < 90 || $diastolic < 60) {
                $score -= 10;
            }
        }

        $pulse = (int) ($vitals['pulse'] ?? $vitals['heartRate'] ?? 0);
        if ($pulse > 0) {
            if ($pulse > 100 || $pulse < 60) {
                $score -= 10;
            }
        }

        $temp = (float) ($vitals['temperature'] ?? 0);
        if ($temp > 0) {
            if ($temp > 38) {
                $score -= 15;
            } elseif ($temp < 36) {
                $score -= 10;
            }
        }

        $spo2 = (float) ($vitals['spo2'] ?? $vitals['oxygenSaturation'] ?? 0);
        if ($spo2 > 0) {
            if ($spo2 < 92) {
                $score -= 20;
            } elseif ($spo2 < 95) {
                $score -= 10;
            }
        }

        return max(0, min(100, $score));
    }

    /**
     * Simulated treatment effectiveness data.
     */
    private function getSimulatedTreatmentEffectivenessData(): array
    {
        return [
            'labels' => ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'],
            'datasets' => [
                [
                    'label' => 'Marie D.',
                    'data' => [65, 70, 75, 78, 82, 85],
                    'borderColor' => '#00A790',
                    'backgroundColor' => 'rgba(0, 167, 144, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Jean M.',
                    'data' => [70, 68, 65, 62, 60, 62],
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Sophie B.',
                    'data' => [68, 70, 71, 72, 72, 73],
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    public function getDoctorDebugInfo(?string $doctorId): array
    {
        $stats = $this->buildDoctorStats();
        $doctorStats = null;
        $features = null;

        if ($doctorId !== null && isset($stats['by_uuid'][$doctorId])) {
            $doctorStats = $stats['by_uuid'][$doctorId];
            $features = [
                'avg_consultation_time' => (float) $doctorStats['avg_duration'],
                'popularity_score' => (float) $doctorStats['popularity_score'],
                'actual_consultations' => (int) $doctorStats['recent_count'],
            ];
        }

        return [
            'doctor_id' => $doctorId,
            'doctor_found' => $doctorStats !== null,
            'doctor_stats' => $doctorStats,
            'features' => $features,
            'avg_recent_all' => $stats['avg_recent'],
            'avg_emergency_all' => $stats['avg_emergency'],
            'doctor_count' => count($stats['by_uuid']),
        ];
    }

    /**
     * Generate simulated data when the API is unavailable.
     */
    public function getSimulatedDashboardData(): array
    {
        return [
            'api_available' => false,
            'predictions' => [
                'doctor_id' => 'demo',
                'predictions' => [
                    ['day' => date('Y-m-d'), 'day_name' => 'Monday', 'predicted_consultations' => 12],
                    ['day' => date('Y-m-d', strtotime('+1 day')), 'day_name' => 'Tuesday', 'predicted_consultations' => 15],
                    ['day' => date('Y-m-d', strtotime('+2 days')), 'day_name' => 'Wednesday', 'predicted_consultations' => 18],
                    ['day' => date('Y-m-d', strtotime('+3 days')), 'day_name' => 'Thursday', 'predicted_consultations' => 14],
                    ['day' => date('Y-m-d', strtotime('+4 days')), 'day_name' => 'Friday', 'predicted_consultations' => 16],
                    ['day' => date('Y-m-d', strtotime('+5 days')), 'day_name' => 'Saturday', 'predicted_consultations' => 8],
                    ['day' => date('Y-m-d', strtotime('+6 days')), 'day_name' => 'Sunday', 'predicted_consultations' => 5],
                ],
                'confidence' => 0.85,
            ],
            'recommendations' => [
                'doctor_id' => 'demo',
                'cluster' => 0,
                'recommendations' => [
                    [
                        'type' => 'planning',
                        'title' => 'Réduire les creux',
                        'description' => 'Vous avez des créneaux vides importants. Regroupez les consultations ou ouvrez des créneaux ciblés.',
                    ],
                    [
                        'type' => 'planning',
                        'title' => 'Répartir la charge',
                        'description' => 'Certaines journées sont surchargées. Répartissez les rendez-vous sur la semaine.',
                    ],
                    [
                        'type' => 'planning',
                        'title' => 'Ajouter des marges',
                        'description' => 'Les consultations sont très rapprochées. Ajoutez des marges pour éviter les retards.',
                    ],
                    [
                        'type' => 'planning',
                        'title' => 'Optimiser le planning',
                        'description' => 'Ouvrez des créneaux courts pour absorber les urgences sans décaler les rendez-vous.',
                    ],
                    [
                        'type' => 'planning',
                        'title' => 'Proposer un décalage d\'horaire',
                        'description' => 'Le lundi est plus chargé. Envisagez de déplacer certains rendez-vous vers le mercredi.',
                    ],
                    [
                        'type' => 'general',
                        'title' => 'Maintenir le cap',
                        'description' => 'Votre activité est dans la moyenne. Continuez ainsi !',
                    ],
                    [
                        'type' => 'profit',
                        'title' => 'Stabiliser les revenus',
                        'description' => 'Mettez en avant vos disponibilités pour réduire les jours creux.',
                    ],
                ],
            ],
            'profit_predictions' => [
                'last_30' => 4200,
                'prev_30' => 3900,
                'next_30' => 4400,
                'avg_fee' => 70,
                'trend' => 0.08,
                'monthly_forecast' => [
                    ['month' => date('Y-m', strtotime('first day of next month')), 'revenue' => 4600],
                    ['month' => date('Y-m', strtotime('first day of +2 month')), 'revenue' => 4750],
                    ['month' => date('Y-m', strtotime('first day of +3 month')), 'revenue' => 4900],
                ],
                'generated_at' => date('c'),
            ],
            'revenue_weekly' => [
                'labels' => ['2026-01-06', '2026-01-13', '2026-01-20', '2026-01-27', '2026-02-03', '2026-02-10'],
                'values' => [600, 820, 740, 900, 780, 860],
            ],
            'revenue_monthly' => [
                'labels' => ['2025-03', '2025-04', '2025-05', '2025-06', '2025-07', '2025-08', '2025-09', '2025-10', '2025-11', '2025-12', '2026-01', '2026-02'],
                'values' => [3100, 2950, 3300, 3600, 3400, 3550, 3700, 3900, 4100, 4050, 4200, 4400],
            ],
            'profit_alerts' => [
                [
                    'type' => 'warning',
                    'title' => 'Baisse de revenus prévue',
                    'message' => 'Les revenus prévus sont inférieurs à 80% des 30 derniers jours.',
                ],
            ],
            'treatment_effectiveness' => [
                'labels' => ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'],
                'datasets' => [
                    [
                        'label' => 'Marie D.',
                        'data' => [65, 70, 75, 78, 82, 85],
                        'borderColor' => '#00A790',
                        'backgroundColor' => 'rgba(0, 167, 144, 0.1)',
                        'fill' => false,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Jean M.',
                        'data' => [70, 68, 65, 62, 60, 62],
                        'borderColor' => '#ef4444',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'fill' => false,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Sophie B.',
                        'data' => [68, 70, 71, 72, 72, 73],
                        'borderColor' => '#f59e0b',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                        'fill' => false,
                        'tension' => 0.4,
                    ],
                ],
            ],
            'all_predictions' => [
                'total_doctors' => 1,
                'predictions' => [
                    ['doctor_id' => 'demo', 'specialty' => 'Generaliste', 'predicted_daily_avg' => 15, 'cluster' => 0],
                ],
                'generated_at' => date('c'),
            ],
        ];
    }
}
