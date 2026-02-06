<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/doctor/schedule')]
class DoctorScheduleController extends AbstractController
{
    #[Route('/day-view', name: 'doctor_schedule_day_view')]
    public function dayView(): Response
    {
        return $this->render('doctor/schedule/day-view.html.twig');
    }

    #[Route('/week-view', name: 'doctor_schedule_week_view')]
    public function weekView(): Response
    {
        return $this->render('doctor/schedule/week-view.html.twig');
    }

    #[Route('/month-view', name: 'doctor_schedule_month_view')]
    public function monthView(): Response
    {
        return $this->render('doctor/schedule/month-view.html.twig');
    }
}
