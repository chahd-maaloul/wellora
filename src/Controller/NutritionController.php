<?php

namespace App\Controller;

use App\Entity\FoodItem;
use App\Entity\FoodLog;
use App\Entity\NutritionGoal;
use App\Entity\WaterIntake;
use App\Form\FoodItemFormType;
use App\Form\FoodLogFormType;
use App\Form\NutritionGoalFormType;
use App\Form\WaterIntakeFormType;
use App\Repository\FoodItemRepository;
use App\Repository\FoodLogRepository;
use App\Repository\NutritionGoalRepository;
use App\Repository\WaterIntakeRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/nutrition', name: 'nutrition_')]
class NutritionController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function getUserId(): int
    {
        // For demo purposes, use a fixed user ID
        // In production, get from security context
        return 1;
    }

    #[Route('/', name: 'dashboard')]
    public function dashboard(
        Request $request,
        FoodLogRepository $foodLogRepository,
        WaterIntakeRepository $waterIntakeRepository,
        NutritionGoalRepository $nutritionGoalRepository
    ): Response {
        $userId = $this->getUserId();
        $today = new DateTime();
        
        // Get or create nutrition goal
        $nutritionGoal = $nutritionGoalRepository->findByUserId($userId);
        if (!$nutritionGoal) {
            $nutritionGoal = new NutritionGoal();
            $nutritionGoal->setUserId($userId);
            $nutritionGoal->setCaloriesTarget(2000);
            $nutritionGoal->setWaterTarget(8);
            $nutritionGoal->setProteinTarget(120);
            $nutritionGoal->setCarbsTarget(200);
            $nutritionGoal->setFatsTarget(65);
            $nutritionGoal->setFiberTarget(25);
            $nutritionGoal->setSugarTarget(25);
            $nutritionGoal->setSodiumTarget(2300);
            $this->entityManager->persist($nutritionGoal);
            $this->entityManager->flush();
        }

        // Get today's food logs
        $foodLogs = $foodLogRepository->findAllByUserIdAndDate($userId, $today);
        
        // Calculate totals
        $totalCalories = 0;
        $totalProteins = 0;
        $totalCarbs = 0;
        $totalFats = 0;
        
        $meals = [
            'breakfast' => ['calories' => 0, 'items' => []],
            'lunch' => ['calories' => 0, 'items' => []],
            'dinner' => ['calories' => 0, 'items' => []],
            'snacks' => ['calories' => 0, 'items' => []]
        ];
        
        foreach ($foodLogs as $foodLog) {
            $mealType = $foodLog->getMealType();
            if (!isset($meals[$mealType])) {
                $mealType = 'snacks';
            }
            
            $foodItems = $foodLog->getFoodItems();
            
            // Items inside FoodLog
            foreach ($foodItems as $item) {
                $totalCalories += $item->getCalories();
                $totalProteins += floatval($item->getProtein());
                $totalCarbs += floatval($item->getCarbs());
                $totalFats += floatval($item->getFats());
                
                $meals[$mealType]['calories'] += $item->getCalories();
                $meals[$mealType]['items'][] = [
                    'name' => $item->getName(),
                    'calories' => $item->getCalories(),
                    'id' => $item->getId(),
                    'category' => $item->getCategory()
                ];
            }
        }

        // Get water intake
        $waterIntake = $waterIntakeRepository->findByUserIdAndDate($userId, $today);
        $waterIntakeValue = $waterIntake ? $waterIntake->getGlasses() : 0;

        // Get recent foods for quick add
        $recentFoods = $foodLogRepository->findByUserId($userId);
        $quickAddFoods = [];
        $foodNames = [];
        foreach ($recentFoods as $log) {
            foreach ($log->getFoodItems() as $item) {
                if (!in_array($item->getName(), $foodNames)) {
                    $foodNames[] = $item->getName();
                    $quickAddFoods[] = [
                        'name' => $item->getName(),
                        'calories' => $item->getCalories(),
                        'unit' => $item->getUnit() ?: 'serving'
                    ];
                }
                if (count($quickAddFoods) >= 6) {
                    break 2;
                }
            }
        }

        return $this->render('nutrition/dashboard.html.twig', [
            'calories' => [
                'consumed' => $totalCalories,
                'target' => $nutritionGoal->getCaloriesTarget() ?? 2000
            ],
            'water' => [
                'intake' => $waterIntakeValue,
                'target' => $nutritionGoal->getWaterTarget() ?? 8
            ],
            'macros' => [
                'proteins' => $totalProteins,
                'carbs' => $totalCarbs,
                'fats' => $totalFats
            ],
            'meals' => $meals,
            'recentFoods' => array_slice($quickAddFoods, 0, 4),
            'quickAddFoods' => $quickAddFoods,
            'nutritionGoal' => $nutritionGoal,
        ]);
    }

    // ============ FOOD ITEM ROUTES ============

    #[Route('/food/add', name: 'food_add', methods: ['GET', 'POST'])]
    public function addFood(Request $request, FoodLogRepository $foodLogRepository): Response
    {
        $userId = $this->getUserId();
        $today = new DateTime();
        $mealType = $request->query->get('meal', 'breakfast');
        
        // Handle quick add via GET parameters
        $quickName = $request->query->get('name');
        $quickCalories = $request->query->getInt('calories', 0);
        $quickProtein = (float) $request->query->get('protein', 0);
        $quickCarbs = (float) $request->query->get('carbs', 0);
        $quickFats = (float) $request->query->get('fats', 0);
        
        // If quick add parameters are present, process them directly
        if ($quickName && $quickCalories > 0) {
            // Find or create FoodLog for this meal type
            $foodLog = $foodLogRepository->findOneBy([
                'userId' => $userId,
                'date' => $today,
                'mealType' => $mealType
            ]);
            
            if (!$foodLog) {
                $foodLog = new FoodLog();
                $foodLog->setUserId($userId);
                $foodLog->setDate($today);
                $foodLog->setMealType($mealType);
                $this->entityManager->persist($foodLog);
                $this->entityManager->flush();
            }
            
            // Check if this is from recipe library (has is_recipe parameter)
            $isFromRecipe = $request->query->getBoolean('is_recipe', false);
            
            // Create a new FoodItem for each entry (separate entry, no merging)
            $foodItem = new FoodItem();
            $foodItem->setFoodLog($foodLog);
            $foodItem->setName($quickName);
            $foodItem->setCalories($quickCalories);
            $foodItem->setProtein((string)$quickProtein);
            $foodItem->setCarbs((string)$quickCarbs);
            $foodItem->setFats((string)$quickFats);
            $foodItem->setQuantity('1');
            $foodItem->setUnit('portion');
            
            // Mark as recipe if from recipe library (read-only)
            if ($isFromRecipe) {
                $foodItem->setCategory('recipe');
            }
            
            $this->entityManager->persist($foodItem);
            $foodLog->calculateTotals();
            $this->entityManager->flush();
            
            $this->addFlash('success', $quickName . ' a été ajouté avec succès!');
            
            return $this->redirectToRoute('nutrition_dashboard');
        }
        
        // Handle manual form submission via POST
        $foodLog = $foodLogRepository->findOneBy([
            'userId' => $userId,
            'date' => $today,
            'mealType' => $mealType
        ]);
        
        if (!$foodLog) {
            $foodLog = new FoodLog();
            $foodLog->setUserId($userId);
            $foodLog->setDate($today);
            $foodLog->setMealType($mealType);
            $this->entityManager->persist($foodLog);
            $this->entityManager->flush();
        }
        
        $foodItem = new FoodItem();
        $foodItem->setFoodLog($foodLog);
        
        // Pre-fill form if query params exist
        if ($request->query->get('name')) {
            $foodItem->setName($request->query->get('name'));
        }
        if ($request->query->get('calories')) {
            $foodItem->setCalories((int)$request->query->get('calories'));
        }
        
        $form = $this->createForm(FoodItemFormType::class, $foodItem);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($foodItem);
            
            // Update food log totals
            $foodLog->calculateTotals();
            
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Aliment ajouté avec succès!');
            
            return $this->redirectToRoute('nutrition_dashboard');
        }
        
        return $this->render('nutrition/food-item-form.html.twig', [
            'form' => $form->createView(),
            'mealType' => $mealType,
            'action' => 'add'
        ]);
    }

    #[Route('/food/{id}/edit', name: 'food_edit', methods: ['GET', 'POST'])]
    public function editFood(Request $request, FoodItem $foodItem): Response
    {
        $form = $this->createForm(FoodItemFormType::class, $foodItem);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Update food log totals
            $foodLog = $foodItem->getFoodLog();
            if ($foodLog) {
                $foodLog->calculateTotals();
            }
            
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Food item updated successfully!');
            
            return $this->redirectToRoute('nutrition_dashboard');
        }
        
        return $this->render('nutrition/food-item-form.html.twig', [
            'form' => $form->createView(),
            'foodItem' => $foodItem,
            'action' => 'edit'
        ]);
    }

    #[Route('/food/{id}/delete', name: 'food_delete', methods: ['GET', 'POST'])]
    public function deleteFood(Request $request, FoodItem $foodItem): Response
    {
        $foodLog = $foodItem->getFoodLog();
        
        $this->entityManager->remove($foodItem);
        
        // Update totals
        if ($foodLog) {
            $foodLog->calculateTotals();
        }
        
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Food item deleted successfully!');
        
        return $this->redirectToRoute('nutrition_dashboard');
    }

    #[Route('/meal/{id}/delete', name: 'meal_delete', methods: ['GET', 'POST'])]
    public function deleteMeal(Request $request, FoodLog $foodLog): Response
    {
        $this->entityManager->remove($foodLog);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Repas supprimé avec succès!');
        
        return $this->redirectToRoute('nutrition_dashboard');
    }

    // ============ FOOD SEARCH API ============

    /**
     * Search foods by name - returns JSON for autocomplete
     */
    #[Route('/food/search', name: 'food_search', methods: ['GET'])]
    public function searchFoods(Request $request, FoodItemRepository $foodItemRepository): Response
    {
        $query = $request->query->get('q', '');
        $limit = $request->query->getInt('limit', 20);
        
        if (strlen($query) < 2) {
            return $this->json([]);
        }
        
        $foods = $foodItemRepository->searchByName($query, $limit);
        
        $results = array_map(function ($food) {
            return [
                'id' => $food->getId(),
                'name' => $food->getName(),
                'calories' => $food->getCalories(),
                'protein' => $food->getProtein(),
                'carbs' => $food->getCarbs(),
                'fats' => $food->getFats(),
                'unit' => $food->getUnit() ?? 'serving',
                'quantity' => $food->getQuantity() ?? 1,
            ];
        }, $foods);
        
        return $this->json($results);
    }

    /**
     * Get frequently logged foods for quick add
     */
    #[Route('/food/frequent', name: 'food_frequent', methods: ['GET'])]
    public function getFrequentFoods(FoodItemRepository $foodItemRepository): Response
    {
        $userId = $this->getUserId();
        
        $foods = $foodItemRepository->findFrequentlyLoggedFoods($userId, 10);
        
        $results = array_map(function ($food) {
            return [
                'name' => $food['name'],
                'calories' => round($food['avgCalories']),
                'count' => $food['count'],
            ];
        }, $foods);
        
        return $this->json($results);
    }

    /**
     * Quick add a frequently used food
     */
    #[Route('/food/quick-add', name: 'food_quick_add', methods: ['POST'])]
    public function quickAddFood(Request $request, FoodLogRepository $foodLogRepository, FoodItemRepository $foodItemRepository): Response
    {
        $userId = $this->getUserId();
        $today = new DateTime();
        
        $data = json_decode($request->getContent(), true);
        
        $mealType = $data['mealType'] ?? 'snacks';
        $foodName = $data['name'] ?? '';
        $calories = $data['calories'] ?? 0;
        $quantity = $data['quantity'] ?? 1;
        $unit = $data['unit'] ?? 'serving';
        $protein = $data['protein'] ?? 0;
        $carbs = $data['carbs'] ?? 0;
        $fats = $data['fats'] ?? 0;
        
        // Get or create food log
        $foodLog = $foodLogRepository->findOneBy([
            'userId' => $userId,
            'date' => $today,
            'mealType' => $mealType
        ]);
        
        if (!$foodLog) {
            $foodLog = new FoodLog();
            $foodLog->setUserId($userId);
            $foodLog->setDate($today);
            $foodLog->setMealType($mealType);
            $this->entityManager->persist($foodLog);
            $this->entityManager->flush();
        }
        
        // Create new food item
        $foodItem = new FoodItem();
        $foodItem->setFoodLog($foodLog);
        $foodItem->setName($foodName);
        $foodItem->setCalories($calories);
        $foodItem->setQuantity((string)$quantity);
        $foodItem->setUnit($unit);
        $foodItem->setProtein((string)$protein);
        $foodItem->setCarbs((string)$carbs);
        $foodItem->setFats((string)$fats);
        
        $this->entityManager->persist($foodItem);
        
        // Update food log totals
        $foodLog->calculateTotals();
        
        $this->entityManager->flush();
        
        return $this->json([
            'success' => true,
            'message' => 'Food added successfully!'
        ]);
    }

    // ============ WATER INTAKE ROUTES ============

    #[Route('/water/add', name: 'water_add', methods: ['GET', 'POST'])]
    public function addWater(Request $request, WaterIntakeRepository $waterIntakeRepository): Response
    {
        $userId = $this->getUserId();
        $today = new DateTime();
        
        // Check if water intake exists for today
        $waterIntake = $waterIntakeRepository->findByUserIdAndDate($userId, $today);
        
        if (!$waterIntake) {
            $waterIntake = new WaterIntake();
            $waterIntake->setUserId($userId);
            $waterIntake->setDate($today);
            $waterIntake->setGlasses(0);
        }
        
        $form = $this->createForm(WaterIntakeFormType::class, $waterIntake);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($waterIntake);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Water intake updated successfully!');
            
            return $this->redirectToRoute('nutrition_dashboard');
        }
        
        return $this->render('nutrition/water-form.html.twig', [
            'form' => $form->createView(),
            'currentGlasses' => $waterIntake->getGlasses()
        ]);
    }

    #[Route('/water/quick-add', name: 'water_quick_add', methods: ['POST'])]
    public function quickAddWater(Request $request, WaterIntakeRepository $waterIntakeRepository): Response
    {
        $userId = $this->getUserId();
        $today = new DateTime();
        
        $glasses = $request->request->getInt('glasses', 1);
        
        $waterIntake = $waterIntakeRepository->findByUserIdAndDate($userId, $today);
        
        if (!$waterIntake) {
            $waterIntake = new WaterIntake();
            $waterIntake->setUserId($userId);
            $waterIntake->setDate($today);
            $waterIntake->setGlasses(0);
        }
        
        $currentGlasses = $waterIntake->getGlasses();
        $waterIntake->setGlasses($currentGlasses + $glasses);
        
        $this->entityManager->persist($waterIntake);
        $this->entityManager->flush();
        
        return $this->redirectToRoute('nutrition_dashboard');
    }

    #[Route('/water/set/{glasses}', name: 'water_set', methods: ['GET'])]
    public function setWaterGlasses(int $glasses, WaterIntakeRepository $waterIntakeRepository): Response
    {
        $userId = $this->getUserId();
        $today = new DateTime();
        
        $waterIntake = $waterIntakeRepository->findByUserIdAndDate($userId, $today);
        
        if (!$waterIntake) {
            $waterIntake = new WaterIntake();
            $waterIntake->setUserId($userId);
            $waterIntake->setDate($today);
            $waterIntake->setGlasses(0);
        }
        
        $waterIntake->setGlasses($glasses);
        
        $this->entityManager->persist($waterIntake);
        $this->entityManager->flush();
        
        return $this->redirectToRoute('nutrition_dashboard');
    }

    // ============ NUTRITION GOALS ROUTES ============

    #[Route('/goals', name: 'goals')]
    public function goals(Request $request, NutritionGoalRepository $nutritionGoalRepository): Response
    {
        $userId = $this->getUserId();
        
        $nutritionGoal = $nutritionGoalRepository->findByUserId($userId);
        
        if (!$nutritionGoal) {
            $nutritionGoal = new NutritionGoal();
            $nutritionGoal->setUserId($userId);
            $nutritionGoal->setCaloriesTarget(2000);
            $nutritionGoal->setWaterTarget(8);
            $nutritionGoal->setProteinTarget(120);
            $nutritionGoal->setCarbsTarget(200);
            $nutritionGoal->setFatsTarget(65);
            $nutritionGoal->setActivityLevel('moderate');
        }
        
        $form = $this->createForm(NutritionGoalFormType::class, $nutritionGoal);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($nutritionGoal);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Nutrition goals saved successfully!');
            
            return $this->redirectToRoute('nutrition_goals');
        }
        
        return $this->render('nutrition/goals.html.twig', [
            'form' => $form->createView(),
            'nutritionGoal' => $nutritionGoal,
            'badges' => [],
            'streaks' => [
                'logging' => 7,
                'water' => 5,
                'veggies' => 3
            ]
        ]);
    }

    // ============ FOOD DIARY ============

    // ============ PROGRESS TRACKING ============

    #[Route('/progress', name: 'progress')]
    public function progress(
        Request $request,
        FoodLogRepository $foodLogRepository,
        WaterIntakeRepository $waterIntakeRepository,
        NutritionGoalRepository $nutritionGoalRepository
    ): Response {
        $userId = $this->getUserId();
        
        // Get nutrition goal
        $nutritionGoal = $nutritionGoalRepository->findByUserId($userId);
        if (!$nutritionGoal) {
            $nutritionGoal = new NutritionGoal();
            $nutritionGoal->setUserId($userId);
            $nutritionGoal->setCaloriesTarget(2000);
            $nutritionGoal->setWaterTarget(8);
            $nutritionGoal->setProteinTarget(120);
            $nutritionGoal->setCarbsTarget(200);
            $nutritionGoal->setFatsTarget(65);
        }
        
        // Get last 7 days of data for progress tracking
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = new DateTime();
            $date->modify("-{$i} days");
            
            $foodLogs = $foodLogRepository->findAllByUserIdAndDate($userId, $date);
            
            $totalCalories = 0;
            $totalProteins = 0;
            $totalCarbs = 0;
            $totalFats = 0;
            
            foreach ($foodLogs as $foodLog) {
                foreach ($foodLog->getFoodItems() as $item) {
                    $totalCalories += $item->getCalories();
                    $totalProteins += floatval($item->getProtein());
                    $totalCarbs += floatval($item->getCarbs());
                    $totalFats += floatval($item->getFats());
                }
            }
            
            $waterIntake = $waterIntakeRepository->findByUserIdAndDate($userId, $date);
            $waterGlasses = $waterIntake ? $waterIntake->getGlasses() : 0;
            
            $weeklyData[] = [
                'date' => $date->format('Y-m-d'),
                'dayName' => $date->format('l'),
                'dayShort' => $date->format('D'),
                'calories' => $totalCalories,
                'calorieTarget' => $nutritionGoal->getCaloriesTarget() ?? 2000,
                'proteins' => $totalProteins,
                'proteinTarget' => $nutritionGoal->getProteinTarget() ?? 120,
                'carbs' => $totalCarbs,
                'carbsTarget' => $nutritionGoal->getCarbsTarget() ?? 200,
                'fats' => $totalFats,
                'fatsTarget' => $nutritionGoal->getFatsTarget() ?? 65,
                'water' => $waterGlasses,
                'waterTarget' => $nutritionGoal->getWaterTarget() ?? 8,
            ];
        }
        
        // Calculate weekly averages
        $avgCalories = array_sum(array_column($weeklyData, 'calories')) / 7;
        $avgProteins = array_sum(array_column($weeklyData, 'proteins')) / 7;
        $avgCarbs = array_sum(array_column($weeklyData, 'carbs')) / 7;
        $avgFats = array_sum(array_column($weeklyData, 'fats')) / 7;
        $avgWater = array_sum(array_column($weeklyData, 'water')) / 7;
        
        // Calculate streaks
        $loggingStreak = 0;
        $waterStreak = 0;
        for ($i = count($weeklyData) - 1; $i >= 0; $i--) {
            if ($weeklyData[$i]['calories'] > 0) {
                $loggingStreak++;
            } else {
                break;
            }
        }
        for ($i = count($weeklyData) - 1; $i >= 0; $i--) {
            if ($weeklyData[$i]['water'] >= $weeklyData[$i]['waterTarget']) {
                $waterStreak++;
            } else {
                break;
            }
        }
        
return $this->render('nutrition/progress-tracking.html.twig', [
            'weeklyData' => $weeklyData,
            'averages' => [
                'calories' => round($avgCalories),
                'proteins' => round($avgProteins, 1),
                'carbs' => round($avgCarbs, 1),
                'fats' => round($avgFats, 1),
                'water' => round($avgWater, 1),
            ],
            'streaks' => [
                'logging' => $loggingStreak,
                'water' => $waterStreak,
            ],
            'nutritionGoal' => $nutritionGoal,
        ]);
    }

    // ============ NUTRITIONAL ANALYSIS ============

    #[Route('/analysis/{period}', name: 'analysis', requirements: ['period' => '\\d+'], defaults: ['period' => 30])]
    public function analysis(
        Request $request,
        int $period = 30,
        FoodLogRepository $foodLogRepository,
        WaterIntakeRepository $waterIntakeRepository,
        NutritionGoalRepository $nutritionGoalRepository
    ): Response {
        $userId = $this->getUserId();
        $today = new DateTime();
        $days = $period;
        
        // Get nutrition goal
        $nutritionGoal = $nutritionGoalRepository->findByUserId($userId);
        if (!$nutritionGoal) {
            $nutritionGoal = new NutritionGoal();
            $nutritionGoal->setUserId($userId);
            $nutritionGoal->setCaloriesTarget(2000);
            $nutritionGoal->setWaterTarget(8);
            $nutritionGoal->setProteinTarget(120);
            $nutritionGoal->setCarbsTarget(200);
            $nutritionGoal->setFatsTarget(65);
        }
        
        // Get today's data
        $foodLogs = $foodLogRepository->findAllByUserIdAndDate($userId, $today);
        
        $totalCalories = 0;
        $totalProteins = 0;
        $totalCarbs = 0;
        $totalFats = 0;
        $totalFiber = 0;
        $totalSugar = 0;
        $totalSodium = 0;
        
        $mealBreakdown = [
            'breakfast' => ['calories' => 0, 'percentage' => 0],
            'lunch' => ['calories' => 0, 'percentage' => 0],
            'dinner' => ['calories' => 0, 'percentage' => 0],
            'snacks' => ['calories' => 0, 'percentage' => 0]
        ];
        
        foreach ($foodLogs as $foodLog) {
            $mealType = $foodLog->getMealType();
            if (!isset($mealBreakdown[$mealType])) {
                $mealType = 'snacks';
            }
            
            foreach ($foodLog->getFoodItems() as $item) {
                $totalCalories += $item->getCalories();
                $totalProteins += floatval($item->getProtein());
                $totalCarbs += floatval($item->getCarbs());
                $totalFats += floatval($item->getFats());
                $totalFiber += floatval($item->getFiber() ?? 0);
                $totalSugar += floatval($item->getSugar() ?? 0);
                $totalSodium += floatval($item->getSodium() ?? 0);
                
                $mealBreakdown[$mealType]['calories'] += $item->getCalories();
            }
        }
        
        // Calculate meal percentages
        if ($totalCalories > 0) {
            foreach ($mealBreakdown as $key => $meal) {
                $mealBreakdown[$key]['percentage'] = round(($meal['calories'] / $totalCalories) * 100);
            }
        }
        
        // Get water intake
        $waterIntake = $waterIntakeRepository->findByUserIdAndDate($userId, $today);
        $waterGlasses = $waterIntake ? $waterIntake->getGlasses() : 0;
        
        // Calculate nutrient balance score (0-100)
        $balanceScore = 0;
        $balanceFactors = [];
        
        // Calorie balance
        $calorieRatio = $totalCalories / max($nutritionGoal->getCaloriesTarget(), 1);
        if ($calorieRatio >= 0.9 && $calorieRatio <= 1.1) {
            $balanceScore += 20;
            $balanceFactors[] = ['name' => 'Calories', 'status' => 'good', 'message' => 'Apport calorique adapté'];
        } elseif ($calorieRatio >= 0.7 && $calorieRatio <= 1.3) {
            $balanceScore += 10;
            $balanceFactors[] = ['name' => 'Calories', 'status' => 'warning', 'message' => 'Apport calorique légèrement dévié'];
        } else {
            $balanceFactors[] = ['name' => 'Calories', 'status' => 'bad', 'message' => 'Apport calorique très différent de l\'objectif'];
        }
        
        // Protein balance
        $proteinRatio = $totalProteins / max($nutritionGoal->getProteinTarget(), 1);
        if ($proteinRatio >= 0.8) {
            $balanceScore += 20;
            $balanceFactors[] = ['name' => 'Protéines', 'status' => 'good', 'message' => 'Apport en protéines suffisant'];
        } elseif ($proteinRatio >= 0.5) {
            $balanceScore += 10;
            $balanceFactors[] = ['name' => 'Protéines', 'status' => 'warning', 'message' => 'Protéines modérées'];
        } else {
            $balanceFactors[] = ['name' => 'Protéines', 'status' => 'bad', 'message' => 'Protéines insuffisantes'];
        }
        
        // Water intake
        $waterRatio = $waterGlasses / max($nutritionGoal->getWaterTarget(), 1);
        if ($waterRatio >= 1) {
            $balanceScore += 20;
            $balanceFactors[] = ['name' => 'Hydratation', 'status' => 'good', 'message' => 'Hydratation suffisante'];
        } elseif ($waterRatio >= 0.5) {
            $balanceScore += 10;
            $balanceFactors[] = ['name' => 'Hydratation', 'status' => 'warning', 'message' => 'Hydratation insuffisante'];
        } else {
            $balanceFactors[] = ['name' => 'Hydratation', 'status' => 'bad', 'message' => 'Hydratation insuffisante'];
        }
        
        // Carbs vs Fats ratio
        if ($totalCarbs > 0 && $totalFats > 0) {
            $carbFatRatio = $totalCarbs / $totalFats;
            if ($carbFatRatio >= 2 && $carbFatRatio <= 4) {
                $balanceScore += 20;
                $balanceFactors[] = ['name' => 'Ratio Glucides/Lipides', 'status' => 'good', 'message' => 'Ratio glucides/lipides équilibrée'];
            } elseif ($carbFatRatio >= 1 && $carbFatRatio <= 5) {
                $balanceScore += 10;
                $balanceFactors[] = ['name' => 'Ratio Glucides/Lipides', 'status' => 'warning', 'message' => 'Ratio acceptable'];
            } else {
                $balanceFactors[] = ['name' => 'Ratio Glucides/Lipides', 'status' => 'bad', 'message' => 'Déséquilibre glucides/lipides'];
            }
        }
        
        // Fiber (if tracked)
        $fiberRatio = $totalFiber / max($nutritionGoal->getFiberTarget() ?? 25, 1);
        if ($fiberRatio >= 0.8) {
            $balanceScore += 20;
            $balanceFactors[] = ['name' => 'Fibres', 'status' => 'good', 'message' => 'Apport en fibres suffisant'];
        } elseif ($fiberRatio >= 0.5) {
            $balanceScore += 10;
            $balanceFactors[] = ['name' => 'Fibres', 'status' => 'warning', 'message' => 'Fibres modérées'];
        } else {
            $balanceFactors[] = ['name' => 'Fibres', 'status' => 'bad', 'message' => 'Fibres insuffisantes'];
        }
        
        // Macros percentages
        $totalMacroCalories = ($totalProteins * 4) + ($totalCarbs * 4) + ($totalFats * 9);
        $macros = [
            'proteins' => ['percentage' => 0, 'grams' => $totalProteins],
            'carbs' => ['percentage' => 0, 'grams' => $totalCarbs],
            'fats' => ['percentage' => 0, 'grams' => $totalFats],
        ];
        
        if ($totalMacroCalories > 0) {
            $macros['proteins']['percentage'] = round(($totalProteins * 4 / $totalMacroCalories) * 100);
            $macros['carbs']['percentage'] = round(($totalCarbs * 4 / $totalMacroCalories) * 100);
            $macros['fats']['percentage'] = round(($totalFats * 9 / $totalMacroCalories) * 100);
        }
        
        // Recommendations based on analysis
        $recommendations = [];
        
        if ($totalCalories > $nutritionGoal->getCaloriesTarget()) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => 'fa-fire',
                'title' => 'Excès calorique',
                'description' => 'Vous avez dépassé votre objectif calorique. Envisagez de réduire les portions lors des prochains repas.'
            ];
        } elseif ($totalCalories < $nutritionGoal->getCaloriesTarget() * 0.5 && $totalCalories > 0) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => 'fa-info-circle',
                'title' => 'Apport calorique faible',
                'description' => 'Votre apport calorique est inférieur à la moitié de votre objectif. Ajoutez des collations nutritious.'
            ];
        }
        
        if ($totalProteins < $nutritionGoal->getProteinTarget() * 0.8) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => 'fa-drumstick-bite',
                'title' => 'Augmentez les protéines',
                'description' => 'Ajoutez des sources de protéines comme la viande, le poisson, les œufs ou les légumineuses.'
            ];
        }
        
        if ($waterGlasses < $nutritionGoal->getWaterTarget()) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => 'fa-tint',
                'title' => 'Hydratation',
                'description' => 'Buvez plus d\'eau pour rester hydraté. Visez ' . $nutritionGoal->getWaterTarget() . ' verres par jour.'
            ];
        }
        
        if (empty($foodLogs)) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => 'fa-utensils',
                'title' => 'Commencez à enregistrer',
                'description' => 'Enregistrez vos repas pour obtenir une analyse détaillée de votre alimentation.'
            ];
        }
        
return $this->render('nutrition/nutrition-analysis.html.twig', [
            'dailySummary' => [
                'calories' => $totalCalories,
                'calorieTarget' => $nutritionGoal->getCaloriesTarget() ?? 2000,
                'proteins' => $totalProteins,
                'proteinTarget' => $nutritionGoal->getProteinTarget() ?? 120,
                'carbs' => $totalCarbs,
                'carbsTarget' => $nutritionGoal->getCarbsTarget() ?? 250,
                'fats' => $totalFats,
                'fatsTarget' => $nutritionGoal->getFatsTarget() ?? 70,
                'fiber' => $totalFiber,
                'fiberTarget' => $nutritionGoal->getFiberTarget() ?? 25,
                'sugar' => $totalSugar,
                'sugarTarget' => $nutritionGoal->getSugarTarget() ?? 25,
                'sodium' => $totalSodium,
                'sodiumTarget' => $nutritionGoal->getSodiumTarget() ?? 2300,
            ],
            'water' => [
                'intake' => $waterGlasses,
                'target' => $nutritionGoal->getWaterTarget() ?? 8
            ],
            'mealBreakdown' => $mealBreakdown,
            'macros' => $macros,
            'balanceScore' => $balanceScore,
            'balanceFactors' => $balanceFactors,
            'recommendations' => $recommendations,
            'nutritionGoal' => $nutritionGoal,
        ]);
    }

    // ============ FOOD DIARY ============

    #[Route('/diary', name: 'food_diary')]
    public function foodDiary(
        FoodLogRepository $foodLogRepository,
        WaterIntakeRepository $waterIntakeRepository,
        NutritionGoalRepository $nutritionGoalRepository
    ): Response {
        $userId = $this->getUserId();
        $today = new DateTime();
        
        // Get nutrition goal
        $nutritionGoal = $nutritionGoalRepository->findByUserId($userId);
        if (!$nutritionGoal) {
            $nutritionGoal = new NutritionGoal();
            $nutritionGoal->setUserId($userId);
            $nutritionGoal->setCaloriesTarget(2000);
            $nutritionGoal->setWaterTarget(8);
            $nutritionGoal->setProteinTarget(120);
            $nutritionGoal->setCarbsTarget(200);
            $nutritionGoal->setFatsTarget(65);
        }
        
        $foodLogs = $foodLogRepository->findAllByUserIdAndDate($userId, $today);
        
        $meals = [
            'breakfast' => ['calories' => 0, 'items' => []],
            'lunch' => ['calories' => 0, 'items' => []],
            'dinner' => ['calories' => 0, 'items' => []],
            'snacks' => ['calories' => 0, 'items' => []]
        ];
        
        $totalCalories = 0;
        $totalProteins = 0;
        $totalCarbs = 0;
        $totalFats = 0;
        
        foreach ($foodLogs as $foodLog) {
            $mealType = $foodLog->getMealType();
            if (!isset($meals[$mealType])) {
                $mealType = 'snacks';
            }
            
            foreach ($foodLog->getFoodItems() as $item) {
                $totalCalories += $item->getCalories();
                $totalProteins += floatval($item->getProtein());
                $totalCarbs += floatval($item->getCarbs());
                $totalFats += floatval($item->getFats());
                
                $meals[$mealType]['calories'] += $item->getCalories();
                $meals[$mealType]['items'][] = [
                    'name' => $item->getName(),
                    'quantity' => $item->getQuantity(),
                    'calories' => $item->getCalories(),
                    'proteins' => $item->getProtein(),
                    'carbs' => $item->getCarbs(),
                    'fats' => $item->getFats(),
                    'id' => $item->getId()
                ];
            }
        }
        
        // Get water intake
        $waterIntake = $waterIntakeRepository->findByUserIdAndDate($userId, $today);
        $waterIntakeValue = $waterIntake ? $waterIntake->getGlasses() : 0;
        
        // Generate nutrition alerts based on daily summary vs targets
        $nutritionAlerts = [];
        
        // Check calorie intake
        if ($totalCalories > $nutritionGoal->getCaloriesTarget()) {
            $nutritionAlerts[] = [
                'type' => 'warning',
                'icon' => 'fa-fire',
                'message' => 'Apport calorique dépassé',
                'recommendation' => 'Vous avez dépassé votre objectif calorique de ' . $nutritionGoal->getCaloriesTarget() . ' kcal. Envisagez de réduire vos portions lors des prochains repas.'
            ];
        } elseif ($totalCalories < $nutritionGoal->getCaloriesTarget() * 0.5 && $totalCalories > 0) {
            $nutritionAlerts[] = [
                'type' => 'info',
                'icon' => 'fa-info-circle',
                'message' => 'Apport calorique faible',
                'recommendation' => 'Vous n\'avez consommé que ' . $totalCalories . ' kcal. Essayez d\'ajouter des collations nutritious.'
            ];
        }
        
        // Check water intake
        if ($waterIntakeValue < $nutritionGoal->getWaterTarget()) {
            $nutritionAlerts[] = [
                'type' => 'warning',
                'icon' => 'fa-tint',
                'message' => 'Hydratation insuffisante',
                'recommendation' => 'Vous n\'avez bu que ' . $waterIntakeValue . '/' . $nutritionGoal->getWaterTarget() . ' verres d\'eau aujourd\'hui. Buvez plus d\'eau pour rester hydraté.'
            ];
        }
        
        // Check protein intake
        if ($totalProteins < $nutritionGoal->getProteinTarget() * 0.5 && $totalProteins > 0) {
            $nutritionAlerts[] = [
                'type' => 'info',
                'icon' => 'fa-drumstick-bite',
                'message' => 'Protéines insuffisantes',
                'recommendation' => 'Votre apport en protéines est bas. Ajoutez des sources de protéines comme la viande, le poisson, les œufs ou les légumineuses.'
            ];
        }
        
        // If no food logged today
        if ($totalCalories === 0) {
            $nutritionAlerts[] = [
                'type' => 'info',
                'icon' => 'fa-utensils',
                'message' => 'Aucun aliment enregistré',
                'recommendation' => 'Commencez à enregistrer vos repas pour suivre votre alimentation.'
            ];
        }
        
        // Calculate food groups based on consumed items (derived from database)
        $foodGroupData = [
            'proteins' => ['calories' => 0, 'percentage' => 0, 'color' => 'bg-amber-500'],
            'carbs' => ['calories' => 0, 'percentage' => 0, 'color' => 'bg-green-500'],
            'fats' => ['calories' => 0, 'percentage' => 0, 'color' => 'bg-purple-500'],
        ];
        
        // Get calories from each macronutrient
        $foodGroupData['proteins']['calories'] = $totalProteins * 4; // 4 kcal per gram
        $foodGroupData['carbs']['calories'] = $totalCarbs * 4; // 4 kcal per gram
        $foodGroupData['fats']['calories'] = $totalFats * 9; // 9 kcal per gram
        
        $totalMacroCalories = $foodGroupData['proteins']['calories'] + $foodGroupData['carbs']['calories'] + $foodGroupData['fats']['calories'];
        
        // Calculate percentages
        if ($totalMacroCalories > 0) {
            $foodGroupData['proteins']['percentage'] = round(($foodGroupData['proteins']['calories'] / $totalMacroCalories) * 100);
            $foodGroupData['carbs']['percentage'] = round(($foodGroupData['carbs']['calories'] / $totalMacroCalories) * 100);
            $foodGroupData['fats']['percentage'] = round(($foodGroupData['fats']['calories'] / $totalMacroCalories) * 100);
        }
        
        // Build food groups array for template
        $foodGroups = [
            'Protéines' => [
                'percentage' => $foodGroupData['proteins']['percentage'],
                'color' => $foodGroupData['proteins']['color']
            ],
            'Glucides' => [
                'percentage' => $foodGroupData['carbs']['percentage'],
                'color' => $foodGroupData['carbs']['color']
            ],
            'Lipides' => [
                'percentage' => $foodGroupData['fats']['percentage'],
                'color' => $foodGroupData['fats']['color']
            ],
        ];
        
        return $this->render('nutrition/food-diary.html.twig', [
            'dailySummary' => [
                'calories' => $totalCalories,
                'calorieTarget' => $nutritionGoal->getCaloriesTarget() ?? 2000,
                'proteins' => $totalProteins,
                'proteinTarget' => $nutritionGoal->getProteinTarget() ?? 120,
                'carbs' => $totalCarbs,
                'carbTarget' => $nutritionGoal->getCarbsTarget() ?? 250,
                'fats' => $totalFats,
                'fatTarget' => $nutritionGoal->getFatsTarget() ?? 70,
            ],
            'meals' => $meals,
            'foodGroups' => $foodGroups,
            'water' => [
                'intake' => $waterIntakeValue,
                'target' => $nutritionGoal->getWaterTarget() ?? 8
            ],
            'macros' => [
                'proteins' => $totalProteins,
                'carbs' => $totalCarbs,
                'fats' => $totalFats
            ],
            'nutritionAlerts' => $nutritionAlerts,
        ]);
    }

    // ============ OTHER ROUTES ============

    #[Route('/quick-log', name: 'quick_log')]
    public function quickLog(
        Request $request,
        FoodLogRepository $foodLogRepository,
        WaterIntakeRepository $waterIntakeRepository,
        NutritionGoalRepository $nutritionGoalRepository
    ): Response
    {
        $userId = $this->getUserId();
        $today = new DateTime();
        
        // Get nutrition goal
        $nutritionGoal = $nutritionGoalRepository->findByUserId($userId);
        
        // Get today's food logs for summary
        $foodLogs = $foodLogRepository->findAllByUserIdAndDate($userId, $today);
        
        $totalCalories = 0;
        $totalProteins = 0;
        $totalCarbs = 0;
        $totalFats = 0;
        
        foreach ($foodLogs as $foodLog) {
            foreach ($foodLog->getFoodItems() as $item) {
                $totalCalories += $item->getCalories();
                $totalProteins += floatval($item->getProtein());
                $totalCarbs += floatval($item->getCarbs());
                $totalFats += floatval($item->getFats());
            }
        }
        
        return $this->render('nutrition/quick-log.html.twig', [
            'calories' => ['consumed' => $totalCalories, 'target' => $nutritionGoal?->getCaloriesTarget() ?? 2000],
            'macros' => [
                'proteins' => $totalProteins,
                'carbs' => $totalCarbs,
                'fats' => $totalFats
            ]
        ]);
    }

    #[Route('/planner', name: 'meal_planner')]
    public function mealPlanner(): Response
    {
        return $this->render('nutrition/meal-planner.html.twig');
    }

    #[Route('/recipes/{category}', name: 'nutrition_recipes', requirements: ['category' => '[a-zA-Z]+'], defaults: ['category' => null])]
    public function recipes(?string $category = null, FoodItemRepository $foodItemRepository): Response
    {
        // Get recipes from database, filtered by category if provided
        $recipes = $foodItemRepository->findBy(
            ['isRecipe' => true],
            ['id' => 'DESC'],
            50
        );
        
        // If category filter is provided, filter the results
        $filteredRecipes = $recipes;
        if ($category) {
            $filteredRecipes = array_filter($recipes, function($recipe) use ($category) {
                return $recipe->getCategory() === $category;
            });
        }

        return $this->render('nutrition/recipe-library.html.twig', [
            'recipes' => $filteredRecipes,
            'category' => $category,
        ]);
    }

    #[Route('/grocery-list', name: 'grocery_list')]
    public function groceryList(): Response
    {
        return $this->render('nutrition/grocery-list.html.twig');
    }

    #[Route('/messages/{nutritionistId}', name: 'messages', requirements: ['nutritionistId' => '\d+'])]
    public function messages(int $nutritionistId): Response
    {
        return $this->render('nutrition/messages.html.twig', [
            'nutritionistId' => $nutritionistId,
        ]);
    }

    #[Route('/consultation', name: 'consultation')]
    public function consultation(): Response
    {
        return $this->render('nutrition/consultation.html.twig');
    }

    #[Route('/barcode-scanner', name: 'barcode_scanner')]
    public function barcodeScanner(): Response
    {
        return $this->render('nutrition/barcode-scanner.html.twig');
    }

    #[Route('/voice-input', name: 'voice_input')]
    public function voiceInput(): Response
    {
        return $this->render('nutrition/voice-input.html.twig');
    }

    // ============ NUTRITIONIST ROUTES ============

    #[Route('/nutritionniste/dashboard', name: 'nutritionniste_dashboard')]
    public function nutritionistDashboard(): Response
    {
        return $this->render('nutritionniste/dashboard.html.twig');
    }

    #[Route('/nutritionniste/patients', name: 'nutritionniste_patients')]
    public function nutritionistPatients(): Response
    {
        return $this->render('nutritionniste/patient-list.html.twig');
    }

    #[Route('/nutritionniste/patient/{id}', name: 'nutritionniste_patient_view', requirements: ['id' => '\d+'])]
    public function nutritionistPatientView(int $id): Response
    {
        return $this->render('nutritionniste/patient-detail.html.twig', [
            'patientId' => $id,
        ]);
    }

    #[Route('/nutritionniste/meal-plan/new', name: 'nutritionniste_meal_plan_new')]
    public function nutritionistMealPlanNew(): Response
    {
        return $this->render('nutritionniste/meal-plan-builder.html.twig');
    }

    #[Route('/nutritionniste/analysis/{patientId}', name: 'nutritionniste_analysis', requirements: ['patientId' => '\d+'])]
    public function nutritionistAnalysis(
        int $patientId,
        FoodLogRepository $foodLogRepository,
        NutritionGoalRepository $nutritionGoalRepository,
        WaterIntakeRepository $waterIntakeRepository
    ): Response {
        // Fetch patient's nutrition goals
        $goals = $nutritionGoalRepository->findBy(['userId' => $patientId]);
        $currentGoal = !empty($goals) ? $goals[0] : null;
        
        // Fetch recent food logs (last 30 days)
        $startDate = new \DateTime('-30 days');
        $endDate = new \DateTime();
        $foodLogs = $foodLogRepository->createQueryBuilder('f')
            ->where('f.userId = :userId')
            ->andWhere('f.date >= :startDate')
            ->setParameter('userId', $patientId)
            ->setParameter('startDate', $startDate)
            ->orderBy('f.date', 'DESC')
            ->getQuery()
            ->getResult();
        
        // Fetch water intake data
        $waterData = $waterIntakeRepository->createQueryBuilder('w')
            ->where('w.userId = :userId')
            ->andWhere('w.date >= :startDate')
            ->setParameter('userId', $patientId)
            ->setParameter('startDate', $startDate)
            ->getQuery()
            ->getResult();
        
        // Calculate statistics
        $totalCalories = 0;
        $totalProteins = 0;
        $totalCarbs = 0;
        $totalFats = 0;
        $totalWater = 0;
        
        foreach ($foodLogs as $log) {
            $totalCalories += $log->getCalories() ?? 0;
            $totalProteins += $log->getProtein() ?? 0;
            $totalCarbs += $log->getCarbs() ?? 0;
            $totalFats += $log->getFats() ?? 0;
        }
        
        foreach ($waterData as $water) {
            $totalWater += $water->getGlasses() ?? 0;
        }
        
        // Calculate daily averages
        $daysCount = count($foodLogs) > 0 ? count($foodLogs) : 1;
        $avgCalories = round($totalCalories / 30);
        $avgProteins = round($totalProteins / 30);
        $avgCarbs = round($totalCarbs / 30);
        $avgFats = round($totalFats / 30);
        $avgWater = round($totalWater / 30);
        
        // Get goal targets
        $calorieTarget = $currentGoal ? $currentGoal->getCaloriesTarget() : 2000;
        $proteinTarget = $currentGoal ? $currentGoal->getProteinTarget() : 120;
        $carbsTarget = $currentGoal ? $currentGoal->getCarbsTarget() : 200;
        $fatsTarget = $currentGoal ? $currentGoal->getFatsTarget() : 65;
        $waterTarget = $currentGoal ? $currentGoal->getWaterTarget() : 8;
        
        // Calculate adherence percentages
        $calorieAdherence = $calorieTarget > 0 ? min(100, round(($avgCalories / $calorieTarget) * 100)) : 0;
        $proteinAdherence = $proteinTarget > 0 ? min(100, round(($avgProteins / $proteinTarget) * 100)) : 0;
        $carbsAdherence = $carbsTarget > 0 ? min(100, round(($avgCarbs / $carbsTarget) * 100)) : 0;
        $fatsAdherence = $fatsTarget > 0 ? min(100, round(($avgFats / $fatsTarget) * 100)) : 0;
        $waterAdherence = $waterTarget > 0 ? min(100, round(($avgWater / $waterTarget) * 100)) : 0;
        
        // Get recent meals (last 7 days)
        $recentMeals = $foodLogRepository->createQueryBuilder('f')
            ->where('f.userId = :userId')
            ->andWhere('f.date >= :startDate')
            ->setParameter('userId', $patientId)
            ->setParameter('startDate', new \DateTime('-7 days'))
            ->orderBy('f.date', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
        
        return $this->render('nutritionniste/nutrition-analysis.html.twig', [
            'patientId' => $patientId,
            'goals' => $goals,
            'currentGoal' => $currentGoal,
            'foodLogs' => $foodLogs,
            'recentMeals' => $recentMeals,
            'waterData' => $waterData,
            'stats' => [
                'totalCalories' => $totalCalories,
                'totalProteins' => $totalProteins,
                'totalCarbs' => $totalCarbs,
                'totalFats' => $totalFats,
                'totalWater' => $totalWater,
                'avgCalories' => $avgCalories,
                'avgProteins' => $avgProteins,
                'avgCarbs' => $avgCarbs,
                'avgFats' => $avgFats,
                'avgWater' => $avgWater,
            ],
            'targets' => [
                'calories' => $calorieTarget,
                'proteins' => $proteinTarget,
                'carbs' => $carbsTarget,
                'fats' => $fatsTarget,
                'water' => $waterTarget,
            ],
            'adherence' => [
                'calories' => $calorieAdherence,
                'proteins' => $proteinAdherence,
                'carbs' => $carbsAdherence,
                'fats' => $fatsAdherence,
                'water' => $waterAdherence,
            ],
        ]);
    }

    #[Route('/nutritionniste/messages', name: 'nutritionniste_messages')]
    public function nutritionistMessages(): Response
    {
        return $this->render('nutritionniste/communication.html.twig');
    }

    #[Route('/nutritionniste/reports', name: 'nutritionniste_reports')]
    public function nutritionistReports(): Response
    {
        return $this->render('nutritionniste/reporting.html.twig');
    }
}
