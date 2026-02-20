<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Healthjournal;
use App\Service\Health\HealthReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/health')]
final class HealthReportController extends AbstractController
{
    public function __construct(
        private readonly HealthReportService $reportService,
    ) {}

    /**
     * Main report page - uses default/latest journal
     */
    #[Route('/report', name: 'app_health_report_index', methods: ['GET'])]
    public function reportIndex(): Response
    {
        // Get journals with at least 5 entries
        $eligibleJournals = $this->reportService->getJournalsWithEnoughEntries();
        
        // Get default journal (using null to get default)
        $journal = $this->reportService->getJournal(null);
        
        // Handle case with no journal
        if (null === $journal) {
            return $this->render('health/report.html.twig', [
                'controller_name' => 'HealthReportController',
                'has_journal' => false,
                'journals' => $this->reportService->getAllJournals(),
                'eligible_journals' => $eligibleJournals,
            ]);
        }
        
        // Redirect to journal-specific report
        return $this->redirectToRoute('app_health_report', ['id' => $journal->getId()]);
    }

    #[Route('/{id}/report', name: 'app_health_report', methods: ['GET'])]
    public function report(?int $id = null): Response
    {
        // Get journals with at least 5 entries
        $eligibleJournals = $this->reportService->getJournalsWithEnoughEntries();
        
        // Get journal (by ID or default)
        $journal = $this->reportService->getJournal($id);
        
        // Handle case with no journal
        if (null === $journal) {
            return $this->render('health/report.html.twig', [
                'controller_name' => 'HealthReportController',
                'has_journal' => false,
                'journals' => $this->reportService->getAllJournals(),
                'eligible_journals' => $eligibleJournals,
            ]);
        }
        
        // Generate report
        $report = $this->reportService->generateReport($journal);
        
        return $this->render('health/report.html.twig', [
            'controller_name' => 'HealthReportController',
            'has_journal' => true,
            'has_data' => $report->totalEntries > 0,
            'report' => $report,
            'journals' => $this->reportService->getAllJournals(),
            'eligible_journals' => $eligibleJournals,
            'selected_journal_id' => $journal->getId(),
        ]);
    }

    #[Route('/{id}/report/pdf', name: 'app_health_report_pdf', methods: ['GET'])]
    public function downloadPdf(?int $id = null): Response
    {
        // Get journal (by ID or default)
        $journal = $this->reportService->getJournal($id);
        
        // Handle case with no journal
        if (null === $journal) {
            $this->addFlash('error', 'Aucun journal de santé trouvé.');
            
            return $this->redirectToRoute('app_health_report', [
                'id' => $id,
            ]);
        }
        
        // Generate report
        $report = $this->reportService->generateReport($journal);
        
        // Handle case with no data
        if ($report->totalEntries === 0) {
            $this->addFlash('warning', 'Aucune donnée disponible pour générer le rapport.');
            
            return $this->redirectToRoute('app_health_report', [
                'id' => $id,
            ]);
        }
        
        // Generate PDF
        $pdfContent = $this->reportService->generatePdf($report);
        
        // Generate filename
        $filename = sprintf(
            'rapport-sante-%s-%s.pdf',
            $report->patientName,
            $report->generatedAt?->format('Y-m-d') ?? date('Y-m-d')
        );
        
        // Check if export parameter is set - if so, force download
        $export = isset($_GET['export']) && $_GET['export'] === '1';
        
        if ($export) {
            // Create response with PDF content as download
            $response = new Response($pdfContent);
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            );
            
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', $disposition);
            
            return $response;
        }
        
        // Otherwise, display in browser
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline');
        
        return $response;
    }
}
