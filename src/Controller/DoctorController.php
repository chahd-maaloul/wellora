<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/doctor')]
class DoctorController extends AbstractController
{
    /**
     * Patient List - Affiche la liste des patients du médecin
     */
    #[Route('/patient-list', name: 'doctor_main_patient_list', methods: ['GET'])]
    public function patientList(): Response
    {
        // Données de patients simulées (à remplacer par des données réelles de la base)
        $patients = [
            [
                'id' => 1,
                'name' => 'Ahmed Ben Ali',
                'email' => 'ahmed.benali@email.com',
                'phone' => '+216 55 123 456',
                'age' => 45,
                'gender' => 'male',
                'lastVisit' => new \DateTime('-5 days'),
                'nextAppointment' => new \DateTime('+3 days'),
                'status' => 'active',
                'avatar' => null,
                'conditions' => ['Diabète Type 2', 'Hypertension'],
            ],
            [
                'id' => 2,
                'name' => 'Fatma Trabelsi',
                'email' => 'fatma.trabelsi@email.com',
                'phone' => '+216 98 765 432',
                'age' => 32,
                'gender' => 'female',
                'lastVisit' => new \DateTime('-2 weeks'),
                'nextAppointment' => null,
                'status' => 'inactive',
                'avatar' => null,
                'conditions' => ['Asthme'],
            ],
            [
                'id' => 3,
                'name' => 'Mohamed Kouki',
                'email' => 'mohamed.kouki@email.com',
                'phone' => '+216 50 111 222',
                'age' => 58,
                'gender' => 'male',
                'lastVisit' => new \DateTime('-1 week'),
                'nextAppointment' => new \DateTime('+1 week'),
                'status' => 'active',
                'avatar' => null,
                'conditions' => ['Cardiopathie', 'Diabète'],
            ],
            [
                'id' => 4,
                'name' => 'Sarra Hachicha',
                'email' => 'sarra.hachicha@email.com',
                'phone' => '+216 52 333 444',
                'age' => 27,
                'gender' => 'female',
                'lastVisit' => new \DateTime('-3 days'),
                'nextAppointment' => new \DateTime('+5 days'),
                'status' => 'active',
                'avatar' => null,
                'conditions' => [],
            ],
            [
                'id' => 5,
                'name' => 'Ali Bouaziz',
                'email' => 'ali.bouaziz@email.com',
                'phone' => '+216 54 555 666',
                'age' => 65,
                'gender' => 'male',
                'lastVisit' => new \DateTime('-1 month'),
                'nextAppointment' => null,
                'status' => 'inactive',
                'avatar' => null,
                'conditions' => ['Arthrite', 'Ostéoporose'],
            ],
        ];

        // Statistiques
        $stats = [
            'totalPatients' => count($patients),
            'activePatients' => count(array_filter($patients, fn($p) => $p['status'] === 'active')),
            'appointmentsToday' => 3,
            'pendingReviews' => 5,
        ];

        return $this->render('doctor/patient-list.html.twig', [
            'patients' => $patients,
            'stats' => $stats,
        ]);
    }

    /**
     * Get Patients - Récupère la liste des patients (AJAX)
     */
    #[Route('/patients', name: 'doctor_get_patients', methods: ['GET'])]
    public function getPatients(Request $request): JsonResponse
    {
        $search = $request->query->get('search', '');
        $status = $request->query->get('status', '');
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        // TODO: Récupérer les données réelles depuis la base
        $patients = [
            [
                'id' => 1,
                'name' => 'Ahmed Ben Ali',
                'email' => 'ahmed.benali@email.com',
                'phone' => '+216 55 123 456',
                'age' => 45,
                'gender' => 'male',
                'lastVisit' => new \DateTime('-5 days'),
                'nextAppointment' => new \DateTime('+3 days'),
                'status' => 'active',
                'avatar' => null,
                'conditions' => ['Diabète Type 2', 'Hypertension'],
            ],
            [
                'id' => 2,
                'name' => 'Fatma Trabelsi',
                'email' => 'fatma.trabelsi@email.com',
                'phone' => '+216 98 765 432',
                'age' => 32,
                'gender' => 'female',
                'lastVisit' => new \DateTime('-2 weeks'),
                'nextAppointment' => null,
                'status' => 'inactive',
                'avatar' => null,
                'conditions' => ['Asthme'],
            ],
            [
                'id' => 3,
                'name' => 'Mohamed Kouki',
                'email' => 'mohamed.kouki@email.com',
                'phone' => '+216 50 111 222',
                'age' => 58,
                'gender' => 'male',
                'lastVisit' => new \DateTime('-1 week'),
                'nextAppointment' => new \DateTime('+1 week'),
                'status' => 'active',
                'avatar' => null,
                'conditions' => ['Cardiopathie', 'Diabète'],
            ],
            [
                'id' => 4,
                'name' => 'Sarra Hachicha',
                'email' => 'sarra.hachicha@email.com',
                'phone' => '+216 52 333 444',
                'age' => 27,
                'gender' => 'female',
                'lastVisit' => new \DateTime('-3 days'),
                'nextAppointment' => new \DateTime('+5 days'),
                'status' => 'active',
                'avatar' => null,
                'conditions' => [],
            ],
            [
                'id' => 5,
                'name' => 'Ali Bouaziz',
                'email' => 'ali.bouaziz@email.com',
                'phone' => '+216 54 555 666',
                'age' => 65,
                'gender' => 'male',
                'lastVisit' => new \DateTime('-1 month'),
                'nextAppointment' => null,
                'status' => 'inactive',
                'avatar' => null,
                'conditions' => ['Arthrite', 'Ostéoporose'],
            ],
        ];

        // Filtrer par recherche
        if ($search) {
            $patients = array_filter($patients, fn($p) => 
                stripos($p['name'], $search) !== false || 
                stripos($p['email'], $search) !== false
            );
        }

        // Filtrer par statut
        if ($status) {
            $patients = array_filter($patients, fn($p) => $p['status'] === $status);
        }

        return $this->json([
            'success' => true,
            'data' => array_values($patients),
            'total' => count($patients),
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    /**
     * Get Patient Details - Récupère les détails d'un patient (AJAX)
     */
    #[Route('/patient/{id}', name: 'doctor_get_patient', methods: ['GET'])]
    public function getPatient(int $id): JsonResponse
    {
        // TODO: Récupérer les données réelles depuis la base
        $patient = [
            'id' => $id,
            'name' => 'Ahmed Ben Ali',
            'email' => 'ahmed.benali@email.com',
            'phone' => '+216 55 123 456',
            'age' => 45,
            'gender' => 'male',
            'dateOfBirth' => '1979-05-15',
            'address' => '123 Rue de la Liberté, Tunis',
            'emergencyContact' => '+216 55 000 111',
            'insurance' => 'COTUNNER',
            'insuranceNumber' => 'AB123456',
            'medicalHistory' => [
                [
                    'date' => '2024-01-15',
                    'type' => 'Consultation',
                    'description' => 'Consultation de suivi',
                    'doctor' => 'Dr. Martin',
                ],
                [
                    'date' => '2023-11-20',
                    'type' => 'Diagnostic',
                    'description' => 'Diagnostic du diabète type 2',
                    'doctor' => 'Dr. Martin',
                ],
            ],
            'currentMedications' => [
                [
                    'name' => 'Metformine 500mg',
                    'dosage' => '2 comprimés par jour',
                    'startDate' => '2023-12-01',
                ],
                [
                    'name' => 'Ramipril 5mg',
                    'dosage' => '1 comprimé par jour',
                    'startDate' => '2024-01-20',
                ],
            ],
            'allergies' => ['Pénicilline'],
            'conditions' => ['Diabète Type 2', 'Hypertension'],
            'lastVisit' => new \DateTime('-5 days'),
            'notes' => 'Patient compliant avec le traitement. À suivre régulièrement.',
        ];

        return $this->json([
            'success' => true,
            'data' => $patient,
        ]);
    }

    /**
     * Add Patient - Ajoute un nouveau patient
     */
    #[Route('/patient', name: 'doctor_add_patient', methods: ['POST'])]
    public function addPatient(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation des données
        if (!$data || !isset($data['name']) || !isset($data['email'])) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides',
            ], 400);
        }

        // TODO: Sauvegarder le patient en base de données

        return $this->json([
            'success' => true,
            'message' => 'Patient ajouté avec succès',
            'data' => [
                'id' => rand(100, 999),
                'name' => $data['name'],
                'email' => $data['email'],
            ],
        ]);
    }

    /**
     * Update Patient - Met à jour les informations d'un patient
     */
    #[Route('/patient/{id}', name: 'doctor_update_patient', methods: ['PUT'])]
    public function updatePatient(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides',
            ], 400);
        }

        // TODO: Mettre à jour le patient en base de données

        return $this->json([
            'success' => true,
            'message' => 'Patient mis à jour avec succès',
            'data' => [
                'id' => $id,
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
            ],
        ]);
    }

    /**
     * Delete Patient - Supprime un patient
     */
    #[Route('/patient/{id}', name: 'doctor_delete_patient', methods: ['DELETE'])]
    public function deletePatient(int $id): JsonResponse
    {
        // TODO: Supprimer le patient de la base de données

        return $this->json([
            'success' => true,
            'message' => 'Patient supprimé avec succès',
        ]);
    }

    /**
     * Patient Chart - Affiche le dossier médical du patient
     */
    #[Route('/patient-chart', name: 'doctor_patient_chart_page', methods: ['GET'])]
    public function patientChart(): Response
    {
        return $this->render('doctor/patient-chart.html.twig');
    }

    /**
     * Patient Queue - Affiche la file d'attente des patients
     */
    #[Route('/patient-queue', name: 'doctor_patient_queue', methods: ['GET'])]
    public function patientQueue(): Response
    {
        // Données simulées pour la file d'attente
        $queue = [
            [
                'id' => 1,
                'name' => 'Ahmed Ben Ali',
                'appointmentTime' => '09:00',
                'type' => 'Consultation',
                'status' => 'waiting',
                'waitTime' => 15,
            ],
            [
                'id' => 2,
                'name' => 'Fatma Trabelsi',
                'appointmentTime' => '09:30',
                'type' => 'Suivi',
                'status' => 'in_consultation',
                'waitTime' => null,
            ],
            [
                'id' => 3,
                'name' => 'Mohamed Kouki',
                'appointmentTime' => '10:00',
                'type' => 'Nouvelle consultation',
                'status' => 'waiting',
                'waitTime' => 5,
            ],
        ];

        $stats = [
            'waiting' => 2,
            'inConsultation' => 1,
            'completed' => 5,
            'next' => '09:00',
        ];

        return $this->render('doctor/patient-queue.html.twig', [
            'queue' => $queue,
            'stats' => $stats,
        ]);
    }

    /**
     * Clinical Notes - Affiche les notes cliniques
     */
    #[Route('/clinical-notes', name: 'doctor_clinical_notes_page', methods: ['GET'])]
    public function clinicalNotes(): Response
    {
        return $this->render('doctor/clinical-notes.html.twig');
    }

    /**
     * Communication - Affiche la page de communication avec les patients
     */
    #[Route('/communication', name: 'doctor_communication_page', methods: ['GET'])]
    public function communication(): Response
    {
        // Données simulées pour les messages
        $conversations = [
            [
                'id' => 1,
                'patientName' => 'Ahmed Ben Ali',
                'lastMessage' => 'Merci pour la consultation',
                'time' => '10:30',
                'unread' => true,
            ],
            [
                'id' => 2,
                'patientName' => 'Fatma Trabelsi',
                'lastMessage' => 'Question sur le traitement',
                'time' => '09:15',
                'unread' => false,
            ],
        ];

        return $this->render('doctor/communication.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Availability Settings - Affiche les paramètres de disponibilité
     */
    #[Route('/availability-settings', name: 'doctor_availability_settings', methods: ['GET'])]
    public function availabilitySettings(): Response
    {
        // Données simulées pour les disponibilités
        $availability = [
            'monday' => ['09:00', '12:00', '14:00', '17:00'],
            'tuesday' => ['09:00', '12:00', '14:00', '17:00'],
            'wednesday' => ['09:00', '12:00'],
            'thursday' => ['09:00', '12:00', '14:00', '17:00'],
            'friday' => ['09:00', '12:00', '14:00', '16:00'],
        ];

        return $this->render('doctor/availability-settings.html.twig', [
            'availability' => $availability,
        ]);
    }
}
