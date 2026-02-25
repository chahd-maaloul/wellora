<?php
namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\DailyPlan;
use App\Form\DailyPlanType;
use App\Repository\DailyPlanRepository;
use App\Repository\ExercisesRepository;
use App\Repository\GoalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DailyPlanController extends AbstractController
{
    #[Route('/coach/daily-plans', name: 'coach_daily_plans')]
    public function index(
        DailyPlanRepository $dailyPlanRepository,
        ExercisesRepository $exercisesRepository,
        GoalRepository $goalRepository
    ): Response
    {
        // Récupérer le coach connecté et le typer
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException('Invalid user type');
        }
        $coach = $user;
        
        // Récupérer uniquement les daily plans du coach connecté
        $dailyPlans = $dailyPlanRepository->findBy(['coach' => $coach], ['date' => 'DESC', 'id' => 'DESC']);
        
        // Récupérer tous les exercices actifs pour la sidebar (associés au coach)
        $allExercises = $exercisesRepository->findBy([
            'User' => $coach,  // Notez le U majuscule !
            'isActive' => true
        ], ['name' => 'ASC']);
    
        // Récupérer les goals assignés à ce coach
        $coachId = $coach->getUuid();
        $coachGoals = $goalRepository->findBy(['coachId' => $coachId]);
        
        // Formater les daily plans pour le template
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
                    'patientName' => $plan->getGoal()->getPatient() ? 
                        $plan->getGoal()->getPatient()->getFullName() : 'Unknown',
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
        
        // Formater les goals du coach pour la sélection
        $formattedGoals = [];
        foreach ($coachGoals as $goal) {
            $patient = $goal->getPatient();
            $formattedGoals[] = [
                'id' => $goal->getId(),
                'title' => $goal->getTitle(),
                'progress' => $goal->getProgress() ?? 0,
                'status' => $goal->getStatus(),
                'patientId' => $patient ? $patient->getUuid() : null,
                'patientName' => $patient ? $patient->getFullName() : 'No patient assigned',
                'patientEmail' => $patient ? $patient->getEmail() : '',
            ];
        }

        return $this->render('coach/DailyPlan/show_plan.html.twig', [
            'pageTitle' => 'Daily Plans Manager',
            'dailyPlans' => $formattedPlans,
            'exercises' => $formattedExercises,
            'coachGoals' => $formattedGoals,
        ]);
    }
    
    #[Route('/coach/daily-plan/new', name: 'coach_daily_plan_new')]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager,
        GoalRepository $goalRepository,
        ExercisesRepository $exercisesRepository
    ): Response
    {
        // Récupérer le coach connecté et le typer
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException('Invalid user type');
        }
        $coach = $user;
        
        $coachId = $coach->getUuid();
        
        $dailyPlan = new DailyPlan();
        $dailyPlan->setCoach($coach);
        $dailyPlan->setDate(new \DateTime()); // Date par défaut
        
        $form = $this->createForm(DailyPlanType::class, $dailyPlan);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire (les champs mappés sont déjà dans $dailyPlan)
            
            // Récupérer l'ID du goal depuis le champ caché
            $goalId = $request->request->get('selected_goal');
            
            // Si un goal est sélectionné, l'associer au plan (SANS AJOUTER LE NOM AUX NOTES)
            if ($goalId) {
                $goal = $goalRepository->find($goalId);
                if ($goal) {
                    // Vérifier que le goal appartient bien à ce coach
                    if ($goal->getCoachId() === $coachId) {
                        $dailyPlan->setGoal($goal);
                        // NE PAS AJOUTER LE NOM DU PATIENT AUX NOTES
                        // Les notes restent telles que saisies par l'utilisateur
                    }
                }
            }
            
            // Calculer les totaux automatiquement
            $this->calculateTotals($dailyPlan);
            
            $entityManager->persist($dailyPlan);
            $entityManager->flush();
            
            $this->addFlash('success', 'Daily plan created successfully!');
            return $this->redirectToRoute('coach_daily_plans');
        }
        
        // Récupérer les goals assignés à ce coach pour le template
        $coachGoals = $goalRepository->findBy(['coachId' => $coachId]);
        $formattedGoals = [];
        foreach ($coachGoals as $goal) {
            $patient = $goal->getPatient();
            $formattedGoals[] = [
                'id' => $goal->getId(),
                'title' => $goal->getTitle(),
                'patientName' => $patient ? $patient->getFullName() : 'No patient',
                'progress' => $goal->getProgress() ?? 0,
            ];
        }
        
        // Récupérer les exercices associés au coach pour le formulaire
        // Pour Select2, nous avons besoin de formater les données correctement
        $exercises = $exercisesRepository->findBy(['User' => $coach, 'isActive' => true], ['name' => 'ASC']);
        
        return $this->render('coach/DailyPlan/new_plan.html.twig', [
            'form' => $form->createView(),
            'coachGoals' => $formattedGoals,
            'exercises' => $exercises, // Passer les exercices au template
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
    
    #[Route('/delete/{id}', name: 'delete_daily_plan', methods: ['POST'])]
    public function delete(ManagerRegistry $doctrine, $id): Response
    {
        $em = $doctrine->getManager();
        $dailyPlan = $em->getRepository(DailyPlan::class)->find($id);
        
        if (!$dailyPlan) {
            throw $this->createNotFoundException('Daily plan not found');
        }
        
        // Vérifier que le plan appartient bien au coach connecté
        $user = $this->getUser();
        if ($dailyPlan->getCoach() !== $user) {
            throw $this->createAccessDeniedException('You can only delete your own plans');
        }
        
        $em->remove($dailyPlan);
        $em->flush();
        
        $this->addFlash('success', 'Daily plan deleted successfully!');
        return $this->redirectToRoute('coach_daily_plans');
    }
    
   #[Route('/coach/daily-plans/{id}/edit', name: 'coach_daily_plan_edit', methods: ['GET', 'POST'])]
public function edit(
    Request $request,
    int $id,
    EntityManagerInterface $entityManager,
    DailyPlanRepository $dailyPlanRepository,
    ExercisesRepository $exercisesRepository,
    GoalRepository $goalRepository
): Response
{
    // Récupérer le coach connecté
    $user = $this->getUser();
    if (!$user instanceof \App\Entity\User) {
        throw $this->createAccessDeniedException('Invalid user type');
    }
    $coach = $user;
    
    $coachId = $coach->getUuid();
    
    // Récupérer le plan
    $dailyPlan = $dailyPlanRepository->find($id);
    
    if (!$dailyPlan) {
        throw $this->createNotFoundException('Daily plan not found');
    }
    
    // Vérifier que le plan appartient bien au coach
    if ($dailyPlan->getCoach() !== $coach) {
        throw $this->createAccessDeniedException('You can only edit your own plans');
    }
    
    // Récupérer les goals assignés à ce coach
    $coachGoals = $goalRepository->findBy(['coachId' => $coachId]);
    $formattedGoals = [];
    foreach ($coachGoals as $goal) {
        $patient = $goal->getPatient();
        $formattedGoals[] = [
            'id' => $goal->getId(),
            'title' => $goal->getTitle(),
            'patientName' => $patient ? $patient->getFullName() : 'No patient',
            'progress' => $goal->getProgress() ?? 0,
        ];
    }
    
    // Formater les données du plan pour Alpine.js
    $exercisesData = [];
    foreach ($dailyPlan->getExercices() as $exercise) {
        $exercisesData[] = [
            'id' => $exercise->getId(),
            'sets' => $exercise->getSets() ?? '',
            'reps' => $exercise->getReps() ?? '',
        ];
    }
    
    $planData = [
        'id' => $dailyPlan->getId(),
        'title' => $dailyPlan->getTitre(),
        'clientId' => $dailyPlan->getGoal() ? $dailyPlan->getGoal()->getPatient() ? $dailyPlan->getGoal()->getPatient()->getUuid() : '' : '',
        'date' => $dailyPlan->getDate()->format('Y-m-d'),
        'status' => $dailyPlan->getStatus() ?? 'planned',
        'goalId' => $dailyPlan->getGoal() ? $dailyPlan->getGoal()->getId() : '',
        'notes' => $dailyPlan->getNotes() ?? '',
        'exercises' => $exercisesData,
        'duration' => $dailyPlan->getDureeMin() ?? 0,
        'calories' => $dailyPlan->getCalories() ?? 0,
    ];
    
    // Récupérer tous les exercices disponibles associés au coach
    $allExercises = $exercisesRepository->findBy(['User' => $coach, 'isActive' => true], ['name' => 'ASC']);
    $formattedExercises = [];
    foreach ($allExercises as $exercise) {
        $formattedExercises[] = [
            'id' => $exercise->getId(),
            'name' => $exercise->getName(),
            'category' => $exercise->getCategory(),
            'duration' => $exercise->getDuration() ?? 0,
            'calories' => $exercise->getCalories() ?? 0,
            'sets' => $exercise->getSets() ?? 0,
            'reps' => $exercise->getReps() ?? 0,
        ];
    }
    
    // Récupérer les clients (patients) pour la sélection
    $clients = [];
    foreach ($coachGoals as $goal) {
        $patient = $goal->getPatient();
        if ($patient && !isset($clients[$patient->getUuid()])) {
            $clients[$patient->getUuid()] = [
                'id' => $patient->getUuid(),
                'name' => $patient->getFullName(),
                'email' => $patient->getEmail(),
            ];
        }
    }
    $clients = array_values($clients); // Réindexer
    
    // Créer le formulaire Symfony
    $form = $this->createForm(DailyPlanType::class, $dailyPlan);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        // Récupérer l'ID du goal depuis le champ caché
        $goalId = $request->request->get('selected_goal');
        
        // Mettre à jour le goal si sélectionné
        if ($goalId) {
            $goal = $goalRepository->find($goalId);
            if ($goal && $goal->getCoachId() === $coachId) {
                $dailyPlan->setGoal($goal);
            }
        } else {
            $dailyPlan->setGoal(null);
        }
        
        // Recalculer les totaux
        $this->calculateTotals($dailyPlan);
        
        $entityManager->flush();
        
        $this->addFlash('success', 'Daily plan updated successfully!');
        return $this->redirectToRoute('coach_daily_plans');
    }
    
    return $this->render('coach/DailyPlan/edit_plan.html.twig', [
        'pageTitle' => 'Edit Daily Plan',
        'form' => $form->createView(),
        'dailyPlan' => $dailyPlan,
        'coachGoals' => $formattedGoals,
        'exercises' => $formattedExercises,
        'clients' => $clients,
        'planData' => $planData, // Données formatées pour Alpine.js
    ]);
}
    #[Route('/coach/rest-day/new', name: 'coach_rest_day_new', methods: ['GET', 'POST'])]
    public function newRestDay(
        Request $request, 
        EntityManagerInterface $entityManager,
        GoalRepository $goalRepository,
        ExercisesRepository $exercisesRepository
    ): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException('Invalid user type');
        }
        $coach = $user;
        $coachId = $coach->getUuid();
        
        $dailyPlan = new DailyPlan();
        $dailyPlan->setCoach($coach);
        $dailyPlan->setDate(new \DateTime());
        $dailyPlan->setStatus('rest');
        $dailyPlan->setDureeMin(0);
        $dailyPlan->setCalories(0);
        $dailyPlan->setTitre('Rest Day');
        
        $form = $this->createForm(DailyPlanType::class, $dailyPlan);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer l'ID du goal depuis le champ caché
            $goalId = $request->request->get('selected_goal');
            
            if ($goalId) {
                $goal = $goalRepository->find($goalId);
                if ($goal && $goal->getCoachId() === $coachId) {
                    $dailyPlan->setGoal($goal);
                    // NE PAS AJOUTER LE NOM DU PATIENT AUX NOTES
                }
            }
           
            // S'assurer qu'il n'y a pas d'exercices
            $dailyPlan->getExercices()->clear();
            
            $entityManager->persist($dailyPlan);
            $entityManager->flush();
            
            $this->addFlash('success', 'Rest day scheduled successfully!');
            return $this->redirectToRoute('coach_daily_plans');
        }
        
        // Récupérer les goals du coach
        $coachGoals = $goalRepository->findBy(['coachId' => $coachId]);
        $formattedGoals = [];
        foreach ($coachGoals as $goal) {
            $patient = $goal->getPatient();
            $formattedGoals[] = [
                'id' => $goal->getId(),
                'title' => $goal->getTitle(),
                'patientName' => $patient ? $patient->getFullName() : 'No patient',
                'progress' => $goal->getProgress() ?? 0,
            ];
        }
        
        // Récupérer les exercices associés au coach
        $exercises = $exercisesRepository->findBy(['User' => $coach, 'isActive' => true], ['name' => 'ASC']);
        
        return $this->render('coach/DailyPlan/restday.html.twig', [
            'form' => $form->createView(),
            'coachGoals' => $formattedGoals,
            'exercises' => $exercises,
        ]);
    }
    #[Route('/coach/client/{clientId}/plans', name: 'coach_client_plans_api', methods: ['GET'])]
public function getClientPlans(string $clientId, DailyPlanRepository $dailyPlanRepository): JsonResponse
{
    $coach = $this->getUser();
    
    if (!$coach instanceof \App\Entity\User) {
        return $this->json(['error' => 'Unauthorized'], 401);
    }
    
    // Récupérer UNIQUEMENT les plans des goals de ce client ET de ce coach
    $plans = $dailyPlanRepository->createQueryBuilder('dp')
        ->leftJoin('dp.goal', 'g')
        ->where('g.patient = :clientId')
        ->andWhere('dp.coach = :coach')
        ->andWhere('g.coachId = :coachId')
        ->setParameter('clientId', $clientId)
        ->setParameter('coach', $coach)
        ->setParameter('coachId', $coach->getUuid())
        ->orderBy('dp.date', 'DESC')
        ->getQuery()
        ->getResult();
    
    $formattedPlans = [];
    foreach ($plans as $plan) {
        $exercises = [];
        foreach ($plan->getExercices() as $exercise) {
            $exercises[] = [
                'id' => $exercise->getId(),
                'name' => $exercise->getName(),
                'duration' => $exercise->getDuration() ?? 0,
                'calories' => $exercise->getCalories() ?? 0,
            ];
        }
        
        $formattedPlans[] = [
            'id' => $plan->getId(),
            'title' => $plan->getTitre(),
            'date' => $plan->getDate()->format('Y-m-d'),
            'duration' => $plan->getDureeMin() ?? 0,
            'calories' => $plan->getCalories() ?? 0,
            'status' => $plan->getStatus() ?? 'draft',
            'notes' => $plan->getNotes() ?? '',
            'exercises' => $exercises,
            'goal' => $plan->getGoal() ? [
                'id' => $plan->getGoal()->getId(),
                'title' => $plan->getGoal()->getTitle(),
            ] : null,
        ];
    }
    
    return $this->json(['plans' => $formattedPlans]);
}
}