<?php

namespace App\Service;

use App\Entity\Goal;
use App\Entity\ExercisePlan;
use Doctrine\ORM\EntityManagerInterface;

class AiPersonalTrainer
{
    private array $exerciseLibrary = [
        'cardio' => [
            'debutant' => [
                ['name' => 'Marche rapide', 'duration' => '20-30 min', 'intensite' => 'Faible', 'calories' => 150],
                ['name' => 'Vélo tranquille', 'duration' => '30 min', 'intensite' => 'Faible', 'calories' => 120],
                ['name' => 'Natation', 'duration' => '20 min', 'intensite' => 'Faible', 'calories' => 180],
            ],
            'intermediaire' => [
                ['name' => 'Course à pied', 'duration' => '25-30 min', 'intensite' => 'Moyenne', 'calories' => 300],
                ['name' => 'Vélo spinning', 'duration' => '30 min', 'intensite' => 'Moyenne', 'calories' => 280],
                ['name' => 'Corde à sauter', 'duration' => '15 min', 'intensite' => 'Moyenne', 'calories' => 250],
            ],
            'avance' => [
                ['name' => 'HIIT course', 'duration' => '20 min', 'intensite' => 'Élevée', 'calories' => 350],
                ['name' => 'Rameur intensif', 'duration' => '25 min', 'intensite' => 'Élevée', 'calories' => 320],
                ['name' => 'Boxe', 'duration' => '30 min', 'intensite' => 'Élevée', 'calories' => 400],
            ]
        ],
        'force' => [
            'debutant' => [
                ['name' => 'Squats poids du corps', 'reps' => '3x12', 'repos' => '60 sec'],
                ['name' => 'Pompes sur genoux', 'reps' => '3x8', 'repos' => '60 sec'],
                ['name' => 'Fentes', 'reps' => '3x10', 'repos' => '60 sec'],
                ['name' => 'Gainage', 'duree' => '3x30 sec', 'repos' => '45 sec'],
            ],
            'intermediaire' => [
                ['name' => 'Squats avec charge', 'reps' => '4x10', 'repos' => '45 sec'],
                ['name' => 'Pompes', 'reps' => '4x12', 'repos' => '45 sec'],
                ['name' => 'Fentes sautées', 'reps' => '3x15', 'repos' => '45 sec'],
                ['name' => 'Tractions assistées', 'reps' => '3x8', 'repos' => '60 sec'],
            ],
            'avance' => [
                ['name' => 'Squats barre', 'reps' => '5x8', 'repos' => '30 sec'],
                ['name' => 'Développé couché', 'reps' => '5x8', 'repos' => '30 sec'],
                ['name' => 'Soulevé de terre', 'reps' => '4x6', 'repos' => '45 sec'],
                ['name' => 'Tractions lestées', 'reps' => '4x8', 'repos' => '45 sec'],
            ]
        ]
    ];

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Analyse le goal et génère un plan 100% personnalisé
     */
    public function generatePersonalizedPlan(Goal $goal): array
    {
        // ÉTAPE 1 : Analyser le goal en profondeur
        $analysis = $this->analyzeGoal($goal);
        
        // ÉTAPE 2 : Déterminer le profil du patient
        $profile = $this->determinePatientProfile($goal, $analysis);
        
        // ÉTAPE 3 : Calculer la durée du programme
        $duration = $this->calculateProgramDuration($goal);
        
        // ÉTAPE 4 : Générer le plan semaine par semaine
        $weeklyPlans = [];
        for ($week = 1; $week <= $duration['weeks']; $week++) {
            $weeklyPlans[] = $this->generateWeekPlan($goal, $profile, $analysis, $week, $duration);
        }
        
        // ÉTAPE 5 : Sauvegarder les plans
        $this->savePlans($goal, $weeklyPlans);
        
        return [
            'analysis' => $analysis,
            'profile' => $profile,
            'duration' => $duration,
            'plans' => $weeklyPlans
        ];
    }

    /**
     * Analyse approfondie du goal
     */
    private function analyzeGoal(Goal $goal): array
    {
        $analysis = [
            'type' => $this->detectGoalType($goal),
            'intensity' => $this->detectIntensity($goal),
            'urgence' => 'normale',
            'contraintes' => [],
            'objectif_principal' => '',
            'sous_objectifs' => []
        ];

        // Analyser le titre et la description
        $title = strtolower($goal->getTitle() ?? '');
        $desc = strtolower($goal->getDescription() ?? '');
        $category = strtolower($goal->getCategory() ?? '');
        
        // Détecter le type d'objectif
        if (str_contains($title, 'perte') || str_contains($title, 'poids') || 
            str_contains($category, 'weight loss')) {
            $analysis['type'] = 'weight_loss';
            $analysis['objectif_principal'] = 'Perte de poids';
            
            // Calculer l'urgence basée sur le poids
            if ($goal->getWeightStart() && $goal->getWeightTarget()) {
                $weightDiff = $goal->getWeightStart() - $goal->getWeightTarget();
                if ($weightDiff > 15) {
                    $analysis['urgence'] = 'haute';
                    $analysis['sous_objectifs'][] = 'Perte rapide (phase 1)';
                } elseif ($weightDiff > 8) {
                    $analysis['urgence'] = 'moyenne';
                }
            }
        }
        elseif (str_contains($title, 'muscle') || str_contains($category, 'muscle gain')) {
            $analysis['type'] = 'muscle_gain';
            $analysis['objectif_principal'] = 'Prise de masse';
        }
        elseif (str_contains($title, 'endurance') || str_contains($category, 'endurance')) {
            $analysis['type'] = 'endurance';
            $analysis['objectif_principal'] = 'Amélioration endurance';
        }

        // Analyser les contraintes de temps
        if ($goal->getSessionsPerWeek()) {
            if ($goal->getSessionsPerWeek() <= 2) {
                $analysis['contraintes'][] = 'temps_limité';
            } elseif ($goal->getSessionsPerWeek() >= 5) {
                $analysis['contraintes'][] = 'intensif';
            }
        }

        // Analyser la durée des séances
        if ($goal->getSessionDuration()) {
            if ($goal->getSessionDuration() < 30) {
                $analysis['contraintes'][] = 'séances_courtes';
            } elseif ($goal->getSessionDuration() > 75) {
                $analysis['contraintes'][] = 'séances_longues';
            }
        }

        return $analysis;
    }

    /**
     * Détermine le profil complet du patient
     */
    private function determinePatientProfile(Goal $goal, array $analysis): array
    {
        $profile = [
            'niveau' => $goal->getDifficultyLevel() ?? 'intermediaire',
            'motivation' => $this->calculateMotivation($goal),
            'disponibilite' => $goal->getSessionsPerWeek() ?? 3,
            'duree_seance' => $goal->getSessionDuration() ?? 45,
            'preferences' => [],
            'points_forts' => [],
            'points_faibles' => []
        ];

        // Adapter le niveau selon la progression
        if ($goal->getProgress() > 70) {
            $profile['points_forts'][] = 'bonne_progression';
        } elseif ($goal->getProgress() < 20 && $goal->getStartDate()->diff(new \DateTime())->days > 30) {
            $profile['points_faibles'][] = 'progression_lente';
        }

        // Préférences basées sur les jours préférés
        if (!empty($goal->getPreferredDays())) {
            $profile['preferences']['jours'] = $goal->getPreferredDays();
        }

        // Heure préférée
        if ($goal->getPreferredTime()) {
            $profile['preferences']['heure'] = $goal->getPreferredTime()->format('H:i');
        }

        return $profile;
    }

    /**
     * Calcule la motivation basée sur les données
     */
    private function calculateMotivation(Goal $goal): string
    {
        $score = 5; // Base
        
        // Plus de séances = plus motivé
        if ($goal->getSessionsPerWeek() >= 4) $score += 2;
        elseif ($goal->getSessionsPerWeek() <= 2) $score -= 1;
        
        // Progression influence
        if ($goal->getProgress() > 50) $score += 2;
        elseif ($goal->getProgress() < 20) $score -= 2;
        
        // Jours depuis le début
        $days = $goal->getStartDate()->diff(new \DateTime())->days;
        if ($days > 60 && $goal->getProgress() > 60) $score += 3;
        elseif ($days > 30 && $goal->getProgress() < 30) $score -= 2;
        
        if ($score >= 8) return 'excellente';
        if ($score >= 6) return 'bonne';
        if ($score >= 4) return 'moyenne';
        return 'fragile';
    }

    /**
     * Calcule la durée du programme
     */
    private function calculateProgramDuration(Goal $goal): array
    {
        // Si durée spécifiée
        if ($goal->getDurationWeeks()) {
            return [
                'weeks' => $goal->getDurationWeeks(),
                'type' => 'fixe'
            ];
        }
        
        // Si date de fin spécifiée
        if ($goal->getEndDate()) {
            $days = $goal->getEndDate()->diff($goal->getStartDate())->days;
            return [
                'weeks' => ceil($days / 7),
                'type' => 'date_limite'
            ];
        }
        
        // Sinon, calcul intelligent selon l'objectif
        $weeks = 12; // Base
        
        $title = strtolower($goal->getTitle() ?? '');
        if (str_contains($title, 'perte') || str_contains($title, 'poids')) {
            if ($goal->getWeightStart() && $goal->getWeightTarget()) {
                $diff = $goal->getWeightStart() - $goal->getWeightTarget();
                $weeks = ceil($diff / 0.5); // 0.5 kg par semaine est réaliste
            }
        }
        
        return [
            'weeks' => min(16, max(4, $weeks)), // Entre 4 et 16 semaines
            'type' => 'intelligent'
        ];
    }

    /**
     * Génère le plan pour une semaine spécifique
     */
    private function generateWeekPlan(Goal $goal, array $profile, array $analysis, int $week, array $duration): array
    {
        $plan = [
            'semaine' => $week,
            'titre' => $this->getWeekTitle($week, $analysis, $duration),
            'focus' => $this->getWeekFocus($week, $analysis),
            'objectifs' => [],
            'seances' => [],
            'conseils' => [],
            'adaptations' => []
        ];

        // Adapter l'intensité selon la semaine
        $phase = $this->determinePhase($week, $duration['weeks']);
        
        // Générer les séances de la semaine
        for ($day = 1; $day <= $profile['disponibilite']; $day++) {
            $seance = $this->generateSession(
                $goal, 
                $profile, 
                $analysis, 
                $phase, 
                $week, 
                $day
            );
            $plan['seances'][] = $seance;
        }

        // Objectifs spécifiques de la semaine
        $plan['objectifs'] = $this->generateWeeklyGoals($goal, $analysis, $phase, $week);
        
        // Conseils personnalisés
        $plan['conseils'] = $this->generateWeeklyAdvice($goal, $analysis, $phase, $week);
        
        // Adaptations si besoin
        if ($goal->getProgress() > 0) {
            $plan['adaptations'] = $this->checkAndAdapt($goal, $analysis, $phase, $week);
        }

        return $plan;
    }

    /**
     * Génère une séance d'entraînement
     */
    private function generateSession(Goal $goal, array $profile, array $analysis, string $phase, int $week, int $day): array
    {
        $session = [
            'jour' => $day,
            'duree' => $profile['duree_seance'],
            'type' => $this->determineSessionType($analysis, $phase, $day),
            'exercices' => [],
            'intensite' => $this->calculateIntensity($profile, $phase, $week)
        ];

        // Sélectionner les exercices selon le type d'objectif
        switch ($analysis['type']) {
            case 'weight_loss':
                $session['exercices'] = $this->buildWeightLossSession(
                    $profile, 
                    $session['type'], 
                    $session['intensite']
                );
                break;
                
            case 'muscle_gain':
                $session['exercices'] = $this->buildMuscleGainSession(
                    $profile, 
                    $session['type'], 
                    $session['intensite']
                );
                break;
                
            default:
                $session['exercices'] = $this->buildGeneralSession(
                    $profile, 
                    $session['intensite']
                );
        }

        return $session;
    }

    /**
     * Construit une séance pour perte de poids
     */
    private function buildWeightLossSession(array $profile, string $type, string $intensite): array
    {
        $exercices = [];
        
        // Échauffement (toujours présent)
        $exercices[] = [
            'nom' => 'Échauffement',
            'duree' => '10 min',
            'instructions' => 'Mobilité articulaire + cardio léger'
        ];

        // Partie principale selon le type
        if ($type === 'cardio') {
            $niveau = $this->mapNiveau($profile['niveau']);
            $cardioEx = $this->exerciseLibrary['cardio'][$niveau][array_rand($this->exerciseLibrary['cardio'][$niveau])];
            $exercices[] = $cardioEx;
            
            // Ajouter un second exercice cardio si la séance est longue
            if ($profile['duree_seance'] > 45) {
                $cardioEx2 = $this->exerciseLibrary['cardio'][$niveau][array_rand($this->exerciseLibrary['cardio'][$niveau])];
                $exercices[] = $cardioEx2;
            }
        } 
        elseif ($type === 'renforcement') {
            $niveau = $this->mapNiveau($profile['niveau']);
            // Prendre 3-4 exercices de force
            $forceEx = $this->exerciseLibrary['force'][$niveau];
            shuffle($forceEx);
            for ($i = 0; $i < min(4, count($forceEx)); $i++) {
                $exercices[] = $forceEx[$i];
            }
        }
        else { // Mixte
            $niveau = $this->mapNiveau($profile['niveau']);
            $forceEx = $this->exerciseLibrary['force'][$niveau][array_rand($this->exerciseLibrary['force'][$niveau])];
            $cardioEx = $this->exerciseLibrary['cardio'][$niveau][array_rand($this->exerciseLibrary['cardio'][$niveau])];
            $exercices[] = $forceEx;
            $exercices[] = $cardioEx;
        }

        // Retour au calme
        $exercices[] = [
            'nom' => 'Retour au calme',
            'duree' => '5-10 min',
            'instructions' => 'Étirements légers, respiration'
        ];

        return $exercices;
    }

    /**
     * Construit une séance pour prise de masse
     */
    private function buildMuscleGainSession(array $profile, string $type, string $intensite): array
    {
        $exercices = [];
        
        // Échauffement spécifique
        $exercices[] = [
            'nom' => 'Échauffement articulaire',
            'duree' => '10 min',
            'instructions' => 'Mobilité épaules, hanches, genoux'
        ];

        $niveau = $this->mapNiveau($profile['niveau']);
        $forceEx = $this->exerciseLibrary['force'][$niveau];
        
        // Sélectionner 4-5 exercices de force
        shuffle($forceEx);
        for ($i = 0; $i < min(5, count($forceEx)); $i++) {
            $ex = $forceEx[$i];
            // Adapter les reps pour la prise de masse
            if (isset($ex['reps'])) {
                $ex['reps'] = str_replace(['12', '10'], ['8-10', '6-8'], $ex['reps']);
            }
            $exercices[] = $ex;
        }

        return $exercices;
    }

    /**
     * Détermine le type de séance
     */
    private function determineSessionType(array $analysis, string $phase, int $day): string
    {
        if ($analysis['type'] === 'weight_loss') {
            // Alterner cardio et renforcement
            return ($day % 2 === 0) ? 'cardio' : 'renforcement';
        } elseif ($analysis['type'] === 'muscle_gain') {
            // Split selon la phase
            $types = ['push', 'pull', 'legs', 'fullbody'];
            return $types[($day - 1) % count($types)];
        }
        
        return ($day % 3 === 0) ? 'cardio' : 'mixte';
    }

    /**
     * Calcule l'intensité de la séance
     */
    private function calculateIntensity(array $profile, string $phase, int $week): string
    {
        $base = match($profile['niveau']) {
            'debutant' => 5,
            'intermediaire' => 6,
            'avance' => 7,
            default => 5
        };
        
        // Ajuster selon la phase
        if ($phase === 'debut') $base -= 1;
        if ($phase === 'pic') $base += 2;
        if ($phase === 'fin') $base += 1;
        
        if ($base <= 4) return 'légère';
        if ($base <= 6) return 'modérée';
        if ($base <= 8) return 'soutenue';
        return 'intensive';
    }

    /**
     * Génère les objectifs de la semaine
     */
    private function generateWeeklyGoals(Goal $goal, array $analysis, string $phase, int $week): array
    {
        $goals = [];
        
        switch ($analysis['type']) {
            case 'weight_loss':
                $goals[] = '🏋️ Objectif poids : ' . ($goal->getWeightTarget() ?? 'selon planning');
                $goals[] = '📊 Perte visée : 0.5-1kg cette semaine';
                $goals[] = '💧 Hydratation : 2L d\'eau par jour minimum';
                break;
                
            case 'muscle_gain':
                $goals[] = '💪 Progression charge : augmenter de 2.5kg si possible';
                $goals[] = '🍗 Protéines : atteindre l\'objectif quotidien';
                $goals[] = '😴 Sommeil : minimum 7h par nuit';
                break;
        }
        
        if ($phase === 'debut') {
            $goals[] = '🎯 Objectif de la semaine : maîtriser la technique';
        } elseif ($phase === 'pic') {
            $goals[] = '⚡ Objectif de la semaine : se dépasser sur chaque séance';
        }
        
        return $goals;
    }

    /**
     * Génère les conseils de la semaine
     */
    private function generateWeeklyAdvice(Goal $goal, array $analysis, string $phase, int $week): array
    {
        $advice = [];
        
        if ($analysis['urgence'] === 'haute') {
            $advice[] = "⚠️ Objectif urgent : contacter le patient en début de semaine";
        }
        
        if ($profile['motivation'] ?? '' === 'fragile') {
            $advice[] = "💬 Message de motivation à envoyer en milieu de semaine";
        }
        
        if ($week % 4 === 0) {
            $advice[] = "📊 Faire un point mensuel avec le patient";
        }
        
        return $advice;
    }

    /**
     * Vérifie et adapte selon la progression
     */
    private function checkAndAdapt(Goal $goal, array $analysis, string $phase, int $week): array
    {
        $adaptations = [];
        
        if ($goal->getProgress() < ($week / ($this->duration['weeks'] ?? 12)) * 100 - 15) {
            $adaptations[] = "📉 Progression inférieure aux attentes : réduire l'intensité";
        } elseif ($goal->getProgress() > ($week / ($this->duration['weeks'] ?? 12)) * 100 + 15) {
            $adaptations[] = "📈 Excellente progression : augmenter la difficulté la semaine prochaine";
        }
        
        return $adaptations;
    }

    /**
     * Détermine la phase de la semaine
     */
    private function determinePhase(int $week, int $totalWeeks): string
    {
        if ($week <= 2) return 'debut';
        if ($week >= $totalWeeks - 2) return 'fin';
        if ($week == round($totalWeeks / 2)) return 'pic';
        return 'milieu';
    }

    /**
     * Titre de la semaine
     */
    private function getWeekTitle(int $week, array $analysis, array $duration): string
    {
        $phase = $this->determinePhase($week, $duration['weeks']);
        
        return match($phase) {
            'debut' => "🔰 Semaine $week : Prise en main",
            'pic' => "⚡ Semaine $week : Objectif intensif",
            'fin' => "🏁 Semaine $week : Dernière ligne droite",
            default => "📅 Semaine $week : Progression régulière"
        };
    }

    /**
     * Focus de la semaine
     */
    private function getWeekFocus(int $week, array $analysis): string
    {
        if ($analysis['type'] === 'weight_loss') {
            $foci = [
                1 => 'Cardio de base',
                2 => 'Renforcement musculaire',
                3 => 'Introduction HIIT',
                4 => 'Endurance',
                5 => 'Explosivité',
                6 => 'Récupération active'
            ];
            return $foci[($week - 1) % 6 + 1];
        }
        
        return "Développement global";
    }

    /**
     * Sauvegarde les plans
     */
    private function savePlans(Goal $goal, array $weeklyPlans): void
    {
        // Supprimer les anciens plans
        foreach ($goal->getExercisePlans() as $oldPlan) {
            $goal->removeExercisePlan($oldPlan);
            $this->entityManager->remove($oldPlan);
        }
        
        // Créer les nouveaux plans
        foreach ($weeklyPlans as $weekPlan) {
            $plan = new ExercisePlan();
            $plan->setGoal($goal);
            $plan->setWeekNumber($weekPlan['semaine']);
            $plan->setFocus($weekPlan['focus']);
            $plan->setExercises($weekPlan);
            $plan->setCoachNotes(implode("\n", $weekPlan['conseils'] ?? []));
            
            $this->entityManager->persist($plan);
        }
        
        $this->entityManager->flush();
    }

    private function detectGoalType(Goal $goal): string
    {
        $text = strtolower($goal->getTitle() . ' ' . ($goal->getDescription() ?? ''));
        if (str_contains($text, 'poids') || str_contains($text, 'weight') || str_contains($text, 'perte')) {
            return 'weight_loss';
        }
        if (str_contains($text, 'muscle') || str_contains($text, 'force')) {
            return 'muscle_gain';
        }
        return 'general';
    }

    private function detectIntensity(Goal $goal): string
    {
        if ($goal->getSessionsPerWeek() > 4) return 'haute';
        if ($goal->getSessionsPerWeek() > 2) return 'moyenne';
        return 'faible';
    }

    private function mapNiveau(?string $niveau): string
    {
        return match(strtolower($niveau ?? '')) {
            'beginner' => 'debutant',
            'intermediate' => 'intermediaire',
            'advanced' => 'avance',
            default => 'intermediaire'
        };
    }
    /**
 * Génère une séance générale pour les objectifs non spécifiques
 */
private function buildGeneralSession(array $profile, string $intensite): array
{
    $exercices = [];
    $niveau = $profile['niveau'] ?? 'intermediaire';
    
    // Échauffement
    $exercices[] = [
        'nom' => 'Échauffement complet',
        'duree' => '10 min',
        'instructions' => 'Mobilité articulaire (cou, épaules, hanches, genoux) + cardio léger'
    ];
    
    // Exercices de force (2-3 exercices)
    if (isset($this->exerciseLibrary['force'][$niveau])) {
        $forceEx = $this->exerciseLibrary['force'][$niveau];
        shuffle($forceEx);
        for ($i = 0; $i < min(3, count($forceEx)); $i++) {
            $exercices[] = $forceEx[$i];
        }
    } else {
        // Fallback si le niveau n'existe pas
        $exercices[] = [
            'nom' => 'Squats',
            'reps' => '3x12',
            'repos' => '60 sec'
        ];
        $exercices[] = [
            'nom' => 'Pompes',
            'reps' => '3x10',
            'repos' => '60 sec'
        ];
    }
    
    // Cardio (1 exercice)
    if (isset($this->exerciseLibrary['cardio'][$niveau])) {
        $cardioEx = $this->exerciseLibrary['cardio'][$niveau][array_rand($this->exerciseLibrary['cardio'][$niveau])];
        $exercices[] = $cardioEx;
    } else {
        $exercices[] = [
            'nom' => 'Marche rapide ou vélo',
            'duree' => '20 min',
            'instructions' => 'Maintenir un rythme soutenu mais confortable'
        ];
    }
    
    // Retour au calme
    $exercices[] = [
        'nom' => 'Retour au calme',
        'duree' => '5-10 min',
        'instructions' => 'Étirements statiques, respiration profonde'
    ];
    
    return $exercices;
}
}