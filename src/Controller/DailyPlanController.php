<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\DailyPlan;
use App\Form\DailyPlanType;
use App\Repository\DailyPlanRepository;
use App\Repository\ExercisesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DailyPlanController extends AbstractController
{
     #[Route('/coach/daily-plans', name: 'coach_daily_plans')]
    public function index(
        DailyPlanRepository $dailyPlanRepository,
        ExercisesRepository $exercisesRepository
    ): Response
    {
        // Récupérer tous les daily plans
        $dailyPlans = $dailyPlanRepository->findBy([], ['date' => 'DESC', 'id' => 'DESC']);
        
        // Récupérer tous les exercices actifs pour la sidebar
        $allExercises = $exercisesRepository->findBy(['isActive' => true], ['name' => 'ASC']);
        
        // Formater les daily plans pour le template Alpine.js
        $formattedPlans = [];
        foreach ($dailyPlans as $plan) {
            // Compter le nombre d'exercices
            $exerciseCount = $plan->getExercices()->count();
            
            // Calculer la durée totale
            $totalDuration = $plan->getDureeMin() ?? 0;
            
            // Formater les exercices pour l'affichage
            $exercisesData = [];
            foreach ($plan->getExercices() as $exercise) {
                $exercisesData[] = [
                    'id' => $exercise->getId(),
                    'name' => $exercise->getName(),
                    'category' => $exercise->getCategory(),
                    'duration' => $exercise->getDuration() ?? 0,
                    'calories' => $exercise->getCalories() ?? 0,
                    'sets' => $exercise->getSets(),
                    'reps' => $exercise->getReps(),
                    'difficulty' => $exercise->getDifficultyLevel(),
                    'equipment' => $exercise->getDefaultUnit() ?? 'Bodyweight',
                ];
            }
            
            
          
            
            // Compter les jours depuis la création
            $planDate = $plan->getDate();
            $today = new \DateTime();
            $daysAgo = $planDate->diff($today)->days;
            
            $formattedPlans[] = [
                'id' => $plan->getId(),
                'title' => $plan->getTitre(),
              
                'date' => $planDate->format('Y-m-d'),
                'formattedDate' => $planDate->format('M d, Y'),
                'daysAgo' => $daysAgo,
                'status' => $plan->getStatus(),
                'duration' => $totalDuration,
                'calories' => $plan->getCalories() ?? 0,
                'exerciseCount' => $exerciseCount,
                'notes' => $plan->getNotes(),
                'exercises' => $exercisesData,
                'goal' => $plan->getGoal() ? [
                    'id' => $plan->getGoal()->getId(),
                    'title' => $plan->getGoal()->getTitle(),
                    'progress' => $plan->getGoal()->getProgress() ?? 0,
                ] : null,
            ];
        }
        
        // Formater les exercices pour la sidebar
        $formattedExercises = [];
        foreach ($allExercises as $exercise) {
            $formattedExercises[] = [
                'id' => $exercise->getId(),
                'name' => $exercise->getName(),
                'category' => $exercise->getCategory(),
                'difficulty' => $exercise->getDifficultyLevel(),
                'duration' => $exercise->getDuration() ?? 0,
                'calories' => $exercise->getCalories() ?? 0,
                'sets' => $exercise->getSets() ?? 0,
                'reps' => $exercise->getReps() ?? 0,
                'description' => $exercise->getDescription() ?? '',
            
                'videoUrl' => $exercise->getVideoUrl(),
            ];
        }
        
        // Données statiques pour les clients (à remplacer par votre système utilisateur)
        $clients = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@email.com', 'avatar' => 'JD'],
            ['id' => 2, 'name' => 'Mary Smith', 'email' => 'mary@email.com', 'avatar' => 'MS'],
            ['id' => 3, 'name' => 'Peter Johnson', 'email' => 'peter@email.com', 'avatar' => 'PJ'],
            ['id' => 4, 'name' => 'Sarah Williams', 'email' => 'sarah@email.com', 'avatar' => 'SW'],
        ];
        
        // Données statiques pour les objectifs
        $goals = [
            ['id' => 1, 'title' => 'Lose 5kg', 'progress' => 60, 'status' => 'ACTIVE'],
            ['id' => 2, 'title' => 'Run 10km', 'progress' => 30, 'status' => 'ACTIVE'],
            ['id' => 3, 'title' => 'Build Muscle', 'progress' => 80, 'status' => 'ACTIVE'],
            ['id' => 4, 'title' => 'Improve Flexibility', 'progress' => 45, 'status' => 'PENDING'],
        ];

        return $this->render('coach/DailyPlan/show_plan.html.twig', [
            'pageTitle' => 'Daily Plans Manager',
            'clients' => $clients,
            'goals' => $goals,
            'dailyPlans' => $formattedPlans,
            'exercises' => $formattedExercises,
        ]);
    }
    
    
    #[Route('/coach/daily-plan/new', name: 'coach_daily_plan_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dailyPlan = new DailyPlan();
        
        $form = $this->createForm(DailyPlanType::class, $dailyPlan);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire HTML (non-mappées)
            $userName = $request->request->get('selected_user');
            $planTitle = $request->request->get('plan_title');
            $planDate = $request->request->get('plan_date');
            
            // Si un utilisateur est sélectionné, l'ajouter aux notes
            if ($userName) {
                $currentNotes = $dailyPlan->getNotes() ?? '';
                $dailyPlan->setNotes("Utilisateur: " . $userName . "\n\n" . $currentNotes);
            }
            
            // Calculer les totaux automatiquement
            $this->calculateTotals($dailyPlan);
            
            $entityManager->persist($dailyPlan);
            $entityManager->flush();
            
            $this->addFlash('success', 'Plan quotidien créé avec succès !');
            return $this->redirectToRoute('coach_daily_plans');
        }
        
        return $this->render('coach/DailyPlan/new_plan.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * Calcule les totaux de durée et calories à partir des exercices
     */
    private function calculateTotals(DailyPlan $dailyPlan): void
    {
        $totalDuration = 0;
        $totalCalories = 0;
        
        foreach ($dailyPlan->getExercices() as $exercise) {
            // Durée de l'exercice en minutes
            $duration = $exercise->getDuration() ?? 0;
            $totalDuration += $duration;
            
            // Calories brûlées par minute
            $caloriesPerMinute = $exercise->getCalories() ?? 0;
            
            // Calcul des calories totales pour cet exercice
            // Si l'exercice a un nombre de sets/reps, on calcule différemment
            if ($exercise->getSets() && $exercise->getReps()) {
                // Pour les exercices avec sets/reps, on estime le temps par set
                $timePerSet = 2; // 2 minutes par set en moyenne
                $totalExerciseTime = $exercise->getSets() * $timePerSet;
                $totalCalories += $totalExerciseTime * $caloriesPerMinute;
            } else {
                // Pour les exercices cardio/endurance, on utilise la durée
                $totalCalories += $duration * $caloriesPerMinute;
            }
        }
        
        $dailyPlan->setDureeMin($totalDuration);
        $dailyPlan->setCalories((int) round($totalCalories));
    }
    #[Route('/delete/{id}', name: 'delete_daily_plan', methods: ['GET'])]
public function delete(ManagerRegistry $m, $id): Response
{
    $em = $m->getManager();
    $dailyPlan = $em->getRepository(DailyPlan::class)->find($id);
    
    if (!$dailyPlan) {
        throw $this->createNotFoundException('Daily plan not found');
    }
    
    $em->remove($dailyPlan);
    $em->flush();
    
    // Redirection vers la liste des plans (corrigé le nom de la route)
    return $this->redirectToRoute('coach_daily_plans');
}
 #[Route('/coach/daily-plans/{id}/edit', name: 'coach_daily_plan_edit', methods: ['GET', 'POST'])]
public function edit(
    Request $request,
    int $id,
    EntityManagerInterface $entityManager,
    DailyPlanRepository $dailyPlanRepository,
    ExercisesRepository $exercisesRepository
): Response
{
    // 1. Récupérer le plan
    $dailyPlan = $dailyPlanRepository->find($id);
    
    if (!$dailyPlan) {
        throw $this->createNotFoundException('Daily plan not found');
    }
    
    // =========== GESTION POST (SAUVEGARDE) ===========
    if ($request->isMethod('POST')) {
        try {
            // Récupérer les données du formulaire
            $data = $request->request->all();
            
            // Mettre à jour les propriétés
            $dailyPlan->setTitre($data['title'] ?? $dailyPlan->getTitre());
            
            if (isset($data['date']) && !empty($data['date'])) {
                $dailyPlan->setDate(new \DateTime($data['date']));
            }
            
            $dailyPlan->setStatus($data['status'] ?? $dailyPlan->getStatus());
            $dailyPlan->setNotes($data['notes'] ?? $dailyPlan->getNotes());
            
            // Mettre à jour la durée et calories si fournies
            if (isset($data['duration'])) {
                $dailyPlan->setDureeMin((int) $data['duration']);
            }
            
            if (isset($data['calories'])) {
                $dailyPlan->setCalories((int) $data['calories']);
            }
            
            // Sauvegarder
            $entityManager->flush();
            
            // Message de succès
            $this->addFlash('success', 'Daily plan updated successfully!');
            
            // Rediriger vers la liste
            return $this->redirectToRoute('coach_daily_plans');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error updating plan: ' . $e->getMessage());
        }
    }
    
    // =========== GESTION GET (AFFICHAGE) ===========
    // 2. Formater les données pour le template (votre code existant)
    $formattedPlan = [
        'id' => $dailyPlan->getId(),
        'title' => $dailyPlan->getTitre(),
        'date' => $dailyPlan->getDate()->format('Y-m-d'),
        'status' => $dailyPlan->getStatus(),
        'duration' => $dailyPlan->getDureeMin(),
        'calories' => $dailyPlan->getCalories(),
        'notes' => $dailyPlan->getNotes(),
        
        'goalId' => $dailyPlan->getGoal() ? $dailyPlan->getGoal()->getId() : null,
        'exercises' => []
    ];
    
    // 3. Ajouter les exercices
    foreach ($dailyPlan->getExercices() as $exercise) {
        $formattedPlan['exercises'][] = [
            'id' => $exercise->getId(),
            'sets' => $exercise->getSets(),
            'reps' => $exercise->getReps(),
        ];
    }
    
    // 4. Récupérer tous les exercices disponibles
    $allExercises = $exercisesRepository->findBy(['isActive' => true], ['name' => 'ASC']);
    $formattedExercises = [];
    
    foreach ($allExercises as $exercise) {
        $formattedExercises[] = [
            'id' => $exercise->getId(),
            'name' => $exercise->getName(),
            'category' => $exercise->getCategory(),
            'duration' => $exercise->getDuration(),
            'calories' => $exercise->getCalories(),
            'sets' => $exercise->getSets(),
            'reps' => $exercise->getReps()
        ];
    }
    
    // 5. Données pour les dropdowns (vous pouvez les garder statiques ou les récupérer)
    $clients = [
        ['id' => 1, 'name' => 'John Doe', 'email' => 'john@email.com'],
        ['id' => 2, 'name' => 'Mary Smith', 'email' => 'mary@email.com'],
        ['id' => 3, 'name' => 'Peter Johnson', 'email' => 'peter@email.com'],
        ['id' => 4, 'name' => 'Sarah Williams', 'email' => 'sarah@email.com'],
    ];
    
    $goals = [
        ['id' => 1, 'title' => 'Lose 5kg'],
        ['id' => 2, 'title' => 'Run 10km'],
        ['id' => 3, 'title' => 'Build Muscle'],
        ['id' => 4, 'title' => 'Improve Flexibility'],
    ];
    
    // 6. Rendre le template
    return $this->render('coach/DailyPlan/edit_plan.html.twig', [
        'pageTitle' => 'Edit Daily Plan',
        'dailyPlan' => $formattedPlan,
        'clients' => $clients,
        'goals' => $goals,
        'exercises' => $formattedExercises,
    ]);
}
#[Route('/coach/rest-day/new', name: 'coach_rest_day_new', methods: ['GET', 'POST'])]
public function newRestDay(Request $request, EntityManagerInterface $entityManager): Response
{
    $dailyPlan = new DailyPlan();
    
    $form = $this->createForm(DailyPlanType::class, $dailyPlan);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        // Récupérer les données du formulaire HTML
        $userName = $request->request->get('selected_user');
        $planTitle = $request->request->get('plan_title') ?? 'Rest Day';
        $planDate = $request->request->get('plan_date');
        $restType = $request->request->get('rest_type');
        
        // Définir le titre et le type
        $dailyPlan->setTitre($planTitle);
        
        // NE PAS ajouter d'informations automatiques aux notes
        // Laisser uniquement les notes du formulaire
        
        // Définir les valeurs spécifiques pour le repos
        $dailyPlan->setStatus('rest_day');
        $dailyPlan->setDureeMin(0); // 0 minutes pour un jour de repos
        $dailyPlan->setCalories(0); // Pas de calories brûlées lors du repos
        
        // Si des exercices ont été sélectionnés par erreur, les vider
        $dailyPlan->getExercices()->clear();
        
        $entityManager->persist($dailyPlan);
        $entityManager->flush();
        
        $this->addFlash('success', 'Rest day scheduled successfully!');
        return $this->redirectToRoute('coach_daily_plans');
    }
    
    // Pour la méthode GET, afficher le formulaire
    return $this->render('coach/DailyPlan/restday.html.twig', [
        'form' => $form->createView(),
    ]);
}
    
}