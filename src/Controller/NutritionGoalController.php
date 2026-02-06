<?php

namespace App\Controller;

use App\Entity\NutritionGoal;
use App\Entity\NutritionGoalAdjustment;
use App\Entity\NutritionGoalAchievement;
use App\Entity\NutritionGoalMilestone;
use App\Entity\NutritionGoalProgress;
use App\Repository\NutritionGoalAchievementRepository;
use App\Repository\NutritionGoalAdjustmentRepository;
use App\Repository\NutritionGoalMilestoneRepository;
use App\Repository\NutritionGoalProgressRepository;
use App\Repository\NutritionGoalRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/nutrition/goals', name: 'nutrition_goals_')]
class NutritionGoalController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(NutritionGoalRepository $goalRepository): Response
    {
        // Demo data for visualization
        $activeGoals = [
            [
                'id' => 1,
                'name' => 'Perte de poids été 2024',
                'type' => 'WEIGHT_LOSS',
                'currentWeight' => 78.5,
                'targetWeight' => 72.0,
                'progress' => 65,
                'status' => 'ACTIVE',
                'priority' => 'HIGH',
                'daysRemaining' => 45,
                'isOnTrack' => true,
                'streak' => 12,
                'startDate' => new DateTime('-30 days'),
                'targetDate' => new DateTime('+45 days'),
            ],
            [
                'id' => 2,
                'name' => 'Manger plus de protéines',
                'type' => 'HEALTHY_EATING',
                'progress' => 80,
                'status' => 'ACTIVE',
                'priority' => 'MEDIUM',
                'streak' => 21,
            ],
        ];

        $stats = [
            'activeGoals' => count($activeGoals),
            'completedThisMonth' => 1,
            'totalStreak' => 33,
            'averageAdherence' => 85,
        ];

        return $this->render('nutrition/goals.html.twig', [
            'activeGoals' => $activeGoals,
            'stats' => $stats,
        ]);
    }

    #[Route('/wizard', name: 'wizard')]
    public function wizard(): Response
    {
        return $this->render('nutrition/goal-wizard.html.twig', [
            'goalTypes' => $this->getGoalTypes(),
            'activityLevels' => $this->getActivityLevels(),
            'priorities' => $this->getPriorities(),
        ]);
    }

    #[Route('/wizard/save', name: 'wizard_save', methods: ['POST'])]
    public function saveWizard(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Create new goal from wizard data
        $goal = new NutritionGoal();
        $goal->setName($data['name'] ?? 'Nouvel objectif');
        $goal->setGoalType($data['goalType']);
        $goal->setCurrentWeight($data['currentWeight'] ?? 0);
        $goal->setTargetWeight($data['targetWeight'] ?? 0);
        $goal->setCurrentCalories($data['currentCalories'] ?? 2000);
        $goal->setTargetCalories($data['targetCalories'] ?? 1800);
        $goal->setBmr($data['bmr'] ?? 0);
        $goal->setTdee($data['tdee'] ?? 0);
        $goal->setTargetProteinGrams($data['targetProtein'] ?? 120);
        $goal->setTargetCarbGrams($data['targetCarbs'] ?? 200);
        $goal->setTargetFatGrams($data['targetFats'] ?? 60);
        $goal->setTargetProteinPercent($data['proteinPercent'] ?? 30);
        $goal->setTargetCarbPercent($data['carbPercent'] ?? 40);
        $goal->setTargetFatPercent($data['fatPercent'] ?? 30);
        $goal->setTargetMealFrequency($data['mealFrequency'] ?? 3);
        $goal->setTargetWaterIntake($data['waterIntake'] ?? 8);
        $goal->setPriority($data['priority'] ?? NutritionGoal::PRIORITY_MEDIUM);
        $goal->setStatus(NutritionGoal::STATUS_ACTIVE);
        $goal->setStartDate(new DateTime());
        $goal->setTargetDate(new DateTime($data['targetDate'] ?? '+90 days'));
        $goal->setActivityLevel($data['activityLevel'] ?? NutritionGoal::ACTIVITY_MODERATELY_ACTIVE);

        // Calculate weekly weight change target
        if ($data['targetWeight'] && $data['currentWeight']) {
            $weightDiff = $data['targetWeight'] - $data['currentWeight'];
            $weeks = $goal->getStartDate()->diff($goal->getTargetDate())->days / 7;
            if ($weeks > 0) {
                $goal->setWeeklyWeightChangeTarget($weightDiff / $weeks);
                $goal->setExpectedWeightChangePerWeek($weightDiff / $weeks);
            }
        }

        $entityManager->persist($goal);
        $entityManager->flush();

        // Create default milestones
        $this->createDefaultMilestones($entityManager, $goal);

        return $this->json([
            'success' => true,
            'goalId' => $goal->getId(),
            'redirect' => $this->generateUrl('nutrition_goals_detail', ['id' => $goal->getId()]),
        ]);
    }

    #[Route('/{id}', name: 'detail')]
    public function detail(
        int $id,
        NutritionGoalRepository $goalRepository,
        NutritionGoalProgressRepository $progressRepository,
        NutritionGoalMilestoneRepository $milestoneRepository,
        NutritionGoalAchievementRepository $achievementRepository
    ): Response {
        $goal = $goalRepository->find($id);

        if (!$goal) {
            throw $this->createNotFoundException('Goal not found');
        }

        // Mock progress data for visualization
        $weeklyProgress = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = new DateTime("-{$i} days");
            $weeklyProgress[] = [
                'date' => $date->format('d/m'),
                'weight' => 78.5 - ($i * 0.1),
                'calories' => 1800 + rand(-200, 200),
                'protein' => 115 + rand(-15, 15),
                'adherence' => 80 + rand(-10, 15),
            ];
        }

        $milestones = [
            [
                'name' => 'Premier jalon',
                'targetValue' => 77.0,
                'unit' => 'kg',
                'isCompleted' => true,
                'completedDate' => new DateTime('-15 days'),
            ],
            [
                'name' => 'Deuxième jalon',
                'targetValue' => 75.0,
                'unit' => 'kg',
                'isCompleted' => false,
                'targetDate' => new DateTime('+15 days'),
            ],
            [
                'name' => 'Objectif final',
                'targetValue' => 72.0,
                'unit' => 'kg',
                'isCompleted' => false,
                'targetDate' => new DateTime('+45 days'),
            ],
        ];

        $recentAchievements = [
            [
                'name' => '7 jours consécutifs',
                'type' => 'STREAK',
                'tier' => 'BRONZE',
                'earnedAt' => new DateTime('-2 days'),
            ],
            [
                'name' => 'Premier jalon atteint',
                'type' => 'MILESTONE',
                'tier' => 'SILVER',
                'earnedAt' => new DateTime('-15 days'),
            ],
        ];

        return $this->render('nutrition/goal-detail.html.twig', [
            'goal' => $goal,
            'weeklyProgress' => $weeklyProgress,
            'milestones' => $milestones,
            'recentAchievements' => $recentAchievements,
            'goalTypes' => $this->getGoalTypes(),
        ]);
    }

    #[Route('/{id}/progress', name: 'progress')]
    public function progress(
        int $id,
        NutritionGoalRepository $goalRepository,
        NutritionGoalProgressRepository $progressRepository
    ): Response {
        $goal = $goalRepository->find($id);

        if (!$goal) {
            throw $this->createNotFoundException('Goal not found');
        }

        $progressRecords = $progressRepository->findRecentProgressByGoal($id, 30);

        // Mock data for visualization
        $progressData = [
            'weight' => [
                'current' => 78.5,
                'start' => 82.0,
                'target' => 72.0,
                'change' => -3.5,
                'changePercent' => 4.27,
                'weeklyAverage' => -0.7,
            ],
            'macros' => [
                'calories' => ['current' => 1850, 'target' => 1800, 'adherence' => 92],
                'protein' => ['current' => 115, 'target' => 120, 'adherence' => 88],
                'carbs' => ['current' => 195, 'target' => 200, 'adherence' => 95],
                'fats' => ['current' => 58, 'target' => 60, 'adherence' => 97],
            ],
            'adherence' => [
                'daily' => [85, 90, 78, 92, 88, 95, 82],
                'weeklyAverage' => 87,
                'monthlyTrend' => 'up',
            ],
            'timeline' => [
                'startDate' => new DateTime('-30 days'),
                'targetDate' => new DateTime('+45 days'),
                'daysElapsed' => 30,
                'daysRemaining' => 45,
                'progressPercent' => 40,
            ],
        ];

        return $this->render('nutrition/progress-tracking.html.twig', [
            'goal' => $goal,
            'progressData' => $progressData,
            'progressRecords' => $progressRecords,
        ]);
    }

    #[Route('/{id}/adjust', name: 'adjust')]
    public function adjust(
        int $id,
        NutritionGoalRepository $goalRepository,
        NutritionGoalAdjustmentRepository $adjustmentRepository
    ): Response {
        $goal = $goalRepository->find($id);

        if (!$goal) {
            throw $this->createNotFoundException('Goal not found');
        }

        $adjustments = $adjustmentRepository->findByGoal($id);

        // Mock adjustment suggestions
        $suggestions = [
            [
                'type' => 'calorie',
                'current' => 1800,
                'suggested' => 1750,
                'reason' => 'Ralentissement de la perte de poids détecté',
                'confidence' => 85,
            ],
            [
                'type' => 'macro',
                'current' => '30/40/30',
                'suggested' => '35/35/30',
                'reason' => 'Augmenter les protéines pour une meilleure récupération',
                'confidence' => 75,
            ],
        ];

        return $this->render('nutrition/goal-adjustment.html.twig', [
            'goal' => $goal,
            'adjustments' => $adjustments,
            'suggestions' => $suggestions,
        ]);
    }

    #[Route('/{id}/adjust/save', name: 'adjust_save', methods: ['POST'])]
    public function saveAdjustment(
        int $id,
        Request $request,
        NutritionGoalRepository $goalRepository,
        NutritionGoalAdjustmentRepository $adjustmentRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $goal = $goalRepository->find($id);

        if (!$goal) {
            return $this->json(['success' => false, 'message' => 'Goal not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $adjustment = new NutritionGoalAdjustment();
        $adjustment->setNutritionGoal($goal);
        $adjustment->setAdjustmentType($data['adjustmentType'] ?? NutritionGoalAdjustment::TYPE_MANUAL);
        $adjustment->setReason($data['reason'] ?? '');
        $adjustment->setPreviousCalories($goal->getTargetCalories());
        $adjustment->setNewCalories($data['newCalories'] ?? $goal->getTargetCalories());
        $adjustment->setPreviousProtein($goal->getTargetProteinGrams());
        $adjustment->setNewProtein($data['newProtein'] ?? $goal->getTargetProteinGrams());
        $adjustment->setPreviousCarbs($goal->getTargetCarbGrams());
        $adjustment->setNewCarbs($data['newCarbs'] ?? $goal->getTargetCarbGrams());
        $adjustment->setPreviousFats($goal->getTargetFatGrams());
        $adjustment->setNewFats($data['newFats'] ?? $goal->getTargetFatGrams());
        $adjustment->setRecommendations($data['recommendations'] ?? '');
        $adjustment->setDaysUntilNextReview($data['daysUntilNextReview'] ?? 14);
        $adjustment->setEffectiveFrom(new DateTime($data['effectiveFrom'] ?? 'now'));

        // Update goal with new values
        $goal->setTargetCalories($adjustment->getNewCalories());
        $goal->setTargetProteinGrams($adjustment->getNewProtein());
        $goal->setTargetCarbGrams($adjustment->getNewCarbs());
        $goal->setTargetFatGrams($adjustment->getNewFats());

        $entityManager->persist($adjustment);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Ajustement enregistré avec succès',
            'adjustmentId' => $adjustment->getId(),
        ]);
    }

    #[Route('/{id}/metrics', name: 'metrics')]
    public function metrics(
        int $id,
        NutritionGoalRepository $goalRepository,
        NutritionGoalProgressRepository $progressRepository
    ): Response {
        $goal = $goalRepository->find($id);

        if (!$goal) {
            throw $this->createNotFoundException('Goal not found');
        }

        // Mock success metrics
        $metrics = [
            'overall' => [
                'score' => 85,
                'rating' => 'Excellent',
                'trend' => 'up',
            ],
            'criteria' => [
                [
                    'name' => 'Perte de poids',
                    'target' => '0.5 kg/semaine',
                    'actual' => '0.7 kg/semaine',
                    'status' => 'ahead',
                    'weight' => 40,
                ],
                [
                    'name' => 'Adhésion calorique',
                    'target' => '90%',
                    'actual' => '92%',
                    'status' => 'on_track',
                    'weight' => 30,
                ],
                [
                    'name' => 'Adhésion aux macros',
                    'target' => '85%',
                    'actual' => '88%',
                    'status' => 'on_track',
                    'weight' => 20,
                ],
                [
                    'name' => 'Hydratation',
                    'target' => '8 verres/jour',
                    'actual' => '7.5 verres/jour',
                    'status' => 'slightly_behind',
                    'weight' => 10,
                ],
            ],
            'predictions' => [
                'completionDate' => new DateTime('+38 days'),
                'successProbability' => 88,
                'expectedFinalWeight' => 71.5,
            ],
            'recommendations' => [
                'Augmenter légèrement l\'hydratation',
                'Maintenir le déficit calorique actuel',
                'Envisager une journée de repos alimentaire',
            ],
        ];

        return $this->render('nutrition/success-metrics.html.twig', [
            'goal' => $goal,
            'metrics' => $metrics,
        ]);
    }

    #[Route('/{id}/progress/record', name: 'record_progress', methods: ['POST'])]
    public function recordProgress(
        int $id,
        Request $request,
        NutritionGoalRepository $goalRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $goal = $goalRepository->find($id);

        if (!$goal) {
            return $this->json(['success' => false, 'message' => 'Goal not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $progress = new NutritionGoalProgress();
        $progress->setNutritionGoal($goal);
        $progress->setDate(new DateTime($data['date'] ?? 'now'));
        $progress->setWeight($data['weight'] ?? null);
        $progress->setBodyFat($data['bodyFat'] ?? null);
        $progress->setCaloriesConsumed($data['calories'] ?? null);
        $progress->setProteinConsumed($data['protein'] ?? null);
        $progress->setCarbsConsumed($data['carbs'] ?? null);
        $progress->setFatsConsumed($data['fats'] ?? null);
        $progress->setWaterIntake($data['water'] ?? null);
        $progress->setAdherenceScore($data['adherenceScore'] ?? null);
        $progress->setGoalsMet($data['goalsMet'] ?? null);
        $progress->setNotes($data['notes'] ?? null);

        $entityManager->persist($progress);

        // Update goal current weight if provided
        if ($data['weight'] && $data['weight'] > 0) {
            $goal->setCurrentWeight($data['weight']);
        }

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Progression enregistrée',
            'progressId' => $progress->getId(),
        ]);
    }

    #[Route('/{id}/milestone/{milestoneId}/complete', name: 'complete_milestone', methods: ['POST'])]
    public function completeMilestone(
        int $id,
        int $milestoneId,
        NutritionGoalMilestoneRepository $milestoneRepository,
        NutritionGoalAchievementRepository $achievementRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $milestone = $milestoneRepository->find($milestoneId);

        if (!$milestone || $milestone->getNutritionGoal()->getId() !== $id) {
            return $this->json(['success' => false, 'message' => 'Milestone not found'], 404);
        }

        $milestone->setCompleted(true);
        $milestone->setCompletedDate(new DateTime());

        // Create achievement for milestone completion
        $achievement = new NutritionGoalAchievement();
        $achievement->setNutritionGoal($milestone->getNutritionGoal());
        $achievement->setName('Jalon complété: ' . $milestone->getName());
        $achievement->setAchievementType(NutritionGoalAchievement::TYPE_MILESTONE);
        $achievement->setTier(NutritionGoalAchievement::TIER_SILVER);
        $achievement->setPoints('50');
        $achievement->setDescription('Vous avez atteint un jalon important dans votre parcours nutritionnel.');

        $entityManager->persist($achievement);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Jalon complété !',
            'achievement' => [
                'name' => $achievement->getName(),
                'tier' => $achievement->getTier(),
                'points' => $achievement->getPoints(),
            ],
        ]);
    }

    private function getGoalTypes(): array
    {
        return [
            [
                'id' => NutritionGoal::GOAL_TYPE_WEIGHT_LOSS,
                'name' => 'Perte de poids',
                'icon' => 'fa-weight-hanging',
                'description' => 'Réduire votre poids corporel de manière saine',
            ],
            [
                'id' => NutritionGoal::GOAL_TYPE_WEIGHT_GAIN,
                'name' => 'Prise de masse',
                'icon' => 'fa-dumbbell',
                'description' => 'Augmenter votre masse musculaire',
            ],
            [
                'id' => NutritionGoal::GOAL_TYPE_MAINTENANCE,
                'name' => 'Maintien du poids',
                'icon' => 'fa-balance-scale',
                'description' => 'Stabiliser votre poids actuel',
            ],
            [
                'id' => NutritionGoal::GOAL_TYPE_BODY_RECOMPOSITION,
                'name' => 'Recomposition corporelle',
                'icon' => 'fa-user-edit',
                'description' => 'Perdre de la graisse tout en gainant les muscles',
            ],
            [
                'id' => NutritionGoal::GOAL_TYPE_BLOOD_SUGAR,
                'name' => 'Gestion du sucre',
                'icon' => 'fa-tint',
                'description' => 'Contrôler votre taux de sucre dans le sang',
            ],
            [
                'id' => NutritionGoal::GOAL_TYPE_CHOLESTEROL,
                'name' => 'Réduction du cholestérol',
                'icon' => 'fa-heart',
                'description' => 'Améliorer votre profil lipidique',
            ],
            [
                'id' => NutritionGoal::GOAL_TYPE_WATER_INTAKE,
                'name' => 'Hydratation',
                'icon' => 'fa-glass-water',
                'description' => 'Augmenter votre consommation d\'eau',
            ],
            [
                'id' => NutritionGoal::GOAL_TYPE_HEALTHY_EATING,
                'name' => 'Alimentation saine',
                'icon' => 'fa-apple-alt',
                'description' => 'Adopter de meilleures habitudes alimentaires',
            ],
        ];
    }

    private function getActivityLevels(): array
    {
        return [
            [
                'id' => NutritionGoal::ACTIVITY_SEDENTARY,
                'name' => 'Sédentaire',
                'multiplier' => 1.2,
                'description' => 'Peu ou pas d\'exercice, travail de bureau',
            ],
            [
                'id' => NutritionGoal::ACTIVITY_LIGHTLY_ACTIVE,
                'name' => 'Légèrement actif',
                'multiplier' => 1.375,
                'description' => 'Exercice léger 1-3 jours/semaine',
            ],
            [
                'id' => NutritionGoal::ACTIVITY_MODERATELY_ACTIVE,
                'name' => 'Modérément actif',
                'multiplier' => 1.55,
                'description' => 'Exercice modéré 3-5 jours/semaine',
            ],
            [
                'id' => NutritionGoal::ACTIVITY_VERY_ACTIVE,
                'name' => 'Très actif',
                'multiplier' => 1.725,
                'description' => 'Exercice intense 6-7 jours/semaine',
            ],
            [
                'id' => NutritionGoal::ACTIVITY_EXTRA_ACTIVE,
                'name' => 'Extrêmement actif',
                'multiplier' => 1.9,
                'description' => 'Exercice très intense, travail physique',
            ],
        ];
    }

    private function getPriorities(): array
    {
        return [
            ['id' => NutritionGoal::PRIORITY_HIGH, 'name' => 'Haute'],
            ['id' => NutritionGoal::PRIORITY_MEDIUM, 'name' => 'Moyenne'],
            ['id' => NutritionGoal::PRIORITY_LOW, 'name' => 'Basse'],
        ];
    }

    private function createDefaultMilestones(EntityManagerInterface $entityManager, NutritionGoal $goal): void
    {
        if (!$goal->getTargetWeight()) {
            return;
        }

        $startWeight = $goal->getCurrentWeight();
        $targetWeight = $goal->getTargetWeight();
        $totalChange = $targetWeight - $startWeight;
        $milestonesCount = 4;

        for ($i = 1; $i <= $milestonesCount; $i++) {
            $milestone = new NutritionGoalMilestone();
            $milestone->setNutritionGoal($goal);
            $milestone->setName('Jalon ' . $i . '/' . $milestonesCount);
            $milestone->setMilestoneType(NutritionGoalMilestone::TYPE_WEIGHT);
            $milestone->setOrder($i);

            if ($totalChange < 0) {
                // Weight loss
                $milestoneValue = $startWeight + ($totalChange * ($i / $milestonesCount));
            } else {
                // Weight gain
                $milestoneValue = $startWeight + ($totalChange * ($i / $milestonesCount));
            }

            $milestone->setTargetValue(round($milestoneValue, 1));
            $milestone->setUnit('kg');

            // Calculate target date proportionally
            $startDate = $goal->getStartDate();
            $targetDate = $goal->getTargetDate();
            if ($startDate && $targetDate) {
                $totalDays = $startDate->diff($targetDate)->days;
                $milestoneDate = clone $startDate;
                $milestoneDate->modify('+' . ($totalDays * $i / $milestonesCount) . ' days');
                $milestone->setTargetDate($milestoneDate);
            }

            $entityManager->persist($milestone);
        }

        $entityManager->flush();
    }
}
