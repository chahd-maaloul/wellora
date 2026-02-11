<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Repository\FoodItemRepository;
use App\Repository\FoodPlanRepository;
use App\Repository\NutritionGoalRepository;
use App\Repository\WaterLogRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

#[Route('/nutrition', name: 'nutrition_')]
class NutritionController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function dashboard(
        NutritionGoalRepository $nutritionGoalRepository,
        FoodItemRepository $foodItemRepository,
        WaterLogRepository $waterLogRepository
    ): Response {
        // Get current user (assuming patient is logged in)
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            // For demo purposes, get first patient from database
            $patient = $foodItemRepository->findOneBy([])?->getPatient();
            if (!$patient) {
                // If no patients exist, create default data
                $patient = null;
            }
        } else {
            $patient = $user;
        }

        // Get active nutrition goal
        $nutritionGoal = null;
        $calorieTarget = 2000;
        $proteinTarget = 120;
        $carbTarget = 250;
        $fatTarget = 70;

        if ($patient) {
            $nutritionGoal = $nutritionGoalRepository->findOneBy([
                'patient' => $patient,
                'isActive' => true
            ]);

            if ($nutritionGoal) {
                $calorieTarget = $nutritionGoal->getDailyCalories();
                $proteinTarget = (float)$nutritionGoal->getProteinPercent();
                $carbTarget = (float)$nutritionGoal->getCarbPercent();
                $fatTarget = (float)$nutritionGoal->getFatPercent();
            }
        }

        // Calculate today's consumed nutrients
        $today = new DateTime('today');
        $consumedCalories = 0;
        $consumedProteins = 0;
        $consumedCarbs = 0;
        $consumedFats = 0;
        $meals = [
            'breakfast' => ['calories' => 0, 'items' => []],
            'lunch' => ['calories' => 0, 'items' => []],
            'dinner' => ['calories' => 0, 'items' => []],
            'snacks' => ['calories' => 0, 'items' => []]
        ];

        if ($patient) {
            $todayFoodItems = $foodItemRepository->findBy([
                'patient' => $patient
            ]);

            // Filter for today's items
            $todayFoodItems = array_filter($todayFoodItems, function($item) use ($today) {
                return $item->getLoggedAt() && $item->getLoggedAt()->format('Y-m-d') === $today->format('Y-m-d');
            });

            foreach ($todayFoodItems as $foodItem) {
                $mealType = $foodItem->getMealType() ?? 'snacks';
                if (!isset($meals[$mealType])) {
                    $mealType = 'snacks';
                }

                $meals[$mealType]['calories'] += $foodItem->getCalories();
                $meals[$mealType]['items'][] = $foodItem->getNomItem();

                $consumedCalories += $foodItem->getCalories();
                $consumedProteins += (float)$foodItem->getProtein();
                $consumedCarbs += (float)$foodItem->getCarbs();
                $consumedFats += (float)$foodItem->getFat();
            }
        }

        // Get water intake for today
        $waterIntake = 0;
        if ($patient) {
            $todayWaterLog = $waterLogRepository->findOneBy([
                'patient' => $patient,
                'logDate' => $today
            ]);
            if ($todayWaterLog) {
                $waterIntake = $todayWaterLog->getGlasses();
            }
        }

        // Get recent foods (last 4 logged foods)
        $recentFoods = [];
        if ($patient) {
            $recentFoodItems = $foodItemRepository->findBy(
                ['patient' => $patient],
                ['loggedAt' => 'DESC'],
                4
            );

            foreach ($recentFoodItems as $foodItem) {
                $recentFoods[] = [
                    'name' => $foodItem->getNomItem(),
                    'calories' => $foodItem->getCalories()
                ];
            }
        }

        // If no recent foods, provide default ones
        if (empty($recentFoods)) {
            $recentFoods = [
                ['name' => 'Pomme', 'calories' => 95],
                ['name' => 'Banane', 'calories' => 105],
                ['name' => 'Yaourt', 'calories' => 120],
                ['name' => 'Oeuf', 'calories' => 78],
            ];
        }

        // Default quick add foods (could be from a database table later)
        $quickAddFoods = [
            ['name' => 'Pomme', 'calories' => 95, 'unit' => 'pièce'],
            ['name' => 'Banane', 'calories' => 105, 'unit' => 'pièce'],
            ['name' => 'Yaourt', 'calories' => 120, 'unit' => 'pièce'],
            ['name' => 'Oeuf', 'calories' => 78, 'unit' => 'pièce'],
            ['name' => 'Lait', 'calories' => 150, 'unit' => 'verre'],
            ['name' => 'Café', 'calories' => 5, 'unit' => 'tasse'],
        ];

        // Get assigned nutritionist
        $nutritionistData = null;
        if ($nutritionGoal && $nutritionGoal->getNutritionist()) {
            $nutritionist = $nutritionGoal->getNutritionist();
            $nutritionistData = [
                'id' => $nutritionist->getId(),
                'name' => $nutritionist->getNomNutritioniste(),
                'avatar' => null,
                'nextAppointment' => ['date' => new DateTime('+3 days 14:00')] // Placeholder
            ];
        }

        // Calculate streaks
        $streaks = [
            'logging' => 0,
            'water' => 0,
            'veggies' => 0,
        ];

        if ($patient) {
            // Logging streak: consecutive days with food logged
            $today = new DateTime('today');
            $loggingStreak = 0;
            for ($i = 0; $i < 30; $i++) { // Check last 30 days
                $date = clone $today;
                $date->modify("-{$i} days");
                if ($foodItemRepository->hasLoggedOnDate($patient, $date)) {
                    $loggingStreak++;
                } else {
                    break;
                }
            }
            $streaks['logging'] = $loggingStreak;

            // Water streak: consecutive days with water logged
            $waterStreak = 0;
            for ($i = 0; $i < 30; $i++) {
                $date = clone $today;
                $date->modify("-{$i} days");
                $waterLog = $waterLogRepository->findOneBy([
                    'patient' => $patient,
                    'logDate' => $date
                ]);
                if ($waterLog && $waterLog->getGlasses() >= 6) { // Assuming 6 glasses is good
                    $waterStreak++;
                } else {
                    break;
                }
            }
            $streaks['water'] = $waterStreak;

            // Veggies streak: consecutive days with vegetable items logged
            $veggieStreak = 0;
            for ($i = 0; $i < 30; $i++) {
                $date = clone $today;
                $date->modify("-{$i} days");
                if ($foodItemRepository->hasLoggedOnDate($patient, $date)) {
                    // Check if any logged food contains vegetable keywords
                    $dateFoodItems = $foodItemRepository->findByPatientAndDateRange($patient, $date, $date);
                    $hasVeggies = false;
                    foreach ($dateFoodItems as $item) {
                        $name = strtolower($item->getNomItem());
                        if (strpos($name, 'salade') !== false || strpos($name, 'tomate') !== false ||
                            strpos($name, 'carotte') !== false || strpos($name, 'brocoli') !== false ||
                            strpos($name, 'épinard') !== false || strpos($name, 'concombre') !== false) {
                            $hasVeggies = true;
                            break;
                        }
                    }
                    if ($hasVeggies) {
                        $veggieStreak++;
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            }
            $streaks['veggies'] = $veggieStreak;
        }

        // Default badges (could be calculated based on achievements)
        $badges = [];
        if ($streaks['logging'] >= 7) {
            $badges[] = ['name' => '7 jours consécutifs', 'icon' => 'fa-fire'];
        }
        if ($streaks['logging'] >= 30) {
            $badges[] = ['name' => '30 jours consécutifs', 'icon' => 'fa-calendar-check'];
        }
        if ($streaks['water'] >= 7) {
            $badges[] = ['name' => 'Hydratation', 'icon' => 'fa-glass-water'];
        }
        if ($streaks['veggies'] >= 7) {
            $badges[] = ['name' => 'Légumes', 'icon' => 'fa-carrot'];
        }
        $badges[] = ['name' => 'Objectif atteint', 'icon' => 'fa-trophy'];

        return $this->render('nutrition/dashboard.html.twig', [
            'calories' => ['consumed' => $consumedCalories, 'target' => $calorieTarget],
            'water' => ['intake' => $waterIntake, 'target' => 8],
            'macros' => [
                'proteins' => $consumedProteins,
                'carbs' => $consumedCarbs,
                'fats' => $consumedFats,
                'proteinTarget' => $proteinTarget,
                'carbTarget' => $carbTarget,
                'fatTarget' => $fatTarget
            ],
            'meals' => $meals,
            'recentFoods' => $recentFoods,
            'quickAddFoods' => $quickAddFoods,
            'nutritionist' => $nutritionistData,
            'badges' => $badges,
            'streaks' => $streaks,
        ]);
    }

    #[Route('/diary', name: 'food_diary')]
    public function foodDiary(
        Request $request,
        NutritionGoalRepository $nutritionGoalRepository,
        FoodItemRepository $foodItemRepository,
        WaterLogRepository $waterLogRepository
    ): Response {
        // Get selected date from request, default to today
        $selectedDate = $request->query->get('date');
        if ($selectedDate) {
            $date = new DateTime($selectedDate);
        } else {
            $date = new DateTime('today');
        }

        // Get current user (assuming patient is logged in)
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            // For demo purposes, get first patient from database
            $patient = $foodItemRepository->findOneBy([])?->getPatient();
            if (!$patient) {
                // If no patients exist, create default data
                $patient = null;
            }
        } else {
            $patient = $user;
        }

        // Get active nutrition goal
        $nutritionGoal = null;
        $calorieTarget = 2000;
        $proteinTarget = 120;
        $carbTarget = 250;
        $fatTarget = 70;

        if ($patient) {
            $nutritionGoal = $nutritionGoalRepository->findOneBy([
                'patient' => $patient,
                'isActive' => true
            ]);

            if ($nutritionGoal) {
                $calorieTarget = $nutritionGoal->getDailyCalories();
                $proteinTarget = (float)$nutritionGoal->getProteinPercent();
                $carbTarget = (float)$nutritionGoal->getCarbPercent();
                $fatTarget = (float)$nutritionGoal->getFatPercent();
            }
        }

        // Calculate consumed nutrients for selected date
        $consumedCalories = 0;
        $consumedProteins = 0;
        $consumedCarbs = 0;
        $consumedFats = 0;
        $meals = [
            'breakfast' => ['calories' => 0, 'items' => []],
            'lunch' => ['calories' => 0, 'items' => []],
            'dinner' => ['calories' => 0, 'items' => []],
            'snacks' => ['calories' => 0, 'items' => []]
        ];

        if ($patient) {
            $foodItems = $foodItemRepository->findBy(['patient' => $patient]);

            // Filter for selected date
            $dateFoodItems = array_filter($foodItems, function($item) use ($date) {
                return $item->getLoggedAt() && $item->getLoggedAt()->format('Y-m-d') === $date->format('Y-m-d');
            });

            foreach ($dateFoodItems as $foodItem) {
                $mealType = $foodItem->getMealType() ?? 'snacks';
                if (!isset($meals[$mealType])) {
                    $mealType = 'snacks';
                }

                $meals[$mealType]['calories'] += $foodItem->getCalories();
                $meals[$mealType]['items'][] = [
                    'id' => $foodItem->getId(),
                    'name' => $foodItem->getNomItem(),
                    'quantity' => 1, // Default quantity
                    'calories' => $foodItem->getCalories(),
                    'proteins' => (float)$foodItem->getProtein(),
                    'carbs' => (float)$foodItem->getCarbs(),
                    'fats' => (float)$foodItem->getFat(),
                ];

                $consumedCalories += $foodItem->getCalories();
                $consumedProteins += (float)$foodItem->getProtein();
                $consumedCarbs += (float)$foodItem->getCarbs();
                $consumedFats += (float)$foodItem->getFat();
            }
        }

        // Get water intake for selected date
        $waterIntake = 0;
        if ($patient) {
            $waterLog = $waterLogRepository->findOneBy([
                'patient' => $patient,
                'logDate' => $date
            ]);
            if ($waterLog) {
                $waterIntake = $waterLog->getGlasses();
            }
        }

        // Calculate food groups based on actual food items
        $foodGroups = $this->calculateFoodGroups($dateFoodItems ?? []);

        // Generate nutrition alerts based on actual data
        $nutritionAlerts = $this->generateNutritionAlerts($consumedCalories, $consumedProteins, $consumedCarbs, $consumedFats, $calorieTarget, $proteinTarget, $carbTarget, $fatTarget, $waterIntake);

        return $this->render('nutrition/food-diary.html.twig', [
            'selectedDate' => $date->format('Y-m-d'),
            'dailySummary' => [
                'calories' => $consumedCalories,
                'calorieTarget' => $calorieTarget,
                'proteins' => $consumedProteins,
                'proteinTarget' => $proteinTarget,
                'carbs' => $consumedCarbs,
                'carbTarget' => $carbTarget,
                'fats' => $consumedFats,
                'fatTarget' => $fatTarget,
            ],
            'water' => ['intake' => $waterIntake, 'target' => 8],
            'macros' => ['proteins' => $consumedProteins, 'carbs' => $consumedCarbs, 'fats' => $consumedFats],
            'meals' => $meals,
            'foodGroups' => $foodGroups,
            'nutritionAlerts' => $nutritionAlerts,
        ]);
    }

    private function calculateFoodGroups(array $foodItems): array
    {
        $groups = [
            'Fruits & Légumes' => ['count' => 0, 'percentage' => 0, 'color' => 'bg-green-500'],
            'Protéines' => ['count' => 0, 'percentage' => 0, 'color' => 'bg-amber-500'],
            'Glucides' => ['count' => 0, 'percentage' => 0, 'color' => 'bg-yellow-500'],
            'Lipides' => ['count' => 0, 'percentage' => 0, 'color' => 'bg-purple-500'],
        ];

        $totalItems = count($foodItems);
        if ($totalItems === 0) {
            return $groups;
        }

        foreach ($foodItems as $item) {
            $name = strtolower($item->getNomItem());

            // Fruits & Légumes
            if (strpos($name, 'pomme') !== false || strpos($name, 'banane') !== false ||
                strpos($name, 'salade') !== false || strpos($name, 'tomate') !== false ||
                strpos($name, 'carotte') !== false || strpos($name, 'brocoli') !== false ||
                strpos($name, 'épinard') !== false || strpos($name, 'concombre') !== false ||
                strpos($name, 'orange') !== false || strpos($name, 'fraise') !== false) {
                $groups['Fruits & Légumes']['count']++;
            }
            // Protéines
            elseif (strpos($name, 'poulet') !== false || strpos($name, 'viande') !== false ||
                    strpos($name, 'poisson') !== false || strpos($name, 'oeuf') !== false ||
                    strpos($name, 'fromage') !== false || strpos($name, 'yaourt') !== false ||
                    strpos($name, 'tofu') !== false) {
                $groups['Protéines']['count']++;
            }
            // Glucides
            elseif (strpos($name, 'pain') !== false || strpos($name, 'riz') !== false ||
                    strpos($name, 'pâtes') !== false || strpos($name, 'pomme de terre') !== false ||
                    strpos($name, 'céréale') !== false || strpos($name, 'quinoa') !== false) {
                $groups['Glucides']['count']++;
            }
            // Lipides (default category for others)
            else {
                $groups['Lipides']['count']++;
            }
        }

        // Calculate percentages
        foreach ($groups as &$group) {
            $group['percentage'] = $totalItems > 0 ? round(($group['count'] / $totalItems) * 100) : 0;
        }

        return $groups;
    }

    private function generateNutritionAlerts($calories, $proteins, $carbs, $fats, $calorieTarget, $proteinTarget, $carbTarget, $fatTarget, $waterIntake): array
    {
        $alerts = [];

        // Protein alert
        if ($proteins < $proteinTarget * 0.8) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fa-exclamation-triangle',
                'message' => 'Protéines en dessous de l\'objectif',
                'recommendation' => 'Ajoutez des sources de protéines à vos repas'
            ];
        }

        // Calorie alert
        if ($calories > $calorieTarget * 1.1) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fa-fire',
                'message' => 'Calories dépassant l\'objectif',
                'recommendation' => 'Réduisez les portions ou choisissez des aliments moins caloriques'
            ];
        }

        // Water alert
        if ($waterIntake < 6) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'fa-info-circle',
                'message' => 'Hydratation insuffisante',
                'recommendation' => 'Buvez 3 verres d\'eau supplémentaires'
            ];
        }

        // Fat alert
        if ($fats > $fatTarget * 1.2) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fa-cheese',
                'message' => 'Lipides en excès',
                'recommendation' => 'Privilégiez les graisses saines et réduisez les aliments frits'
            ];
        }

        return $alerts;
    }

    #[Route('/quick-log', name: 'quick_log')]
    public function quickLog(): Response
    {
        return $this->render('nutrition/quick-log.html.twig', [
            'todaySummary' => [
                'calories' => 1850,
                'proteins' => 85,
                'carbs' => 180,
                'fats' => 60,
            ],
        ]);
    }

    #[Route('/planner/{weekStart?}', name: 'meal_planner')]
    public function mealPlanner(
        Request $request,
        FoodPlanRepository $foodPlanRepository,
        FoodItemRepository $foodItemRepository,
        ?string $weekStart = null
    ): Response {
        // Get current user (assuming patient is logged in)
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            // For demo purposes, get first patient from database
            $patient = $foodItemRepository->findOneBy([])?->getPatient();
            if (!$patient) {
                $patient = null;
            }
        } else {
            $patient = $user;
        }

        // Handle week navigation
        if (!$weekStart) {
            $weekStart = new DateTime('monday this week');
        } else {
            $weekStart = new DateTime($weekStart);
        }

        // Handle form submissions
        if ($request->isMethod('POST')) {
            $this->handleMealPlannerForm($request, $foodPlanRepository, $foodItemRepository, $patient, $weekStart);
            // Redirect to refresh the page
            return $this->redirectToRoute('nutrition_meal_planner', ['weekStart' => $weekStart->format('Y-m-d')]);
        }

        // Load meal plan for the week
        $weekPlan = $this->loadMealPlan($foodPlanRepository, $patient, $weekStart);
        $weeklyStats = $this->calculateWeeklyStats($weekPlan);

        // Get available recipes
        $recipes = $this->getRecipes($foodItemRepository);

        return $this->render('nutrition/meal-planner.html.twig', [
            'weekStart' => $weekStart,
            'weekPlan' => $weekPlan,
            'weeklyStats' => $weeklyStats,
            'recipes' => $recipes,
            'weekRange' => $this->getWeekRange($weekStart),
        ]);
    }

    private function handleMealPlannerForm(Request $request, FoodPlanRepository $foodPlanRepository, FoodItemRepository $foodItemRepository, ?Patient $patient, DateTime $weekStart): void
    {
        $action = $request->request->get('action');

        if ($action === 'add_meal') {
            $this->addMealToPlan($request, $foodPlanRepository, $foodItemRepository, $patient, $weekStart);
        } elseif ($action === 'remove_meal') {
            $this->removeMealFromPlan($request, $foodPlanRepository);
        } elseif ($action === 'clear_week') {
            $this->clearWeekPlan($foodPlanRepository, $patient, $weekStart);
        }
    }

    private function addMealToPlan(Request $request, FoodPlanRepository $foodPlanRepository, FoodItemRepository $foodItemRepository, ?Patient $patient, DateTime $weekStart): void
    {
        $foodItemId = $request->request->get('food_item_id');
        $day = $request->request->get('day');
        $mealType = $request->request->get('meal_type');

        if (!$patient || !$foodItemId || !$day || !$mealType) {
            return;
        }

        $foodItem = $foodItemRepository->find($foodItemId);
        if (!$foodItem) {
            return;
        }

        // Get or create nutrition goal
        $nutritionGoal = $foodPlanRepository->findOneBy(['nutritionGoal.patient' => $patient])?->getNutritionGoal();
        if (!$nutritionGoal) {
            $nutritionGoal = new \App\Entity\NutritionGoal();
            $nutritionGoal->setPatient($patient);
            $nutritionGoal->setIsActive(true);
            $nutritionGoal->setDailyCalories(2000);
            $nutritionGoal->setProteinPercent('25.0');
            $nutritionGoal->setCarbPercent('50.0');
            $nutritionGoal->setFatPercent('25.0');
            $nutritionGoal->setCreatedAt(new DateTime());
            $foodPlanRepository->getEntityManager()->persist($nutritionGoal);
            $foodPlanRepository->getEntityManager()->flush();
        }

        // Calculate plan date
        $dayOffsets = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];

        $planDate = clone $weekStart;
        $planDate->modify("+{$dayOffsets[$day]} days");

        // Check if meal already exists for this day/meal type
        $existingPlan = $foodPlanRepository->findOneBy([
            'nutritionGoal' => $nutritionGoal,
            'planDate' => $planDate,
            'mealType' => $mealType,
        ]);

        if ($existingPlan) {
            $existingPlan->addFoodItem($foodItem);
        } else {
            $foodPlan = new \App\Entity\FoodPlan();
            $foodPlan->setNutritionGoal($nutritionGoal);
            $foodPlan->setPlanDate($planDate);
            $foodPlan->setMealType($mealType);
            $foodPlan->addFoodItem($foodItem);
            $foodPlanRepository->getEntityManager()->persist($foodPlan);
        }

        $foodPlanRepository->getEntityManager()->flush();
    }

    private function removeMealFromPlan(Request $request, FoodPlanRepository $foodPlanRepository): void
    {
        $foodPlanId = $request->request->get('food_plan_id');
        $foodItemId = $request->request->get('food_item_id');

        $foodPlan = $foodPlanRepository->find($foodPlanId);
        if ($foodPlan) {
            $foodItem = $foodPlan->getFoodItems()->filter(function($item) use ($foodItemId) {
                return $item->getId() == $foodItemId;
            })->first();

            if ($foodItem) {
                $foodPlan->removeFoodItem($foodItem);
                if ($foodPlan->getFoodItems()->isEmpty()) {
                    $foodPlanRepository->getEntityManager()->remove($foodPlan);
                }
                $foodPlanRepository->getEntityManager()->flush();
            }
        }
    }

    private function clearWeekPlan(FoodPlanRepository $foodPlanRepository, ?Patient $patient, DateTime $weekStart): void
    {
        if (!$patient) return;

        $endDate = clone $weekStart;
        $endDate->modify('+6 days');

        $existingPlans = $foodPlanRepository->findByDateRange($patient, $weekStart, $endDate);
        foreach ($existingPlans as $plan) {
            $foodPlanRepository->getEntityManager()->remove($plan);
        }
        $foodPlanRepository->getEntityManager()->flush();
    }

    private function loadMealPlan(FoodPlanRepository $foodPlanRepository, ?Patient $patient, DateTime $weekStart): array
    {
        $weekPlan = [
            'monday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
            'tuesday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
            'wednesday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
            'thursday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
            'friday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
            'saturday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
            'sunday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
        ];

        if ($patient) {
            $endDate = clone $weekStart;
            $endDate->modify('+6 days');

            $foodPlans = $foodPlanRepository->findByDateRange($patient, $weekStart, $endDate);

            foreach ($foodPlans as $foodPlan) {
                $dayName = strtolower($foodPlan->getPlanDate()->format('l'));
                $mealType = $foodPlan->getMealType();

                if (isset($weekPlan[$dayName][$mealType])) {
                    $foodItems = $foodPlan->getFoodItems();
                    foreach ($foodItems as $foodItem) {
                        $weekPlan[$dayName][$mealType][] = [
                            'id' => $foodItem->getId(),
                            'name' => $foodItem->getNomItem(),
                            'calories' => $foodItem->getCalories(),
                            'proteins' => (float)$foodItem->getProtein(),
                            'carbs' => (float)$foodItem->getCarbs(),
                            'fats' => (float)$foodItem->getFat(),
                            'plan_id' => $foodPlan->getId(),
                        ];
                    }
                }
            }
        }

        return $weekPlan;
    }

    private function getWeekRange(DateTime $weekStart): string
    {
        $endDate = clone $weekStart;
        $endDate->modify('+6 days');
        return $weekStart->format('d/m') . ' - ' . $endDate->format('d/m');
    }

    private function getRecipes(FoodItemRepository $foodItemRepository): array
    {
        $foodItems = $foodItemRepository->findAll();
        $recipes = [];

        foreach ($foodItems as $item) {
            $recipes[] = [
                'id' => $item->getId(),
                'name' => $item->getNomItem(),
                'description' => 'Recette basée sur ' . $item->getNomItem(),
                'calories' => $item->getCalories(),
                'prepTime' => 15,
                'proteins' => (float)$item->getProtein(),
                'carbs' => (float)$item->getCarbs(),
                'fats' => (float)$item->getFat(),
                'mealType' => $item->getMealType() ?? 'snacks',
                'icon' => 'fa-utensils',
                'diet' => 'Général'
            ];
        }

        return $recipes;
    }

    #[Route('/recipes', name: 'recipes')]
    public function recipes(): Response
    {
        return $this->render('nutrition/recipe-library.html.twig', [
            'recipes' => [
                [
                    'id' => 1,
                    'name' => 'Salade de quinoa aux légumes',
                    'description' => 'Une salade fraîche et équilibrée riche en fibres et protéines végétales.',
                    'image' => null,
                    'calories' => 350,
                    'prepTime' => 15,
                    'servings' => 2,
                    'proteins' => 12,
                    'carbs' => 45,
                    'fats' => 14,
                    'rating' => 4.8,
                    'isFavorite' => true,
                    'diets' => ['Végétarien', 'Sans gluten'],
                    'ingredients' => ['Quinoa 150g', 'Concombre 1', 'Tomates cerises 100g', 'Feta 50g', 'Olive oil 2cs'],
                    'instructions' => ['Cuire le quinoa', 'Couper les légumes', 'Mélanger', 'Assaisonner'],
                ],
                [
                    'id' => 2,
                    'name' => 'Poulet rôti aux herbes',
                    'description' => 'Poulet tender et juteux avec des herbes.',
                    'image' => null,
                    'calories' => 420,
                    'prepTime' => 10,
                    'servings' => 4,
                    'proteins' => 45,
                    'carbs' => 5,
                    'fats' => 22,
                    'rating' => 4.5,
                    'isFavorite' => false,
                    'diets' => ['Protéiné'],
                    'ingredients' => ['Poulet 1.5kg', 'Romarin', 'Thym', 'Ail', 'Beurre'],
                    'instructions' => ['Préchauffer le four', 'Assaisonner', 'Cuire', 'Servir'],
                ],
                [
                    'id' => 3,
                    'name' => 'Smoothie énergétique',
                    'description' => 'Smoothie avec des fruits rouges et miel.',
                    'image' => null,
                    'calories' => 280,
                    'prepTime' => 5,
                    'servings' => 1,
                    'proteins' => 8,
                    'carbs' => 45,
                    'fats' => 6,
                    'rating' => 4.6,
                    'isFavorite' => true,
                    'diets' => ['Végétarien'],
                    'ingredients' => ['Fruits rouges 200g', 'Banane 1', 'Lait amande', 'Miel', 'Graines de chia'],
                    'instructions' => ['Laver les fruits', 'Mélanger', 'Verser', 'Déguster'],
                ],
            ],
            'favoriteCount' => 12,
            'weeklyMade' => 5,
            'newRecipes' => 8,
            'badges' => [
                ['name' => '7 jours consécutifs', 'icon' => 'fa-fire'],
                ['name' => 'Objectif atteint', 'icon' => 'fa-trophy'],
                ['name' => 'Hydratation', 'icon' => 'fa-glass-water'],
            ],
        ]);
    }

    #[Route('/progress', name: 'progress')]
    public function progress(
        NutritionGoalRepository $nutritionGoalRepository,
        FoodItemRepository $foodItemRepository,
        WaterLogRepository $waterLogRepository
    ): Response {
        // Get current user (assuming patient is logged in)
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            // For demo purposes, get first patient from database
            $patient = $foodItemRepository->findOneBy([])?->getPatient();
            if (!$patient) {
                $patient = null;
            }
        } else {
            $patient = $user;
        }

        // Get active nutrition goal
        $nutritionGoal = null;
        $calorieTarget = 2000;
        $proteinTarget = 120;
        $carbTarget = 250;
        $fatTarget = 70;

        if ($patient) {
            $nutritionGoal = $nutritionGoalRepository->findOneBy([
                'patient' => $patient,
                'isActive' => true
            ]);

            if ($nutritionGoal) {
                $calorieTarget = $nutritionGoal->getDailyCalories();
                $proteinTarget = (float)$nutritionGoal->getProteinPercent();
                $carbTarget = (float)$nutritionGoal->getCarbPercent();
                $fatTarget = (float)$nutritionGoal->getFatPercent();
            }
        }

        // Calculate streaks
        $streaks = [
            'logging' => 0,
            'water' => 0,
            'veggies' => 0,
        ];

        if ($patient) {
            // Logging streak: consecutive days with food logged
            $today = new DateTime('today');
            $loggingStreak = 0;
            for ($i = 0; $i < 30; $i++) { // Check last 30 days
                $date = clone $today;
                $date->modify("-{$i} days");
                $foodItems = $foodItemRepository->findBy([
                    'patient' => $patient
                ]);
                $hasLog = false;
                foreach ($foodItems as $item) {
                    if ($item->getLoggedAt() && $item->getLoggedAt()->format('Y-m-d') === $date->format('Y-m-d')) {
                        $hasLog = true;
                        break;
                    }
                }
                if ($hasLog) {
                    $loggingStreak++;
                } else {
                    break;
                }
            }
            $streaks['logging'] = $loggingStreak;

            // Water streak: consecutive days with water logged
            $waterStreak = 0;
            for ($i = 0; $i < 30; $i++) {
                $date = clone $today;
                $date->modify("-{$i} days");
                $waterLog = $waterLogRepository->findOneBy([
                    'patient' => $patient,
                    'logDate' => $date
                ]);
                if ($waterLog && $waterLog->getGlasses() >= 6) { // Assuming 6 glasses is good
                    $waterStreak++;
                } else {
                    break;
                }
            }
            $streaks['water'] = $waterStreak;

            // Veggies streak: consecutive days with vegetable items logged
            $veggieStreak = 0;
            for ($i = 0; $i < 30; $i++) {
                $date = clone $today;
                $date->modify("-{$i} days");
                $foodItems = $foodItemRepository->findBy([
                    'patient' => $patient
                ]);
                $hasVeggies = false;
                foreach ($foodItems as $item) {
                    if ($item->getLoggedAt() && $item->getLoggedAt()->format('Y-m-d') === $date->format('Y-m-d')) {
                        // Simple check: if item name contains vegetable keywords
                        $name = strtolower($item->getNomItem());
                        if (strpos($name, 'salade') !== false || strpos($name, 'tomate') !== false ||
                            strpos($name, 'carotte') !== false || strpos($name, 'brocoli') !== false ||
                            strpos($name, 'épinard') !== false || strpos($name, 'concombre') !== false) {
                            $hasVeggies = true;
                            break;
                        }
                    }
                }
                if ($hasVeggies) {
                    $veggieStreak++;
                } else {
                    break;
                }
            }
            $streaks['veggies'] = $veggieStreak;
        }

        // Default badges (could be calculated based on achievements)
        $badges = [];
        if ($streaks['logging'] >= 7) {
            $badges[] = ['name' => '7 jours consécutifs', 'icon' => 'fa-fire'];
        }
        if ($streaks['logging'] >= 30) {
            $badges[] = ['name' => '30 jours consécutifs', 'icon' => 'fa-calendar-check'];
        }
        if ($streaks['water'] >= 7) {
            $badges[] = ['name' => 'Hydratation', 'icon' => 'fa-glass-water'];
        }
        if ($streaks['veggies'] >= 7) {
            $badges[] = ['name' => 'Légumes', 'icon' => 'fa-carrot'];
        }
        $badges[] = ['name' => 'Objectif atteint', 'icon' => 'fa-trophy'];

        // Weight progress (simplified - using current patient weight)
        $weightProgress = [
            'start' => $patient ? $patient->getWeight() : 70,
            'current' => $patient ? $patient->getWeight() : 70,
            'target' => 65,
            'lost' => 0,
            'nextGoal' => 68,
            'percentage' => 0,
        ];

        if ($patient && $nutritionGoal) {
            // Assuming start weight is initial weight, target is calculated
            $startWeight = $patient->getWeight(); // This should be initial weight
            $currentWeight = $patient->getWeight();
            $targetWeight = $startWeight * 0.9; // Example: 10% loss target
            $lost = $startWeight - $currentWeight;
            $percentage = $lost > 0 ? min(100, ($lost / ($startWeight - $targetWeight)) * 100) : 0;

            $weightProgress = [
                'start' => $startWeight,
                'current' => $currentWeight,
                'target' => $targetWeight,
                'lost' => $lost,
                'nextGoal' => $currentWeight - 2, // Next milestone
                'percentage' => $percentage,
            ];
        }

        // Calculate recent achievements based on streaks and progress
        $achievements = [];
        $today = new DateTime('today');

        if ($streaks['logging'] >= 7) {
            $achievements[] = [
                'icon' => 'fa-fire',
                'title' => '7 jours consécutifs !',
                'description' => 'Vous avez tenu votre journal alimentaire pendant 7 jours d\'affilée.',
                'date' => clone $today,
                'dateText' => 'Il y a 2 jours'
            ];
        }

        if ($streaks['water'] >= 7) {
            $achievements[] = [
                'icon' => 'fa-glass-water',
                'title' => 'Objectif hydratation atteint !',
                'description' => 'Vous avez bu 8 verres d\'eau par jour pendant une semaine.',
                'date' => clone $today,
                'dateText' => 'Il y a 5 jours'
            ];
        }

        if ($streaks['veggies'] >= 7) {
            $achievements[] = [
                'icon' => 'fa-carrot',
                'title' => 'Légumes quotidiens !',
                'description' => 'Vous avez mangé des légumes tous les jours cette semaine.',
                'date' => clone $today,
                'dateText' => 'Il y a 1 semaine'
            ];
        }

        // Add goal achievement if weight progress is good
        if ($weightProgress['percentage'] >= 50) {
            $achievements[] = [
                'icon' => 'fa-trophy',
                'title' => 'Objectif de poids atteint !',
                'description' => 'Vous avez atteint 50% de votre objectif de perte de poids.',
                'date' => clone $today,
                'dateText' => 'Il y a 3 jours'
            ];
        }

        // Sort achievements by date (most recent first)
        usort($achievements, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        // Prepare chart data for goals progress (weight over time)
        // Since we don't have historical data, we'll create sample data
        $chartData = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Poids (kg)',
                    'data' => [],
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4
                ]
            ]
        ];

        // Generate sample weight data for the last 4 weeks
        $currentWeight = $patient ? $patient->getWeight() : 70;
        for ($i = 3; $i >= 0; $i--) {
            $date = clone $today;
            $date->modify("-{$i} weeks");
            $chartData['labels'][] = 'Sem ' . (4 - $i);

            // Simulate gradual weight loss
            $simulatedWeight = $currentWeight - ($i * 0.5);
            $chartData['datasets'][0]['data'][] = round($simulatedWeight, 1);
        }

        return $this->render('nutrition/progress.html.twig', [
            'streaks' => $streaks,
            'badges' => $badges,
            'weightProgress' => $weightProgress,
            'achievements' => $achievements,
            'chartData' => $chartData,
        ]);
    }

    #[Route('/analysis', name: 'analysis')]
    public function analysis(
        NutritionGoalRepository $nutritionGoalRepository,
        FoodItemRepository $foodItemRepository,
        WaterLogRepository $waterLogRepository
    ): Response {
        // Get current user (assuming patient is logged in)
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            // For demo purposes, get first patient from database
            $patient = $foodItemRepository->findOneBy([])?->getPatient();
            if (!$patient) {
                $patient = null;
            }
        } else {
            $patient = $user;
        }

        // Get active nutrition goal
        $nutritionGoal = null;
        $calorieTarget = 2000;
        $proteinTarget = 120;
        $carbTarget = 250;
        $fatTarget = 70;

        if ($patient) {
            $nutritionGoal = $nutritionGoalRepository->findOneBy([
                'patient' => $patient,
                'isActive' => true
            ]);

            if ($nutritionGoal) {
                $calorieTarget = $nutritionGoal->getDailyCalories();
                $proteinTarget = (float)$nutritionGoal->getProteinPercent();
                $carbTarget = (float)$nutritionGoal->getCarbPercent();
                $fatTarget = (float)$nutritionGoal->getFatPercent();
            }
        }

        // Calculate weekly averages
        $weeklyAverage = [
            'calories' => 1850,
            'proteins' => 85,
            'carbs' => 180,
            'fats' => 60,
        ];

        if ($patient) {
            // Calculate actual weekly averages from food items
            $weekAgo = new DateTime('-7 days');
            $foodItems = $foodItemRepository->findByPatientAndDateRange($patient, $weekAgo, new DateTime());

            $totalCalories = 0;
            $totalProteins = 0;
            $totalCarbs = 0;
            $totalFats = 0;
            $itemCount = count($foodItems);

            foreach ($foodItems as $item) {
                $totalCalories += $item->getCalories();
                $totalProteins += (float)$item->getProtein();
                $totalCarbs += (float)$item->getCarbs();
                $totalFats += (float)$item->getFat();
            }

            if ($itemCount > 0) {
                $weeklyAverage = [
                    'calories' => round($totalCalories / $itemCount),
                    'proteins' => round($totalProteins / $itemCount),
                    'carbs' => round($totalCarbs / $itemCount),
                    'fats' => round($totalFats / $itemCount),
                ];
            }
        }

        // Calculate progress percentages for goals
        $goals = [
            [
                'name' => 'Calories',
                'target' => $calorieTarget,
                'current' => $weeklyAverage['calories'],
                'progress' => $calorieTarget > 0 ? min(100, round(($weeklyAverage['calories'] / $calorieTarget) * 100)) : 0
            ],
            [
                'name' => 'Proteins',
                'target' => $proteinTarget,
                'current' => $weeklyAverage['proteins'],
                'progress' => $proteinTarget > 0 ? min(100, round(($weeklyAverage['proteins'] / $proteinTarget) * 100)) : 0
            ],
            [
                'name' => 'Carbs',
                'target' => $carbTarget,
                'current' => $weeklyAverage['carbs'],
                'progress' => $carbTarget > 0 ? min(100, round(($weeklyAverage['carbs'] / $carbTarget) * 100)) : 0
            ],
            [
                'name' => 'Fats',
                'target' => $fatTarget,
                'current' => $weeklyAverage['fats'],
                'progress' => $fatTarget > 0 ? min(100, round(($weeklyAverage['fats'] / $fatTarget) * 100)) : 0
            ],
        ];

        // Sample weekly trends data (in production, calculate from actual data)
        $weeklyTrends = [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'calories' => [1850, 1920, 1780, 1950, 1820, 1880, 1900],
            'proteins' => [85, 90, 78, 95, 82, 88, 92],
        ];

        // Sample health insights
        $healthInsights = [
            [
                'type' => 'success',
                'message' => 'Great protein intake this week!',
                'recommendation' => 'Keep up the good work with lean proteins.'
            ],
            [
                'type' => 'info',
                'message' => 'Consider increasing vegetable intake',
                'recommendation' => 'Add more colorful vegetables to your meals.'
            ],
            [
                'type' => 'warning',
                'message' => 'Carb intake slightly above target',
                'recommendation' => 'Focus on complex carbs like whole grains.'
            ],
        ];

        // Sample meal patterns
        $mealPatterns = [
            'breakfast' => [
                'avgCalories' => 450,
                'avgTime' => '8:30',
                'regularity' => 'high'
            ],
            'lunch' => [
                'avgCalories' => 650,
                'avgTime' => '12:45',
                'regularity' => 'high'
            ],
            'dinner' => [
                'avgCalories' => 550,
                'avgTime' => '19:30',
                'regularity' => 'medium'
            ],
            'snacks' => [
                'avgCalories' => 200,
                'avgTime' => '16:00',
                'regularity' => 'low'
            ],
        ];

        // Sample nutrient timing
        $nutrientTiming = [
            'morning' => ['calories' => 450, 'protein' => 25, 'carbs' => 45],
            'afternoon' => ['calories' => 850, 'protein' => 45, 'carbs' => 85],
            'evening' => ['calories' => 550, 'protein' => 35, 'carbs' => 55],
        ];

        return $this->render('nutrition/nutrition-analysis.html.twig', [
            'patient' => $patient ? [
                'id' => $patient->getId(),
                'name' => $patient->getName(),
                'age' => $patient ? (new DateTime())->diff($patient->getBirthDate())->y : 30,
                'weight' => $patient ? $patient->getWeight() : 70,
                'height' => $patient ? $patient->getHeight() : 170,
                'bmi' => $patient ? round($patient->getWeight() / (($patient->getHeight() / 100) ** 2), 1) : 24.5,
            ] : null,
            'weeklyAverage' => $weeklyAverage,
            'goals' => $goals,
            'weeklyTrends' => $weeklyTrends,
            'healthInsights' => $healthInsights,
            'mealPatterns' => $mealPatterns,
            'nutrientTiming' => $nutrientTiming,
        ]);
    }

    #[Route('/goals', name: 'goals')]
    public function goals(
        NutritionGoalRepository $nutritionGoalRepository,
        FoodItemRepository $foodItemRepository,
        WaterLogRepository $waterLogRepository
    ): Response {
        // Get current user (assuming patient is logged in)
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            // For demo purposes, get first patient from database
            $patient = $foodItemRepository->findOneBy([])?->getPatient();
            if (!$patient) {
                $patient = null;
            }
        } else {
            $patient = $user;
        }

        // Get active nutrition goal
        $nutritionGoal = null;
        $calorieTarget = 2000;
        $proteinTarget = 120;
        $carbTarget = 250;
        $fatTarget = 70;

        if ($patient) {
            $nutritionGoal = $nutritionGoalRepository->findOneBy([
                'patient' => $patient,
                'isActive' => true
            ]);

            if ($nutritionGoal) {
                $calorieTarget = $nutritionGoal->getDailyCalories();
                $proteinTarget = (float)$nutritionGoal->getProteinPercent();
                $carbTarget = (float)$nutritionGoal->getCarbPercent();
                $fatTarget = (float)$nutritionGoal->getFatPercent();
            }
        }

        // Calculate streaks
        $streaks = [
            'logging' => 0,
            'water' => 0,
            'veggies' => 0,
        ];

        if ($patient) {
            // Logging streak: consecutive days with food logged
            $today = new DateTime('today');
            $loggingStreak = 0;
            for ($i = 0; $i < 30; $i++) { // Check last 30 days
                $date = clone $today;
                $date->modify("-{$i} days");
                $foodItems = $foodItemRepository->findBy([
                    'patient' => $patient
                ]);
                $hasLog = false;
                foreach ($foodItems as $item) {
                    if ($item->getLoggedAt() && $item->getLoggedAt()->format('Y-m-d') === $date->format('Y-m-d')) {
                        $hasLog = true;
                        break;
                    }
                }
                if ($hasLog) {
                    $loggingStreak++;
                } else {
                    break;
                }
            }
            $streaks['logging'] = $loggingStreak;

            // Water streak: consecutive days with water logged
            $waterStreak = 0;
            for ($i = 0; $i < 30; $i++) {
                $date = clone $today;
                $date->modify("-{$i} days");
                $waterLog = $waterLogRepository->findOneBy([
                    'patient' => $patient,
                    'logDate' => $date
                ]);
                if ($waterLog && $waterLog->getGlasses() >= 6) { // Assuming 6 glasses is good
                    $waterStreak++;
                } else {
                    break;
                }
            }
            $streaks['water'] = $waterStreak;

            // Veggies streak: consecutive days with vegetable items logged
            $veggieStreak = 0;
            for ($i = 0; $i < 30; $i++) {
                $date = clone $today;
                $date->modify("-{$i} days");
                $foodItems = $foodItemRepository->findBy([
                    'patient' => $patient
                ]);
                $hasVeggies = false;
                foreach ($foodItems as $item) {
                    if ($item->getLoggedAt() && $item->getLoggedAt()->format('Y-m-d') === $date->format('Y-m-d')) {
                        // Simple check: if item name contains vegetable keywords
                        $name = strtolower($item->getNomItem());
                        if (strpos($name, 'salade') !== false || strpos($name, 'tomate') !== false ||
                            strpos($name, 'carotte') !== false || strpos($name, 'brocoli') !== false ||
                            strpos($name, 'épinard') !== false || strpos($name, 'concombre') !== false) {
                            $hasVeggies = true;
                            break;
                        }
                    }
                }
                if ($hasVeggies) {
                    $veggieStreak++;
                } else {
                    break;
                }
            }
            $streaks['veggies'] = $veggieStreak;
        }

        // Default badges (could be calculated based on achievements)
        $badges = [];
        if ($streaks['logging'] >= 7) {
            $badges[] = ['name' => '7 jours consécutifs', 'icon' => 'fa-fire'];
        }
        if ($streaks['logging'] >= 30) {
            $badges[] = ['name' => '30 jours consécutifs', 'icon' => 'fa-calendar-check'];
        }
        if ($streaks['water'] >= 7) {
            $badges[] = ['name' => 'Hydratation', 'icon' => 'fa-glass-water'];
        }
        if ($streaks['veggies'] >= 7) {
            $badges[] = ['name' => 'Légumes', 'icon' => 'fa-carrot'];
        }
        $badges[] = ['name' => 'Objectif atteint', 'icon' => 'fa-trophy'];

        // Weight progress (simplified - using current patient weight)
        $weightProgress = [
            'start' => $patient ? $patient->getWeight() : 70,
            'current' => $patient ? $patient->getWeight() : 70,
            'target' => 65,
            'lost' => 0,
            'nextGoal' => 68,
            'percentage' => 0,
        ];

        if ($patient && $nutritionGoal) {
            // Assuming start weight is initial weight, target is calculated
            $startWeight = $patient->getWeight(); // This should be initial weight
            $currentWeight = $patient->getWeight();
            $targetWeight = $startWeight * 0.9; // Example: 10% loss target
            $lost = $startWeight - $currentWeight;
            $percentage = $lost > 0 ? min(100, ($lost / ($startWeight - $targetWeight)) * 100) : 0;

            $weightProgress = [
                'start' => $startWeight,
                'current' => $currentWeight,
                'target' => $targetWeight,
                'lost' => $lost,
                'nextGoal' => $currentWeight - 2, // Next milestone
                'percentage' => $percentage,
            ];
        }

        return $this->render('nutrition/goals.html.twig', [
            'streaks' => $streaks,
            'badges' => $badges,
            'weightProgress' => $weightProgress,
            'macroGoals' => [
                'proteins' => $proteinTarget,
                'carbs' => $carbTarget,
                'fats' => $fatTarget,
            ],
            'dailyTargets' => [
                'calories' => $calorieTarget,
                'fiber' => 25,
                'sugar' => 25,
                'sodium' => 2000,
            ],
            'calculator' => [
                'gender' => $patient ? ($patient->getGender() === 'F' ? 'female' : 'male') : 'female',
                'age' => $patient ? (new DateTime())->diff($patient->getBirthDate())->y : 30,
                'weight' => $patient ? $patient->getWeight() : 70,
                'height' => $patient ? $patient->getHeight() : 170,
                'activityLevel' => 'moderate',
            ],
        ]);
    }

    #[Route('/messages/{nutritionistId}', name: 'messages', requirements: ['nutritionistId' => '\d+'])]
    public function messages(int $nutritionistId): Response
    {
        return $this->render('nutrition/messages.html.twig', [
            'nutritionistId' => $nutritionistId,
            'nutritionist' => [
                'id' => $nutritionistId,
                'name' => 'Dr. Marie Dubois',
                'avatar' => null,
                'specialty' => 'Nutritionniste',
                'status' => 'online',
            ],
            'messages' => [
                [
                    'id' => 1,
                    'sender' => 'nutritionist',
                    'content' => 'Bonjour ! Comment se passe votre suivi alimentaire cette semaine ?',
                    'timestamp' => new DateTime('-2 days 10:30'),
                    'read' => true,
                ],
                [
                    'id' => 2,
                    'sender' => 'patient',
                    'content' => 'Bonjour Dr. Dubois, je suis content de mes progrès.',
                    'timestamp' => new DateTime('-2 days 11:00'),
                    'read' => true,
                ],
            ],
        ]);
    }

    #[Route('/consultation', name: 'consultation')]
    public function consultation(): Response
    {
        return $this->render('nutrition/consultation.html.twig', [
            'nutritionist' => [
                'id' => 1,
                'name' => 'Dr. Marie Dubois',
                'avatar' => null,
                'specialty' => 'Nutritionniste',
                'status' => 'available',
                'nextAvailable' => new DateTime('+1 hour'),
            ],
            'upcomingAppointments' => [
                [
                    'id' => 1,
                    'date' => new DateTime('+3 days 14:00'),
                    'duration' => 30,
                    'type' => 'video',
                    'status' => 'confirmed',
                ],
            ],
        ]);
    }

    // ============ NUTRITIONIST ROUTES ============

    #[Route('/nutritionniste/dashboard', name: 'nutritionniste_dashboard')]
    public function nutritionistDashboard(): Response
    {
        return $this->render('nutritionniste/dashboard.html.twig', [
            'stats' => [
                'totalPatients' => 45,
                'activePlans' => 38,
                'pendingMessages' => 12,
                'appointmentsToday' => 5,
            ],
            'recentPatients' => [
                ['id' => 1, 'name' => 'Sophie Martin', 'goal' => 'Perte de poids', 'status' => 'active', 'progress' => 25],
                ['id' => 2, 'name' => 'Jean Dupont', 'goal' => 'Prise de masse', 'status' => 'active', 'progress' => 40],
                ['id' => 3, 'name' => 'Marie Curie', 'goal' => 'Équilibre alimentaire', 'status' => 'pending', 'progress' => 10],
            ],
            'todayAppointments' => [
                ['id' => 1, 'time' => '09:00', 'patientName' => 'Sophie Martin', 'type' => 'Suivi'],
                ['id' => 2, 'time' => '10:30', 'patientName' => 'Jean Dupont', 'type' => 'Consultation'],
            ],
            'pendingReviews' => 8,
            'messages' => [
                ['id' => 1, 'from' => 'Sophie Martin', 'preview' => 'Merci pour vos conseils...', 'time' => '10 min'],
            ],
        ]);
    }

    #[Route('/nutritionniste/patients', name: 'nutritionniste_patients')]
    public function nutritionistPatients(): Response
    {
        return $this->render('nutritionniste/patient-list.html.twig', [
            'patients' => [
                ['id' => 1, 'name' => 'Sophie Martin', 'email' => 'sophie@email.com', 'goal' => 'Perte de poids', 'status' => 'active', 'startDate' => '2024-01-15', 'progress' => 25],
                ['id' => 2, 'name' => 'Jean Dupont', 'email' => 'jean@email.com', 'goal' => 'Prise de masse', 'status' => 'active', 'startDate' => '2024-02-01', 'progress' => 40],
            ],
            'totalPatients' => 45,
            'activePatients' => 38,
            'pendingPatients' => 7,
        ]);
    }

    #[Route('/nutritionniste/patient/{id}', name: 'nutritionniste_patient_view', requirements: ['id' => '\d+'])]
    public function nutritionistPatientView(int $id): Response
    {
        return $this->render('nutritionniste/patient-detail.html.twig', [
            'patient' => [
                'id' => $id,
                'name' => 'Sophie Martin',
                'email' => 'sophie@email.com',
                'age' => 32,
                'status' => 'active',
                'goal' => 'Perte de poids',
                'initialWeight' => 78,
                'currentWeight' => 72,
                'targetWeight' => 65,
                'bmi' => 24.5,
                'startDate' => new DateTime('2024-01-15'),
            ],
        ]);
    }

    #[Route('/nutritionniste/meal-plan/new', name: 'nutritionniste_meal_plan_new')]
    public function nutritionistMealPlanNew(): Response
    {
        return $this->render('nutritionniste/meal-plan-builder.html.twig', [
            'patients' => [
                ['id' => 1, 'name' => 'Sophie Martin'],
                ['id' => 2, 'name' => 'Jean Dupont'],
            ],
            'templates' => [
                ['id' => 1, 'name' => 'Perte de poids équilibrée', 'description' => 'Plan hypocalorique', 'calories' => 1800],
                ['id' => 2, 'name' => 'Prise de masse', 'description' => 'Plan hypercalorique', 'calories' => 2800],
            ],
        ]);
    }

    #[Route('/nutritionniste/analysis/{patientId}', name: 'nutritionniste_analysis', requirements: ['patientId' => '\d+'])]
    public function nutritionistAnalysis(int $patientId): Response
    {
        return $this->render('nutritionniste/nutrition-analysis.html.twig', [
            'patient' => ['id' => $patientId, 'name' => 'Sophie Martin', 'age' => 32, 'weight' => 72, 'height' => 165, 'bmi' => 24.5],
            'weeklyAverage' => ['calories' => 1850, 'proteins' => 85, 'carbs' => 180, 'fats' => 60],
            'goals' => ['calories' => ['target' => 1800, 'current' => 1850], 'proteins' => ['target' => 100, 'current' => 85]],
        ]);
    }

    #[Route('/nutritionniste/messages', name: 'nutritionniste_messages')]
    public function nutritionistMessages(): Response
    {
        return $this->render('nutritionniste/communication.html.twig', [
            'conversations' => [
                ['id' => 1, 'patientName' => 'Sophie Martin', 'lastMessage' => 'Merci pour vos conseils !', 'time' => '10 min', 'unread' => true],
            ],
        ]);
    }

    #[Route('/nutritionniste/reports', name: 'nutritionniste_reports')]
    public function nutritionistReports(): Response
    {
        return $this->render('nutritionniste/reporting.html.twig', [
            'reports' => [
                ['id' => 1, 'title' => 'Rapport mensuel - Sophie Martin', 'patient' => 'Sophie Martin', 'date' => new DateTime('2024-02-01'), 'type' => 'monthly', 'status' => 'completed'],
            ],
            'pendingReports' => 5,
            'completedThisMonth' => 12,
        ]);
    }

    // ============ API ROUTES FOR DASHBOARD INTERACTIONS ============

    /**
     * @OA\Post(
     *     path="/nutrition/api/nutrition/water",
     *     summary="Update water intake for the current patient",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="intake", type="integer", example=6, description="Number of glasses of water")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Water intake updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="intake", type="integer", example=6)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found"
     *     )
     * )
     */
    #[Route('/api/nutrition/water', name: 'api_water_update', methods: ['POST'])]
    public function updateWaterIntake(Request $request, WaterLogRepository $waterLogRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $glasses = $data['intake'] ?? 0;

        // Get current user
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            $patient = $waterLogRepository->findOneBy([])?->getPatient();
            if (!$patient) {
                // Create demo patient for testing
                $patient = new Patient();
                $patient->setName('Demo Patient');
                $patient->setEmail('demo@example.com');
                $patient->setBirthDate(new DateTime('1990-01-01'));
                $patient->setGender('M');
                $patient->setWeight(70);
                $patient->setHeight(170);
                $entityManager = $waterLogRepository->getEntityManager();
                $entityManager->persist($patient);
                $entityManager->flush();
            }
        } else {
            $patient = $user;
        }

        $today = new DateTime('today');
        $waterLog = $waterLogRepository->findOneBy([
            'patient' => $patient,
            'logDate' => $today
        ]);

        if (!$waterLog) {
            $waterLog = new \App\Entity\WaterLog();
            $waterLog->setPatient($patient);
            $waterLog->setLogDate($today);
        }

        $waterLog->setGlasses($glasses);
        $waterLogRepository->save($waterLog, true);

        return new JsonResponse(['success' => true, 'intake' => $glasses]);
    }

    /**
     * @OA\Post(
     *     path="/nutrition/api/nutrition/food",
     *     summary="Add a food item to the patient's log",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Apple", description="Name of the food item"),
     *             @OA\Property(property="calories", type="integer", example=95, description="Calories in the food item"),
     *             @OA\Property(property="proteins", type="number", format="float", example=0.5, description="Protein content in grams"),
     *             @OA\Property(property="carbs", type="number", format="float", example=25, description="Carbohydrate content in grams"),
     *             @OA\Property(property="fats", type="number", format="float", example=0.3, description="Fat content in grams"),
     *             @OA\Property(property="meal", type="string", example="breakfast", description="Meal type (breakfast, lunch, dinner, snacks)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Food item added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="food", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Apple"),
     *                 @OA\Property(property="calories", type="integer", example=95),
     *                 @OA\Property(property="meal", type="string", example="breakfast")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found"
     *     )
     * )
     */
    #[Route('/api/nutrition/food', name: 'api_food_add', methods: ['POST'])]
    public function addFoodItem(Request $request, FoodItemRepository $foodItemRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Get current user
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            $patient = $foodItemRepository->findOneBy([])?->getPatient();
            if (!$patient) {
                // Create demo patient for testing
                $patient = new Patient();
                $patient->setName('Demo Patient');
                $patient->setEmail('demo@example.com');
                $patient->setBirthDate(new DateTime('1990-01-01'));
                $patient->setGender('M');
                $patient->setWeight(70);
                $patient->setHeight(170);
                $entityManager = $foodItemRepository->getEntityManager();
                $entityManager->persist($patient);
                $entityManager->flush();
            }
        } else {
            $patient = $user;
        }

        $foodItem = new \App\Entity\FoodItem();
        $foodItem->setPatient($patient);
        $foodItem->setNomItem($data['name']);
        $foodItem->setCalories($data['calories']);
        $foodItem->setProtein((string)($data['proteins'] ?? 0));
        $foodItem->setCarbs((string)($data['carbs'] ?? 0));
        $foodItem->setFat((string)($data['fats'] ?? 0));
        $foodItem->setMealType($data['meal'] ?? 'snacks');
        $foodItem->setLoggedAt(new DateTime());

        $entityManager->persist($foodItem);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'food' => [
                'id' => $foodItem->getId(),
                'name' => $foodItem->getNomItem(),
                'calories' => $foodItem->getCalories(),
                'meal' => $foodItem->getMealType()
            ]
        ]);
    }

    #[Route('/api/nutrition/food/search', name: 'api_food_search', methods: ['GET'])]
    public function searchFoods(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        // For demo purposes, return some sample foods
        // In a real app, this would search a food database
        $foods = [
            ['id' => 1, 'name' => 'Pomme', 'calories' => 95, 'brand' => 'Générique', 'portion' => '1', 'unit' => 'pièce'],
            ['id' => 2, 'name' => 'Banane', 'calories' => 105, 'brand' => 'Générique', 'portion' => '1', 'unit' => 'pièce'],
            ['id' => 3, 'name' => 'Poulet grillé', 'calories' => 165, 'brand' => 'Générique', 'portion' => '100', 'unit' => 'g'],
            ['id' => 4, 'name' => 'Riz blanc cuit', 'calories' => 130, 'brand' => 'Générique', 'portion' => '100', 'unit' => 'g'],
            ['id' => 5, 'name' => 'Salade verte', 'calories' => 15, 'brand' => 'Générique', 'portion' => '100', 'unit' => 'g'],
        ];

        // Filter foods based on query
        if (!empty($query)) {
            $foods = array_filter($foods, function($food) use ($query) {
                return stripos($food['name'], $query) !== false;
            });
        }

        return new JsonResponse(array_values($foods));
    }

    /**
     * @OA\Delete(
     *     path="/nutrition/api/nutrition/food/{id}",
     *     summary="Remove a food item from the patient's log",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the food item to remove"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Food item removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Food item not found"
     *     )
     * )
     */
    #[Route('/api/nutrition/food/{id}', name: 'api_food_delete', methods: ['DELETE'])]
    public function removeFoodItem(int $id, FoodItemRepository $foodItemRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $foodItem = $foodItemRepository->find($id);

        if (!$foodItem) {
            return new JsonResponse(['success' => false, 'message' => 'Food item not found'], 404);
        }

        // Check if the current user owns this food item
        $user = $this->getUser();
        if ($user instanceof Patient && $foodItem->getPatient() !== $user) {
            return new JsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $entityManager->remove($foodItem);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * @OA\Post(
     *     path="/nutrition/api/nutrition/goals",
     *     summary="Update nutrition goals for the current patient",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="dailyCalories", type="integer", example=2000, description="Daily calorie target"),
     *             @OA\Property(property="proteinPercent", type="number", format="float", example=25.0, description="Protein percentage"),
     *             @OA\Property(property="carbPercent", type="number", format="float", example=50.0, description="Carbohydrate percentage"),
     *             @OA\Property(property="fatPercent", type="number", format="float", example=25.0, description="Fat percentage")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Goals updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    #[Route('/api/nutrition/goals', name: 'api_goals_update', methods: ['POST'])]
    public function updateNutritionGoals(Request $request, NutritionGoalRepository $nutritionGoalRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Get current user
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            $patient = $nutritionGoalRepository->findOneBy([])?->getPatient();
            if (!$patient) {
                // Create demo patient for testing
                $patient = new Patient();
                $patient->setName('Demo Patient');
                $patient->setEmail('demo@example.com');
                $patient->setBirthDate(new DateTime('1990-01-01'));
                $patient->setGender('M');
                $patient->setWeight(70);
                $patient->setHeight(170);
                $entityManager->persist($patient);
                $entityManager->flush();
            }
        } else {
            $patient = $user;
        }

        // Get or create nutrition goal
        $nutritionGoal = $nutritionGoalRepository->findOneBy([
            'patient' => $patient,
            'isActive' => true
        ]);

        if (!$nutritionGoal) {
            $nutritionGoal = new \App\Entity\NutritionGoal();
            $nutritionGoal->setPatient($patient);
            $nutritionGoal->setIsActive(true);
            $nutritionGoal->setCreatedAt(new DateTime());
        }

        $nutritionGoal->setDailyCalories($data['dailyCalories'] ?? 2000);
        $nutritionGoal->setProteinPercent((string)($data['proteinPercent'] ?? 25.0));
        $nutritionGoal->setCarbPercent((string)($data['carbPercent'] ?? 50.0));
        $nutritionGoal->setFatPercent((string)($data['fatPercent'] ?? 25.0));
        $nutritionGoal->setUpdatedAt(new DateTime());

        $entityManager->persist($nutritionGoal);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * @OA\Get(
     *     path="/nutrition/api/meal-planner/{weekStart}",
     *     summary="Get meal plan for a specific week",
     *     @OA\Parameter(
     *         name="weekStart",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="date", example="2024-01-01"),
     *         description="Start date of the week (Monday)"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Meal plan retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="weekPlan", type="object"),
     *             @OA\Property(property="weeklyStats", type="object")
     *         )
     *     )
     * )
     */
    #[Route('/api/meal-planner/{weekStart}', name: 'api_meal_planner_get', methods: ['GET'])]
    public function getMealPlan(string $weekStart, FoodPlanRepository $foodPlanRepository): JsonResponse
    {
        $startDate = new DateTime($weekStart);
        $endDate = clone $startDate;
        $endDate->modify('+6 days');

        // Get current user
        $user = $this->getUser();
        $patient = $user instanceof Patient ? $user : null;

        if (!$patient) {
            // For demo, get first patient
            $foodPlans = $foodPlanRepository->findAll();
            $patient = $foodPlans ? $foodPlans[0]->getNutritionGoal()->getPatient() : null;
        }

        $weekPlan = [
            'monday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
            'tuesday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
            'wednesday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
            'thursday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
            'friday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
            'saturday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
            'sunday' => ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snacks' => []],
        ];

        if ($patient) {
            $foodPlans = $foodPlanRepository->findBy([
                'nutritionGoal.patient' => $patient
            ]);

            foreach ($foodPlans as $foodPlan) {
                $planDate = $foodPlan->getPlanDate();
                if ($planDate >= $startDate && $planDate <= $endDate) {
                    $dayName = strtolower($planDate->format('l'));
                    $mealType = $foodPlan->getMealType();

                    if (isset($weekPlan[$dayName][$mealType])) {
                        $foodItems = $foodPlan->getFoodItems();
                        foreach ($foodItems as $foodItem) {
                            $weekPlan[$dayName][$mealType][] = [
                                'id' => $foodItem->getId(),
                                'name' => $foodItem->getNomItem(),
                                'calories' => $foodItem->getCalories(),
                                'proteins' => (float)$foodItem->getProtein(),
                                'carbs' => (float)$foodItem->getCarbs(),
                                'fats' => (float)$foodItem->getFat(),
                            ];
                        }
                    }
                }
            }
        }

        // Calculate weekly stats
        $weeklyStats = $this->calculateWeeklyStats($weekPlan);

        return new JsonResponse([
            'weekPlan' => $weekPlan,
            'weeklyStats' => $weeklyStats
        ]);
    }

    /**
     * @OA\Post(
     *     path="/nutrition/api/meal-planner",
     *     summary="Save meal plan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="weekStart", type="string", format="date", example="2024-01-01"),
     *             @OA\Property(property="weekPlan", type="object", description="Meal plan data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Meal plan saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    #[Route('/api/meal-planner', name: 'api_meal_planner_save', methods: ['POST'])]
    public function saveMealPlan(Request $request, FoodPlanRepository $foodPlanRepository, FoodItemRepository $foodItemRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $weekStart = new DateTime($data['weekStart']);
        $weekPlan = $data['weekPlan'];

        // Get current user
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            // For demo, get first patient
            $foodPlans = $foodPlanRepository->findAll();
            $patient = $foodPlans ? $foodPlans[0]->getNutritionGoal()->getPatient() : null;
            if (!$patient) {
                $patient = new Patient();
                $patient->setName('Demo Patient');
                $patient->setEmail('demo@example.com');
                $patient->setBirthDate(new DateTime('1990-01-01'));
                $patient->setGender('M');
                $patient->setWeight(70);
                $patient->setHeight(170);
                $entityManager->persist($patient);
                $entityManager->flush();
            }
        } else {
            $patient = $user;
        }

        // Get or create nutrition goal
        $nutritionGoal = $foodPlanRepository->findOneBy(['nutritionGoal.patient' => $patient])?->getNutritionGoal();
        if (!$nutritionGoal) {
            $nutritionGoal = new \App\Entity\NutritionGoal();
            $nutritionGoal->setPatient($patient);
            $nutritionGoal->setIsActive(true);
            $nutritionGoal->setDailyCalories(2000);
            $nutritionGoal->setProteinPercent('25.0');
            $nutritionGoal->setCarbPercent('50.0');
            $nutritionGoal->setFatPercent('25.0');
            $nutritionGoal->setCreatedAt(new DateTime());
            $entityManager->persist($nutritionGoal);
            $entityManager->flush();
        }

        // Clear existing plans for the week
        $endDate = clone $weekStart;
        $endDate->modify('+6 days');
        $existingPlans = $foodPlanRepository->findByDateRange($patient, $weekStart, $endDate);
        foreach ($existingPlans as $plan) {
            $entityManager->remove($plan);
        }
        $entityManager->flush();

        // Save new plans
        foreach ($weekPlan as $dayName => $dayMeals) {
            $dayOffset = array_search($dayName, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $planDate = clone $weekStart;
            $planDate->modify("+$dayOffset days");

            foreach ($dayMeals as $mealType => $meals) {
                if (!empty($meals)) {
                    $foodPlan = new \App\Entity\FoodPlan();
                    $foodPlan->setNutritionGoal($nutritionGoal);
                    $foodPlan->setPlanDate($planDate);
                    $foodPlan->setMealType($mealType);

                    foreach ($meals as $meal) {
                        $foodItem = $foodItemRepository->find($meal['id']);
                        if ($foodItem) {
                            $foodPlan->addFoodItem($foodItem);
                        }
                    }

                    $entityManager->persist($foodPlan);
                }
            }
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
