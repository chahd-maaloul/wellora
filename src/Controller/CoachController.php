<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\GoalRepository;
use App\Repository\DailyPlanRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Coach;
use App\Repository\ExercisesRepository;

#[Route('/coach')]
class CoachController extends AbstractController
{
    // Coach Dashboard - Main entry point
    #[Route('/dashboard', name: 'coach_dashboard')]
    public function dashboard(GoalRepository $goalRepository, DailyPlanRepository $dailyPlanRepository): Response
    {
        // Fetch real goals data
        $goals = $goalRepository->findAll();
        $totalGoals = count($goals);

        // Calculate stats from real data
        $activeGoals = count(array_filter($goals, function($goal) {
            return $goal->getStatus() === 'in progress';
        }));
        $completedGoals = count(array_filter($goals, function($goal) {
            return $goal->getStatus() === 'completed';
        }));
        $pendingGoals = count(array_filter($goals, function($goal) {
            return $goal->getStatus() === 'PENDING';
        }));

        // Calculate average progress
        $totalProgress = array_reduce($goals, function($sum, $goal) {
            return $sum + ($goal->getProgress() ?? 0);
        }, 0);
        $avgProgress = $totalGoals > 0 ? round($totalProgress / $totalGoals) : 0;

        $stats = [
            'totalClients' => $totalGoals, // Using goals as proxy for clients
            'activePrograms' => $activeGoals,
            'pendingReviews' => $pendingGoals,
            'sessionsThisWeek' => 32, // Keep hardcoded for now
            'avgAdherenceRate' => $avgProgress,
            'newClientsThisMonth' => 5, // Keep hardcoded for now
        ];

        // Generate recent activity from goals
        $recentActivity = [];
        foreach (array_slice($goals, 0, 5) as $goal) {
            $activityType = match($goal->getStatus()) {
                'completed' => 'goal_achieved',
                'in progress' => 'workout_completed',
                default => 'program_assigned'
            };

            $icon = match($goal->getStatus()) {
                'completed' => 'fa-trophy',
                'in progress' => 'fa-dumbbell',
                default => 'fa-clipboard-list'
            };

            $color = match($goal->getStatus()) {
                'completed' => 'text-yellow-500',
                'in progress' => 'text-green-500',
                default => 'text-wellcare-500'
            };

            $recentActivity[] = [
                'type' => $activityType,
                'client' => 'Client ' . $goal->getId(), // Placeholder
                'message' => $goal->getTitle(),
                'time' => $goal->getDate()?->format('M j, Y') ?? 'Recently',
                'icon' => $icon,
                'color' => $color,
            ];
        }

        // Keep upcoming sessions hardcoded for now
        $upcomingSessions = [
            [
                'client' => 'Sarah Wilson',
                'time' => 'Today, 2:00 PM',
                'type' => 'Video Consultation',
                'avatar' => 'S',
            ],
            [
                'client' => 'Mike Johnson',
                'time' => 'Today, 4:00 PM',
                'type' => 'Form Review',
                'avatar' => 'M',
            ],
            [
                'client' => 'Emily Brown',
                'time' => 'Tomorrow, 10:00 AM',
                'type' => 'Progress Assessment',
                'avatar' => 'E',
            ],
        ];

        // Generate client progress from goals
        $clientProgress = [];
        foreach (array_slice($goals, 0, 3) as $goal) {
            $progress = $goal->getProgress() ?? 0;
            $status = match(true) {
                $progress >= 80 => 'excellent',
                $progress >= 50 => 'on_track',
                default => 'needs_attention'
            };

            $clientProgress[] = [
                'name' => 'Client ' . $goal->getId(), // Placeholder
                'avatar' => strtoupper(substr($goal->getTitle(), 0, 1)),
                'progress' => $progress,
                'goal' => $goal->getTitle(),
                'status' => $status,
            ];
        }

        return $this->render('coach/dashboard.html.twig', [
            'pageTitle' => 'Coach Dashboard - WellCare Connect',
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'upcomingSessions' => $upcomingSessions,
            'clientProgress' => $clientProgress,
        ]);
    }

    // Client Management
#[Route('/clients', name: 'coach_clients')]
public function clientManagement(UserRepository $userRepository , GoalRepository $goalRepository ): Response
{
    // Récupérer le coach connecté
    $coach = $this->getUser();
    
    if (!$coach instanceof Coach) {
        throw $this->createAccessDeniedException('Vous devez être un coach pour accéder à cette page.');
    }
    
    // Récupérer les patients de ce coach
    $clients = $userRepository->findPatientsByCoach($coach);
    
    // Récupérer UNIQUEMENT les goals du coach connecté
    $coachGoals = $goalRepository->findBy(['coachId' => $coach->getUuid()]);
    
    // Organiser les goals par client pour faciliter l'affichage
    $goalsByClient = [];
    foreach ($coachGoals as $goal) {
        $patientId = $goal->getPatient()->getUuid(); // ou getId() selon votre entité
        if (!isset($goalsByClient[$patientId])) {
            $goalsByClient[$patientId] = [];
        }
        $goalsByClient[$patientId][] = $goal;
    }

    return $this->render('coach/client-management.html.twig', [
        'pageTitle' => 'Client Management - WellCare Connect',
        'clients' => $clients,
        'coachGoals' => $coachGoals,
        'goalsByClient' => $goalsByClient, // Ajoutez cette variable
    ]);
}
    // Client Detail Page
    #[Route('/clients/{id}', name: 'coach_client_detail')]
    public function clientDetail(int $id): Response
    {
        $client = [
            'id' => $id,
            'name' => 'John Doe',
            'avatar' => 'J',
            'email' => 'john.doe@email.com',
            'phone' => '+1 234 567 890',
            'status' => 'active',
            'goals' => ['Marathon Training', 'Weight Management'],
            'medicalNotes' => 'No limitations',
            'startDate' => 'Jan 15, 2024',
            'age' => 32,
            'weight' => '75 kg',
            'height' => '178 cm',
            'fitnessLevel' => 'Intermediate',
        ];

        $workoutHistory = [
            [
                'date' => 'Today',
                'workout' => 'Morning Run',
                'duration' => '45 min',
                'calories' => 420,
                'status' => 'completed',
            ],
            [
                'date' => 'Yesterday',
                'workout' => 'Upper Body Strength',
                'duration' => '60 min',
                'calories' => 380,
                'status' => 'completed',
            ],
            [
                'date' => '2 days ago',
                'workout' => 'Rest Day',
                'duration' => '-',
                'calories' => 0,
                'status' => 'rest',
            ],
        ];

        return $this->render('coach/client-detail.html.twig', [
            'pageTitle' => 'Client Detail - WellCare Connect',
            'client' => $client,
            'workoutHistory' => $workoutHistory,
            
        ]);
    }

    // Program Designer
    #[Route('/programs', name: 'coach_programs')]
    public function programDesigner(): Response
    {
        $templates = [
            [
                'id' => 1,
                'name' => '12-Week Weight Loss Program',
                'description' => 'Comprehensive weight loss program combining cardio and strength training',
                'duration' => '12 weeks',
                'difficulty' => 'Intermediate',
                'clientsUsing' => 8,
                'rating' => 4.8,
                'image' => 'fa-weight-loss',
                'color' => 'text-red-500',
            ],
            [
                'id' => 2,
                'name' => 'Marathon Training Plan',
                'description' => 'Progressive running program for marathon preparation',
                'duration' => '16 weeks',
                'difficulty' => 'Advanced',
                'clientsUsing' => 5,
                'rating' => 4.9,
                'image' => 'fa-running',
                'color' => 'text-blue-500',
            ],
            [
                'id' => 3,
                'name' => 'Strength Fundamentals',
                'description' => 'Beginner-friendly strength training program',
                'duration' => '8 weeks',
                'difficulty' => 'Beginner',
                'clientsUsing' => 12,
                'rating' => 4.7,
                'image' => 'fa-dumbbell',
                'color' => 'text-green-500',
            ],
            [
                'id' => 4,
                'name' => 'Mobility & Flexibility',
                'description' => 'Improve range of motion and reduce injury risk',
                'duration' => '6 weeks',
                'difficulty' => 'All Levels',
                'clientsUsing' => 6,
                'rating' => 4.6,
                'image' => 'fa-yoga',
                'color' => 'text-purple-500',
            ],
        ];

        $exercises = [
            [
                'name' => 'Squats',
                'category' => 'Strength',
                'difficulty' => 'Intermediate',
                'equipment' => 'Bodyweight, Barbell',
                'muscles' => ['Quadriceps', 'Glutes', 'Hamstrings'],
                'video' => true,
            ],
            [
                'name' => 'Push-ups',
                'category' => 'Strength',
                'difficulty' => 'Beginner',
                'equipment' => 'Bodyweight',
                'muscles' => ['Chest', 'Triceps', 'Shoulders'],
                'video' => true,
            ],
            [
                'name' => 'Lunges',
                'category' => 'Strength',
                'difficulty' => 'Beginner',
                'equipment' => 'Bodyweight, Dumbbells',
                'muscles' => ['Quadriceps', 'Glutes', 'Hamstrings'],
                'video' => true,
            ],
            [
                'name' => 'Deadlifts',
                'category' => 'Strength',
                'difficulty' => 'Advanced',
                'equipment' => 'Barbell, Dumbbells',
                'muscles' => ['Back', 'Glutes', 'Hamstrings'],
                'video' => true,
            ],
            [
                'name' => 'Planks',
                'category' => 'Core',
                'difficulty' => 'Beginner',
                'equipment' => 'Bodyweight',
                'muscles' => ['Core', 'Shoulders'],
                'video' => true,
            ],
            [
                'name' => 'Burpees',
                'category' => 'Cardio',
                'difficulty' => 'Advanced',
                'equipment' => 'Bodyweight',
                'muscles' => ['Full Body'],
                'video' => true,
            ],
        ];

        return $this->render('coach/program-designer.html.twig', [
            'pageTitle' => 'Program Designer - WellCare Connect',
            'templates' => $templates,
            'exercises' => $exercises,
        ]);
    }

    // Progress Monitoring
    #[Route('/progress', name: 'coach_progress')]
    public function progressMonitoring(): Response
    {
        $clients = [
            [
                'name' => 'John Doe',
                'avatar' => 'J',
                'goal' => 'Marathon Training',
                'progress' => 75,
                'adherenceRate' => 92,
                'trend' => 'up',
                'nextMilestone' => 'Complete 15K run',
                'daysRemaining' => 14,
            ],
            [
                'name' => 'Sarah Wilson',
                'avatar' => 'S',
                'goal' => 'Weight Loss',
                'progress' => 45,
                'adherenceRate' => 78,
                'trend' => 'stable',
                'nextMilestone' => 'Lose 2 more kg',
                'daysRemaining' => 30,
            ],
            [
                'name' => 'Mike Johnson',
                'avatar' => 'M',
                'goal' => 'Strength Building',
                'progress' => 90,
                'adherenceRate' => 95,
                'trend' => 'up',
                'nextMilestone' => 'Increase bench press by 10kg',
                'daysRemaining' => 7,
            ],
        ];

        $analytics = [
            'totalWorkoutsCompleted' => 456,
            'totalHoursTrained' => 380,
            'avgSessionDuration' => '52 min',
            'caloriesBurned' => 45600,
            'improvementRate' => 23,
            'injuryRate' => 2,
        ];

        return $this->render('coach/progress-monitoring.html.twig', [
            'pageTitle' => 'Progress Monitoring - WellCare Connect',
            'clients' => $clients,
            'analytics' => $analytics,
        ]);
    }

    

    // Reporting Tools
    #[Route('/reports', name: 'coach_reports')]
    public function reportingTools(): Response
    {
        $reports = [
            [
                'id' => 1,
                'name' => 'Monthly Progress Report - John Doe',
                'type' => 'Individual',
                'createdAt' => 'Feb 5, 2024',
                'status' => 'ready',
            ],
            [
                'id' => 2,
                'name' => 'Q1 Client Overview',
                'type' => 'Aggregate',
                'createdAt' => 'Feb 1, 2024',
                'status' => 'ready',
            ],
            [
                'id' => 3,
                'name' => 'Program Effectiveness Analysis',
                'type' => 'Analytics',
                'createdAt' => 'Jan 28, 2024',
                'status' => 'ready',
            ],
            [
                'id' => 4,
                'name' => 'Client Retention Report',
                'type' => 'Business',
                'createdAt' => 'Jan 15, 2024',
                'status' => 'ready',
            ],
        ];

        $clients = [
            ['id' => 1, 'name' => 'John Doe'],
            ['id' => 2, 'name' => 'Jane Smith'],
            ['id' => 3, 'name' => 'Mike Johnson'],
            ['id' => 4, 'name' => 'Emily Brown'],
        ];

        $metrics = [
            ['id' => 'weight', 'name' => 'Weight'],
            ['id' => 'bodyFat', 'name' => 'Body Fat %'],
            ['id' => 'muscleMass', 'name' => 'Muscle Mass'],
            ['id' => 'vo2Max', 'name' => 'VO2 Max'],
            ['id' => 'restingHR', 'name' => 'Resting Heart Rate'],
            ['id' => 'flexibility', 'name' => 'Flexibility'],
        ];

        return $this->render('coach/reporting-tools.html.twig', [
            'pageTitle' => 'Reporting Tools - WellCare Connect',
            'reports' => $reports,
            'recentReports' => $reports,  // Same as reports for now
            'clients' => $clients,
            'stats' => [
                'activeClients' => 24,
                'completedReports' => 47,
                'pendingReports' => 5,
                'totalClients' => 28,
                'newClientsThisMonth' => 3,
                'sessionsThisMonth' => 156,
                'completionRate' => 94,
                'avgAdherence' => 87,
                'goalsAchieved' => 156,
                'goalsInProgress' => 23,
            ],
            'metrics' => $metrics,
        ]);
    }
}
