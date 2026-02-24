<?php

namespace App\Service;

use App\Entity\FoodItem;
use App\Entity\NutritionGoal;
use App\Entity\WaterIntake;
use App\Repository\FoodItemRepository;
use App\Repository\FoodLogRepository;
use App\Repository\NutritionGoalRepository;
use App\Repository\WaterIntakeRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

/**
 * NutritionAI Service - A comprehensive, API-free AI system for nutrition
 * 
 * This service provides intelligent nutrition recommendations, meal planning,
 * calorie calculations, and personalized advice based on user goals and data.
 */
class NutritionAIService
{
    private EntityManagerInterface $entityManager;
    private ?int $userId;
    private array $userProfile = [];
    private ?NutritionGoal $currentGoal = null;
    private TunisianPriceService $priceService;
    
    // Food databases (comprehensive local data)
    private array $foodDatabase = [];
    private array $recipeDatabase = [];
    private array $nutritionKnowledge = [];
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->priceService = new TunisianPriceService();
        $this->initializeFoodDatabase();
        $this->initializeNutritionKnowledge();
    }
    
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
        $this->loadUserProfile();
    }
    
    /**
     * Load user profile data for personalized AI responses
     */
    private function loadUserProfile(): void
    {
        if (!$this->userId) return;
        
        // Load nutrition goals
        $goalRepo = $this->entityManager->getRepository(NutritionGoal::class);
        $this->currentGoal = $goalRepo->findOneBy(['userId' => $this->userId]);
        
        if ($this->currentGoal) {
            $this->userProfile = [
                'calories' => $this->currentGoal->getCaloriesTarget(),
                'protein' => $this->currentGoal->getProteinTarget(),
                'carbs' => $this->currentGoal->getCarbsTarget(),
                'fats' => $this->currentGoal->getFatsTarget(),
                'fiber' => $this->currentGoal->getFiberTarget(),
                'water' => $this->currentGoal->getWaterTarget(),
                'sugar' => $this->currentGoal->getSugarTarget(),
                'sodium' => $this->currentGoal->getSodiumTarget(),
                'weight' => $this->currentGoal->getCurrentWeight(),
                'goalWeight' => $this->currentGoal->getWeightTarget(),
                'activityLevel' => $this->currentGoal->getActivityLevel(),
            ];
        }
    }
    
    /**
     * Main AI processing method - analyzes user message and generates response
     */
    public function processMessage(string $message, array $conversationHistory = []): array
    {
        $message = strtolower(trim($message));
        $intent = $this->detectIntent($message);
        
        return match($intent) {
            'greeting' => $this->handleGreeting(),
            'help' => $this->handleHelp(),
            'thanks' => $this->handleThanks(),
            'goodbye' => $this->handleGoodbye(),
            'recipe' => $this->handleRecipeRequest($message),
            'meal_plan' => $this->handleMealPlanRequest($message),
            'weight_loss' => $this->handleWeightLoss($message),
            'muscle' => $this->handleMuscleGain($message),
            'vegan' => $this->handleVegan($message),
            'keto' => $this->handleKeto($message),
            'sport' => $this->handleSport($message),
            'water' => $this->handleHydration($message),
            'calories' => $this->handleCalories($message),
            'protein' => $this->handleProtein($message),
            'carbs' => $this->handleCarbs($message),
            'fats' => $this->handleFats($message),
            'fiber' => $this->handleFiber($message),
            'analyze' => $this->handleAnalysis($message),
            'diabetes' => $this->handleDiabetes(),
            'heart' => $this->handleHeartHealth(),
            'pregnancy' => $this->handlePregnancy(),
            'deficiency' => $this->handleDeficiency($message),
            'budget' => $this->handleBudget($message),
            'quick_meal' => $this->handleQuickMeal($message),
            'seasonal' => $this->handleSeasonal($message),
            'progress' => $this->handleProgress(),
            'recommendations' => $this->handleRecommendations($message),
            'grocery_list' => $this->handleGroceryList($message),
            'product_info' => $this->handleProductInfo($message),
            default => $this->handleDefault($message),
        };
    }
    
    /**
     * Detect user intent from message
     */
    private function detectIntent(string $message): string
    {
        // FIRST: Check if message contains any product name from our database
        $allPrices = $this->priceService->getAllPrices();
        foreach ($allPrices as $name => $data) {
            if (stripos($message, strtolower($name)) !== false) {
                return 'product_info';
            }
        }
        
        // Also check for generic food keywords that might indicate product query
        $foodKeywords = ['aliment', 'manger', 'mange', 'nourriture', 'produit', 'acheter', 'prix', 'calorie'];
        foreach ($foodKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                // Check if there's any other product-like word
                $commonFoods = ['poulet', 'viande', 'poisson', 'legume', 'fruit', 'lait', 'fromage', 'oeuf', 'pain', 'riz', 'pate', 'tomate', 'pomme', 'banane', 'orange', 'thon', 'boeuf'];
                foreach ($commonFoods as $food) {
                    if (stripos($message, $food) !== false) {
                        return 'product_info';
                    }
                }
            }
        }
        
        // Intent patterns
        $patterns = [
            'greeting' => ['bonjour', 'salut', 'hello', 'hi', 'hey', 'coucou', 'bjr', 'slt', 'wesh', 'good morning', 'good evening'],
            'help' => ['aide', 'help', 'comment', 'quoi faire', 'que faire', 'explique', 'peux-tu', 'que sais-tu'],
            'thanks' => ['merci', 'thanks', 'thx', 'bravo', 'super', 'génial', 'parfait', 'bien joué'],
            'goodbye' => ['au revoir', 'bye', 'salut', 'à plus', 'adios', 'à bientôt'],
            'recipe' => ['recette', 'recipe', 'préparer', 'cuisiner', 'menu', 'repas', 'cook', 'cuisine', 'préparation', 'plat'],
            'meal_plan' => ['plan', 'semaine', 'planning', 'programme', 'hebdomadaire', '7 jours', 'menu'],
            'weight_loss' => ['perdre', 'maigrir', 'mincir', 'poids', 'slim', 'amaigrissement', 'weight loss'],
            'muscle' => ['muscle', 'musculaire', 'force', 'prise de masse', 'bodybuilding', 'athlete', 'fitness', 'gym', 'muscle gain'],
            'vegan' => ['végétalien', 'vegan', 'végétarien', 'vegetarian', 'sans viande', 'végétal', 'plant based'],
            'keto' => ['keto', 'cétogène', 'low carb', 'cetogene', 'céto'],
            'sport' => ['sport', 'athlete', 'entraînement', 'exercice', 'training', 'course', 'running', 'foot', 'tennis', 'marche', 'natation', 'velo', 'cyclisme', 'musculation'],
            'water' => ['eau', 'water', 'hydratation', 'boire', 'litre', 'hydrate', 'hydratation'],
            'calories' => ['calorie', 'kcal', 'calories', 'apport calorique'],
            'protein' => ['protéine', 'protein', 'protéines', 'proteine'],
            'carbs' => ['glucide', 'carb', 'carbs', 'glucides', 'sucre lent', 'amidons'],
            'fats' => ['lipide', 'fat', 'fats', 'lipides', 'graisse', 'gras'],
            'fiber' => ['fibre', 'fibres', 'fibres alimentaires', 'fibers'],
            'analyze' => ['analyse', 'calculer', 'valeur nutritive', 'apport', 'combien', 'macros'],
            'diabetes' => ['diabète', 'diabete', 'glycémie', 'sucre sang', 'glucose', 'diabetic'],
            'heart' => ['coeur', 'cardio', 'cardiaque', 'cholesterol', 'tension', 'hypertension', 'cardiovascular'],
            'pregnancy' => ['grossesse', 'bébé', 'baby', 'enceint', 'grossesse', 'pregnant'],
            'deficiency' => ['carence', 'déficience', 'manque', 'manquant', ' deficiency'],
            'budget' => ['pas cher', 'économique', 'budget', 'abo', 'pas cher', 'low cost', 'economic'],
            'quick_meal' => ['rapide', 'vite', 'quick', 'express', 'facile', 'simple', 'quick meal'],
            'seasonal' => ['été', 'hiver', 'printemps', 'automne', 'saison', 'seasonal', 'de saison'],
            'progress' => ['progrès', 'progress', 'évolution', 'résultat', 'comment je vais', 'statut'],
            'recommendations' => ['conseil', 'conseils', 'recommande', 'suggestion', 'tips', 'advice'],
            'grocery_list' => ['courses', 'liste', 'acheter', 'marche', 'epicerie', 'grocery', 'shopping'],
            'product_info' => ['info produit', 'informations', 'produit', 'nutriments', 'vitamines', 'prix'],
        ];
        
        foreach ($patterns as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return $intent;
                }
            }
        }
        
        return 'default';
    }
    
    // ==================== INTENT HANDLERS ====================
    
    private function handleGreeting(): array
    {
        $goalInfo = $this->userProfile ? $this->formatGoalSummary() : "\n\n💡 Définissez vos objectifs pour des recommandations personnalisées!";
        
        return [
            'message' => "🌟═══════════════════════════════════\n" .
            "   WELL CARE AI - ASSISTANT\n" .
            "═══════════════════════════════════\n\n" .
            "Bonjour! Je suis votre assistant nutritionnel.\n" .
            "Je suis là pour vous aider à atteindre vos objectifs santé{$goalInfo}\n\n" .
            "📋 **Mes domaines d'expertise:**\n\n" .
            "🍳  Recettes    → Suggestions personnalisées\n" .
            "📅  Planning    → Menus de la semaine\n" .
            "⚖️  Poids       → Perte de poids\n" .
            "💪  Muscle      → Prise de masse\n" .
            "🌱  Régimes     → Vegan, Keto\n" .
            "🏃  Sport       → Performance\n" .
            "💧  Eau         → Hydratation\n" .
            "📊  Analyse     → Valeurs nutritives\n" .
            "❤️  Santé       → Diabète, cœur\n\n" .
            "🛒  **Courses**  → Liste avec prix\n" .
            "🍎  **Produits** → Info produit\n\n" .
            "Tapez votre demande ou utilisez les boutons ci-dessus!",
            'meals' => [],
            'quickActions' => $this->getQuickActions(),
        ];
    }
    
    private function handleHelp(): array
    {
        return [
            'message' => "Je peux vous aider de nombreuses façons:\n\n🍳 **Recettes**\n\"recette déjeuner\", \"petit-déjeuner\", \"dîner\"\n\n📅 **Planification**\n\"menu semaine\", \"planning\"\n\n⚖️ **Perte de poids**\n\"perdre du poids\", \"mincir\"\n\n💪 **Muscle**\n\"prise de muscle\", \"fitness\"\n\n🌱 **Régimes spéciaux**\n\"vegan\", \"keto\", \"végétarien\"\n\n🏃 **Sport**\n\"sportif\", \"athlete\"\n\n💧 **Hydratation**\n\"eau\", \"hydratation\"\n\n📊 **Analyse**\n\"calories\", \"protéines\", \"analyse\"\n\n❤️ **Santé**\n\"diabète\", \"coeur\"\n\n💰 **Budget**\n\"pas cher\", \"économique\"\n\n⏱️ **Rapide**\n\"repas rapide\", \"express\"\n\nTapez simplement votre demande!",
            'meals' => [],
        ];
    }
    
    private function handleThanks(): array
    {
        return [
            'message' => "Avec plaisir! 😊\n\nJe suis là pour vous aider à chaque étape de votre parcours nutritionnel:\n• Atteindre vos objectifs\n• Comprendre vos apports\n• Trouver des recettes adaptées\n• Planifier vos repas\n\nN'hésitez pas à me poser d'autres questions!",
            'meals' => [],
        ];
    }
    
    private function handleGoodbye(): array
    {
        return [
            'message' => "Au revoir! 👋\n\nPassez une excellente journée et prenez soin de vous!\n\nN'hésitez pas à revenir pour:\n• Des nouvelles recettes\n• Votre progression\n• Des conseils personnalisés\n\nÀ bientôt!",
            'meals' => [],
        ];
    }
    
    private function handleRecipeRequest(string $message): array
    {
        $mealType = $this->detectMealType($message);
        $meals = $this->generateMealSuggestions($mealType);
        
        $mealTypeNames = [
            'breakfast' => 'petit-déjeuner',
            'lunch' => 'déjeuner', 
            'dinner' => 'dîner',
            'snack' => 'collation'
        ];
        
        $typeName = $mealTypeNames[$mealType] ?? 'repas';
        
        return [
            'message' => "🍳 **Suggestions de {$typeName}:**\n\nVoici {$typeName}s adaptés à vos objectifs" . ($this->userProfile ? " ({$this->userProfile['calories']}kcal/jour)" : "") . ":\n\n" . implode("\n\n", array_map(fn($m) => "**{$m['name']}**\n📊 {$m['calories']} kcal | P:{$m['protein']}g | G:{$m['carbs']}g | L:{$m['fats']}g\n💡 {$m['description']}", $meals)),
            'meals' => $meals,
            'actions' => [
                ['label' => 'Ajouter au planner', 'action' => 'add_to_planner']
            ]
        ];
    }
    
    private function handleMealPlanRequest(string $message): array
    {
        $weeklyPlan = $this->generateWeeklyPlan();
        
        return [
            'message' => "📅 **Menu de la semaine**\n\nVoici un plan alimentaire adapté à vos objectifs" . ($this->userProfile ? " ({$this->userProfile['calories']}kcal/jour)" : "") . ":\n\n" . $this->formatWeeklyPlan($weeklyPlan),
            'weeklyPlan' => $weeklyPlan,
            'actions' => [
                ['label' => 'Sauvegarder le menu', 'action' => 'save_weekly_plan']
            ]
        ];
    }
    
    private function handleWeightLoss(string $message): array
    {
        $currentWeight = $this->userProfile['weight'] ?? null;
        $goalWeight = $this->userProfile['goalWeight'] ?? null;
        
        $advice = $this->userProfile ? $this->generateWeightLossAdvice() : $this->generateGeneralWeightLossAdvice();
        
        $meals = $this->generateMealSuggestions('lunch', 3);
        
        return [
            'message' => "⚖️ **Perte de poids**\n\n{$advice}\n\n**Conseils clés:**\n🥗 Privilégiez les légumes (50% de l'assiette)\n🍗 Choisissez des protéines maigres\n🥔 Préférez les féculents complets\n💧 Buvez 2L d'eau par jour\n🏃 Bougez 30min par jour\n😴 Dormez 7-8h\n\n**Repas recommandés:**",
            'meals' => $meals,
        ];
    }
    
    private function handleMuscleGain(string $message): array
    {
        $advice = $this->userProfile ? $this->generateMuscleAdvice() : $this->generateGeneralMuscleAdvice();
        
        $meals = $this->generateMealSuggestions('lunch', 4);
        
        return [
            'message' => "💪 **Prise de muscle**\n\n{$advice}\n\n**Stratégie nutritionnelle:**\n🥩 Protéines: 1.6-2g par kg de poids\n🍚 Glucides: énergie pour l'entraînement\n🥑 Lipides: hormones et energía\n⏰ Timing: protéines après séance\n\n**Repas riches en protéines:**",
            'meals' => $meals,
        ];
    }
    
    private function handleVegan(string $message): array
    {
        $meals = $this->generateVeganMeals();
        
        return [
            'message' => "🌱 **Régime végan**\n\n**Sources de protéines végétales:**\n• Lentilles (9g/100g)\n• Pois chiches (8.9g/100g)\n• Tofu (8g/100g)\n• Tempeh (19g/100g)\n• Seitan (25g/100g)\n• Quinoa (4g/100g)\n• Noix et graines\n\n**Compléments recommandés:**\n• Vitamine B12\n• Oméga-3 (graines de lin)\n• Fer\n• Zinc\n\n**Repas végan recommandés:**",
            'meals' => $meals,
        ];
    }
    
    private function handleKeto(string $message): array
    {
        $meals = $this->generateKetoMeals();
        
        return [
            'message' => "🥑 **Régime Keto / Cétogène**\n\n**Principes:**\n• Glucides: <50g/jour\n• Protéines: modérées\n• Lipides: 70-80% des calories\n\n**Aliments autorisés:**\n✅ Viandes grasses\n✅ Poissons gras\n✅ Œufs\n✅ Fromages\n✅ Avocat\n✅ Huiles\n✅ Noix\n\n**À éviter:**\n❌ Pain, pâtes, riz\n❌ Fruits sucrés\n❌ Pommes de terre\n❌ Légumes féculents\n\n**Repas keto:**",
            'meals' => $meals,
        ];
    }
    
    private function handleSport(string $message): array
    {
        $advice = $this->generateSportNutritionAdvice();
        
        return [
            'message' => "🏃 **Nutrition du sportif**\n\n{$advice}\n\n**Avant l'exercice (2-3h avant):**\n• Repas complet: glucides complexes + protéines\n• Éviter les lipides\n\n**Pendant l'exercice (>1h):**\n• Boissons énergétiques\n• Banane, fruits secs\n\n**Après l'exercice (30min-2h):**\n• Proteins: 20-40g\n• Glucides: 1g/kg poids\n• Hydratation: 1.5L par kg perdu",
            'meals' => [],
        ];
    }
    
    private function handleHydration(string $message): array
    {
        $waterTarget = $this->userProfile['water'] ?? 2;
        
        return [
            'message' => "💧 **Hydratation**\n\n**Votre objectif:** {$waterTarget}L par jour\n\n**Répartition recommandée:**\n• Au réveil: 1 verre (250ml)\n• Petit-déjeuner: 1 verre\n• Midi: 2 verres\n• Après-midi: 2 verres\n• Soir: 1-2 verres\n\n**Signes de déshydratation:**\n• Soif intense (trop tard!)\n• Urine foncée\n• Fatigue\n• Maux de tête\n• Peau sèche\n\n**Conseils:**\n✅ Buvez régulièrement\n✅ Ajoutez du citron/cucumber\n✅ Évitez les sodas\n✅ Alternez avec tisanes",
            'meals' => [],
        ];
    }
    
    private function handleCalories(string $message): array
    {
        $target = $this->userProfile['calories'] ?? 2000;
        $consumed = $this->getTodayCalories();
        
        return [
            'message' => "📊 **Calories**\n\n**Votre objectif quotidien:** {$target} kcal\n\n**Aujourd'hui:** {$consumed} kcal consommés\n**Restant:** " . max(0, $target - $consumed) . " kcal\n\n" . ($consumed > $target ? "⚠️ Vous avez dépassé votre objectif" : "✅ Dans les limites") . "\n\n**Répartition recommandée:**\n🍳 Petit-déjeuner: " . round($target * 0.25) . " kcal (25%)\n🍱 Déjeuner: " . round($target * 0.35) . " kcal (35%)\n🍽️ Dîner: " . round($target * 0.30) . " kcal (30%)\n🍿 Collation: " . round($target * 0.10) . " kcal (10%)",
            'meals' => [],
        ];
    }
    
    private function handleProtein(string $message): array
    {
        $target = $this->userProfile['protein'] ?? 120;
        
        return [
            'message' => "💪 **Protéines**\n\n**Votre objectif:** {$target}g par jour\n\n**Sources de protéines de qualité:**\n\n🥩 **Animales (haute qualité):**\n• Poulet: 31g/100g\n• Bœuf: 26g/100g\n• Poisson: 20-25g/100g\n• Œufs: 6g/unité\n• Yaourt grec: 10g/100g\n\n🌱 **Végétales:**\n• Lentilles: 9g/100g\n• Pois chiches: 8.9g/100g\n• Tofu: 8g/100g\n• Tempeh: 19g/100g\n• Seitan: 25g/100g\n\n**Calculateur rapide:**\n1g protéines = 4 kcal\n\nProtéines aujourd'hui: {$this->getTodayProtein()}g",
            'meals' => [],
        ];
    }
    
    private function handleCarbs(string $message): array
    {
        $target = $this->userProfile['carbs'] ?? 200;
        
        return [
            'message' => "🍚 **Glucides**\n\n**Votre objectif:** {$target}g par jour\n\n**Types de glucides:**\n\n⏰ **Lents (complexes):**\n• Riz complet\n• Patate douce\n• Avoine\n• Quinoa\n• Légumineuses\n\n⚡ **Rapides (simples):**\n• Fruits\n• Miel\n• Sucreries (limiter)\n\n**Rôle des glucides:**\n✅ Source d'énergie principale\n✅ Cerveau: 120g/jour minimum\n✅ Performance sportive\n\nGlucides aujourd'hui: {$this->getTodayCarbs()}g",
            'meals' => [],
        ];
    }
    
    private function handleFats(string $message): array
    {
        $target = $this->userProfile['fats'] ?? 65;
        
        return [
            'message' => "🥑 **Lipides**\n\n**Votre objectif:** {$target}g par jour\n\n**Types de lipides:**\n\n✅ **Bonnes graisses:**\n• Oméga-3: poisson gras, lin, noix\n• Oméga-9: huile d'olive, avocat\n• Graisses mono-insaturées\n\n⚠️ **À limiter:**\n• Graisses saturées (charcuterie, fromage)\n• Acides gras trans (industriels)\n\n**Rôle des lipides:**\n✅ Hormones\n✅ Absorption vitamines A,D,E,K\n✅ Énergie (9kcal/g)\n\nLipides aujourd'hui: {$this->getTodayFats()}g",
            'meals' => [],
        ];
    }
    
    private function handleFiber(string $message): array
    {
        $target = $this->userProfile['fiber'] ?? 25;
        
        return [
            'message' => "🌾 **Fibres**\n\n**Votre objectif:** {$target}g par jour\n\n**Sources de fibres:**\n\n🥦 **Légumes:**\n• Artichauts: 10g/100g\n• Brocoli: 2.6g/100g\n• Carottes: 2.8g/100g\n\n🌾 **Céréales:**\n• Avoine: 10g/100g\n• Pain complet: 6g/100g\n\n🫘 **Légumineuses:**\n• Lentilles: 8g/100g\n• Haricots: 7g/100g\n\n🍎 **Fruits:**\n• Framboises: 5g/100g\n• Pommes: 2.4g/100g\n\n**Bénéfices:**\n✅ Digestion\n✅ Satiété\n✅ Cholesterol\n✅ Glycémie",
            'meals' => [],
        ];
    }
    
    private function handleAnalysis(string $message): array
    {
        $todayStats = $this->getTodayStats();
        
        return [
            'message' => "📊 **Analyse du jour**\n\n**Apports aujourd'hui:**\n\n🔥 Calories: {$todayStats['calories']}/{$todayStats['targetCalories']} kcal (" . round($todayStats['calories'] / max(1, $todayStats['targetCalories']) * 100) . "%)\n\n🥩 Protéines: {$todayStats['protein']}/{$todayStats['targetProtein']}g (" . round($todayStats['protein'] / max(1, $todayStats['targetProtein']) * 100) . "%)\n\n🍚 Glucides: {$todayStats['carbs']}/{$todayStats['targetCarbs']}g (" . round($todayStats['carbs'] / max(1, $todayStats['targetCarbs']) * 100) . "%)\n\n🥑 Lipides: {$todayStats['fats']}/{$todayStats['targetFats']}g (" . round($todayStats['fats'] / max(1, $todayStats['targetFats']) * 100) . "%)\n\n💧 Eau: {$todayStats['water']}/{$todayStats['targetWater']}L",
            'meals' => [],
        ];
    }
    
    private function handleDiabetes(): array
    {
        return [
            'message' => "🩸 **Gestion du diabète**\n\n**Conseils alimentaires:**\n\n✅ **À privilégier:**\n• Légumes non féculents\n• Protéines maigres\n• Graisses saines\n• Fibres\n\n⚠️ **À limiter:**\n• Sucres simples\n• Farines raffinées\n• Fruits secs\n• Boissons sucrées\n\n📊 **Index glycémique:**\n• Préférez IG < 55\n• Combinez glucides + protéines + lipides\n\n🍽️ **Repas réguliers:**\n• 3 repas par jour\n• Évitez le grignotage\n\n💊 **Important:**\nSuivez les recommandations de votre médecin",
            'meals' => [],
        ];
    }
    
    private function handleHeartHealth(): array
    {
        return [
            'message' => "❤️ **Santé cardiovasculaire**\n\n**Aliments bons pour le cœur:**\n\n🟢 **À volonté:**\n• Fruits et légumes\n• Poissons gras (saumon, maquereau)\n• Huiles végétales\n• Noix\n\n🟡 **Avec modération:**\n• Viandes maigres\n• Œufs\n• Produits laitiers\n\n🔴 **À limiter:**\n• Sel (<5g/jour)\n• Graisses saturées\n• Sucres ajoutés\n• Alcool\n\n**Statines naturelles:**\n• Ail\n• Son d'avoine\n• Poissons gras\n\n🏃 **Mode de vie:**\n• Exercise régulière\n• Pas de tabac\n• Gestion du stress",
            'meals' => [],
        ];
    }
    
    private function handlePregnancy(): array
    {
        return [
            'message' => "🤰 **Nutrition pendant la grossesse**\n\n**Nutriments essentiels:**\n\n🥩 **Protéines:** 70-100g/jour\n• Viandes, poissons, œufs\n• Légumineuses\n\n🥛 **Calcium:** 1000mg/jour\n• Produits laitiers\n• Végétales riches en calcium\n\n🌿 **Acide folique:** 400μg/jour\n• Légumes verts\n• Complément recommandé\n\n⚡ **Fer:** 30mg/jour\n• Viandes rouges\n• Lentilles, épinards\n\n**À éviter:**\n❌ Alcool\n❌ Poisson cru\n❌ Fromages au lait cru\n❌ Charcuterie\n\n**Prise de poids recommandée:**\n• Sous-poids: 12-18kg\n• Normal: 11-16kg\n• Surpoids: 7-11kg\n• Obèse: 5-9kg",
            'meals' => [],
        ];
    }
    
    private function handleDeficiency(string $message): array
    {
        return [
            'message' => "🔬 **Carences nutritionnelles courantes**\n\n**Fer:**\n• Symptômes: fatigue, pâleur\n• Sources: viande rouge, légumineuses\n\n**Vitamine D:**\n• Symptômes: os fragiles\n• Sources: soleil, poisson gras\n\n**Vitamine B12:**\n• Symptômes: anémie, fourmillements\n• Sources: incontourn\n\n**Calcium:**\n• Symptômes: ostéoporose\n• Sources: produits laitaires\n\n**Magnésium:**\n• Symptômes: crampes, stress\n• Sources: noix, chocolat noir\n\n💡 **Conseil:**\nFaites une prise de sang annuelle",
            'meals' => [],
        ];
    }
    
    private function handleBudget(string $message): array
    {
        return [
            'message' => "💰 **Nutrition économique**\n\n**Astuces budget:**\n\n🛒 **Courses intelligentes:**\n• Légumes de saison\n• Promo sur congelés\n• Marques distributeur\n• Vrac\n\n🍳 **Repas économiques:**\n• Lentilles, pois chiches\n• Œufs (8g protéine/unité)\n• Pommes de terre\n• Riz, pâtes\n\n📦 **Conservation:**\n• Congelez les surplus\n• Restes = nouveaux repas\n\n🚫 **Économies inutiles:**\n• Supplements inutiles\n• Produits \"bio\" non nécessaires\n• Plats préparés",
            'meals' => $this->generateBudgetMeals(),
        ];
    }
    
    private function handleQuickMeal(string $message): array
    {
        return [
            'message' => "⏱️ **Repas rapides**\n\n**Moins de 10 minutes:**\n\n🍳 **Œufs brouillés**\n3 œufs + lait + épices\n\n🥗 **Salade composée**\nLégumes + vinaigrette + protéines\n\n🥪 **Sandwich garni**\nPain complet + dinde + fromage\n\n🍜 **Pâtes rapides**\nPâtes + sauce tomate + basilic\n\n🍳 **Omelette**\nŒufs + fromage + légumes\n\n🍌 **Smoothie**\nFruits + lait + miel",
            'meals' => $this->generateQuickMeals(),
        ];
    }
    
    private function handleSeasonal(string $message): array
    {
        $season = $this->detectSeason();
        
        return [
            'message' => "🍂 **Aliments de saison - " . ucfirst($season) . "**\n\n" . $this->getSeasonalFoods($season),
            'meals' => [],
        ];
    }
    
    private function handleProgress(): array
    {
        $stats = $this->getTodayStats();
        
        return [
            'message' => "📈 **Votre progression**\n\n**Aujourd'hui:**\n\n" . $this->formatProgressBar('Calories', $stats['calories'], $stats['targetCalories']) . "\n\n" . $this->formatProgressBar('Protéines', $stats['protein'], $stats['targetProtein'], 'g') . "\n\n" . $this->formatProgressBar('Glucides', $stats['carbs'], $stats['targetCarbs'], 'g') . "\n\n" . $this->formatProgressBar('Lipides', $stats['fats'], $stats['targetFats'], 'g') . "\n\n" . $this->formatProgressBar('Eau', $stats['water'], $stats['targetWater'], 'L') . "\n\n" . ($this->userProfile['weight'] ? "⚖️ Poids actuel: {$this->userProfile['weight']}kg" : ""),
            'meals' => [],
        ];
    }
    
    private function handleRecommendations(string $message): array
    {
        return [
            'message' => "💡 **Recommandations personnalisées**\n\n" . ($this->generatePersonalizedRecommendations()),
            'meals' => [],
        ];
    }
    
    private function handleGroceryList(string $message): array
    {
        // Get grocery items with prices
        $groceryItems = $this->priceService->getAllPrices();
        
        // Build a beautiful response with prices in Tunisian format
        $response = "🛒═══════════════════════════════════\n";
        $response .= "   LISTE DE COURSES - PRIX TUNISIE\n";
        $response .= "═══════════════════════════════════\n\n";
        $response .= "📌 *Prix moyens du marché tunisien*\n\n";
        
        $categories = $this->priceService->getCategories();
        
        foreach ($categories as $category) {
            $response .= "📁 **$category**\n";
            $response .= "────────────────────────────────\n";
            $items = $this->priceService->getItemsByCategory($category);
            
            // Show up to 6 items per category
            $count = 0;
            foreach ($items as $name => $data) {
                if ($count >= 6) {
                    $response .= "  ➕ ... et plus\n";
                    break;
                }
                $price = $this->priceService->formatPrice($data['price']);
                $response .= "  • $name: $price / {$data['unit']}\n";
                $count++;
            }
            $response .= "\n";
        }
        
        $response .= "═══════════════════════════════════\n";
        $response .= "💡 Créez votre liste personnalisée:\n";
        $response .= "   👉 [Liste de courses](http://127.0.0.1:8000/nutrition/grocery-list)\n\n";
        $response .= "📥 PDF: [Télécharger](http://127.0.0.1:8000/nutrition/grocery-list/pdf)\n";
        
        return [
            'message' => $response,
            'meals' => [],
        ];
    }
    
    private function handleProductInfo(string $message): array
    {
        // Extract product name - be more flexible, search entire message
        $allPrices = $this->priceService->getAllPrices();
        $foundProducts = [];
        
        // Search for any product name in the message
        foreach ($allPrices as $name => $data) {
            if (stripos($message, strtolower($name)) !== false) {
                $foundProducts[$name] = $data;
            }
        }
        
        // If no specific product found, check for general nutrition keywords
        if (empty($foundProducts)) {
            if ($this->containsAny($message, ['calories', 'nutriment', 'vitamine', 'proteine', 'gras', 'sucre', 'fiber', 'lipide'])) {
                return $this->handleNutritionInfo($message);
            }
            
            // Show general grocery list
            return $this->handleGroceryList($message);
        }
        
        $response = "🍎═══════════════════════════════════\n";
        $response .= "   INFORMATIONS PRODUIT\n";
        $response .= "═══════════════════════════════════\n\n";
        
        foreach ($foundProducts as $name => $data) {
            $price = $this->priceService->formatPrice($data['price']);
            $calories = $data['calories'] ?? 0;
            
            $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $response .= "🍽️  **$name**\n\n";
            $response .= "   📦 Catégorie: {$data['category']}\n";
            $response .= "   💰 Prix: $price / {$data['unit']}\n";
            $response .= "   🔥 Calories: $calories kcal/100g\n";
            $response .= "\n";
        }
        $response .= "═══════════════════════════════════\n";
        
        return [
            'message' => $response,
            'meals' => [],
        ];
    }
    
    private function handleNutritionInfo(string $message): array
    {
        $response = "📊 **Informations nutritionnelles**\n\n";
        
        if ($this->containsAny($message, ['calories', 'combien'])) {
            $response .= "Calories par 100g:\n";
            $response .= "• Fruits: 30-80 kcal\n";
            $response .= "• Viandes: 150-300 kcal\n";
            $response .= "• Legumes: 15-50 kcal\n";
            $response .= "• Produits laitiers: 40-150 kcal\n";
        }
        
        if ($this->containsAny($message, ['proteine', 'protein'])) {
            $response .= "\nProteines:\n";
            $response .= "• Poulet: 31g\n";
            $response .= "• Poisson: 20-25g\n";
            $response .= "• Oeufs: 13g\n";
            $response .= "• Legumineuses: 8-10g\n";
        }
        
        if ($this->containsAny($message, ['gras', 'fat', 'lipide'])) {
            $response .= "\nMatieres grasses:\n";
            $response .= "• Huiles: 100g\n";
            $response .= "• Beurre: 81g\n";
            $response .= "• Avocat: 15g\n";
        }
        
        return [
            'message' => $response,
            'meals' => [],
        ];
    }
    
    private function handleDefault(string $message): array
    {
        $goalInfo = $this->userProfile ? " ({$this->userProfile['calories']}kcal/jour)" : "";
        
        return [
            'message' => "Je n'ai pas bien compris votre demande. 😕\n\nEssayez ces commandes:\n\n🍳 \"recette\", \"recette déjeuner\"\n📅 \"menu semaine\"\n⚖️ \"perdre du poids\"\n💪 \"prise de muscle\"\n🌱 \"vegan\", \"keto\"\n🏃 \"sportif\"\n💧 \"eau\"\n📊 \"calories\", \"protéines\"\n\n{$goalInfo}\n\nOu tapez \"aide\" pour voir toutes les options!",
            'meals' => [],
        ];
    }
    
    // ==================== HELPER METHODS ====================
    
    private function detectMealType(string $message): string
    {
        $breakfast = ['petit-déjeuner', 'breakfast', 'matin', 'matinale', 'matinée'];
        $lunch = ['déjeuner', 'lunch', 'midi'];
        $dinner = ['dîner', 'dinner', 'soir', 'soirée'];
        $snack = ['snack', 'collation', 'gouter', 'goûter'];
        
        if ($this->containsAny($message, $breakfast)) return 'breakfast';
        if ($this->containsAny($message, $lunch)) return 'lunch';
        if ($this->containsAny($message, $dinner)) return 'dinner';
        if ($this->containsAny($message, $snack)) return 'snack';
        
        return 'lunch'; // default
    }
    
    private function containsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
    
    private function getTodayCalories(): int
    {
        if (!$this->userId) return 0;
        
        $repo = $this->entityManager->getRepository(\App\Entity\FoodLog::class);
        $today = new DateTime();
        $logs = $repo->findAllByUserIdAndDate($this->userId, $today);
        
        $total = 0;
        foreach ($logs as $log) {
            foreach ($log->getFoodItems() as $item) {
                $total += $item->getCalories() ?? 0;
            }
        }
        
        return $total;
    }
    
    private function getTodayProtein(): float
    {
        if (!$this->userId) return 0;
        
        $repo = $this->entityManager->getRepository(\App\Entity\FoodLog::class);
        $today = new DateTime();
        $logs = $repo->findAllByUserIdAndDate($this->userId, $today);
        
        $total = 0;
        foreach ($logs as $log) {
            foreach ($log->getFoodItems() as $item) {
                $total += floatval($item->getProtein() ?? 0);
            }
        }
        
        return $total;
    }
    
    private function getTodayCarbs(): float
    {
        if (!$this->userId) return 0;
        
        $repo = $this->entityManager->getRepository(\App\Entity\FoodLog::class);
        $today = new DateTime();
        $logs = $repo->findAllByUserIdAndDate($this->userId, $today);
        
        $total = 0;
        foreach ($logs as $log) {
            foreach ($log->getFoodItems() as $item) {
                $total += floatval($item->getCarbs() ?? 0);
            }
        }
        
        return $total;
    }
    
    private function getTodayFats(): float
    {
        if (!$this->userId) return 0;
        
        $repo = $this->entityManager->getRepository(\App\Entity\FoodLog::class);
        $today = new DateTime();
        $logs = $repo->findAllByUserIdAndDate($this->userId, $today);
        
        $total = 0;
        foreach ($logs as $log) {
            foreach ($log->getFoodItems() as $item) {
                $total += floatval($item->getFats() ?? 0);
            }
        }
        
        return $total;
    }
    
    private function getTodayStats(): array
    {
        $targetCal = $this->userProfile['calories'] ?? 2000;
        $targetProt = $this->userProfile['protein'] ?? 120;
        $targetCarbs = $this->userProfile['carbs'] ?? 200;
        $targetFats = $this->userProfile['fats'] ?? 65;
        $targetWater = $this->userProfile['water'] ?? 2;
        
        return [
            'calories' => $this->getTodayCalories(),
            'protein' => round($this->getTodayProtein()),
            'carbs' => round($this->getTodayCarbs()),
            'fats' => round($this->getTodayFats()),
            'water' => $this->getTodayWater(),
            'targetCalories' => $targetCal,
            'targetProtein' => $targetProt,
            'targetCarbs' => $targetCarbs,
            'targetFats' => $targetFats,
            'targetWater' => $targetWater,
        ];
    }
    
    private function getTodayWater(): float
    {
        if (!$this->userId) return 0;
        
        $repo = $this->entityManager->getRepository(WaterIntake::class);
        $today = new DateTime();
        $intakes = $repo->findByUserIdAndDate($this->userId, $today);
        
        $total = 0;
        foreach ($intakes as $intake) {
            $total += $intake->getGlasses() ?? 0;
        }
        
        return $total / 1000; // Convert to liters
    }
    
    private function formatGoalSummary(): string
    {
        return "\n\n🎯 Vos objectifs:\n• {$this->userProfile['calories']}kcal\n• {$this->userProfile['protein']}g protéines\n• {$this->userProfile['carbs']}g glucides\n• {$this->userProfile['fats']}g lipides";
    }
    
    private function getQuickActions(): array
    {
        return [
            ['label' => '🍳 Recettes', 'action' => 'recette déjeuner'],
            ['label' => '📅 Menu semaine', 'action' => 'menu semaine'],
            ['label' => '⚖️ Perdre du poids', 'action' => 'perdre du poids'],
            ['label' => '💪 Muscle', 'action' => 'prise de muscle'],
            ['label' => '🌱 Végan', 'action' => 'régime vegan'],
            ['label' => '💧 Hydratation', 'action' => 'eau'],
        ];
    }
    
    private function generateMealSuggestions(string $mealType, int $count = 4): array
    {
        $targetCal = $this->userProfile['calories'] ?? 2000;
        $targetProt = $this->userProfile['protein'] ?? 120;
        $targetCarbs = $this->userProfile['carbs'] ?? 200;
        $targetFats = $this->userProfile['fats'] ?? 65;
        
        $calories = [
            'breakfast' => round($targetCal * 0.25),
            'lunch' => round($targetCal * 0.35),
            'dinner' => round($targetCal * 0.30),
            'snack' => round($targetCal * 0.10),
        ];
        
        $meals = [
            'breakfast' => [
                ['name' => 'Omelette Légumes', 'calories' => $calories['breakfast'], 'protein' => round($targetProt * 0.20), 'carbs' => round($targetCarbs * 0.15), 'fats' => round($targetFats * 0.25), 'mealType' => 'breakfast', 'description' => 'Omelette avec légumes de saison'],
                ['name' => 'Yaourt Grec + Fruits', 'calories' => $calories['breakfast'] - 30, 'protein' => round($targetProt * 0.18), 'carbs' => round($targetCarbs * 0.22), 'fats' => round($targetFats * 0.10), 'mealType' => 'breakfast', 'description' => 'Yaourt grec nature + fruits rouges'],
                ['name' => 'Toast Avocat Oeuf', 'calories' => $calories['breakfast'] + 20, 'protein' => round($targetProt * 0.15), 'carbs' => round($targetCarbs * 0.25), 'fats' => round($targetFats * 0.35), 'mealType' => 'breakfast', 'description' => 'Pain complet avec avocat et œuf poché'],
                ['name' => 'Porridge Avoine', 'calories' => $calories['breakfast'] + 10, 'protein' => round($targetProt * 0.12), 'carbs' => round($targetCarbs * 0.35), 'fats' => round($targetFats * 0.15), 'mealType' => 'breakfast', 'description' => 'Flocons d\'avoine avec fruits frais'],
                ['name' => 'Smoothie Protéiné', 'calories' => $calories['breakfast'] - 50, 'protein' => round($targetProt * 0.25), 'carbs' => round($targetCarbs * 0.30), 'fats' => round($targetFats * 0.08), 'mealType' => 'breakfast', 'description' => 'Smoothie banane, lait d\'amande, protéine'],
                ['name' => 'Galette Sarrasin Oeuf', 'calories' => $calories['breakfast'], 'protein' => round($targetProt * 0.22), 'carbs' => round($targetCarbs * 0.20), 'fats' => round($targetFats * 0.20), 'mealType' => 'breakfast', 'description' => 'Galette de sarrasin avec œuf et fromage'],
            ],
            'lunch' => [
                ['name' => 'Salade Quinoa Poulet', 'calories' => $calories['lunch'], 'protein' => round($targetProt * 0.35), 'carbs' => round($targetCarbs * 0.30), 'fats' => round($targetFats * 0.20), 'mealType' => 'lunch', 'description' => 'Quinoa, poulet grillé, avocat, légumes'],
                ['name' => 'Bowl Poke Thon', 'calories' => $calories['lunch'] - 50, 'protein' => round($targetProt * 0.32), 'carbs' => round($targetCarbs * 0.35), 'fats' => round($targetFats * 0.15), 'mealType' => 'lunch', 'description' => 'Bol hawaïen au thon, riz, Edamame'],
                ['name' => 'Wrap Poulet Légumes', 'calories' => $calories['lunch'] - 80, 'protein' => round($targetProt * 0.30), 'carbs' => round($targetCarbs * 0.30), 'fats' => round($targetFats * 0.22), 'mealType' => 'lunch', 'description' => 'Tortilla complète, poulet, crudités'],
                ['name' => 'Pâtes Complètes', 'calories' => $calories['lunch'] + 30, 'protein' => round($targetProt * 0.20), 'carbs' => round($targetCarbs * 0.45), 'fats' => round($targetFats * 0.18), 'mealType' => 'lunch', 'description' => 'Pâtes complètes sauce légumes'],
                ['name' => 'Bowl Saumon Avocat', 'calories' => $calories['lunch'] - 30, 'protein' => round($targetProt * 0.35), 'carbs' => round($targetCarbs * 0.25), 'fats' => round($targetFats * 0.30), 'mealType' => 'lunch', 'description' => 'Riz, saumon, avocat, concombre'],
                ['name' => 'Soupe Lentilles', 'calories' => $calories['lunch'] - 100, 'protein' => round($targetProt * 0.20), 'carbs' => round($targetCarbs * 0.35), 'fats' => round($targetFats * 0.10), 'mealType' => 'lunch', 'description' => 'Soupe de lentilles corail avec pain'],
            ],
            'dinner' => [
                ['name' => 'Saumon Grillé Herbes', 'calories' => $calories['dinner'], 'protein' => round($targetProt * 0.38), 'carbs' => round($targetCarbs * 0.12), 'fats' => round($targetFats * 0.35), 'mealType' => 'dinner', 'description' => 'Saumon grillé aux herbes + légumes'],
                ['name' => 'Poisson Blanc Vapeur', 'calories' => $calories['dinner'] - 100, 'protein' => round($targetProt * 0.40), 'carbs' => round($targetCarbs * 0.08), 'fats' => round($targetFats * 0.18), 'mealType' => 'dinner', 'description' => 'Poisson blanc vapeur citron'],
                ['name' => 'Poulet Rôti Brocoli', 'calories' => $calories['dinner'] - 50, 'protein' => round($targetProt * 0.42), 'carbs' => round($targetCarbs * 0.12), 'fats' => round($targetFats * 0.25), 'mealType' => 'dinner', 'description' => 'Poulet rôti aux épices et brocoli'],
                ['name' => 'Steak Haché Légumes', 'calories' => $calories['dinner'] + 20, 'protein' => round($targetProt * 0.40), 'carbs' => round($targetCarbs * 0.15), 'fats' => round($targetFats * 0.30), 'mealType' => 'dinner', 'description' => 'Steak bœuf avec haricots verts'],
                ['name' => 'Curry Poulet Coco', 'calories' => $calories['dinner'], 'protein' => round($targetProt * 0.32), 'carbs' => round($targetCarbs * 0.25), 'fats' => round($targetFats * 0.35), 'mealType' => 'dinner', 'description' => 'Curry poulet au lait de coco'],
                ['name' => 'Tofu Stir Fry', 'calories' => $calories['dinner'] - 60, 'protein' => round($targetProt * 0.28), 'carbs' => round($targetCarbs * 0.25), 'fats' => round($targetFats * 0.25), 'mealType' => 'dinner', 'description' => 'Tofu sauté légumes sauce soja'],
            ],
            'snack' => [
                ['name' => 'Yaourt + Noix', 'calories' => $calories['snack'], 'protein' => round($targetProt * 0.10), 'carbs' => round($targetCarbs * 0.08), 'fats' => round($targetFats * 0.15), 'mealType' => 'snack', 'description' => 'Yaourt grec + noix'],
                ['name' => 'Fruit + Amandes', 'calories' => $calories['snack'], 'protein' => round($targetProt * 0.08), 'carbs' => round($targetCarbs * 0.12), 'fats' => round($targetFats * 0.10), 'mealType' => 'snack', 'description' => 'Pomme + amandes'],
                ['name' => 'Houmous + Légumes', 'calories' => $calories['snack'] - 30, 'protein' => round($targetProt * 0.06), 'carbs' => round($targetCarbs * 0.10), 'fats' => round($targetFats * 0.08), 'mealType' => 'snack', 'description' => 'Houmous + bâtonnets de légumes'],
            ],
        ];
        
        $options = $meals[$mealType] ?? $meals['lunch'];
        shuffle($options);
        return array_slice($options, 0, $count);
    }
    
    private function generateWeeklyPlan(): array
    {
        $targetCal = $this->userProfile['calories'] ?? 2000;
        
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $plan = [];
        
        foreach ($days as $index => $day) {
            $plan[] = [
                'day' => $index,
                'dayName' => $day,
                'meals' => [
                    ['name' => 'Petit-déjeuner santé', 'calories' => round($targetCal * 0.25), 'mealType' => 'breakfast'],
                    ['name' => 'Déjeuner équilibré', 'calories' => round($targetCal * 0.35), 'mealType' => 'lunch'],
                    ['name' => 'Dîner léger', 'calories' => round($targetCal * 0.30), 'mealType' => 'dinner'],
                ]
            ];
        }
        
        return $plan;
    }
    
    private function formatWeeklyPlan(array $plan): string
    {
        $output = "";
        
        foreach ($plan as $day) {
            $output .= "**📅 {$day['dayName']}**\n";
            foreach ($day['meals'] as $meal) {
                $emoji = match($meal['mealType']) {
                    'breakfast' => '🍳',
                    'lunch' => '🍱',
                    'dinner' => '🍽️',
                    default => '🍴'
                };
                $output .= "{$emoji} {$meal['name']}: {$meal['calories']} kcal\n";
            }
            $output .= "\n";
        }
        
        return $output;
    }
    
    private function generateVeganMeals(): array
    {
        return [
            ['name' => 'Bowl Quinoa Lentilles', 'calories' => 450, 'protein' => 18, 'carbs' => 65, 'fats' => 12, 'mealType' => 'lunch', 'description' => 'Quinoa, lentilles, avocat, légumes'],
            ['name' => 'Tofu Stir Fry', 'calories' => 380, 'protein' => 22, 'carbs' => 28, 'fats' => 18, 'mealType' => 'dinner', 'description' => 'Tofu, légumes, sauce soja'],
            ['name' => 'Burger Végétalien', 'calories' => 420, 'protein' => 15, 'carbs' => 55, 'fats' => 14, 'mealType' => 'lunch', 'description' => 'Pain, galette pois chiches, salade'],
            ['name' => 'Soupe Lentilles Corail', 'calories' => 280, 'protein' => 12, 'carbs' => 45, 'fats' => 4, 'mealType' => 'dinner', 'description' => 'Lentilles corail, carottes, épices'],
        ];
    }
    
    private function generateKetoMeals(): array
    {
        return [
            ['name' => 'Steak Fromage Salade', 'calories' => 520, 'protein' => 35, 'carbs' => 5, 'fats' => 42, 'mealType' => 'lunch', 'description' => 'Steak, fromage, salade verte'],
            ['name' => 'Saumon Avocat', 'calories' => 480, 'protein' => 32, 'carbs' => 4, 'fats' => 38, 'mealType' => 'dinner', 'description' => 'Saumon, avocat, huile d\'olive'],
            ['name' => 'Omelette Bacon Fromage', 'calories' => 420, 'protein' => 28, 'carbs' => 3, 'fats' => 34, 'mealType' => 'breakfast', 'description' => '3 œufs, bacon, fromage'],
            ['name' => 'Poulet Mayo', 'calories' => 450, 'protein' => 38, 'carbs' => 2, 'fats' => 32, 'mealType' => 'lunch', 'description' => 'Poulet, mayonnaise, légumes'],
        ];
    }
    
    private function generateBudgetMeals(): array
    {
        return [
            ['name' => 'Lentilles Riz', 'calories' => 380, 'protein' => 15, 'carbs' => 60, 'fats' => 6, 'mealType' => 'lunch', 'description' => 'Lentilles, riz, oignons'],
            ['name' => 'Pâtes Tomate Oeufs', 'calories' => 420, 'protein' => 14, 'carbs' => 55, 'fats' => 12, 'mealType' => 'dinner', 'description' => 'Pâtes, sauce tomate, œufs'],
            ['name' => 'Omelette Pommes Terre', 'calories' => 350, 'protein' => 12, 'carbs' => 30, 'fats' => 18, 'mealType' => 'breakfast', 'description' => 'Œufs, pommes de terre, oignons'],
        ];
    }
    
    private function generateQuickMeals(): array
    {
        return [
            ['name' => 'Oeufs Brouillés', 'calories' => 200, 'protein' => 14, 'carbs' => 2, 'fats' => 15, 'mealType' => 'breakfast', 'description' => '3 œufs, beurre, épices'],
            ['name' => 'Sandwich Complet', 'calories' => 350, 'protein' => 15, 'carbs' => 40, 'fats' => 12, 'mealType' => 'lunch', 'description' => 'Pain complet, dinde, fromage, légumes'],
            ['name' => 'Salade Composée', 'calories' => 250, 'protein' => 8, 'carbs' => 15, 'fats' => 16, 'mealType' => 'dinner', 'description' => 'Légumes, poulet, vinaigrette'],
        ];
    }
    
    private function generateWeightLossAdvice(): string
    {
        $current = $this->userProfile['weight'] ?? null;
        $goal = $this->userProfile['goalWeight'] ?? null;
        
        $calories = $this->userProfile['calories'] ?? 2000;
        $deficit = $this->userProfile['calories'] ? $this->userProfile['calories'] - 500 : 1500;
        
        return "**Votre plan personnalisé:**\n\n🎯 Déf calorique: {$deficit} kcal/jour\n" . ($goal && $current ? "⚖️ Objectif: {$goal}kg (actuel: {$current}kg)\n" : "") . "💧 Eau: {$this->userProfile['water']}L/jour";
    }
    
    private function generateGeneralWeightLossAdvice(): string
    {
        return "**Conseils pour perdre du poids:**\n\n⚠️ Créez un déficit calorique de 300-500 kcal\n🥗 Mangez 5 portions de fruits/légumes\n💧 Buvez 2L d'eau par jour\n🏃 Exercisez 30min quotidiennement\n😴 Dormez 7-8 heures";
    }
    
    private function generateMuscleAdvice(): string
    {
        $protein = $this->userProfile['protein'] ?? 150;
        $calories = $this->userProfile['calories'] ?? 2500;
        
        return "**Votre plan muscle:**\n\n💪 Protéines: {$protein}g/jour (1.8-2g/kg)\n🔥 Calories: {$calories} kcal/jour\n⏰ 4-5 repas par jour";
    }
    
    private function generateGeneralMuscleAdvice(): string
    {
        return "**Conseils pour la prise de muscle:**\n\n💪 Protéines: 1.6-2g par kg de poids\n🍚 Glucides: énergétique pour l'entraînement\n🥑 Lipides: 0.8g par kg\n⏰ Repas après l'entraînement";
    }
    
    private function generateSportNutritionAdvice(): string
    {
        return "**Nutrition du sportif:**\n\nAvant: Repas riche en glucides 2-3h avant\nPendant: Hydratation + électrolytes\nAprès: Proteins + glucides dans 30min";
    }
    
    private function generatePersonalizedRecommendations(): string
    {
        $recommendations = [];
        
        if ($this->userProfile) {
            $stats = $this->getTodayStats();
            
            if ($stats['calories'] > $stats['targetCalories']) {
                $recommendations[] = "⚠️ Vous avez dépassé vos calories aujourd'hui - privilégiez les aliments légers";
            }
            if ($stats['protein'] < $stats['targetProtein'] * 0.5) {
                $recommendations[] = "💪 Pensez à ajouter des protéines à vos repas";
            }
            if ($stats['water'] < $stats['targetWater'] * 0.5) {
                $recommendations[] = "💧 Hydratez-vous davantage";
            }
        }
        
        if (empty($recommendations)) {
            $recommendations[] = "✅ Vous êtes sur la bonne voie! Continuez comme ça";
            $recommendations[] = "💡_variez vos repas pour plus de nutriments";
        }
        
        return implode("\n\n", $recommendations);
    }
    
    private function detectSeason(): string
    {
        $month = (int)date('n');
        
        if ($month >= 3 && $month <= 5) return 'printemps';
        if ($month >= 6 && $month <= 8) return 'été';
        if ($month >= 9 && $month <= 11) return 'automne';
        return 'hiver';
    }
    
    private function getSeasonalFoods(string $season): string
    {
        $foods = [
            'printemps' => "• Artichauts\n• Asperges\n• Fèves\n• Fraises\n• Radis\n• Épinards",
            'été' => "• Tomates\n• Melon\n• Pêches\n• Abricots\n• Courgettes\n• Poivrons",
            'automne' => "• Pommes\n• Poires\n• Raisins\n• Champignons\n• Potimarron\n• Noix",
            'hiver' => "• Choux\n• Carottes\n• Navets\n• Agrumes\n• Topinambours\n• Patates douces"
        ];
        
        return $foods[$season] ?? '';
    }
    
    private function formatProgressBar(string $label, int $current, int $target, string $unit = ''): string
    {
        $percentage = min(100, round(($current / max(1, $target)) * 100));
        $filled = round($percentage / 10);
        $bar = str_repeat('█', $filled) . str_repeat('░', 10 - $filled);
        $color = $percentage > 100 ? '🔴' : ($percentage >= 80 ? '🟢' : '🟡');
        
        return "{$color} **{$label}**: {$current}/{$target}{$unit} ({$percentage}%)\n   [{$bar}]";
    }
    
    // ==================== DATABASE INITIALIZATION ====================
    
    private function initializeFoodDatabase(): void
    {
        $this->foodDatabase = [
            // Proteins
            'poulet' => ['calories' => 165, 'protein' => 31, 'carbs' => 0, 'fats' => 3.6, 'category' => 'protein'],
            'boeuf' => ['calories' => 250, 'protein' => 26, 'carbs' => 0, 'fats' => 15, 'category' => 'protein'],
            'poisson' => ['calories' => 208, 'protein' => 20, 'carbs' => 0, 'fats' => 13, 'category' => 'protein'],
            'saumon' => ['calories' => 208, 'protein' => 20, 'carbs' => 0, 'fats' => 13, 'category' => 'protein'],
            'thon' => ['calories' => 130, 'protein' => 29, 'carbs' => 0, 'fats' => 1, 'category' => 'protein'],
            'oeuf' => ['calories' => 155, 'protein' => 13, 'carbs' => 1.1, 'fats' => 11, 'category' => 'protein'],
            'tofu' => ['calories' => 76, 'protein' => 8, 'carbs' => 1.9, 'fats' => 4.8, 'category' => 'protein'],
            
            // Carbs
            'riz' => ['calories' => 130, 'protein' => 2.7, 'carbs' => 28, 'fats' => 0.3, 'category' => 'carbs'],
            'pates' => ['calories' => 131, 'protein' => 5, 'carbs' => 25, 'fats' => 1.1, 'category' => 'carbs'],
            'pain' => ['calories' => 265, 'protein' => 9, 'carbs' => 49, 'fats' => 3.2, 'category' => 'carbs'],
            'quinoa' => ['calories' => 120, 'protein' => 4.4, 'carbs' => 21, 'fats' => 1.9, 'category' => 'carbs'],
            'patate_douce' => ['calories' => 86, 'protein' => 1.6, 'carbs' => 20, 'fats' => 0.1, 'category' => 'carbs'],
            
            // Vegetables
            'brocoli' => ['calories' => 34, 'protein' => 2.8, 'carbs' => 7, 'fats' => 0.4, 'category' => 'vegetable'],
            'epinards' => ['calories' => 23, 'protein' => 2.9, 'carbs' => 3.6, 'fats' => 0.4, 'category' => 'vegetable'],
            'carotte' => ['calories' => 41, 'protein' => 0.9, 'carbs' => 10, 'fats' => 0.2, 'category' => 'vegetable'],
            'tomate' => ['calories' => 18, 'protein' => 0.9, 'carbs' => 3.9, 'fats' => 0.2, 'category' => 'vegetable'],
            'avocat' => ['calories' => 160, 'protein' => 2, 'carbs' => 8.5, 'fats' => 15, 'category' => 'vegetable'],
            
            // Fruits
            'pomme' => ['calories' => 52, 'protein' => 0.3, 'carbs' => 14, 'fats' => 0.2, 'category' => 'fruit'],
            'banane' => ['calories' => 89, 'protein' => 1.1, 'carbs' => 23, 'fats' => 0.3, 'category' => 'fruit'],
            'fraise' => ['calories' => 32, 'protein' => 0.7, 'carbs' => 7.7, 'fats' => 0.3, 'category' => 'fruit'],
            'orange' => ['calories' => 47, 'protein' => 0.9, 'carbs' => 12, 'fats' => 0.1, 'category' => 'fruit'],
            
            // Dairy
            'yaourt' => ['calories' => 59, 'protein' => 10, 'carbs' => 3.6, 'fats' => 0.4, 'category' => 'dairy'],
            'fromage' => ['calories' => 402, 'protein' => 25, 'carbs' => 1.3, 'fats' => 33, 'category' => 'dairy'],
            'lait' => ['calories' => 42, 'protein' => 3.4, 'carbs' => 5, 'fats' => 1, 'category' => 'dairy'],
        ];
    }
    
    private function initializeNutritionKnowledge(): void
    {
        $this->nutritionKnowledge = [
            'macros' => [
                'proteins' => [
                    'name' => 'Protéines',
                    'calories_per_gram' => 4,
                    'daily_need' => '0.8-2g par kg de poids',
                    'sources' => ['viande', 'poisson', 'oeufs', 'légumineuses', 'produits laitiers']
                ],
                'carbs' => [
                    'name' => 'Glucides',
                    'calories_per_gram' => 4,
                    'daily_need' => '45-65% des calories',
                    'sources' => ['riz', 'pâtes', 'pain', 'fruits', 'légumes']
                ],
                'fats' => [
                    'name' => 'Lipides',
                    'calories_per_gram' => 9,
                    'daily_need' => '20-35% des calories',
                    'sources' => ['huiles', 'avocat', 'noix', 'poissons gras']
                ]
            ],
            'vitamins' => [
                'A' => ['sources' => ['carottes', 'patates douces', 'foie'], 'deficiency' => 'cécité nocturne'],
                'B12' => ['sources' => ['viande', 'poisson', 'oeufs'], 'deficiency' => 'anémie'],
                'C' => ['sources' => ['agrumes', 'fraises', 'poivrons'], 'deficiency' => 'scorbut'],
                'D' => ['sources' => ['soleil', 'poisson gras', 'oeufs'], 'deficiency' => 'ostéoporose'],
                'E' => ['sources' => ['huile', 'noix', 'graines'], 'deficiency' => 'problèmes neurologiques'],
                'K' => ['sources' => ['légumes verts', 'choux'], 'deficiency' => 'problèmes de coagulation']
            ],
            'minerals' => [
                'fer' => ['sources' => ['viande rouge', 'légumineuses', 'épinards'], 'deficiency' => 'anémie', 'rda' => '8-18mg'],
                'calcium' => ['sources' => ['produits laitaires', 'légumes verts'], 'deficiency' => 'ostéoporose', 'rda' => '1000mg'],
                'magnesium' => ['sources' => ['noix', 'graines', 'chocolat noir'], 'deficiency' => 'crampes', 'rda' => '300-400mg'],
                'zinc' => ['sources' => ['viande', 'fruits de mer', 'légumineuses'], 'deficiency' => 'immunodéficience', 'rda' => '8-11mg'],
                'potassium' => ['sources' => ['bananes', 'patates', 'légumes'], 'deficiency' => 'fatigue', 'rda' => '2000mg']
            ]
        ];
    }
}
