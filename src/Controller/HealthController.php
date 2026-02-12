<?php

namespace App\Controller;

use App\Entity\Healthentry;
use App\Entity\Healthjournal;
use App\Entity\Symptom;
use App\Form\HealthentryType;
use App\Repository\HealthentryRepository;
use App\Repository\HealthjournalRepository;
use App\Repository\SymptomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/health')]
final class HealthController extends AbstractController
{
    #[Route('/', name: 'app_health_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('app_healthentry_index');
    }

    #[Route('/journal', name: 'app_health_journal', methods: ['GET'])]
    public function journal(): Response
    {
        return $this->render('health/journal.html.twig', [
            'controller_name' => 'HealthController',
        ]);
    }

    #[Route('/dashboard', name: 'app_health_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('health/dashboard.html.twig', [
            'controller_name' => 'HealthController',
        ]);
    }

    #[Route('/records', name: 'app_health_records', methods: ['GET'])]
    public function records(): Response
    {
        return $this->render('health/records.html.twig', [
            'controller_name' => 'HealthController',
        ]);
    }

    #[Route('/prescriptions', name: 'app_health_prescriptions', methods: ['GET'])]
    public function prescriptions(): Response
    {
        return $this->render('health/prescriptions.html.twig', [
            'controller_name' => 'HealthController',
        ]);
    }

    #[Route('/lab-results', name: 'app_health_lab_results', methods: ['GET'])]
    public function labResults(): Response
    {
        return $this->render('health/lab-results.html.twig', [
            'controller_name' => 'HealthController',
        ]);
    }

    #[Route('/symptoms', name: 'app_health_symptoms', methods: ['GET'])]
    public function symptoms(): Response
    {
        return $this->render('health/symptoms.html.twig', [
            'controller_name' => 'HealthController',
        ]);
    }

    #[Route('/billing', name: 'app_health_billing', methods: ['GET'])]
    public function billing(): Response
    {
        return $this->render('health/billing.html.twig', [
            'controller_name' => 'HealthController',
        ]);
    }

    #[Route('/analytics', name: 'app_health_analytics', methods: ['GET', 'POST'])]
    public function analyticsPatient(
        Request $request,
        HealthjournalRepository $journalRepo,
        HealthentryRepository $entryRepo,
        SymptomRepository $symptomRepo
    ): Response {
        $journalId = $request->query->get('journal_id');
        
        // Get all journals
        $allJournals = $journalRepo->findAll();
        
        $selectedJournal = null;
        $entries = [];
        $startDate = null;
        $endDate = null;
        
        if ($journalId) {
            $selectedJournal = $journalRepo->find($journalId);
        }
        
        // If no journal selected and journals exist, use first one
        if (!$selectedJournal && count($allJournals) > 0) {
            $selectedJournal = $allJournals[0];
        }
        
        if ($selectedJournal) {
            $entries = $entryRepo->findBy(['journal' => $selectedJournal], ['date' => 'ASC']);
            
            if (count($entries) > 0) {
                $startDate = $entries[0]->getDate();
                $endDate = end($entries)->getDate();
            }
            
            // Parse month from journal name (e.g., "mars 2026", "March 2026", "january 2025")
            $journalName = $selectedJournal->getName() ?? '';
            $monthNum = null;
            
            $frenchMonths = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
            $englishMonths = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
            
            $journalNameLower = strtolower($journalName);
            
            foreach ($frenchMonths as $index => $frMonth) {
                if (strpos($journalNameLower, $frMonth) !== false) {
                    $monthNum = $index + 1;
                    break;
                }
            }
            
            if (!$monthNum) {
                foreach ($englishMonths as $index => $enMonth) {
                    if (strpos($journalNameLower, $enMonth) !== false) {
                        $monthNum = $index + 1;
                        break;
                    }
                }
            }
            
            // Extract year from journal name (4 digits)
            if (preg_match('/\b(19|20)\d{2}\b/', $journalName, $matches)) {
                $year = (int)$matches[0];
            } else {
                $year = (int)date('Y');
            }
            
            // Override date range if month/year found in journal name
            if ($monthNum) {
                $startDate = new \DateTime("$year-$monthNum-01");
                $endDate = new \DateTime("$year-$monthNum-" . date('t', mktime(0, 0, 0, $monthNum, 1, $year)));
            }
        }
        
        // Data arrays for charts
        $glycemicData = [];
        $bpSystolic = [];
        $bpDiastolic = [];
        $sleepData = [];
        $weightData = [];
        $dates = [];
        $symptomIntensity = [];
        
        foreach ($entries as $entry) {
            $glycemicData[] = $entry->getGlycemie() ?? 0;
            
            $tension = $entry->getTension();
            if ($tension !== null && $tension !== '') {
                $bpDiastolic[] = (float)$tension;
                // Estimate systolic as tension + 40 (common approximation: 120/80 = 40 difference)
                $bpSystolic[] = (float)$tension + 40;
            } else {
                $bpSystolic[] = 0;
                $bpDiastolic[] = 0;
            }
            
            $sleepData[] = $entry->getSommeil() ?? 0;
            $weightData[] = $entry->getPoids() ?? 0;
            $dates[] = $entry->getDate()->format('d/m/Y');
            
            $entrySymptoms = $entry->getSymptoms();
            $totalIntensity = 0;
            foreach ($entrySymptoms as $symptom) {
                $totalIntensity += $symptom->getIntensite();
            }
            $symptomIntensity[] = $totalIntensity;
        }
        
        // Calculate averages and scores
        $avgGlycemia = count($glycemicData) > 0 ? array_sum($glycemicData) / count($glycemicData) : 0;
        $minGlycemia = count($glycemicData) > 0 ? min($glycemicData) : 0;
        $maxGlycemia = count($glycemicData) > 0 ? max($glycemicData) : 0;
        $avgSystolic = count($bpSystolic) > 0 ? round(array_sum($bpSystolic) / count($bpSystolic)) : 0;
        $avgDiastolic = count($bpDiastolic) > 0 ? round(array_sum($bpDiastolic) / count($bpDiastolic)) : 0;
        $avgSleep = count($sleepData) > 0 ? array_sum($sleepData) / count($sleepData) : 0;
        $currentWeight = count($weightData) > 0 ? end($weightData) : 0;
        $weightVariation = 0;
        if (count($weightData) >= 2) {
            $weightVariation = $currentWeight - $weightData[0];
        }
        
        $avgIntensity = count($symptomIntensity) > 0 ? array_sum($symptomIntensity) / count($symptomIntensity) : 0;
        $totalSymptoms = array_sum($symptomIntensity);
        
        // Calculate scores (0-100)
        // Glycemic score: 100 = 1.0 g/L (ideal), lower/higher = lower score
        $glycemicScore = $avgGlycemia > 0 ? max(0, min(100, 100 - abs($avgGlycemia - 1.0) * 100)) : 50;
        
        // Blood pressure score: 100 = 120/80 (ideal)
        if ($avgSystolic >= 90 && $avgSystolic <= 120 && $avgDiastolic >= 60 && $avgDiastolic <= 80) {
            $bpScore = 100;
        } elseif ($avgSystolic >= 70 && $avgSystolic <= 130 && $avgDiastolic >= 50 && $avgDiastolic <= 85) {
            $bpScore = 75;
        } else {
            $bpScore = 50;
        }
        
        // Sleep score: 100 = 8 hours
        $sleepScore = $avgSleep > 0 ? min(100, ($avgSleep / 8) * 100) : 50;
        
        // Symptom score: 100 = no symptoms
        $symptomScore = $avgIntensity > 0 ? max(0, 100 - ($avgIntensity * 10)) : 100;
        
        // Weight score: BMI-based (simplified)
        $bmi = 23.2; // Placeholder
        $weightScore = $bmi >= 18.5 && $bmi <= 25 ? 100 : ($bmi >= 17 && $bmi <= 30 ? 75 : 50);
        
        // Global health score (weighted average)
        $globalScore = round(($glycemicScore * 0.25 + $bpScore * 0.25 + $sleepScore * 0.2 + $symptomScore * 0.15 + $weightScore * 0.15));
        
        return $this->render('health/analytics/patient-view.html.twig', [
            'controller_name' => 'HealthController',
            'glycemic_data' => $glycemicData,
            'bp_systolic' => $bpSystolic,
            'bp_diastolic' => $bpDiastolic,
            'sleep_data' => $sleepData,
            'weight_data' => $weightData,
            'dates' => $dates,
            'symptom_intensity' => $symptomIntensity,
            
            // Pass data for JS
            'avg_glycemia' => $avgGlycemia,
            'min_glycemia' => $minGlycemia,
            'max_glycemia' => $maxGlycemia,
            'avg_systolic' => $avgSystolic,
            'avg_diastolic' => $avgDiastolic,
            'avg_sleep' => $avgSleep,
            'current_weight' => $currentWeight,
            'weight_variation' => $weightVariation,
            'avg_intensity' => $avgIntensity,
            'total_symptoms' => $totalSymptoms,
            
            'glycemic_score' => $glycemicScore,
            'bp_score' => $bpScore,
            'sleep_score' => $sleepScore,
            'symptom_score' => $symptomScore,
            'weight_score' => $weightScore,
            'global_score' => $globalScore,
            
            'journals' => $allJournals,
            'selected_journal_id' => $journalId ?: (count($allJournals) > 0 ? $allJournals[0]->getId() : null),
            'start_date' => $startDate ? $startDate->format('d/m/Y') : null,
            'end_date' => $endDate ? $endDate->format('d/m/Y') : null,
            'start_date_js' => $startDate ? $startDate->format('Y-m-d') : '',
            'end_date_js' => $endDate ? $endDate->format('Y-m-d') : '',
        ]);
    }

    #[Route('/accessible/body-map', name: 'app_health_accessible_body_map', methods: ['GET'])]
    public function bodyMapAccessible(): Response
    {
        return $this->render('health/accessible/body-map.html.twig', [
            'controller_name' => 'HealthController',
        ]);
    }

    #[Route('/journal/accessible', name: 'app_health_journal_accessible', methods: ['GET', 'POST'])]
    public function journalAccessible(Request $request, EntityManagerInterface $entityManager, HealthjournalRepository $journalRepo, HealthentryRepository $entryRepo): Response
    {
        $healthentry = new Healthentry();
        
        // Pre-populate with one empty symptom for UX
        $healthentry->addSymptom(new Symptom());
        
        $form = $this->createForm(HealthentryType::class, $healthentry);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            // Handle "Add Symptom" button
            if ($request->request->has('add_symptom')) {
                // Add a new empty symptom to the collection
                $symptom = new Symptom();
                $healthentry->addSymptom($symptom);
                
                // Re-create form with updated data
                $form = $this->createForm(HealthentryType::class, $healthentry);
                
                return $this->render('health/accessible/journal-entry.html.twig', [
                    'controller_name' => 'HealthController',
                    'form' => $form->createView(),
                ]);
            }
            
            // Manual validation for numeric fields
            $poids = $form->get('poids')->getData();
            if ($poids !== null && $poids !== '' && ($poids < 30 || $poids > 200)) {
                $form->get('poids')->addError(new \Symfony\Component\Form\FormError(
                    'Le poids doit être compris entre 30 et 200 kg'
                ));
            }
            
            $glycemie = $form->get('glycemie')->getData();
            if ($glycemie !== null && $glycemie !== '' && ($glycemie < 0.5 || $glycemie > 3)) {
                $form->get('glycemie')->addError(new \Symfony\Component\Form\FormError(
                    'La glycémie doit être comprise entre 0.5 et 3 g/l'
                ));
            }
            
            $tension = $form->get('tension')->getData();
            if ($tension !== null && $tension !== '') {
                $tensionValue = (float)$tension;
                if ($tensionValue < 40 || $tensionValue > 120) {
                    $form->get('tension')->addError(new \Symfony\Component\Form\FormError(
                        'La tension doit être comprise entre 40 et 120 mmHg'
                    ));
                }
            }
            
            $sommeil = $form->get('sommeil')->getData();
            if ($sommeil !== null && $sommeil !== '' && ($sommeil < 0 || $sommeil > 12)) {
                $form->get('sommeil')->addError(new \Symfony\Component\Form\FormError(
                    'Le sommeil doit être compris entre 0 et 12 heures'
                ));
            }
            
            if ($form->isValid()) {
                // Set journal
                $journal = $journalRepo->findOneBy([]);
                if (!$journal) {
                    $journal = new Healthjournal();
                    $journal->setName('Journal Principal');
                    $journal->setDatedebut(new \DateTime());
                    $entityManager->persist($journal);
                    $entityManager->flush();
                }
                $healthentry->setJournal($journal);
                
                // Check for duplicate entry
                $date = $healthentry->getDate();
                if ($date) {
                    $existingEntry = $entryRepo->findOneBy(['date' => $date, 'journal' => $journal]);
                    if ($existingEntry) {
                        $form->addError(new \Symfony\Component\Form\FormError(
                            'Une entrée existe déjà pour cette date. Veuillez modifier l\'entrée existante.'
                        ));
                        return $this->render('health/accessible/journal-entry.html.twig', [
                            'controller_name' => 'HealthController',
                            'form' => $form->createView(),
                        ]);
                    }
                }
                
                $entityManager->persist($healthentry);
                $entityManager->flush();

                $this->addFlash('success', 'Entrée de journal créée avec succès');
                return $this->redirectToRoute('app_health_journal_accessible');
            }
        }
        
        return $this->render('health/accessible/journal-entry.html.twig', [
            'controller_name' => 'HealthController',
            'form' => $form->createView(),
        ]);
    }

    // Legacy route redirect for backward compatibility
    #[Route('/accessible/journal-entry', name: 'app_health_accessible_journal_entry', methods: ['GET'])]
    public function accessibleJournalEntryRedirect(): Response
    {
        return $this->redirectToRoute('app_health_journal_accessible', [], 301);
    }
}
