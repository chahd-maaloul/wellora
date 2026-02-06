// Coach Module JavaScript - Alpine.js functionality

// Coach Dashboard Functionality
function coachDashboard() {
    return {
        // Stats
        stats: {
            totalClients: 24,
            activePrograms: 18,
            sessionsThisWeek: 47,
            pendingReviews: 5
        },
        
        // Activity Feed
        activities: [],
        
        // Upcoming Sessions
        upcomingSessions: [],
        
        // Quick Actions
        showQuickActions: false,
        
        init() {
            console.log('Coach Dashboard initialized');
            this.loadActivities();
            this.loadSessions();
        },
        
        loadActivities() {
            // Simulate loading activities
            this.activities = [
                {
                    id: 1,
                    type: 'workout_completed',
                    client: 'Sarah Johnson',
                    message: 'completed Upper Body Strength workout',
                    time: '2 hours ago',
                    icon: 'dumbbell',
                    color: 'green'
                },
                {
                    id: 2,
                    type: 'goal_achieved',
                    client: 'Mike Chen',
                    message: 'achieved goal: Run 5K under 25 minutes',
                    time: '4 hours ago',
                    icon: 'trophy',
                    color: 'amber'
                },
                {
                    id: 3,
                    type: 'message_received',
                    client: 'Emma Wilson',
                    message: 'sent a question about recovery',
                    time: '5 hours ago',
                    icon: 'comment',
                    color: 'blue'
                },
                {
                    id: 4,
                    type: 'photo_uploaded',
                    client: 'James Rodriguez',
                    message: 'uploaded progress photos',
                    time: 'Yesterday',
                    icon: 'camera',
                    color: 'purple'
                }
            ];
        },
        
        loadSessions() {
            // Simulate loading sessions
            const today = new Date();
            this.upcomingSessions = [
                {
                    id: 1,
                    client: 'Sarah Johnson',
                    time: '10:00 AM',
                    type: 'Video Consultation',
                    duration: '30 min',
                    status: 'confirmed'
                },
                {
                    id: 2,
                    client: 'Mike Chen',
                    time: '11:30 AM',
                    type: 'Program Review',
                    duration: '45 min',
                    status: 'confirmed'
                },
                {
                    id: 3,
                    client: 'Emma Wilson',
                    time: '2:00 PM',
                    type: 'In-Person Session',
                    duration: '60 min',
                    status: 'pending'
                }
            ];
        },
        
        startSession(sessionId) {
            console.log('Starting session:', sessionId);
            // Navigate to consultation room
            window.location.href = '/teleconsultation/consultation-room';
        },
        
        reviewClient(clientId) {
            console.log('Reviewing client:', clientId);
            // Navigate to client detail
            window.location.href = '/coach/clients/' + clientId;
        }
    };
}

// Client Management Functionality
function clientManagement() {
    return {
        clients: [],
        searchQuery: '',
        filterStatus: 'all',
        filterGoal: 'all',
        sortBy: 'name',
        sortOrder: 'asc',
        viewMode: 'grid',
        
        init() {
            console.log('Client Management initialized');
            this.loadClients();
        },
        
        loadClients() {
            // Simulate loading clients
            this.clients = [
                {
                    id: 1,
                    name: 'Sarah Johnson',
                    avatar: 'SJ',
                    email: 'sarah.j@email.com',
                    phone: '+1 234-567-8901',
                    status: 'active',
                    goal: 'Weight Loss',
                    startDate: '2024-01-15',
                    sessionsCompleted: 24,
                    adherenceRate: 92,
                    progress: 78,
                    nextSession: '2024-02-20',
                    lastActivity: 'Today'
                },
                {
                    id: 2,
                    name: 'Mike Chen',
                    avatar: 'MC',
                    email: 'mike.chen@email.com',
                    phone: '+1 234-567-8902',
                    status: 'active',
                    goal: 'Muscle Gain',
                    startDate: '2024-02-01',
                    sessionsCompleted: 12,
                    adherenceRate: 85,
                    progress: 45,
                    nextSession: '2024-02-21',
                    lastActivity: 'Yesterday'
                },
                {
                    id: 3,
                    name: 'Emma Wilson',
                    avatar: 'EW',
                    email: 'emma.w@email.com',
                    phone: '+1 234-567-8903',
                    status: 'active',
                    goal: 'Marathon Training',
                    startDate: '2024-01-20',
                    sessionsCompleted: 32,
                    adherenceRate: 95,
                    progress: 62,
                    nextSession: '2024-02-22',
                    lastActivity: 'Today'
                },
                {
                    id: 4,
                    name: 'James Rodriguez',
                    avatar: 'JR',
                    email: 'james.r@email.com',
                    phone: '+1 234-567-8904',
                    status: 'paused',
                    goal: 'General Fitness',
                    startDate: '2023-12-01',
                    sessionsCompleted: 18,
                    adherenceRate: 70,
                    progress: 55,
                    nextSession: '2024-03-01',
                    lastActivity: '3 days ago'
                },
                {
                    id: 5,
                    name: 'Lisa Park',
                    avatar: 'LP',
                    email: 'lisa.park@email.com',
                    phone: '+1 234-567-8905',
                    status: 'active',
                    goal: 'Flexibility',
                    startDate: '2024-02-10',
                    sessionsCompleted: 8,
                    adherenceRate: 88,
                    progress: 30,
                    nextSession: '2024-02-23',
                    lastActivity: 'Today'
                }
            ];
        },
        
        get filteredClients() {
            let result = this.clients;
            
            // Filter by search query
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                result = result.filter(c => 
                    c.name.toLowerCase().includes(query) ||
                    c.email.toLowerCase().includes(query) ||
                    c.goal.toLowerCase().includes(query)
                );
            }
            
            // Filter by status
            if (this.filterStatus !== 'all') {
                result = result.filter(c => c.status === this.filterStatus);
            }
            
            // Filter by goal
            if (this.filterGoal !== 'all') {
                result = result.filter(c => c.goal.toLowerCase() === this.filterGoal.toLowerCase());
            }
            
            // Sort
            result.sort((a, b) => {
                let valA = a[this.sortBy];
                let valB = b[this.sortBy];
                
                if (typeof valA === 'string') {
                    valA = valA.toLowerCase();
                    valB = valB.toLowerCase();
                }
                
                if (valA < valB) return this.sortOrder === 'asc' ? -1 : 1;
                if (valA > valB) return this.sortOrder === 'asc' ? 1 : -1;
                return 0;
            });
            
            return result;
        },
        
        toggleSort(field) {
            if (this.sortBy === field) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = field;
                this.sortOrder = 'asc';
            }
        },
        
        viewClient(clientId) {
            window.location.href = '/coach/clients/' + clientId;
        },
        
        messageClient(clientId) {
            window.location.href = '/coach/messages?client=' + clientId;
        }
    };
}

// Program Designer Functionality
function programDesigner() {
    return {
        programs: [],
        selectedProgram: null,
        exercises: [],
        categories: [],
        searchQuery: '',
        selectedCategory: 'all',
        
        init() {
            console.log('Program Designer initialized');
            this.loadPrograms();
            this.loadExercises();
        },
        
        loadPrograms() {
            this.programs = [
                {
                    id: 1,
                    name: '12-Week Muscle Builder',
                    description: 'Comprehensive strength training program',
                    duration: '12 weeks',
                    difficulty: 'Intermediate',
                    clients: 8,
                    sessions: 36,
                    rating: 4.8,
                    status: 'active'
                },
                {
                    id: 2,
                    name: 'Fat Loss Bootcamp',
                    description: 'High-intensity fat burning program',
                    duration: '8 weeks',
                    difficulty: 'Advanced',
                    clients: 12,
                    sessions: 24,
                    rating: 4.6,
                    status: 'active'
                },
                {
                    id: 3,
                    name: 'Beginner Fitness',
                    description: 'Perfect for fitness newcomers',
                    duration: '6 weeks',
                    difficulty: 'Beginner',
                    clients: 15,
                    sessions: 18,
                    rating: 4.9,
                    status: 'active'
                }
            ];
        },
        
        loadExercises() {
            this.categories = [
                { id: 'all', name: 'All Exercises', icon: 'th' },
                { id: 'chest', name: 'Chest', icon: 'person-through-window' },
                { id: 'back', name: 'Back', icon: 'person-rays' },
                { id: 'shoulders', name: 'Shoulders', icon: 'person-rays' },
                { id: 'arms', name: 'Arms', icon: 'hand' },
                { id: 'legs', name: 'Legs', icon: 'person-walking' },
                { id: 'core', name: 'Core', icon: 'child' },
                { id: 'cardio', name: 'Cardio', icon: 'heart-pulse' }
            ];
            
            this.exercises = [
                {
                    id: 1,
                    name: 'Barbell Bench Press',
                    category: 'chest',
                    difficulty: 'Intermediate',
                    equipment: ['Barbell', 'Bench'],
                    muscles: ['Chest', 'Triceps', 'Front Delts'],
                    videoUrl: '#',
                    instructions: 'Lie on bench, grip bar slightly wider than shoulder width...'
                },
                {
                    id: 2,
                    name: 'Pull-ups',
                    category: 'back',
                    difficulty: 'Intermediate',
                    equipment: ['Pull-up Bar'],
                    muscles: ['Back', 'Biceps', 'Rear Delts'],
                    videoUrl: '#',
                    instructions: 'Grip bar slightly wider than shoulder width...'
                },
                {
                    id: 3,
                    name: 'Squats',
                    category: 'legs',
                    difficulty: 'Beginner',
                    equipment: ['Barbell', 'Squat Rack'],
                    muscles: ['Quadriceps', 'Glutes', 'Hamstrings'],
                    videoUrl: '#',
                    instructions: 'Position bar on upper back, feet shoulder width apart...'
                }
            ];
        },
        
        get filteredExercises() {
            let result = this.exercises;
            
            if (this.selectedCategory !== 'all') {
                result = result.filter(e => e.category === this.selectedCategory);
            }
            
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                result = result.filter(e => 
                    e.name.toLowerCase().includes(query) ||
                    e.muscles.some(m => m.toLowerCase().includes(query))
                );
            }
            
            return result;
        },
        
        createProgram() {
            console.log('Creating new program');
            this.selectedProgram = { id: null, name: '', weeks: [] };
        },
        
        editProgram(programId) {
            console.log('Editing program:', programId);
            this.selectedProgram = this.programs.find(p => p.id === programId);
        },
        
        addExerciseToProgram(exercise) {
            console.log('Adding exercise to program:', exercise.name);
        },
        
        saveProgram() {
            console.log('Saving program');
        }
    };
}

// Progress Monitoring Functionality
function progressMonitoring() {
    return {
        clients: [],
        analytics: {},
        timeRange: 'month',
        filterStatus: 'all',
        
        init() {
            console.log('Progress Monitoring initialized');
            this.loadData();
        },
        
        loadData() {
            this.analytics = {
                totalWorkoutsCompleted: 342,
                totalHoursTrained: 168,
                avgSessionDuration: '45 min',
                caloriesBurned: 45600,
                improvementRate: 23
            };
            
            this.clients = [
                {
                    id: 1,
                    name: 'Sarah Johnson',
                    avatar: 'SJ',
                    goal: 'Weight Loss - Phase 2',
                    progress: 78,
                    adherenceRate: 92,
                    nextMilestone: 'Reach 170 lbs',
                    daysRemaining: 14,
                    trend: 'up'
                },
                {
                    id: 2,
                    name: 'Mike Chen',
                    avatar: 'MC',
                    goal: 'Strength Building',
                    progress: 45,
                    adherenceRate: 85,
                    nextMilestone: 'Bench Press 200 lbs',
                    daysRemaining: 28,
                    trend: 'up'
                },
                {
                    id: 3,
                    name: 'Emma Wilson',
                    avatar: 'EW',
                    goal: 'Marathon Prep',
                    progress: 62,
                    adherenceRate: 95,
                    nextMilestone: 'Complete 15K run',
                    daysRemaining: 7,
                    trend: 'up'
                },
                {
                    id: 4,
                    name: 'James Rodriguez',
                    avatar: 'JR',
                    goal: 'General Fitness',
                    progress: 55,
                    adherenceRate: 70,
                    nextMilestone: '5K under 25 min',
                    daysRemaining: 21,
                    trend: 'down'
                }
            ];
        },
        
        generateReport() {
            console.log('Generating report for:', this.timeRange);
        }
    };
}

// Communication Hub Functionality
function communicationHub() {
    return {
        conversations: [],
        selectedConversationId: null,
        searchQuery: '',
        newMessage: '',
        
        init() {
            console.log('Communication Hub initialized');
            this.loadConversations();
        },
        
        loadConversations() {
            this.conversations = [
                {
                    id: 1,
                    client: 'Sarah Johnson',
                    avatar: 'SJ',
                    lastMessage: 'Thanks for the workout update! I will try...',
                    time: '2m ago',
                    unread: 2,
                    online: true
                },
                {
                    id: 2,
                    client: 'Mike Chen',
                    avatar: 'MC',
                    lastMessage: 'The new program looks great!',
                    time: '1h ago',
                    unread: 0,
                    online: false
                },
                {
                    id: 3,
                    client: 'Emma Wilson',
                    avatar: 'EW',
                    lastMessage: 'Quick question about tomorrow\'s session',
                    time: '3h ago',
                    unread: 1,
                    online: true
                },
                {
                    id: 4,
                    client: 'James Rodriguez',
                    avatar: 'JR',
                    lastMessage: 'I need to reschedule our session',
                    time: 'Yesterday',
                    unread: 3,
                    online: false
                }
            ];
            
            if (this.conversations.length > 0) {
                this.selectedConversationId = this.conversations[0].id;
            }
        },
        
        selectConversation(id) {
            this.selectedConversationId = id;
            // Mark as read
            const conv = this.conversations.find(c => c.id === id);
            if (conv) {
                conv.unread = 0;
            }
        },
        
        sendMessage() {
            if (this.newMessage.trim()) {
                console.log('Sending message:', this.newMessage);
                this.newMessage = '';
            }
        },
        
        openBroadcastModal() {
            console.log('Opening broadcast modal');
        }
    };
}

// Reporting Tools Functionality
function reportingTools() {
    return {
        stats: {},
        clients: [],
        metrics: [],
        recentReports: [],
        scheduledReports: [],
        reportType: 'individual',
        dateRange: {
            start: '',
            end: ''
        },
        selectedClient: '',
        selectedMetrics: [],
        exportFormat: 'pdf',
        
        init() {
            console.log('Reporting Tools initialized');
            this.loadData();
        },
        
        loadData() {
            const today = new Date();
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
            
            this.stats = {
                activeClients: 24,
                sessionsThisMonth: 156,
                completionRate: 87,
                avgAdherence: 82,
                goalsAchieved: 18,
                goalsInProgress: 32,
                newClientsThisMonth: 4
            };
            
            this.clients = [
                { id: 1, name: 'Sarah Johnson' },
                { id: 2, name: 'Mike Chen' },
                { id: 3, name: 'Emma Wilson' },
                { id: 4, name: 'James Rodriguez' }
            ];
            
            this.metrics = [
                { id: 'adherence', name: 'Adherence Rate' },
                { id: 'progress', name: 'Progress Tracking' },
                { id: 'strength', name: 'Strength Gains' },
                { id: 'cardio', name: 'Cardio Metrics' },
                { id: 'bodycomp', name: 'Body Composition' },
                { id: 'mood', name: 'Mood & Energy' }
            ];
            
            this.recentReports = [
                {
                    title: 'Monthly Progress Report - Sarah',
                    date: 'Feb 15, 2024',
                    type: 'pdf',
                    size: '2.4 MB'
                },
                {
                    title: 'Q4 Cohort Analysis',
                    date: 'Feb 10, 2024',
                    type: 'excel',
                    size: '5.1 MB'
                },
                {
                    title: 'Program Effectiveness Review',
                    date: 'Feb 5, 2024',
                    type: 'pdf',
                    size: '3.8 MB'
                }
            ];
            
            this.scheduledReports = [
                {
                    name: 'Weekly Client Summary',
                    frequency: 'Every Monday',
                    status: 'active'
                },
                {
                    name: 'Monthly Revenue Report',
                    frequency: '1st of month',
                    status: 'active'
                },
                {
                    name: 'Adherence Analysis',
                    frequency: 'Weekly',
                    status: 'paused'
                }
            ];
            
            this.dateRange.start = lastMonth.toISOString().split('T')[0];
            this.dateRange.end = today.toISOString().split('T')[0];
        },
        
        generateReport() {
            console.log('Generating report:', {
                type: this.reportType,
                dateRange: this.dateRange,
                client: this.selectedClient,
                metrics: this.selectedMetrics,
                format: this.exportFormat
            });
        }
    };
}

// Export functionality for use in templates
window.coachFunctions = {
    dashboard: coachDashboard,
    clientManagement: clientManagement,
    programDesigner: programDesigner,
    progressMonitoring: progressMonitoring,
    communicationHub: communicationHub,
    reportingTools: reportingTools
};
