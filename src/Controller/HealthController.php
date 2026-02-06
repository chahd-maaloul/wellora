<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/health')]
class HealthController extends AbstractController
{
    /**
     * Dashboard principal - Affiche le tableau de bord santé du patient
     */
    #[Route('/dashboard', name: 'health_index', methods: ['GET'])]
    public function index(): Response
    {
        // Données de signes vitaux simulées (à remplacer par des données réelles de la base)
        $vitalSigns = [
            'heartRate' => [
                'value' => 72,
                'status' => 'normal',
                'trend' => 'stable',
                'change' => '+2',
            ],
            'bloodPressure' => [
                'systolic' => 120,
                'diastolic' => 80,
                'status' => 'normal',
                'trend' => 'stable',
                'change' => '0',
            ],
            'temperature' => [
                'value' => 36.6,
                'status' => 'normal',
                'trend' => 'stable',
                'change' => '-0.1',
            ],
            'oxygenSaturation' => [
                'value' => 98,
                'status' => 'normal',
                'trend' => 'up',
                'change' => '+1',
            ],
        ];

        // Entrées récentes du journal de santé
        $entries = [
            [
                'id' => 1,
                'date' => new \DateTime('-1 day'),
                'mood' => 4,
                'energy' => 8,
                'sleep' => 7.5,
                'symptoms' => [],
                'notes' => 'Bonne journée, beaucoup d\'énergie',
            ],
            [
                'id' => 2,
                'date' => new \DateTime('-2 days'),
                'mood' => 3,
                'energy' => 6,
                'sleep' => 6.0,
                'symptoms' => ['Légère fatigue'],
                'notes' => 'Journée moyenne',
            ],
            [
                'id' => 3,
                'date' => new \DateTime('-3 days'),
                'mood' => 5,
                'energy' => 9,
                'sleep' => 8.0,
                'symptoms' => [],
                'notes' => 'Excellente journée',
            ],
        ];

        // Données pour les graphiques
        $chartData = [
            'energy' => [
                'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                'data' => [7, 6, 8, 7, 9, 8, 8],
            ],
            'mood' => [
                'labels' => ['Très mal', 'Mal', 'Moyen', 'Bien', 'Très bien'],
                'data' => [0, 1, 2, 3, 4],
            ],
        ];

        // Insights générés par l'IA
        $insights = [
            [
                'type' => 'pattern',
                'title' => 'Activité et sommeil',
                'description' => 'Vous dormez 23% mieux les soirées où vous faites une promenade.',
                'icon' => 'walking',
                'severity' => 'positive',
            ],
            [
                'type' => 'recommendation',
                'title' => 'Hydratation',
                'description' => 'Votre énergie est plus élevée les jours où vous buvez plus de 2L d\'eau.',
                'icon' => 'droplet',
                'severity' => 'info',
            ],
            [
                'type' => 'alert',
                'title' => 'Tension artérielle',
                'description' => 'Votre tension a légèrement augmenté cette semaine. Surveillez votre consommation de sel.',
                'icon' => 'heart-pulse',
                'severity' => 'warning',
            ],
        ];

        // Rendez-vous à venir
        $appointments = [
            [
                'id' => 1,
                'doctor' => 'Dr. Sophie Martin',
                'specialty' => 'Médecine générale',
                'date' => new \DateTime('+15 days'),
                'time' => '14:30',
                'type' => 'Consultation générale',
            ],
        ];

        // Médicaments du jour
        $medications = [
            [
                'id' => 1,
                'name' => 'Doliprane 500mg',
                'dosage' => '1 comprimé',
                'time' => 'Matin',
                'taken' => true,
                'icon' => 'sun',
            ],
            [
                'id' => 2,
                'name' => 'Vitamine D',
                'dosage' => '1 gélule',
                'time' => 'Soir',
                'taken' => false,
                'icon' => 'moon',
            ],
        ];

        return $this->render('health/dashboard.html.twig', [
            'vitalSigns' => $vitalSigns,
            'entries' => $entries,
            'chartData' => $chartData,
            'insights' => $insights,
            'appointments' => $appointments,
            'medications' => $medications,
        ]);
    }

    /**
     * Accessible Journal Entry - Affiche le formulaire d'entrée journalière accessible
     */
    #[Route('/accessible/journal-entry', name: 'health_journal_entry_accessible', methods: ['GET'])]
    public function journalEntryAccessible(): Response
    {
        return $this->render('health/accessible/journal-entry.html.twig');
    }

    /**
     * Health Journal - Affiche le journal de santé complet du patient
     */
    #[Route('/journal', name: 'health_journal', methods: ['GET'])]
    public function journal(): Response
    {
        // Données du journal de santé
        $journalEntries = [
            [
                'id' => 1,
                'date' => new \DateTime('today'),
                'mood' => 4,
                'energy' => 8,
                'sleep' => 7.5,
                'symptoms' => [],
                'notes' => 'Bonne journée, beaucoup d\'énergie',
                'weather' => 'sunny',
                'activities' => ['Marche 30min', 'Yoga'],
            ],
            [
                'id' => 2,
                'date' => new \DateTime('yesterday'),
                'mood' => 3,
                'energy' => 6,
                'sleep' => 6.0,
                'symptoms' => ['Légère fatigue', 'Céphalée'],
                'notes' => 'Journée moyenne, un peu stressant au travail',
                'weather' => 'cloudy',
                'activities' => ['Travail sedentaire'],
            ],
            [
                'id' => 3,
                'date' => new \DateTime('-2 days'),
                'mood' => 5,
                'energy' => 9,
                'sleep' => 8.0,
                'symptoms' => [],
                'notes' => 'Excellente journée, sortie en famille',
                'weather' => 'sunny',
                'activities' => ['Randonnée', 'Famille'],
            ],
            [
                'id' => 4,
                'date' => new \DateTime('-3 days'),
                'mood' => 4,
                'energy' => 7,
                'sleep' => 7.0,
                'symptoms' => ['Éternuements'],
                'notes' => 'Légère allergie printanière',
                'weather' => 'windy',
                'activities' => ['Jardinage'],
            ],
        ];

        // Statistiques du journal
        $stats = [
            'totalEntries' => count($journalEntries),
            'averageMood' => 4.0,
            'averageEnergy' => 7.5,
            'averageSleep' => 7.125,
            'mostCommonSymptoms' => ['Fatigue', 'Céphalée', 'Éternuements'],
            'bestDay' => 'Mardi',
            'worstDay' => 'Mercredi',
        ];

        return $this->render('health/journal.html.twig', [
            'entries' => $journalEntries,
            'stats' => $stats,
        ]);
    }

    /**
     * Symptom Tracker - Affiche le suivi des symptômes avec Body Map
     */
    #[Route('/symptoms', name: 'health_symptoms', methods: ['GET'])]
    public function symptoms(): Response
    {
        // Données des symptômes
        $symptoms = [
            [
                'id' => 1,
                'date' => new \DateTime('today'),
                'bodyPart' => 'head',
                'symptom' => 'Céphalée',
                'severity' => 3,
                'duration' => '2 heures',
                'triggers' => ['Stress', 'Écran'],
                'relief' => ['Repos', 'Paracétamol'],
            ],
            [
                'id' => 2,
                'date' => new \DateTime('yesterday'),
                'bodyPart' => 'back',
                'symptom' => 'Dorsalgie',
                'severity' => 4,
                'duration' => 'Toute la journée',
                'triggers' => ['Position assise'],
                'relief' => ['Étirements', 'Chaleur'],
            ],
            [
                'id' => 3,
                'date' => new \DateTime('-2 days'),
                'bodyPart' => 'throat',
                'symptom' => 'Mal de gorge',
                'severity' => 2,
                'duration' => '1 jour',
                'triggers' => [],
                'relief' => ['Thé au miel'],
            ],
        ];

        // Body parts for the body map
        $bodyParts = [
            ['id' => 'head', 'name' => 'Tête', 'icon' => 'fa-face-smile'],
            ['id' => 'throat', 'name' => 'Cou', 'icon' => 'fa-user'],
            ['id' => 'chest', 'name' => 'Poitrine', 'icon' => 'fa-child'],
            ['id' => 'arms', 'name' => 'Bras', 'icon' => 'fa-hand'],
            ['id' => 'back', 'name' => 'Dos', 'icon' => 'fa-person'],
            ['id' => 'stomach', 'name' => 'Ventre', 'icon' => 'fa-lemon'],
            ['id' => 'legs', 'name' => 'Jambes', 'icon' => 'fa-person-walking'],
            ['id' => 'feet', 'name' => 'Pieds', 'icon' => 'fa-shoe-prints'],
        ];

        // Symptom patterns
        $patterns = [
            [
                'symptom' => 'Céphalée',
                'frequency' => '2-3x par semaine',
                'commonTriggers' => ['Stress', 'Écran', 'Manque de sommeil'],
                'recommendation' => 'Pensez à faire des pauses régulières et à vous hydrater',
            ],
            [
                'symptom' => 'Dorsalgie',
                'frequency' => '1x par semaine',
                'commonTriggers' => ['Position assise prolongée', 'Mauvaise posture'],
                'recommendation' => 'Étirements et pauses recommandées toutes les 30 minutes',
            ],
        ];

        return $this->render('health/symptoms.html.twig', [
            'symptoms' => $symptoms,
            'bodyParts' => $bodyParts,
            'patterns' => $patterns,
        ]);
    }

    /**
     * Quick Entry - Enregistrement rapide d'une entrée journalière (AJAX)
     */
    #[Route('/quick-entry', name: 'health_quick_entry', methods: ['POST'])]
    public function quickEntry(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation des données
        if (!$data) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides',
            ], 400);
        }

        $requiredFields = ['mood', 'energy', 'sleep'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json([
                    'success' => false,
                    'message' => sprintf('Le champ "%s" est requis', $field),
                ], 400);
            }
        }

        // Validation des valeurs
        if ($data['mood'] < 1 || $data['mood'] > 5) {
            return $this->json([
                'success' => false,
                'message' => 'L\'humeur doit être comprise entre 1 et 5',
            ], 400);
        }

        if ($data['energy'] < 1 || $data['energy'] > 10) {
            return $this->json([
                'success' => false,
                'message' => 'Le niveau d\'énergie doit être compris entre 1 et 10',
            ], 400);
        }

        if ($data['sleep'] < 0 || $data['sleep'] > 24) {
            return $this->json([
                'success' => false,
                'message' => 'La durée de sommeil doit être comprise entre 0 et 24 heures',
            ], 400);
        }

        // TODO: Sauvegarder l'entrée en base de données
        // $entry = new HealthEntry();
        // $entry->setMood($data['mood']);
        // $entry->setEnergy($data['energy']);
        // $entry->setSleep($data['sleep']);
        // $entry->setSymptoms($data['symptoms'] ?? []);
        // $entry->setNotes($data['notes'] ?? null);
        // $entityManager->persist($entry);
        // $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Entrée enregistrée avec succès',
            'data' => [
                'id' => uniqid(),
                'date' => (new \DateTime())->format('Y-m-d H:i:s'),
                'mood' => $data['mood'],
                'energy' => $data['energy'],
                'sleep' => $data['sleep'],
            ],
        ]);
    }

    /**
     * Get Metrics - Récupère les signes vitaux (AJAX)
     */
    #[Route('/metrics', name: 'health_get_metrics', methods: ['GET'])]
    public function getMetrics(): JsonResponse
    {
        // TODO: Récupérer les données réelles depuis la base
        $metrics = [
            'heartRate' => [
                'value' => 72,
                'unit' => 'bpm',
                'status' => 'normal',
                'trend' => 'stable',
                'change' => '+2',
                'normalRange' => '60-100',
                'lastUpdated' => (new \DateTime('-30 minutes'))->format('H:i'),
            ],
            'bloodPressure' => [
                'systolic' => 120,
                'diastolic' => 80,
                'unit' => 'mmHg',
                'status' => 'normal',
                'trend' => 'stable',
                'change' => '0',
                'normalRange' => '90-120/60-80',
                'lastUpdated' => (new \DateTime('-1 hour'))->format('H:i'),
            ],
            'temperature' => [
                'value' => 36.6,
                'unit' => '°C',
                'status' => 'normal',
                'trend' => 'stable',
                'change' => '-0.1',
                'normalRange' => '36.1-37.2',
                'lastUpdated' => (new \DateTime('-2 hours'))->format('H:i'),
            ],
            'oxygenSaturation' => [
                'value' => 98,
                'unit' => '%',
                'status' => 'normal',
                'trend' => 'up',
                'change' => '+1',
                'normalRange' => '95-100',
                'lastUpdated' => (new \DateTime('-30 minutes'))->format('H:i'),
            ],
            'weight' => [
                'value' => 70.5,
                'unit' => 'kg',
                'status' => 'normal',
                'trend' => 'stable',
                'change' => '0',
                'normalRange' => null,
                'lastUpdated' => (new \DateTime('-1 day'))->format('d/m/Y H:i'),
            ],
        ];

        return $this->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Get Chart Data - Récupère les données pour les graphiques Chart.js (AJAX)
     */
    #[Route('/charts', name: 'health_get_chart_data', methods: ['GET'])]
    public function getChartData(Request $request): JsonResponse
    {
        $period = $request->query->get('period', '7d'); // 7d, 30d, 3m, 1y

        // Générer des données selon la période demandée
        $chartData = match ($period) {
            '30d' => [
                'energy' => [
                    'labels' => $this->generateDateLabels(30),
                    'datasets' => [
                        [
                            'label' => 'Niveau d\'énergie',
                            'data' => $this->generateRandomData(30, 4, 9),
                            'borderColor' => '#00A790',
                            'backgroundColor' => 'rgba(0, 167, 144, 0.1)',
                            'fill' => true,
                            'tension' => 0.4,
                        ],
                    ],
                ],
                'mood' => [
                    'labels' => ['Très mal', 'Mal', 'Moyen', 'Bien', 'Très bien'],
                    'datasets' => [
                        [
                            'data' => [2, 5, 8, 10, 5],
                            'backgroundColor' => [
                                '#ef4444',
                                '#f97316',
                                '#eab308',
                                '#22c55e',
                                '#00A790',
                            ],
                        ],
                    ],
                ],
            ],
            '3m' => [
                'energy' => [
                    'labels' => $this->generateWeekLabels(12),
                    'datasets' => [
                        [
                            'label' => 'Niveau d\'énergie moyen',
                            'data' => $this->generateRandomData(12, 5, 8),
                            'borderColor' => '#00A790',
                            'backgroundColor' => 'rgba(0, 167, 144, 0.1)',
                            'fill' => true,
                            'tension' => 0.4,
                        ],
                    ],
                ],
            ],
            default => [
                'energy' => [
                    'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                    'datasets' => [
                        [
                            'label' => 'Niveau d\'énergie',
                            'data' => [7, 6, 8, 7, 9, 8, 8],
                            'borderColor' => '#00A790',
                            'backgroundColor' => 'rgba(0, 167, 144, 0.1)',
                            'fill' => true,
                            'tension' => 0.4,
                        ],
                    ],
                ],
                'mood' => [
                    'labels' => ['Très mal', 'Mal', 'Moyen', 'Bien', 'Très bien'],
                    'datasets' => [
                        [
                            'data' => [0, 1, 2, 3, 4],
                            'backgroundColor' => [
                                '#ef4444',
                                '#f97316',
                                '#eab308',
                                '#22c55e',
                                '#00A790',
                            ],
                        ],
                    ],
                ],
                'sleep' => [
                    'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                    'datasets' => [
                        [
                            'label' => 'Heures de sommeil',
                            'data' => [7.5, 6.0, 8.0, 7.0, 7.5, 9.0, 8.5],
                            'borderColor' => '#6366f1',
                            'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                            'fill' => true,
                            'tension' => 0.4,
                        ],
                    ],
                ],
            ],
        };

        return $this->json([
            'success' => true,
            'period' => $period,
            'data' => $chartData,
        ]);
    }

    /**
     * Get Insights - Récupère les insights de l'IA (AJAX)
     */
    #[Route('/insights', name: 'health_get_insights', methods: ['GET'])]
    public function getInsights(): JsonResponse
    {
        // TODO: Générer des insights réels basés sur les données du patient
        $insights = [
            [
                'id' => 'insight_1',
                'type' => 'pattern',
                'category' => 'activité',
                'title' => 'Activité et sommeil',
                'description' => 'Vous dormez 23% mieux les soirées où vous faites une promenade.',
                'icon' => 'person-walking',
                'severity' => 'positive',
                'confidence' => 0.87,
                'actionable' => true,
                'action' => 'Essayez de marcher 30 minutes ce soir',
            ],
            [
                'id' => 'insight_2',
                'type' => 'correlation',
                'category' => 'hydratation',
                'title' => 'Hydratation',
                'description' => 'Votre énergie est plus élevée les jours où vous buvez plus de 2L d\'eau.',
                'icon' => 'droplet',
                'severity' => 'info',
                'confidence' => 0.72,
                'actionable' => true,
                'action' => 'Buvez un verre d\'eau maintenant',
            ],
            [
                'id' => 'insight_3',
                'type' => 'alert',
                'category' => 'cardiovasculaire',
                'title' => 'Tension artérielle',
                'description' => 'Votre tension a légèrement augmenté cette semaine. Surveillez votre consommation de sel.',
                'icon' => 'heart-pulse',
                'severity' => 'warning',
                'confidence' => 0.65,
                'actionable' => true,
                'action' => 'Consultez votre médecin si cela persiste',
            ],
            [
                'id' => 'insight_4',
                'type' => 'recommendation',
                'category' => 'sommeil',
                'title' => 'Qualité du sommeil',
                'description' => 'Votre sommeil est optimal quand vous vous couchez avant 23h.',
                'icon' => 'bed',
                'severity' => 'positive',
                'confidence' => 0.91,
                'actionable' => true,
                'action' => 'Essayez de dormir avant 23h ce soir',
            ],
        ];

        return $this->json([
            'success' => true,
            'generatedAt' => (new \DateTime())->format('Y-m-d H:i:s'),
            'data' => $insights,
        ]);
    }

    /**
     * Export Data - Exporte les données de santé
     */
    #[Route('/export', name: 'health_export', methods: ['GET'])]
    public function exportData(Request $request): Response
    {
        $format = $request->query->get('format', 'pdf'); // pdf, csv, json
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');

        // TODO: Récupérer les données réelles depuis la base
        $exportData = [
            'patient' => [
                'id' => 'P001',
                'exportDate' => (new \DateTime())->format('Y-m-d H:i:s'),
                'period' => [
                    'start' => $startDate ?? (new \DateTime('-30 days'))->format('Y-m-d'),
                    'end' => $endDate ?? (new \DateTime())->format('Y-m-d'),
                ],
            ],
            'entries' => [
                [
                    'date' => '2024-01-15',
                    'mood' => 4,
                    'energy' => 8,
                    'sleep' => 7.5,
                    'symptoms' => [],
                ],
                [
                    'date' => '2024-01-14',
                    'mood' => 3,
                    'energy' => 6,
                    'sleep' => 6.0,
                    'symptoms' => ['Fatigue'],
                ],
            ],
            'metrics' => [
                [
                    'date' => '2024-01-15 08:00',
                    'type' => 'heartRate',
                    'value' => 72,
                    'unit' => 'bpm',
                ],
                [
                    'date' => '2024-01-15 08:00',
                    'type' => 'bloodPressure',
                    'value' => '120/80',
                    'unit' => 'mmHg',
                ],
            ],
        ];

        return match ($format) {
            'json' => $this->json([
                'success' => true,
                'data' => $exportData,
            ]),
            'csv' => $this->exportCsv($exportData),
            default => $this->exportPdf($exportData),
        };
    }

    /**
     * Medical Records - Affiche l'historique médical du patient
     */
    #[Route('/records', name: 'health_records', methods: ['GET'])]
    public function records(): Response
    {
        $records = [
            [
                'id' => 1,
                'type' => 'consultation',
                'title' => 'Consultation générale',
                'doctor' => 'Dr. Sophie Martin',
                'date' => new \DateTime('-30 days'),
                'summary' => 'Examen de routine, tout va bien',
            ],
            [
                'id' => 2,
                'type' => 'lab_result',
                'title' => 'Analyse de sang complète',
                'date' => new \DateTime('-15 days'),
                'summary' => 'Tous les résultats dans les normes',
            ],
            [
                'id' => 3,
                'type' => 'consultation',
                'title' => 'Consultation cardiologie',
                'doctor' => 'Dr. Ahmed Ben Ali',
                'date' => new \DateTime('-7 days'),
                'summary' => 'Électrocardiogramme normal',
            ],
        ];

        return $this->render('health/records.html.twig', [
            'records' => $records,
        ]);
    }

    /**
     * Prescriptions - Affiche les ordonnances du patient
     */
    #[Route('/prescriptions', name: 'health_prescriptions', methods: ['GET'])]
    public function prescriptions(): Response
    {
        $prescriptions = [
            [
                'id' => 1,
                'medication' => 'Doliprane 500mg',
                'dosage' => '1 comprimé 3 fois par jour',
                'duration' => '7 jours',
                'doctor' => 'Dr. Sophie Martin',
                'date' => new \DateTime('-30 days'),
                'status' => 'active',
            ],
            [
                'id' => 2,
                'medication' => 'Vitamine D 1000UI',
                'dosage' => '1 gélule par jour',
                'duration' => '90 jours',
                'doctor' => 'Dr. Sophie Martin',
                'date' => new \DateTime('-60 days'),
                'status' => 'active',
            ],
            [
                'id' => 3,
                'medication' => 'Ibuprofen 400mg',
                'dosage' => '1 comprimé si nécessaire',
                'duration' => '14 jours',
                'doctor' => 'Dr. Ahmed Ben Ali',
                'date' => new \DateTime('-90 days'),
                'status' => 'completed',
            ],
        ];

        return $this->render('health/prescriptions.html.twig', [
            'prescriptions' => $prescriptions,
        ]);
    }

    /**
     * Lab Results - Affiche les résultats de laboratoire
     */
    #[Route('/lab-results', name: 'health_lab_results', methods: ['GET'])]
    public function labResults(): Response
    {
        $labResults = [
            [
                'id' => 1,
                'name' => 'Analyse de sang complète',
                'date' => new \DateTime('-15 days'),
                'status' => 'completed',
                'results' => [
                    ['test' => 'Hémoglobine', 'value' => '14.5', 'unit' => 'g/dL', 'normal' => '12-16'],
                    ['test' => 'Globules blancs', 'value' => '7500', 'unit' => '/mm³', 'normal' => '4000-10000'],
                    ['test' => 'Plaquettes', 'value' => '250000', 'unit' => '/mm³', 'normal' => '150000-400000'],
                ],
            ],
            [
                'id' => 2,
                'name' => 'Profil lipidique',
                'date' => new \DateTime('-60 days'),
                'status' => 'completed',
                'results' => [
                    ['test' => 'Cholestérol total', 'value' => '5.2', 'unit' => 'mmol/L', 'normal' => '<5.2'],
                    ['test' => 'LDL', 'value' => '3.1', 'unit' => 'mmol/L', 'normal' => '<3.4'],
                    ['test' => 'HDL', 'value' => '1.5', 'unit' => 'mmol/L', 'normal' => '>1.0'],
                ],
            ],
        ];

        return $this->render('health/lab-results.html.twig', [
            'labResults' => $labResults,
        ]);
    }

    /**
     * Body Map - Affiche la carte corporelle pour le suivi des symptômes
     */
    #[Route('/body-map', name: 'health_body_map', methods: ['GET'])]
    public function bodyMap(): Response
    {
        // Récupérer les symptômes enregistrés (simulés)
        $recordedSymptoms = [
            [
                'id' => 1,
                'bodyPart' => 'head',
                'symptom' => 'Maux de tête légers',
                'intensity' => 3,
                'date' => new \DateTime('-2 days'),
                'status' => 'resolved',
            ],
            [
                'id' => 2,
                'bodyPart' => 'chest',
                'symptom' => 'Légère oppression',
                'intensity' => 2,
                'date' => new \DateTime('-1 day'),
                'status' => 'active',
            ],
        ];

        // Liste des parties du corps disponibles
        $bodyParts = [
            ['id' => 'head', 'label' => 'Tête', 'icon' => 'brain'],
            ['id' => 'neck', 'label' => 'Cou', 'icon' => 'neck'],
            ['id' => 'chest', 'label' => 'Poitrine', 'icon' => 'lungs'],
            ['id' => 'abdomen', 'label' => 'Abdomen', 'icon' => 'stomach'],
            ['id' => 'back', 'label' => 'Dos', 'icon' => 'back'],
            ['id' => 'leftArm', 'label' => 'Bras gauche', 'icon' => 'arm'],
            ['id' => 'rightArm', 'label' => 'Bras droit', 'icon' => 'arm'],
            ['id' => 'leftLeg', 'label' => 'Jambe gauche', 'icon' => 'leg'],
            ['id' => 'rightLeg', 'label' => 'Jambe droite', 'icon' => 'leg'],
        ];

        // Types de symptômes courants
        $symptomTypes = [
            'Douleur',
            'Engourdissement',
            'Picotements',
            'Brûlure',
            'Crampes',
            'Raideur',
            'Gonflement',
            'Rougeur',
            'Démangeaisons',
            'Autre',
        ];

        return $this->render('health/body_map.html.twig', [
            'recordedSymptoms' => $recordedSymptoms,
            'bodyParts' => $bodyParts,
            'symptomTypes' => $symptomTypes,
        ]);
    }

    /**
     * Accessible Body Map - Affiche la carte corporelle accessible pour le suivi des symptômes
     */
    #[Route('/accessible/body-map', name: 'health_body_map_accessible', methods: ['GET'])]
    public function bodyMapAccessible(): Response
    {
        return $this->render('health/accessible/body-map.html.twig');
    }

    /**
     * Record Symptom - Enregistre un symptôme depuis la carte corporelle (AJAX)
     */
    #[Route('/symptom', name: 'health_record_symptom', methods: ['POST'])]
    public function recordSymptom(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation
        if (!$data) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides',
            ], 400);
        }

        $requiredFields = ['bodyPart', 'symptom', 'intensity'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return $this->json([
                    'success' => false,
                    'message' => sprintf('Le champ "%s" est requis', $field),
                ], 400);
            }
        }

        // Validation de l'intensité
        $intensity = (int) $data['intensity'];
        if ($intensity < 1 || $intensity > 10) {
            return $this->json([
                'success' => false,
                'message' => 'L\'intensité doit être comprise entre 1 et 10',
            ], 400);
        }

        // TODO: Sauvegarder le symptôme en base de données
        // $symptom = new Symptom();
        // $symptom->setBodyPart($data['bodyPart']);
        // $symptom->setDescription($data['symptom']);
        // $symptom->setIntensity($intensity);
        // $symptom->setNotes($data['notes'] ?? null);
        // $symptom->setDate(new \DateTime());
        // $entityManager->persist($symptom);
        // $entityManager->flush();

        // Ajouter un message flash
        $this->addFlash('success', 'Symptôme enregistré avec succès');

        return $this->json([
            'success' => true,
            'message' => 'Symptôme enregistré avec succès',
            'data' => [
                'id' => uniqid(),
                'bodyPart' => $data['bodyPart'],
                'symptom' => $data['symptom'],
                'intensity' => $intensity,
                'date' => (new \DateTime())->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Helper: Export CSV
     */
    private function exportCsv(array $data): Response
    {
        $csvData = [];
        $csvData[] = ['Date', 'Humeur', 'Énergie', 'Sommeil', 'Symptômes'];

        foreach ($data['entries'] as $entry) {
            $csvData[] = [
                $entry['date'],
                $entry['mood'],
                $entry['energy'],
                $entry['sleep'],
                implode(', ', $entry['symptoms']),
            ];
        }

        $output = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export-sante-' . date('Y-m-d') . '.csv"');

        return $response;
    }

    /**
     * Helper: Export PDF
     */
    private function exportPdf(array $data): Response
    {
        // TODO: Implémenter la génération PDF avec une bibliothèque comme TCPDF ou Dompdf
        // Pour l'instant, on retourne une réponse JSON
        return $this->json([
            'success' => true,
            'message' => 'Export PDF en cours de développement',
            'data' => $data,
        ]);
    }

    /**
     * Helper: Génère des labels de dates
     */
    private function generateDateLabels(int $days): array
    {
        $labels = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $labels[] = (new \DateTime("-$i days"))->format('d/m');
        }
        return $labels;
    }

    /**
     * Helper: Génère des labels de semaines
     */
    private function generateWeekLabels(int $weeks): array
    {
        $labels = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $labels[] = 'S-' . (new \DateTime("-$i weeks"))->format('W');
        }
        return $labels;
    }

    /**
     * Helper: Génère des données aléatoires
     */
    private function generateRandomData(int $count, int $min, int $max): array
    {
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[] = rand($min * 10, $max * 10) / 10;
        }
        return $data;
    }

    /**
     * Analytics Dashboard - Patient View (Alias)
     * Alias route for patient-view template
     */
    #[Route('/analytics/patient-view', name: 'health_analytics_patient_view', methods: ['GET'])]
    public function analyticsPatientView(): Response
    {
        return $this->redirectToRoute('health_analytics_patient', [], 301);
    }

    /**
     * Analytics Dashboard - Patient View
     * Affiche le tableau de bord d'analytics pour les patients
     */
    #[Route('/analytics', name: 'health_analytics_patient', methods: ['GET'])]
    public function analyticsPatient(): Response
    {
        return $this->render('health/analytics/patient-view.html.twig', [
            'page_title' => 'Mon Analyse Santé',
        ]);
    }

    /**
     * Analytics Dashboard - Doctor View (Alias)
     * Alias route for doctor-view template
     */
    #[Route('/analytics/doctor-view', name: 'health_analytics_doctor_view', methods: ['GET'])]
    public function analyticsDoctorView(): Response
    {
        return $this->redirectToRoute('health_analytics_doctor', [], 301);
    }

    /**
     * Analytics Dashboard - Doctor View
     * Affiche le tableau de bord d'analytics pour les médecins
     */
    #[Route('/analytics/doctor', name: 'health_analytics_doctor', methods: ['GET'])]
    public function analyticsDoctor(): Response
    {
        // Données simulées pour les patients
        $patients = [
            [
                'id' => 'P001',
                'name' => 'Marie Dupont',
                'avatar' => 'https://ui-avatars.com/api/?name=Marie+Dupont&background=00A790&color=fff',
                'healthScore' => 85,
                'trend' => 'improving',
                'alerts' => [],
                'lastEntry' => 'Il y a 2h',
            ],
            [
                'id' => 'P002',
                'name' => 'Jean Martin',
                'avatar' => 'https://ui-avatars.com/api/?name=Jean+Martin&background=ef4444&color=fff',
                'healthScore' => 62,
                'trend' => 'declining',
                'alerts' => [
                    ['id' => 1, 'severity' => 'critical', 'message' => 'Tension élevée', 'icon' => 'fa-heart-pulse'],
                ],
                'lastEntry' => 'Il y a 5h',
            ],
        ];

        return $this->render('health/analytics/doctor-view.html.twig', [
            'page_title' => 'Tableau de Bord Médecin',
            'patients' => $patients,
        ]);
    }

    /**
     * Report Generator (Alias)
     * Alias route for report-generator template
     */
    #[Route('/analytics/report-generator', name: 'health_analytics_report_generator', methods: ['GET'])]
    public function analyticsReportGenerator(): Response
    {
        return $this->redirectToRoute('health_analytics_reports', [], 301);
    }

    /**
     * Report Generator
     * Génère des rapports médicaux personnalisés
     */
    #[Route('/analytics/reports', name: 'health_analytics_reports', methods: ['GET'])]
    public function analyticsReports(): Response
    {
        // Liste des patients pour le sélecteur
        $patients = [
            ['id' => 'P001', 'name' => 'Marie Dupont', 'age' => 45, 'gender' => 'F', 'fileNumber' => '2024-001'],
            ['id' => 'P002', 'name' => 'Jean Martin', 'age' => 52, 'gender' => 'M', 'fileNumber' => '2024-002'],
            ['id' => 'P003', 'name' => 'Sophie Bernard', 'age' => 38, 'gender' => 'F', 'fileNumber' => '2024-003'],
        ];

        return $this->render('health/analytics/report-generator.html.twig', [
            'page_title' => 'Générateur de Rapports',
            'patients' => $patients,
        ]);
    }

    /**
     * Get Analytics Data - Récupère les données pour les graphiques d'analytics (AJAX)
     */
    #[Route('/analytics/data', name: 'health_analytics_data', methods: ['GET'])]
    public function getAnalyticsData(Request $request): JsonResponse
    {
        $type = $request->query->get('type', 'trends');
        $period = $request->query->get('period', '7d');
        $patientId = $request->query->get('patient_id');

        $data = match ($type) {
            'trends' => $this->getTrendsData($period),
            'symptoms' => $this->getSymptomData($period),
            'medications' => $this->getMedicationData($period),
            'correlations' => $this->getCorrelationData($period),
            'triggers' => $this->getTriggerData($period),
            default => [],
        };

        return $this->json([
            'success' => true,
            'type' => $type,
            'period' => $period,
            'data' => $data,
        ]);
    }

    /**
     * Generate Report - Génère un rapport médical (AJAX)
     */
    #[Route('/analytics/generate-report', name: 'health_generate_report', methods: ['POST'])]
    public function generateReport(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['patient_id'])) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides',
            ], 400);
        }

        // Simulation de génération de rapport
        $reportId = uniqid('RPT-');

        return $this->json([
            'success' => true,
            'message' => 'Rapport généré avec succès',
            'report_id' => $reportId,
            'download_url' => '/health/analytics/download/' . $reportId,
        ]);
    }

    /**
     * Helper: Get trends data
     */
    private function getTrendsData(string $period): array
    {
        $days = match ($period) {
            '30d' => 30,
            '3m' => 90,
            '1y' => 365,
            default => 7,
        };

        return [
            'labels' => $this->generateDateLabels($days),
            'energy' => $this->generateRandomData($days, 4, 9),
            'mood' => $this->generateRandomData($days, 2, 5),
            'sleep' => $this->generateRandomData($days, 5, 9),
        ];
    }

    /**
     * Helper: Get symptom data
     */
    private function getSymptomData(string $period): array
    {
        return [
            'labels' => ['Fatigue', 'Maux de tête', 'Tension', 'Douleurs', 'Insomnie'],
            'frequency' => [12, 8, 5, 3, 6],
            'intensity' => [6.5, 4.2, 5.8, 3.1, 5.5],
        ];
    }

    /**
     * Helper: Get medication data
     */
    private function getMedicationData(string $period): array
    {
        return [
            'adherence' => 87,
            'taken' => 87,
            'missed' => 10,
            'skipped' => 3,
            'medications' => [
                ['name' => 'Vitamine D', 'effectiveness' => 85, 'adherence' => 92],
                ['name' => 'Magnésium', 'effectiveness' => 72, 'adherence' => 88],
                ['name' => 'Oméga-3', 'effectiveness' => 68, 'adherence' => 81],
            ],
        ];
    }

    /**
     * Helper: Get correlation data
     */
    private function getCorrelationData(string $period): array
    {
        return [
            'exercise_energy' => [
                'correlation' => 0.73,
                'data' => array_map(fn($i) => [
                    'x' => rand(0, 60),
                    'y' => rand(40, 95) / 10,
                ], range(1, 20)),
            ],
            'sleep_mood' => [
                'correlation' => 0.68,
                'data' => array_map(fn($i) => [
                    'x' => rand(40, 100) / 10,
                    'y' => rand(20, 50) / 10,
                ], range(1, 20)),
            ],
        ];
    }

    /**
     * Helper: Get trigger data
     */
    private function getTriggerData(string $period): array
    {
        return [
            ['name' => 'Stress', 'impact' => 23, 'type' => 'negative'],
            ['name' => 'Sommeil < 6h', 'impact' => 18, 'type' => 'negative'],
            ['name' => 'Exercice', 'impact' => -15, 'type' => 'positive'],
            ['name' => 'Caféine', 'impact' => 12, 'type' => 'negative'],
            ['name' => 'Hydratation', 'impact' => -8, 'type' => 'positive'],
        ];
    }

    /**
     * Billing - Affiche les factures et paiements du patient
     */
    #[Route('/billing', name: 'health_billing', methods: ['GET'])]
    public function billing(): Response
    {
        $invoices = [
            [
                'id' => 'INV-2024-001',
                'date' => new \DateTime('-30 days'),
                'description' => 'Consultation Dr. Sophie Martin',
                'amount' => 50,
                'status' => 'paid',
            ],
            [
                'id' => 'INV-2024-002',
                'date' => new \DateTime('-15 days'),
                'description' => 'Analyse de sang complète',
                'amount' => 70,
                'status' => 'paid',
            ],
            [
                'id' => 'INV-2024-003',
                'date' => new \DateTime('-3 days'),
                'description' => 'Consultation Dr. Ahmed Ben Ali',
                'amount' => 120,
                'status' => 'pending',
            ],
        ];

        $totalPaid = array_sum(array_map(fn($inv) => $inv['status'] === 'paid' ? $inv['amount'] : 0, $invoices));
        $totalPending = array_sum(array_map(fn($inv) => $inv['status'] === 'pending' ? $inv['amount'] : 0, $invoices));

        return $this->render('health/billing.html.twig', [
            'invoices' => $invoices,
            'totalPaid' => $totalPaid,
            'totalPending' => $totalPending,
            'totalAmount' => $totalPaid + $totalPending,
        ]);
    }

    /**
     * Doctor Interface - Patient List
     * Affiche la liste des patients pour les médecins
     */
    #[Route('/doctor/patients', name: 'doctor_patient_list', methods: ['GET'])]
    public function doctorPatientList(): Response
    {
        return $this->render('doctor/patient-list.html.twig', [
            'page_title' => 'Liste des Patients',
        ]);
    }

    /**
     * Doctor Interface - Patient Chart
     * Affiche le dossier médical complet d'un patient
     */
    #[Route('/doctor/patient/{id}/chart', name: 'doctor_patient_chart', methods: ['GET'])]
    public function doctorPatientChart(string $id): Response
    {
        return $this->render('doctor/patient-chart.html.twig', [
            'page_title' => 'Dossier Médical',
            'patient_id' => $id,
        ]);
    }

    /**
     * Doctor Interface - Clinical Notes
     * Interface pour les notes cliniques SOAP
     */
    #[Route('/doctor/patient/{id}/notes', name: 'doctor_clinical_notes', methods: ['GET'])]
    public function doctorClinicalNotes(string $id): Response
    {
        return $this->render('doctor/clinical-notes.html.twig', [
            'page_title' => 'Notes Cliniques',
            'patient_id' => $id,
        ]);
    }

    /**
     * Doctor Interface - Communication
     * Interface de messagerie avec les patients
     */
    #[Route('/doctor/patient/{id}/communication', name: 'doctor_communication', methods: ['GET'])]
    public function doctorCommunication(string $id): Response
    {
        return $this->render('doctor/communication.html.twig', [
            'page_title' => 'Communication',
            'patient_id' => $id,
        ]);
    }
}
