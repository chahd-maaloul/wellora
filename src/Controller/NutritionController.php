<?php

namespace App\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/nutrition', name: 'nutrition_')]
class NutritionController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('nutrition/dashboard.html.twig', [
            'calories' => ['consumed' => 1850, 'target' => 2000],
            'water' => ['intake' => 5, 'target' => 8],
            'macros' => ['proteins' => 85, 'carbs' => 180, 'fats' => 60],
            'meals' => [
                'breakfast' => ['calories' => 350, 'items' => ['Café noir', 'Pain complet avec confiture']],
                'lunch' => ['calories' => 650, 'items' => ['Salade composée', 'Poulet rôti', 'Riz']],
                'dinner' => ['calories' => 550, 'items' => ['Soupe de légumes', 'Yaourt nature']],
                'snacks' => ['calories' => 300, 'items' => ['Pomme', 'Amandes']]
            ],
            'recentFoods' => [
                ['name' => 'Pomme', 'calories' => 95],
                ['name' => 'Banane', 'calories' => 105],
                ['name' => 'Yaourt', 'calories' => 120],
                ['name' => 'Oeuf', 'calories' => 78],
            ],
            'quickAddFoods' => [
                ['name' => 'Pomme', 'calories' => 95, 'unit' => 'pièce'],
                ['name' => 'Banane', 'calories' => 105, 'unit' => 'pièce'],
                ['name' => 'Yaourt', 'calories' => 120, 'unit' => 'pièce'],
                ['name' => 'Oeuf', 'calories' => 78, 'unit' => 'pièce'],
                ['name' => 'Lait', 'calories' => 150, 'unit' => 'verre'],
                ['name' => 'Café', 'calories' => 5, 'unit' => 'tasse'],
            ],
            'nutritionist' => [
                'id' => 1,
                'name' => 'Dr. Marie Dubois',
                'avatar' => null,
                'nextAppointment' => ['date' => new DateTime('+3 days 14:00')]
            ],
            'badges' => [
                ['name' => '7 jours consécutifs', 'icon' => 'fa-fire'],
                ['name' => 'Objectif atteint', 'icon' => 'fa-trophy'],
                ['name' => 'Hydratation', 'icon' => 'fa-glass-water'],
            ],
        ]);
    }

    #[Route('/diary', name: 'food_diary')]
    public function foodDiary(): Response
    {
        return $this->render('nutrition/food-diary.html.twig', [
            'dailySummary' => [
                'calories' => 1850,
                'calorieTarget' => 2000,
                'proteins' => 85,
                'proteinTarget' => 120,
                'carbs' => 180,
                'carbTarget' => 250,
                'fats' => 60,
                'fatTarget' => 70,
            ],
            'water' => ['intake' => 5, 'target' => 8],
            'macros' => ['proteins' => 85, 'carbs' => 180, 'fats' => 60],
            'meals' => [
                'breakfast' => ['calories' => 350, 'items' => [
                    ['name' => 'Café noir', 'quantity' => 1, 'calories' => 5, 'proteins' => 0, 'carbs' => 0, 'fats' => 0],
                    ['name' => 'Pain complet', 'quantity' => 2, 'calories' => 160, 'proteins' => 8, 'carbs' => 28, 'fats' => 2],
                    ['name' => 'Confiture', 'quantity' => 1, 'calories' => 50, 'proteins' => 0, 'carbs' => 12, 'fats' => 0],
                ]],
                'lunch' => ['calories' => 650, 'items' => [
                    ['name' => 'Salade composée', 'quantity' => 1, 'calories' => 200, 'proteins' => 5, 'carbs' => 15, 'fats' => 12],
                    ['name' => 'Poulet rôti', 'quantity' => 150, 'calories' => 250, 'proteins' => 45, 'carbs' => 0, 'fats' => 8],
                    ['name' => 'Riz blanc', 'quantity' => 150, 'calories' => 200, 'proteins' => 4, 'carbs' => 44, 'fats' => 0],
                ]],
                'dinner' => ['calories' => 550, 'items' => []],
                'snacks' => ['calories' => 300, 'items' => []],
            ],
            'foodGroups' => [
                'Fruits & Légumes' => ['percentage' => 35, 'color' => 'bg-green-500'],
                'Protéines' => ['percentage' => 25, 'color' => 'bg-amber-500'],
                'Glucides' => ['percentage' => 25, 'color' => 'bg-yellow-500'],
                'Lipides' => ['percentage' => 15, 'color' => 'bg-purple-500'],
            ],
            'nutritionAlerts' => [
                ['type' => 'warning', 'icon' => 'fa-exclamation-triangle', 'message' => 'Protéines en dessous de l\'objectif', 'recommendation' => 'Ajoutez des sources de protéines à vos repas'],
                ['type' => 'info', 'icon' => 'fa-info-circle', 'message' => 'Hydratation insuffisante', 'recommendation' => 'Buvez 3 verres d\'eau supplémentaires'],
            ],
        ]);
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

    #[Route('/planner', name: 'meal_planner')]
    public function mealPlanner(): Response
    {
        return $this->render('nutrition/meal-planner.html.twig', [
            'mealPlan' => [],
            'weeklyAvg' => [
                'calories' => 1950,
                'cost' => 45,
                'prepTime' => 30,
                'goalsMet' => 5,
            ],
            'suggestedRecipes' => [
                ['id' => 1, 'name' => 'Salade de quinoa', 'calories' => 350, 'prepTime' => 15, 'macros' => 'P: 12g | G: 45g | L: 14g', 'image' => '/build/images/recipe-placeholder.svg'],
                ['id' => 2, 'name' => 'Poulet aux légumes', 'calories' => 450, 'prepTime' => 25, 'macros' => 'P: 40g | G: 25g | L: 18g', 'image' => '/build/images/recipe-placeholder.svg'],
            ],
            'allRecipes' => [
                ['id' => 1, 'name' => 'Salade de quinoa', 'calories' => 350, 'prepTime' => 15, 'macros' => 'P: 12g | G: 45g | L: 14g', 'image' => '/build/images/recipe-placeholder.svg'],
                ['id' => 2, 'name' => 'Smoothie fruits rouges', 'calories' => 250, 'prepTime' => 5, 'macros' => 'P: 5g | G: 35g | L: 8g', 'image' => '/build/images/recipe-placeholder.svg'],
            ],
        ]);
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

    #[Route('/goals', name: 'goals')]
    public function goals(): Response
    {
        return $this->render('nutrition/goals.html.twig', [
            'streaks' => [
                'logging' => 7,
                'water' => 5,
                'veggies' => 3,
            ],
            'badges' => [
                ['name' => '7 jours consécutifs', 'icon' => 'fa-fire'],
                ['name' => 'Objectif atteint', 'icon' => 'fa-trophy'],
                ['name' => 'Hydratation', 'icon' => 'fa-glass-water'],
                ['name' => 'Légumes', 'icon' => 'fa-carrot'],
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
}