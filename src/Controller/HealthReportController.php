<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Healthjournal;
use App\Service\Health\HealthReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/health')]
final class HealthReportController extends AbstractController
{
    public function __construct(
        private readonly HealthReportService $reportService,
        private readonly Security $security,
    ) {}

    /**
     * Main report page - shows list of available reports
     */
    #[Route('/report', name: 'app_health_report_index', methods: ['GET'])]
    public function reportIndex(): Response
    {
        $user = $this->security->getUser();
        
        // Get journals with at least 5 entries for current user
        $eligibleJournals = $this->reportService->getJournalsWithEnoughEntries($user);
        
        // Get default journal (using null to get default)
        $journal = $this->reportService->getJournal(null, $user);
        
        // Render the list page (don't redirect - show the list first)
        return $this->render('health/report.html.twig', [
            'controller_name' => 'HealthReportController',
            'has_journal' => $journal !== null,
            'has_data' => false,
            'journals' => $this->reportService->getAllJournals($user),
            'eligible_journals' => $eligibleJournals,
            'selected_journal_id' => null,
        ]);
    }

    #[Route('/{id}/report', name: 'app_health_report', methods: ['GET'])]
    public function report(?int $id = null): Response
    {
        $user = $this->security->getUser();
        
        // Get journals with at least 5 entries for current user
        $eligibleJournals = $this->reportService->getJournalsWithEnoughEntries($user);
        
        // Get journal (by ID or default)
        $journal = $this->reportService->getJournal($id, $user);
        
        // Handle case with no journal
        if (null === $journal) {
            return $this->render('health/report.html.twig', [
                'controller_name' => 'HealthReportController',
                'has_journal' => false,
                'has_data' => false,
                'journals' => $this->reportService->getAllJournals($user),
                'eligible_journals' => $eligibleJournals,
            ]);
        }
        
        // Generate report
        $report = $this->reportService->generateReport($journal, $user);
        
        return $this->render('health/report.html.twig', [
            'controller_name' => 'HealthReportController',
            'has_journal' => true,
            'has_data' => $report->totalEntries > 0,
            'report' => $report,
            'journals' => $this->reportService->getAllJournals($user),
            'eligible_journals' => $eligibleJournals,
            'selected_journal_id' => $journal->getId(),
        ]);
    }

    #[Route('/{id}/report/pdf', name: 'app_health_report_pdf', methods: ['GET'])]
    public function downloadPdf(?int $id = null): Response
    {
        $user = $this->security->getUser();
        
        // Get journal (by ID or default)
        $journal = $this->reportService->getJournal($id, $user);
        
        // Handle case with no journal
        if (null === $journal) {
            $this->addFlash('error', 'Aucun journal de santé trouvé.');
            
            return $this->redirectToRoute('app_health_report', [
                'id' => $id,
            ]);
        }
        
        // Generate report
        $report = $this->reportService->generateReport($journal, $user);
        
        // Generate PDF (even if no data - will show empty report)
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
