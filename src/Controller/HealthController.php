<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Health\HealthMetricDTO;
use App\DTO\Health\HealthScoreDTO;
use App\DTO\Health\HealthStatisticsDTO;
use App\DTO\Health\HealthTrendDTO;
use App\DTO\Health\HealthTrendDirection;
use App\DTO\Health\HealthRiskDTO;
use App\Entity\Healthentry;
use App\Entity\Healthjournal;
use App\Entity\Symptom;
use App\Form\HealthentryType;
use App\Repository\HealthentryRepository;
use App\Repository\HealthjournalRepository;
use App\Repository\SymptomRepository;
use App\Service\Health\HealthAnalyticsService;
use App\Service\Health\HealthRiskEngineService;
use App\Service\Health\HealthTrendService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/health')]
final class HealthController extends AbstractController
{
    public function __construct(
        private readonly HealthAnalyticsService $analyticsService,
        private readonly HealthTrendService $trendService,
        private readonly HealthRiskEngineService $riskEngineService,
    ) {}

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
    ): Response {
        // Get selected journal
        $journalId = $request->query->get('journal_id');
        $selectedJournal = $this->resolveSelectedJournal($journalRepo, $journalId);
        
        // Handle case with no data
        if (null === $selectedJournal) {
            return $this->render('health/analytics/patient-view.html.twig', [
                'controller_name' => 'HealthController',
                'has_data' => false,
                'start_date' => null,
                'end_date' => null,
                'start_date_js' => '',
                'end_date_js' => '',
            ]);
        }
        
        // Get analytics data from service
        $analyticsData = $this->analyticsService->getAnalyticsForJournal($selectedJournal);
        
        $metrics = $analyticsData['metrics'];
        $statistics = $analyticsData['statistics'];
        $scores = $analyticsData['scores'];
        
        // Handle case with no entries - still pass date range
        if ($metrics->isEmpty()) {
            $dateRange = $this->parseJournalDateRange($selectedJournal);
            
            return $this->render('health/analytics/patient-view.html.twig', [
                'controller_name' => 'HealthController',
                'has_data' => false,
                'journals' => $journalRepo->findAll(),
                'selected_journal_id' => $selectedJournal->getId(),
                'start_date' => $dateRange['start'],
                'end_date' => $dateRange['end'],
                'start_date_js' => $dateRange['startJs'],
                'end_date_js' => $dateRange['endJs'],
            ]);
        }
        
        // Get trend comparison
        $trend = $this->trendService->compareWithPrevious($selectedJournal);
        
        // Get risk assessment
        $risk = $this->riskEngineService->analyzeRisk($metrics);
        
        // Prepare chart data
        $chartData = $this->prepareChartData($metrics);
        
        // Date range parsing from journal name (kept in controller as it's view-related)
        $dateRange = $this->parseJournalDateRange($selectedJournal);
        
        return $this->render('health/analytics/patient-view.html.twig', [
            'controller_name' => 'HealthController',
            'has_data' => true,
            
            // Chart data
            'glycemic_data' => $chartData['glycemia'],
            'bp_systolic' => $chartData['bpSystolic'],
            'bp_diastolic' => $chartData['bpDiastolic'],
            'sleep_data' => $chartData['sleep'],
            'weight_data' => $chartData['weight'],
            'dates' => $chartData['dates'],
            'symptom_intensity' => $chartData['symptomIntensity'],
            
            // Statistics
            'avg_glycemia' => $statistics->avgGlycemia,
            'min_glycemia' => $statistics->minGlycemia,
            'max_glycemia' => $statistics->maxGlycemia,
            'avg_systolic' => $statistics->avgSystolic,
            'avg_diastolic' => $statistics->avgDiastolic,
            'avg_sleep' => $statistics->avgSleep,
            'current_weight' => $statistics->currentWeight,
            'weight_variation' => $statistics->weightVariation,
            'avg_intensity' => $statistics->avgIntensity,
            'total_symptoms' => $statistics->totalSymptomIntensity,
            
            // Scores
            'glycemic_score' => $scores->glycemicScore,
            'bp_score' => $scores->bloodPressureScore,
            'sleep_score' => $scores->sleepScore,
            'symptom_score' => $scores->symptomScore,
            'weight_score' => $scores->weightScore,
            'global_score' => $scores->globalScore,
            'global_grade' => $scores->globalScoreGrade,
            
            // Trend data
            'has_trend_data' => $trend->hasPreviousData,
            'global_evolution' => $trend->globalEvolutionPercentage,
            'trend_direction' => $trend->globalDirection->value,
            
            // Risk data
            'risk_tier' => $risk->tier->value,
            'risk_score' => $risk->overallRiskScore,
            'risk_summary' => $risk->summary,
            'risk_recommendations' => $risk->recommendations,
            'risk_factors' => array_map(fn($f) => [
                'name' => $f->name,
                'description' => $f->description,
                'severity' => $f->severity,
            ], $risk->riskFactors),
            'requires_attention' => $risk->requiresImmediateAttention,
            
            // Journal info
            'journals' => $journalRepo->findAll(),
            'selected_journal_id' => $selectedJournal->getId(),
            'start_date' => $dateRange['start'],
            'end_date' => $dateRange['end'],
            'start_date_js' => $dateRange['startJs'],
            'end_date_js' => $dateRange['endJs'],
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
    public function journalAccessible(
        Request $request,
        EntityManagerInterface $entityManager,
        HealthjournalRepository $journalRepo,
        HealthentryRepository $entryRepo
    ): Response {
        $healthentry = new Healthentry();
        // Add empty symptom for form rendering - will be removed before saving if not filled
        $healthentry->addSymptom(new Symptom());
        
        $form = $this->createForm(HealthentryType::class, $healthentry);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            // Handle "Add Symptom" button
            if ($request->request->has('add_symptom')) {
                $symptom = new Symptom();
                $healthentry->addSymptom($symptom);
                $form = $this->createForm(HealthentryType::class, $healthentry);
                
                return $this->render('health/accessible/journal-entry.html.twig', [
                    'controller_name' => 'HealthController',
                    'form' => $form->createView(),
                ]);
            }
            
            // Manual validation for numeric fields
            $this->validateNumericFields($form);
            
            if ($form->isValid()) {
                $this->processValidEntry($form, $entityManager, $journalRepo, $entryRepo, $request);
            }
        }
        
        return $this->render('health/accessible/journal-entry.html.twig', [
            'controller_name' => 'HealthController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/accessible/journal-entry', name: 'app_health_accessible_journal_entry', methods: ['GET'])]
    public function accessibleJournalEntryRedirect(): Response
    {
        return $this->redirectToRoute('app_health_journal_accessible', [], 301);
    }

    // ============================================
    // PRIVATE HELPER METHODS
    // ============================================
    
    private function resolveSelectedJournal(
        HealthjournalRepository $journalRepo,
        ?string $journalId
    ): ?Healthjournal {
        // If specific journal requested
        if ($journalId) {
            return $journalRepo->find((int) $journalId);
        }
        
        // Get all journals and return first one
        $journals = $journalRepo->findBy([], ['datedebut' => 'DESC']);
        
        return !empty($journals) ? $journals[0] : null;
    }
    
    /**
     * @param \App\DTO\Health\HealthMetricDTO $metrics
     */
    private function prepareChartData($metrics): array
    {
        // Create new arrays for template to avoid readonly issues
        $glycemia = [];
        $bpSystolic = [];
        $bpDiastolic = [];
        $sleep = [];
        $weight = [];
        $dates = [];
        $symptomIntensity = [];
        
        foreach ($metrics->glycemia as $v) {
            $glycemia[] = $v;
        }
        foreach ($metrics->bloodPressureSystolic as $v) {
            $bpSystolic[] = $v;
        }
        foreach ($metrics->bloodPressureDiastolic as $v) {
            $bpDiastolic[] = $v;
        }
        foreach ($metrics->sleep as $v) {
            $sleep[] = $v;
        }
        foreach ($metrics->weight as $v) {
            $weight[] = $v;
        }
        foreach ($metrics->symptomIntensity as $v) {
            $symptomIntensity[] = $v;
        }
        foreach ($metrics->dates as $d) {
            $dates[] = $d instanceof \DateTimeInterface 
                ? $d->format('d/m/Y') 
                : (is_object($d) ? $d->format('d/m/Y') : '');
        }
        
        return [
            'glycemia' => $glycemia,
            'bpSystolic' => $bpSystolic,
            'bpDiastolic' => $bpDiastolic,
            'sleep' => $sleep,
            'weight' => $weight,
            'dates' => $dates,
            'symptomIntensity' => $symptomIntensity,
        ];
    }
    
    private function parseJournalDateRange(Healthjournal $journal): array
    {
        $journalName = $journal->getName() ?? '';
        $datedebut = $journal->getDatedebut();
        $datefin = $journal->getDatefin();
        
        // Try to extract month/year from journal name
        $extracted = $this->extractMonthYearFromName($journalName);
        
        if (null !== $extracted) {
            $startDate = $extracted['start'];
            $endDate = $extracted['end'];
        } elseif (null !== $datedebut && null !== $datefin) {
            $startDate = $datedebut;
            $endDate = $datefin;
        } else {
            $startDate = new \DateTime();
            $endDate = new \DateTime();
        }
        
        return [
            'start' => $startDate->format('d/m/Y'),
            'end' => $endDate->format('d/m/Y'),
            'startJs' => $startDate->format('Y-m-d'),
            'endJs' => $endDate->format('Y-m-d'),
        ];
    }
    
    private function extractMonthYearFromName(string $name): ?array
    {
        $frenchMonths = [
            'janvier' => 1, 'février' => 2, 'mars' => 3, 'avril' => 4,
            'mai' => 5, 'juin' => 6, 'juillet' => 7, 'août' => 8,
            'septembre' => 9, 'octobre' => 10, 'novembre' => 11, 'décembre' => 12,
        ];
        
        $englishMonths = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
        ];
        
        $nameLower = strtolower($name);
        $month = null;
        
        // Check French months
        foreach ($frenchMonths as $monthName => $monthNum) {
            if (str_contains($nameLower, $monthName)) {
                $month = $monthNum;
                break;
            }
        }
        
        // Check English months if not found
        if (null === $month) {
            foreach ($englishMonths as $monthName => $monthNum) {
                if (str_contains($nameLower, $monthName)) {
                    $month = $monthNum;
                    break;
                }
            }
        }
        
        // Extract year
        $year = (int) date('Y');
        if (preg_match('/\b(19|20)\d{2}\b/', $name, $matches)) {
            $year = (int) $matches[0];
        }
        
        if (null === $month) {
            return null;
        }
        
        $startDate = new \DateTime(sprintf('%d-%02d-01', $year, $month));
        $endDate = (clone $startDate)->modify('last day of this month');
        
        return ['start' => $startDate, 'end' => $endDate];
    }
    
    private function validateNumericFields($form): void
    {
        $poids = $form->get('poids')->getData();
        if (null !== $poids && $poids !== '' && ($poids < 30 || $poids > 200)) {
            $form->get('poids')->addError(new \Symfony\Component\Form\FormError(
                'Le poids doit être compris entre 30 et 200 kg'
            ));
        }
        
        $glycemie = $form->get('glycemie')->getData();
        if (null !== $glycemie && $glycemie !== '' && ($glycemie < 0.5 || $glycemie > 3)) {
            $form->get('glycemie')->addError(new \Symfony\Component\Form\FormError(
                'La glycémie doit être comprise entre 0.5 et 3 g/l'
            ));
        }
        
        $tension = $form->get('tension')->getData();
        if (null !== $tension && $tension !== '') {
            $tensionValue = (float) $tension;
            if ($tensionValue < 40 || $tensionValue > 120) {
                $form->get('tension')->addError(new \Symfony\Component\Form\FormError(
                    'La tension doit être comprise entre 40 et 120 mmHg'
                ));
            }
        }
        
        $sommeil = $form->get('sommeil')->getData();
        if (null !== $sommeil && $sommeil !== '' && ($sommeil < 0 || $sommeil > 12)) {
            $form->get('sommeil')->addError(new \Symfony\Component\Form\FormError(
                'Le sommeil doit être compris entre 0 et 12 heures'
            ));
        }
    }
    
    private function processValidEntry(
        $form,
        EntityManagerInterface $entityManager,
        HealthjournalRepository $journalRepo,
        HealthentryRepository $entryRepo,
        Request $request
    ): Response {
        $healthentry = $form->getData();
        
        // Remove empty symptoms (those without type) before saving
        if ($healthentry->getSymptoms()->count() > 0) {
            $symptomsToRemove = [];
            foreach ($healthentry->getSymptoms() as $symptom) {
                if (null === $symptom->getType() || '' === $symptom->getType()) {
                    $symptomsToRemove[] = $symptom;
                }
            }
            foreach ($symptomsToRemove as $symptom) {
                $healthentry->removeSymptom($symptom);
            }
        }
        
        // Find the journal based on entry date - entries are automatically assigned
        // to the journal whose date range includes the entry date
        $entryDate = $healthentry->getDate();
        $journal = null;
        
        if (null !== $entryDate) {
            // Find journal whose date range includes this entry date
            $journal = $journalRepo->createQueryBuilder('j')
                ->andWhere('j.datedebut <= :entryDate')
                ->andWhere('j.datefin >= :entryDate')
                ->setParameter('entryDate', $entryDate)
                ->getQuery()
                ->getOneOrNullResult();
        }
        
        // Fallback: if no journal found by date, use first journal
        if (null === $journal) {
            $journal = $journalRepo->findOneBy([]);
            if (null === $journal) {
                $journal = new Healthjournal();
                $journal->setName('Journal Principal');
                $journal->setDatedebut(new \DateTime());
                $entityManager->persist($journal);
                $entityManager->flush();
            }
        }
        
        $healthentry->setJournal($journal);
        
        // Check for duplicate entry
        $date = $healthentry->getDate();
        if (null !== $date) {
            $existingEntry = $entryRepo->findOneBy(['date' => $date, 'journal' => $journal]);
            if (null !== $existingEntry) {
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
