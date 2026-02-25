<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\Examens;
use App\Entity\Medecin;
use App\Entity\Ordonnance;
use App\Entity\Patient;
use App\Entity\User;
use App\Repository\ConsultationRepository;
use App\Service\AiModelDoctorService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/health')]
class HealthController extends AbstractController
{
    public function __construct(
        private AiModelDoctorService $aiModelDoctorService,
        private ConsultationRepository $consultationRepository
    ) {
    }

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
    public function prescriptions(EntityManagerInterface $em): Response
    {
        $rows = $em->getRepository(Ordonnance::class)->findBy([], ['date_ordonnance' => 'DESC']);
        $prescriptions = [];
        foreach ($rows as $ordonnance) {
            $consultation = $ordonnance->getIdConsultation();
            $consultationStatus = $consultation ? strtolower((string) $consultation->getStatus()) : '';
            $status = in_array($consultationStatus, ['termine', 'completed', 'done'], true) ? 'completed' : 'active';

            $prescriptions[] = [
                'id' => $ordonnance->getId(),
                'medication' => $ordonnance->getMedicament(),
                'dosage' => $ordonnance->getDosage(),
                'duration' => $ordonnance->getDureeTraitement(),
                'doctor' => '-',
                'date' => $ordonnance->getDateOrdonnance(),
                'status' => $status,
            ];
        }

        return $this->render('health/prescriptions.html.twig', [
            'prescriptions' => $prescriptions,
        ]);
    }

    /**
     * Lab Results - Affiche les résultats de laboratoire
     */
    #[Route('/lab-results', name: 'health_lab_results', methods: ['GET'])]
    public function labResults(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            $this->addFlash('error', 'AccÃ¨s patient requis.');
            return $this->render('health/lab-results.html.twig', [
                'labResults' => [],
            ]);
        }

        $exams = $em->getRepository(Examens::class)->findByPatientUuid($user->getUuid());

        $labResults = [];
        foreach ($exams as $exam) {
            $labResults[] = [
                'id' => $exam->getId(),
                'name' => $exam->getNomExamen() ?: ($exam->getTypeExamen() ?: 'Examen'),
                'date' => $exam->getDateExamen(),
                'status' => $exam->getStatus() ?: 'prescrit',
                'result' => $exam->getResultat() ?: '',
                'resultFile' => $exam->getResultFile(),
                'doctorAnalysis' => $exam->getDoctorAnalysis(),
                'doctorTreatment' => $exam->getDoctorTreatment(),
            ];
        }

        return $this->render('health/lab-results.html.twig', [
            'labResults' => $labResults,
        ]);
    }

    #[Route('/lab-results/{id}/upload', name: 'health_lab_results_upload', methods: ['POST'])]
    public function uploadLabResult(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            $this->addFlash('error', 'AccÃ¨s patient requis.');
            return $this->redirectToRoute('health_lab_results');
        }

        $exam = $em->getRepository(Examens::class)->findOneForPatient($id, $user->getUuid());
        if (!$exam) {
            $this->addFlash('error', 'Examen introuvable');
            return $this->redirectToRoute('health_lab_results');
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('result_pdf');
        if (!$file) {
            $this->addFlash('error', 'Veuillez choisir un fichier PDF.');
            return $this->redirectToRoute('health_lab_results');
        }

        if ($file->getClientOriginalExtension() != 'pdf') {
            $this->addFlash('error', 'Seuls les fichiers PDF sont autoris?s.');
            return $this->redirectToRoute('health_lab_results');
        }

        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/lab-results';
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0775, true);
        }

        // Remove old file if exists
        $existingFile = $exam->getResultFile();
        if ($existingFile) {
            $existingPath = $this->getParameter('kernel.project_dir') . '/public/' . ltrim($existingFile, '/');
            if (is_file($existingPath)) {
                @unlink($existingPath);
            }
        }

        $filename = 'exam_' . $exam->getId() . '_' . uniqid() . '.pdf';
        $file->move($uploadsDir, $filename);

        $exam->setResultFile('uploads/lab-results/' . $filename);
        if (!$exam->getResultat()) {
            $exam->setResultat('Résultat disponible (PDF).');
        }
        $exam->setStatus('termine');
        $exam->setDateRealisation(new \DateTime());
        $em->flush();

        $this->addFlash('success', 'Résultat PDF enregistré.');
        return $this->redirectToRoute('health_lab_results');
    }

    #[Route('/lab-results/{id}/delete-file', name: 'health_lab_results_delete_file', methods: ['POST'])]
    public function deleteLabResultFile(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            $this->addFlash('error', 'AccÃ¨s patient requis.');
            return $this->redirectToRoute('health_lab_results');
        }

        $exam = $em->getRepository(Examens::class)->findOneForPatient($id, $user->getUuid());
        if (!$exam) {
            $this->addFlash('error', 'Examen introuvable');
            return $this->redirectToRoute('health_lab_results');
        }

        $existingFile = $exam->getResultFile();
        if ($existingFile) {
            $existingPath = $this->getParameter('kernel.project_dir') . '/public/' . ltrim($existingFile, '/');
            if (is_file($existingPath)) {
                @unlink($existingPath);
            }
        }

        $exam->setResultFile(null);
        $exam->setStatus('prescrit');
        $em->flush();

        $this->addFlash('success', 'Fichier supprimé.');
        return $this->redirectToRoute('health_lab_results');
    }

    #[Route('/doctor/examens/{id}/analysis', name: 'doctor_examens_analysis', methods: ['POST'])]
    public function updateExamAnalysis(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User || $user instanceof Patient) {
            $this->addFlash('error', 'AccÃ¨s mÃ©decin requis.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('doctor_patient_list'));
        }

        $exam = $em->getRepository(Examens::class)->findOneForDoctor($id, $user->getUuid());
        if (!$exam) {
            $this->addFlash('error', 'Examen introuvable ou non autorisÃ©.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('doctor_patient_list'));
        }

        $analysis = trim((string) $request->request->get('doctor_analysis', ''));
        $treatment = trim((string) $request->request->get('doctor_treatment', ''));

        $exam->setDoctorAnalysis($analysis !== '' ? $analysis : null);
        $exam->setDoctorTreatment($treatment !== '' ? $treatment : null);
        $em->flush();

        $this->addFlash('success', 'Analyse et traitement enregistrÃ©s.');
        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('doctor_patient_list'));
    }

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
     * Affiche le tableau de bord d'analytics pour les médecins avec IA
     */
    #[Route('/analytics/doctor', name: 'health_analytics_doctor', methods: ['GET'])]
    public function analyticsDoctor(): Response
    {
        $doctorId = null;
        $user = $this->getUser();
        if ($user instanceof Medecin) {
            $doctorId = $user->getId();
        } elseif ($user instanceof User && !($user instanceof Patient)) {
            $doctorId = $user->getUuid();
        }

        // Récupérer les données AI pour le tableau de bord médecin
        $aiDashboardData = $this->aiModelDoctorService->getDashboardData($doctorId);
        
        // Si l'API n'est pas disponible OU s'il n'y a pas de doctorId, utiliser des données simulées
        // MAIS garder les données de traitement réelles si disponibles
        if (!$aiDashboardData['api_available'] || $doctorId === null) {
            $simulatedData = $this->aiModelDoctorService->getSimulatedDashboardData();
            // Sauvegarder les données de traitement réelles
            $realTreatmentData = $aiDashboardData['treatment_effectiveness'];
            // Fusionner les données
            $aiDashboardData = array_merge($simulatedData, array_filter($aiDashboardData, fn($v) => $v !== null));
            // Restaurer les données de traitement réelles si elles existent
            if ($realTreatmentData !== null) {
                $aiDashboardData['treatment_effectiveness'] = $realTreatmentData;
            }
        }

        $patients = [];
        $recentAlerts = [];
        $criticalAlerts = 0;
        $todayAppointments = 0;
        $nextAppointment = null;
        $reportsGenerated = 0;

        if ($user instanceof Medecin) {
            $consultations = $this->consultationRepository->findByMedecinOrderedByDateTime($user->getId());
            $byPatient = [];
            foreach ($consultations as $consultation) {
                $patient = $consultation->getPatient();
                if (!$patient) {
                    continue;
                }
                $pid = $patient->getId();
                if (!isset($byPatient[$pid])) {
                    $byPatient[$pid] = [
                        'patient' => $patient,
                        'consultations' => [],
                    ];
                }
                $byPatient[$pid]['consultations'][] = $consultation;
            }

            $now = new \DateTimeImmutable();
            $today = $now->format('Y-m-d');

            foreach ($consultations as $consultation) {
                $date = $consultation->getDateConsultation();
                if ($date && $date->format('Y-m-d') === $today) {
                    $todayAppointments++;
                }

                $time = $consultation->getTimeConsultation();
                if ($date && $time) {
                    $dateTime = \DateTimeImmutable::createFromFormat(
                        'Y-m-d H:i:s',
                        $date->format('Y-m-d') . ' ' . $time->format('H:i:s')
                    );
                    if ($dateTime && $dateTime > $now) {
                        if ($nextAppointment === null || $dateTime < $nextAppointment['time']) {
                            $nextAppointment = [
                                'time' => $dateTime,
                                'label' => $dateTime->format('H:i') . ' - ' . $consultation->getPatient()?->getFirstName(),
                            ];
                        }
                    }
                }
            }

            foreach ($byPatient as $entry) {
                $patient = $entry['patient'];
                $consults = $entry['consultations'];
                $latest = $consults[0] ?? null;
                $previous = $consults[1] ?? null;

                $latestVitals = is_array($latest?->getVitals()) ? $latest->getVitals() : [];
                $latestScore = $this->computeHealthScore($latestVitals);
                $previousScore = $previous ? $this->computeHealthScore(is_array($previous->getVitals()) ? $previous->getVitals() : []) : $latestScore;

                $trend = 'stable';
                $trendLabel = 'Stable';
                if ($latestScore > $previousScore + 2) {
                    $trend = 'improving';
                    $trendLabel = 'En amélioration';
                } elseif ($latestScore < $previousScore - 2) {
                    $trend = 'declining';
                    $trendLabel = 'En déclin';
                }

                $alerts = $latest ? $this->buildAlerts($latest) : [];
                foreach ($alerts as $alert) {
                    if ($alert['severity'] === 'critical') {
                        $criticalAlerts++;
                    }
                    $recentAlerts[] = [
                        'id' => $alert['id'],
                        'severity' => $alert['severity'],
                        'patientName' => $patient->getFirstName() . ' ' . $patient->getLastName(),
                        'message' => $alert['message'],
                        'time' => $this->formatRelativeTime($latest?->getDateConsultation(), $latest?->getTimeConsultation()),
                    ];
                }

                $avatar = $patient->getAvatarUrl();
                if (!$avatar) {
                    $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($patient->getFirstName() . ' ' . $patient->getLastName()) . '&background=00A790&color=fff';
                }

                $patients[] = [
                    'id' => $patient->getId(),
                    'name' => $patient->getFirstName() . ' ' . $patient->getLastName(),
                    'avatar' => $avatar,
                    'healthScore' => $latestScore,
                    'trend' => $trend,
                    'trendLabel' => $trendLabel,
                    'alerts' => $alerts,
                    'lastEntry' => $this->formatRelativeTime($latest?->getDateConsultation(), $latest?->getTimeConsultation()),
                ];
            }
        }

        return $this->render('health/analytics/doctor-view.html.twig', [
            'page_title' => 'Tableau de Bord Médecin',
            'patients' => $patients,
            'ai_data' => $aiDashboardData,
            'api_available' => $aiDashboardData['api_available'],
            'doctor_id' => $doctorId,
            'stats' => [
                'criticalAlerts' => $criticalAlerts,
                'todayAppointments' => $todayAppointments,
                'nextAppointment' => $nextAppointment['label'] ?? 'Aucun',
                'reportsGenerated' => $reportsGenerated,
            ],
            'recent_alerts' => array_slice($recentAlerts, 0, 5),
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
     * Get AI Predictions - Récupère les prédictions IA pour les médecins (AJAX)
     */
    #[Route('/analytics/ai/predictions', name: 'health_analytics_ai_predictions', methods: ['GET'])]
    public function getAiPredictions(Request $request): JsonResponse
    {
        $doctorId = $request->query->get('doctor_id');
        if (!is_string($doctorId) || $doctorId === '') {
            $user = $this->getUser();
            if ($user instanceof Medecin) {
                $doctorId = $user->getId();
            } elseif ($user instanceof User && !($user instanceof Patient)) {
                $doctorId = $user->getUuid();
            } else {
                $doctorId = null;
            }
        }
        
        // Récupérer les prédictions
        if (is_string($doctorId) && $doctorId !== '') {
            $predictions = $this->aiModelDoctorService->predictDoctorActivity($doctorId);
            $recommendations = $this->aiModelDoctorService->getDoctorRecommendations($doctorId);
        } else {
            $predictions = $this->aiModelDoctorService->predictAllDoctors();
            $recommendations = null;
        }

        return $this->json([
            'success' => true,
            'api_available' => $this->aiModelDoctorService->isAvailable(),
            'predictions' => $predictions,
            'recommendations' => $recommendations,
            'profit_predictions' => $doctorId ? $this->aiModelDoctorService->getDoctorProfitPredictions($doctorId) : null,
            'revenue_weekly' => $doctorId ? $this->aiModelDoctorService->getDoctorRevenueWeekly($doctorId) : null,
            'revenue_monthly' => $doctorId ? $this->aiModelDoctorService->getDoctorRevenueMonthly($doctorId) : null,
            'profit_alerts' => $doctorId ? $this->aiModelDoctorService->getDoctorProfitAlerts($doctorId) : null,
        ]);
    }


    /**
     * Get AI Status - Vérifie le statut de l'API IA (AJAX)
     */
    #[Route('/analytics/ai/status', name: 'health_analytics_ai_status', methods: ['GET'])]
    public function getAiStatus(): JsonResponse
    {
        return $this->json([
            'success' => true,
            'available' => $this->aiModelDoctorService->isAvailable(),
            'timestamp' => date('c'),
        ]);
    }

    private function computeHealthScore(array $vitals): int
    {
        $score = 100;

        $temp = $vitals['temperature'] ?? null;
        if (is_numeric($temp)) {
            if ($temp >= 39) {
                $score -= 25;
            } elseif ($temp >= 38) {
                $score -= 15;
            }
        }

        $spo2 = $vitals['spo2'] ?? $vitals['oxygenSaturation'] ?? null;
        if (is_numeric($spo2)) {
            if ($spo2 < 88) {
                $score -= 35;
            } elseif ($spo2 < 92) {
                $score -= 25;
            }
        }

        $bp = is_array($vitals['bloodPressure'] ?? null) ? $vitals['bloodPressure'] : [];
        $systolic = $bp['systolic'] ?? $vitals['bloodPressureSystolic'] ?? null;
        $diastolic = $bp['diastolic'] ?? $vitals['bloodPressureDiastolic'] ?? null;
        if (is_numeric($systolic) && is_numeric($diastolic)) {
            if ($systolic >= 160 || $diastolic >= 100) {
                $score -= 20;
            } elseif ($systolic >= 140 || $diastolic >= 90) {
                $score -= 10;
            }
        }

        $pulse = $vitals['pulse'] ?? $vitals['heartRate'] ?? null;
        if (is_numeric($pulse)) {
            if ($pulse > 110 || $pulse < 50) {
                $score -= 10;
            }
        }

        $score = max(0, min(100, $score));
        if ($score === 100 && empty($vitals)) {
            $score = 75;
        }

        return $score;
    }

    private function buildAlerts(\App\Entity\Consultation $consultation): array
    {
        $alerts = [];
        $id = 1;

        if ($consultation->getStatus() === 'emergency' || $consultation->getConsultationType() === 'emergency') {
            $alerts[] = [
                'id' => $id++,
                'severity' => 'critical',
                'message' => 'Consultation d\'urgence',
                'icon' => 'fa-triangle-exclamation',
            ];
        } elseif ($consultation->getStatus() === 'pending') {
            $alerts[] = [
                'id' => $id++,
                'severity' => 'warning',
                'message' => 'Consultation en attente',
                'icon' => 'fa-clock',
            ];
        }

        $vitals = is_array($consultation->getVitals()) ? $consultation->getVitals() : [];

        $temp = $vitals['temperature'] ?? null;
        if (is_numeric($temp) && $temp >= 38) {
            $alerts[] = [
                'id' => $id++,
                'severity' => $temp >= 39 ? 'critical' : 'warning',
                'message' => 'Fièvre détectée',
                'icon' => 'fa-temperature-high',
            ];
        }

        $spo2 = $vitals['spo2'] ?? $vitals['oxygenSaturation'] ?? null;
        if (is_numeric($spo2) && $spo2 < 92) {
            $alerts[] = [
                'id' => $id++,
                'severity' => 'critical',
                'message' => 'SpO2 basse',
                'icon' => 'fa-lungs',
            ];
        }

        $bp = is_array($vitals['bloodPressure'] ?? null) ? $vitals['bloodPressure'] : [];
        $systolic = $bp['systolic'] ?? $vitals['bloodPressureSystolic'] ?? null;
        $diastolic = $bp['diastolic'] ?? $vitals['bloodPressureDiastolic'] ?? null;
        if (is_numeric($systolic) && is_numeric($diastolic) && ($systolic >= 140 || $diastolic >= 90)) {
            $alerts[] = [
                'id' => $id++,
                'severity' => ($systolic >= 160 || $diastolic >= 100) ? 'critical' : 'warning',
                'message' => 'Tension élevée',
                'icon' => 'fa-heart-pulse',
            ];
        }

        return array_slice($alerts, 0, 3);
    }

    private function formatRelativeTime(?\DateTimeInterface $date, ?\DateTimeInterface $time): string
    {
        if (!$date) {
            return '-';
        }

        $dateTime = $date;
        if ($time) {
            $dateTime = \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s',
                $date->format('Y-m-d') . ' ' . $time->format('H:i:s')
            ) ?: $date;
        }

        $now = new \DateTimeImmutable();
        $diff = $now->getTimestamp() - $dateTime->getTimestamp();

        if ($diff < 3600) {
            $mins = max(1, (int) floor($diff / 60));
            return 'Il y a ' . $mins . ' min';
        }

        if ($diff < 86400) {
            $hours = (int) floor($diff / 3600);
            return 'Il y a ' . $hours . 'h';
        }

        $days = (int) floor($diff / 86400);
        return 'Il y a ' . $days . ' j';
    }

    private function parsePeriodStart(string $period): ?\DateTimeInterface
    {
        $now = new \DateTimeImmutable();
        return match ($period) {
            '7d' => $now->modify('-7 days'),
            '30d' => $now->modify('-30 days'),
            '3m' => $now->modify('-3 months'),
            default => null,
        };
    }

    private function buildPatientReport(array $consultations, string $type, string $period): array
    {
        $latest = $consultations[0] ?? null;
        $patient = $latest?->getPatient();

        $total = count($consultations);
        $avgDuration = $total > 0 ? array_sum(array_map(fn($c) => (int) $c->getDuration(), $consultations)) / $total : 0;
        $statusCounts = [];
        $emergencyCount = 0;
        $vitalSums = ['temperature' => 0.0, 'spo2' => 0.0, 'pulse' => 0.0, 'bp_sys' => 0.0, 'bp_dia' => 0.0];
        $vitalCounts = ['temperature' => 0, 'spo2' => 0, 'pulse' => 0, 'bp' => 0];

        foreach ($consultations as $consultation) {
            $status = $consultation->getStatus() ?? 'unknown';
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;

            if ($consultation->getStatus() === 'emergency' || $consultation->getConsultationType() === 'emergency') {
                $emergencyCount++;
            }

            $vitals = is_array($consultation->getVitals()) ? $consultation->getVitals() : [];
            $temp = $vitals['temperature'] ?? null;
            if (is_numeric($temp)) {
                $vitalSums['temperature'] += (float) $temp;
                $vitalCounts['temperature']++;
            }
            $spo2 = $vitals['spo2'] ?? $vitals['oxygenSaturation'] ?? null;
            if (is_numeric($spo2)) {
                $vitalSums['spo2'] += (float) $spo2;
                $vitalCounts['spo2']++;
            }
            $pulse = $vitals['pulse'] ?? $vitals['heartRate'] ?? null;
            if (is_numeric($pulse)) {
                $vitalSums['pulse'] += (float) $pulse;
                $vitalCounts['pulse']++;
            }
            $bp = is_array($vitals['bloodPressure'] ?? null) ? $vitals['bloodPressure'] : [];
            $systolic = $bp['systolic'] ?? $vitals['bloodPressureSystolic'] ?? null;
            $diastolic = $bp['diastolic'] ?? $vitals['bloodPressureDiastolic'] ?? null;
            if (is_numeric($systolic) && is_numeric($diastolic)) {
                $vitalSums['bp_sys'] += (float) $systolic;
                $vitalSums['bp_dia'] += (float) $diastolic;
                $vitalCounts['bp']++;
            }
        }

        $avgVitals = [
            'temperature' => $vitalCounts['temperature'] ? round($vitalSums['temperature'] / $vitalCounts['temperature'], 2) : null,
            'spo2' => $vitalCounts['spo2'] ? round($vitalSums['spo2'] / $vitalCounts['spo2'], 2) : null,
            'pulse' => $vitalCounts['pulse'] ? round($vitalSums['pulse'] / $vitalCounts['pulse'], 2) : null,
            'bloodPressure' => $vitalCounts['bp'] ? [
                'systolic' => round($vitalSums['bp_sys'] / $vitalCounts['bp'], 1),
                'diastolic' => round($vitalSums['bp_dia'] / $vitalCounts['bp'], 1),
            ] : null,
        ];

        return [
            'meta' => [
                'type' => $type,
                'period' => $period,
                'generated_at' => (new \DateTimeImmutable())->format(DATE_ATOM),
            ],
            'patient' => [
                'id' => $patient?->getId(),
                'name' => $patient ? ($patient->getFirstName() . ' ' . $patient->getLastName()) : null,
            ],
            'summary' => [
                'total_consultations' => $total,
                'average_duration' => round($avgDuration, 2),
                'emergency_count' => $emergencyCount,
                'status_counts' => $statusCounts,
                'last_consultation_date' => $latest?->getDateConsultation()?->format('Y-m-d'),
            ],
            'vitals_average' => $avgVitals,
        ];
    }

    private function renderReportPdf(array $reportData): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $patientName = $reportData['patient']['name'] ?? 'Patient';
        $meta = $reportData['meta'] ?? [];
        $summary = $reportData['summary'] ?? [];
        $vitals = $reportData['vitals_average'] ?? [];

        $html = '<html><head><meta charset="UTF-8"><style>'
            . 'body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }'
            . 'h1 { font-size: 18px; margin-bottom: 8px; }'
            . 'h2 { font-size: 14px; margin-top: 16px; }'
            . '.meta, .section { margin-bottom: 12px; }'
            . 'table { width: 100%; border-collapse: collapse; }'
            . 'th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }'
            . 'th { background: #f5f5f5; }'
            . '</style></head><body>';

        $html .= '<h1>Rapport Medical</h1>';
        $html .= '<div class="meta"><strong>Patient:</strong> ' . htmlspecialchars($patientName) . '<br>';
        $html .= '<strong>Type:</strong> ' . htmlspecialchars((string) ($meta['type'] ?? '')) . '<br>';
        $html .= '<strong>Periode:</strong> ' . htmlspecialchars((string) ($meta['period'] ?? '')) . '<br>';
        $html .= '<strong>Genere le:</strong> ' . htmlspecialchars((string) ($meta['generated_at'] ?? '')) . '</div>';

        $html .= '<div class="section"><h2>Resume</h2><table><tbody>';
        $html .= '<tr><th>Total consultations</th><td>' . htmlspecialchars((string) ($summary['total_consultations'] ?? '0')) . '</td></tr>';
        $html .= '<tr><th>Duree moyenne</th><td>' . htmlspecialchars((string) ($summary['average_duration'] ?? '0')) . '</td></tr>';
        $html .= '<tr><th>Urgences</th><td>' . htmlspecialchars((string) ($summary['emergency_count'] ?? '0')) . '</td></tr>';
        $html .= '<tr><th>Derniere consultation</th><td>' . htmlspecialchars((string) ($summary['last_consultation_date'] ?? '-')) . '</td></tr>';
        $html .= '</tbody></table></div>';

        $html .= '<div class="section"><h2>Signes vitaux (moyenne)</h2><table><tbody>';
        $html .= '<tr><th>Temperature</th><td>' . htmlspecialchars((string) ($vitals['temperature'] ?? '-')) . '</td></tr>';
        $html .= '<tr><th>SpO2</th><td>' . htmlspecialchars((string) ($vitals['spo2'] ?? '-')) . '</td></tr>';
        $html .= '<tr><th>Pulse</th><td>' . htmlspecialchars((string) ($vitals['pulse'] ?? '-')) . '</td></tr>';
        $bp = $vitals['bloodPressure'] ?? null;
        $bpText = '-';
        if (is_array($bp)) {
            $bpText = ($bp['systolic'] ?? '-') . '/' . ($bp['diastolic'] ?? '-');
        }
        $html .= '<tr><th>Tension</th><td>' . htmlspecialchars((string) $bpText) . '</td></tr>';
        $html .= '</tbody></table></div>';

        $html .= '</body></html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function sanitizeFilename(string $name): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
        if ($value === false) {
            $value = $name;
        }
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim($value ?? '', '-');
        return $value !== '' ? $value : 'patient';
    }

    /**
     * Generate Report - Génère un rapport médical (AJAX)
     */
    #[Route('/analytics/generate-report', name: 'health_generate_report', methods: ['POST'])]
    public function generateReport(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['patient_id'])) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides',
            ], 400);
        }

        $user = $this->getUser();
        if (!$user instanceof Medecin) {
            return $this->json([
                'success' => false,
                'message' => 'Accès refusé',
            ], 403);
        }

        $patientId = (string) $data['patient_id'];
        $reportType = (string) ($data['report_type'] ?? 'summary');
        $reportPeriod = (string) ($data['report_period'] ?? '30d');
        $since = $this->parsePeriodStart($reportPeriod);

        $consultations = $this->consultationRepository
            ->findByMedecinAndPatientOrderedByDateTime($user->getId(), $patientId, $since);

        if (count($consultations) === 0) {
            return $this->json([
                'success' => false,
                'message' => 'Aucune consultation trouvée pour ce patient',
            ], 404);
        }

        $reportId = uniqid('RPT-');
        $reportData = $this->buildPatientReport($consultations, $reportType, $reportPeriod);

        $reportFormat = (string) ($data['report_format'] ?? 'pdf');

        if ($reportFormat === 'pdf') {
            $pdfContent = $this->renderReportPdf($reportData);
            $patientName = (string) ($reportData['patient']['name'] ?? 'patient');
            $slug = $this->sanitizeFilename($patientName);
            $date = (new \DateTimeImmutable())->format('Y-m-d');
            $filename = 'rapport-' . $slug . '-' . $date . '.pdf';
            $response = new Response($pdfContent);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
            return $response;
        }

        return $this->json([
            'success' => true,
            'message' => 'Rapport g?n?r? avec succ?s',
            'report_id' => $reportId,
            'report' => $reportData,
            'filename' => 'rapport-' . $reportId . '.json',
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
     * Affiche la liste des patients acceptés pour le médecin connecté
     */
    #[Route('/doctor/patients', name: 'doctor_patient_list', methods: ['GET'])]
    public function doctorPatientList(ConsultationRepository $consultationRepository): Response
    {
        // Récupérer le médecin actuellement connecté
        $user = $this->getUser();
        
        // Récupérer uniquement les consultations acceptées pour ce médecin
        $consultations = $consultationRepository->findAcceptedByMedecin($user->getUuid());
        
        return $this->render('doctor/patient-list.html.twig', [
            'page_title' => 'Liste des Patients',
            'consultations' => $consultations,
        ]);
    }

    /**
     * Doctor Interface - Patient Chart
     * Affiche le dossier médical complet d'une consultation avec ses symptômes et traitements
     */
    #[Route('/doctor/patient/{id}/chart', name: 'doctor_patient_chart', methods: ['GET'])]
    public function doctorPatientChart(int $id, EntityManagerInterface $em): Response
    {
        // Récupérer la consultation par ID
        $consultation = $em->getRepository(Consultation::class)->find($id);
        
        if (!$consultation) {
            return $this->render('doctor/patient-chart.html.twig', [
                'page_title' => 'Dossier Médical',
                'consultation' => null,
            ]);
        }
        
        // Récupérer le patient depuis la consultation
        $patient = $consultation->getPatient();
        
        // Préparer les données de la consultation actuelle
        $currentConsultation = [
            'id' => $consultation->getId(),
            'date' => $consultation->getDateConsultation() ? $consultation->getDateConsultation()->format('d/m/Y') : 'N/A',
            'time' => $consultation->getTimeConsultation() ? $consultation->getTimeConsultation()->format('H:i') : '',
            'reasonForVisit' => $consultation->getReasonForVisit(),
            'symptomsDescription' => $consultation->getSymptomsDescription(),
            'diagnoses' => $consultation->getDiagnoses() ?? [],
            'assessment' => $consultation->getAssessment(),
            'plan' => $consultation->getPlan(),
            'notes' => $consultation->getNotes(),
            'status' => $consultation->getStatus(),
            'soapNotes' => $consultation->getSoapNotes() ?? [],
            'appointmentMode' => $consultation->getAppointmentMode(),
            'consultationType' => $consultation->getConsultationType(),
            'duration' => $consultation->getDuration(),
            'location' => $consultation->getLocation(),
        ];
        
        // Préparer les données du patient
        $patientData = null;
        $allVitals = [];
        $allMedications = [];
        $allExamens = [];
        $timelineData = [];
        $allConsultationsData = [];
        
        if ($patient) {
            $patientData = [
                'id' => $patient->getUuid(),
                'name' => trim(($patient->getFirstName() ?? '') . ' ' . ($patient->getLastName() ?? '')),
                'firstName' => $patient->getFirstName(),
                'lastName' => $patient->getLastName(),
                'email' => $patient->getEmail(),
                'phone' => $patient->getPhone(),
                'age' => $patient->getBirthdate() ? $patient->getBirthdate()->diff(new \DateTime())->y : null,
                'gender' => 'M', // Default
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode(trim(($patient->getFirstName() ?? '') . ' ' . ($patient->getLastName() ?? ''))) . '&background=00A790&color=fff',
                'birthDate' => $patient->getBirthdate() ? $patient->getBirthdate()->format('d/m/Y') : '--',
            ];
            
            // Récupérer toutes les consultations du patient
            $allConsultations = $em->getRepository(Consultation::class)->findBy(
                ['patient' => $patient],
                ['date_consultation' => 'DESC']
            );
            
            // Agréger toutes les données de toutes les consultations
            foreach ($allConsultations as $cons) {
                // Build consultations list for sidebar
                $allConsultationsData[] = [
                    'id' => $cons->getId(),
                    'date' => $cons->getDateConsultation() ? $cons->getDateConsultation()->format('d/m/Y') : 'N/A',
                    'time' => $cons->getTimeConsultation() ? $cons->getTimeConsultation()->format('H:i') : '',
                    'reasonForVisit' => $cons->getReasonForVisit(),
                    'status' => $cons->getStatus(),
                    'isCurrent' => $cons->getId() === $consultation->getId(),
                ];
                
                // Vitals
                $vitals = $cons->getVitals();
                if (!empty($vitals)) {
                    // Handle both flat and nested blood pressure structures
                    $bpS = $vitals['bloodPressureSystolic'] ?? null;
                    $bpD = $vitals['bloodPressureDiastolic'] ?? null;
                    
                    // Check for nested bloodPressure object
                    if (isset($vitals['bloodPressure']) && is_array($vitals['bloodPressure'])) {
                        $bpS = $bpS ?? $vitals['bloodPressure']['systolic'] ?? null;
                        $bpD = $bpD ?? $vitals['bloodPressure']['diastolic'] ?? null;
                    }
                    
                    $bloodPressure = null;
                    if ($bpS && $bpD) {
                        $bloodPressure = $bpS . '/' . $bpD;
                    } elseif (isset($vitals['bloodPressure']) && is_string($vitals['bloodPressure'])) {
                        $bloodPressure = $vitals['bloodPressure'];
                    }
                    
                    $allVitals[] = [
                        'date' => $cons->getDateConsultation() ? $cons->getDateConsultation()->format('d/m/Y') : 'N/A',
                        'time' => $cons->getTimeConsultation() ? $cons->getTimeConsultation()->format('H:i') : '',
                        'bloodPressure' => $bloodPressure ?? '--',
                        'heartRate' => $vitals['heartRate'] ?? $vitals['pulse'] ?? null,
                        'temperature' => $vitals['temperature'] ?? null,
                        'weight' => $vitals['weight'] ?? null,
                        'height' => $vitals['height'] ?? null,
                        'spo2' => $vitals['oxygenSaturation'] ?? $vitals['spo2'] ?? null,
                        'consultationId' => $cons->getId(),
                    ];
                }
                
                // Medications (ordonnances)
                $ordonnances = $em->getRepository(Ordonnance::class)->findBy(['consultation' => $cons]);
                foreach ($ordonnances as $ord) {
                    $allMedications[] = [
                        'id' => $ord->getId(),
                        'name' => $ord->getMedicament(),
                        'dosage' => $ord->getDosage(),
                        'frequency' => $ord->getFrequency(),
                        'instructions' => $ord->getInstructions(),
                        'date' => $ord->getDateOrdonnance() ? $ord->getDateOrdonnance()->format('d/m/Y') : '--',
                        'consultationId' => $cons->getId(),
                    ];
                }
                
                // Examens
                $examens = $em->getRepository(Examens::class)->findBy(['consultation' => $cons]);
                foreach ($examens as $exam) {
                    $allExamens[] = [
                        'id' => $exam->getId(),
                        'name' => $exam->getNomExamen(),
                        'type' => $exam->getTypeExamen(),
                        'result' => $exam->getResultat(),
                        'date' => $exam->getDateExamen() ? $exam->getDateExamen()->format('d/m/Y') : '--',
                        'consultationId' => $cons->getId(),
                        'status' => $exam->getStatus(),
                        'resultFile' => $exam->getResultFile(),
                        'doctorAnalysis' => $exam->getDoctorAnalysis(),
                        'doctorTreatment' => $exam->getDoctorTreatment(),
                    ];
                }
                
                // Timeline
                $timelineData[] = [
                    'id' => $cons->getId(),
                    'type' => 'consultation',
                    'typeLabel' => 'Consultation',
                    'title' => $cons->getReasonForVisit() ?? 'Consultation',
                    'description' => $cons->getSymptomsDescription() ?? $cons->getNotes() ?? 'Pas de description',
                    'date' => $cons->getDateConsultation() ? $cons->getDateConsultation()->format('d/m/Y') : 'N/A',
                    'status' => $cons->getStatus(),
                ];
            }
        } else {
            // Fallback: utiliser les données de la consultation unique
            $patientData = [
                'id' => $consultation->getId(),
                'name' => $consultation->getReasonForVisit() ?? 'Patient',
                'firstName' => null,
                'lastName' => null,
                'email' => '--',
                'phone' => '--',
                'age' => null,
                'gender' => 'M',
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($consultation->getReasonForVisit() ?? 'Patient') . '&background=00A790&color=fff',
                'birthDate' => '--',
            ];
            
            // Vitals from single consultation
            $vitals = $consultation->getVitals();
            if (!empty($vitals)) {
                $bpS = $vitals['bloodPressureSystolic'] ?? null;
                $bpD = $vitals['bloodPressureDiastolic'] ?? null;
                
                if (isset($vitals['bloodPressure']) && is_array($vitals['bloodPressure'])) {
                    $bpS = $bpS ?? $vitals['bloodPressure']['systolic'] ?? null;
                    $bpD = $bpD ?? $vitals['bloodPressure']['diastolic'] ?? null;
                }
                
                $bloodPressure = null;
                if ($bpS && $bpD) {
                    $bloodPressure = $bpS . '/' . $bpD;
                }
                
                $allVitals[] = [
                    'date' => $consultation->getDateConsultation() ? $consultation->getDateConsultation()->format('d/m/Y') : 'N/A',
                    'time' => $consultation->getTimeConsultation() ? $consultation->getTimeConsultation()->format('H:i') : '',
                    'bloodPressure' => $bloodPressure ?? '--',
                    'heartRate' => $vitals['heartRate'] ?? $vitals['pulse'] ?? null,
                    'temperature' => $vitals['temperature'] ?? null,
                    'weight' => $vitals['weight'] ?? null,
                    'height' => $vitals['height'] ?? null,
                    'spo2' => $vitals['oxygenSaturation'] ?? $vitals['spo2'] ?? null,
                    'consultationId' => $consultation->getId(),
                ];
            }
            
            // Timeline for single consultation
            $timelineData[] = [
                'id' => $consultation->getId(),
                'type' => 'consultation',
                'typeLabel' => 'Consultation',
                'title' => $consultation->getReasonForVisit() ?? 'Consultation',
                'description' => $consultation->getSymptomsDescription() ?? $consultation->getNotes() ?? 'Pas de description',
                'date' => $consultation->getDateConsultation() ? $consultation->getDateConsultation()->format('d/m/Y') : 'N/A',
                'status' => $consultation->getStatus(),
            ];
            
            // Medications from single consultation
            $ordonnances = $em->getRepository(Ordonnance::class)->findBy(['consultation' => $consultation]);
            foreach ($ordonnances as $ord) {
                $allMedications[] = [
                    'id' => $ord->getId(),
                    'name' => $ord->getMedicament(),
                    'dosage' => $ord->getDosage(),
                    'frequency' => $ord->getFrequency(),
                    'instructions' => $ord->getInstructions(),
                    'date' => $ord->getDateOrdonnance() ? $ord->getDateOrdonnance()->format('d/m/Y') : '--',
                    'consultationId' => $consultation->getId(),
                ];
            }
            
            // Examens from single consultation
            $examens = $em->getRepository(Examens::class)->findBy(['consultation' => $consultation]);
            foreach ($examens as $exam) {
                $allExamens[] = [
                    'id' => $exam->getId(),
                    'name' => $exam->getNomExamen(),
                    'type' => $exam->getTypeExamen(),
                    'result' => $exam->getResultat(),
                    'date' => $exam->getDateExamen() ? $exam->getDateExamen()->format('d/m/Y') : '--',
                    'consultationId' => $consultation->getId(),
                    'status' => $exam->getStatus(),
                    'resultFile' => $exam->getResultFile(),
                    'doctorAnalysis' => $exam->getDoctorAnalysis(),
                    'doctorTreatment' => $exam->getDoctorTreatment(),
                ];
            }
            
            $allConsultationsData[] = [
                'id' => $consultation->getId(),
                'date' => $consultation->getDateConsultation() ? $consultation->getDateConsultation()->format('d/m/Y') : 'N/A',
                'time' => $consultation->getTimeConsultation() ? $consultation->getTimeConsultation()->format('H:i') : '',
                'reasonForVisit' => $consultation->getReasonForVisit(),
                'status' => $consultation->getStatus(),
                'isCurrent' => true,
            ];
        }
        
        return $this->render('doctor/patient-chart.html.twig', [
            'page_title' => 'Dossier Médical',
            'consultation_id' => $id,
            'consultation' => $currentConsultation,
            'patient_data' => $patientData,
            'vital_signs' => $allVitals,
            'medications_data' => $allMedications,
            'examens_data' => $allExamens,
            'timeline_data' => $timelineData,
            'all_consultations' => $allConsultationsData,
        ]);
    }

    /**
     * API - Patient chart data (based on consultation)
     */
    #[Route('/doctor/api/patient-chart/{id}', name: 'health_doctor_patient_chart_api', methods: ['GET'])]
    public function getPatientChartData(int $id, EntityManagerInterface $em): JsonResponse
    {
        $consultation = $em->getRepository(Consultation::class)->find($id);
        
        if (!$consultation) {
            return $this->json([
                'success' => false,
                'message' => 'Consultation non trouvee',
            ], 404);
        }
        
        $ordonnances = $em->getRepository(Ordonnance::class)->findBy(['consultation' => $consultation]);
        $examens = $em->getRepository(Examens::class)->findBy(['consultation' => $consultation]);
        
        $chartData = $this->buildPatientChartData($consultation, $ordonnances, $examens);
        
        return $this->json([
            'success' => true,
            'data' => $chartData,
        ]);
    }

    /**
     * Helper - Build patient chart data from consultation
     */
    private function buildPatientChartData(Consultation $consultation, array $ordonnances, array $examens): array
    {
        $patientName = $consultation->getReasonForVisit() ?: 'Patient';
        $diagnoses = $consultation->getDiagnoses();
        $conditions = is_array($diagnoses) ? array_values($diagnoses) : [];
        
        $vitals = $consultation->getVitals();
        $vitals = is_array($vitals) ? $vitals : [];
        
        $vitalSigns = [];
        if (!empty($vitals)) {
            // Handle both flat and nested blood pressure structures
            $bpS = $vitals['bloodPressureSystolic'] ?? null;
            $bpD = $vitals['bloodPressureDiastolic'] ?? null;
            
            // Check for nested bloodPressure object
            if (isset($vitals['bloodPressure']) && is_array($vitals['bloodPressure'])) {
                $bpS = $bpS ?? $vitals['bloodPressure']['systolic'] ?? null;
                $bpD = $bpD ?? $vitals['bloodPressure']['diastolic'] ?? null;
            }
            
            $bloodPressure = null;
            if ($bpS && $bpD) {
                $bloodPressure = $bpS . '/' . $bpD;
            } elseif (isset($vitals['bloodPressure']) && is_string($vitals['bloodPressure'])) {
                $bloodPressure = $vitals['bloodPressure'];
            }
            
            $vitalSigns[] = [
                'date' => $consultation->getDateConsultation() ? $consultation->getDateConsultation()->format('d/m/Y') : 'N/A',
                'time' => $consultation->getTimeConsultation() ? $consultation->getTimeConsultation()->format('H:i') : '',
                'bloodPressure' => $bloodPressure ?? '--',
                'heartRate' => $vitals['heartRate'] ?? $vitals['pulse'] ?? null,
                'temperature' => $vitals['temperature'] ?? null,
                'weight' => $vitals['weight'] ?? null,
                'height' => $vitals['height'] ?? null,
                'spo2' => $vitals['oxygenSaturation'] ?? $vitals['spo2'] ?? null,
            ];
        }
        
        $height = isset($vitals['height']) ? (float) $vitals['height'] : 0;
        $weight = isset($vitals['weight']) ? (float) $vitals['weight'] : 0;
        $bmi = 0;
        if ($height > 0 && $weight > 0) {
            $heightM = $height / 100;
            $bmi = round($weight / ($heightM * $heightM), 1);
        }
        
        $patientStatus = 'stable';
        if ($consultation->getStatus() === 'completed') {
            $patientStatus = 'active';
        } elseif ($consultation->getStatus() === 'emergency') {
            $patientStatus = 'critical';
        }
        
        $patient = [
            'id' => $consultation->getId(),
            'name' => $patientName,
            'age' => '--',
            'gender' => 'M',
            'birthDate' => '--',
            'fileNumber' => 'CONS-' . str_pad((string) $consultation->getId(), 4, '0', STR_PAD_LEFT),
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($patientName) . '&background=00A790&color=fff',
            'status' => $patientStatus,
            'healthScore' => $this->calculateHealthScoreFromVitals($vitals),
            'conditions' => array_slice($conditions, 0, 5),
            'lastVisitDate' => $consultation->getDateConsultation() ? $consultation->getDateConsultation()->format('d/m/Y') : '--',
            'nextAppointment' => null,
            'bloodType' => '--',
            'height' => $height,
            'weight' => $weight,
            'bmi' => $bmi,
            'phone' => '--',
            'email' => '--',
            'address' => '--',
            'emergencyContact' => [
                'name' => '--',
                'relation' => '--',
                'phone' => '--',
            ],
            'allergies' => [],
            'medications' => [],
        ];
        
        $timeline = [
            [
                'id' => $consultation->getId(),
                'type' => 'symptom',
                'typeLabel' => 'Consultation',
                'title' => $consultation->getReasonForVisit() ?? 'Consultation',
                'description' => $consultation->getSymptomsDescription() ?? $consultation->getNotes() ?? 'Pas de description',
                'date' => $consultation->getDateConsultation() ? $consultation->getDateConsultation()->format('d/m/Y') : 'N/A',
                'severity' => $consultation->getStatus() === 'emergency' ? 5 : 2,
            ],
        ];
        
        $symptoms = [];
        if ($consultation->getSymptomsDescription()) {
            $symptoms[] = [
                'id' => 1,
                'name' => $consultation->getReasonForVisit() ?? 'Symptome',
                'date' => $consultation->getDateConsultation() ? $consultation->getDateConsultation()->format('d/m/Y') : 'N/A',
                'intensity' => 5,
                'status' => $consultation->getStatus() === 'completed' ? 'resolved' : 'active',
                'description' => $consultation->getSymptomsDescription(),
            ];
        }
        
        $medications = [];
        foreach ($ordonnances as $ord) {
            $medications[] = [
                'id' => $ord->getId(),
                'name' => $ord->getMedicament() ?? 'Medicament',
                'dosage' => $ord->getDosage() ?? '--',
                'frequency' => $ord->getFrequency() ?? '--',
                'active' => true,
            ];
            
            $timeline[] = [
                'id' => 'MED-' . $ord->getId(),
                'type' => 'medication',
                'typeLabel' => 'Traitement',
                'title' => 'Prescription: ' . ($ord->getMedicament() ?? 'Medicament'),
                'description' => ($ord->getDosage() ?? '') . ' - ' . ($ord->getInstructions() ?? ''),
                'date' => $ord->getDateOrdonnance() ? $ord->getDateOrdonnance()->format('d/m/Y') : 'N/A',
            ];
        }
        
        foreach ($examens as $exam) {
            $timeline[] = [
                'id' => 'EXAM-' . $exam->getId(),
                'type' => 'lab',
                'typeLabel' => 'Examen',
                'title' => $exam->getNomExamen() ?? $exam->getTypeExamen() ?? 'Examen',
                'description' => $exam->getResultat() ?? 'En attente',
                'date' => $exam->getDateExamen() ? $exam->getDateExamen()->format('d/m/Y') : 'N/A',
            ];
        }
        
        usort($timeline, function ($a, $b) {
            $dateA = ($a['date'] ?? 'N/A') === 'N/A' ? '1970-01-01' : $a['date'];
            $dateB = ($b['date'] ?? 'N/A') === 'N/A' ? '1970-01-01' : $b['date'];
            return $dateB <=> $dateA;
        });
        
        $followUp = $consultation->getFollowUp();
        $followUp = is_array($followUp) ? $followUp : [];
        $treatmentGoals = $followUp['goals'] ?? [];
        $treatmentFollowUps = $followUp['followUps'] ?? [];
        
        if (empty($treatmentGoals) && !empty($medications)) {
            $treatmentGoals = array_map(function ($med, $index) {
                $label = trim(($med['name'] ?? 'Médicament') . ' ' . ($med['dosage'] ?? ''));
                return [
                    'id' => $med['id'] ?? ($index + 1),
                    'description' => 'Traitement: ' . $label,
                    'completed' => false,
                    'deadline' => 'En cours',
                ];
            }, $medications, array_keys($medications));
        }
        
        $treatment = [
            'adherence' => (int) ($followUp['adherence'] ?? (!empty($medications) ? 80 : 0)),
            'goals' => $treatmentGoals,
            'followUps' => $treatmentFollowUps,
        ];
        
        $patient['medications'] = $medications;
        
        return [
            'patient' => $patient,
            'timeline' => $timeline,
            'symptoms' => $symptoms,
            'medications' => $medications,
            'treatment' => $treatment,
            'vitalSigns' => $vitalSigns,
        ];
    }

    /**
     * Helper - Health score from vitals
     */
    private function calculateHealthScoreFromVitals(array $vitals): int
    {
        $score = 85;
        
        if (isset($vitals['bloodPressureSystolic']) && isset($vitals['bloodPressureDiastolic'])) {
            $systolic = (int) $vitals['bloodPressureSystolic'];
            $diastolic = (int) $vitals['bloodPressureDiastolic'];
            
            if ($systolic > 140 || $diastolic > 90) {
                $score -= 15;
            } elseif ($systolic < 90 || $diastolic < 60) {
                $score -= 10;
            }
        }
        
        if (isset($vitals['temperature'])) {
            $temp = (float) $vitals['temperature'];
            if ($temp > 38 || $temp < 36) {
                $score -= 10;
            }
        }
        
        if (isset($vitals['heartRate'])) {
            $heartRate = (int) $vitals['heartRate'];
            if ($heartRate > 100 || $heartRate < 60) {
                $score -= 5;
            }
        }
        
        if (isset($vitals['oxygenSaturation'])) {
            $o2 = (int) $vitals['oxygenSaturation'];
            if ($o2 < 95) {
                $score -= 15;
            }
        }
        
        return max(0, min(100, $score));
    }

    /**
     * Doctor Interface - Patient Chart by Patient UUID
     * Affiche le dossier médical complet d'un patient avec toutes ses consultations
     */
    #[Route('/doctor/patient-chart/{uuid}', name: 'doctor_patient_chart_by_uuid', methods: ['GET'])]
    public function doctorPatientChartByUuid(string $uuid, EntityManagerInterface $em): Response
    {
        // Use the base UserRepository - Doctrine STI will return the correct subclass
        $user = $em->getRepository(User::class)->findOneBy(['uuid' => $uuid]);
        
        // Debug: Log what we found
        $debugInfo = [
            'uuid' => $uuid,
            'user_found' => $user ? 'yes' : 'no',
            'user_class' => $user ? get_class($user) : 'null',
            'is_patient' => $user instanceof Patient ? 'yes' : 'no',
        ];
        
        // Check if user exists and is a Patient instance
        if (!$user) {
            // Add debug info to the response for troubleshooting
            $patientData = [
                'id' => $uuid,
                'name' => 'Patient non trouvé (User not found)',
                'firstName' => '',
                'lastName' => '',
                'email' => '--',
                'phone' => '--',
                'age' => '--',
                'gender' => 'M',
                'avatar' => 'https://ui-avatars.com/api/?name=Patient&background=00A790&color=fff',
                'birthDate' => '--',
                'fileNumber' => 'PAT-' . substr($uuid, 0, 8),
                'status' => 'active',
                'healthScore' => 85,
                'conditions' => [],
                'lastVisitDate' => '--',
                'nextAppointment' => null,
                'bloodType' => '--',
                'height' => 0,
                'weight' => 0,
                'bmi' => 0,
                'address' => '--',
                'emergencyContact' => [
                    'name' => '--',
                    'relation' => '--',
                    'phone' => '--'
                ],
                'allergies' => [],
                'medications' => [],
                'debug' => $debugInfo,
            ];
            
            return $this->render('doctor/patient-chart.html.twig', [
                'page_title' => 'Dossier Médical',
                'patient_data' => $patientData,
                'vital_signs' => [],
                'medications_data' => [],
                'examens_data' => [],
                'timeline_data' => [],
                'consultation' => null,
                'consultation_id' => null,
                'consultation_data' => [],
                'all_consultations' => [],
            ]);
        }
        
        // If user exists but is not a Patient, still show their data
        // (they might be viewing their own chart or the role check might be too strict)
        $patient = $user; // Use the user object directly
        
        // Récupérer toutes les consultations du patient
        $consultations = $em->getRepository(Consultation::class)->findBy(
            ['patient' => $patient],
            ['date_consultation' => 'DESC']
        );
        
        // Debug: Log consultations
        error_log("Consultations found: " . count($consultations));
        
        // Préparer les données du patient avec tous les champs requis par le composant Alpine
        // Patient extends User, so all User fields are available
        $firstName = $patient->getFirstName() ?? '';
        $lastName = $patient->getLastName() ?? '';
        $fullName = trim($firstName . ' ' . $lastName);
        if (empty($fullName)) {
            $fullName = 'Patient ' . substr($patient->getUuid(), 0, 8);
        }
        
        // Debug: Log patient name
        error_log("Patient name: " . $fullName);
        
        $patientData = [
            'id' => $patient->getUuid(),
            'name' => $fullName,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $patient->getEmail() ?? '--',
            'phone' => $patient->getPhone() ?? '--',
            'age' => $patient->getBirthdate() ? $patient->getBirthdate()->diff(new \DateTime())->y : '--',
            'gender' => 'M', // Default - could be stored in user entity if needed
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=00A790&color=fff',
            'birthDate' => $patient->getBirthdate() ? $patient->getBirthdate()->format('d/m/Y') : '--',
            // Additional fields required by Alpine component
            'fileNumber' => 'PAT-' . substr($patient->getUuid(), 0, 8),
            'status' => 'active',
            'healthScore' => 85,
            'conditions' => [],
            'lastVisitDate' => '--',
            'nextAppointment' => null,
            'bloodType' => '--',
            'height' => 0,
            'weight' => 0,
            'bmi' => 0,
            'address' => $patient->getAddress() ?? '--',
            'emergencyContact' => [
                'name' => '--',
                'relation' => '--',
                'phone' => '--'
            ],
            'allergies' => [],
            'medications' => []
        ];
        
        // Agréger toutes les données de toutes les consultations
        $allVitals = [];
        $allMedications = [];
        $allExamens = [];
        $timelineData = [];
        
        foreach ($consultations as $consultation) {
            // Vitals
            $vitals = $consultation->getVitals();
            if (!empty($vitals)) {
                // Handle both flat and nested blood pressure structures
                $bpS = $vitals['bloodPressureSystolic'] ?? null;
                $bpD = $vitals['bloodPressureDiastolic'] ?? null;
                
                // Check for nested bloodPressure object
                if (isset($vitals['bloodPressure']) && is_array($vitals['bloodPressure'])) {
                    $bpS = $bpS ?? $vitals['bloodPressure']['systolic'] ?? null;
                    $bpD = $bpD ?? $vitals['bloodPressure']['diastolic'] ?? null;
                }
                
                $bloodPressure = null;
                if ($bpS && $bpD) {
                    $bloodPressure = $bpS . '/' . $bpD;
                } elseif (isset($vitals['bloodPressure']) && is_string($vitals['bloodPressure'])) {
                    $bloodPressure = $vitals['bloodPressure'];
                }
                
                $allVitals[] = [
                    'date' => $consultation->getDateConsultation() ? $consultation->getDateConsultation()->format('d/m/Y') : 'N/A',
                    'time' => $consultation->getTimeConsultation() ? $consultation->getTimeConsultation()->format('H:i') : '',
                    'bloodPressure' => $bloodPressure ?? '--',
                    'heartRate' => $vitals['heartRate'] ?? $vitals['pulse'] ?? null,
                    'temperature' => $vitals['temperature'] ?? null,
                    'weight' => $vitals['weight'] ?? null,
                    'height' => $vitals['height'] ?? null,
                    'spo2' => $vitals['oxygenSaturation'] ?? $vitals['spo2'] ?? null,
                ];
            }
            
            // Medications (ordonnances)
            $ordonnances = $em->getRepository(Ordonnance::class)->findBy(['consultation' => $consultation]);
            foreach ($ordonnances as $ord) {
                $allMedications[] = [
                    'id' => $ord->getId(),
                    'name' => $ord->getMedicament(),
                    'dosage' => $ord->getDosage(),
                    'frequency' => $ord->getFrequency(),
                    'instructions' => $ord->getInstructions(),
                    'date' => $ord->getDateOrdonnance() ? $ord->getDateOrdonnance()->format('d/m/Y') : '--',
                ];
            }
            
            // Examens
            $examens = $em->getRepository(Examens::class)->findBy(['consultation' => $consultation]);
            foreach ($examens as $exam) {
                $allExamens[] = [
                    'id' => $exam->getId(),
                    'name' => $exam->getNomExamen(),
                    'type' => $exam->getTypeExamen(),
                    'result' => $exam->getResultat(),
                    'date' => $exam->getDateExamen() ? $exam->getDateExamen()->format('d/m/Y') : '--',
                    'status' => $exam->getStatus(),
                    'resultFile' => $exam->getResultFile(),
                    'doctorAnalysis' => $exam->getDoctorAnalysis(),
                    'doctorTreatment' => $exam->getDoctorTreatment(),
                ];
            }
            
            // Timeline
            $timelineData[] = [
                'id' => $consultation->getId(),
                'type' => 'consultation',
                'typeLabel' => 'Consultation',
                'title' => $consultation->getReasonForVisit() ?? 'Consultation',
                'description' => $consultation->getSymptomsDescription() ?? $consultation->getNotes() ?? 'Pas de description',
                'date' => $consultation->getDateConsultation() ? $consultation->getDateConsultation()->format('d/m/Y') : 'N/A',
                'status' => $consultation->getStatus(),
            ];
        }
        
        // Convert consultations to array format for JSON encoding
        $allConsultationsData = [];
        foreach ($consultations as $cons) {
            $allConsultationsData[] = [
                'id' => $cons->getId(),
                'date' => $cons->getDateConsultation() ? $cons->getDateConsultation()->format('d/m/Y') : 'N/A',
                'time' => $cons->getTimeConsultation() ? $cons->getTimeConsultation()->format('H:i') : '',
                'reasonForVisit' => $cons->getReasonForVisit(),
                'status' => $cons->getStatus(),
                'consultationType' => $cons->getConsultationType(),
                'appointmentMode' => $cons->getAppointmentMode(),
            ];
        }
        
        // Build symptoms data from consultations
        $symptomsData = [];
        foreach ($consultations as $cons) {
            $symptomsDesc = $cons->getSymptomsDescription();
            if (!empty($symptomsDesc)) {
                // Parse symptoms from description (assuming comma-separated or structured format)
                $symptomNames = array_map('trim', explode(',', $symptomsDesc));
                foreach ($symptomNames as $idx => $symptomName) {
                    if (!empty($symptomName)) {
                        $symptomsData[] = [
                            'id' => $cons->getId() . '_' . $idx,
                            'date' => $cons->getDateConsultation() ? $cons->getDateConsultation()->format('d/m/Y') : 'N/A',
                            'name' => $symptomName,
                            'intensity' => 5, // Default intensity
                            'duration' => '--',
                            'status' => 'resolved', // Past consultations have resolved symptoms
                        ];
                    }
                }
            }
        }
        
        // Build treatment data
        $treatmentData = [
            'adherence' => 85, // Default adherence percentage
            'goals' => [], // Treatment goals could be stored in a separate entity
            'followUps' => [], // Follow-up appointments
        ];
        
        // Add follow-up appointments from consultations
        foreach ($consultations as $cons) {
            if ($cons->getStatus() === 'scheduled' || $cons->getStatus() === 'pending') {
                $treatmentData['followUps'][] = [
                    'id' => $cons->getId(),
                    'type' => $cons->getConsultationType() ?? 'Consultation',
                    'date' => $cons->getDateConsultation() ? $cons->getDateConsultation()->format('d/m/Y') : 'N/A',
                    'time' => $cons->getTimeConsultation() ? $cons->getTimeConsultation()->format('H:i') : '',
                    'status' => $cons->getStatus(),
                ];
            }
        }
        
        return $this->render('doctor/patient-chart.html.twig', [
            'page_title' => 'Dossier Médical - ' . $patientData['name'],
            'patient_data' => $patientData,
            'vital_signs' => $allVitals,
            'medications_data' => $allMedications,
            'examens_data' => $allExamens,
            'timeline_data' => $timelineData,
            'symptoms_data' => $symptomsData,
            'treatment_data' => $treatmentData,
            'consultation' => null, // Not a single consultation view
            'consultation_id' => null,
            'consultation_data' => [], // Empty for patient chart by UUID
            'all_consultations' => $allConsultationsData, // All consultations for this patient as array
        ]);
    }

    /**
     * Doctor Interface - Clinical Notes
     * Interface pour les notes cliniques SOAP
     */
    #[Route('/doctor/patient/{id}/notes', name: 'doctor_patient_notes', methods: ['GET'])]
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
