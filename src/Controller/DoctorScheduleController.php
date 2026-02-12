<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/doctor/schedule')]
class DoctorScheduleController extends AbstractController
{
    #[Route('', name: 'doctor_schedule')]
    #[Route('/week', name: 'doctor_schedule_week')]  // Route principale = semaine
    public function index(): Response
    {
        return $this->render('doctor/schedule/week-view.html.twig');
    }

    #[Route('/day', name: 'doctor_schedule_day')]
    public function dayView(): Response
    {
        return $this->render('doctor/schedule/day-view.html.twig');
    }

    #[Route('/week', name: 'doctor_schedule_week_view')]  // Alias
    public function weekView(): Response
    {
        return $this->render('doctor/schedule/week-view.html.twig');
    }

    #[Route('/month', name: 'doctor_schedule_month')]
    public function monthView(): Response
    {
        return $this->render('doctor/schedule/month-view.html.twig');
    }
}