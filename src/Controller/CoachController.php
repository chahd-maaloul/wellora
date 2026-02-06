<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/coach')]
class CoachController extends AbstractController
{
    // Coach Dashboard - Main entry point
    #[Route('/dashboard', name: 'coach_dashboard')]
    public function dashboard(): Response
    {
        $stats = [
            'totalClients' => 24,
            'activePrograms' => 18,
            'pendingReviews' => 7,
            'sessionsThisWeek' => 32,
            'avgAdherenceRate' => 87,
            'newClientsThisMonth' => 5,
        ];

        $recentActivity = [
            [
                'type' => 'workout_completed',
                'client' => 'John Doe',
                'message' => 'Completed "Upper Body Strength" workout',
                'time' => '2 hours ago',
                'icon' => 'fa-dumbbell',
                'color' => 'text-green-500',
            ],
            [
                'type' => 'message_received',
                'client' => 'Sarah Wilson',
                'message' => 'Sent a message about knee pain concerns',
                'time' => '3 hours ago',
                'icon' => 'fa-comment-alt',
                'color' => 'text-blue-500',
            ],
            [
                'type' => 'goal_achieved',
                'client' => 'Mike Johnson',
                'message' => 'Achieved "Run 5K in under 25 min" goal',
                'time' => '5 hours ago',
                'icon' => 'fa-trophy',
                'color' => 'text-yellow-500',
            ],
            [
                'type' => 'video_submitted',
                'client' => 'Emily Brown',
                'message' => 'Submitted squat form video for review',
                'time' => '6 hours ago',
                'icon' => 'fa-video',
                'color' => 'text-purple-500',
            ],
            [
                'type' => 'program_assigned',
                'client' => 'David Lee',
                'message' => 'Assigned "12-Week Weight Loss Program"',
                'time' => '1 day ago',
                'icon' => 'fa-clipboard-list',
                'color' => 'text-wellcare-500',
            ],
        ];

        $upcomingSessions = [
            [
                'client' => 'Sarah Wilson',
                'time' => 'Today, 2:00 PM',
                'type' => 'Video Consultation',
                'avatar' => 'S',
            ],
            [
                'client' => 'Mike Johnson',
                'time' => 'Today, 4:00 PM',
                'type' => 'Form Review',
                'avatar' => 'M',
            ],
            [
                'client' => 'Emily Brown',
                'time' => 'Tomorrow, 10:00 AM',
                'type' => 'Progress Assessment',
                'avatar' => 'E',
            ],
        ];

        $clientProgress = [
            [
                'name' => 'John Doe',
                'avatar' => 'J',
                'progress' => 75,
                'goal' => 'Marathon Training',
                'status' => 'on_track',
            ],
            [
                'name' => 'Sarah Wilson',
                'avatar' => 'S',
                'progress' => 45,
                'goal' => 'Weight Loss',
                'status' => 'needs_attention',
            ],
            [
                'name' => 'Mike Johnson',
                'avatar' => 'M',
                'progress' => 90,
                'goal' => 'Strength Building',
                'status' => 'excellent',
            ],
        ];

        return $this->render('coach/dashboard.html.twig', [
            'pageTitle' => 'Coach Dashboard - WellCare Connect',
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'upcomingSessions' => $upcomingSessions,
            'clientProgress' => $clientProgress,
        ]);
    }

    // Client Management
    #[Route('/clients', name: 'coach_clients')]
    public function clientManagement(): Response
    {
        $clients = [
            [
                'id' => 1,
                'name' => 'John Doe',
                'avatar' => 'J',
                'email' => 'john.doe@email.com',
                'phone' => '+1 234 567 890',
                'status' => 'active',
                'adherenceRate' => 92,
                'goals' => ['Marathon Training', 'Weight Management'],
                'nextCheckIn' => 'Tomorrow, 2:00 PM',
                'lastActive' => '2 hours ago',
                'startDate' => 'Jan 15, 2024',
                'medicalNotes' => 'No limitations',
                'progress' => 75,
            ],
            [
                'id' => 2,
                'name' => 'Sarah Wilson',
                'avatar' => 'S',
                'email' => 'sarah.wilson@email.com',
                'phone' => '+1 234 567 891',
                'status' => 'active',
                'adherenceRate' => 78,
                'goals' => ['Weight Loss', 'Cardio Fitness'],
                'nextCheckIn' => 'Today, 4:00 PM',
                'lastActive' => '1 hour ago',
                'startDate' => 'Feb 1, 2024',
                'medicalNotes' => 'Knee sensitivity - avoid high impact',
                'progress' => 45,
            ],
            [
                'id' => 3,
                'name' => 'Mike Johnson',
                'avatar' => 'M',
                'email' => 'mike.johnson@email.com',
                'phone' => '+1 234 567 892',
                'status' => 'active',
                'adherenceRate' => 95,
                'goals' => ['Strength Building', 'Muscle Gain'],
                'nextCheckIn' => 'Friday, 10:00 AM',
                'lastActive' => '30 mins ago',
                'startDate' => 'Jan 1, 2024',
                'medicalNotes' => 'Shoulder rehab - modified upper body',
                'progress' => 90,
            ],
            [
                'id' => 4,
                'name' => 'Emily Brown',
                'avatar' => 'E',
                'email' => 'emily.brown@email.com',
                'phone' => '+1 234 567 893',
                'status' => 'active',
                'adherenceRate' => 85,
                'goals' => ['Flexibility', 'Posture Correction'],
                'nextCheckIn' => 'Wednesday, 3:00 PM',
                'lastActive' => '5 hours ago',
                'startDate' => 'Mar 1, 2024',
                'medicalNotes' => 'Lower back pain history',
                'progress' => 60,
            ],
            [
                'id' => 5,
                'name' => 'David Lee',
                'avatar' => 'D',
                'email' => 'david.lee@email.com',
                'phone' => '+1 234 567 894',
                'status' => 'inactive',
                'adherenceRate' => 45,
                'goals' => ['Weight Loss', 'General Fitness'],
                'nextCheckIn' => 'TBD',
                'lastActive' => '1 week ago',
                'startDate' => 'Feb 15, 2024',
                'medicalNotes' => 'No limitations',
                'progress' => 25,
            ],
            [
                'id' => 6,
                'name' => 'Lisa Anderson',
                'avatar' => 'L',
                'email' => 'lisa.anderson@email.com',
                'phone' => '+1 234 567 895',
                'status' => 'active',
                'adherenceRate' => 88,
                'goals' => ['Athletic Performance', 'Speed Training'],
                'nextCheckIn' => 'Thursday, 11:00 AM',
                'lastActive' => '4 hours ago',
                'startDate' => 'Jan 20, 2024',
                'medicalNotes' => 'Ankle sprain recovery',
                'progress' => 70,
            ],
        ];

        return $this->render('coach/client-management.html.twig', [
            'pageTitle' => 'Client Management - WellCare Connect',
            'clients' => $clients,
        ]);
    }

    // Client Detail Page
    #[Route('/clients/{id}', name: 'coach_client_detail')]
    public function clientDetail(int $id): Response
    {
        $client = [
            'id' => $id,
            'name' => 'John Doe',
            'avatar' => 'J',
            'email' => 'john.doe@email.com',
            'phone' => '+1 234 567 890',
            'status' => 'active',
            'goals' => ['Marathon Training', 'Weight Management'],
            'medicalNotes' => 'No limitations',
            'startDate' => 'Jan 15, 2024',
            'age' => 32,
            'weight' => '75 kg',
            'height' => '178 cm',
            'fitnessLevel' => 'Intermediate',
        ];

        $workoutHistory = [
            [
                'date' => 'Today',
                'workout' => 'Morning Run',
                'duration' => '45 min',
                'calories' => 420,
                'status' => 'completed',
            ],
            [
                'date' => 'Yesterday',
                'workout' => 'Upper Body Strength',
                'duration' => '60 min',
                'calories' => 380,
                'status' => 'completed',
            ],
            [
                'date' => '2 days ago',
                'workout' => 'Rest Day',
                'duration' => '-',
                'calories' => 0,
                'status' => 'rest',
            ],
        ];

        return $this->render('coach/client-detail.html.twig', [
            'pageTitle' => 'Client Detail - WellCare Connect',
            'client' => $client,
            'workoutHistory' => $workoutHistory,
        ]);
    }

    // Program Designer
    #[Route('/programs', name: 'coach_programs')]
    public function programDesigner(): Response
    {
        $templates = [
            [
                'id' => 1,
                'name' => '12-Week Weight Loss Program',
                'description' => 'Comprehensive weight loss program combining cardio and strength training',
                'duration' => '12 weeks',
                'difficulty' => 'Intermediate',
                'clientsUsing' => 8,
                'rating' => 4.8,
                'image' => 'fa-weight-loss',
                'color' => 'text-red-500',
            ],
            [
                'id' => 2,
                'name' => 'Marathon Training Plan',
                'description' => 'Progressive running program for marathon preparation',
                'duration' => '16 weeks',
                'difficulty' => 'Advanced',
                'clientsUsing' => 5,
                'rating' => 4.9,
                'image' => 'fa-running',
                'color' => 'text-blue-500',
            ],
            [
                'id' => 3,
                'name' => 'Strength Fundamentals',
                'description' => 'Beginner-friendly strength training program',
                'duration' => '8 weeks',
                'difficulty' => 'Beginner',
                'clientsUsing' => 12,
                'rating' => 4.7,
                'image' => 'fa-dumbbell',
                'color' => 'text-green-500',
            ],
            [
                'id' => 4,
                'name' => 'Mobility & Flexibility',
                'description' => 'Improve range of motion and reduce injury risk',
                'duration' => '6 weeks',
                'difficulty' => 'All Levels',
                'clientsUsing' => 6,
                'rating' => 4.6,
                'image' => 'fa-yoga',
                'color' => 'text-purple-500',
            ],
        ];

        $exercises = [
            [
                'name' => 'Squats',
                'category' => 'Strength',
                'difficulty' => 'Intermediate',
                'equipment' => 'Bodyweight, Barbell',
                'muscles' => ['Quadriceps', 'Glutes', 'Hamstrings'],
                'video' => true,
            ],
            [
                'name' => 'Push-ups',
                'category' => 'Strength',
                'difficulty' => 'Beginner',
                'equipment' => 'Bodyweight',
                'muscles' => ['Chest', 'Triceps', 'Shoulders'],
                'video' => true,
            ],
            [
                'name' => 'Lunges',
                'category' => 'Strength',
                'difficulty' => 'Beginner',
                'equipment' => 'Bodyweight, Dumbbells',
                'muscles' => ['Quadriceps', 'Glutes', 'Hamstrings'],
                'video' => true,
            ],
            [
                'name' => 'Deadlifts',
                'category' => 'Strength',
                'difficulty' => 'Advanced',
                'equipment' => 'Barbell, Dumbbells',
                'muscles' => ['Back', 'Glutes', 'Hamstrings'],
                'video' => true,
            ],
            [
                'name' => 'Planks',
                'category' => 'Core',
                'difficulty' => 'Beginner',
                'equipment' => 'Bodyweight',
                'muscles' => ['Core', 'Shoulders'],
                'video' => true,
            ],
            [
                'name' => 'Burpees',
                'category' => 'Cardio',
                'difficulty' => 'Advanced',
                'equipment' => 'Bodyweight',
                'muscles' => ['Full Body'],
                'video' => true,
            ],
        ];

        return $this->render('coach/program-designer.html.twig', [
            'pageTitle' => 'Program Designer - WellCare Connect',
            'templates' => $templates,
            'exercises' => $exercises,
        ]);
    }

    // Progress Monitoring
    #[Route('/progress', name: 'coach_progress')]
    public function progressMonitoring(): Response
    {
        $clients = [
            [
                'name' => 'John Doe',
                'avatar' => 'J',
                'goal' => 'Marathon Training',
                'progress' => 75,
                'adherenceRate' => 92,
                'trend' => 'up',
                'nextMilestone' => 'Complete 15K run',
                'daysRemaining' => 14,
            ],
            [
                'name' => 'Sarah Wilson',
                'avatar' => 'S',
                'goal' => 'Weight Loss',
                'progress' => 45,
                'adherenceRate' => 78,
                'trend' => 'stable',
                'nextMilestone' => 'Lose 2 more kg',
                'daysRemaining' => 30,
            ],
            [
                'name' => 'Mike Johnson',
                'avatar' => 'M',
                'goal' => 'Strength Building',
                'progress' => 90,
                'adherenceRate' => 95,
                'trend' => 'up',
                'nextMilestone' => 'Increase bench press by 10kg',
                'daysRemaining' => 7,
            ],
        ];

        $analytics = [
            'totalWorkoutsCompleted' => 456,
            'totalHoursTrained' => 380,
            'avgSessionDuration' => '52 min',
            'caloriesBurned' => 45600,
            'improvementRate' => 23,
            'injuryRate' => 2,
        ];

        return $this->render('coach/progress-monitoring.html.twig', [
            'pageTitle' => 'Progress Monitoring - WellCare Connect',
            'clients' => $clients,
            'analytics' => $analytics,
        ]);
    }

    // Communication Hub
    #[Route('/messages', name: 'coach_messages')]
    public function communicationHub(): Response
    {
        $conversations = [
            [
                'id' => 1,
                'client' => 'John Doe',
                'avatar' => 'J',
                'lastMessage' => 'Great job on today\'s workout! Your form is improving.',
                'time' => '2 hours ago',
                'unread' => 0,
                'online' => true,
            ],
            [
                'id' => 2,
                'client' => 'Sarah Wilson',
                'avatar' => 'S',
                'lastMessage' => 'I\'m experiencing some knee discomfort after running',
                'time' => '30 mins ago',
                'unread' => 2,
                'online' => true,
            ],
            [
                'id' => 3,
                'client' => 'Mike Johnson',
                'avatar' => 'M',
                'lastMessage' => 'Video uploaded for squat form review',
                'time' => '1 hour ago',
                'unread' => 1,
                'online' => false,
            ],
            [
                'id' => 4,
                'client' => 'Emily Brown',
                'avatar' => 'E',
                'lastMessage' => 'Thank you for the mobility exercises, they really help!',
                'time' => 'Yesterday',
                'unread' => 0,
                'online' => false,
            ],
        ];

        return $this->render('coach/communication-hub.html.twig', [
            'pageTitle' => 'Communication Hub - WellCare Connect',
            'conversations' => $conversations,
        ]);
    }

    // Reporting Tools
    #[Route('/reports', name: 'coach_reports')]
    public function reportingTools(): Response
    {
        $reports = [
            [
                'id' => 1,
                'name' => 'Monthly Progress Report - John Doe',
                'type' => 'Individual',
                'createdAt' => 'Feb 5, 2024',
                'status' => 'ready',
            ],
            [
                'id' => 2,
                'name' => 'Q1 Client Overview',
                'type' => 'Aggregate',
                'createdAt' => 'Feb 1, 2024',
                'status' => 'ready',
            ],
            [
                'id' => 3,
                'name' => 'Program Effectiveness Analysis',
                'type' => 'Analytics',
                'createdAt' => 'Jan 28, 2024',
                'status' => 'ready',
            ],
            [
                'id' => 4,
                'name' => 'Client Retention Report',
                'type' => 'Business',
                'createdAt' => 'Jan 15, 2024',
                'status' => 'ready',
            ],
        ];

        $clients = [
            ['id' => 1, 'name' => 'John Doe'],
            ['id' => 2, 'name' => 'Jane Smith'],
            ['id' => 3, 'name' => 'Mike Johnson'],
            ['id' => 4, 'name' => 'Emily Brown'],
        ];

        $metrics = [
            ['id' => 'weight', 'name' => 'Weight'],
            ['id' => 'bodyFat', 'name' => 'Body Fat %'],
            ['id' => 'muscleMass', 'name' => 'Muscle Mass'],
            ['id' => 'vo2Max', 'name' => 'VO2 Max'],
            ['id' => 'restingHR', 'name' => 'Resting Heart Rate'],
            ['id' => 'flexibility', 'name' => 'Flexibility'],
        ];

        return $this->render('coach/reporting-tools.html.twig', [
            'pageTitle' => 'Reporting Tools - WellCare Connect',
            'reports' => $reports,
            'recentReports' => $reports,  // Same as reports for now
            'clients' => $clients,
            'stats' => [
                'activeClients' => 24,
                'completedReports' => 47,
                'pendingReports' => 5,
                'totalClients' => 28,
                'newClientsThisMonth' => 3,
                'sessionsThisMonth' => 156,
                'completionRate' => 94,
                'avgAdherence' => 87,
                'goalsAchieved' => 156,
                'goalsInProgress' => 23,
            ],
            'metrics' => $metrics,
        ]);
    }
}
