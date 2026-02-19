<?php

namespace App\EventSubscriber;

use App\Service\Health\HealthCalendarService;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Calendar subscriber to load health events into FullCalendar.
 * 
 * This subscriber listens to the CalendarEvents::SET_DATA event
 * and loads health entry data from the database.
 * 
 * The CalendarBundle\Entity\Event class uses addOption() for all
 * FullCalendar properties (backgroundColor, textColor, url, extendedProps, etc.)
 * These options are merged into the serialized event array via toArray().
 */
class CalendarSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly HealthCalendarService $calendarService,
        private readonly UrlGeneratorInterface $router,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar): void
    {
        $filters = $calendar->getFilters();

        // Get journal ID from filters (0 or "all" means all journals)
        $journalId = $filters['journal_id'] ?? null;

        if (null === $journalId) {
            return;
        }

        try {
            // Determine which events to load
            $intJournalId = (int) $journalId;

            if (0 === $intJournalId || 'all' === $journalId) {
                // Load events from ALL journals
                $events = $this->calendarService->getEventsForAllJournals();
            } else {
                // Load events for a specific journal
                $events = $this->calendarService->getEventsForJournal($intJournalId);
            }

            foreach ($events as $eventData) {
                $event = new Event(
                    $eventData['title'],
                    new \DateTime($eventData['start']),
                );

                // Set optional end date if present
                if (isset($eventData['end'])) {
                    $event->setEnd(new \DateTime($eventData['end']));
                }

                // Set colors via addOption (merged into FullCalendar event JSON)
                if (isset($eventData['backgroundColor'])) {
                    $event->addOption('backgroundColor', $eventData['backgroundColor']);
                    $event->addOption('borderColor', $eventData['borderColor'] ?? $eventData['backgroundColor']);
                }

                if (isset($eventData['textColor'])) {
                    $event->addOption('textColor', $eventData['textColor']);
                }

                // Add extended properties for event click details
                if (isset($eventData['extendedProps'])) {
                    $event->addOption('extendedProps', $eventData['extendedProps']);
                }

                // Add URL for event click (pointing to calendar with journal context)
                $eventUrl = $this->router->generate('app_health_calendar', [
                    'journal_id' => $intJournalId,
                ]);
                $event->addOption('url', $eventUrl);

                $calendar->addEvent($event);
            }
        } catch (\InvalidArgumentException $e) {
            // Journal not found, no events to add
            return;
        }
    }
}
