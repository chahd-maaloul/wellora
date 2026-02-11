<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/teleconsultation')]
class TeleconsultationController extends AbstractController
{
    #[Route('/waiting-room', name: 'teleconsultation_waiting_room')]
    public function waitingRoom(): Response
    {
        return $this->render('teleconsultation/waiting-room.html.twig');
    }

    #[Route('/waiting-room/', name: 'teleconsultation_waiting_room_slash')]
    public function waitingRoomSlash(): RedirectResponse
    {
        return $this->redirectToRoute('teleconsultation_waiting_room');
    }

    #[Route('/consultation-room', name: 'teleconsultation_consultation_room')]
    public function consultationRoom(): Response
    {
        return $this->render('teleconsultation/consultation-room.html.twig');
    }

    #[Route('/consultation-room/', name: 'teleconsultation_consultation_room_slash')]
    public function consultationRoomSlash(): RedirectResponse
    {
        return $this->redirectToRoute('teleconsultation_consultation_room');
    }

    #[Route('/medical-tools', name: 'teleconsultation_medical_tools')]
    public function medicalTools(): Response
    {
        return $this->render('teleconsultation/medical-tools.html.twig');
    }

    #[Route('/medical-tools/', name: 'teleconsultation_medical_tools_slash')]
    public function medicalToolsSlash(): RedirectResponse
    {
        return $this->redirectToRoute('teleconsultation_medical_tools');
    }

    #[Route('/prescription-writer', name: 'teleconsultation_prescription_writer')]
    public function prescriptionWriter(): Response
    {
        return $this->render('teleconsultation/prescription-writer.html.twig');
    }

    #[Route('/prescription-writer/', name: 'teleconsultation_prescription_writer_slash')]
    public function prescriptionWriterSlash(): RedirectResponse
    {
        return $this->redirectToRoute('teleconsultation_prescription_writer');
    }

    #[Route('/soap-notes', name: 'teleconsultation_soap_notes')]
    public function soapNotes(): Response
    {
        return $this->render('teleconsultation/soap-notes.html.twig');
    }

    #[Route('/soap-notes/', name: 'teleconsultation_soap_notes_slash')]
    public function soapNotesSlash(): RedirectResponse
    {
        return $this->redirectToRoute('teleconsultation_soap_notes');
    }
}
