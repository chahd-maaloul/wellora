<?php
// src/Service/AiCoachForConnectedCoachService.php

namespace App\Service;

use App\Entity\Goal;
use Doctrine\ORM\EntityManagerInterface;

class AiCoachForConnectedCoachService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Récupère tous les objectifs pour un coach (par son ID)
     */
    public function getAllGoalsForConnectedCoach(string $coachId): array
    {
        return $this->entityManager
            ->getRepository(Goal::class)
            ->findBy(['coachId' => $coachId]);
    }

    /**
     * Analyse un objectif spécifique
     */
    public function analyzeGoal(Goal $goal, string $coachId): array
    {
        // Vérification de sécurité
        if ($goal->getCoachId() !== $coachId) {
            return ['error' => 'Vous n\'êtes pas autorisé à analyser cet objectif'];
        }
        
        $today = new \DateTime();
        $daysSinceStart = $goal->getStartDate()->diff($today)->days ?: 1;
        $expectedProgress = min(100, ($daysSinceStart / 30) * 33);
        $progressGap = $goal->getProgress() - $expectedProgress;
        
        $metrics = [
            'daysSinceStart' => $daysSinceStart,
            'expectedProgress' => round($expectedProgress, 2),
            'progressGap' => round($progressGap, 2),
            'currentDifficulty' => $goal->getDifficultyLevel(),
        ];
        
        // Générer le conseil
        $advice = $this->generateAdvice($goal, $progressGap, $daysSinceStart);
        
        // Sauvegarder
        $goal->setAiCoachAdvice($advice);
        $goal->setAiMetrics($metrics);
        $goal->setLastAiAnalysis($today);
        $this->entityManager->flush();
        
        return [
            'goal' => $goal,
            'advice' => $advice,
            'metrics' => $metrics,
            'riskLevel' => $this->getRiskLevel($progressGap)
        ];
    }

    /**
     * Analyse tous les objectifs d'un coach
     */
    public function analyzeAllGoalsForConnectedCoach(string $coachId): array
    {
        $goals = $this->getAllGoalsForConnectedCoach($coachId);
        $results = [];
        
        foreach ($goals as $goal) {
            $results[] = $this->analyzeGoal($goal, $coachId);
        }
        
        return $results;
    }

    /**
     * Statistiques globales pour un coach
     */
    public function getGlobalStatsForConnectedCoach(string $coachId): array
    {
        $goals = $this->getAllGoalsForConnectedCoach($coachId);
        
        $totalGoals = count($goals);
        $completedGoals = 0;
        $inProgressGoals = 0;
        $highRiskGoals = 0;
        $totalProgress = 0;
        
        foreach ($goals as $goal) {
            if ($goal->getStatus() === 'completed') {
                $completedGoals++;
            } elseif ($goal->getStatus() === 'in progress') {
                $inProgressGoals++;
            }
            
            $totalProgress += $goal->getProgress();
            
            $metrics = $goal->getAiMetrics();
            if (isset($metrics['progressGap']) && $metrics['progressGap'] < -30) {
                $highRiskGoals++;
            }
        }
        
        return [
            'totalGoals' => $totalGoals,
            'completedGoals' => $completedGoals,
            'inProgressGoals' => $inProgressGoals,
            'highRiskGoals' => $highRiskGoals,
            'averageProgress' => $totalGoals > 0 ? round($totalProgress / $totalGoals, 2) : 0,
            'completionRate' => $totalGoals > 0 ? round(($completedGoals / $totalGoals) * 100, 2) : 0
        ];
    }

    private function generateAdvice(Goal $goal, float $progressGap, int $daysSinceStart): string
    {
        if ($goal->getProgress() >= 100) {
            $advice = "🎉 **Objectif ATTEINT !** Félicitations au patient !";
        } 
        elseif ($progressGap > 20) {
            $advice = "⚡ **Progression EXCELLENTE !** Le patient est en avance de " . round($progressGap) . "%. Augmentez la difficulté !";
        }
        elseif ($progressGap < -20) {
            $advice = "🐢 **Progression LENTE.** Le patient est en retard de " . abs(round($progressGap)) . "%. Réduisez l'intensité.";
        }
        elseif ($daysSinceStart > 7 && $goal->getProgress() < 5) {
            $advice = "⚠️ **DÉMARRAGE DIFFICILE.** Contactez le patient.";
        }
        else {
            $advice = "✅ **PROGRESSION NORMALE.** Continuez ainsi !";
        }
        
        if ($goal->getPatientSatisfaction() && $goal->getPatientSatisfaction() < 3) {
            $advice .= "\n\n🔴 **ALERTE SATISFACTION :** Le patient a noté " . $goal->getPatientSatisfaction() . "/5. Contacter URGENT.";
        }
        
        if ($goal->getCoachNotes()) {
            $advice .= "\n\n📝 **Note personnelle :** " . $goal->getCoachNotes();
        }
        
        return $advice;
    }

    private function getRiskLevel(float $progressGap): string
    {
        if ($progressGap < -30) return 'high';
        if ($progressGap < -15) return 'medium';
        return 'low';
    }
}