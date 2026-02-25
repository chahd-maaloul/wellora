<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\User;
use App\Service\AiCoachForConnectedCoachService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\AiPersonalTrainer;

#[Route('/coach/ai')]
#[IsGranted('ROLE_COACH')]
class AiCoachDashboardController extends AbstractController
{
    public function __construct(
        private AiCoachForConnectedCoachService $aiCoachService,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/dashboard', name: 'ai_coach_dashboard')]
    public function dashboard(): Response
    {
        /** @var User $coach */
        $coach = $this->getUser();
        
        // Récupère l'identifiant du coach (uuid ou id selon ce qui existe)
        $coachId = $this->getCoachIdentifier($coach);
        
        $stats = $this->aiCoachService->getGlobalStatsForConnectedCoach($coachId);
        $goals = $this->aiCoachService->getAllGoalsForConnectedCoach($coachId);
        
        return $this->render('ai_coach/dashboard.html.twig', [
            'stats' => $stats,
            'goals' => $goals,
            'coach' => $coach
        ]);
    }

    #[Route('/analyze-all', name: 'ai_coach_analyze_all')]
    public function analyzeAll(): Response
    {
        /** @var User $coach */
        $coach = $this->getUser();
        $coachId = $this->getCoachIdentifier($coach);
        
        $results = $this->aiCoachService->analyzeAllGoalsForConnectedCoach($coachId);
        
        $this->addFlash('success', 'Analyse IA terminée pour ' . count($results) . ' objectifs');
        
        return $this->redirectToRoute('ai_coach_dashboard');
    }

    #[Route('/goal/{id}/analyze', name: 'ai_coach_analyze_goal')]
    public function analyzeGoal(Goal $goal): Response
    {
        /** @var User $coach */
        $coach = $this->getUser();
        $coachId = $this->getCoachIdentifier($coach);
        
        // Vérifie que ce goal appartient bien au coach connecté
        if ($goal->getCoachId() !== $coachId) {
            throw $this->createAccessDeniedException('Cet objectif ne vous appartient pas');
        }
        
        $result = $this->aiCoachService->analyzeGoal($goal, $coachId);
        
        return $this->render('ai_coach/goal_detail.html.twig', [
            'result' => $result,
            'goal' => $goal
        ]);
    }

    #[Route('/goal/{id}/update-notes', name: 'ai_coach_update_notes', methods: ['POST'])]
    public function updateNotes(Goal $goal, Request $request): Response
    {
        /** @var User $coach */
        $coach = $this->getUser();
        $coachId = $this->getCoachIdentifier($coach);
        
        if ($goal->getCoachId() !== $coachId) {
            throw $this->createAccessDeniedException('Cet objectif ne vous appartient pas');
        }
        
        $notes = $request->request->get('coachNotes');
        $satisfaction = $request->request->get('patientSatisfaction');
        
        if ($notes !== null) {
            $goal->setCoachNotes($notes);
        }
        
        if ($satisfaction !== null) {
            $goal->setPatientSatisfaction((int)$satisfaction);
        }
        
        $this->entityManager->flush();
        $this->aiCoachService->analyzeGoal($goal, $coachId);
        
        $this->addFlash('success', 'Notes mises à jour et IA réanalysée');
        
        return $this->redirectToRoute('ai_coach_analyze_goal', ['id' => $goal->getId()]);
    }

    /**
     * Récupère l'identifiant du coach (uuid ou id selon ce qui existe)
     */
    private function getCoachIdentifier(User $coach): string
    {
        // Essaie d'abord getUuid() si ça existe
        if (method_exists($coach, 'getUuid')) {
            return $coach->getUuid();
        }
        
        // Sinon utilise getId() et le convertit en string
        return (string) $coach->getId();
    }
    #[Route('/goal/{id}/generate-personalized-plan', name: 'ai_coach_generate_personalized_plan')]
public function generatePersonalizedPlan(Goal $goal, AiPersonalTrainer $personalTrainer): Response
{
    $coach = $this->getUser();
    $coachId = $this->getCoachIdentifier($coach);
    
    if ($goal->getCoachId() !== $coachId) {
        throw $this->createAccessDeniedException('Cet objectif ne vous appartient pas');
    }
    
    // Génère le plan personnalisé
    $plan = $personalTrainer->generatePersonalizedPlan($goal);
    
    $this->addFlash('success', '🎯 Plan personnalisé généré pour ' . $plan['duration']['weeks'] . ' semaines !');
    
    return $this->redirectToRoute('ai_coach_view_personalized_plan', ['id' => $goal->getId()]);
}

// Ajoute cette méthode
#[Route('/goal/{id}/view-personalized-plan', name: 'ai_coach_view_personalized_plan')]
public function viewPersonalizedPlan(Goal $goal): Response
{
    $coach = $this->getUser();
    $coachId = $this->getCoachIdentifier($coach);
    
    if ($goal->getCoachId() !== $coachId) {
        throw $this->createAccessDeniedException('Cet objectif ne vous appartient pas');
    }
    
    $exercisePlans = $goal->getExercisePlans();
    
    // Trier par numéro de semaine
    $sortedPlans = $exercisePlans->toArray();
    usort($sortedPlans, fn($a, $b) => $a->getWeekNumber() <=> $b->getWeekNumber());
    
    return $this->render('ai_coach/personalized_plan.html.twig', [
        'goal' => $goal,
        'exercisePlans' => $sortedPlans
    ]);
}
}