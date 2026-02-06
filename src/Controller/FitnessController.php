<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FitnessController extends AbstractController
{
    /**
     * Main Fitness Dashboard
     */
    #[Route('/fitness/dashboard', name: 'fitness_dashboard')]
    public function dashboard(): Response
    {
        $todayDate = new \DateTime();
        
        return $this->render('fitness/patient-dashboard.html.twig', [
            'page_title' => 'Fitness Dashboard',
            'streak' => ['days' => 12],
            'weeklyStats' => [
                'minutes' => 245,
                'target' => 300,
                'workoutsCompleted' => 4,
                'targetWorkouts' => 5,
            ],
            'dailyStats' => [
                'caloriesBurned' => 485,
                'caloriesChange' => 12,
            ],
            'todayWorkout' => [
                'completed' => false,
                'duration' => 45,
                'calories' => 320,
                'exercises' => [
                    ['name' => 'Warm-up', 'sets' => 1, 'reps' => '-', 'weight' => null, 'duration' => 5, 'rpe' => 3, 'completed' => true],
                    ['name' => 'Bench Press', 'sets' => 4, 'reps' => 10, 'weight' => 60, 'duration' => 12, 'rpe' => 7, 'completed' => false],
                    ['name' => 'Shoulder Press', 'sets' => 3, 'reps' => 12, 'weight' => 35, 'duration' => 10, 'rpe' => 6, 'completed' => false],
                    ['name' => 'Lateral Raises', 'sets' => 3, 'reps' => 15, 'weight' => 12, 'duration' => 8, 'rpe' => 5, 'completed' => false],
                    ['name' => 'Tricep Dips', 'sets' => 3, 'reps' => 12, 'weight' => null, 'duration' => 8, 'rpe' => 6, 'completed' => false],
                    ['name' => 'Cool-down', 'sets' => 1, 'reps' => '-', 'weight' => null, 'duration' => 5, 'rpe' => 2, 'completed' => false],
                ],
            ],
            'goals' => [
                [
                    'title' => 'Run Half Marathon',
                    'category' => 'Endurance',
                    'progress' => 65,
                    'targetDate' => 'Apr 15, 2024',
                    'daysRemaining' => 45,
                    'icon' => '🏃',
                    'status' => 'active',
                ],
                [
                    'title' => 'Lose 5kg',
                    'category' => 'Weight Loss',
                    'progress' => 40,
                    'targetDate' => 'Mar 30, 2024',
                    'daysRemaining' => 30,
                    'icon' => '⚖️',
                    'status' => 'active',
                ],
                [
                    'title' => 'Bench Press 80kg',
                    'category' => 'Strength',
                    'progress' => 75,
                    'targetDate' => 'May 1, 2024',
                    'daysRemaining' => 60,
                    'icon' => '💪',
                    'status' => 'active',
                ],
            ],
            'recentActivities' => [
                ['type' => 'workout', 'title' => 'Upper Body Strength', 'date' => 'Yesterday', 'duration' => '48 min', 'calories' => 320, 'icon' => '💪', 'iconBg' => 'bg-blue-100 dark:bg-blue-900/30', 'iconColor' => 'text-blue-500'],
                ['type' => 'workout', 'title' => 'Cardio Session', 'date' => '2 days ago', 'duration' => '35 min', 'calories' => 380, 'icon' => '🏃', 'iconBg' => 'bg-green-100 dark:bg-green-900/30', 'iconColor' => 'text-green-500'],
                ['type' => 'milestone', 'title' => 'Completed: Run 10km', 'date' => '3 days ago', 'duration' => null, 'calories' => null, 'icon' => '🎯', 'iconBg' => 'bg-purple-100 dark:bg-purple-900/30', 'iconColor' => 'text-purple-500'],
                ['type' => 'workout', 'title' => 'HIIT Workout', 'date' => '4 days ago', 'duration' => '28 min', 'calories' => 290, 'icon' => '🔥', 'iconBg' => 'bg-orange-100 dark:bg-orange-900/30', 'iconColor' => 'text-orange-500'],
            ],
            'upcomingWorkouts' => [
                ['day' => 'Tomorrow', 'title' => 'Lower Body Strength', 'time' => '08:00 AM', 'duration' => '50 min', 'type' => 'strength', 'icon' => '🦵', 'color' => 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400'],
                ['day' => 'Friday', 'title' => 'Cardio Endurance', 'time' => '07:00 AM', 'duration' => '40 min', 'type' => 'cardio', 'icon' => '🏃', 'color' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400'],
                ['day' => 'Saturday', 'title' => 'Flexibility & Recovery', 'time' => '10:00 AM', 'duration' => '30 min', 'type' => 'flexibility', 'icon' => '🧘', 'color' => 'bg-teal-100 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400'],
            ],
            'weeklyChart' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'data' => [45, 60, 30, 55, 45, 90, 30],
            ],
            'coachNotes' => [
                'text' => 'Great progress on your bench press! Keep maintaining good form. Remember to prioritize sleep for optimal recovery.',
                'date' => 'Today',
                'coach' => 'Coach Sarah',
                'avatar' => '👩‍🏫',
            ],
            'weekDays' => [
                ['name' => 'Mon', 'hasWorkout' => true, 'completed' => true, 'workouts' => 1],
                ['name' => 'Tue', 'hasWorkout' => true, 'completed' => true, 'workouts' => 1],
                ['name' => 'Wed', 'hasWorkout' => true, 'completed' => true, 'workouts' => 1],
                ['name' => 'Thu', 'hasWorkout' => true, 'completed' => true, 'workouts' => 1],
                ['name' => 'Fri', 'hasWorkout' => true, 'completed' => false, 'workouts' => 0],
                ['name' => 'Sat', 'hasWorkout' => false, 'completed' => false, 'workouts' => 0],
                ['name' => 'Sun', 'hasWorkout' => false, 'completed' => false, 'workouts' => 0],
            ],
            'recovery' => [
                'ready' => true,
                'score' => 75,
                'sleepQuality' => 7,
                'muscleReadiness' => 8,
                'trainingLoad' => 65,
            ],
        ]);
    }

    /**
     * Workout Planner
     */
    #[Route('/fitness/planner', name: 'fitness_planner')]
    public function planner(): Response
    {
        return $this->render('fitness/workout-planner.html.twig', [
            'page_title' => 'Workout Planner',
            'currentWeek' => 'Feb 5 - Feb 11, 2024',
            'weeklyGoal' => 5,
            'completedWorkouts' => 4,
            'schedule' => [
                'Monday' => ['type' => 'Upper Body', 'time' => '08:00', 'duration' => 45, 'completed' => true],
                'Tuesday' => ['type' => 'Cardio', 'time' => '07:00', 'duration' => 35, 'completed' => true],
                'Wednesday' => ['type' => 'Lower Body', 'time' => '08:00', 'duration' => 50, 'completed' => true],
                'Thursday' => ['type' => 'HIIT', 'time' => '07:00', 'duration' => 25, 'completed' => true],
                'Friday' => ['type' => 'Upper Body', 'time' => '08:00', 'duration' => 45, 'completed' => false],
                'Saturday' => ['type' => 'Active Recovery', 'time' => '10:00', 'duration' => 30, 'completed' => false],
                'Sunday' => ['type' => 'Rest', 'time' => null, 'duration' => 0, 'completed' => false],
            ],
        ]);
    }

    /**
     * Exercise Library
     */
    #[Route('/fitness/library', name: 'fitness_library')]
    public function library(): Response
    {
        return $this->render('fitness/exercise-library.html.twig', [
            'page_title' => 'Exercise Library',
            'categories' => ['All', 'Strength', 'Cardio', 'Flexibility', 'HIIT'],
            'exercises' => [
                ['id' => 1, 'name' => 'Push-ups', 'category' => 'Strength', 'difficulty' => 'Beginner', 'muscleGroup' => 'Chest, Triceps', 'equipment' => 'None', 'video' => true],
                ['id' => 2, 'name' => 'Bench Press', 'category' => 'Strength', 'difficulty' => 'Intermediate', 'muscleGroup' => 'Chest, Triceps', 'equipment' => 'Barbell, Bench', 'video' => true],
                ['id' => 3, 'name' => 'Squats', 'category' => 'Strength', 'difficulty' => 'Beginner', 'muscleGroup' => 'Legs, Glutes', 'equipment' => 'None', 'video' => true],
                ['id' => 4, 'name' => 'Deadlifts', 'category' => 'Strength', 'difficulty' => 'Advanced', 'muscleGroup' => 'Back, Legs', 'equipment' => 'Barbell', 'video' => true],
                ['id' => 5, 'name' => 'Running', 'category' => 'Cardio', 'difficulty' => 'Beginner', 'muscleGroup' => 'Full Body', 'equipment' => 'None', 'video' => false],
                ['id' => 6, 'name' => 'Jumping Jacks', 'category' => 'Cardio', 'difficulty' => 'Beginner', 'muscleGroup' => 'Full Body', 'equipment' => 'None', 'video' => false],
                ['id' => 7, 'name' => 'Yoga Flow', 'category' => 'Flexibility', 'difficulty' => 'Beginner', 'muscleGroup' => 'Full Body', 'equipment' => 'Mat', 'video' => true],
                ['id' => 8, 'name' => 'Burpees', 'category' => 'HIIT', 'difficulty' => 'Advanced', 'muscleGroup' => 'Full Body', 'equipment' => 'None', 'video' => false],
            ],
        ]);
    }

    /**
     * Workout Log
     */
    #[Route('/fitness/log', name: 'fitness_log')]
    public function log(): Response
    {
        return $this->render('fitness/workout-log.html.twig', [
            'page_title' => 'Workout Log',
            'recentWorkouts' => [
                ['date' => 'Feb 5, 2024', 'type' => 'Upper Body', 'duration' => 48, 'calories' => 320, 'exercises' => 6],
                ['date' => 'Feb 3, 2024', 'type' => 'Cardio', 'duration' => 35, 'calories' => 380, 'exercises' => 5],
                ['date' => 'Feb 1, 2024', 'type' => 'Lower Body', 'duration' => 52, 'calories' => 350, 'exercises' => 7],
                ['date' => 'Jan 30, 2024', 'type' => 'HIIT', 'duration' => 28, 'calories' => 290, 'exercises' => 8],
            ],
        ]);
    }

    /**
     * Performance Analytics
     */
    #[Route('/fitness/analytics', name: 'fitness_analytics')]
    public function analytics(): Response
    {
        return $this->render('fitness/performance-analytics.html.twig', [
            'page_title' => 'Performance Analytics',
            'strengthProgress' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'benchPress' => [60, 65, 70, 72, 75, 80],
                'squat' => [80, 85, 90, 95, 100, 105],
                'deadlift' => [100, 110, 115, 120, 125, 130],
            ],
            'enduranceMetrics' => [
                'vo2Max' => ['value' => 42, 'change' => '+8%'],
                'pace' => ['value' => '5:30/km', 'change' => '-12%'],
                'distance' => ['value' => '8.5km', 'change' => '+25%'],
            ],
            'recoveryData' => [
                'averageRestingHR' => 62,
                'hrv' => 45,
                'sleepQuality' => 85,
                'recoveryScore' => 78,
            ],
        ]);
    }

    /**
     * Coach Communication
     */
    #[Route('/fitness/coach', name: 'fitness_coach')]
    public function coach(): Response
    {
        return $this->render('fitness/coach-communication.html.twig', [
            'page_title' => 'Coach Communication',
            'coach' => [
                'name' => 'Coach Sarah',
                'specialty' => 'Strength & Conditioning',
                'avatar' => '👩‍🏫',
            ],
            'messages' => [
                ['sender' => 'coach', 'text' => 'Great progress on your bench press! Keep maintaining good form.', 'time' => '2 hours ago'],
                ['sender' => 'user', 'text' => 'Thanks! I\'ll focus on form more.', 'time' => '1 hour ago'],
                ['sender' => 'coach', 'text' => 'Remember to prioritize sleep for optimal recovery.', 'time' => '30 mins ago'],
            ],
            'recentFeedback' => [
                'type' => 'form_correction',
                'title' => 'Bench Press Form',
                'feedback' => 'Keep your elbows at 45 degrees to protect your shoulders.',
                'date' => 'Today',
            ],
            'conversations' => [
                ['id' => 1, 'name' => 'Coach Sarah', 'avatar' => '👩‍🏫', 'lastMessage' => 'Great progress!', 'time' => '2h ago', 'unread' => true, 'online' => true],
                ['id' => 2, 'name' => 'Coach Mike', 'avatar' => '👨‍🏫', 'lastMessage' => 'Keep up the work!', 'time' => '1d ago', 'unread' => false, 'online' => false],
                ['id' => 3, 'name' => 'Coach Emma', 'avatar' => '👩‍⚕️', 'lastMessage' => 'Your form is improving', 'time' => '3d ago', 'unread' => false, 'online' => false],
            ],
        ]);
    }

    /**
     * SMART Goals Dashboard
     */
    #[Route('/fitness/goals', name: 'fitness_goals')]
    public function goals(): Response
    {
        return $this->render('fitness/goal-wizard.html.twig', [
            'page_title' => 'SMART Goals',
            'goals' => [
                ['id' => 1, 'title' => 'Run a Half Marathon', 'status' => 'active', 'progress' => 65, 'category' => 'Endurance'],
                ['id' => 2, 'title' => 'Lose 10kg', 'status' => 'active', 'progress' => 40, 'category' => 'Weight Loss'],
                ['id' => 3, 'title' => 'Do 20 Pull-ups', 'status' => 'paused', 'progress' => 75, 'category' => 'Strength'],
            ],
        ]);
    }

    /**
     * SMART Goal Wizard - Create New Goal
     */
    #[Route('/fitness/goals/wizard', name: 'fitness_goal_wizard')]
    public function goalWizard(): Response
    {
        return $this->render('fitness/goal-wizard.html.twig', [
            'page_title' => 'Create SMART Goal',
        ]);
    }

    /**
     * SMART Goal Editor
     */
    #[Route('/fitness/goals/editor', name: 'fitness_goal_editor')]
    public function goalEditor(): Response
    {
        return $this->render('fitness/smart-goal-editor.html.twig', [
            'page_title' => 'Edit SMART Goals',
            'goals' => [
                ['id' => 1, 'title' => 'Run a Half Marathon', 'status' => 'active', 'statusLabel' => 'Active', 'progress' => 65],
                ['id' => 2, 'title' => 'Lose 10kg', 'status' => 'active', 'statusLabel' => 'Active', 'progress' => 40],
                ['id' => 3, 'title' => 'Do 20 Pull-ups', 'status' => 'paused', 'statusLabel' => 'Paused', 'progress' => 75],
            ],
            'goalTimeline' => [
                ['action' => 'Goal created', 'date' => 'Jan 15, 2024'],
                ['action' => 'Milestone completed: Run 10km', 'date' => 'Feb 15, 2024'],
                ['action' => 'Progress update: 50% complete', 'date' => 'Mar 1, 2024'],
            ],
            'milestones' => [
                ['id' => 1, 'title' => 'Run 10km continuously', 'targetDate' => 'Feb 15', 'value' => '10km', 'completed' => true],
                ['id' => 2, 'title' => 'Complete 15km run', 'targetDate' => 'Mar 1', 'value' => '15km', 'completed' => true],
                ['id' => 3, 'title' => 'Run 18km at race pace', 'targetDate' => 'Mar 15', 'value' => '18km', 'completed' => true],
                ['id' => 4, 'title' => 'Complete half marathon', 'targetDate' => 'Apr 1', 'value' => '21.1km', 'completed' => false],
            ],
        ]);
    }

    /**
     * Milestone Tracker
     */
    #[Route('/fitness/milestones', name: 'fitness_milestones')]
    public function milestones(): Response
    {
        return $this->render('fitness/milestone-tracker.html.twig', [
            'page_title' => 'Milestones & Rewards',
            'totalPoints' => 2450,
            'completedMilestones' => 18,
            'inProgressMilestones' => 5,
            'currentStreak' => 12,
            'badgesEarned' => 12,
            'currentWeek' => 7,
            'monthName' => 'February',
            'year' => 2024,
            'monthlyPoints' => 320,
            'achievementLevels' => [
                ['name' => 'Bronze', 'icon' => '🥉', 'requirement' => '0-500 points', 'progress' => 100, 'unlocked' => true],
                ['name' => 'Silver', 'icon' => '🥈', 'requirement' => '500-1500 points', 'progress' => 100, 'unlocked' => true],
                ['name' => 'Gold', 'icon' => '🥇', 'requirement' => '1500-3000 points', 'progress' => 82, 'unlocked' => true],
                ['name' => 'Platinum', 'icon' => '💎', 'requirement' => '3000+ points', 'progress' => 27, 'unlocked' => false],
            ],
            'weeklyAchievements' => [
                ['icon' => '🏃', 'title' => 'Complete 3 Workouts', 'description' => 'Finish at least 3 workouts this week', 'current' => 3, 'target' => 3, 'progress' => 100, 'points' => 50, 'completed' => true],
                ['icon' => '🔥', 'title' => 'Burn 2000 Calories', 'description' => 'Burn 2000 calories through exercise', 'current' => 1850, 'target' => 2000, 'progress' => 92, 'points' => 75, 'completed' => false],
                ['icon' => '⏱️', 'title' => '300 Minutes Activity', 'description' => 'Reach 300 minutes of activity', 'current' => 280, 'target' => 300, 'progress' => 93, 'points' => 60, 'completed' => false],
            ],
            'badges' => [
                ['name' => 'First Workout', 'icon' => '🎯', 'earned' => true],
                ['name' => 'Week Warrior', 'icon' => '🔥', 'earned' => true],
                ['name' => 'Early Bird', 'icon' => '🌅', 'earned' => true],
                ['name' => 'Night Owl', 'icon' => '🦉', 'earned' => false],
                ['name' => 'Marathon Ready', 'icon' => '🏃', 'earned' => true],
                ['name' => 'Strength Star', 'icon' => '💪', 'earned' => true],
            ],
            'nextBadge' => [
                'requirement' => 'Complete 30 workouts',
                'current' => 22,
                'target' => 30,
                'progress' => 73,
            ],
            'activityHeatmap' => [
                ['day' => 'M', 'date' => 'Feb 5', 'value' => '150', 'level' => 4],
                ['day' => 'T', 'date' => 'Feb 6', 'value' => '100', 'level' => 3],
                ['day' => 'W', 'date' => 'Feb 7', 'value' => '75', 'level' => 2],
                ['day' => 'T', 'date' => 'Feb 8', 'value' => '120', 'level' => 4],
                ['day' => 'F', 'date' => 'Feb 9', 'value' => '0', 'level' => 0],
                ['day' => 'S', 'date' => 'Feb 10', 'value' => '200', 'level' => 4],
                ['day' => 'S', 'date' => 'Feb 11', 'value' => '50', 'level' => 1],
            ],
            'monthlyChallenges' => [
                ['icon' => '🏃', 'title' => 'Monthly Distance', 'description' => 'Run 100km this month', 'current' => 78, 'target' => 100, 'progress' => 78, 'completed' => false],
                ['icon' => '💪', 'title' => 'Strength Goal', 'description' => 'Complete 20 strength workouts', 'current' => 15, 'target' => 20, 'progress' => 75, 'completed' => false],
                ['icon' => '🧘', 'title' => 'Flexibility Challenge', 'description' => 'Do 30 stretching sessions', 'current' => 30, 'target' => 30, 'progress' => 100, 'completed' => true],
            ],
            'leaderboard' => [
                ['rank' => 1, 'avatar' => '👤', 'name' => 'Sarah M.', 'achievement' => 'Elite Athlete', 'points' => 3250],
                ['rank' => 2, 'avatar' => '👤', 'name' => 'John D.', 'achievement' => 'Pro Runner', 'points' => 2980],
                ['rank' => 3, 'avatar' => '👤', 'name' => 'Emma W.', 'achievement' => 'Fitness Enthusiast', 'points' => 2750],
                ['rank' => 4, 'avatar' => '👤', 'name' => 'You', 'achievement' => 'Rising Star', 'points' => 2450],
                ['rank' => 5, 'avatar' => '👤', 'name' => 'Mike R.', 'achievement' => 'Active Member', 'points' => 2100],
            ],
        ]);
    }

    /**
     * Progression System
     */
    #[Route('/fitness/progression', name: 'fitness_progression')]
    public function progression(): Response
    {
        return $this->render('fitness/progression-system.html.twig', [
            'page_title' => 'Adaptive Progression',
            'fitnessLevel' => 'Intermediate',
            'trainingLoad' => 'Moderate',
            'recoveryScore' => 85,
            'aiRecommendation' => 'Your performance data shows consistent improvement in cardiovascular metrics. Based on your recovery score and training load, I recommend maintaining current intensity for the next 3-4 days before gradually increasing workout volume.',
            'progressMetrics' => [
                ['name' => 'VO2 Max', 'icon' => 'M13 7h8m0 0V3m-4 5h4m4-2v5m-2 4h4m-6 4a2 2 0 01-2-2V5a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2h-2', 'bgColor' => 'bg-green-100 dark:bg-green-900/30', 'iconColor' => 'text-green-500', 'change' => 'vs. last month', 'current' => '42', 'improvement' => '+8%'],
                ['name' => 'Bench Press', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16', 'bgColor' => 'bg-blue-100 dark:bg-blue-900/30', 'iconColor' => 'text-blue-500', 'change' => 'vs. last month', 'current' => '85kg', 'improvement' => '+5%'],
                ['name' => 'Running Pace', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'bgColor' => 'bg-purple-100 dark:bg-purple-900/30', 'iconColor' => 'text-purple-500', 'change' => 'vs. last month', 'current' => '5:30/km', 'improvement' => '+12%'],
                ['name' => 'Flexibility', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'bgColor' => 'bg-amber-100 dark:bg-amber-900/30', 'iconColor' => 'text-amber-500', 'change' => 'vs. last month', 'current' => 'Good', 'improvement' => '+3%'],
            ],
            'recommendations' => [
                ['id' => 1, 'title' => 'Increase Running Distance', 'description' => 'Gradually increase weekly running distance by 10% to improve endurance', 'impact' => 'High Impact', 'timeframe' => 'This week', 'borderColor' => 'border-green-500', 'badgeColor' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400'],
                ['id' => 2, 'title' => 'Add HIIT Sessions', 'description' => 'Incorporate 1-2 high-intensity interval training sessions per week', 'impact' => 'Medium Impact', 'timeframe' => 'Next week', 'borderColor' => 'border-blue-500', 'badgeColor' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'],
                ['id' => 3, 'title' => 'Focus on Recovery', 'description' => 'Schedule a deload week after current training block', 'impact' => 'Recovery', 'timeframe' => 'In 2 weeks', 'borderColor' => 'border-amber-500', 'badgeColor' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400'],
            ],
            'weekSchedule' => [
                ['name' => 'Mon', 'activity' => 'Upper Body', 'isDeload' => false],
                ['name' => 'Tue', 'activity' => 'Cardio', 'isDeload' => false],
                ['name' => 'Wed', 'activity' => 'Lower Body', 'isDeload' => false],
                ['name' => 'Thu', 'activity' => 'HIIT', 'isDeload' => false],
                ['name' => 'Fri', 'activity' => 'Upper Body', 'isDeload' => false],
                ['name' => 'Sat', 'activity' => 'Active Rest', 'isDeload' => true],
                ['name' => 'Sun', 'activity' => 'Rest Day', 'isDeload' => true],
            ],
        ]);
    }

    /**
     * Adaptive Workouts
     */
    #[Route('/fitness/adaptive', name: 'fitness_adaptive')]
    public function adaptive(): Response
    {
        return $this->render('fitness/adaptive-workouts.html.twig', [
            'page_title' => 'Adaptive Workouts',
            'readinessScore' => 85,
            'lastWorkout' => 'Yesterday',
            'lastWorkoutType' => 'Upper Body Strength',
            'recoveryStatus' => 72,
            'suggestedFocus' => 'Lower Body',
            'aiRecommendation' => 'Based on your recovery data and upcoming schedule, I recommend a moderate-intensity strength workout focusing on lower body. Your upper body muscles are still recovering from yesterday\'s session. Feel free to add 1-2 extra sets if you\'re feeling energetic!',
            'workoutTypes' => [
                ['id' => 'strength', 'name' => 'Strength', 'icon' => '💪', 'duration' => '45-60 min'],
                ['id' => 'cardio', 'name' => 'Cardio', 'icon' => '🏃', 'duration' => '30-45 min'],
                ['id' => 'hiit', 'name' => 'HIIT', 'icon' => '🔥', 'duration' => '20-30 min'],
                ['id' => 'flexibility', 'name' => 'Flexibility', 'icon' => '🧘', 'duration' => '20-30 min'],
            ],
            'warmup' => [
                ['icon' => '🏃', 'name' => 'Light Jog', 'duration' => '3 min'],
                ['icon' => '🤸', 'name' => 'Dynamic Stretching', 'duration' => '5 min'],
                ['icon' => '⭕', 'name' => 'Arm Circles', 'duration' => '2 min'],
            ],
            'mainExercises' => [
                ['icon' => '🦵', 'name' => 'Barbell Squats', 'target' => 'Quadriceps, Glutes', 'sets' => 4, 'reps' => '8-10', 'rest' => '90s', 'rpe' => '7', 'adaptation' => ['type' => 'reduced', 'reason' => 'Recovery adaptation'], 'notes' => 'Reduced volume due to recent upper body training'],
                ['icon' => '🦿', 'name' => 'Romanian Deadlifts', 'target' => 'Hamstrings, Glutes', 'sets' => 3, 'reps' => '10-12', 'rest' => '75s', 'rpe' => '6', 'adaptation' => null, 'notes' => null],
                ['icon' => '🦵', 'name' => 'Leg Press', 'target' => 'Quadriceps', 'sets' => 3, 'reps' => '12-15', 'rest' => '60s', 'rpe' => '6', 'adaptation' => ['type' => 'increased', 'reason' => 'Progression'], 'notes' => null],
                ['icon' => '🏋️', 'name' => 'Calf Raises', 'target' => 'Calves', 'sets' => 4, 'reps' => '15-20', 'rest' => '45s', 'rpe' => '5', 'adaptation' => null, 'notes' => null],
            ],
            'cooldown' => [
                ['icon' => '🧘', 'name' => 'Static Stretching', 'duration' => '5 min'],
                ['icon' => '🫁', 'name' => 'Deep Breathing', 'duration' => '3 min'],
            ],
            'activeModifications' => [
                ['condition' => 'Upper Body Fatigue', 'modification' => 'Reduced upper body volume by 20%', 'status' => 'Active', 'severity' => 'warning'],
                ['condition' => 'Knee Sensitivity', 'modification' => 'Low impact alternatives for jumping exercises', 'status' => 'Applied', 'severity' => 'info'],
            ],
            'substitutions' => [
                ['original' => 'Jump Squats', 'replacement' => 'Box Squats', 'reason' => 'Reduced impact on knees'],
                ['original' => 'Burpees', 'replacement' => 'Mountain Climbers', 'reason' => 'Upper body fatigue modification'],
            ],
            'recoveryRecommendations' => [
                ['icon' => '💧', 'title' => 'Stay Hydrated', 'description' => 'Drink at least 2L of water today', 'completed' => false],
                ['icon' => '😴', 'title' => 'Prioritize Sleep', 'description' => 'Aim for 7-8 hours of quality sleep', 'completed' => false],
                ['icon' => '🍌', 'title' => 'Post-Workout Nutrition', 'description' => 'Consume protein within 30 minutes', 'completed' => true],
                ['icon' => '🧊', 'title' => 'Cold Shower', 'description' => '10-minute cold shower for recovery', 'completed' => false],
            ],
        ]);
    }
}
