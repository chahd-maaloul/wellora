<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Goal;
use App\Entity\DailyPlan;
use App\Entity\Exercises;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class AIPythonService
{
    private $httpClient;
    private $entityManager;
    private $pythonApiUrl;
    private $aiCoachId;

    public function __construct(
        HttpClientInterface $httpClient,
        EntityManagerInterface $entityManager,
        string $pythonApiUrl = 'http://localhost:5000',
        string $aiCoachId = 'ai-generated-coach'
    ) {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->pythonApiUrl = $pythonApiUrl;
        $this->aiCoachId = $aiCoachId;
    }

    /**
     * Vérifie que le service Python est accessible
     */
    public function healthCheck(): bool
    {
        try {
            $response = $this->httpClient->request('GET', $this->pythonApiUrl . '/health');
            $data = $response->toArray();
            return $data['status'] === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Génère un programme complet depuis la demande utilisateur
     */
    public function generateAndSaveProgram(string $userRequest, ?User $patient = null): array
    {
        // 1. Appeler l'API Python
        $response = $this->httpClient->request('POST', $this->pythonApiUrl . '/api/generate-program', [
            'json' => ['user_request' => $userRequest]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de l\'appel à l\'IA Python');
        }

        $data = $response->toArray();
        
        if (!$data['success']) {
            throw new \Exception('L\'IA n\'a pas pu générer le programme: ' . ($data['error'] ?? 'Erreur inconnue'));
        }

        $program = $data['program'];

        // 2. Créer et sauvegarder le Goal
        $goal = $this->createGoalFromAI($program['goal'], $patient);
        
        // 3. Créer et sauvegarder les DailyPlans et Exercises
        $this->createDailyPlansFromAI($program['daily_plans'], $goal);

        // 4. Retourner le résultat
        return [
            'goal' => $goal,
            'analysis' => $program['analysis']
        ];
    }

    /**
     * Crée un Goal à partir des données IA
     */
    private function createGoalFromAI(array $goalData, ?User $patient = null): Goal
    {
        $goal = new Goal();
        $goal->setTitle($goalData['title']);
        $goal->setDescription($goalData['description'] ?? '');
        $goal->setCategory($goalData['category']);
        $goal->setStatus('PENDING');
        $goal->setStartDate(new \DateTime($goalData['startDate']));
        
        if (isset($goalData['endDate'])) {
            $goal->setEndDate(new \DateTime($goalData['endDate']));
        }
        
        $goal->setDifficultyLevel($goalData['difficultyLevel']);
        $goal->setSessionsPerWeek($goalData['sessionsPerWeek']);
        $goal->setDurationWeeks($goalData['durationWeeks']);
        $goal->setProgress(0);
        
        if (isset($goalData['targetAudience'])) {
            $goal->setTargetAudience($goalData['targetAudience']);
        }
        
        // Associer le patient si fourni
        if ($patient) {
            $goal->setPatient($patient);
        }
        
        // Définir le coachId comme l'ID spécial de l'IA
        $goal->setCoachId($this->aiCoachId);

        $this->entityManager->persist($goal);
        $this->entityManager->flush();

        return $goal;
    }

    /**
     * Crée les DailyPlans à partir des données IA
     */
    private function createDailyPlansFromAI(array $dailyPlansData, Goal $goal): void
    {
        foreach ($dailyPlansData as $planData) {
            $dailyPlan = new DailyPlan();
            $dailyPlan->setDate(new \DateTime($planData['date']));
            $dailyPlan->setStatus($planData['status']);
            $dailyPlan->setNotes($planData['notes'] ?? '');
            $dailyPlan->setTitre($planData['titre']);
            $dailyPlan->setCalories($planData['calories'] ?? 0);
            $dailyPlan->setDureeMin($planData['duree_min'] ?? 0);
            $dailyPlan->setGoal($goal);
            
            $this->entityManager->persist($dailyPlan);
            
            // Créer ou associer les exercices
            foreach ($planData['exercices'] as $exerciseData) {
                $exercise = $this->findOrCreateExercise($exerciseData);
                $dailyPlan->addExercice($exercise);
            }
        }
        
        $this->entityManager->flush();
    }

    /**
     * Trouve un exercice existant ou en crée un nouveau
     */
    private function findOrCreateExercise(array $exerciseData): Exercises
    {
        // Chercher si l'exercice existe déjà (par nom et niveau)
        $existingExercise = $this->entityManager
            ->getRepository(Exercises::class)
            ->findOneBy([
                'name' => $exerciseData['name'],
                'difficulty_level' => $exerciseData['difficulty_level'] ?? 'Intermediate'
            ]);
        
        if ($existingExercise) {
            return $existingExercise;
        }
        
        // Créer un nouvel exercice
        $exercise = new Exercises();
        $exercise->setName($exerciseData['name']);
        $exercise->setDescription($exerciseData['description'] ?? '');
        $exercise->setCategory($exerciseData['category'] ?? 'Strength');
        $exercise->setDifficultyLevel($exerciseData['difficulty_level'] ?? 'Intermediate');
        $exercise->setDefaultUnit($exerciseData['defaultUnit'] ?? 'reps');
        $exercise->setIsActive(true);
        $exercise->setCreatedAt(new \DateTimeImmutable());
        
        if (isset($exerciseData['duration'])) {
            $exercise->setDuration($exerciseData['duration']);
        }
        
        if (isset($exerciseData['calories'])) {
            $exercise->setCalories($exerciseData['calories']);
        }
        
        if (isset($exerciseData['sets'])) {
            $exercise->setSets($exerciseData['sets']);
        }
        
        if (isset($exerciseData['reps'])) {
            $exercise->setReps($exerciseData['reps']);
        }
        
        $this->entityManager->persist($exercise);
        
        return $exercise;
    }

    /**
     * Analyse seulement la demande
     */
    public function analyzeRequest(string $userRequest): array
    {
        $response = $this->httpClient->request('POST', $this->pythonApiUrl . '/api/analyze-request', [
            'json' => ['user_request' => $userRequest]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de l\'appel à l\'IA Python');
        }

        $data = $response->toArray();
        
        if (!$data['success']) {
            throw new \Exception('L\'IA n\'a pas pu analyser la demande');
        }

        return $data['analysis'];
    }
}