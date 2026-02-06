<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/appointment')]
class AppointmentController extends AbstractController
{
    #[Route('/search-doctors', name: 'appointment_search_doctors')]
    public function searchDoctors(): Response
    {
        return $this->render('appointment/search-doctors.html.twig');
    }

    #[Route('/search-doctors/', name: 'appointment_search_doctors_slash')]
    public function searchDoctorsSlash(): RedirectResponse
    {
        return $this->redirectToRoute('appointment_search_doctors');
    }

    #[Route('/doctor-profile', name: 'appointment_doctor_profile')]
    public function doctorProfile(): Response
    {
        return $this->render('appointment/doctor-profile.html.twig');
    }

    #[Route('/doctor-profile/', name: 'appointment_doctor_profile_slash')]
    public function doctorProfileSlash(): RedirectResponse
    {
        return $this->redirectToRoute('appointment_doctor_profile');
    }

    #[Route('/booking-flow', name: 'appointment_booking_flow')]
    public function bookingFlow(): Response
    {
        return $this->render('appointment/booking-flow.html.twig');
    }

    #[Route('/booking-flow/', name: 'appointment_booking_flow_slash')]
    public function bookingFlowSlash(): RedirectResponse
    {
        return $this->redirectToRoute('appointment_booking_flow');
    }

    #[Route('/confirmation', name: 'appointment_confirmation')]
    public function confirmation(): Response
    {
        return $this->render('appointment/confirmation.html.twig');
    }

    #[Route('/confirmation/', name: 'appointment_confirmation_slash')]
    public function confirmationSlash(): RedirectResponse
    {
        return $this->redirectToRoute('appointment_confirmation');
    }

    #[Route('/patient-dashboard', name: 'appointment_patient_dashboard')]
    public function patientDashboard(): Response
    {
        return $this->render('appointment/patient-dashboard.html.twig');
    }

    #[Route('/patient-dashboard/', name: 'appointment_patient_dashboard_slash')]
    public function patientDashboardSlash(): RedirectResponse
    {
        return $this->redirectToRoute('appointment_patient_dashboard');
    }

    #[Route('/consultation-room', name: 'appointment_consultation_room')]
    public function consultationRoom(): Response
    {
        return $this->render('appointment/consultation-room.html.twig', [
            'appointmentId' => 1,
        ]);
    }

    #[Route('/consultation-room/', name: 'appointment_consultation_room_slash')]
    public function consultationRoomSlash(): RedirectResponse
    {
        return $this->redirectToRoute('appointment_consultation_room');
    }
}
