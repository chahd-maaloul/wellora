<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    /**
     * Admin Dashboard - Main entry point for administrators
     */
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        $stats = [
            'totalUsers' => 1250,
            'totalPatients' => 890,
            'totalMedecins' => 120,
            'totalCoaches' => 45,
            'totalNutritionists' => 35,
            'pendingVerifications' => 18,
            'activeSubscriptions' => 456,
            'revenueThisMonth' => 45600,
        ];

        $recentActivities = [
            [
                'type' => 'user_registered',
                'message' => 'New patient registration',
                'user' => 'Ahmed Ben Ali',
                'time' => '5 minutes ago',
                'icon' => 'fa-user-plus',
                'color' => 'text-green-500',
            ],
            [
                'type' => 'medecin_verified',
                'message' => 'Physician verified',
                'user' => 'Dr. Mohamed Salah',
                'time' => '1 hour ago',
                'icon' => 'fa-check-circle',
                'color' => 'text-blue-500',
            ],
            [
                'type' => 'subscription',
                'message' => 'New premium subscription',
                'user' => 'Fatma Trabelsi',
                'time' => '2 hours ago',
                'icon' => 'fa-credit-card',
                'color' => 'text-purple-500',
            ],
            [
                'type' => 'appointment',
                'message' => 'Teleconsultation completed',
                'user' => 'Dr. Marie Dubois & Jean Dupont',
                'time' => '3 hours ago',
                'icon' => 'fa-video',
                'color' => 'text-wellcare-500',
            ],
        ];

        $pendingProfessionals = [
            [
                'id' => 1,
                'name' => 'Dr. Ali Ben Ahmed',
                'specialty' => 'Cardiologie',
                'licenseNumber' => 'MD-2024-1234',
                'requestDate' => '2024-02-05',
                'diplomaUrl' => '/uploads/diplomas/example.pdf',
            ],
            [
                'id' => 2,
                'name' => 'Dr. Sarah Martin',
                'specialty' => 'Dermatologie',
                'licenseNumber' => 'MD-2024-5678',
                'requestDate' => '2024-02-06',
                'diplomaUrl' => '/uploads/diplomas/example.pdf',
            ],
            [
                'id' => 3,
                'name' => 'Coach Jean Pierre',
                'specialty' => 'Fitness & Nutrition',
                'licenseNumber' => 'CO-2024-9012',
                'requestDate' => '2024-02-07',
                'diplomaUrl' => '/uploads/diplomas/example.pdf',
            ],
        ];

        $systemAlerts = [
            [
                'type' => 'warning',
                'message' => 'Server load at 75%',
                'time' => '10 minutes ago',
            ],
            [
                'type' => 'info',
                'message' => 'New feature update available',
                'time' => '1 day ago',
            ],
        ];

        return $this->render('admin/dashboard.html.twig', [
            'pageTitle' => 'Admin Dashboard - WellCare Connect',
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'pendingProfessionals' => $pendingProfessionals,
            'systemAlerts' => $systemAlerts,
        ]);
    }

    /**
     * User Management
     */
    #[Route('/users', name: 'users')]
    public function users(): Response
    {
        return $this->render('admin/users.html.twig', [
            'pageTitle' => 'User Management - WellCare Connect',
        ]);
    }

    /**
     * Professional Verifications
     */
    #[Route('/verifications', name: 'verifications')]
    public function verifications(): Response
    {
        return $this->render('admin/verifications.html.twig', [
            'pageTitle' => 'Professional Verifications - WellCare Connect',
        ]);
    }

    /**
     * Analytics Overview
     */
    #[Route('/analytics', name: 'analytics')]
    public function analytics(): Response
    {
        return $this->render('admin/analytics.html.twig', [
            'pageTitle' => 'Analytics - WellCare Connect',
        ]);
    }

    /**
     * System Settings
     */
    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        return $this->render('admin/settings.html.twig', [
            'pageTitle' => 'System Settings - WellCare Connect',
        ]);
    }
}
