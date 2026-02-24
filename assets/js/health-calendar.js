/**
 * Health Calendar JavaScript Module
 * Handles FullCalendar initialization and event fetching for health journals
 */

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

// Health score color configuration
const healthColors = {
    green: '#22c55e',
    orange: '#f97316',
    red: '#ef4444',
    gray: '#6b7280',
};

/**
 * Initialize health calendar on the specified container element
 * 
 * @param {string} calendarId - The DOM ID of the calendar container
 * @param {string} dataUrl - The API URL to fetch calendar events
 * @param {Object} options - Optional configuration options
 */
function initHealthCalendar(calendarId, dataUrl, options = {}) {
    const calendarEl = document.getElementById(calendarId);
    
    if (!calendarEl) {
        console.error(`Calendar container element #${calendarId} not found`);
        return null;
    }

    // Default calendar options
    const defaultOptions = {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth',
        },
        height: 'auto',
        editable: false,
        selectable: false,
        dayMaxEvents: true,
        weekends: true,
        events: dataUrl,
        eventClick: handleEventClick,
        eventDidMount: handleEventDidMount,
        loading: handleLoading,
        locale: 'fr',
        buttonText: {
            today: "Aujourd'hui",
            month: 'Mois',
            list: 'Liste',
        },
    };

    // Merge with custom options
    const calendarOptions = { ...defaultOptions, ...options };

    // Create and initialize the calendar
    const calendar = new Calendar(calendarEl, calendarOptions);
    calendar.render();

    return calendar;
}

/**
 * Handle event click to show details in a modal
 * 
 * @param {Object} info - FullCalendar event info object
 */
function handleEventClick(info) {
    const event = info.event;
    const props = event.extendedProps;
    
    // Build detail message
    const details = `
        <div class="health-event-details">
            <h4>Score de Santé: ${props.score || 'N/A'}</h4>
            <p><strong>Grade:</strong> ${props.grade || 'N/A'}</p>
            <hr>
            <p><strong>Glycémie:</strong> ${props.glycemicScore?.toFixed(1) || 'N/A'}</p>
            <p><strong>Tension:</strong> ${props.bloodPressureScore?.toFixed(1) || 'N/A'}</p>
            <p><strong>Sommeil:</strong> ${props.sleepScore?.toFixed(1) || 'N/A'}</p>
            <p><strong>Symptoms:</strong> ${props.symptomScore?.toFixed(1) || 'N/A'}</p>
            <p><strong>Poids:</strong> ${props.weightScore?.toFixed(1) || 'N/A'}</p>
        </div>
    `;

    // Show in a modal or alert (customize as needed)
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: `Date: ${event.startStr}`,
            html: details,
            icon: 'info',
            confirmButtonText: 'Fermer',
        });
    } else {
        // Fallback to alert if SweetAlert2 is not available
        alert(`Date: ${event.startStr}\nScore: ${props.score}\nGrade: ${props.grade}`);
    }
}

/**
 * Handle event mount for additional styling
 * 
 * @param {Object} info - FullCalendar event info object
 */
function handleEventDidMount(info) {
    // Add tooltip with score details
    const props = info.event.extendedProps;
    const tooltipText = `
        Score: ${props.score || 'N/A'} (${props.grade || 'N/A'})
        Glycémie: ${props.glycemicScore?.toFixed(1) || 'N/A'}
        Tension: ${props.bloodPressureScore?.toFixed(1) || 'N/A'}
        Sommeil: ${props.sleepScore?.toFixed(1) || 'N/A'}
    `;
    
    info.el.title = tooltipText;
}

/**
 * Show/hide loading indicator during data fetch
 * 
 * @param {boolean} isLoading - Loading state
 */
function handleLoading(isLoading) {
    const loaderEl = document.getElementById('calendar-loader');
    if (loaderEl) {
        loaderEl.style.display = isLoading ? 'block' : 'none';
    }
}

/**
 * Create a health calendar with custom configuration
 * 
 * @param {Object} config - Configuration object
 * @returns {Calendar} FullCalendar instance
 */
function createHealthCalendar(config) {
    const {
        containerId,
        apiUrl,
        initialView = 'dayGridMonth',
        onEventClick = null,
    } = config;

    const calendarEl = document.getElementById(containerId);
    
    if (!calendarEl) {
        console.error(`Calendar container #${containerId} not found`);
        return null;
    }

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth',
        },
        height: 'auto',
        editable: false,
        selectable: false,
        dayMaxEvents: true,
        weekends: true,
        events: apiUrl,
        eventClick: onEventClick || handleEventClick,
        eventDidMount: handleEventDidMount,
        loading: handleLoading,
        locale: 'fr',
        buttonText: {
            today: "Aujourd'hui",
            month: 'Mois',
            list: 'Liste',
        },
    });

    calendar.render();
    return calendar;
}

/**
 * Fetch calendar events manually
 * 
 * @param {string} url - API URL
 * @returns {Promise<Array>} Array of events
 */
async function fetchCalendarEvents(url) {
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            return data.events;
        } else {
            console.error('Failed to fetch calendar data:', data.error);
            return [];
        }
    } catch (error) {
        console.error('Error fetching calendar events:', error);
        return [];
    }
}

/**
 * Update calendar events dynamically
 * 
 * @param {Calendar} calendar - FullCalendar instance
 * @param {string} journalId - Journal ID to fetch
 */
async function refreshCalendarEvents(calendar, journalId) {
    if (!calendar) return;
    
    const url = `/health/${journalId}/calendar-data`;
    const events = await fetchCalendarEvents(url);
    
    calendar.removeAllEvents();
    calendar.addEventSource(events);
}

// Export functions for use in other modules
window.HealthCalendar = {
    init: initHealthCalendar,
    create: createHealthCalendar,
    fetchEvents: fetchCalendarEvents,
    refresh: refreshCalendarEvents,
    colors: healthColors,
};

// Auto-initialize on DOM ready if data attributes are present
document.addEventListener('DOMContentLoaded', function() {
    const autoInitElements = document.querySelectorAll('[data-health-calendar]');
    
    autoInitElements.forEach(function(el) {
        const containerId = el.id || 'health-calendar';
        const journalId = el.dataset.journalId;
        const apiUrl = `/health/${journalId}/calendar-data`;
        
        initHealthCalendar(containerId, apiUrl);
    });
});

export default {
    init: initHealthCalendar,
    create: createHealthCalendar,
    fetchEvents: fetchCalendarEvents,
    refresh: refreshCalendarEvents,
    colors: healthColors,
};
