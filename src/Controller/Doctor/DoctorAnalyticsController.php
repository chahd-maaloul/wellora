<?php

namespace App\Controller\Doctor;

use App\Entity\Healthentry;
use App\Entity\Healthjournal;
use App\Entity\Symptom;
use App\Repository\HealthentryRepository;
use App\Repository\HealthjournalRepository;
use App\Repository\SymptomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Callback;

#[Route('/doctor/analytics')]
final class DoctorAnalyticsController extends AbstractController
{
    /**
     * Dashboard overview with key health metrics
     */
    #[Route(name: 'app_doctor_analytics_dashboard', methods: ['GET'])]
    public function dashboard(
        HealthjournalRepository $healthjournalRepository,
        HealthentryRepository $healthentryRepository,
        SymptomRepository $symptomRepository
    ): Response {
        // Get all journals
        $journals = $healthjournalRepository->findAll();
        
        // Calculate entry counts for each journal
        $journalCounts = [];
        foreach ($journals as $journal) {
            $count = $healthentryRepository->createQueryBuilder('e')
                ->select('COUNT(e.id)')
                ->where('e.journal = :journal')
                ->setParameter('journal', $journal)
                ->getQuery()
                ->getSingleScalarResult();
            $journalCounts[$journal->getId()] = $count;
        }

        // Get statistics
        $totalJournals = count($journals);
        $totalEntries = $healthentryRepository->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $avgWeight = $healthentryRepository->createQueryBuilder('e')
            ->select('AVG(e.poids)')
            ->getQuery()
            ->getSingleScalarResult();

        $avgGlycemie = $healthentryRepository->createQueryBuilder('e')
            ->select('AVG(e.glycemie)')
            ->getQuery()
            ->getSingleScalarResult();

        // Get recent entries (last 30 days)
        $recentEntries = $healthentryRepository->createQueryBuilder('e')
            ->where('e.date >= :date')
            ->setParameter('date', new \DateTime('-30 days'))
            ->orderBy('e.date', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Get most common symptoms
        $commonSymptoms = $symptomRepository->createQueryBuilder('s')
            ->select('s.type as symptomName, COUNT(s.id) as symptomCount')
            ->groupBy('s.type')
            ->orderBy('symptomCount', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $this->render('doctor/analytics/dashboard.html.twig', [
            'journals' => $journals,
            'journalCounts' => $journalCounts,
            'stats' => [
                'totalJournals' => $totalJournals,
                'totalEntries' => $totalEntries,
                'avgWeight' => round($avgWeight ?: 0, 1),
                'avgGlycemie' => round($avgGlycemie ?: 0, 2),
            ],
            'recentEntries' => $recentEntries,
            'commonSymptoms' => $commonSymptoms,
        ]);
    }

    /**
     * Journal detail with all entries and analytics
     */
    #[Route('/journal/{id}', name: 'app_doctor_analytics_journal_detail', methods: ['GET'])]
    public function journalDetail(
        Healthjournal $journal,
        HealthentryRepository $healthentryRepository,
        SymptomRepository $symptomRepository
    ): Response {
        // Get all entries for this journal
        $entries = $healthentryRepository->createQueryBuilder('e')
            ->where('e.journal = :journal')
            ->setParameter('journal', $journal)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();

        // Calculate statistics
        $weights = !empty($entries) ? array_map(fn($e) => $e->getPoids(), $entries) : [];
        $glycemies = !empty($entries) ? array_map(fn($e) => $e->getGlycemie(), $entries) : [];
        $sleepHours = !empty($entries) ? array_map(fn($e) => $e->getSommeil(), $entries) : [];
        $firstEntry = !empty($entries) ? $entries[0] : null;
        $lastEntry = !empty($entries) ? end($entries) : null;
        
        $stats = [
            'entryCount' => count($entries),
            'avgWeight' => count($weights) > 0 ? round(array_sum($weights) / count($weights), 1) : 0,
            'minWeight' => count($weights) > 0 ? min($weights) : 0,
            'maxWeight' => count($weights) > 0 ? max($weights) : 0,
            'weightChange' => count($weights) >= 2 ? round(end($weights) - $weights[0], 1) : 0,
            'avgGlycemie' => count($glycemies) > 0 ? round(array_sum($glycemies) / count($glycemies), 2) : 0,
            'avgSleep' => count($sleepHours) > 0 ? round(array_sum($sleepHours) / count($sleepHours), 1) : 0,
            'startDate' => $firstEntry && $firstEntry->getDate() ? $firstEntry->getDate()->format('d/m/Y') : '-',
            'endDate' => $lastEntry && $lastEntry->getDate() ? $lastEntry->getDate()->format('d/m/Y') : '-',
        ];

        // Get symptoms for this journal
        $symptoms = $symptomRepository->createQueryBuilder('s')
            ->join('s.entry', 'e')
            ->where('e.journal = :journal')
            ->setParameter('journal', $journal)
            ->select('s.type, COUNT(s.id) as count')
            ->groupBy('s.type')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();

        // Group entries by month for trend analysis
        $monthlyData = [];
        foreach ($entries as $entry) {
            $month = $entry->getDate()->format('Y-m');
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = ['weight' => [], 'glycemie' => [], 'sleep' => []];
            }
            $monthlyData[$month]['weight'][] = $entry->getPoids();
            $monthlyData[$month]['glycemie'][] = $entry->getGlycemie();
            $monthlyData[$month]['sleep'][] = $entry->getSommeil();
        }

        // Calculate monthly averages
        $monthlyStats = [];
        foreach ($monthlyData as $month => $data) {
            $monthlyStats[$month] = [
                'avgWeight' => round(array_sum($data['weight']) / count($data['weight']), 1),
                'avgGlycemie' => round(array_sum($data['glycemie']) / count($data['glycemie']), 2),
                'avgSleep' => round(array_sum($data['sleep']) / count($data['sleep']), 1),
            ];
        }

        return $this->render('doctor/analytics/journal-detail.html.twig', [
            'journal' => $journal,
            'entries' => $entries,
            'stats' => $stats,
            'symptoms' => $symptoms,
            'monthlyStats' => $monthlyStats,
        ]);
    }

    /**
     * Generate health report for a journal
     */
    #[Route('/report/{id}', name: 'app_doctor_analytics_report', methods: ['GET'])]
    public function generateReport(
        Healthjournal $journal,
        HealthentryRepository $healthentryRepository,
        SymptomRepository $symptomRepository
    ): Response {
        $entries = $healthentryRepository->createQueryBuilder('e')
            ->where('e.journal = :journal')
            ->setParameter('journal', $journal)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();

        if (empty($entries)) {
            $this->addFlash('warning', 'No entries found for this journal.');
            return $this->redirectToRoute('app_doctor_analytics_dashboard');
        }

        // Calculate comprehensive statistics
        $weights = array_map(fn($e) => $e->getPoids(), $entries);
        $glycemies = array_map(fn($e) => $e->getGlycemie(), $entries);
        $sleepHours = array_map(fn($e) => $e->getSommeil(), $entries);
        $tensions = array_map(fn($e) => $e->getTension(), $entries);

        $firstEntry = $entries[0];
        $lastEntry = end($entries);

        // Trend analysis
        $weightTrend = count($weights) >= 2 ? 
            ($weights[count($weights)-1] - $weights[0]) : 0;
        $glycemieTrend = count($glycemies) >= 2 ? 
            ($glycemies[count($glycemies)-1] - $glycemies[0]) : 0;

        // Health assessment
        $assessments = [];
        
        // Weight assessment
        if ($weightTrend < -2) {
            $assessments[] = [
                'metric' => 'Weight',
                'status' => 'warning',
                'message' => 'Significant weight loss detected (' . abs($weightTrend) . ' kg). Monitor closely.',
            ];
        } elseif ($weightTrend > 2) {
            $assessments[] = [
                'metric' => 'Weight',
                'status' => 'warning',
                'message' => 'Significant weight gain detected (' . $weightTrend . ' kg). Consider lifestyle review.',
            ];
        } else {
            $assessments[] = [
                'metric' => 'Weight',
                'status' => 'good',
                'message' => 'Weight is stable.',
            ];
        }

        // Glycemie assessment
        $avgGlycemie = array_sum($glycemies) / count($glycemies);
        if ($avgGlycemie > 1.4) {
            $assessments[] = [
                'metric' => 'Blood Sugar',
                'status' => 'danger',
                'message' => 'Average blood sugar is elevated (' . round($avgGlycemie, 2) . ' g/l). Recommend dietary review.',
            ];
        } elseif ($avgGlycemie < 0.7) {
            $assessments[] = [
                'metric' => 'Blood Sugar',
                'status' => 'warning',
                'message' => 'Average blood sugar is low (' . round($avgGlycemie, 2) . ' g/l). Monitor for hypoglycemia.',
            ];
        } else {
            $assessments[] = [
                'metric' => 'Blood Sugar',
                'status' => 'good',
                'message' => 'Blood sugar levels are within normal range.',
            ];
        }

        // Sleep assessment
        $avgSleep = array_sum($sleepHours) / count($sleepHours);
        if ($avgSleep < 6) {
            $assessments[] = [
                'metric' => 'Sleep',
                'status' => 'warning',
                'message' => 'Average sleep is insufficient (' . round($avgSleep, 1) . ' hours).',
            ];
        } else {
            $assessments[] = [
                'metric' => 'Sleep',
                'status' => 'good',
                'message' => 'Sleep patterns are healthy.',
            ];
        }

        // Symptom analysis
        $symptoms = $symptomRepository->createQueryBuilder('s')
            ->join('s.entry', 'e')
            ->where('e.journal = :journal')
            ->setParameter('journal', $journal)
            ->select('s.type, COUNT(s.id) as count, s.severity')
            ->groupBy('s.type, s.severity')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('doctor/analytics/report.html.twig', [
            'journal' => $journal,
            'report' => [
                'generatedAt' => new \DateTime(),
                'period' => [
                    'start' => $firstEntry->getDate()->format('d/m/Y'),
                    'end' => $lastEntry->getDate()->format('d/m/Y'),
                    'days' => $firstEntry->getDate()->diff($lastEntry->getDate())->days,
                ],
                'statistics' => [
                    'totalEntries' => count($entries),
                    'avgWeight' => round($avgWeight = array_sum($weights) / count($weights), 1),
                    'minWeight' => min($weights),
                    'maxWeight' => max($weights),
                    'weightChange' => $weightTrend,
                    'avgGlycemie' => round($avgGlycemie, 2),
                    'avgSleep' => round($avgSleep, 1),
                    'avgTension' => count(array_filter($tensions)) > 0 ? 
                        round(array_sum(array_filter($tensions)) / count(array_filter($tensions)), 1) : 'N/A',
                ],
                'assessments' => $assessments,
                'symptoms' => $symptoms,
                'monthlyTrend' => $this->calculateMonthlyTrend($entries),
            ],
        ]);
    }

    /**
     * Export journal data as JSON
     */
    #[Route('/export/{id}', name: 'app_doctor_analytics_export', methods: ['GET'])]
    public function export(
        Healthjournal $journal,
        HealthentryRepository $healthentryRepository
    ): Response {
        $entries = $healthentryRepository->createQueryBuilder('e')
            ->where('e.journal = :journal')
            ->setParameter('journal', $journal)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();

        $data = [
            'journal' => [
                'name' => $journal->getName(),
                'startDate' => $journal->getDatedebut()->format('Y-m-d'),
                'endDate' => $journal->getDatefin()?->format('Y-m-d'),
            ],
            'entries' => array_map(function($entry) {
                return [
                    'date' => $entry->getDate()->format('Y-m-d'),
                    'weight' => $entry->getPoids(),
                    'bloodSugar' => $entry->getGlycemie(),
                    'bloodPressure' => $entry->getTension(),
                    'sleep' => $entry->getSommeil(),
                    'symptoms' => array_map(fn($s) => $s->getType(), $entry->getSymptoms()->toArray()),
                ];
            }, $entries),
        ];

        $response = new Response(json_encode($data, JSON_PRETTY_PRINT));
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment; filename="health-report-' . $journal->getId() . '.json"');

        return $response;
    }

    /**
     * Calculate monthly trend data
     */
    private function calculateMonthlyTrend(array $entries): array
    {
        $monthly = [];
        foreach ($entries as $entry) {
            $month = $entry->getDate()->format('Y-m');
            if (!isset($monthly[$month])) {
                $monthly[$month] = ['weights' => [], 'glycemies' => []];
            }
            $monthly[$month]['weights'][] = $entry->getPoids();
            $monthly[$month]['glycemies'][] = $entry->getGlycemie();
        }

        $trend = [];
        foreach ($monthly as $month => $data) {
            $trend[] = [
                'month' => $month,
                'avgWeight' => round(array_sum($data['weights']) / count($data['weights']), 1),
                'avgGlycemie' => round(array_sum($data['glycemies']) / count($data['glycemies']), 2),
            ];
        }

        return $trend;
    }
}
