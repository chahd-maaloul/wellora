<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Ordonnance;
use App\Entity\Examens;
use App\Entity\Patient;
use App\Entity\Consultation;
use App\Form\SoapType;
use App\Form\OrdonnanceType;
use App\Form\ExamenType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/health/doctor')]
class DoctorController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /*******************************************************************
     * DASHBOARD ET PAGES PRINCIPALES
     *******************************************************************/

    /**
     * Dashboard du médecin
     */
    #[Route('/dashboard', name: 'doctor_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $patientCount = $this->em->getRepository(Patient::class)->count([]);
        $consultationCount = $this->em->getRepository(Consultation::class)->count([]);
        $todayConsultations = $this->em->getRepository(Consultation::class)->createQueryBuilder('c')
            ->where('c.date_consultation >= :today_start')
            ->andWhere('c.date_consultation < :today_end')
            ->setParameter('today_start', (new \DateTime())->setTime(0, 0, 0))
            ->setParameter('today_end', (new \DateTime())->setTime(23, 59, 59))
            ->getQuery()
            ->getResult();
        
        $nextAppointments = $this->em->getRepository(Consultation::class)->createQueryBuilder('c')
            ->where('c.date_consultation >= :now')
            ->andWhere('c.status = :scheduled')
            ->setParameter('now', new \DateTime())
            ->setParameter('scheduled', 'scheduled')
            ->orderBy('c.date_consultation', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $this->render('doctor/dashboard.html.twig', [
            'patientCount' => $patientCount,
            'consultationCount' => $consultationCount,
            'todayConsultations' => count($todayConsultations),
            'nextAppointments' => $nextAppointments,
        ]);
    }

    /**
     * Patient List - Affiche la liste des consultations
     */
    #[Route('/patients', name: 'doctor_patients', methods: ['GET'])]
    public function patientList(): Response
    {
        $consultations = $this->em->getRepository(Consultation::class)
            ->searchForPatientList('', '', '', '', '', 'lastVisit', 'DESC', 1, 100);

        $consultationsArray = [];
        foreach ($consultations as $consultation) {
            $consultationsArray[] = $this->formatConsultationForList($consultation);
        }

        return $this->render('doctor/patient-list.html.twig', [
            'consultationsData' => $consultationsArray,
        ]);
    }

    /*******************************************************************
     * GESTION DES CONSULTATIONS (API)
     *******************************************************************/

    /**
     * Get Consultations - R??cup??re la liste des consultations (AJAX)
     * NOTE: Version temporaire sans entit?? Patient - ?? mettre ?? jour quand Patient sera pr??t
     */
    #[Route('/api/consultations', name: 'doctor_get_consultations', methods: ['GET'])]
    public function getConsultations(Request $request): JsonResponse
    {
        try {
            $search = $request->query->get('search', '');
            $status = $request->query->get('status', '');
            $condition = $request->query->get('condition', '');
            $dateFrom = $request->query->get('dateFrom', '');
            $dateTo = $request->query->get('dateTo', '');
            $sortBy = $request->query->get('sortBy', 'lastVisit');
            $sortDir = $request->query->get('sortDir', 'DESC');
            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', 100);

            $consultationRepository = $this->em->getRepository(Consultation::class);
            $consultations = $consultationRepository->searchForPatientList(
                $search,
                $status,
                $condition,
                $dateFrom,
                $dateTo,
                $sortBy,
                $sortDir,
                $page,
                $limit
            );

            $consultationsArray = [];
            foreach ($consultations as $consultation) {
                try {
                    $consultationsArray[] = $this->formatConsultationForList($consultation);
                } catch (\Exception $e) {
                    error_log('Erreur traitement consultation ' . $consultation->getId() . ': ' . $e->getMessage());
                    continue;
                }
            }

            return $this->json($consultationsArray);

        } catch (\Exception $e) {
            error_log('Erreur getConsultations: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            return $this->json([
                'error' => true,
                'message' => 'Erreur lors de la r??cup??ration des consultations',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper - Calcule le score de sante
     */
    private function calculateHealthScore(Consultation $consultation): int
    {
        $score = 85;
        
        $vitals = $consultation->getVitals();
        
        if (is_array($vitals)) {
            if (isset($vitals['bloodPressureSystolic']) && isset($vitals['bloodPressureDiastolic'])) {
                $systolic = (int) $vitals['bloodPressureSystolic'];
                $diastolic = (int) $vitals['bloodPressureDiastolic'];
                
                if ($systolic > 140 || $diastolic > 90) {
                    $score -= 15;
                } elseif ($systolic < 90 || $diastolic < 60) {
                    $score -= 10;
                }
            }
            
            if (isset($vitals['temperature'])) {
                $temp = (float) $vitals['temperature'];
                if ($temp > 38 || $temp < 36) {
                    $score -= 10;
                }
            }
            
            if (isset($vitals['heartRate'])) {
                $heartRate = (int) $vitals['heartRate'];
                if ($heartRate > 100 || $heartRate < 60) {
                    $score -= 5;
                }
            }
            
            if (isset($vitals['oxygenSaturation'])) {
                $o2 = (int) $vitals['oxygenSaturation'];
                if ($o2 < 95) {
                    $score -= 15;
                }
            }
        }
        
        return max(0, min(100, $score));
    }

    private function formatConsultationForList(Consultation $consultation): array
    {
        // TODO: Remplacer par les vraies donnÃ©es patient quand l'entitÃ© sera prÃªte
        $patientName = 'Consultation ' . $consultation->getId();
        $age = 35;

        $consultStatus = 'stable';
        $assessment = strtolower($consultation->getAssessment() ?? '');

        if (strpos($assessment, 'critique') !== false || strpos($assessment, 'urgent') !== false) {
            $consultStatus = 'critical';
        } elseif (strpos($assessment, 'suivi') !== false || strpos($assessment, 'contrÃ´le') !== false) {
            $consultStatus = 'follow-up';
        } elseif ($consultation->getStatus() === 'completed') {
            $consultStatus = 'active';
        }

        $healthScore = $this->calculateHealthScore($consultation);

        $conditions = [];
        $diagnoses = $consultation->getDiagnoses();

        if (is_array($diagnoses) && !empty($diagnoses)) {
            $conditions = array_slice($diagnoses, 0, 3);
        }

        $consultationDate = '';
        $consultationTime = '';
        if ($consultation->getDateConsultation()) {
            $consultationDate = $consultation->getDateConsultation()->format('d/m/Y');
        }
        if ($consultation->getTimeConsultation()) {
            $consultationTime = $consultation->getTimeConsultation()->format('H:i');
        }

        return [
            'id' => $consultation->getId(),
            'consultationId' => $consultation->getId(),
            'patientId' => $consultation->getId(), // TODO: Utiliser le vrai ID patient
            'name' => $patientName, // TODO: Utiliser le vrai nom du patient
            'email' => '', // TODO: Utiliser le vrai email
            'phone' => '', // TODO: Utiliser le vrai tÃ©lÃ©phone
            'age' => $age, // TODO: Calculer le vrai Ã¢ge
            'gender' => 'M', // TODO: Utiliser le vrai sexe
            'avatar' => '/images/avatars/default.png',
            'fileNumber' => 'CONS-' . str_pad($consultation->getId(), 4, '0', STR_PAD_LEFT),
            'status' => $consultStatus,
            'healthScore' => $healthScore,
            'conditions' => $conditions,
            'lastVisitDate' => $consultationDate,
            'lastVisitTime' => $consultationTime,
            'reasonForVisit' => $consultation->getReasonForVisit(),
        ];
    }

    /**
     * Get Patients - Récupère la liste des patients (AJAX)
     */
    #[Route('/api/patients', name: 'doctor_get_patients', methods: ['GET'])]
    public function getPatients(Request $request): JsonResponse
    {
        try {
            $search = $request->query->get('search', '');
            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', 100);

            $patientRepository = $this->em->getRepository(Patient::class);
            $qb = $patientRepository->createQueryBuilder('p');
            
            if ($search) {
                $qb->andWhere('p.nom LIKE :search OR p.prenom LIKE :search OR p.email LIKE :search')
                   ->setParameter('search', '%' . $search . '%');
            }
            
            $qb->setFirstResult(($page - 1) * $limit)
               ->setMaxResults($limit);
               
            $patients = $qb->getQuery()->getResult();
            
            $patientsArray = [];
            foreach ($patients as $patient) {
                try {
                    $age = null;
                    if ($patient->getDateNaissance()) {
                        $age = $patient->getDateNaissance()->diff(new \DateTime())->y;
                    }

                    $status = 'active';
                    if ($age && $age > 70) {
                        $status = 'follow-up';
                    }

                    $healthScore = rand(60, 95);

                    $conditions = [];
                    if ($patient->getAntecedentsMedicaux()) {
                        $conditions = array_slice(explode(',', $patient->getAntecedentsMedicaux()), 0, 3);
                    }

                    $lastVisitDate = '';
                    $lastVisitTime = '';
                    if ($patient->getDateDerniereVisite()) {
                        $lastVisitDate = $patient->getDateDerniereVisite()->format('d/m/Y');
                        $lastVisitTime = $patient->getDateDerniereVisite()->format('H:i');
                    }

                    $patientsArray[] = [
                        'id' => $patient->getId(),
                        'name' => trim($patient->getNom() . ' ' . $patient->getPrenom()),
                        'email' => $patient->getEmail(),
                        'phone' => $patient->getTelephone(),
                        'age' => $age,
                        'gender' => $patient->getSexe(),
                        'avatar' => '/images/avatars/default.png',
                        'fileNumber' => '2024-' . str_pad($patient->getId(), 3, '0', STR_PAD_LEFT),
                        'status' => $status,
                        'healthScore' => $healthScore,
                        'conditions' => $conditions,
                        'lastVisitDate' => $lastVisitDate,
                        'lastVisitTime' => $lastVisitTime,
                    ];
                } catch (\Exception $e) {
                    continue;
                }
            }

            return $this->json($patientsArray);
            
        } catch (\Exception $e) {
            error_log('Erreur getPatients: ' . $e->getMessage());
            
            return $this->json([
                'error' => true,
                'message' => 'Erreur lors de la récupération des patients',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Patient Details
     */
    #[Route('/api/patient/{id}', name: 'doctor_get_patient', methods: ['GET'])]
    public function getPatient(int $id): JsonResponse
    {
        $patient = $this->em->getRepository(Patient::class)->find($id);
        
        if (!$patient) {
            return $this->json([
                'success' => false,
                'message' => 'Patient non trouvé',
            ], 404);
        }

        $consultations = $this->em->getRepository(Consultation::class)->findBy([], ['date_consultation' => 'DESC'], 10);
        
        $medicalHistory = [];
        foreach ($consultations as $consultation) {
            $medicalHistory[] = [
                'date' => $consultation->getDateConsultation()->format('Y-m-d'),
                'type' => 'Consultation',
                'description' => $consultation->getReasonForVisit(),
                'doctor' => 'Dr. ' . ($this->getUser() ? $this->getUser()->getNom() : 'Médecin'),
            ];
        }

        $ordonnances = $this->em->getRepository(Ordonnance::class)->createQueryBuilder('o')
            ->join('o.id_consultation', 'c')
            ->orderBy('o.date_ordonnance', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
            
        $currentMedications = [];
        foreach ($ordonnances as $ordonnance) {
            $currentMedications[] = [
                'name' => $ordonnance->getMedicament(),
                'dosage' => $ordonnance->getDosage(),
                'startDate' => $ordonnance->getDateOrdonnance()->format('Y-m-d'),
            ];
        }

        $patientData = [
            'id' => $patient->getId(),
            'name' => $patient->getNom() . ' ' . $patient->getPrenom(),
            'email' => $patient->getEmail(),
            'phone' => $patient->getTelephone(),
            'age' => $patient->getDateNaissance() ? $patient->getDateNaissance()->diff(new \DateTime())->y : null,
            'gender' => $patient->getSexe(),
            'dateOfBirth' => $patient->getDateNaissance() ? $patient->getDateNaissance()->format('Y-m-d') : null,
            'address' => $patient->getAdresse(),
            'emergencyContact' => $patient->getContactUrgence(),
            'insurance' => $patient->getAssurance(),
            'insuranceNumber' => $patient->getNumeroAssurance(),
            'medicalHistory' => $medicalHistory,
            'currentMedications' => $currentMedications,
            'allergies' => $patient->getAllergies() ? explode(',', $patient->getAllergies()) : [],
            'conditions' => $patient->getAntecedentsMedicaux() ? explode(',', $patient->getAntecedentsMedicaux()) : [],
            'lastVisit' => $patient->getDateDerniereVisite(),
            'notes' => $patient->getNotes(),
        ];

        return $this->json([
            'success' => true,
            'data' => $patientData,
        ]);
    }

    /**
     * Add Patient
     */
    #[Route('/api/patient', name: 'doctor_add_patient', methods: ['POST'])]
    public function addPatient(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['name']) || !isset($data['email'])) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides',
            ], 400);
        }

        $patient = new Patient();
        
        $nameParts = explode(' ', $data['name'], 2);
        $patient->setNom($nameParts[0] ?? '');
        $patient->setPrenom($nameParts[1] ?? '');
        
        $patient->setEmail($data['email']);
        $patient->setTelephone($data['phone'] ?? '');
        $patient->setSexe($data['gender'] ?? '');
        
        if (isset($data['dateOfBirth'])) {
            $patient->setDateNaissance(new \DateTime($data['dateOfBirth']));
        }
        
        $patient->setAdresse($data['address'] ?? '');
        $patient->setContactUrgence($data['emergencyContact'] ?? '');
        $patient->setAssurance($data['insurance'] ?? '');
        $patient->setNumeroAssurance($data['insuranceNumber'] ?? '');
        $patient->setAllergies($data['allergies'] ?? '');
        $patient->setAntecedentsMedicaux($data['conditions'] ?? '');
        $patient->setNotes($data['notes'] ?? '');
        $patient->setDateInscription(new \DateTime());
        $patient->setDateDerniereVisite(new \DateTime());

        $this->em->persist($patient);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Patient ajouté avec succès',
            'data' => [
                'id' => $patient->getId(),
                'name' => $patient->getNom() . ' ' . $patient->getPrenom(),
                'email' => $patient->getEmail(),
            ],
        ]);
    }

    /**
     * Update Patient
     */
    #[Route('/api/patient/{id}', name: 'doctor_update_patient', methods: ['PUT'])]
    public function updatePatient(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides',
            ], 400);
        }

        $patient = $this->em->getRepository(Patient::class)->find($id);
        
        if (!$patient) {
            return $this->json([
                'success' => false,
                'message' => 'Patient non trouvé',
            ], 404);
        }

        if (isset($data['name'])) {
            $nameParts = explode(' ', $data['name'], 2);
            $patient->setNom($nameParts[0] ?? '');
            $patient->setPrenom($nameParts[1] ?? '');
        }
        
        if (isset($data['email'])) {
            $patient->setEmail($data['email']);
        }
        
        if (isset($data['phone'])) {
            $patient->setTelephone($data['phone']);
        }
        
        if (isset($data['gender'])) {
            $patient->setSexe($data['gender']);
        }
        
        if (isset($data['dateOfBirth'])) {
            $patient->setDateNaissance(new \DateTime($data['dateOfBirth']));
        }
        
        if (isset($data['address'])) {
            $patient->setAdresse($data['address']);
        }
        
        if (isset($data['emergencyContact'])) {
            $patient->setContactUrgence($data['emergencyContact']);
        }
        
        if (isset($data['insurance'])) {
            $patient->setAssurance($data['insurance']);
        }
        
        if (isset($data['insuranceNumber'])) {
            $patient->setNumeroAssurance($data['insuranceNumber']);
        }
        
        if (isset($data['allergies'])) {
            $patient->setAllergies(is_array($data['allergies']) ? implode(',', $data['allergies']) : $data['allergies']);
        }
        
        if (isset($data['conditions'])) {
            $patient->setAntecedentsMedicaux(is_array($data['conditions']) ? implode(',', $data['conditions']) : $data['conditions']);
        }
        
        if (isset($data['notes'])) {
            $patient->setNotes($data['notes']);
        }

        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Patient mis à jour avec succès',
            'data' => [
                'id' => $patient->getId(),
                'name' => $patient->getNom() . ' ' . $patient->getPrenom(),
                'email' => $patient->getEmail(),
            ],
        ]);
    }

    /**
     * Delete Patient
     */
    #[Route('/api/patient/{id}', name: 'doctor_delete_patient', methods: ['DELETE'])]
    public function deletePatient(int $id): JsonResponse
    {
        $patient = $this->em->getRepository(Patient::class)->find($id);
        
        if (!$patient) {
            return $this->json([
                'success' => false,
                'message' => 'Patient non trouvé',
            ], 404);
        }

        $consultations = $this->em->getRepository(Consultation::class)->findBy([], [], 1);
        
        if (count($consultations) > 0) {
            return $this->json([
                'success' => false,
                'message' => 'Impossible de supprimer le patient car il a des consultations associées',
            ], 400);
        }

        $this->em->remove($patient);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Patient supprimé avec succès',
        ]);
    }

    /*******************************************************************
     * GESTION DES NOTES CLINIQUES
     *******************************************************************/

    /**
     * Clinical Notes - Page des notes cliniques
     */
    #[Route('/clinical-notes', name: 'doctor_clinical_notes', methods: ['GET'])]
    public function clinicalNotes(Request $request): Response
    {
        $consultationId = $request->query->get('consultationId');
        $patientId = $request->query->get('patientId');
        
        $patient = null;
        $currentNoteData = null;
        $consultation = null;

        if ($consultationId) {
            $consultation = $this->em->getRepository(Consultation::class)->find($consultationId);
            
            if (!$consultation) {
                $this->addFlash('error', 'Consultation non trouvée');
                return $this->redirectToRoute('doctor_patients');
            }
            
            if (method_exists($consultation, 'getIdPatient')) {
                $patient = $consultation->getIdPatient();
            } else {
                $patient = null;
            }
            
            $currentNoteData = [
                'id' => $consultation->getId(),
                'chiefComplaint' => $consultation->getReasonForVisit(),
                'subjective' => $consultation->getSubjective(),
                'objective' => $consultation->getObjective(),
                'assessment' => $consultation->getAssessment(),
                'plan' => $consultation->getPlan(),
                'vitals' => $consultation->getVitals(),
                'date' => $consultation->getDateConsultation() ? $consultation->getDateConsultation()->format('Y-m-d') : '',
                'diagnoses' => $consultation->getDiagnoses() ?? [],
                'followUp' => $consultation->getFollowUp() ?? [],
                'medications' => $this->getMedicationsArray($consultation),
                'labTests' => $this->getExamsArray($consultation)
            ];
        } 
        elseif ($patientId) {
            $patient = $this->em->getRepository(Patient::class)->find($patientId);
            
            if (!$patient) {
                $this->addFlash('error', 'Patient non trouvé');
                return $this->redirectToRoute('doctor_patients');
            }
        }
        else {
            $this->addFlash('error', 'Veuillez sélectionner un patient ou une consultation');
            return $this->redirectToRoute('doctor_patients');
        }

        $history = [];
        if ($patient) {
            $history = $this->em->getRepository(Consultation::class)->findBy(
                ['id_patient' => $patient],
                ['date_consultation' => 'DESC']
            );
        } elseif ($consultation) {
            $history = [$consultation];
        }
        
        $historyData = [];
        foreach ($history as $item) {
            $vitals = $item->getVitals();
            $vitals = is_array($vitals) ? $vitals : [];
            
            $historyData[] = [
                'id' => $item->getId(),
                'date' => $item->getDateConsultation() ? $item->getDateConsultation()->format('d/m/Y') : '',
                'title' => 'Note SOAP',
                'summary' => $item->getReasonForVisit() ?? '',
                'data' => [
                    'consultation' => [
                        'chiefComplaint' => $item->getReasonForVisit() ?? '',
                        'subjective' => $item->getSubjective() ?? '',
                        'objective' => $item->getObjective() ?? '',
                        'assessment' => $item->getAssessment() ?? '',
                        'plan' => $item->getPlan() ?? '',
                        'vitals' => [
                            'bloodPressure' => [
                                'systolic' => $vitals['bloodPressure']['systolic'] ?? $vitals['bloodPressureSystolic'] ?? '',
                                'diastolic' => $vitals['bloodPressure']['diastolic'] ?? $vitals['bloodPressureDiastolic'] ?? '',
                            ],
                            'pulse' => $vitals['pulse'] ?? $vitals['heartRate'] ?? '',
                            'temperature' => $vitals['temperature'] ?? '',
                            'spo2' => $vitals['spo2'] ?? $vitals['oxygenSaturation'] ?? '',
                        ],
                    ],
                    'diagnoses' => $item->getDiagnoses() ?? [],
                    'medications' => $this->getMedicationsArray($item),
                    'labTests' => $this->getExamsArray($item),
                    'followUp' => $item->getFollowUp() ?? [],
                ],
            ];
        }

        return $this->render('doctor/clinical-notes.html.twig', [
            'patient' => $patient,
            'history' => $history,
            'historyData' => $historyData,
            'currentNoteData' => $currentNoteData,
            'currentConsultationId' => $consultationId
        ]);
    }

    /**
     * Helper - Récupérer les médicaments
     */
    private function getMedicationsArray(Consultation $consultation): array
    {
        $ordonnances = $this->em->getRepository(Ordonnance::class)->findBy(['id_consultation' => $consultation]);
        $medications = [];
        
        foreach ($ordonnances as $ord) {
            $medications[] = [
                'name' => $ord->getMedicament(),
                'dosage' => $ord->getDosage(),
                'form' => $ord->getForme(),
                'frequency' => $ord->getFrequency(),
                'duration' => $ord->getDureeTraitement(),
                'instructions' => $ord->getInstructions()
            ];
        }
        
        return $medications;
    }

    /**
     * Helper - Récupérer les examens
     */
    private function getExamsArray(Consultation $consultation): array
    {
        $examens = $this->em->getRepository(Examens::class)->findBy(['id_consultation' => $consultation]);
        $exams = [];
        
        foreach ($examens as $exam) {
            $exams[] = [
                'type' => $exam->getTypeExamen(),
                'name' => $exam->getNomExamen(),
                'result' => $exam->getResultat(),
                'status' => $exam->getStatus(),
                'notes' => $exam->getNotes()
            ];
        }
        
        return $exams;
    }

    /**
     * Save Clinical Note
     */
    #[Route('/api/clinical-notes/save', name: 'doctor_save_clinical_note', methods: ['POST'])]
    public function saveClinicalNote(Request $request): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['consultation'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Données invalides: consultation manquante'
                ], 400);
            }
            
            $consultation = new Consultation();
            $consultationData = $data['consultation'];
            
            if (empty($consultationData['chiefComplaint'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Le motif de consultation est obligatoire'
                ], 400);
            }
            
            $consultation->setReasonForVisit($consultationData['chiefComplaint']);
            $consultation->setSubjective($consultationData['subjective'] ?? '');
            $consultation->setObjective($consultationData['objective'] ?? '');
            $consultation->setAssessment($consultationData['assessment'] ?? '');
            $consultation->setPlan($consultationData['plan'] ?? '');
            
            if (isset($consultationData['vitals']) && is_array($consultationData['vitals'])) {
                $consultation->setVitals($consultationData['vitals']);
            }
            
            $consultation->setDateConsultation(new \DateTime());
            $consultation->setTimeConsultation(new \DateTime());
            $consultation->setConsultationType('soap');
            $consultation->setStatus('completed');
            $consultation->setAppointmentMode('in_person');
            $consultation->setDuration(30);
            $consultation->setFee(0);
            
            $symptoms = ($consultationData['subjective'] ?? '') . "\n" . ($consultationData['objective'] ?? '');
            $consultation->setSymptomsDescription(substr($symptoms, 0, 500));
            $consultation->setLocation('Cabinet');
            $consultation->setNotes($consultationData['plan'] ?? '');
            
            $this->em->persist($consultation);
            
            if (isset($data['diagnoses']) && is_array($data['diagnoses'])) {
                $consultation->setDiagnoses($data['diagnoses']);
            }
            
            if (isset($data['medications']) && is_array($data['medications'])) {
                foreach ($data['medications'] as $medData) {
                    $ordonnance = new Ordonnance();
                    $ordonnance->setIdConsultation($consultation);
                    $ordonnance->setMedicament($medData['name'] ?? 'Médicament');
                    $ordonnance->setDosage($medData['dosage'] ?? '');
                    $ordonnance->setForme($medData['form'] ?? 'comprimé');
                    $ordonnance->setFrequency($medData['frequency'] ?? '1x/jour');
                    $ordonnance->setDureeTraitement($medData['duration'] ?? '7 jours');
                    $ordonnance->setInstructions($medData['instructions'] ?? '');
                    $ordonnance->setDiagnosisCode($medData['diagnosisCode'] ?? $medData['associatedDiagnosis'] ?? '');
                    $ordonnance->setDateOrdonnance(new \DateTime());
                    
                    $this->em->persist($ordonnance);
                }
            }
            
            if (isset($data['labTests']) && is_array($data['labTests'])) {
                foreach ($data['labTests'] as $examData) {
                    $examen = new Examens();
                    $examen->setIdConsultation($consultation);
                    $examen->setTypeExamen($examData['type'] ?? 'laboratoire');
                    $examen->setNomExamen($examData['name'] ?? 'Examen');
                    $examen->setDateExamen(new \DateTime());
                    $examen->setResultat($examData['result'] ?? '');
                    $examen->setStatus($examData['status'] ?? 'prescrit');
                    $examen->setNotes($examData['notes'] ?? '');
                    
                    $this->em->persist($examen);
                }
            }
            
            if (isset($data['followUp']) && is_array($data['followUp'])) {
                $consultation->setFollowUp($data['followUp']);
            }
            
            $this->em->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Note clinique sauvegardée avec succès',
                'consultationId' => $consultation->getId(),
                'date' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Update Clinical Note (modify existing)
     */
    #[Route('/api/clinical-notes/update', name: 'doctor_update_clinical_note', methods: ['PUT'])]
    public function updateClinicalNote(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data || !isset($data['consultation'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Donn??es invalides: consultation manquante'
                ], 400);
            }

            $noteId = $data['noteId'] ?? $data['consultationId'] ?? null;
            if (!$noteId) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Identifiant de note manquant'
                ], 400);
            }

            $consultation = $this->em->getRepository(Consultation::class)->find($noteId);
            if (!$consultation) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Note non trouv??e'
                ], 404);
            }

            $consultationData = $data['consultation'];

            if (array_key_exists('chiefComplaint', $consultationData)
                && trim((string) $consultationData['chiefComplaint']) === '') {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Le motif de consultation est obligatoire'
                ], 400);
            }

            if (array_key_exists('chiefComplaint', $consultationData)) {
                $consultation->setReasonForVisit($consultationData['chiefComplaint']);
            }

            $subjective = $consultation->getSubjective() ?? '';
            $objective = $consultation->getObjective() ?? '';
            $updateSymptoms = false;

            if (array_key_exists('subjective', $consultationData)) {
                $subjective = $consultationData['subjective'] ?? '';
                $consultation->setSubjective($subjective);
                $updateSymptoms = true;
            }

            if (array_key_exists('objective', $consultationData)) {
                $objective = $consultationData['objective'] ?? '';
                $consultation->setObjective($objective);
                $updateSymptoms = true;
            }

            if (array_key_exists('assessment', $consultationData)) {
                $consultation->setAssessment($consultationData['assessment'] ?? '');
            }

            if (array_key_exists('plan', $consultationData)) {
                $consultation->setPlan($consultationData['plan'] ?? '');
                $consultation->setNotes($consultationData['plan'] ?? '');
            }

            if (isset($consultationData['vitals']) && is_array($consultationData['vitals'])) {
                $consultation->setVitals($consultationData['vitals']);
            }

            if ($updateSymptoms) {
                $symptoms = $subjective . "\n" . $objective;
                $consultation->setSymptomsDescription(substr($symptoms, 0, 500));
            }

            if (array_key_exists('location', $consultationData)) {
                $consultation->setLocation($consultationData['location'] ?? 'Cabinet');
            }

            if (array_key_exists('diagnoses', $data) && is_array($data['diagnoses'])) {
                $consultation->setDiagnoses($data['diagnoses']);
            }

            if (array_key_exists('followUp', $data) && is_array($data['followUp'])) {
                $consultation->setFollowUp($data['followUp']);
            }

            if (array_key_exists('medications', $data) && is_array($data['medications']) && count($data['medications']) > 0) {
                $existingOrdonnances = $this->em->getRepository(Ordonnance::class)->findBy(['id_consultation' => $consultation]);
                foreach ($existingOrdonnances as $ordonnance) {
                    $this->em->remove($ordonnance);
                }
                foreach ($data['medications'] as $medData) {
                    $ordonnance = new Ordonnance();
                    $ordonnance->setIdConsultation($consultation);
                    $ordonnance->setMedicament($medData['name'] ?? 'M??dicament');
                    $ordonnance->setDosage($medData['dosage'] ?? '');
                    $ordonnance->setForme($medData['form'] ?? 'comprim??');
                    $ordonnance->setFrequency($medData['frequency'] ?? '1x/jour');
                    $ordonnance->setDureeTraitement($medData['duration'] ?? '7 jours');
                    $ordonnance->setInstructions($medData['instructions'] ?? '');
                    $ordonnance->setDiagnosisCode($medData['diagnosisCode'] ?? $medData['associatedDiagnosis'] ?? '');
                    $ordonnance->setDateOrdonnance(new \DateTime());

                    $this->em->persist($ordonnance);
                }
            }

            if (array_key_exists('labTests', $data) && is_array($data['labTests']) && count($data['labTests']) > 0) {
                $existingExams = $this->em->getRepository(Examens::class)->findBy(['id_consultation' => $consultation]);
                foreach ($existingExams as $examen) {
                    $this->em->remove($examen);
                }
                foreach ($data['labTests'] as $examData) {
                    $examen = new Examens();
                    $examen->setIdConsultation($consultation);
                    $examen->setTypeExamen($examData['type'] ?? 'laboratoire');
                    $examen->setNomExamen($examData['name'] ?? 'Examen');
                    $examen->setDateExamen(new \DateTime());
                    $examen->setResultat($examData['result'] ?? '');
                    $examen->setStatus($examData['status'] ?? 'prescrit');
                    $examen->setNotes($examData['notes'] ?? '');

                    $this->em->persist($examen);
                }
            }

            $this->em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Note clinique modifi??e avec succ??s',
                'consultationId' => $consultation->getId(),
                'date' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Clinical Note
     */
    #[Route('/api/clinical-notes/delete/{id}', name: 'api_clinical_note_delete', methods: ['DELETE'])]
    public function apiDeleteClinicalNote(int $id): JsonResponse
    {
        try {
            $consultation = $this->em->getRepository(Consultation::class)->find($id);
            if (!$consultation) {
                return new JsonResponse(['success' => false, 'message' => 'Note non trouvée'], 404);
            }

            $ordonnances = $this->em->getRepository(Ordonnance::class)->findBy(['id_consultation' => $consultation]);
            foreach ($ordonnances as $ordonnance) {
                $this->em->remove($ordonnance);
            }
            
            $examens = $this->em->getRepository(Examens::class)->findBy(['id_consultation' => $consultation]);
            foreach ($examens as $examen) {
                $this->em->remove($examen);
            }
            
            $this->em->remove($consultation);
            $this->em->flush();

            return new JsonResponse(['success' => true, 'message' => 'Note supprimée avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /*******************************************************************
     * GESTION DES CONSULTATIONS (CRUD COMPLET)
     *******************************************************************/

    #[Route('/consultation', name: 'doctor_consultation_list', methods: ['GET'])]
    public function consultationList(): Response
    {
        $consultations = $this->em->getRepository(Consultation::class)->findBy([], ['date_consultation' => 'DESC']);
        
        return $this->render('doctor/consultation/list.html.twig', [
            'consultations' => $consultations,
        ]);
    }

    #[Route('/consultation/new', name: 'doctor_consultation_new', methods: ['GET', 'POST'])]
    public function newConsultation(Request $request): Response
    {
        $consultation = new Consultation();
        $form = $this->createForm(SoapType::class, $consultation);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$consultation->getDateConsultation()) {
                $consultation->setDateConsultation(new \DateTime());
            }
            if (!$consultation->getTimeConsultation()) {
                $consultation->setTimeConsultation(new \DateTime());
            }
            if (!$consultation->getConsultationType()) {
                $consultation->setConsultationType('soap');
            }
            if (!$consultation->getStatus()) {
                $consultation->setStatus('completed');
            }
            if (!$consultation->getAppointmentMode()) {
                $consultation->setAppointmentMode('in_person');
            }
            if (!$consultation->getDuration()) {
                $consultation->setDuration(30);
            }
            if (!$consultation->getFee()) {
                $consultation->setFee(0);
            }
            
            $this->em->persist($consultation);
            $this->em->flush();
            
            $this->addFlash('success', 'Consultation créée avec succès');
            return $this->redirectToRoute('doctor_consultation_list');
        }
        
        return $this->render('doctor/consultation/new.html.twig', [
            'form' => $form->createView(),
            'consultation' => $consultation,
        ]);
    }

    #[Route('/consultation/{id}/edit', name: 'doctor_consultation_edit', methods: ['GET', 'POST'])]
    public function editConsultation(Request $request, Consultation $consultation): Response
    {
        $form = $this->createForm(SoapType::class, $consultation);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $consultation->setUpdatedAt(new \DateTime());
            
            $this->em->flush();
            
            $this->addFlash('success', 'Consultation modifiée avec succès');
            return $this->redirectToRoute('doctor_consultation_list');
        }
        
        return $this->render('doctor/consultation/edit.html.twig', [
            'form' => $form->createView(),
            'consultation' => $consultation,
        ]);
    }

    /**
     * Delete Consultation (API)
     */
    #[Route('/api/consultation/{id}', name: 'doctor_api_delete_consultation', methods: ['DELETE'])]
    public function deleteConsultationApi(int $id): JsonResponse
    {
        try {
            $consultation = $this->em->getRepository(Consultation::class)->find($id);
            
            if (!$consultation) {
                return $this->json([
                    'success' => false,
                    'message' => 'Consultation non trouvée',
                ], 404);
            }

            // Delete related ordonnances
            $ordonnances = $this->em->getRepository(Ordonnance::class)->findBy(['id_consultation' => $consultation]);
            foreach ($ordonnances as $ordonnance) {
                $this->em->remove($ordonnance);
            }
            
            // Delete related examens
            $examens = $this->em->getRepository(Examens::class)->findBy(['id_consultation' => $consultation]);
            foreach ($examens as $examen) {
                $this->em->remove($examen);
            }
            
            // Delete consultation
            $this->em->remove($consultation);
            $this->em->flush();
            
            return $this->json([
                'success' => true,
                'message' => 'Consultation supprimée avec succès',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la consultation',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/consultation/{id}/delete', name: 'doctor_consultation_delete', methods: ['POST'])]
    public function deleteConsultation(Request $request, Consultation $consultation): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete' . $consultation->getId(), $request->request->get('_token'))) {
            $ordonnances = $this->em->getRepository(Ordonnance::class)->findBy(['id_consultation' => $consultation]);
            foreach ($ordonnances as $ordonnance) {
                $this->em->remove($ordonnance);
            }
            
            $examens = $this->em->getRepository(Examens::class)->findBy(['id_consultation' => $consultation]);
            foreach ($examens as $examen) {
                $this->em->remove($examen);
            }
            
            $this->em->remove($consultation);
            $this->em->flush();
            
            $this->addFlash('success', 'Consultation supprimée avec succès');
        }
        
        return $this->redirectToRoute('doctor_consultation_list');
    }

    #[Route('/consultation/{id}/show', name: 'doctor_consultation_show', methods: ['GET'])]
    public function showConsultation(Consultation $consultation): Response
    {
        $ordonnances = $this->em->getRepository(Ordonnance::class)->findBy(['id_consultation' => $consultation]);
        $examens = $this->em->getRepository(Examens::class)->findBy(['id_consultation' => $consultation]);
        
        return $this->render('doctor/consultation/show.html.twig', [
            'consultation' => $consultation,
            'ordonnances' => $ordonnances,
            'examens' => $examens,
        ]);
    }

    #[Route('/patient/{id}/communication', name: 'doctor_patient_communication', methods: ['GET'])]
    public function patientCommunication(int $id): Response
    {
        $patient = $this->em->getRepository(Patient::class)->find($id);
        
        if (!$patient) {
            $this->addFlash('error', 'Patient non trouvé');
            return $this->redirectToRoute('doctor_patients');
        }

        return $this->render('doctor/patient-communication.html.twig', [
            'patient' => $patient
        ]);
    }

    #[Route('/patient-queue', name: 'doctor_patient_queue_page', methods: ['GET'])]
    public function patientQueue(): Response
    {
        $todayStart = (new \DateTime())->setTime(0, 0, 0);
        $todayEnd = (new \DateTime())->setTime(23, 59, 59);
        
        $todaysConsultations = $this->em->getRepository(Consultation::class)->createQueryBuilder('c')
            ->where('c.date_consultation >= :today_start')
            ->andWhere('c.date_consultation <= :today_end')
            ->andWhere('c.status IN (:statuses)')
            ->setParameter('today_start', $todayStart)
            ->setParameter('today_end', $todayEnd)
            ->setParameter('statuses', ['scheduled', 'in_progress', 'waiting'])
            ->orderBy('c.date_consultation', 'ASC')
            ->getQuery()
            ->getResult();

        $queueData = [];
        foreach ($todaysConsultations as $consultation) {
            if (method_exists($consultation, 'getIdPatient')) {
                $patient = $consultation->getIdPatient();
            } else {
                $patient = null;
            }
            $queueData[] = [
                'id' => $consultation->getId(),
                'patientName' => $patient ? $patient->getNom() . ' ' . $patient->getPrenom() : 'Patient inconnu',
                'appointmentTime' => $consultation->getTimeConsultation() ? $consultation->getTimeConsultation()->format('H:i') : '',
                'status' => $consultation->getStatus(),
                'reason' => $consultation->getReasonForVisit(),
                'waitTime' => '15 min',
            ];
        }

        return $this->render('doctor/patient-queue.html.twig', [
            'queue' => $queueData,
        ]);
    }

    #[Route('/availability-settings', name: 'doctor_availability_settings', methods: ['GET'])]
    public function availabilitySettings(): Response
    {
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
