<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminTrailAnalyticsController extends AbstractController
{
    // ==================== ADMIN TRAIL ANALYTICS ROUTES ====================
    
    #[Route('/admin/trail-analytics', name: 'admin_trail_analytics')]
    public function dashboard(): Response
    {
        return $this->render('admin/trail-analytics/dashboard.html.twig');
    }

    #[Route('/admin/trail-analytics/usage', name: 'admin_trail_usage')]
    public function usage(): Response
    {
        return $this->render('admin/trail-analytics/usage-metrics.html.twig');
    }

    #[Route('/admin/trail-analytics/safety', name: 'admin_trail_safety')]
    public function safety(): Response
    {
        return $this->render('admin/trail-analytics/safety-reports.html.twig');
    }

    #[Route('/admin/trail-analytics/user-behavior', name: 'admin_trail_user_behavior')]
    public function userBehavior(): Response
    {
        return $this->render('admin/trail-analytics/user-behavior.html.twig');
    }

    #[Route('/admin/trail-analytics/content', name: 'admin_trail_content')]
    public function content(): Response
    {
        return $this->render('admin/trail-analytics/content-metrics.html.twig');
    }

    #[Route('/admin/trail-analytics/reports', name: 'admin_trail_reports')]
    public function reports(): Response
    {
        return $this->render('admin/trail-analytics/report-generator.html.twig');
    }
}
