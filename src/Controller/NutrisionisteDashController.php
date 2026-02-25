<?php

namespace App\Controller;

use App\Entity\NutritionConsultation;
use App\Repository\NutritionGoalRepository;
use App\Repository\FoodLogRepository;
use App\Repository\WaterIntakeRepository;
use App\Repository\NutritionConsultationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class NutrisionisteDashController extends AbstractController
{
    private $nutritionGoalRepository;
    private $foodLogRepository;
    private $waterIntakeRepository;
    private $consultationRepository;
    private $entityManager;

    public function __construct(
        NutritionGoalRepository $nutritionGoalRepository,
        FoodLogRepository $foodLogRepository,
        WaterIntakeRepository $waterIntakeRepository,
        NutritionConsultationRepository $consultationRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->nutritionGoalRepository = $nutritionGoalRepository;
        $this->foodLogRepository = $foodLogRepository;
        $this->waterIntakeRepository = $waterIntakeRepository;
        $this->consultationRepository = $consultationRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Main dashboard for nutritionist - shows consultation schedule and messages
     */
    #[Route('/nutrisioniste/dashboard', name: 'nutrisioniste_dash')]
    public function index(): Response
    {
        // Get all patients
        $patients = $this->nutritionGoalRepository->findAll();
        
        // Get recent food logs for statistics
        $recentLogs = $this->foodLogRepository->createQueryBuilder('f')
            ->where('f.date >= :startDate')
            ->setParameter('startDate', new \DateTime('-7 days'))
            ->getQuery()
            ->getResult();
        
        // Calculate unique patients with recent activity
        $activePatientIds = [];
        foreach ($recentLogs as $log) {
            if ($log->getUserId()) {
                $activePatientIds[$log->getUserId()] = true;
            }
        }
        
        // Get consultations from database - use mock data if empty
        $nutritionistId = 1; // Default nutritionist
        $todayConsultations = $this->consultationRepository->findTodayByNutritionist($nutritionistId);
        
        // If no consultations in DB, use mock data for demo
        if (empty($todayConsultations)) {
            $today = new \DateTime();
            $consultations = [
                [
                    'id' => 1,
                    'patientName' => 'Marie Dubois',
                    'patientId' => 1,
                    'time' => '09:00',
                    'duration' => 30,
                    'type' => 'Consultation initiale',
                    'status' => 'confirmed',
                    'date' => $today->format('Y-m-d')
                ],
                [
                    'id' => 2,
                    'patientName' => 'Jean Martin',
                    'patientId' => 2,
                    'time' => '10:30',
                    'duration' => 30,
                    'type' => 'Suivi mensuel',
                    'status' => 'in_progress',
                    'date' => $today->format('Y-m-d')
                ],
                [
                    'id' => 3,
                    'patientName' => 'Sophie Bernard',
                    'patientId' => 3,
                    'time' => '14:00',
                    'duration' => 45,
                    'type' => 'Bilan nutritionnel',
                    'status' => 'pending',
                    'date' => $today->format('Y-m-d')
                ],
                [
                    'id' => 4,
                    'patientName' => 'Ahmed Trabelsi',
                    'patientId' => 4,
                    'time' => '15:30',
                    'duration' => 30,
                    'type' => 'Suivi hebdomadaire',
                    'status' => 'confirmed',
                    'date' => $today->format('Y-m-d')
                ],
            ];
        } else {
            // Convert entities to array format
            $consultations = [];
            foreach ($todayConsultations as $c) {
                $consultations[] = [
                    'id' => $c->getId(),
                    'patientName' => $c->getPatientName() ?? 'Patient #' . $c->getPatientId(),
                    'patientId' => $c->getPatientId(),
                    'time' => $c->getScheduledAt() ? $c->getScheduledAt()->format('H:i') : '',
                    'duration' => $c->getDuration(),
                    'type' => $c->getTypeName(),
                    'status' => $c->getStatus(),
                    'date' => $c->getScheduledAt() ? $c->getScheduledAt()->format('Y-m-d') : ''
                ];
            }
        }
        
        // Get upcoming consultations for next days
        $upcomingConsultations = [];
        $today = new \DateTime();
        for ($i = 1; $i <= 7; $i++) {
            $futureDate = (clone $today)->modify("+{$i} days");
            $upcomingConsultations[] = [
                'date' => $futureDate->format('Y-m-d'),
                'dayName' => $futureDate->format('l'),
                'count' => rand(1, 4)
            ];
        }
        
        // Unread messages count
        $unreadMessages = 3;
        $urgentMessages = 2;
        
        return $this->render('nutrisioniste/dashboard.html.twig', [
            'consultations' => $consultations,
            'upcomingConsultations' => $upcomingConsultations,
            'stats' => [
                'totalPatients' => count($patients),
                'activePatients' => count($activePatientIds),
                'todayConsultations' => count($consultations),
                'pendingConsultations' => count(array_filter($consultations, fn($c) => $c['status'] === 'pending')),
                'unreadMessages' => $unreadMessages,
                'urgentMessages' => $urgentMessages,
            ],
            'patients' => array_slice($patients, 0, 10),
        ]);
    }

    /**
     * View consultation schedule
     */
    #[Route('/nutrisioniste/schedule', name: 'nutrisioniste_schedule')]
    public function schedule(Request $request): Response
    {
        $date = $request->query->get('date', date('Y-m-d'));
        $view = $request->query->get('view', 'day'); // day, week, month
        
        // Try to get consultations from database
        $selectedDate = new \DateTime($date);
        $nutritionistId = 1;
        
        // Get consultations for the selected date from DB
        $startOfDay = clone $selectedDate;
        $startOfDay->setTime(0, 0, 0);
        $endOfDay = clone $selectedDate;
        $endOfDay->setTime(23, 59, 59);
        
        $consultations = $this->consultationRepository->createQueryBuilder('c')
            ->where('c.nutritionistId = :nutritionistId')
            ->andWhere('c.scheduledAt >= :start')
            ->andWhere('c.scheduledAt <= :end')
            ->setParameter('nutritionistId', $nutritionistId)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->orderBy('c.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
        
        // If empty, use mock data
        if (empty($consultations)) {
            $consultations = [
                [
                    'id' => 1,
                    'patientName' => 'Marie Dubois',
                    'patientId' => 1,
                    'time' => '09:00',
                    'endTime' => '09:30',
                    'type' => 'Consultation initiale',
                    'status' => 'confirmed',
                    'notes' => 'Première consultation - bilan complet'
                ],
                [
                    'id' => 2,
                    'patientName' => 'Jean Martin',
                    'patientId' => 2,
                    'time' => '10:30',
                    'endTime' => '11:00',
                    'type' => 'Suivi mensuel',
                    'status' => 'completed',
                    'notes' => 'Suivi de poids'
                ],
                [
                    'id' => 3,
                    'patientName' => 'Sophie Bernard',
                    'patientId' => 3,
                    'time' => '14:00',
                    'endTime' => '14:45',
                    'type' => 'Bilan nutritionnel',
                    'status' => 'pending',
                    'notes' => 'Bilan trimestriel'
                ],
            ];
        } else {
            // Convert entities to array format
            $converted = [];
            foreach ($consultations as $c) {
                $converted[] = [
                    'id' => $c->getId(),
                    'patientName' => $c->getPatientName() ?? 'Patient #' . $c->getPatientId(),
                    'patientId' => $c->getPatientId(),
                    'time' => $c->getScheduledAt() ? $c->getScheduledAt()->format('H:i') : '',
                    'endTime' => $c->getScheduledAt() ? (clone $c->getScheduledAt())->modify("+{$c->getDuration()} minutes")->format('H:i') : '',
                    'type' => $c->getTypeName(),
                    'status' => $c->getStatus(),
                    'notes' => $c->getNotes()
                ];
            }
            $consultations = $converted;
        }
        
        return $this->render('nutrisioniste/schedule.html.twig', [
            'consultations' => $consultations,
            'selectedDate' => $date,
            'view' => $view,
        ]);
    }

    /**
     * View and manage messages
     */
    #[Route('/nutrisioniste/messages', name: 'nutrisioniste_messages')]
    public function messages(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $filter = $request->query->get('filter', 'all'); // all, unread, urgent
        
        // Mock conversations
        $conversations = [
            [
                'id' => 1,
                'patientName' => 'Marie Dubois',
                'patientId' => 1,
                'avatar' => 'MD',
                'lastMessage' => 'Bonjour Dr, j\'ai une question sur mon régime...',
                'time' => 'Il y a 2h',
                'unread' => true,
                'urgent' => false,
            ],
            [
                'id' => 2,
                'patientName' => 'Jean Martin',
                'patientId' => 2,
                'avatar' => 'JM',
                'lastMessage' => 'Merci pour les conseils, je commence demain',
                'time' => 'Hier',
                'unread' => true,
                'urgent' => true,
            ],
            [
                'id' => 3,
                'patientName' => 'Sophie Bernard',
                'patientId' => 3,
                'avatar' => 'SB',
                'lastMessage' => 'Le nouveau plan alimentaire fonctionne très bien!',
                'time' => 'Il y a 3 jours',
                'unread' => false,
                'urgent' => false,
            ],
            [
                'id' => 4,
                'patientName' => 'Ahmed Trabelsi',
                'patientId' => 4,
                'avatar' => 'AT',
                'lastMessage' => 'À tomorrow pour la consultation',
                'time' => 'Il y a 5 jours',
                'unread' => false,
                'urgent' => false,
            ],
        ];
        
        // Filter conversations
        if ($filter === 'unread') {
            $conversations = array_filter($conversations, fn($c) => $c['unread']);
        } elseif ($filter === 'urgent') {
            $conversations = array_filter($conversations, fn($c) => $c['urgent']);
        }
        
        if ($search) {
            $conversations = array_filter($conversations, fn($c) => 
                stripos($c['patientName'], $search) !== false
            );
        }
        
        return $this->render('nutrisioniste/messages.html.twig', [
            'conversations' => array_values($conversations),
            'search' => $search,
            'filter' => $filter,
            'stats' => [
                'total' => 4,
                'unread' => 2,
                'urgent' => 1,
            ]
        ]);
    }

    /**
     * View a specific conversation
     */
    #[Route('/nutrisioniste/messages/{id}', name: 'nutrisioniste_message_view', requirements: ['id' => '\d+'])]
    public function viewMessage(int $id, Request $request): Response
    {
        // Mock messages for conversation
        $messages = [
            [
                'id' => 1,
                'sender' => 'patient',
                'content' => 'Bonjour Dr, j\'ai une question sur mon régime alimentaire.',
                'time' => '09:00',
                'date' => '2026-02-21'
            ],
            [
                'id' => 2,
                'sender' => 'nutritionist',
                'content' => 'Bonjour Marie! Bien sûr, quelle est votre question?',
                'time' => '09:15',
                'date' => '2026-02-21'
            ],
            [
                'id' => 3,
                'sender' => 'patient',
                'content' => 'Je voudrais savoir si je peux remplacer le poulet par du poisson dans mon plan alimentaire?',
                'time' => '09:20',
                'date' => '2026-02-21'
            ],
            [
                'id' => 4,
                'sender' => 'nutritionist',
                'content' => 'Oui, absolument! Le poisson est une excellente alternative. Préférez le poisson gras comme le saumon ou la sardine pour les omega-3. Vous pouvez remplacer le poulet par 150g de poisson 2-3 fois par semaine.',
                'time' => '09:30',
                'date' => '2026-02-21'
            ],
        ];
        
        $patientName = 'Marie Dubois';
        $patientId = $id;
        
        return $this->render('nutrisioniste/message-detail.html.twig', [
            'conversationId' => $id,
            'patientName' => $patientName,
            'patientId' => $patientId,
            'messages' => $messages,
        ]);
    }

    /**
     * Send a message (mock)
     */
    #[Route('/nutrisioniste/messages/{id}/send', name: 'nutrisioniste_message_send', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function sendMessage(int $id, Request $request): Response
    {
        $message = $request->request->get('message', '');
        
        // In real app, save message to database
        // For now, just redirect back to conversation
        
        $this->addFlash('success', 'Message envoyé avec succès!');
        
        return $this->redirectToRoute('nutrisioniste_message_view', ['id' => $id]);
    }

    /**
     * View all patients
     */
    #[Route('/nutrisioniste/patients', name: 'nutrisioniste_patients')]
    public function patients(Request $request): Response
    {
        $search = $request->query->get('search', '');
        
        $patients = $this->nutritionGoalRepository->findAll();
        
        // Filter by search
        if ($search) {
            $patients = array_filter($patients, fn($p) => 
                stripos((string)$p->getUserId(), $search) !== false
            );
        }
        
        return $this->render('nutrisioniste/patients.html.twig', [
            'patients' => $patients,
            'search' => $search,
            'stats' => [
                'total' => count($patients),
                'active' => count($patients),
            ]
        ]);
    }

    /**
     * View patient details
     */
    #[Route('/nutrisioniste/patient/{id}', name: 'nutrisioniste_patient_view', requirements: ['id' => '\d+'])]
    public function viewPatient(int $id): Response
    {
        $goals = $this->nutritionGoalRepository->findBy(['userId' => $id]);
        $goal = !empty($goals) ? $goals[0] : null;
        
        // Get recent food logs
        $startDate = new \DateTime('-30 days');
        $foodLogs = $this->foodLogRepository->createQueryBuilder('f')
            ->where('f.userId = :userId')
            ->andWhere('f.date >= :startDate')
            ->setParameter('userId', $id)
            ->setParameter('startDate', $startDate)
            ->orderBy('f.date', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
        
        // Calculate stats
        $totalCalories = 0;
        $totalProteins = 0;
        $totalCarbs = 0;
        $totalFats = 0;
        
        foreach ($foodLogs as $log) {
            $totalCalories += $log->getTotalCalories() ?? 0;
            $totalProteins += $log->getProtein() ?? 0;
            $totalCarbs += $log->getCarbs() ?? 0;
            $totalFats += $log->getFats() ?? 0;
        }
        
        return $this->render('nutrisioniste/patient-detail.html.twig', [
            'patientId' => $id,
            'goal' => $goal,
            'foodLogs' => array_slice($foodLogs, 0, 10),
            'stats' => [
                'totalCalories' => $totalCalories,
                'avgCalories' => $totalCalories > 0 ? round($totalCalories / 30) : 0,
                'totalProteins' => $totalProteins,
                'totalCarbs' => $totalCarbs,
                'totalFats' => $totalFats,
                'logCount' => count($foodLogs),
            ],
        ]);
    }

    /**
     * Create new consultation
     */
    #[Route('/nutrisioniste/consultation/new', name: 'nutrisioniste_consultation_new', methods: ['GET', 'POST'])]
    public function newConsultation(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Get form data
            $patientId = $request->request->get('patient');
            $date = $request->request->get('date');
            $time = $request->request->get('time');
            $type = $request->request->get('type');
            $duration = $request->request->get('duration');
            $notes = $request->request->get('notes');
            
            // Create new consultation
            $consultation = new NutritionConsultation();
            $consultation->setPatientId((int)$patientId);
            $consultation->setNutritionistId(1); // Default nutritionist
            $consultation->setNutritionistName('Dr. Marie Laurent');
            
            // Set patient name based on selection (in real app, fetch from user entity)
            $patientNames = [
                1 => 'Marie Dubois',
                2 => 'Jean Martin',
                3 => 'Sophie Bernard',
                4 => 'Ahmed Trabelsi'
            ];
            $consultation->setPatientName($patientNames[$patientId] ?? 'Patient #' . $patientId);
            
            // Combine date and time
            $scheduledAt = new \DateTime($date . ' ' . $time);
            $consultation->setScheduledAt($scheduledAt);
            $consultation->setDuration((int)$duration);
            $consultation->setType($type);
            $consultation->setNotes($notes);
            $consultation->setStatus('pending');
            
            // Set price based on type
            $prices = [
                'initial' => 100,
                'followup' => 80,
                'weekly' => 45,
                'review' => 100,
                'emergency' => 120
            ];
            $consultation->setPrice($prices[$type] ?? 80);
            
            // Save to database
            $this->entityManager->persist($consultation);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Consultation créée avec succès!');
            return $this->redirectToRoute('nutrisioniste_schedule');
        }
        
        $patients = $this->nutritionGoalRepository->findAll();
        
        return $this->render('nutrisioniste/consultation-form.html.twig', [
            'patients' => $patients,
        ]);
    }
}
