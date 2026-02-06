/**
 * WellCare Connect - Fitness Module JavaScript
 * Patient-facing fitness dashboard functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize fitness dashboard components
    initFitnessDashboard();
    initWorkoutPlanner();
    initExerciseLibrary();
    initWorkoutLog();
    initPerformanceAnalytics();
    initCoachCommunication();
});

/**
 * Initialize fitness dashboard widgets and metrics
 */
function initFitnessDashboard() {
    const dashboardContainer = document.getElementById('fitness-dashboard');
    if (!dashboardContainer) return;

    // Initialize recovery score circular progress
    initRecoveryScore();

    // Initialize goal progress bars
    initGoalProgress();

    // Initialize workout completion checkmarks
    initWorkoutCheckmarks();

    // Emergency stop button handler
    initEmergencyStop();
}

/**
 * Recovery Score Circular Progress Indicator
 */
function initRecoveryScore() {
    const recoveryCanvas = document.getElementById('recovery-score-canvas');
    if (!recoveryCanvas) return;

    const ctx = recoveryCanvas.getContext('2d');
    const score = parseInt(recoveryCanvas.dataset.score) || 75;
    const maxScore = 100;

    // Clear canvas
    ctx.clearRect(0, 0, recoveryCanvas.width, recoveryCanvas.height);

    const centerX = recoveryCanvas.width / 2;
    const centerY = recoveryCanvas.height / 2;
    const radius = Math.min(centerX, centerY) - 10;

    // Draw background circle
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
    ctx.strokeStyle = '#e5e7eb';
    ctx.lineWidth = 12;
    ctx.stroke();

    // Draw progress arc
    const startAngle = -Math.PI / 2;
    const endAngle = startAngle + (2 * Math.PI * (score / maxScore));

    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, startAngle, endAngle);
    ctx.strokeStyle = score >= 70 ? '#00A790' : score >= 40 ? '#f59e0b' : '#ef4444';
    ctx.lineWidth = 12;
    ctx.lineCap = 'round';
    ctx.stroke();

    // Draw score text
    ctx.fillStyle = '#1f2937';
    ctx.font = 'bold 24px system-ui';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(score + '%', centerX, centerY);
}

/**
 * Goal Progress Bars Animation
 */
function initGoalProgress() {
    const progressBars = document.querySelectorAll('.goal-progress-bar');
    progressBars.forEach(bar => {
        const progress = parseInt(bar.dataset.progress) || 0;
        const target = bar.querySelector('.progress-fill');
        if (target) {
            target.style.width = '0%';
            setTimeout(() => {
                target.style.width = progress + '%';
            }, 100);
        }
    });
}

/**
 * Workout Completion Checkmarks
 */
function initWorkoutCheckmarks() {
    const checkmarks = document.querySelectorAll('.workout-complete-btn');
    checkmarks.forEach(btn => {
        btn.addEventListener('click', function() {
            const workoutId = this.dataset.workoutId;
            const workoutCard = this.closest('.workout-card');
            
            // Toggle completion state
            this.classList.toggle('bg-wellcare-500');
            this.classList.toggle('bg-gray-200');
            this.querySelector('svg').classList.toggle('text-white');
            this.querySelector('svg').classList.toggle('text-wellcare-500');

            // Update workout status
            if (workoutCard) {
                workoutCard.classList.toggle('opacity-75');
                workoutCard.classList.toggle('line-through');
            }

            // Show completion feedback
            showNotification('Workout completion updated!', 'success');
        });
    });
}

/**
 * Emergency Stop Button Handler
 */
function initEmergencyStop() {
    const emergencyBtn = document.getElementById('emergency-stop-btn');
    if (!emergencyBtn) return;

    emergencyBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to stop your workout? This will log your current progress and end the session.')) {
            // Log current progress
            showNotification('Workout ended. Progress saved.', 'info');
            
            // Redirect to dashboard
            window.location.href = '/fitness/dashboard';
        }
    });
}

/**
 * Initialize Workout Planner with calendar
 */
function initWorkoutPlanner() {
    const plannerContainer = document.getElementById('workout-planner');
    if (!plannerContainer) return;

    // Initialize calendar events
    initCalendarEvents();

    // Initialize drag and drop
    initDragAndDrop();
}

/**
 * Calendar Events Display
 */
function initCalendarEvents() {
    const calendarDays = document.querySelectorAll('.calendar-day');
    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            const date = this.dataset.date;
            showWorkoutModal(date);
        });
    });
}

/**
 * Drag and Drop Workout Scheduling
 */
function initDragAndDrop() {
    const draggables = document.querySelectorAll('.workout-item');
    const dropZones = document.querySelectorAll('.calendar-day-content');

    draggables.forEach(draggable => {
        draggable.setAttribute('draggable', 'true');
        
        draggable.addEventListener('dragstart', function() {
            this.classList.add('dragging');
        });

        draggable.addEventListener('dragend', function() {
            this.classList.remove('dragging');
        });
    });

    dropZones.forEach(zone => {
        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('bg-wellcare-50');
        });

        zone.addEventListener('dragleave', function() {
            this.classList.remove('bg-wellcare-50');
        });

        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('bg-wellcare-50');
            
            const dragging = document.querySelector('.dragging');
            if (dragging) {
                this.appendChild(dragagging);
                // Update workout date in database
                updateWorkoutDate(dragging.dataset.workoutId, this.dataset.date);
            }
        });
    });
}

/**
 * Initialize Exercise Library
 */
function initExerciseLibrary() {
    const libraryContainer = document.getElementById('exercise-library');
    if (!libraryContainer) return;

    // Initialize filters
    initExerciseFilters();

    // Initialize video player
    initVideoPlayers();

    // Initialize exercise search
    initExerciseSearch();
}

/**
 * Exercise Library Filters
 */
function initExerciseFilters() {
    const categoryFilters = document.querySelectorAll('.exercise-category-filter');
    const difficultyFilters = document.querySelectorAll('.exercise-difficulty-filter');
    const exerciseCards = document.querySelectorAll('.exercise-card');

    const applyFilters = () => {
        const activeCategory = document.querySelector('.exercise-category-filter:checked')?.value || 'all';
        const activeDifficulty = document.querySelector('.exercise-difficulty-filter:checked')?.value || 'all';

        exerciseCards.forEach(card => {
            const cardCategory = card.dataset.category;
            const cardDifficulty = card.dataset.difficulty;

            const categoryMatch = activeCategory === 'all' || cardCategory === activeCategory;
            const difficultyMatch = activeDifficulty === 'all' || cardDifficulty === activeDifficulty;

            card.style.display = categoryMatch && difficultyMatch ? '' : 'none';
        });
    };

    categoryFilters.forEach(filter => {
        filter.addEventListener('change', applyFilters);
    });

    difficultyFilters.forEach(filter => {
        filter.addEventListener('change', applyFilters);
    });
}

/**
 * Video Player for Exercise Demonstrations
 */
function initVideoPlayers() {
    const videoButtons = document.querySelectorAll('.exercise-video-btn');
    
    videoButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const exerciseId = this.dataset.exerciseId;
            openVideoModal(exerciseId);
        });
    });
}

/**
 * Exercise Search Functionality
 */
function initExerciseSearch() {
    const searchInput = document.getElementById('exercise-search');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const exerciseCards = document.querySelectorAll('.exercise-card');

        exerciseCards.forEach(card => {
            const exerciseName = card.querySelector('.exercise-name')?.textContent?.toLowerCase() || '';
            const exerciseDescription = card.querySelector('.exercise-description')?.textContent?.toLowerCase() || '';

            const matches = exerciseName.includes(searchTerm) || exerciseDescription.includes(searchTerm);
            card.style.display = matches ? '' : 'none';
        });
    });
}

/**
 * Initialize Workout Log functionality
 */
function initWorkoutLog() {
    const logContainer = document.getElementById('workout-log');
    if (!logContainer) return;

    // Initialize set/reps tracking
    initSetTracking();

    // Initialize RPE scale
    initRPEScale();

    // Initialize pain/discomfort tracking
    initPainTracking();

    // Initialize form check upload
    initFormCheckUpload();
}

/**
 * Set/Reps/Weight Tracking
 */
function initSetTracking() {
    const setRows = document.querySelectorAll('.set-row');
    
    setRows.forEach(row => {
        const completedCheckbox = row.querySelector('.set-completed');
        if (completedCheckbox) {
            completedCheckbox.addEventListener('change', function() {
                const setData = {
                    exerciseId: row.dataset.exerciseId,
                    setNumber: row.dataset.setNumber,
                    reps: row.querySelector('.reps-input')?.value || 0,
                    weight: row.querySelector('.weight-input')?.value || 0,
                    completed: this.checked
                };
                
                // Auto-save set data
                saveSetData(setData);
            });
        }
    });
}

/**
 * RPE Scale (Rate of Perceived Exertion) 1-10
 */
function initRPEScale() {
    const rpeButtons = document.querySelectorAll('.rpe-btn');
    
    rpeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const rpeValue = this.dataset.rpe;
            const container = this.closest('.rpe-container');
            
            // Update selected state
            container.querySelectorAll('.rpe-btn').forEach(b => {
                b.classList.remove('bg-wellcare-500', 'text-white');
                b.classList.add('bg-gray-200', 'text-gray-700');
            });
            
            this.classList.remove('bg-gray-200', 'text-gray-700');
            this.classList.add('bg-wellcare-500', 'text-white');
            
            // Store value
            container.dataset.rpeValue = rpeValue;
        });
    });
}

/**
 * Pain/Discomfort Tracking
 */
function initPainTracking() {
    const painSelectors = document.querySelectorAll('.pain-location');
    
    painSelectors.forEach(selector => {
        selector.addEventListener('change', function() {
            const painLevel = this.value;
            const painIndicator = document.getElementById('pain-indicator');
            
            if (painIndicator) {
                painIndicator.className = 'w-full h-2 overflow-hidden bg-gray-200 rounded-full';
                
                if (painLevel > 0) {
                    const fillLevel = Math.min(painLevel * 20, 100);
                    painIndicator.innerHTML = `<div class="h-full ${painLevel <= 3 ? 'bg-yellow-500' : painLevel <= 6 ? 'bg-orange-500' : 'bg-red-500'}" style="width: ${fillLevel}%"></div>`;
                }
            }
        });
    });
}

/**
 * Form Check Video Upload
 */
function initFormCheckUpload() {
    const uploadAreas = document.querySelectorAll('.form-check-upload');
    
    uploadAreas.forEach(area => {
        const fileInput = area.querySelector('input[type="file"]');
        
        area.addEventListener('click', () => fileInput?.click());
        
        fileInput?.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Preview uploaded video
                const preview = area.querySelector('.upload-preview');
                if (preview) {
                    preview.innerHTML = `<video src="${URL.createObjectURL(file)}" class="w-full h-32 object-cover rounded"></video>`;
                }
                
                // Auto-upload
                uploadFormCheck(file);
            }
        });
    });
}

/**
 * Initialize Performance Analytics Charts
 */
function initPerformanceAnalytics() {
    const analyticsContainer = document.getElementById('performance-analytics');
    if (!analyticsContainer) return;

    // Load 1RM chart
    load1RMChart();

    // Load volume load chart
    loadVolumeChart();

    // Load heart rate zones chart
    loadHeartRateChart();
}

/**
 * 1RM (One Rep Max) Line Chart
 */
function load1RMChart() {
    const canvas = document.getElementById('1rm-chart');
    if (!canvas || typeof Chart === 'undefined') return;

    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
            datasets: [{
                label: '1RM (kg)',
                data: [80, 85, 82, 90, 92, 95],
                borderColor: '#00A790',
                backgroundColor: 'rgba(0, 167, 144, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 60,
                    max: 100
                }
            }
        }
    });
}

/**
 * Volume Load Bar Chart
 */
function loadVolumeChart() {
    const canvas = document.getElementById('volume-chart');
    if (!canvas || typeof Chart === 'undefined') return;

    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Volume (kg)',
                data: [5000, 4500, 6000, 5500, 7000, 4000, 2000],
                backgroundColor: '#00A790'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/**
 * Heart Rate Zones Doughnut Chart
 */
function loadHeartRateChart() {
    const canvas = document.getElementById('hr-zones-chart');
    if (!canvas || typeof Chart === 'undefined') return;

    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Zone 1 (Recovery)', 'Zone 2 (Fat Burn)', 'Zone 3 (Aerobic)', 'Zone 4 (Anaerobic)', 'Zone 5 (Max)'],
            datasets: [{
                data: [20, 35, 25, 15, 5],
                backgroundColor: [
                    '#94a3b8',
                    '#22c55e',
                    '#facc15',
                    '#f97316',
                    '#ef4444'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
}

/**
 * Initialize Coach Communication
 */
function initCoachCommunication() {
    const coachContainer = document.getElementById('coach-communication');
    if (!coachContainer) return;

    // Initialize chat
    initChat();

    // Initialize video consultation scheduling
    initVideoScheduling();

    // Initialize form check requests
    initFormCheckRequests();
}

/**
 * Real-time Chat Interface
 */
function initChat() {
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-message-btn');
    const messagesContainer = document.getElementById('chat-messages');

    const sendMessage = () => {
        const message = messageInput?.value?.trim();
        if (message) {
            // Add message to UI
            const messageHTML = `
                <div class="flex justify-end mb-4">
                    <div class="bg-wellcare-500 text-white px-4 py-2 rounded-lg max-w-xs">
                        ${message}
                    </div>
                </div>
            `;
            messagesContainer?.insertAdjacentHTML('beforeend', messageHTML);
            
            // Clear input
            messageInput.value = '';
            
            // Scroll to bottom
            messagesContainer?.scrollTo(0, messagesContainer.scrollHeight);
            
            // Simulate coach reply
            setTimeout(() => {
                showCoachReply();
            }, 2000);
        }
    };

    sendBtn?.addEventListener('click', sendMessage);
    messageInput?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
}

/**
 * Video Consultation Scheduling
 */
function initVideoScheduling() {
    const scheduleBtns = document.querySelectorAll('.schedule-video-btn');
    
    scheduleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const consultationType = this.dataset.type;
            openScheduleModal(consultationType);
        });
    });
}

/**
 * Form Check Request Submission
 */
function initFormCheckRequests() {
    const requestBtns = document.querySelectorAll('.request-form-check-btn');
    
    requestBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const exerciseId = this.dataset.exerciseId;
            openFormCheckModal(exerciseId);
        });
    });
}

// ============ Helper Functions ============

/**
 * Show Workout Modal for specific date
 */
function showWorkoutModal(date) {
    const modal = document.getElementById('workout-modal');
    if (modal) {
        modal.classList.remove('hidden');
        // Populate modal with date
        const dateElement = modal.querySelector('[x-text="selectedDate"]');
        if (dateElement) dateElement.textContent = date;
    }
}

/**
 * Update workout date after drag and drop
 */
function updateWorkoutDate(workoutId, newDate) {
    fetch(`/fitness/api/workouts/${newDate}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ workoutId })
    }).then(response => {
        showNotification('Workout rescheduled successfully', 'success');
    }).catch(error => {
        showNotification('Failed to reschedule workout', 'error');
    });
}

/**
 * Open Exercise Video Modal
 */
function openVideoModal(exerciseId) {
    const modal = document.getElementById('video-modal');
    if (modal) {
        modal.classList.remove('hidden');
        // Load video content
    }
}

/**
 * Save Set Data (auto-save)
 */
function saveSetData(setData) {
    fetch('/fitness/api/log', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(setData)
    });
}

/**
 * Upload Form Check Video
 */
function uploadFormCheck(file) {
    const formData = new FormData();
    formData.append('video', file);
    
    fetch('/fitness/api/form-check', {
        method: 'POST',
        body: formData
    }).then(response => {
        showNotification('Form check video uploaded for review', 'success');
    }).catch(error => {
        showNotification('Failed to upload video', 'error');
    });
}

/**
 * Show Coach Reply (simulated)
 */
function showCoachReply() {
    const container = document.getElementById('chat-messages');
    const replyHTML = `
        <div class="flex justify-start mb-4">
            <div class="bg-gray-200 px-4 py-2 rounded-lg max-w-xs">
                Thanks for your message! I'll review your workout and get back to you shortly.
            </div>
        </div>
    `;
    container?.insertAdjacentHTML('beforeend', replyHTML);
    container?.scrollTo(0, container.scrollHeight);
}

/**
 * Open Schedule Video Modal
 */
function openScheduleModal(type) {
    const modal = document.getElementById('schedule-modal');
    if (modal) {
        modal.classList.remove('hidden');
        // Populate with consultation type
    }
}

/**
 * Open Form Check Request Modal
 */
function openFormCheckModal(exerciseId) {
    const modal = document.getElementById('form-check-modal');
    if (modal) {
        modal.classList.remove('hidden');
        // Populate with exercise info
    }
}

/**
 * Show Notification Toast
 */
function showNotification(message, type = 'info') {
    const container = document.getElementById('notification-container');
    if (!container) return;

    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    
    const notification = document.createElement('div');
    notification.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg mb-2`;
    notification.textContent = message;
    
    container.appendChild(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Alpine.js integration for modal states
document.addEventListener('alpine:init', () => {
    Alpine.data('fitnessModals', () => ({
        showVideoModal: false,
        showWorkoutModal: false,
        showScheduleModal: false,
        showFormCheckModal: false,
        selectedDate: '',
        
        openVideoModal(exerciseId) {
            this.showVideoModal = true;
        },
        
        openWorkoutModal(date) {
            this.selectedDate = date;
            this.showWorkoutModal = true;
        },
        
        closeModals() {
            this.showVideoModal = false;
            this.showWorkoutModal = false;
            this.showScheduleModal = false;
            this.showFormCheckModal = false;
        }
    }));

    Alpine.data('adaptiveWorkouts', () => ({
        readinessScore: 85,
        lastWorkout: 'Yesterday',
        lastWorkoutType: 'Upper Body Strength',
        recoveryStatus: 72,
        suggestedFocus: 'Lower Body',
        selectedType: 'strength',
        aiRecommendation: 'Based on your recovery data and upcoming schedule, I recommend a moderate-intensity strength workout focusing on lower body. Your upper body muscles are still recovering from yesterday. Feel free to add 1-2 extra sets if you are feeling energetic.',
        
        selectWorkoutType(type) {
            this.selectedType = type;
        },
        
        generateWorkout() {
            alert('Generating new workout based on your current status');
        },
        
        regenerateWorkout() {
            alert('Regenerating workout with different exercises');
        },
        
        addModification() {
            alert('Open modification dialog');
        }
    }));
});
