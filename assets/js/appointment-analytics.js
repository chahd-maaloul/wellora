/**
 * Appointment Analytics JavaScript Module
 * Handles appointment statistics, clinic performance, and financial analytics
 */

// Medical color palette for analytics
const analyticsColors = {
    primary: '#00A790',
    primaryLight: 'rgba(0, 167, 144, 0.1)',
    secondary: '#6366f1',
    success: '#22c55e',
    successLight: 'rgba(34, 197, 94, 0.1)',
    warning: '#f59e0b',
    warningLight: 'rgba(245, 158, 11, 0.1)',
    danger: '#ef4444',
    dangerLight: 'rgba(239, 68, 68, 0.1)',
    info: '#3b82f6',
    purple: '#8b5cf6',
    cyan: '#06b6d4',
    orange: '#f97316',
};

document.addEventListener('alpine:init', () => {
    
    // ============================================
    // PATIENT APPOINTMENT ANALYTICS
    // ============================================
    Alpine.data('patientAppointmentAnalytics', () => ({
        dateRange: '30d',
        chartPeriod: 'day',
        
        // Summary data
        summary: {
            totalAppointments: 1247,
            attendanceRate: 89,
            cancellationRate: 8,
            newPatients: 342,
        },
        
        // Chart instances
        trendChart: null,
        typesChart: null,
        durationChart: null,
        
        // Sample data
        hours: ['08', '09', '10', '11', '12', '13', '14', '15', '16', '17'],
        days: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
        
        doctorPerformance: [
            { id: 1, name: 'Dr. Martin', specialty: 'Médecine Générale', appointments: 312 },
            { id: 2, name: 'Dr. Bernard', specialty: 'Cardiologie', appointments: 245 },
            { id: 3, name: 'Dr. Petit', specialty: 'Dermatologie', appointments: 198 },
            { id: 4, name: 'Dr. Durand', specialty: 'Pédiatrie', appointments: 176 },
        ],
        
        noShowStats: {
            present: 89,
            noShow: 6,
            cancelled: 5,
        },
        
        cancellationReasons: [
            { label: 'Conflits d\'agenda', percentage: 35 },
            { label: 'Maladie', percentage: 25 },
            { label: 'Oubli', percentage: 20 },
            { label: 'Transport', percentage: 12 },
            { label: 'Autre', percentage: 8 },
        ],
        
        avgDuration: 25,
        overTimePercentage: 12,
        
        specialtyDistribution: [
            { name: 'Médecine Générale', percentage: 35, color: '#00A790' },
            { name: 'Cardiologie', percentage: 18, color: '#6366f1' },
            { name: 'Pédiatrie', percentage: 15, color: '#8b5cf6' },
            { name: 'Dermatologie', percentage: 12, color: '#06b6d4' },
            { name: 'Orthopédie', percentage: 10, color: '#f97316' },
            { name: 'Autres', percentage: 10, color: '#9ca3af' },
        ],
        
        init() {
            this.loadData();
            this.$watch('dateRange', () => this.loadData());
        },
        
        loadData() {
            this.initCharts();
            this.loadHeatmapData();
        },
        
        initCharts() {
            // Appointments Trend Chart
            const trendCtx = document.getElementById('appointmentsTrendChart');
            if (trendCtx) {
                if (this.trendChart) {
                    this.trendChart.destroy();
                }
                this.trendChart = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: this.getTrendLabels(),
                        datasets: [
                            {
                                label: 'Rendez-vous',
                                data: this.getTrendData(),
                                borderColor: analyticsColors.primary,
                                backgroundColor: analyticsColors.primaryLight,
                                fill: true,
                                tension: 0.4,
                            },
                            {
                                label: 'Présences',
                                data: this.getAttendanceData(),
                                borderColor: analyticsColors.success,
                                backgroundColor: 'transparent',
                                tension: 0.4,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                            },
                        },
                    },
                });
            }
            
            // Appointment Types Chart
            const typesCtx = document.getElementById('appointmentTypesChart');
            if (typesCtx) {
                if (this.typesChart) {
                    this.typesChart.destroy();
                }
                this.typesChart = new Chart(typesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Nouvelle consultation', 'Suivi', 'Procédure', 'Téléconsultation', 'Urgence'],
                        datasets: [{
                            data: [25, 35, 15, 20, 5],
                            backgroundColor: [
                                analyticsColors.purple,
                                analyticsColors.cyan,
                                analyticsColors.orange,
                                analyticsColors.success,
                                analyticsColors.danger,
                            ],
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                            },
                        },
                    },
                });
            }
        },
        
        getTrendLabels() {
            const labels = [];
            const days = this.dateRange === '7d' ? 7 : this.dateRange === '30d' ? 30 : 90;
            for (let i = 0; i < days; i++) {
                const date = new Date();
                date.setDate(date.getDate() - (days - i - 1));
                labels.push(date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }));
            }
            return labels;
        },
        
        getTrendData() {
            const data = [];
            const days = this.dateRange === '7d' ? 7 : this.dateRange === '30d' ? 30 : 90;
            for (let i = 0; i < days; i++) {
                data.push(Math.floor(Math.random() * 50) + 30);
            }
            return data;
        },
        
        getAttendanceData() {
            const data = [];
            const days = this.dateRange === '7d' ? 7 : this.dateRange === '30d' ? 30 : 90;
            for (let i = 0; i < days; i++) {
                data.push(Math.floor(Math.random() * 40) + 25);
            }
            return data;
        },
        
        loadHeatmapData() {
            // Heatmap data is generated dynamically
        },
        
        getHeatmapColor(day, hour) {
            const value = Math.floor(Math.random() * 100);
            const intensity = value / 100;
            const r = Math.round(0 + (16 - 0) * (1 - intensity));
            const g = Math.round(167 + (185 - 167) * (1 - intensity));
            const b = Math.round(144 + (100 - 144) * (1 - intensity));
            return `rgb(${r}, ${g}, ${b})`;
        },
        
        getHeatmapTextColor(day, hour) {
            const value = Math.floor(Math.random() * 100);
            return value > 50 ? 'text-white' : 'text-gray-700';
        },
        
        getHeatmapValue(day, hour) {
            return Math.floor(Math.random() * 20);
        },
        
        get maxDoctorAppointments() {
            return Math.max(...this.doctorPerformance.map(d => d.appointments));
        },
        
        exportReport() {
            alert('Export du rapport en cours...');
        },
        
        printReport() {
            window.print();
        },
    }));
    
    // ============================================
    // CLINIC PERFORMANCE ANALYTICS
    // ============================================
    Alpine.data('clinicPerformanceAnalytics', () => ({
        period: 'monthly',
        clinicId: 'all',
        
        kpis: {
            occupancyRate: 78,
            occupancyTrend: 5,
            patientsPerDay: 45,
            patientsTrend: 12,
            avgWaitTime: 18,
            waitTimeTrend: -8,
            satisfactionScore: 4.6,
            satisfactionTrend: 3,
            revenuePerDay: 4500,
            revenueTrend: 15,
        },
        
        patientFlow: [
            { name: 'Prise de RDV', count: 1500 },
            { name: 'Confirmés', count: 1350 },
            { name: 'Présentés', count: 1200 },
            { name: 'Consultés', count: 1150 },
            { name: 'Revenus', count: 450 },
        ],
        
        stageColors: [
            'bg-wellcare-500',
            'bg-blue-500',
            'bg-cyan-500',
            'bg-emerald-500',
            'bg-purple-500',
        ],
        
        peakColors: [
            'bg-emerald-500',
            'bg-emerald-600',
            'bg-amber-500',
            'bg-amber-600',
            'bg-red-500',
        ],
        
        resourceUtilization: [
            { name: 'Salles de consultation', rate: 82, details: '6/7 salles' },
            { name: 'Équipements médicaux', rate: 65, details: '23/35 actifs' },
            { name: 'Staff médical', rate: 88, details: '12/14 disponibles' },
            { name: 'Parking', rate: 45, details: '90/200 places' },
        ],
        
        doctorProductivity: [
            { id: 1, name: 'Dr. Martin', productivity: 92, patients: 145 },
            { id: 2, name: 'Dr. Bernard', productivity: 88, patients: 132 },
            { id: 3, name: 'Dr. Petit', productivity: 95, patients: 158 },
            { id: 4, name: 'Dr. Durand', productivity: 78, patients: 98 },
        ],
        
        roomUtilization: [
            { name: 'Bureau 1', utilization: 95, status: 'busy' },
            { name: 'Bureau 2', utilization: 75, status: 'available' },
            { name: 'Salle d\'attente A', utilization: 60, status: 'available' },
            { name: 'Salle de procédure', utilization: 45, status: 'available' },
        ],
        
        peakHours: [
            { hour: '09', intensity: 95, patients: 42 },
            { hour: '10', intensity: 100, patients: 48 },
            { hour: '11', intensity: 85, patients: 38 },
            { hour: '14', intensity: 70, patients: 32 },
            { hour: '15', intensity: 80, patients: 36 },
        ],
        
        recommendation: 'Ajouter un créneau à 10h pour absorber la demande',
        
        bottlenecks: [
            {
                id: 1,
                name: 'Salle d\'attente',
                description: 'Capacité maximale atteinte à 10h',
                icon: 'fa-users',
                severity: 'medium',
                impact: '15 patients/heure',
            },
            {
                id: 2,
                name: 'Réception',
                description: 'Temps d\'accueil > 5 min',
                icon: 'fa-door-open',
                severity: 'low',
                impact: '8 min/patient',
            },
            {
                id: 3,
                name: 'Équipements',
                description: 'Machine RX surchargée',
                icon: 'fa-stethoscope',
                severity: 'high',
                impact: '20% des RDV',
            },
            {
                id: 4,
                name: 'Staff',
                description: 'Sous-effectif le vendredi',
                icon: 'fa-user-nurse',
                severity: 'medium',
                impact: '25% patients',
            },
        ],
        
        init() {
            this.loadData();
        },
        
        loadData() {
            // Load performance data
        },
        
        runAnalysis() {
            alert('Analyse des goulots d\'étranglement en cours...');
        },
        
        viewSolution(bottleneck) {
            alert('Solutions pour: ' + bottleneck.name);
        },
        
        generateReport() {
            alert('Génération du rapport de performance...');
        },
    }));
    
    // ============================================
    // FINANCIAL ANALYTICS
    // ============================================
    Alpine.data('financialAnalytics', () => ({
        period: 'month',
        revenueView: 'monthly',
        
        summary: {
            totalRevenue: 142500,
            revenuePerPatient: 125,
            outstanding: 28500,
            overdue: 18,
            refunds: 4250,
            refundRate: 3.2,
        },
        
        billing: {
            consultations: 67500,
            procedures: 42000,
            exams: 22500,
            medications: 10500,
            total: 142500,
        },
        
        insurance: {
            claimSuccessRate: 92,
            accepted: 145,
            pending: 23,
            rejected: 12,
            totalClaimed: 125000,
        },
        
        payments: {
            paid: 114000,
            pending: 18500,
            overdue: 10000,
        },
        
        serviceRevenue: [
            { name: 'Consultations', amount: 52000, percentage: 36.5, color: '#00A790' },
            { name: 'Procédures', amount: 38000, percentage: 26.7, color: '#6366f1' },
            { name: 'Examens', amount: 28000, percentage: 19.6, color: '#8b5cf6' },
            { name: 'Téléconsultations', amount: 15500, percentage: 10.9, color: '#06b6d4' },
            { name: 'Autres', amount: 9000, percentage: 6.3, color: '#9ca3af' },
        ],
        
        profitability: [
            { name: 'Consultations', revenue: 52000, costs: 28000, profit: 24000, margin: 46, color: '#00A790', trend: 5 },
            { name: 'Procédures', revenue: 38000, costs: 22000, profit: 16000, margin: 42, color: '#6366f1', trend: 8 },
            { name: 'Examens', revenue: 28000, costs: 18000, profit: 10000, margin: 36, color: '#8b5cf6', trend: -2 },
            { name: 'Téléconsultations', revenue: 15500, costs: 5000, profit: 10500, margin: 68, color: '#06b6d4', trend: 15 },
        ],
        
        init() {
            this.loadData();
        },
        
        loadData() {
            this.initCharts();
        },
        
        initCharts() {
            // Revenue chart
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                new Chart(revenueCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
                        datasets: [{
                            label: 'Revenus (€)',
                            data: [125000, 138000, 142000, 136000, 145000, 142500],
                            backgroundColor: analyticsColors.primary,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                        },
                    },
                });
            }
            
            // Service revenue chart
            const serviceCtx = document.getElementById('serviceRevenueChart');
            if (serviceCtx) {
                new Chart(serviceCtx, {
                    type: 'doughnut',
                    data: {
                        labels: this.serviceRevenue.map(s => s.name),
                        datasets: [{
                            data: this.serviceRevenue.map(s => s.amount),
                            backgroundColor: this.serviceRevenue.map(s => s.color),
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                    },
                });
            }
            
            // Claim status chart
            const claimCtx = document.getElementById('claimStatusChart');
            if (claimCtx) {
                new Chart(claimCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Acceptés', 'En attente', 'Refusés'],
                        datasets: [{
                            data: [this.insurance.accepted, this.insurance.pending, this.insurance.rejected],
                            backgroundColor: [analyticsColors.success, analyticsColors.warning, analyticsColors.danger],
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                    },
                });
            }
        },
        
        exportFinancialReport() {
            alert('Export du rapport financier...');
        },
        
        exportProfitability() {
            alert('Export de la rentabilité...');
        },
    }));
    
    // ============================================
    // QUALITY METRICS ANALYTICS
    // ============================================
    Alpine.data('qualityMetricsAnalytics', () => ({
        period: 'quarter',
        
        globalScore: 85,
        scoreTrend: 5,
        
        satisfactionScore: 92,
        totalReviews: 428,
        
        readmissionRate: 8,
        followUpRate: 78,
        
        satisfactionCriteria: [
            { name: 'Accueil et prise en charge', score: 95 },
            { name: 'Communication du médecin', score: 88 },
            { name: 'Temps d\'attente', score: 72 },
            { name: 'Clarté des explications', score: 91 },
            { name: 'Suivi post-consultation', score: 78 },
            { name: 'Propreté des locaux', score: 96 },
            { name: 'Accessibilité', score: 85 },
        ],
        
        clinicalIndicators: [
            {
                id: 1,
                name: 'Vaccination à jour',
                description: 'Patients avec vaccinations complètes',
                currentValue: 94,
                targetValue: 95,
                progress: 94,
                status: 'progress',
            },
            {
                id: 2,
                name: 'Dépistage cancer',
                description: 'Patients à jour dans les dépistages',
                currentValue: 78,
                targetValue: 80,
                progress: 78,
                status: 'progress',
            },
            {
                id: 3,
                name: 'Gestion chronique',
                description: 'Diabétiques sous traitement',
                currentValue: 88,
                targetValue: 85,
                progress: 88,
                status: 'achieved',
            },
            {
                id: 4,
                name: 'Antibiotiques',
                description: 'Prescriptions conformes aux guidelines',
                currentValue: 82,
                targetValue: 90,
                progress: 82,
                status: 'progress',
            },
            {
                id: 5,
                name: 'Hospitalisations évitables',
                description: 'Réduction des admissions urgentes',
                currentValue: 12,
                targetValue: 10,
                progress: 83,
                status: 'missed',
            },
            {
                id: 6,
                name: 'Satisfaction globale',
                description: 'Score de satisfaction patient',
                currentValue: 92,
                targetValue: 90,
                progress: 92,
                status: 'achieved',
            },
        ],
        
        recentFeedback: [
            {
                id: 1,
                patientName: 'Marie Dupont',
                rating: 5,
                comment: 'Excellent accueil et prise en charge rapide. Le médecin a été très à l\'écoute.',
                date: '02/02/2024',
            },
            {
                id: 2,
                patientName: 'Jean Martin',
                rating: 4,
                comment: 'Bonne consultation mais temps d\'attente un peu long.',
                date: '01/02/2024',
            },
            {
                id: 3,
                patientName: 'Sophie Bernard',
                rating: 5,
                comment: 'Professionnalisme impeccable. Je recommande.',
                date: '31/01/2024',
            },
        ],
        
        improvementActions: [
            {
                id: 1,
                title: 'Réduire le temps d\'attente',
                responsible: 'Dr. Martin',
                status: 'in-progress',
                progress: 65,
                deadline: '15/03/2024',
            },
            {
                id: 2,
                title: 'Formation équipe',
                responsible: 'Direction',
                status: 'pending',
                progress: 0,
                deadline: '01/04/2024',
            },
            {
                id: 3,
                title: 'Mise à jour équipements',
                responsible: 'Dr. Bernard',
                status: 'completed',
                progress: 100,
                deadline: '15/01/2024',
            },
            {
                id: 4,
                title: 'Enquête satisfaction',
                responsible: 'Qualité',
                status: 'delayed',
                progress: 40,
                deadline: '01/02/2024',
            },
        ],
        
        init() {
            this.loadData();
        },
        
        loadData() {
            this.initCharts();
        },
        
        initCharts() {
            // Quality radar chart
            const radarCtx = document.getElementById('qualityRadarChart');
            if (radarCtx) {
                new Chart(radarCtx, {
                    type: 'radar',
                    data: {
                        labels: ['Sécurité', 'Efficacité', 'Patient-Centeredness', 'Temps', 'Équité', 'Intégration'],
                        datasets: [{
                            label: 'Score actuel',
                            data: [88, 82, 90, 72, 85, 78],
                            backgroundColor: analyticsColors.primaryLight,
                            borderColor: analyticsColors.primary,
                            pointBackgroundColor: analyticsColors.primary,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: {
                                beginAtZero: true,
                                max: 100,
                            },
                        },
                    },
                });
            }
        },
        
        scoreColor(score) {
            if (score >= 80) return '#22c55e';
            if (score >= 60) return '#f59e0b';
            return '#ef4444';
        },
        
        scoreTextColor(score) {
            if (score >= 80) return 'text-emerald-600';
            if (score >= 60) return 'text-amber-600';
            return 'text-red-600';
        },
        
        scoreBgColor(score) {
            if (score >= 80) return 'bg-emerald-500';
            if (score >= 60) return 'bg-amber-500';
            return 'bg-red-500';
        },
        
        generateQualityReport() {
            alert('Génération du rapport qualité...');
        },
        
        addAction() {
            alert('Ajouter une nouvelle action d\'amélioration');
        },
    }));
});
