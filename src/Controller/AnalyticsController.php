<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/analytics')]
class AnalyticsController extends AbstractController
{
    #[Route('/clinic-performance', name: 'analytics_clinic_performance')]
    public function clinicPerformance(): Response
    {
        return $this->render('analytics/clinic-performance.html.twig');
    }

    #[Route('/patient-appointments', name: 'analytics_patient_appointments')]
    public function patientAppointments(): Response
    {
        return $this->render('analytics/patient-appointments.html.twig');
    }

    #[Route('/quality-metrics', name: 'analytics_quality_metrics')]
    public function qualityMetrics(): Response
    {
        return $this->render('analytics/quality-metrics.html.twig');
    }

    #[Route('/financial-reports', name: 'analytics_financial_reports')]
    public function financialReports(): Response
    {
        return $this->render('analytics/financial-reports.html.twig');
    }
}
