<?php
namespace App\Controller;

use App\Entity\Goal;
use App\Form\GoalType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\DailyPlan;
use App\Repository\DailyPlanRepository;
use App\Entity\Exercises;
use App\Repository\GoalRepository;  
use App\Repository\ExercisesRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

class FitnessController extends AbstractController
{
    // Remove ANY constructor if you have one
    // Or make sure it calls parent::__construct()
    
   
    /**
     * Main Fitness Dashboard
     */
   #[Route('/fitness/dashboard', name: 'fitness_dashboard')]
public function index(

    GoalRepository $goalRepository,
    DailyPlanRepository $dailyPlanRepository,
    ExercisesRepository $exercisesRepository
): Response
{
    // 1. Objectifs actifs
    $activeGoals = $goalRepository->findBy(
        ['status' => 'in progress'], // Critères
        ['id' => 'DESC'],
        5 // Limite à 5 objectifs
    );
    
   // 1. Date d'aujourd'hui - CORRECTION ICI
    $currentDate = new \DateTime();  // Renommer la variable pour plus de clarté
    $currentDate->setTime(0, 0, 0);  // Important : mettre l'heure à minuit pour la comparaison
    
    // Utiliser une variable plus descriptive pour la comparaison
    $todayDate = $currentDate->format('Y-m-d');

    $allPlans = $dailyPlanRepository->findAll();
    
    $todayPlan = null;
    foreach ($allPlans as $plan) {
        $planDate = $plan->getDate();
        if ($planDate && $planDate->format('Y-m-d') === $todayDate) {
            $todayPlan = $plan;
            break;
        }
    }
   $startOfWeek = (clone $currentDate)->modify('monday this week')->setTime(0, 0, 0);
    $endOfWeek = (clone $startOfWeek)->modify('+6 days')->setTime(23, 59, 59);
    
    $weeklyPlans = $dailyPlanRepository->createQueryBuilder('p')
        ->where('p.date BETWEEN :start AND :end')
        ->setParameter('start', $startOfWeek)
        ->setParameter('end', $endOfWeek)
        ->orderBy('p.date', 'ASC')
        ->getQuery()
        ->getResult();
    
    // 4. Exercices récents - CORRIGER CETTE LIGNE
    $recentExercises = [];
    // ERREUR ICI: findBy attend d'abord les critères, puis l'ordre
    // $recentPlans = $dailyPlanRepository->findBy([], ['date' => 'DESC'], 10);
    
    // CORRECTION:
    $recentPlans = $dailyPlanRepository->findBy(
        [], // Critères (vide = tous)
        ['date' => 'DESC'], // Ordre
        10  // Limite
    );
    
    foreach ($recentPlans as $plan) {
        foreach ($plan->getExercices() as $exercise) {
            if (!in_array($exercise->getId(), array_column($recentExercises, 'id'))) {
                $recentExercises[] = [
                    'id' => $exercise->getId(),
                    'name' => $exercise->getName(),
                    'category' => $exercise->getCategory(),
                    'duration' => $exercise->getDuration() ?? 0,
                    'calories' => $exercise->getCalories() ?? 0,
                    'sets' => $exercise->getSets() ?? 0,
                    'reps' => $exercise->getReps() ?? 0,
                    'description' => $exercise->getDescription() ?? '',
                    'difficulty_level' => $exercise->getDifficultyLevel() ?? 'medium',
                    'videoUrl' => $exercise->getVideoUrl() ?? '',
                    'videoFileName' => $exercise->getVideoFileName() ?? ''
                ];
            }
            if (count($recentExercises) >= 10) break;
        }
        if (count($recentExercises) >= 10) break;
    }
    
    // 5. Catégories populaires
    $allExercises = $exercisesRepository->findBy(['isActive' => true]);
    $categoryCounts = [];
    foreach ($allExercises as $exercise) {
        $category = $exercise->getCategory();
        if ($category) {
            $categoryCounts[$category] = ($categoryCounts[$category] ?? 0) + 1;
        }
    }
    arsort($categoryCounts);
    $popularCategories = [];
    foreach (array_slice($categoryCounts, 0, 6) as $category => $count) {
        $popularCategories[] = ['name' => $category, 'count' => $count];
    }
    
    // 6. Statistiques mensuelles
    $startOfMonth = (clone $currentDate)->modify('first day of this month')->setTime(0, 0, 0);
    $endOfMonth = (clone $currentDate)->modify('last day of this month')->setTime(23, 59, 59);
    
    $monthlyPlans = $dailyPlanRepository->createQueryBuilder('p')
        ->where('p.date BETWEEN :start AND :end')
        ->setParameter('start', $startOfMonth)
        ->setParameter('end', $endOfMonth)
        ->getQuery()
        ->getResult();
    
    $monthlyStats = [
        'plans' => count($monthlyPlans),
        'minutes' => array_sum(array_map(fn($p) => $p->getDureeMin() ?? 0, $monthlyPlans)),
        'exercises' => array_sum(array_map(fn($p) => $p->getExercices()->count(), $monthlyPlans)),
        'completedPlans' => count(array_filter($monthlyPlans, fn($p) => $p->getStatus() == 'completed')),
    ];
    $monthlyStats['completionRate'] = $monthlyStats['plans'] > 0 
        ? round(($monthlyStats['completedPlans'] / $monthlyStats['plans']) * 100) 
        : 0;
    
    // 7. Formater les objectifs
    $formattedGoals = [];
    foreach ($activeGoals as $goal) {
        $formattedGoals[] = [
            'id' => $goal->getId(),
            'title' => $goal->getTitle(),
            'description' => $goal->getDescription() ?? '',
            'progress' => $goal->getProgress() ?? 0,
            'dailyPlans' => method_exists($goal, 'getDailyPlans') ? $goal->getDailyPlan()->toArray() : []
        ];
    }
    
    // 8. Formater le plan d'aujourd'hui
    $formattedTodayPlan = null;
    if ($todayPlan) {
        $formattedTodayPlan = [
            'id' => $todayPlan->getId(),
            'titre' => $todayPlan->getTitre() ?? 'Sans titre',
            'date' => $todayPlan->getDate(),
            'status' => $todayPlan->getStatus() ?? 'planned',
            'dureeMin' => $todayPlan->getDureeMin() ?? 0,
            'calories' => $todayPlan->getCalories() ?? 0,
            'notes' => $todayPlan->getNotes() ?? '',
            'exercices' => []
        ];
        
        foreach ($todayPlan->getExercices() as $exercise) {
            $formattedTodayPlan['exercices'][] = [
                'id' => $exercise->getId(),
                'name' => $exercise->getName(),
                'category' => $exercise->getCategory(),
                'duration' => $exercise->getDuration() ?? 0,
                'calories' => $exercise->getCalories() ?? 0,
                'sets' => $exercise->getSets() ?? 0,
                'reps' => $exercise->getReps() ?? 0,
                'description' => $exercise->getDescription() ?? '',
                'difficulty_level' => $exercise->getDifficultyLevel() ?? 'medium',
                'videoUrl' => $exercise->getVideoUrl() ?? '',
                'videoFileName' => $exercise->getVideoFileName() ?? ''
            ];
        }
    }
    
    // 9. Formater les plans de la semaine
    $formattedWeeklyPlans = [];
    foreach ($weeklyPlans as $plan) {
        $formattedWeeklyPlans[] = [
            'id' => $plan->getId(),
            'titre' => $plan->getTitre() ?? 'Sans titre',
            'date' => $plan->getDate(),
            'status' => $plan->getStatus() ?? 'planned',
            'dureeMin' => $plan->getDureeMin() ?? 0,
            'calories' => $plan->getCalories() ?? 0,
            'notes' => $plan->getNotes() ?? '',
            'exercices' => $plan->getExercices()->toArray()
        ];
    }
     $formattedCurrentDate = $currentDate->format('l, F j, Y');
    
    return $this->render('fitness/patient-dashboard.html.twig', [
        'activeGoals' => $formattedGoals,
        'todayPlan' => $formattedTodayPlan,
        'weeklyPlans' => $formattedWeeklyPlans,
        'recentExercises' => $recentExercises,
        'popularCategories' => $popularCategories,
        'monthlyStats' => $monthlyStats,
        'currentDate' => $formattedCurrentDate,
        
    ]);
}
    
    /**
     * Workout Planner
     */
   #[Route('/fitness/planner', name: 'fitness_planner')]
    public function workoutPlanner(DailyPlanRepository $dailyPlanRepository): Response
{
    // Récupérer les daily plans de l'utilisateur courant
    $dailyPlans = $dailyPlanRepository->findBy(
        [], // Vous pouvez filtrer par utilisateur ici
        ['date' => 'ASC']
    );
    
    // Formater les daily plans pour le calendrier
    $formattedPlans = [];
    foreach ($dailyPlans as $plan) {
        $formattedPlans[] = [
            'id' => $plan->getId(),
            'title' => $plan->getTitre(),
            'date' => $plan->getDate()->format('Y-m-d'),
            'dateDisplay' => $plan->getDate()->format('d'),
            'type' => $this->determineWorkoutType($plan), // Méthode à créer
            'status' => $plan->getStatus(),
            'duration' => $plan->getDureeMin(),
            'calories' => $plan->getCalories(),
            'notes' => $plan->getNotes(),
            'exercises' => $plan->getExercices()->count(),
            'isRestDay' => $plan->getExercices()->count() === 0,
        ];
    }
    
    // Calculer les stats hebdomadaires
    $weeklyStats = $this->calculateWeeklyStats($dailyPlans); // Méthode à créer
    
    return $this->render('fitness/workout-planner.html.twig', [
        'pageTitle' => 'Workout Planner',
        'dailyPlans' => $formattedPlans,
        'weeklyStats' => $weeklyStats,
    ]);
}

private function determineWorkoutType(DailyPlan $plan): string
{
    // Logique pour déterminer le type d'entraînement
    $exerciseCount = $plan->getExercices()->count();
    
    if ($exerciseCount === 0) {
        return 'rest';
    }
    
    // Vous pouvez ajouter votre propre logique ici
    // Par exemple, basé sur les catégories d'exercices
    return 'strength'; // Valeur par défaut
}

private function calculateWeeklyStats(array $dailyPlans): array
{
    $today = new \DateTime();
    $startOfWeek = clone $today;
    $startOfWeek->modify('Monday this week');
    $endOfWeek = clone $startOfWeek;
    $endOfWeek->modify('+6 days');
    
    $stats = [
        'scheduled' => 0,
        'completed' => 0,
        'minutes' => 0,
        'restDays' => 0,
        'calories' => 0
    ];
    
    foreach ($dailyPlans as $plan) {
        $planDate = $plan->getDate();
        
        // Vérifier si le plan est dans la semaine en cours
        if ($planDate >= $startOfWeek && $planDate <= $endOfWeek) {
            if ($plan->getExercices()->count() === 0) {
                $stats['restDays']++;
            } else {
                $stats['scheduled']++;
                
                if ($plan->getStatus() === 'completed') {
                    $stats['completed']++;
                }
                
                $stats['minutes'] += $plan->getDureeMin() ?? 0;
                $stats['calories'] += $plan->getCalories() ?? 0;
            }
        }
    }
    
    return $stats;
}

    /**
     * Exercise Library
     */
    #[Route('/fitness/library', name: 'fitness_library')]
    public function show(ManagerRegistry $m): Response
    {
        $em = $m->getManager();
        $exerciseRepository = $em->getRepository(Exercises::class);
        $exercises = $exerciseRepository->findAll();
        
        // Convertir les objets en tableau pour Alpine.js
        $exercisesArray = [];
        foreach ($exercises as $exercise) {
            $exercisesArray[] = [
                'id' => $exercise->getId(),
                'name' => $exercise->getName(),
                'description' => $exercise->getDescription(),
                'category' => $exercise->getCategory(),
                'difficulty_level' => $exercise->getDifficultyLevel(),
                'defaultUnit' => $exercise->getDefaultUnit(),
                'videoUrl' => $exercise->getVideoUrl(),
                'videoFileName' => $exercise->getVideoFileName(),  // <-- IMPORTANT : ajouté
                'isActive' => $exercise->isActive(),
                'createdAt' => $exercise->getCreatedAt() ? $exercise->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'duration' => $exercise->getDuration(),
                'calories' => $exercise->getCalories(),
                'sets' => $exercise->getSets(),
                'reps' => $exercise->getReps(),
            ];
        }
            
        return $this->render('fitness/exercise-library.html.twig', [
            'page_title' => 'Exercise Library',
            'exercises' => $exercisesArray,
        ]);  
    }
     
    
    

    /**
     * Workout Log
     */
    #[Route('/fitness/log', name: 'fitness_log')]
    public function log(): Response
    {
        return $this->render('fitness/workout-log.html.twig', [
            'page_title' => 'Workout Log',
            'recentWorkouts' => [
                ['date' => 'Feb 5, 2024', 'type' => 'Upper Body', 'duration' => 48, 'calories' => 320, 'exercises' => 6],
                ['date' => 'Feb 3, 2024', 'type' => 'Cardio', 'duration' => 35, 'calories' => 380, 'exercises' => 5],
                ['date' => 'Feb 1, 2024', 'type' => 'Lower Body', 'duration' => 52, 'calories' => 350, 'exercises' => 7],
                ['date' => 'Jan 30, 2024', 'type' => 'HIIT', 'duration' => 28, 'calories' => 290, 'exercises' => 8],
            ],
        ]);
    }

    /**
     * Performance Analytics
     */
    #[Route('/fitness/analytics', name: 'fitness_analytics')]
    public function analytics(): Response
    {
        return $this->render('fitness/performance-analytics.html.twig', [
            'page_title' => 'Performance Analytics',
            'strengthProgress' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'benchPress' => [60, 65, 70, 72, 75, 80],
                'squat' => [80, 85, 90, 95, 100, 105],
                'deadlift' => [100, 110, 115, 120, 125, 130],
            ],
            'enduranceMetrics' => [
                'vo2Max' => ['value' => 42, 'change' => '+8%'],
                'pace' => ['value' => '5:30/km', 'change' => '-12%'],
                'distance' => ['value' => '8.5km', 'change' => '+25%'],
            ],
            'recoveryData' => [
                'averageRestingHR' => 62,
                'hrv' => 45,
                'sleepQuality' => 85,
                'recoveryScore' => 78,
            ],
        ]);
    }

    /**
     * Coach Communication
     */
    #[Route('/fitness/coach', name: 'fitness_coach')]
    public function coach(): Response
    {
        return $this->render('fitness/coach-communication.html.twig', [
            'page_title' => 'Coach Communication',
            'coach' => [
                'name' => 'Coach Sarah',
                'specialty' => 'Strength & Conditioning',
                'avatar' => '👩‍🏫',
            ],
            'messages' => [
                ['sender' => 'coach', 'text' => 'Great progress on your bench press! Keep maintaining good form.', 'time' => '2 hours ago'],
                ['sender' => 'user', 'text' => 'Thanks! I\'ll focus on form more.', 'time' => '1 hour ago'],
                ['sender' => 'coach', 'text' => 'Remember to prioritize sleep for optimal recovery.', 'time' => '30 mins ago'],
            ],
            'recentFeedback' => [
                'type' => 'form_correction',
                'title' => 'Bench Press Form',
                'feedback' => 'Keep your elbows at 45 degrees to protect your shoulders.',
                'date' => 'Today',
            ],
            'conversations' => [
                ['id' => 1, 'name' => 'Coach Sarah', 'avatar' => '👩‍🏫', 'lastMessage' => 'Great progress!', 'time' => '2h ago', 'unread' => true, 'online' => true],
                ['id' => 2, 'name' => 'Coach Mike', 'avatar' => '👨‍🏫', 'lastMessage' => 'Keep up the work!', 'time' => '1d ago', 'unread' => false, 'online' => false],
                ['id' => 3, 'name' => 'Coach Emma', 'avatar' => '👩‍⚕️', 'lastMessage' => 'Your form is improving', 'time' => '3d ago', 'unread' => false, 'online' => false],
            ],
        ]);
    }

    /**
     * SMART Goals Dashboard
     */
    
    #[Route('/fitness/goals', name: 'fitness_goals')]
    public function goals(): Response
    {
        return $this->render('fitness/goal-wizard.html.twig', [
            'page_title' => 'SMART Goals',
            'goals' => [
                ['id' => 1, 'title' => 'Run a Half Marathon', 'status' => 'active', 'progress' => 65, 'category' => 'Endurance'],
                ['id' => 2, 'title' => 'Lose 10kg', 'status' => 'active', 'progress' => 40, 'category' => 'Weight Loss'],
                ['id' => 3, 'title' => 'Do 20 Pull-ups', 'status' => 'paused', 'progress' => 75, 'category' => 'Strength'],
            ],
        ]);
    }

    /**
     * SMART Goal Wizard - Create New Goal
     */
    #[Route('/fitness/goals/wizard', name: 'fitness_goal_wizard')]
    public function goalWizard(): Response
    {
        return $this->render('fitness/goal-wizard.html.twig', [
            'page_title' => 'Create SMART Goal',
        ]);
    }

    /**
     * SMART Goal Editor
     */
    #[Route('/fitness/goals/editor', name: 'fitness_goal_editor')]
    public function goalEditor(): Response
    {
        return $this->render('fitness/smart-goal-editor.html.twig', [
            'page_title' => 'Edit SMART Goals',
            'goals' => [
                ['id' => 1, 'title' => 'Run a Half Marathon', 'status' => 'active', 'statusLabel' => 'Active', 'progress' => 65],
                ['id' => 2, 'title' => 'Lose 10kg', 'status' => 'active', 'statusLabel' => 'Active', 'progress' => 40],
                ['id' => 3, 'title' => 'Do 20 Pull-ups', 'status' => 'paused', 'statusLabel' => 'Paused', 'progress' => 75],
            ],
            'goalTimeline' => [
                ['action' => 'Goal created', 'date' => 'Jan 15, 2024'],
                ['action' => 'Milestone completed: Run 10km', 'date' => 'Feb 15, 2024'],
                ['action' => 'Progress update: 50% complete', 'date' => 'Mar 1, 2024'],
            ],
            'milestones' => [
                ['id' => 1, 'title' => 'Run 10km continuously', 'targetDate' => 'Feb 15', 'value' => '10km', 'completed' => true],
                ['id' => 2, 'title' => 'Complete 15km run', 'targetDate' => 'Mar 1', 'value' => '15km', 'completed' => true],
                ['id' => 3, 'title' => 'Run 18km at race pace', 'targetDate' => 'Mar 15', 'value' => '18km', 'completed' => true],
                ['id' => 4, 'title' => 'Complete half marathon', 'targetDate' => 'Apr 1', 'value' => '21.1km', 'completed' => false],
            ],
        ]);
    }

    /**
     * Milestone Tracker
     */
    #[Route('/fitness/milestones', name: 'fitness_milestones')]
public function milestones(GoalRepository $goalRepository, DailyPlanRepository $dailyPlanRepository): Response
{
    $user = $this->getUser(); // Récupérer l'utilisateur connecté
    
    // Récupérer tous les goals de l'utilisateur
    $goals = $goalRepository->findBy(['user' => $user]); // Assurez-vous que votre entité Goal a une relation avec User
    
    // Récupérer tous les daily plans de l'utilisateur
    $dailyPlans = $dailyPlanRepository->findAll(); // Vous devriez filtrer par utilisateur
    
    // Calculer les statistiques basées sur les données réelles
    $completedGoals = 0;
    $activeGoals = 0;
    $totalWorkouts = 0;
    $workoutStreak = 0;
    $monthlyWorkouts = 0;
    $totalCaloriesBurned = 0;
    $totalWorkoutMinutes = 0;
    
    foreach ($goals as $goal) {
        if ($goal->getStatus() === 'completed') {
            $completedGoals++;
        } elseif ($goal->getStatus() === 'in progress') {
            $activeGoals++;
        }
    }
    
    // Analyser les DailyPlans pour les statistiques
    $currentMonth = date('m');
    $currentYear = date('Y');
    $lastWorkoutDate = null;
    $currentStreak = 0;
    $datesWithWorkouts = [];
    
    foreach ($dailyPlans as $plan) {
        if ($plan->getDate() && $plan->getStatus() === 'completed') {
            $totalWorkouts++;
            $totalCaloriesBurned += $plan->getCalories();
            $totalWorkoutMinutes += $plan->getDureeMin();
            
            // Vérifier si c'est dans le mois courant
            if ($plan->getDate()->format('m') == $currentMonth && 
                $plan->getDate()->format('Y') == $currentYear) {
                $monthlyWorkouts++;
            }
            
            // Garder trace des dates pour calculer la série
            $datesWithWorkouts[$plan->getDate()->format('Y-m-d')] = true;
        }
    }
    
    // Calculer la série d'entraînements (streak)
    $today = new \DateTime();
    $yesterday = clone $today;
    $yesterday->modify('-1 day');
    
    // Parcourir les jours récents pour trouver la série
    for ($i = 0; $i < 30; $i++) { // Vérifier jusqu'à 30 jours
        $checkDate = clone $today;
        $checkDate->modify("-$i days");
        $dateStr = $checkDate->format('Y-m-d');
        
        if (isset($datesWithWorkouts[$dateStr])) {
            $workoutStreak++;
        } else {
            break; // Série brisée
        }
    }
    
    // Calculer les points totaux (basé sur les entraînements complétés)
    $totalPoints = $totalWorkouts * 50; // 50 points par entraînement
    
    // Calculer le niveau de l'utilisateur basé sur le nombre d'entraînements
    $userLevel = 'beginner';
    if ($totalWorkouts >= 100) {
        $userLevel = 'expert';
    } elseif ($totalWorkouts >= 50) {
        $userLevel = 'advanced';
    } elseif ($totalWorkouts >= 20) {
        $userLevel = 'intermediate';
    }
    
    // Calculer la progression pour chaque niveau
    $beginnerProgress = min(100, ($totalWorkouts / 5) * 100);
    $intermediateProgress = min(100, ($totalWorkouts / 20) * 100);
    $advancedProgress = min(100, ($totalWorkouts / 50) * 100);
    $expertProgress = min(100, ($totalWorkouts / 100) * 100);
    
    // Badges basés sur les accomplissements réels
    $badges = [];
    $badgesEarned = 0;
    
    // Badge: Premier entraînement
    if ($totalWorkouts >= 1) {
        $badges[] = ['name' => 'First Workout', 'icon' => '🎯', 'earned' => true];
        $badgesEarned++;
    }
    
    // Badge: Série de 7 jours
    if ($workoutStreak >= 7) {
        $badges[] = ['name' => '7 Day Streak', 'icon' => '🔥', 'earned' => true];
        $badgesEarned++;
    } else {
        $badges[] = ['name' => '7 Day Streak', 'icon' => '🔥', 'earned' => false];
    }
    
    // Badge: Force (si entraînements de force dans les DailyPlans)
    $strengthWorkouts = 0;
    foreach ($dailyPlans as $plan) {
        if (stripos($plan->getTitre(), 'strength') !== false || 
            stripos($plan->getTitre(), 'weight') !== false) {
            $strengthWorkouts++;
        }
    }
    
    if ($strengthWorkouts >= 10) {
        $badges[] = ['name' => 'Strength Master', 'icon' => '💪', 'earned' => true];
        $badgesEarned++;
    } else {
        $badges[] = ['name' => 'Strength Master', 'icon' => '💪', 'earned' => false];
    }
    
    // Badge: Cardio (si courses dans les DailyPlans)
    $runningWorkouts = 0;
    foreach ($dailyPlans as $plan) {
        if (stripos($plan->getTitre(), 'run') !== false || 
            stripos($plan->getTitre(), 'cardio') !== false) {
            $runningWorkouts++;
        }
    }
    
    if ($runningWorkouts >= 5) {
        $badges[] = ['name' => 'Marathon Ready', 'icon' => '🏃', 'earned' => true];
        $badgesEarned++;
    } else {
        $badges[] = ['name' => 'Marathon Ready', 'icon' => '🏃', 'earned' => false];
    }
    
    // Badge: Yoga (si yoga dans les DailyPlans)
    $yogaWorkouts = 0;
    foreach ($dailyPlans as $plan) {
        if (stripos($plan->getTitre(), 'yoga') !== false || 
            stripos($plan->getTitre(), 'stretch') !== false) {
            $yogaWorkouts++;
        }
    }
    
    if ($yogaWorkouts >= 3) {
        $badges[] = ['name' => 'Yoga Pro', 'icon' => '🧘', 'earned' => true];
        $badgesEarned++;
    } else {
        $badges[] = ['name' => 'Yoga Pro', 'icon' => '🧘', 'earned' => false];
    }
    
    // Badge: Début rapide (si 3 entraînements en 3 jours)
    $quickStart = false;
    if ($totalWorkouts >= 3) {
        // Vérifier si les 3 premiers entraînements étaient dans les 3 premiers jours
        $quickStart = true; // Simplifié pour l'exemple
    }
    $badges[] = ['name' => 'Quick Starter', 'icon' => '⚡', 'earned' => $quickStart];
    if ($quickStart) $badgesEarned++;
    
    // Défis mensuels basés sur les données réelles
    $monthlyChallenges = [
        [
            'icon' => '🏃', 
            'title' => 'Monthly Distance', 
            'description' => 'Run 100km this month', 
            'current' => $runningWorkouts * 5, // Estimation: 5km par course
            'target' => 100, 
            'progress' => min(100, ($runningWorkouts * 5 / 100) * 100), 
            'completed' => ($runningWorkouts * 5) >= 100
        ],
        [
            'icon' => '💪', 
            'title' => 'Strength Goal', 
            'description' => 'Complete 15 strength sessions', 
            'current' => $strengthWorkouts, 
            'target' => 15, 
            'progress' => min(100, ($strengthWorkouts / 15) * 100), 
            'completed' => $strengthWorkouts >= 15
        ],
        [
            'icon' => '🔥', 
            'title' => 'Calories Burned', 
            'description' => 'Burn 5000 calories', 
            'current' => $totalCaloriesBurned, 
            'target' => 5000, 
            'progress' => min(100, ($totalCaloriesBurned / 5000) * 100), 
            'completed' => $totalCaloriesBurned >= 5000
        ],
    ];
    
    // Objectifs hebdomadaires basés sur les données réelles
    $currentWeekNumber = date('W');
    $weeklyGoals = [];
    
    // Objectif: Entraînements cette semaine
    $weeklyWorkouts = 0;
    $weeklyCalories = 0;
    $weeklyMinutes = 0;
    
    foreach ($dailyPlans as $plan) {
        if ($plan->getDate() && $plan->getStatus() === 'completed') {
            $planWeek = $plan->getDate()->format('W');
            if ($planWeek == $currentWeekNumber) {
                $weeklyWorkouts++;
                $weeklyCalories += $plan->getCalories();
                $weeklyMinutes += $plan->getDureeMin();
            }
        }
    }
    
    $weeklyGoals[] = [
        'icon' => '🏋️', 
        'title' => 'Complete 3 Workouts', 
        'description' => 'Finish at least 3 workouts this week', 
        'current' => $weeklyWorkouts, 
        'target' => 3, 
        'progress' => min(100, ($weeklyWorkouts / 3) * 100), 
        'points' => 50, 
        'completed' => $weeklyWorkouts >= 3
    ];
    
    $weeklyGoals[] = [
        'icon' => '🔥', 
        'title' => 'Burn 2000 Calories', 
        'description' => 'Burn 2000 calories through exercise', 
        'current' => $weeklyCalories, 
        'target' => 2000, 
        'progress' => min(100, ($weeklyCalories / 2000) * 100), 
        'points' => 75, 
        'completed' => $weeklyCalories >= 2000
    ];
    
    $weeklyGoals[] = [
        'icon' => '⏱️', 
        'title' => '300 Minutes Activity', 
        'description' => 'Reach 300 minutes of activity', 
        'current' => $weeklyMinutes, 
        'target' => 300, 
        'progress' => min(100, ($weeklyMinutes / 300) * 100), 
        'points' => 60, 
        'completed' => $weeklyMinutes >= 300
    ];
    
    // Calendrier d'entraînement pour le mois en cours
    $workoutCalendar = [];
    $monthStart = new \DateTime('first day of this month');
    $monthEnd = new \DateTime('last day of this month');
    
    for ($date = clone $monthStart; $date <= $monthEnd; $date->modify('+1 day')) {
        $dateStr = $date->format('Y-m-d');
        $dayShort = $date->format('D')[0]; // Premier caractère du jour
        
        // Vérifier s'il y a eu un entraînement ce jour
        $workoutLevel = 0;
        if (isset($datesWithWorkouts[$dateStr])) {
            // Déterminer l'intensité basée sur les calories brûlées
            foreach ($dailyPlans as $plan) {
                if ($plan->getDate()->format('Y-m-d') == $dateStr && $plan->getStatus() === 'completed') {
                    $calories = $plan->getCalories();
                    if ($calories >= 300) {
                        $workoutLevel = 4;
                    } elseif ($calories >= 200) {
                        $workoutLevel = 3;
                    } elseif ($calories >= 100) {
                        $workoutLevel = 2;
                    } else {
                        $workoutLevel = 1;
                    }
                    break;
                }
            }
        }
        
        $workoutCalendar[] = ['day' => $dayShort, 'level' => $workoutLevel];
    }
    
    return $this->render('fitness/milestone-tracker.html.twig', [
        'page_title' => 'Fitness Milestone Tracker',
        'totalPoints' => $totalPoints,
        'completedGoals' => $completedGoals,
        'activeGoals' => $activeGoals,
        'workoutStreak' => $workoutStreak,
        'totalWorkouts' => $totalWorkouts,
        'badgesEarned' => $badgesEarned,
        'currentWeek' => $currentWeekNumber,
        'monthName' => date('F'),
        'year' => date('Y'),
        'monthlyWorkouts' => $monthlyWorkouts,
        'userLevel' => $userLevel,
        'beginnerProgress' => $beginnerProgress,
        'intermediateProgress' => $intermediateProgress,
        'advancedProgress' => $advancedProgress,
        'expertProgress' => $expertProgress,
        'achievementLevels' => [
            [
                'name' => 'Beginner', 
                'icon' => '👶', 
                'requirement' => 'Complete 5 workouts', 
                'progress' => $beginnerProgress, 
                'unlocked' => $totalWorkouts >= 5
            ],
            [
                'name' => 'Intermediate', 
                'icon' => '💪', 
                'requirement' => 'Complete 20 workouts', 
                'progress' => $intermediateProgress, 
                'unlocked' => $totalWorkouts >= 20
            ],
            [
                'name' => 'Advanced', 
                'icon' => '🏆', 
                'requirement' => 'Complete 50 workouts', 
                'progress' => $advancedProgress, 
                'unlocked' => $totalWorkouts >= 50
            ],
            [
                'name' => 'Expert', 
                'icon' => '🏅', 
                'requirement' => 'Complete 100+ workouts', 
                'progress' => $expertProgress, 
                'unlocked' => $totalWorkouts >= 100
            ],
        ],
        'weeklyGoals' => $weeklyGoals,
        'badges' => $badges,
        'nextBadgeCurrent' => $strengthWorkouts, // Prochain badge basé sur les entraînements de force
        'nextBadgeTarget' => 10,
        'workoutCalendar' => $workoutCalendar,
        'monthlyChallenges' => $monthlyChallenges,
        'leaderboard' => [
            [
                'rank' => 1, 
                'avatar' => '👑', 
                'name' => 'Alex Johnson', 
                'achievement' => 'Top Performer', 
                'points' => 3450
            ],
            [
                'rank' => 2, 
                'avatar' => '💪', 
                'name' => 'Maria Garcia', 
                'achievement' => 'Strength Champion', 
                'points' => 2980
            ],
            [
                'rank' => 3, 
                'avatar' => '🏃', 
                'name' => 'David Smith', 
                'achievement' => 'Cardio King', 
                'points' => 2675
            ],
            [
                'rank' => 4, 
                'avatar' => '🥇', 
                'name' => 'You', 
                'achievement' => 'Consistency Master', 
                'points' => $totalPoints
            ],
            [
                'rank' => 5, 
                'avatar' => '⚡', 
                'name' => 'Sarah Lee', 
                'achievement' => 'Quick Starter', 
                'points' => 1980
            ],
        ],
    ]);
}
    /**
     * Progression System
     */
    #[Route('/fitness/progression', name: 'fitness_progression')]
    public function progression(): Response
    {
        return $this->render('fitness/progression-system.html.twig', [
            'page_title' => 'Adaptive Progression',
            'fitnessLevel' => 'Intermediate',
            'trainingLoad' => 'Moderate',
            'recoveryScore' => 85,
            'aiRecommendation' => 'Your performance data shows consistent improvement in cardiovascular metrics. Based on your recovery score and training load, I recommend maintaining current intensity for the next 3-4 days before gradually increasing workout volume.',
            'progressMetrics' => [
                ['name' => 'VO2 Max', 'icon' => 'M13 7h8m0 0V3m-4 5h4m4-2v5m-2 4h4m-6 4a2 2 0 01-2-2V5a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2h-2', 'bgColor' => 'bg-green-100 dark:bg-green-900/30', 'iconColor' => 'text-green-500', 'change' => 'vs. last month', 'current' => '42', 'improvement' => '+8%'],
                ['name' => 'Bench Press', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16', 'bgColor' => 'bg-blue-100 dark:bg-blue-900/30', 'iconColor' => 'text-blue-500', 'change' => 'vs. last month', 'current' => '85kg', 'improvement' => '+5%'],
                ['name' => 'Running Pace', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'bgColor' => 'bg-purple-100 dark:bg-purple-900/30', 'iconColor' => 'text-purple-500', 'change' => 'vs. last month', 'current' => '5:30/km', 'improvement' => '+12%'],
                ['name' => 'Flexibility', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'bgColor' => 'bg-amber-100 dark:bg-amber-900/30', 'iconColor' => 'text-amber-500', 'change' => 'vs. last month', 'current' => 'Good', 'improvement' => '+3%'],
            ],
            'recommendations' => [
                ['id' => 1, 'title' => 'Increase Running Distance', 'description' => 'Gradually increase weekly running distance by 10% to improve endurance', 'impact' => 'High Impact', 'timeframe' => 'This week', 'borderColor' => 'border-green-500', 'badgeColor' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400'],
                ['id' => 2, 'title' => 'Add HIIT Sessions', 'description' => 'Incorporate 1-2 high-intensity interval training sessions per week', 'impact' => 'Medium Impact', 'timeframe' => 'Next week', 'borderColor' => 'border-blue-500', 'badgeColor' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'],
                ['id' => 3, 'title' => 'Focus on Recovery', 'description' => 'Schedule a deload week after current training block', 'impact' => 'Recovery', 'timeframe' => 'In 2 weeks', 'borderColor' => 'border-amber-500', 'badgeColor' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400'],
            ],
            'weekSchedule' => [
                ['name' => 'Mon', 'activity' => 'Upper Body', 'isDeload' => false],
                ['name' => 'Tue', 'activity' => 'Cardio', 'isDeload' => false],
                ['name' => 'Wed', 'activity' => 'Lower Body', 'isDeload' => false],
                ['name' => 'Thu', 'activity' => 'HIIT', 'isDeload' => false],
                ['name' => 'Fri', 'activity' => 'Upper Body', 'isDeload' => false],
                ['name' => 'Sat', 'activity' => 'Active Rest', 'isDeload' => true],
                ['name' => 'Sun', 'activity' => 'Rest Day', 'isDeload' => true],
            ],
        ]);
    }

    /**
     * Adaptive Workouts
     */
    #[Route('/fitness/adaptive', name: 'fitness_adaptive')]
    public function adaptive(): Response
    {
        return $this->render('fitness/adaptive-workouts.html.twig', [
            'page_title' => 'Adaptive Workouts',
            'readinessScore' => 85,
            'lastWorkout' => 'Yesterday',
            'lastWorkoutType' => 'Upper Body Strength',
            'recoveryStatus' => 72,
            'suggestedFocus' => 'Lower Body',
            'aiRecommendation' => 'Based on your recovery data and upcoming schedule, I recommend a moderate-intensity strength workout focusing on lower body. Your upper body muscles are still recovering from yesterday\'s session. Feel free to add 1-2 extra sets if you\'re feeling energetic!',
            'workoutTypes' => [
                ['id' => 'strength', 'name' => 'Strength', 'icon' => '💪', 'duration' => '45-60 min'],
                ['id' => 'cardio', 'name' => 'Cardio', 'icon' => '🏃', 'duration' => '30-45 min'],
                ['id' => 'hiit', 'name' => 'HIIT', 'icon' => '🔥', 'duration' => '20-30 min'],
                ['id' => 'flexibility', 'name' => 'Flexibility', 'icon' => '🧘', 'duration' => '20-30 min'],
            ],
            'warmup' => [
                ['icon' => '🏃', 'name' => 'Light Jog', 'duration' => '3 min'],
                ['icon' => '🤸', 'name' => 'Dynamic Stretching', 'duration' => '5 min'],
                ['icon' => '⭕', 'name' => 'Arm Circles', 'duration' => '2 min'],
            ],
            'mainExercises' => [
                ['icon' => '🦵', 'name' => 'Barbell Squats', 'target' => 'Quadriceps, Glutes', 'sets' => 4, 'reps' => '8-10', 'rest' => '90s', 'rpe' => '7', 'adaptation' => ['type' => 'reduced', 'reason' => 'Recovery adaptation'], 'notes' => 'Reduced volume due to recent upper body training'],
                ['icon' => '🦿', 'name' => 'Romanian Deadlifts', 'target' => 'Hamstrings, Glutes', 'sets' => 3, 'reps' => '10-12', 'rest' => '75s', 'rpe' => '6', 'adaptation' => null, 'notes' => null],
                ['icon' => '🦵', 'name' => 'Leg Press', 'target' => 'Quadriceps', 'sets' => 3, 'reps' => '12-15', 'rest' => '60s', 'rpe' => '6', 'adaptation' => ['type' => 'increased', 'reason' => 'Progression'], 'notes' => null],
                ['icon' => '🏋️', 'name' => 'Calf Raises', 'target' => 'Calves', 'sets' => 4, 'reps' => '15-20', 'rest' => '45s', 'rpe' => '5', 'adaptation' => null, 'notes' => null],
            ],
            'cooldown' => [
                ['icon' => '🧘', 'name' => 'Static Stretching', 'duration' => '5 min'],
                ['icon' => '🫁', 'name' => 'Deep Breathing', 'duration' => '3 min'],
            ],
            'activeModifications' => [
                ['condition' => 'Upper Body Fatigue', 'modification' => 'Reduced upper body volume by 20%', 'status' => 'Active', 'severity' => 'warning'],
                ['condition' => 'Knee Sensitivity', 'modification' => 'Low impact alternatives for jumping exercises', 'status' => 'Applied', 'severity' => 'info'],
            ],
            'substitutions' => [
                ['original' => 'Jump Squats', 'replacement' => 'Box Squats', 'reason' => 'Reduced impact on knees'],
                ['original' => 'Burpees', 'replacement' => 'Mountain Climbers', 'reason' => 'Upper body fatigue modification'],
            ],
            'recoveryRecommendations' => [
                ['icon' => '💧', 'title' => 'Stay Hydrated', 'description' => 'Drink at least 2L of water today', 'completed' => false],
                ['icon' => '😴', 'title' => 'Prioritize Sleep', 'description' => 'Aim for 7-8 hours of quality sleep', 'completed' => false],
                ['icon' => '🍌', 'title' => 'Post-Workout Nutrition', 'description' => 'Consume protein within 30 minutes', 'completed' => true],
                ['icon' => '🧊', 'title' => 'Cold Shower', 'description' => '10-minute cold shower for recovery', 'completed' => false],
            ],
        ]);
    }

    /**
     * Create a new fitness goal
     */
   

    /**
     * Display a single goal
     */
    #[Route('/fitness/goals/{id}', name: 'fitness_goal_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showGoal(Goal $goal): Response
    {
        return $this->render('goal/show.html.twig', [
            'goal' => $goal,
        ]);
    }
    // Dans votre contrôleur FitnessController.php

#[Route('/daily-plan/{id}/complete', name: 'daily_plan_complete', methods: ['POST'])]
public function completeDailyPlan(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $this->denyAccessUnlessGranted('ROLE_USER');
    
    $dailyPlan = $entityManager->getRepository(DailyPlan::class)->find($id);
    
    if (!$dailyPlan) {
        return $this->json(['success' => false, 'message' => 'Plan non trouvé']);
    }
    
    // Vérifier que l'utilisateur est autorisé à modifier ce plan
    if ($dailyPlan->getUser() !== $this->getUser()) {
        return $this->json(['success' => false, 'message' => 'Non autorisé']);
    }
    
    $dailyPlan->setStatus('completed');
    $dailyPlan->setCompletedAt(new \DateTime());
    
    // Mettre à jour la progression des objectifs liés
    $goals = $dailyPlan->getFitnessGoals();
    foreach ($goals as $goal) {
        // Recalculer la progression basée sur les plans complétés
        $completedPlans = $goal->getDailyPlans()->filter(function($plan) {
            return $plan->getStatus() === 'completed';
        })->count();
        
        $totalPlans = $goal->getDailyPlans()->count();
        $progress = $totalPlans > 0 ? round(($completedPlans / $totalPlans) * 100) : 0;
        
        $goal->setProgress($progress);
        
        // Si la progression atteint 100%, marquer l'objectif comme terminé
        if ($progress >= 100) {
            $goal->setStatus('completed');
        }
    }
    
    $entityManager->flush();
    
    return $this->json([
        'success' => true,
        'message' => 'Plan marqué comme terminé',
        'progress' => $progress ?? 0
    ]);
}
}
