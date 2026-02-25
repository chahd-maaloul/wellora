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
    /**
     * Calcule la progression intelligente d'un goal
     * Basée sur : jours écoulés complétés / jours totaux
     * Les rest days sont exclus du calcul
     */
    private function calculateSmartProgress($goal): array
    {
        $allPlans = $goal->getDailyplan();
        $currentDate = new \DateTime();
        $currentDate->setTime(0, 0, 0);
        
        // Initialisation
        $totalPlans = count($allPlans);
        $completedPlans = 0;
        $restDays = 0;
        $workoutDays = 0;
        $completedWorkoutDays = 0;
        $totalDuration = 0;
        $totalCalories = 0;
        
        // Parcourir tous les plans
        foreach ($allPlans as $plan) {
            $planDate = $plan->getDate();
            $isRestDay = $plan->getExercices()->count() === 0;
            
            if ($isRestDay) {
                $restDays++;
            } else {
                $workoutDays++;
            }
            
            // Durée et calories totales
            $totalDuration += $plan->getDureeMin() ?? 0;
            $totalCalories += $plan->getCalories() ?? 0;
            
            // Plans complétés
            if ($plan->getStatus() === 'completed') {
                $completedPlans++;
                if (!$isRestDay) {
                    $completedWorkoutDays++;
                }
            }
        }
        
        // Calcul de la progression basée sur les jours du goal
        $progress = 0;
        $daysTotal = 0;
        $daysPassed = 0;
        $daysLeft = null;
        
        if ($goal->getStartDate() && $goal->getEndDate()) {
            $start = clone $goal->getStartDate();
            $start->setTime(0, 0, 0);
            $end = clone $goal->getEndDate();
            $end->setTime(0, 0, 0);
            
            // Jours total du goal
            $interval = $start->diff($end);
            $daysTotal = $interval->days + 1; // +1 pour inclure le dernier jour
            
            // Jours écoulés depuis le début
            if ($currentDate < $start) {
                $daysPassed = 0;
            } elseif ($currentDate > $end) {
                $daysPassed = $daysTotal;
                $daysLeft = 0;
            } else {
                $intervalPassed = $start->diff($currentDate);
                $daysPassed = $intervalPassed->days + 1;
                $intervalLeft = $currentDate->diff($end);
                $daysLeft = $intervalLeft->days + 1;
            }
            
            // Progression = (jours passés complétés) / (jours totaux)
            // Un jour est considéré complété si tous ses plans sont complétés
            $completedDays = 0;
            $datesProcessed = [];
            
            foreach ($allPlans as $plan) {
                $planDate = $plan->getDate();
                if (!$planDate) continue;
                
                $dateStr = $planDate->format('Y-m-d');
                
                // Ignorer les jours futurs
                if ($planDate > $currentDate) continue;
                
                if (!isset($datesProcessed[$dateStr])) {
                    $datesProcessed[$dateStr] = [
                        'total' => 0,
                        'completed' => 0,
                        'isRestDay' => false
                    ];
                }
                
                $datesProcessed[$dateStr]['total']++;
                if ($plan->getStatus() === 'completed') {
                    $datesProcessed[$dateStr]['completed']++;
                }
                if ($plan->getExercices()->count() === 0) {
                    $datesProcessed[$dateStr]['isRestDay'] = true;
                }
            }
            
            // Compter les jours complétés (tous les plans du jour sont complétés)
            foreach ($datesProcessed as $dateData) {
                // Ignorer les rest days dans le calcul
                if ($dateData['isRestDay']) continue;
                
                if ($dateData['total'] > 0 && $dateData['completed'] === $dateData['total']) {
                    $completedDays++;
                }
            }
            
            $progress = $daysTotal > 0 ? round(($completedDays / $daysTotal) * 100) : 0;
        }
        
        return [
            'total_plans' => $totalPlans,
            'completed_plans' => $completedPlans,
            'rest_days' => $restDays,
            'workout_days' => $workoutDays,
            'completed_workout_days' => $completedWorkoutDays,
            'progress' => $progress,
            'days_total' => $daysTotal,
            'days_passed' => $daysPassed,
            'days_left' => $daysLeft,
            'total_duration' => $totalDuration,
            'total_calories' => $totalCalories,
        ];
    }

    #[Route('/fitness/dashboard', name: 'fitness_dashboard')]
    public function index(
        GoalRepository $goalRepository,
        DailyPlanRepository $dailyPlanRepository,
        ExercisesRepository $exercisesRepository
    ): Response
    {
        $user = $this->getUser();
        
        // Récupérer TOUS les objectifs non complétés de l'utilisateur
        $allGoals = $goalRepository->findBy(
            ['patient' => $user, 'status' => ['PENDING', 'in progress']],
            ['startDate' => 'ASC']
        );
        
        // Préparer les données pour chaque objectif
        $goalsData = [];
        foreach ($allGoals as $goal) {
            $goalsData[] = $this->prepareGoalData($goal, $dailyPlanRepository);
        }
        
        // Objectif actif (premier de la liste ou celui sélectionné)
        $activeGoalId = $_GET['goal'] ?? ($goalsData[0]['id'] ?? null);
        
        // Préparer les données communes
        $currentDate = new \DateTime();
        $currentDate->setTime(0, 0, 0);
        
        // Exercices récents (globaux)
        $recentExercises = $this->getRecentExercises($dailyPlanRepository);
        
        // Catégories populaires
        $popularCategories = $this->getPopularCategories($exercisesRepository);
        
        return $this->render('fitness/patient-dashboard.html.twig', [
            'goals' => $goalsData,
            'activeGoalId' => $activeGoalId,
            'recentExercises' => $recentExercises,
            'popularCategories' => $popularCategories,
            'currentDate' => $currentDate->format('l, F j, Y'),
        ]);
    }

    /**
     * Prépare les données pour un objectif spécifique
     */
    private function prepareGoalData($goal, $dailyPlanRepository): array
    {
        $currentDate = new \DateTime();
        $currentDate->setTime(0, 0, 0);
        
        // Récupérer les plans liés à cet objectif
        $allPlans = $goal->getDailyplan();
        
        // Utiliser le nouveau calcul de progression
        $progressData = $this->calculateSmartProgress($goal);
        
        // Plan du jour
        $todayPlan = null;
        $todayDate = $currentDate->format('Y-m-d');
        foreach ($allPlans as $plan) {
            $planDate = $plan->getDate();
            if ($planDate && $planDate->format('Y-m-d') === $todayDate) {
                $todayPlan = $this->formatPlan($plan);
                break;
            }
        }
        
        // Plans de la semaine
        $startOfWeek = (clone $currentDate)->modify('monday this week')->setTime(0, 0, 0);
        $endOfWeek = (clone $startOfWeek)->modify('+6 days')->setTime(23, 59, 59);
        
        $weeklyPlans = [];
        foreach ($allPlans as $plan) {
            $planDate = $plan->getDate();
            if ($planDate && $planDate >= $startOfWeek && $planDate <= $endOfWeek) {
                $weeklyPlans[] = $this->formatPlan($plan);
            }
        }
        
        // Trier les plans par date
        usort($weeklyPlans, function($a, $b) {
            return $a['date'] <=> $b['date'];
        });
        
        return [
            'id' => $goal->getId(),
            'title' => $goal->getTitle(),
            'description' => $goal->getDescription(),
            'category' => $goal->getCategory(),
            'status' => $goal->getStatus(),
            'progress' => $progressData['progress'],
            'startDate' => $goal->getStartDate(),
            'endDate' => $goal->getEndDate(),
            'targetValue' => $goal->getTargetValue(),
            'currentValue' => $goal->getCurrentValue(),
            'unit' => $goal->getUnit(),
            'daysLeft' => $progressData['days_left'],
            'todayPlan' => $todayPlan,
            'weeklyPlans' => $weeklyPlans,
            'stats' => $progressData
        ];
    }

    /**
     * Formate un plan
     */
    private function formatPlan($plan): array
    {
        $exercices = [];
        foreach ($plan->getExercices() as $exercise) {
            $exercices[] = [
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
        
        return [
            'id' => $plan->getId(),
            'titre' => $plan->getTitre() ?? 'Sans titre',
            'date' => $plan->getDate(),
            'status' => $plan->getStatus() ?? 'planned',
            'dureeMin' => $plan->getDureeMin() ?? 0,
            'calories' => $plan->getCalories() ?? 0,
            'notes' => $plan->getNotes() ?? '',
            'exercices' => $exercices,
            'isRestDay' => count($exercices) === 0
        ];
    }

    /**
     * Récupère les exercices récents
     */
    private function getRecentExercises($dailyPlanRepository): array
    {
        $recentPlans = $dailyPlanRepository->findBy(
            [],
            ['date' => 'DESC'],
            10
        );
        
        $recentExercises = [];
        foreach ($recentPlans as $plan) {
            foreach ($plan->getExercices() as $exercise) {
                if (!in_array($exercise->getId(), array_column($recentExercises, 'id'))) {
                    $recentExercises[] = [
                        'id' => $exercise->getId(),
                        'name' => $exercise->getName(),
                        'category' => $exercise->getCategory(),
                        'duration' => $exercise->getDuration() ?? 0,
                        'calories' => $exercise->getCalories() ?? 0,
                        'videoFileName' => $exercise->getVideoFileName() ?? ''
                    ];
                }
                if (count($recentExercises) >= 5) break;
            }
            if (count($recentExercises) >= 5) break;
        }
        
        return $recentExercises;
    }

    /**
     * Récupère les catégories populaires
     */
    private function getPopularCategories($exercisesRepository): array
    {
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
        
        return $popularCategories;
    }

    #[Route('/fitness/planner', name: 'fitness_planner')]
    public function workoutPlanner(
        Request $request,
        DailyPlanRepository $dailyPlanRepository,
        GoalRepository $goalRepository
    ): Response {
        $user = $this->getUser();
        $goalId = $request->query->get('goal');
        $goal = null;
        
        // Récupérer tous les objectifs actifs
        $goals = $goalRepository->findBy(
            ['patient' => $user, 'status' => ['PENDING', 'in progress']],
            ['startDate' => 'ASC']
        );
        
        // Récupérer les daily plans
        if ($goalId) {
            $goal = $goalRepository->find($goalId);
            if ($goal && $goal->getPatient() === $user) {
                $dailyPlans = $goal->getDailyplan()->toArray();
            } else {
                $dailyPlans = [];
            }
        } else {
            $userGoals = $goalRepository->findBy(['patient' => $user]);
            $dailyPlans = [];
            foreach ($userGoals as $userGoal) {
                foreach ($userGoal->getDailyplan() as $plan) {
                    $dailyPlans[] = $plan;
                }
            }
        }
        
        // FORMATTER LES PLANS
        $formattedPlans = [];
        foreach ($dailyPlans as $plan) {
            $planDate = $plan->getDate();
            $planDate->setTimezone(new \DateTimeZone('UTC'));
            $dateStr = $planDate->format('Y-m-d');
            $exercisesCount = $plan->getExercices()->count();
            
            $formattedPlans[] = [
                'id' => $plan->getId(),
                'title' => $plan->getTitre() ?? 'Sans titre',
                'date' => $dateStr,
                'status' => $plan->getStatus() ?? 'planned',
                'duration' => $plan->getDureeMin() ?? 0,
                'calories' => $plan->getCalories() ?? 0,
                'notes' => $plan->getNotes() ?? '',
                'exercises' => $exercisesCount,
                'isRestDay' => $exercisesCount === 0,
                'goalId' => $plan->getGoal() ? $plan->getGoal()->getId() : null,
                'goalTitle' => $plan->getGoal() ? $plan->getGoal()->getTitle() : null,
            ];
        }
        
        // Statistiques hebdomadaires
        $weeklyStats = [
            'scheduled' => count($formattedPlans),
            'completed' => count(array_filter($formattedPlans, fn($p) => $p['status'] === 'completed')),
            'minutes' => array_sum(array_column($formattedPlans, 'duration')),
            'restDays' => count(array_filter($formattedPlans, fn($p) => $p['isRestDay'])),
            'calories' => array_sum(array_column($formattedPlans, 'calories')),
        ];
        
        return $this->render('fitness/workout-planner.html.twig', [
            'pageTitle' => 'Workout Planner',
            'dailyPlans' => $formattedPlans,
            'weeklyStats' => $weeklyStats,
            'goals' => $goals,
            'goal' => $goal,
        ]);
    }

   #[Route('/fitness/library', name: 'fitness_library')]
public function show(
    Request $request, 
    ExercisesRepository $exercisesRepository, 
    GoalRepository $goalRepository
): Response {
    $user = $this->getUser();
    
    // Récupérer tous les objectifs de l'utilisateur pour le filtre
    $userGoals = $goalRepository->findBy(
        ['patient' => $user],
        ['startDate' => 'DESC']
    );
    
    // Paramètres de pagination
    $page = $request->query->getInt('page', 1);
    $limit = 12; // Nombre d'exercices par page
    $offset = ($page - 1) * $limit;
    
    // Paramètres de filtrage
    $goalId = $request->query->get('goal');
    $category = $request->query->get('category');
    $difficulty = $request->query->get('difficulty');
    $search = $request->query->get('search');
    
    // Utiliser les méthodes du repository pour les filtres et la pagination
    $exercises = $exercisesRepository->findByFilters(
        $goalId, 
        $category, 
        $difficulty, 
        $search, 
        $offset, 
        $limit
    );
    
    $totalExercises = $exercisesRepository->countByFilters(
        $goalId, 
        $category, 
        $difficulty, 
        $search
    );
    
    $totalPages = ceil($totalExercises / $limit);
    
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
            'videoFileName' => $exercise->getVideoFileName(),
            'isActive' => $exercise->isActive(),
            'createdAt' => $exercise->getCreatedAt() ? $exercise->getCreatedAt()->format('Y-m-d H:i:s') : null,
            'duration' => $exercise->getDuration(),
            'calories' => $exercise->getCalories(),
            'sets' => $exercise->getSets(),
            'reps' => $exercise->getReps(),
        ];
    }
    
    // Si c'est une requête AJAX, retourner du JSON
    if ($request->isXmlHttpRequest()) {
        return $this->json([
            'exercises' => $exercisesArray,
            'total' => $totalExercises,
            'page' => $page,
            'totalPages' => $totalPages,
            'hasMore' => $page < $totalPages
        ]);
    }
    
    return $this->render('fitness/exercise-library.html.twig', [
        'page_title' => 'Exercise Library',
        'exercises' => $exercisesArray,
        'userGoals' => $userGoals,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalExercises' => $totalExercises,
        'filters' => [
            'goal' => $goalId,
            'category' => $category,
            'difficulty' => $difficulty,
            'search' => $search
        ]
    ]);  
}

    /**
     * NOUVELLE ROUTE POUR TOGGLER LE STATUT D'UN PLAN
     */
   

    #[Route('/fitness/goals-plans', name: 'fitness_goals_plans')]
    public function goalsPlans(
        Request $request,
        GoalRepository $goalRepository,
        DailyPlanRepository $dailyPlanRepository
    ): Response {
        $user = $this->getUser();
        
        // Récupérer TOUS les objectifs de l'utilisateur
        $allGoals = $goalRepository->findBy(
            ['patient' => $user],
            ['startDate' => 'DESC']
        );
        
        // Récupérer l'ID de l'objectif sélectionné
        $selectedGoalId = $request->query->get('goal');
        $selectedGoal = null;
        $formattedPlans = [];
        $stats = [
        'completed_plans' => 0,
        'total_plans_created' => 0
       ];
        
        if ($selectedGoalId) {
            $selectedGoal = $goalRepository->find($selectedGoalId);
            if ($selectedGoal && $selectedGoal->getPatient() === $user) {
                $formattedPlans = $this->formatGoalPlans($selectedGoal);
                  // Calculer les stats pour ce goal
            $today = new \DateTime();
            $today->setTime(0, 0, 0);
            
            foreach ($selectedGoal->getDailyplan() as $plan) {
                if ($plan->getDate() <= $today) {
                    $stats['total_plans_created']++;
                    if ($plan->getStatus() === 'completed') {
                        $stats['completed_plans']++;
                    }
                }
            }
            }
        } elseif (!empty($allGoals)) {
            $selectedGoal = $allGoals[0];
            $selectedGoalId = $selectedGoal->getId();
            $formattedPlans = $this->formatGoalPlans($selectedGoal);
             // Calculer les stats pour ce goal
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        
        foreach ($selectedGoal->getDailyplan() as $plan) {
            if ($plan->getDate() <= $today) {
                $stats['total_plans_created']++;
                if ($plan->getStatus() === 'completed') {
                    $stats['completed_plans']++;
                }
            }
        }
        }
        
        return $this->render('fitness/goals-plans.html.twig', [
            'page_title' => 'Goals & Plans',
            'allGoals' => $allGoals,
            'selectedGoal' => $selectedGoal,
            'selectedGoalId' => $selectedGoalId,
            'plans' => $formattedPlans,
            'stats' => $stats
        ]);
    }

    /**
     * Formate les plans d'un objectif avec leurs exercices
     */
    private function formatGoalPlans($goal): array
    {
        $allPlans = $goal->getDailyplan()->toArray();
        $formattedPlans = [];
        $completedPlans = 0;
        
        foreach ($allPlans as $plan) {
            // Formater les exercices
            $exercises = [];
            foreach ($plan->getExercices() as $exercise) {
                $exercises[] = [
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
                    'videoFileName' => $exercise->getVideoFileName() ?? '',
                ];
            }
            
            $isCompleted = $plan->getStatus() === 'completed';
            if ($isCompleted) {
                $completedPlans++;
            }
            
            $planDate = $plan->getDate();
            $formattedPlans[] = [
                'id' => $plan->getId(),
                'title' => $plan->getTitre() ?? 'Sans titre',
                'date' => $planDate,
                'dateFormatted' => $planDate ? $planDate->format('d/m/Y') : 'Date non définie',
                'dayName' => $planDate ? $planDate->format('l') : '',
                'status' => $plan->getStatus() ?? 'planned',
                'duration' => $plan->getDureeMin() ?? 0,
                'calories' => $plan->getCalories() ?? 0,
                'notes' => $plan->getNotes() ?? '',
                'exercises' => $exercises,
                'exerciseCount' => count($exercises),
                'isCompleted' => $isCompleted,
                'isRestDay' => count($exercises) === 0,
            ];
        }
        
        // Trier par date (du plus récent au plus ancien)
        usort($formattedPlans, function($a, $b) {
            if (!$a['date'] && !$b['date']) return 0;
            if (!$a['date']) return 1;
            if (!$b['date']) return -1;
            return $b['date'] <=> $a['date'];
        });
        
        return $formattedPlans;
    }
    
#[Route('/fitness/daily-plan/{id}/complete', name: 'fitness_daily_plan_complete', methods: ['POST'])]
public function completeDailyPlan(
    DailyPlan $dailyPlan, 
    EntityManagerInterface $entityManager
): JsonResponse {
    try {
        $user = $this->getUser();
        $goal = $dailyPlan->getGoal();
        
        if (!$goal || $goal->getPatient() !== $user) {
            return $this->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }
        
        if ($dailyPlan->getStatus() === 'completed') {
            return $this->json(['success' => false, 'error' => 'Already completed'], 400);
        }
        
        // 1. Marquer le plan comme complété
        $dailyPlan->setStatus('completed');
        
        // 2. Utiliser EXACTEMENT la même méthode que le dashboard
        $progressData = $this->calculateSmartProgress($goal); // 👈 Celle du dashboard
        $newProgress = $progressData['progress']; // 4%
        
        // 3. Sauvegarder en base
        $goal->setProgress($newProgress);
        $entityManager->flush();
        
        return $this->json([
            'success' => true,
            'progress' => $newProgress, // 4% ✅
            'goalId' => $goal->getId()
        ]);
        
    } catch (\Exception $e) {
        return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}
}
