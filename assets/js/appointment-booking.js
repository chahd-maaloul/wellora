/**
 * WellCare Connect - Appointment Booking JavaScript
 * Handles all appointment booking functionality including:
 * - Multi-step form navigation
 * - Calendar interactions
 * - Real-time availability checking
 * - Form validation
 * - Payment processing
 */

import '../styles/appointment.css';

// Doctor Search Alpine.js Component
function doctorSearch() {
    return {
        // Search query
        searchQuery: '',
        
        // Filters
        filters: {
            specialties: [],
            consultationTypes: [],
            maxPrice: 200,
            languages: [],
            gender: '',
            minRating: 0,
            location: '',
            availability: ''
        },
        
        // View mode
        viewMode: 'list',
        sortBy: 'recommended',
        
        // Loading state
        loading: false,
        
        // Doctors data
        doctors: [
            {
                id: 1,
                name: 'Sarah Johnson',
                specialty: 'Cardiology',
                rating: 4.9,
                reviewCount: 127,
                experience: 15,
                location: 'Tunis',
                languages: ['English', 'French', 'Arabic'],
                hospitals: ['Mustapha Hospital', 'Clinique Pasteur'],
                price: 150,
                nextAvailable: 'Today',
                isVerified: true,
                availableSlots: [
                    { date: '2026-02-03', time: '09:00' },
                    { date: '2026-02-03', time: '10:00' },
                    { date: '2026-02-03', time: '14:00' },
                    { date: '2026-02-04', time: '09:00' },
                    { date: '2026-02-04', time: '11:00' }
                ]
            },
            {
                id: 2,
                name: 'Michael Chen',
                specialty: 'Dermatology',
                rating: 4.8,
                reviewCount: 98,
                experience: 12,
                location: 'Sousse',
                languages: ['English', 'Arabic'],
                hospitals: ['Sousse Medical Center'],
                price: 120,
                nextAvailable: 'Tomorrow',
                isVerified: true,
                availableSlots: [
                    { date: '2026-02-04', time: '10:00' },
                    { date: '2026-02-04', time: '14:00' },
                    { date: '2026-02-05', time: '09:00' }
                ]
            },
            {
                id: 3,
                name: 'Emma Wilson',
                specialty: 'General Practice',
                rating: 4.7,
                reviewCount: 215,
                experience: 8,
                location: 'Tunis',
                languages: ['English', 'French', 'Arabic'],
                hospitals: ['City Health Clinic'],
                price: 80,
                nextAvailable: 'Today',
                isVerified: true,
                availableSlots: [
                    { date: '2026-02-03', time: '11:00' },
                    { date: '2026-02-03', time: '15:00' },
                    { date: '2026-02-03', time: '16:00' }
                ]
            },
            {
                id: 4,
                name: 'James Rodriguez',
                specialty: 'Neurology',
                rating: 4.9,
                reviewCount: 89,
                experience: 18,
                location: 'Sfax',
                languages: ['Spanish', 'French', 'Arabic'],
                hospitals: ['Sfax University Hospital'],
                price: 200,
                nextAvailable: 'This Week',
                isVerified: false,
                availableSlots: [
                    { date: '2026-02-06', time: '09:00' },
                    { date: '2026-02-07', time: '10:00' }
                ]
            },
            {
                id: 5,
                name: 'Lisa Thompson',
                specialty: 'Orthopedics',
                rating: 4.6,
                reviewCount: 156,
                experience: 14,
                location: 'Monastir',
                languages: ['English', 'Arabic'],
                hospitals: ['Monastir Regional Hospital'],
                price: 180,
                nextAvailable: 'Weekend',
                isVerified: true,
                availableSlots: [
                    { date: '2026-02-08', time: '09:00' },
                    { date: '2026-02-08', time: '10:00' },
                    { date: '2026-02-08', time: '11:00' }
                ]
            },
            {
                id: 6,
                name: 'David Kim',
                specialty: 'Pediatrics',
                rating: 4.8,
                reviewCount: 312,
                experience: 10,
                location: 'Nabeul',
                languages: ['Korean', 'English', 'French'],
                hospitals: ['Nabeul Children\'s Hospital'],
                price: 100,
                nextAvailable: 'Today',
                isVerified: true,
                availableSlots: [
                    { date: '2026-02-03', time: '08:00' },
                    { date: '2026-02-03', time: '09:00' },
                    { date: '2026-02-03', time: '10:00' },
                    { date: '2026-02-03', time: '13:00' }
                ]
            }
        ],
        
        // Popular specialties
        popularSpecialties: ['Cardiology', 'Dermatology', 'General Practice', 'Neurology', 'Orthopedics', 'Pediatrics'],
        
        // All specialties
        allSpecialties: ['Cardiology', 'Dermatology', 'General Practice', 'Neurology', 'Orthopedics', 'Pediatrics', 'Psychiatry', 'Ophthalmology', 'Gynecology', 'Urology'],
        
        // Languages
        languages: [
            { code: 'en', name: 'English' },
            { code: 'fr', name: 'French' },
            { code: 'ar', name: 'Arabic' },
            { code: 'es', name: 'Spanish' }
        ],
        
        // Pagination
        currentPage: 1,
        itemsPerPage: 10,
        
        // Initialize
        init() {
            console.log('Doctor search initialized');
        },
        
        // Computed: filtered doctors
        get filteredDoctors() {
            let result = this.doctors;
            
            // Search query filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                result = result.filter(doc => 
                    doc.name.toLowerCase().includes(query) ||
                    doc.specialty.toLowerCase().includes(query)
                );
            }
            
            // Specialty filter
            if (this.filters.specialties.length > 0) {
                result = result.filter(doc => 
                    this.filters.specialties.includes(doc.specialty)
                );
            }
            
            // Price filter
            result = result.filter(doc => doc.price <= this.filters.maxPrice);
            
            // Language filter
            if (this.filters.languages.length > 0) {
                result = result.filter(doc => 
                    doc.languages.some(lang => this.filters.languages.includes(lang))
                );
            }
            
            // Rating filter
            result = result.filter(doc => doc.rating >= this.filters.minRating);
            
            // Location filter
            if (this.filters.location) {
                result = result.filter(doc => 
                    doc.location.toLowerCase() === this.filters.location.toLowerCase()
                );
            }
            
            // Sort
            result = this.sortDoctorsList(result);
            
            return result;
        },
        
        // Sort doctors
        sortDoctorsList(doctors) {
            return doctors.sort((a, b) => {
                switch (this.sortBy) {
                    case 'rating':
                        return b.rating - a.rating;
                    case 'experience':
                        return b.experience - a.experience;
                    case 'price-low':
                        return a.price - b.price;
                    case 'price-high':
                        return b.price - a.price;
                    case 'availability':
                        return a.nextAvailable.localeCompare(b.nextAvailable);
                    default:
                        return (b.rating * 10 + b.reviewCount) - (a.rating * 10 + a.reviewCount);
                }
            });
        },
        
        // Paginated doctors
        get paginatedDoctors() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredDoctors.slice(start, start + this.itemsPerPage);
        },
        
        // Apply filters
        applyFilters() {
            this.currentPage = 1;
        },
        
        // Reset filters
        resetFilters() {
            this.searchQuery = '';
            this.filters.specialties = [];
            this.filters.maxPrice = 200;
            this.filters.languages = [];
            this.filters.minRating = 0;
            this.filters.location = '';
            this.sortBy = 'recommended';
            this.currentPage = 1;
        },
        
        // Select specialty
        selectSpecialty(specialty) {
            this.searchQuery = specialty;
            this.applyFilters();
        },
        
        // Debounce search
        debounceTimer: null,
        debouncedSearch() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.applyFilters();
            }, 300);
        },
        
        // Search doctors
        searchDoctors() {
            this.applyFilters();
        },
        
        // Book slot
        bookSlot(doctor, slot) {
            window.location.href = `/appointment/booking-flow?doctor=${doctor.id}&date=${slot.date}&time=${slot.time}`;
        }
    };
}

// Register with Alpine
import Alpine from 'alpinejs';
Alpine.data('doctorSearch', doctorSearch);

// Appointment Booking Module
const AppointmentBooking = {
    // Configuration
    config: {
        apiBaseUrl: '/api/appointments',
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        dateFormat: 'YYYY-MM-DD',
        timeFormat: 'HH:mm'
    },

    // State
    state: {
        currentStep: 1,
        totalSteps: 4,
        formData: {},
        availableSlots: [],
        selectedDoctor: null,
        isLoading: false
    },

    /**
     * Initialize the appointment booking module
     */
    init() {
        this.setupEventListeners();
        this.initializeCalendar();
        this.loadDoctorData();
        console.log('Appointment Booking module initialized');
    },

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Form navigation
        document.querySelectorAll('[data-step]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const step = parseInt(e.target.dataset.step);
                this.navigateToStep(step);
            });
        });

        // Consultation type selection
        document.querySelectorAll('[data-consultation-type]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.selectConsultationType(e.target.dataset.consultationType);
            });
        });

        // Appointment mode selection
        document.querySelectorAll('[data-appointment-mode]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.selectAppointmentMode(e.target.dataset.appointmentMode);
            });
        });

        // Real-time availability check
        const dateInputs = document.querySelectorAll('[data-availability-check]');
        dateInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                this.checkAvailability(e.target.value);
            });
        });
    },

    /**
     * Navigate to a specific step in the booking flow
     */
    navigateToStep(step) {
        if (step < 1 || step > this.state.totalSteps) return;
        
        // Validate current step before proceeding
        if (step > this.state.currentStep && !this.validateStep(this.state.currentStep)) {
            return;
        }

        this.state.currentStep = step;
        this.updateStepVisibility();
        this.updateProgressIndicator();
    },

    /**
     * Validate the current step
     */
    validateStep(step) {
        const stepElement = document.querySelector(`[data-step-content="${step}"]`);
        if (!stepElement) return true;

        const requiredFields = stepElement.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('input-error');
                
                // Add error message
                let errorMsg = field.parentElement.querySelector('.error-message');
                if (!errorMsg) {
                    errorMsg = document.createElement('p');
                    errorMsg.className = 'form-error';
                    errorMsg.textContent = 'This field is required';
                    field.parentElement.appendChild(errorMsg);
                }
            } else {
                field.classList.remove('input-error');
                const errorMsg = field.parentElement.querySelector('.error-message');
                if (errorMsg) errorMsg.remove();
            }
        });

        return isValid;
    },

    /**
     * Update step visibility
     */
    updateStepVisibility() {
        document.querySelectorAll('[data-step-content]').forEach(el => {
            const step = parseInt(el.dataset.stepContent);
            if (step === this.state.currentStep) {
                el.classList.remove('hidden');
                el.classList.add('animate-fade-in');
            } else {
                el.classList.add('hidden');
                el.classList.remove('animate-fade-in');
            }
        });
    },

    /**
     * Update progress indicator
     */
    updateProgressIndicator() {
        document.querySelectorAll('[data-progress-step]').forEach(el => {
            const step = parseInt(el.dataset.progressStep);
            el.classList.remove('active', 'completed');
            
            if (step === this.state.currentStep) {
                el.classList.add('active');
            } else if (step < this.state.currentStep) {
                el.classList.add('completed');
            }
        });
    },

    /**
     * Initialize calendar
     */
    initializeCalendar() {
        const calendarEl = document.getElementById('appointment-calendar');
        if (!calendarEl) return;

        // Generate calendar days
        this.generateCalendarDays(new Date());
    },

    /**
     * Generate calendar days
     */
    generateCalendarDays(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        const calendarGrid = document.getElementById('calendar-grid');
        if (!calendarGrid) return;

        calendarGrid.innerHTML = '';

        // Empty slots for days before the first of the month
        for (let i = 0; i < firstDay; i++) {
            const emptySlot = document.createElement('div');
            emptySlot.className = 'calendar-day empty';
            calendarGrid.appendChild(emptySlot);
        }

        // Days of the month
        const today = new Date();
        for (let i = 1; i <= daysInMonth; i++) {
            const dayDate = new Date(year, month, i);
            const isPast = dayDate < new Date(today.setHours(0, 0, 0, 0));
            const isToday = dayDate.toDateString() === new Date().toDateString();
            
            const dayEl = document.createElement('button');
            dayEl.className = `calendar-day ${isPast ? 'disabled' : ''} ${isToday ? 'today' : ''}`;
            dayEl.textContent = i;
            
            if (!isPast) {
                dayEl.addEventListener('click', () => {
                    this.selectDate(dayDate);
                });
            }
            
            calendarGrid.appendChild(dayEl);
        }
    },

    /**
     * Handle date selection
     */
    selectDate(date) {
        this.state.formData.selectedDate = date;
        
        // Update UI
        document.querySelectorAll('.calendar-day').forEach(el => {
            el.classList.remove('selected');
        });
        event.target.classList.add('selected');
        
        // Load available time slots
        this.loadTimeSlots(date);
    },

    /**
     * Load available time slots for selected date
     */
    async loadTimeSlots(date) {
        const slotsContainer = document.getElementById('time-slots');
        if (!slotsContainer) return;

        this.showLoading(slotsContainer);

        try {
            // Mock API call - replace with actual endpoint
            const response = await fetch(`${this.config.apiBaseUrl}/available-slots`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    date: date.toISOString().split('T')[0],
                    doctorId: this.state.selectedDoctor?.id
                })
            });

            const slots = await response.json();
            this.state.availableSlots = slots;
            this.renderTimeSlots(slots);
        } catch (error) {
            console.error('Failed to load time slots:', error);
            this.showError(slotsContainer, 'Failed to load available times. Please try again.');
        }
    },

    /**
     * Render time slots
     */
    renderTimeSlots(slots) {
        const container = document.getElementById('time-slots');
        if (!container) return;

        container.innerHTML = '';

        slots.forEach(slot => {
            const slotEl = document.createElement('button');
            slotEl.className = `time-slot ${slot.available ? '' : 'booked'}`;
            slotEl.textContent = slot.time;
            
            if (slot.available) {
                slotEl.addEventListener('click', () => {
                    this.selectTimeSlot(slot);
                });
            }
            
            container.appendChild(slotEl);
        });
    },

    /**
     * Handle time slot selection
     */
    selectTimeSlot(slot) {
        this.state.formData.selectedTime = slot.time;
        
        // Update UI
        document.querySelectorAll('.time-slot').forEach(el => {
            el.classList.remove('selected');
        });
        event.target.classList.add('selected');
    },

    /**
     * Select consultation type
     */
    selectConsultationType(type) {
        this.state.formData.consultationType = type;
        
        // Update UI
        document.querySelectorAll('[data-consultation-type]').forEach(el => {
            el.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
    },

    /**
     * Select appointment mode
     */
    selectAppointmentMode(mode) {
        this.state.formData.appointmentMode = mode;
        
        // Update UI
        document.querySelectorAll('[data-appointment-mode]').forEach(el => {
            el.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        
        // Update price display
        this.updatePriceDisplay(mode);
    },

    /**
     * Update price display based on mode
     */
    updatePriceDisplay(mode) {
        const prices = {
            'in-person': 120,
            'video': 90,
            'phone': 70
        };
        
        const priceEl = document.getElementById('consultation-price');
        if (priceEl) {
            priceEl.textContent = `${prices[mode]} TND`;
        }
    },

    /**
     * Check availability for a date
     */
    async checkAvailability(date) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/check-availability`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ date })
            });
            
            const data = await response.json();
            this.updateAvailabilityIndicator(data.available);
        } catch (error) {
            console.error('Availability check failed:', error);
        }
    },

    /**
     * Update availability indicator
     */
    updateAvailabilityIndicator(available) {
        const indicator = document.getElementById('availability-indicator');
        if (!indicator) return;

        if (available) {
            indicator.className = 'availability-indicator available';
            indicator.textContent = 'Available';
        } else {
            indicator.className = 'availability-indicator unavailable';
            indicator.textContent = 'Not Available';
        }
    },

    /**
     * Load doctor data
     */
    async loadDoctorData() {
        const doctorId = new URLSearchParams(window.location.search).get('doctor');
        if (!doctorId) return;

        try {
            const response = await fetch(`/api/doctors/${doctorId}`);
            this.state.selectedDoctor = await response.json();
            this.renderDoctorInfo();
        } catch (error) {
            console.error('Failed to load doctor data:', error);
        }
    },

    /**
     * Render doctor information
     */
    renderDoctorInfo() {
        const doctor = this.state.selectedDoctor;
        if (!doctor) return;

        const nameEl = document.getElementById('doctor-name');
        const specialtyEl = document.getElementById('doctor-specialty');
        
        if (nameEl) nameEl.textContent = `Dr. ${doctor.name}`;
        if (specialtyEl) specialtyEl.textContent = doctor.specialty;
    },

    /**
     * Submit booking form
     */
    async submitBooking() {
        if (!this.validateStep(this.state.currentStep)) {
            return;
        }

        this.state.isLoading = true;
        this.showLoading(document.getElementById('booking-form'));

        try {
            const response = await fetch(`${this.config.apiBaseUrl}/book`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.state.formData)
            });

            const result = await response.json();
            
            if (result.success) {
                this.handleBookingSuccess(result);
            } else {
                this.handleBookingError(result);
            }
        } catch (error) {
            console.error('Booking failed:', error);
            this.handleBookingError({ message: 'Network error. Please try again.' });
        } finally {
            this.state.isLoading = false;
        }
    },

    /**
     * Handle successful booking
     */
    handleBookingSuccess(result) {
        // Store booking reference
        localStorage.setItem('lastBooking', JSON.stringify(result));
        
        // Redirect to confirmation page
        window.location.href = `/appointment/confirmation?id=${result.bookingId}`;
    },

    /**
     * Handle booking error
     */
    handleBookingError(result) {
        const errorContainer = document.getElementById('booking-errors');
        if (errorContainer) {
            errorContainer.textContent = result.message || 'Booking failed. Please try again.';
            errorContainer.classList.remove('hidden');
        }
    },

    /**
     * Show loading state
     */
    showLoading(element) {
        if (!element) return;
        element.classList.add('loading');
    },

    /**
     * Hide loading state
     */
    hideLoading(element) {
        if (!element) return;
        element.classList.remove('loading');
    },

    /**
     * Show error message
     */
    showError(element, message) {
        if (!element) return;
        element.innerHTML = `<p class="error-message">${message}</p>`;
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    AppointmentBooking.init();
});

// Export for use in Alpine.js
window.AppointmentBooking = AppointmentBooking;
