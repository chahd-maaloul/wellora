<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\HealthjournalRepository;
use App\Service\Health\HealthCalendarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for health calendar functionality.
 * 
 * Provides API endpoints for FullCalendar integration
 * with health journal entries visualization.
 */
#[Route('/health')]
final class HealthCalendarController extends AbstractController
{
    public function __construct(
        private readonly HealthCalendarService $calendarService,
        private readonly HealthjournalRepository $journalRepository,
    ) {}

    /**
     * Render the calendar page.
     */
    #[Route('/calendar', name: 'app_health_calendar', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $journals = $this->journalRepository->findBy([], ['datedebut' => 'DESC']);
        
        // Check for journal_id query parameter (0 = all journals)
        $requestedJournalId = $request->query->get('journal_id');
        if (null !== $requestedJournalId) {
            $selectedJournalId = (int) $requestedJournalId;
        } else {
            // Default to 0 (all journals) when no specific journal is requested
            $selectedJournalId = 0;
        }

        // Determine the initial date for the calendar view
        // When a specific journal is selected, jump to its start date
        $initialDate = null;
        if ($selectedJournalId > 0) {
            $selectedJournal = $this->journalRepository->find($selectedJournalId);
            if (null !== $selectedJournal && null !== $selectedJournal->getDatedebut()) {
                $initialDate = $selectedJournal->getDatedebut()->format('Y-m-d');
            }
        }

        return $this->render('health/calendar.html.twig', [
            'journals' => $journals,
            'selectedJournalId' => $selectedJournalId,
            'initialDate' => $initialDate,
        ]);
    }

    /**
     * Get calendar events for a specific health journal.
     * 
     * Returns JSON array of FullCalendar events with:
     * - title: Display score
     * - start: Date in Y-m-d format
     * - backgroundColor: Color based on health score
     * - extendedProps: Detailed score information
     * 
     * @param int $id The health journal ID
     * @return JsonResponse JSON response with calendar events
     */
    #[Route('/{id}/calendar-data', name: 'app_health_calendar_data', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getCalendarData(int $id): JsonResponse
    {
        try {
            $events = $this->calendarService->getEventsForJournal($id);

            return new JsonResponse([
                'success' => true,
                'events' => $events,
                'colorConfig' => $this->calendarService->getColorConfig(),
            ], Response::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'An error occurred while fetching calendar data',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get color configuration for the calendar legend.
     * 
     * @return JsonResponse JSON response with color thresholds
     */
    #[Route('/calendar/colors', name: 'app_health_calendar_colors', methods: ['GET'])]
    public function getColorConfig(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'config' => $this->calendarService->getColorConfig(),
        ], Response::HTTP_OK);
    }
}
