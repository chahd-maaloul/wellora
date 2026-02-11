<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Repository\ConsultationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/appointment')]
class AppointmentController extends AbstractController
{
    // ============== PATIENT SIDE - PAGES ==============

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

    #[Route('/doctor-profile/{id}', name: 'appointment_doctor_profile', requirements: ['id' => '\d+'])]
    public function doctorProfile(int $id = null): Response
    {
        return $this->render('appointment/doctor-profile.html.twig', [
            'doctorId' => $id
        ]);
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
        return $this->render('appointment/confirmation.html.twig', [
            'consultation' => null
        ]);
    }

    #[Route('/confirmation/{id}', name: 'appointment_confirmation_detail', requirements: ['id' => '\d+'])]
    public function confirmationDetail(int $id, ConsultationRepository $consultationRepository): Response
    {
        $consultation = $consultationRepository->find($id);
        
        if (!$consultation) {
            throw $this->createNotFoundException('Rendez-vous non trouvé');
        }
        
        return $this->render('appointment/confirmation.html.twig', [
            'consultation' => $consultation
        ]);
    }

    #[Route('/confirmation/', name: 'appointment_confirmation_slash')]
    public function confirmationSlash(): RedirectResponse
    {
        return $this->redirectToRoute('appointment_confirmation');
    }

    #[Route('/patient-dashboard', name: 'appointment_patient_dashboard')]
    public function patientDashboard(ConsultationRepository $consultationRepository): Response
    {
        $consultations = $consultationRepository->findAll();
        
        return $this->render('appointment/patient-dashboard.html.twig', [
            'consultations' => $consultations
        ]);
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

    // ============== PATIENT SIDE - API ==============

    /**
     * CRÉER UN RENDEZ-VOUS (patient)
     * Statut initial: 'pending'
     */
    #[Route('/create', name: 'appointment_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            $data = $request->request->all();
        }

        // Validation
        if (empty($data['consultationType'])) {
            return $this->json(['success' => false, 'message' => 'Le type de consultation est requis'], 400);
        }

        if (empty($data['reason'])) {
            return $this->json(['success' => false, 'message' => 'Le motif de la visite est requis'], 400);
        }

        if (empty($data['selectedDate'])) {
            return $this->json(['success' => false, 'message' => 'La date est requise'], 400);
        }

        $consultation = new Consultation();
        
        // Données obligatoires
        $consultation->setConsultationType($data['consultationType']);
        $consultation->setReasonForVisit($data['reason']);
        
        // Symptômes
        if (!empty($data['symptoms']) && is_array($data['symptoms'])) {
            $consultation->setSymptomsDescription(implode(', ', $data['symptoms']));
        } else {
            $consultation->setSymptomsDescription('');
        }
        
        // Date
        try {
            $date = new \DateTime($data['selectedDate']);
            $consultation->setDateConsultation($date);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Format de date invalide'], 400);
        }
        
        // Heure
        if (!empty($data['selectedTime'])) {
            try {
                $time = \DateTime::createFromFormat('h:i A', $data['selectedTime']);
                if (!$time) {
                    $time = \DateTime::createFromFormat('H:i', $data['selectedTime']);
                }
                if (!$time) {
                    $time = new \DateTime($data['selectedTime']);
                }
                
                if ($time) {
                    $consultation->setTimeConsultation($time);
                }
            } catch (\Exception $e) {
                $consultation->setTimeConsultation(new \DateTime('09:00'));
            }
        } else {
            $consultation->setTimeConsultation(new \DateTime('09:00'));
        }
        
        // Durée
        $consultation->setDuration($data['duration'] ?? 30);
        
        // Mode
        $appointmentMode = $data['appointmentMode'] ?? 'in-person';
        $consultation->setAppointmentMode($appointmentMode);
        
        // Localisation
        if ($appointmentMode === 'in-person') {
            $consultation->setLocation('123 Avenue Habib Bourguiba, Tunis');
        } else {
            $consultation->setLocation('Online');
        }
        
        // Frais
        $fees = ['in-person' => 120, 'video' => 90, 'phone' => 70];
        $consultation->setFee($fees[$appointmentMode] ?? 120);
        
        // STATUT INITIAL: EN ATTENTE
        $consultation->setStatus('pending');
        
        // Notes
        if (!empty($data['notes'])) {
            $consultation->setNotes($data['notes']);
        }

        try {
            $entityManager->persist($consultation);
            $entityManager->flush();
            
            error_log('✅ Rendez-vous créé ID: ' . $consultation->getId() . ' - Statut: pending');
            
            return $this->json([
                'success' => true,
                'message' => 'Rendez-vous créé avec succès et en attente de validation',
                'appointmentId' => $consultation->getId()
            ]);
        } catch (\Exception $e) {
            error_log('❌ Erreur création: ' . $e->getMessage());
            
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ANNULER UN RENDEZ-VOUS (patient)
     */
    #[Route('/cancel/{id}', name: 'appointment_cancel', methods: ['POST'])]
    public function cancel(int $id, Request $request, ConsultationRepository $consultationRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $consultation = $consultationRepository->find($id);
        
        if (!$consultation) {
            return $this->json(['success' => false, 'message' => 'Rendez-vous non trouvé'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        $reason = $data['reason'] ?? '';
        
        $consultation->setStatus('cancelled');
        $consultation->setNotes($consultation->getNotes() . ' | Annulé: ' . $reason);
        $consultation->setUpdatedAt(new \DateTime());
        
        $entityManager->flush();
        
        return $this->json(['success' => true, 'message' => 'Rendez-vous annulé avec succès']);
    }

    /**
     * REPROGRAMMER UN RENDEZ-VOUS (page)
     */
    #[Route('/reschedule/{id}', name: 'appointment_reschedule')]
    public function reschedule(int $id, ConsultationRepository $consultationRepository): Response
    {
        $consultation = $consultationRepository->find($id);
        
        return $this->render('appointment/booking-flow.html.twig', [
            'consultation' => $consultation,
            'rescheduleId' => $consultation ? $consultation->getId() : null,
            'rescheduleData' => $consultation ? [
                'date' => $consultation->getDateConsultation() ? $consultation->getDateConsultation()->format('Y-m-d') : null,
                'time' => $consultation->getTimeConsultation() ? $consultation->getTimeConsultation()->format('h:i A') : null,
                'consultationType' => $consultation->getConsultationType(),
                'appointmentMode' => $consultation->getAppointmentMode(),
                'duration' => $consultation->getDuration(),
                'reason' => $consultation->getReasonForVisit(),
            ] : null
        ]);
    }

    /**
     * REPROGRAMMER UN RENDEZ-VOUS (action)
     */
    #[Route('/reschedule/{id}', name: 'appointment_reschedule_update', methods: ['POST'])]
    public function rescheduleUpdate(int $id, Request $request, ConsultationRepository $consultationRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $consultation = $consultationRepository->find($id);

        if (!$consultation) {
            return $this->json(['success' => false, 'message' => 'Rendez-vous non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            $data = $request->request->all();
        }

        if (empty($data['selectedDate'])) {
            return $this->json(['success' => false, 'message' => 'La date est requise'], 400);
        }

        try {
            $date = new \DateTime($data['selectedDate']);
            $consultation->setDateConsultation($date);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Format de date invalide'], 400);
        }

        if (!empty($data['selectedTime'])) {
            try {
                $time = \DateTime::createFromFormat('h:i A', $data['selectedTime']);
                if (!$time) {
                    $time = \DateTime::createFromFormat('H:i', $data['selectedTime']);
                }
                if ($time) {
                    $consultation->setTimeConsultation($time);
                }
            } catch (\Exception $e) {
                return $this->json(['success' => false, 'message' => 'Format d\'heure invalide'], 400);
            }
        }

        if (!empty($data['appointmentMode'])) {
            $consultation->setAppointmentMode($data['appointmentMode']);
        }

        if (!empty($data['duration'])) {
            $consultation->setDuration((int) $data['duration']);
        }

        $consultation->setUpdatedAt(new \DateTime());
        $consultation->setStatus('pending'); // Remis en attente
        
        $entityManager->flush();

        return $this->json(['success' => true, 'message' => 'Rendez-vous reprogrammé avec succès']);
    }

    /**
     * SUPPRIMER UN RENDEZ-VOUS
     */
    #[Route('/delete/{id}', name: 'appointment_delete', methods: ['DELETE'])]
    public function deleteAppointment(int $id, ConsultationRepository $consultationRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $consultation = $consultationRepository->find($id);

        if (!$consultation) {
            return $this->json(['success' => false, 'message' => 'Rendez-vous non trouvé'], 404);
        }

        $entityManager->remove($consultation);
        $entityManager->flush();

        return $this->json(['success' => true, 'message' => 'Rendez-vous supprimé avec succès']);
    }

    // ============== PATIENT DASHBOARD API ==============
    // ⚠️⚠️⚠️ NE PAS MODIFIER - UTILISÉ PAR LE DASHBOARD PATIENT ⚠️⚠️⚠️

    /**
     * API POUR LE DASHBOARD PATIENT
     * Format EXACT attendu par le template patient-dashboard.html.twig
     */
    #[Route('/api/appointments', name: 'api_appointments', methods: ['GET'])]
    public function getAppointments(ConsultationRepository $consultationRepository): JsonResponse
    {
        $consultations = $consultationRepository->findAll();
        
        $upcoming = [];
        $past = [];
        $cancelled = [];
        $now = new \DateTime();
        
        foreach ($consultations as $consultation) {
            $date = $consultation->getDateConsultation();
            $time = $consultation->getTimeConsultation();
            $appointmentDateTime = null;
            
            if ($date instanceof \DateTimeInterface) {
                $appointmentDateTime = new \DateTime($date->format('Y-m-d'));
                if ($time instanceof \DateTimeInterface) {
                    $appointmentDateTime->setTime(
                        (int) $time->format('H'),
                        (int) $time->format('i')
                    );
                }
            }

            $appointmentData = [
                'id' => $consultation->getId(),
                'doctorId' => 1,
                'doctorName' => 'Ahmed Ben Ali',
                'specialty' => 'Cardiologist',
                'month' => $date ? $date->format('M') : '',
                'day' => $date ? $date->format('d') : '',
                'weekday' => $date ? $date->format('D') : '',
                'time' => $time ? $time->format('h:i A') : '',
                'duration' => $consultation->getDuration() . ' min',
                'type' => $consultation->getAppointmentMode() ?? 'in-person',
                'location' => $consultation->getLocation(),
                'status' => $consultation->getStatus(),
                'canJoin' => false,
                'isSoon' => false,
                'notes' => $consultation->getNotes(),
                'hasReview' => false,
                'rating' => 0
            ];
            
            if ($consultation->getStatus() === 'cancelled') {
                $appointmentData['cancelledDate'] = $consultation->getUpdatedAt()->format('M d, Y');
                $appointmentData['cancellationReason'] = 'Annulé par le patient';
                $cancelled[] = $appointmentData;
            } elseif ($appointmentDateTime instanceof \DateTimeInterface && $appointmentDateTime >= $now) {
                $upcoming[] = $appointmentData;
            } else {
                $past[] = $appointmentData;
            }
        }
        
        return $this->json([
            'upcoming' => $upcoming,
            'past' => $past,
            'cancelled' => $cancelled
        ]);
    }

    // ============== DOCTOR SIDE - PAGES ==============

    /**
     * Page des demandes en attente - CORRIGÉ avec méthode du Repository
     */
    #[Route('/doctor/pending', name: 'appointment_doctor_pending')]
    public function doctorPendingAppointments(ConsultationRepository $consultationRepository): Response
    {
        // ✅ UTILISATION DE LA MÉTHODE DU REPOSITORY
        $pendingAppointments = $consultationRepository->findPendingOrderedByDate();
        
        return $this->render('appointment/doctor-pending.html.twig', [
            'pendingAppointments' => $pendingAppointments
        ]);
    }

    // ============== DOCTOR SIDE - API ==============

    /**
     * API: COMPTEUR DES DEMANDES EN ATTENTE (pour le badge)
     */
    #[Route('/api/doctor/pending', name: 'api_doctor_pending', methods: ['GET'])]
    public function getPendingCount(ConsultationRepository $consultationRepository): JsonResponse
    {
        $pendingCount = $consultationRepository->count(['status' => 'pending']);
        
        return $this->json([
            'success' => true,
            'count' => $pendingCount
        ]);
    }

    /**
     * API: LISTE DES DEMANDES EN ATTENTE (détaillée) - CORRIGÉ
     */
    #[Route('/api/doctor/pending/list', name: 'api_doctor_pending_list', methods: ['GET'])]
    public function getPendingAppointments(ConsultationRepository $consultationRepository): JsonResponse
    {
        // ✅ UTILISATION DE LA MÉTHODE DU REPOSITORY
        $pendingAppointments = $consultationRepository->findPendingOrderedByDate();
        
        $appointments = [];
        foreach ($pendingAppointments as $consultation) {
            $appointments[] = $this->formatAppointmentData($consultation);
        }
        
        return $this->json([
            'success' => true,
            'appointments' => $appointments,
            'count' => count($appointments)
        ]);
    }

    /**
     * API: ACCEPTER UN RENDEZ-VOUS
     * pending -> accepted
     */
    #[Route('/api/doctor/accept/{id}', name: 'appointment_accept', methods: ['POST'])]
    public function acceptAppointment(int $id, ConsultationRepository $consultationRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $consultation = $consultationRepository->find($id);
        
        if (!$consultation) {
            return $this->json(['success' => false, 'message' => 'Rendez-vous non trouvé'], 404);
        }
        
        if ($consultation->getStatus() !== 'pending') {
            return $this->json([
                'success' => false, 
                'message' => 'Ce rendez-vous n\'est pas en attente (statut: ' . $consultation->getStatus() . ')'
            ], 400);
        }
        
        $consultation->setStatus('accepted');
        $consultation->setUpdatedAt(new \DateTime());
        $consultation->setNotes(($consultation->getNotes() ?? '') . ' | Accepté le ' . date('d/m/Y H:i'));
        
        try {
            $entityManager->flush();
            
            error_log("✅ Rendez-vous #$id accepté - Maintenant dans le planning");
            
            return $this->json([
                'success' => true,
                'message' => 'Rendez-vous accepté avec succès',
                'appointmentId' => $id,
                'newStatus' => 'accepted'
            ]);
        } catch (\Exception $e) {
            error_log("❌ Erreur acceptation #$id: " . $e->getMessage());
            
            return $this->json([
                'success' => false, 
                'message' => 'Erreur lors de l\'acceptation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: REFUSER UN RENDEZ-VOUS
     * pending -> rejected
     */
    #[Route('/api/doctor/reject/{id}', name: 'appointment_reject', methods: ['POST'])]
    public function rejectAppointment(int $id, Request $request, ConsultationRepository $consultationRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $consultation = $consultationRepository->find($id);
        
        if (!$consultation) {
            return $this->json(['success' => false, 'message' => 'Rendez-vous non trouvé'], 404);
        }
        
        if ($consultation->getStatus() !== 'pending') {
            return $this->json([
                'success' => false, 
                'message' => 'Ce rendez-vous n\'est pas en attente'
            ], 400);
        }
        
        $data = json_decode($request->getContent(), true);
        $reason = $data['reason'] ?? 'Non spécifiée';
        
        $consultation->setStatus('rejected');
        $consultation->setNotes(($consultation->getNotes() ?? '') . ' | Refusé: ' . $reason);
        $consultation->setUpdatedAt(new \DateTime());
        
        try {
            $entityManager->flush();
            
            error_log("✅ Rendez-vous #$id refusé - Raison: $reason");
            
            return $this->json([
                'success' => true,
                'message' => 'Rendez-vous refusé',
                'appointmentId' => $id,
                'newStatus' => 'rejected'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false, 
                'message' => 'Erreur lors du refus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: PLANNING MÉDECIN - RENDEZ-VOUS ACCEPTÉS - CORRIGÉ
     * UNIQUEMENT les rendez-vous avec statut 'accepted'
     */
    #[Route('/api/doctor/accepted', name: 'api_doctor_accepted', methods: ['GET'])]
    public function getAcceptedAppointments(ConsultationRepository $consultationRepository): JsonResponse
    {
        // ✅ UTILISATION DE LA MÉTHODE DU REPOSITORY
        $acceptedAppointments = $consultationRepository->findAcceptedOrderedByDate();
        
        $appointments = [];
        foreach ($acceptedAppointments as $consultation) {
            $date = $consultation->getDateConsultation();
            $time = $consultation->getTimeConsultation();
            
            // Déterminer le type pour l'affichage
            $type = $this->determineAppointmentType($consultation);
            
            $appointments[] = [
                'id' => $consultation->getId(),
                'patientName' => $this->getPatientName($consultation),
                'consultationType' => $consultation->getConsultationType(),
                'reason' => $consultation->getReasonForVisit(),
                'date' => $date ? $date->format('Y-m-d') : null,
                'time' => $time ? $time->format('H:i') : null,
                'duration' => $consultation->getDuration(),
                'mode' => $consultation->getAppointmentMode(),
                'location' => $consultation->getLocation(),
                'fee' => $consultation->getFee(),
                'status' => $consultation->getStatus(),
                'type' => $type,
                'notes' => $consultation->getNotes(),
            ];
        }
        
        error_log("📊 Planning médecin: " . count($appointments) . " rendez-vous acceptés");
        
        return $this->json([
            'success' => true,
            'appointments' => $appointments,
            'count' => count($appointments)
        ]);
    }

    /**
     * API: TOUS LES RENDEZ-VOUS (pour debug/admin)
     */
    #[Route('/api/appointments/all', name: 'api_appointments_all', methods: ['GET'])]
    public function getAllAppointments(ConsultationRepository $consultationRepository): JsonResponse
    {
        $consultations = $consultationRepository->findAll();
        
        $upcoming = [];
        $past = [];
        $cancelled = [];
        $pending = [];
        $accepted = [];
        $rejected = [];
        $now = new \DateTime();
        
        foreach ($consultations as $consultation) {
            $appointmentData = $this->formatAppointmentData($consultation);
            
            $date = $consultation->getDateConsultation();
            $time = $consultation->getTimeConsultation();
            $appointmentDateTime = null;
            
            if ($date instanceof \DateTimeInterface) {
                $appointmentDateTime = new \DateTime($date->format('Y-m-d'));
                if ($time instanceof \DateTimeInterface) {
                    $appointmentDateTime->setTime(
                        (int) $time->format('H'),
                        (int) $time->format('i')
                    );
                }
            }

            switch ($consultation->getStatus()) {
                case 'cancelled':
                    $cancelled[] = $appointmentData;
                    break;
                case 'pending':
                    $pending[] = $appointmentData;
                    break;
                case 'accepted':
                    $accepted[] = $appointmentData;
                    if ($appointmentDateTime >= $now) {
                        $upcoming[] = $appointmentData;
                    } else {
                        $past[] = $appointmentData;
                    }
                    break;
                case 'rejected':
                    $rejected[] = $appointmentData;
                    break;
                default:
                    if ($appointmentDateTime >= $now) {
                        $upcoming[] = $appointmentData;
                    } else {
                        $past[] = $appointmentData;
                    }
            }
        }
        
        return $this->json([
            'success' => true,
            'upcoming' => $upcoming,
            'past' => $past,
            'cancelled' => $cancelled,
            'pending' => $pending,
            'accepted' => $accepted,
            'rejected' => $rejected,
            'total' => count($consultations)
        ]);
    }

    // ============== MÉTHODES PRIVÉES ==============

    /**
     * Formater les données d'un rendez-vous pour l'API
     */
    private function formatAppointmentData(Consultation $consultation): array
    {
        $date = $consultation->getDateConsultation();
        $time = $consultation->getTimeConsultation();
        
        return [
            'id' => $consultation->getId(),
            'patientName' => $this->getPatientName($consultation),
            'patientId' => null, // À implémenter avec relation Patient
            'consultationType' => $consultation->getConsultationType(),
            'reason' => $consultation->getReasonForVisit(),
            'date' => $date ? $date->format('Y-m-d') : null,
            'time' => $time ? $time->format('H:i') : null,
            'duration' => $consultation->getDuration(),
            'mode' => $consultation->getAppointmentMode() ?? 'in-person',
            'location' => $consultation->getLocation(),
            'fee' => $consultation->getFee(),
            'status' => $consultation->getStatus(),
            'type' => $this->determineAppointmentType($consultation),
            'notes' => $consultation->getNotes(),
            'symptoms' => $consultation->getSymptomsDescription(),
            'createdAt' => $consultation->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $consultation->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Déterminer le type d'affichage pour le calendrier
     */
    private function determineAppointmentType(Consultation $consultation): string
    {
        // Première visite
        if ($consultation->getConsultationType() === 'first-visit' || 
            $consultation->getConsultationType() === 'new') {
            return 'new';
        }
        
        // Téléconsultation
        $mode = $consultation->getAppointmentMode();
        if ($mode === 'phone' || $mode === 'video' || $mode === 'telemedicine') {
            return 'telemedicine';
        }
        
        // Procédure (plus longue)
        if ($consultation->getConsultationType() === 'procedure' || 
            $consultation->getDuration() > 45) {
            return 'procedure';
        }
        
        // Par défaut: suivi
        return 'follow-up';
    }

    /**
     * Obtenir le nom du patient
     */
    private function getPatientName(Consultation $consultation): string
    {
        // TODO: Remplacer par une vraie relation avec l'entité Patient
        $names = [
            'Marie Dubois', 'Jean Martin', 'Sophie Laurent', 
            'Pierre Durand', 'Claire Bernard', 'Thomas Petit',
            'Emma Richard', 'Lucas Moreau', 'Camille Simon', 'Antoine Blanc'
        ];
        
        $index = $consultation->getId() % count($names);
        return $names[$index];
    }
}