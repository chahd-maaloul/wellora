/**
 * Health Analytics JavaScript Module
 * Handles Chart.js initialization and Alpine.js data for analytics dashboards
 */

// Medical color palette - color-blind friendly
const medicalColors = {
    primary: '#00A790',
    primaryLight: 'rgba(0, 167, 144, 0.1)',
    secondary: '#6366f1',
    secondaryLight: 'rgba(99, 102, 241, 0.1)',
    success: '#22c55e',
    successLight: 'rgba(34, 197, 94, 0.1)',
    warning: '#f59e0b',
    warningLight: 'rgba(245, 158, 11, 0.1)',
    danger: '#ef4444',
    dangerLight: 'rgba(239, 68, 68, 0.1)',
    info: '#3b82f6',
    infoLight: 'rgba(59, 130, 246, 0.1)',
    purple: '#a855f7',
    purpleLight: 'rgba(168, 85, 247, 0.1)',
    mood: {
        veryBad: '#ef4444',
        bad: '#f97316',
        neutral: '#eab308',
        good: '#22c55e',
        veryGood: '#00A790'
    }
};

// Chart.js default configuration for medical theme
Chart.defaults.font.family = "'Open Sans', 'Roboto', sans-serif";
Chart.defaults.color = '#6b7280';
Chart.defaults.scale.grid.color = 'rgba(0, 0, 0, 0.05)';

// Patient Analytics Alpine.js Component
document.addEventListener('alpine:init', () => {
    
    Alpine.data('patientAnalytics', () => ({
        // State
        selectedPeriod: '7d',
        activeMetrics: ['energy', 'mood', 'sleep'],
        correlationType: 'exercise',
        healthScore: 78,
        trendDirection: '↗ Amélioration',
        avgEnergy: '7.2',
        avgSleep: '7.4',
        energyTrend: '+5% vs sem. dernière',
        sleepTrend: '+2% vs sem. dernière',
        mostFrequentSymptom: 'Fatigue',
        avgSymptomDuration: '2.3 jours',
        totalEntries: 42,
        goodDays: '76',
        symptomFreeDays: 28,
        medicationAdherence: '87',
        exerciseDays: '64',
        energyWithExercise: '8.4',
        sleepQuality: 'Bonne',
        
        // Medications data
        medications: [
            { id: 1, name: 'Vitamine D', effectiveness: 85, color: medicalColors.primary },
            { id: 2, name: 'Magnésium', effectiveness: 72, color: medicalColors.secondary },
            { id: 3, name: 'Oméga-3', effectiveness: 68, color: medicalColors.success },
        ],
        
        // Triggers data
        triggers: [
            { id: 1, name: 'Stress', description: 'Augmente la fatigue de 23%', impact: 23, icon: 'fa-solid fa-brain' },
            { id: 2, name: 'Sommeil < 6h', description: 'Impact négatif sur humeur', impact: 18, icon: 'fa-solid fa-bed' },
            { id: 3, name: 'Exercice', description: 'Améliore l\'énergie', impact: -15, icon: 'fa-solid fa-person-running' },
            { id: 4, name: 'Caféine', description: 'Peut causer des palpitations', impact: 12, icon: 'fa-solid fa-mug-hot' },
        ],
        
        // Charts instances
        charts: {},
        
        init() {
            this.$nextTick(() => {
                this.initDatePicker();
                this.initCharts();
            });
        },
        
        get healthScoreLabel() {
            if (this.healthScore >= 80) return 'Excellent';
            if (this.healthScore >= 60) return 'Bon';
            if (this.healthScore >= 40) return 'Moyen';
            return 'À améliorer';
        },
        
        initDatePicker() {
            if (typeof flatpickr !== 'undefined') {
                flatpickr(this.$refs.dateRange, {
                    mode: 'range',
                    dateFormat: 'd/m/Y',
                    locale: 'fr',
                    defaultDate: [new Date(Date.now() - 7 * 24 * 60 * 60 * 1000), new Date()],
                    onChange: (selectedDates) => {
                        if (selectedDates.length === 2) {
                            this.updateChartsForDateRange(selectedDates[0], selectedDates[1]);
                        }
                    }
                });
            }
        },
        
        initCharts() {
            this.initTrendsChart();
            this.initSymptomHeatmap();
            this.initMedicationChart();
            this.initCorrelationChart();
            this.initTriggerChart();
        },
        
        initTrendsChart() {
            const ctx = document.getElementById('trendsChart');
            if (!ctx) return;
            
            this.charts.trends = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                    datasets: [
                        {
                            label: 'Énergie',
                            data: [6, 7, 8, 7, 9, 8, 7],
                            borderColor: medicalColors.primary,
                            backgroundColor: medicalColors.primaryLight,
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Humeur',
                            data: [3, 4, 4, 3, 5, 4, 4],
                            borderColor: medicalColors.purple,
                            backgroundColor: medicalColors.purpleLight,
                            fill: false,
                            tension: 0.4,
                            yAxisID: 'y1'
                        },
                        {
                            label: 'Sommeil (h)',
                            data: [7, 6.5, 8, 7.5, 8, 9, 7],
                            borderColor: medicalColors.secondary,
                            backgroundColor: medicalColors.secondaryLight,
                            fill: false,
                            tension: 0.4,
                            yAxisID: 'y2'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1f2937',
                            bodyColor: '#4b5563',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: true
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            min: 0,
                            max: 10,
                            title: {
                                display: true,
                                text: 'Énergie (0-10)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            min: 1,
                            max: 5,
                            grid: {
                                drawOnChartArea: false,
                            },
                            title: {
                                display: true,
                                text: 'Humeur (1-5)'
                            }
                        },
                        y2: {
                            type: 'linear',
                            display: false,
                            min: 0,
                            max: 12
                        }
                    }
                }
            });
        },
        
        initSymptomHeatmap() {
            const ctx = document.getElementById('symptomHeatmapChart');
            if (!ctx) return;
            
            this.charts.symptomHeatmap = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                    datasets: [
                        {
                            label: 'Fatigue',
                            data: [2, 1, 0, 1, 0, 0, 1],
                            backgroundColor: medicalColors.warning
                        },
                        {
                            label: 'Maux de tête',
                            data: [1, 0, 0, 0, 1, 0, 0],
                            backgroundColor: medicalColors.danger
                        },
                        {
                            label: 'Tension',
                            data: [0, 1, 1, 0, 0, 0, 0],
                            backgroundColor: medicalColors.info
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            max: 5,
                            title: {
                                display: true,
                                text: 'Intensité'
                            }
                        }
                    }
                }
            });
        },
        
        initMedicationChart() {
            const ctx = document.getElementById('medicationChart');
            if (!ctx) return;
            
            this.charts.medication = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Pris', 'Oublié', 'Refusé'],
                    datasets: [{
                        data: [87, 10, 3],
                        backgroundColor: [
                            medicalColors.success,
                            medicalColors.warning,
                            medicalColors.danger
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        }
                    }
                }
            });
        },
        
        initCorrelationChart() {
            const ctx = document.getElementById('correlationChart');
            if (!ctx) return;
            
            this.charts.correlation = new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Exercice vs Énergie',
                        data: [
                            { x: 0, y: 5 },
                            { x: 15, y: 6 },
                            { x: 30, y: 7 },
                            { x: 45, y: 8 },
                            { x: 60, y: 8.5 },
                            { x: 20, y: 6.5 },
                            { x: 40, y: 7.5 },
                            { x: 0, y: 4 },
                            { x: 30, y: 7.5 },
                            { x: 50, y: 8 }
                        ],
                        backgroundColor: medicalColors.primary,
                        borderColor: medicalColors.primary,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    return `Exercice: ${context.parsed.x}min, Énergie: ${context.parsed.y}/10`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Minutes d\'exercice'
                            },
                            min: 0,
                            max: 70
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Niveau d\'énergie'
                            },
                            min: 0,
                            max: 10
                        }
                    }
                }
            });
        },
        
        initTriggerChart() {
            const ctx = document.getElementById('triggerChart');
            if (!ctx) return;
            
            this.charts.trigger = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Stress', 'Sommeil < 6h', 'Exercice', 'Caféine'],
                    datasets: [{
                        label: 'Impact sur symptômes (%)',
                        data: [23, 18, -15, 12],
                        backgroundColor: [
                            medicalColors.danger,
                            medicalColors.warning,
                            medicalColors.success,
                            medicalColors.info
                        ],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Impact (%)'
                            },
                            min: -20,
                            max: 30
                        }
                    }
                }
            });
        },
        
        toggleMetric(metric) {
            const index = this.activeMetrics.indexOf(metric);
            if (index > -1) {
                if (this.activeMetrics.length > 1) {
                    this.activeMetrics.splice(index, 1);
                }
            } else {
                this.activeMetrics.push(metric);
            }
            this.updateTrendsChart();
        },
        
        updateTrendsChart() {
            if (!this.charts.trends) return;
            
            const datasets = [];
            if (this.activeMetrics.includes('energy')) {
                datasets.push({
                    label: 'Énergie',
                    data: [6, 7, 8, 7, 9, 8, 7],
                    borderColor: medicalColors.primary,
                    backgroundColor: medicalColors.primaryLight,
                    fill: true,
                    tension: 0.4
                });
            }
            if (this.activeMetrics.includes('mood')) {
                datasets.push({
                    label: 'Humeur',
                    data: [3, 4, 4, 3, 5, 4, 4],
                    borderColor: medicalColors.purple,
                    backgroundColor: medicalColors.purpleLight,
                    fill: false,
                    tension: 0.4
                });
            }
            if (this.activeMetrics.includes('sleep')) {
                datasets.push({
                    label: 'Sommeil',
                    data: [7, 6.5, 8, 7.5, 8, 9, 7],
                    borderColor: medicalColors.secondary,
                    backgroundColor: medicalColors.secondaryLight,
                    fill: false,
                    tension: 0.4
                });
            }
            
            this.charts.trends.data.datasets = datasets;
            this.charts.trends.update();
        },
        
        updatePeriod() {
            // Simulate data update based on period
            const periodMultipliers = {
                '7d': 1,
                '30d': 4,
                '3m': 12,
                '1y': 52
            };
            
            const multiplier = periodMultipliers[this.selectedPeriod] || 1;
            this.totalEntries = Math.round(42 * multiplier / 4);
            this.goodDays = Math.round(76 + (Math.random() * 10 - 5));
            
            this.updateChartsForPeriod();
        },
        
        updateChartsForPeriod() {
            // Update all charts with new period data
            Object.values(this.charts).forEach(chart => {
                if (chart) chart.update('active');
            });
        },
        
        updateChartsForDateRange(start, end) {
            // Update charts based on selected date range
            this.updateChartsForPeriod();
        },
        
        updateCorrelationChart() {
            if (!this.charts.correlation) return;
            
            const correlations = {
                exercise: {
                    label: 'Exercice vs Énergie',
                    xAxis: 'Minutes d\'exercice',
                    yAxis: 'Niveau d\'énergie'
                },
                sleep: {
                    label: 'Sommeil vs Humeur',
                    xAxis: 'Heures de sommeil',
                    yAxis: 'Niveau d\'humeur'
                },
                food: {
                    label: 'Alimentation vs Symptômes',
                    xAxis: 'Qualité alimentation',
                    yAxis: 'Nombre de symptômes'
                }
            };
            
            const config = correlations[this.correlationType];
            this.charts.correlation.data.datasets[0].label = config.label;
            this.charts.correlation.options.scales.x.title.text = config.xAxis;
            this.charts.correlation.options.scales.y.title.text = config.yAxis;
            this.charts.correlation.update();
        },
        
        exportData(format) {
            const formats = {
                pdf: () => this.exportPDF(),
                csv: () => this.exportCSV(),
                png: () => this.exportPNG()
            };
            
            if (formats[format]) {
                formats[format]();
            }
        },
        
        exportPDF() {
            alert('Export PDF en cours de génération...');
            // Implementation would use a PDF generation library
        },
        
        exportCSV() {
            const csvContent = 'data:text/csv;charset=utf-8,' + 
                'Date,Énergie,Humeur,Sommeil\n' +
                '2024-01-15,7,4,7.5\n' +
                '2024-01-16,6,3,6.0\n' +
                '2024-01-17,8,4,8.0\n';
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', 'export-sante.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
        
        exportPNG() {
            Object.entries(this.charts).forEach(([name, chart]) => {
                if (chart) {
                    const link = document.createElement('a');
                    link.download = `graphique-${name}.png`;
                    link.href = chart.toBase64Image();
                    link.click();
                }
            });
        }
    }));
    
    // Doctor Analytics Alpine.js Component
    Alpine.data('doctorAnalytics', () => ({
        // State
        patientSearch: '',
        alertFilter: 'all',
        isRefreshing: false,
        showPatientModal: false,
        selectedPatient: null,
        reportPatient: '',
        reportType: 'summary',
        reportPeriod: '30d',
        treatmentMetric: 'symptoms',
        zoomLevel: 100,
        
        // Stats
        totalPatients: 24,
        criticalAlerts: 3,
        todayAppointments: 8,
        nextAppointment: '14:30 - Dr. Martin',
        reportsGenerated: 12,
        
        // Data
        patients: [
            {
                id: 'P001',
                name: 'Marie Dupont',
                avatar: 'https://ui-avatars.com/api/?name=Marie+Dupont&background=00A790&color=fff',
                healthScore: 85,
                trend: 'improving',
                trendLabel: 'En amélioration',
                alerts: [],
                lastEntry: 'Il y a 2h'
            },
            {
                id: 'P002',
                name: 'Jean Martin',
                avatar: 'https://ui-avatars.com/api/?name=Jean+Martin&background=ef4444&color=fff',
                healthScore: 62,
                trend: 'declining',
                trendLabel: 'En déclin',
                alerts: [
                    { id: 1, severity: 'critical', message: 'Tension élevée', icon: 'fa-heart-pulse' },
                    { id: 2, severity: 'warning', message: 'Médicament oublié', icon: 'fa-pills' }
                ],
                lastEntry: 'Il y a 5h'
            },
            {
                id: 'P003',
                name: 'Sophie Bernard',
                avatar: 'https://ui-avatars.com/api/?name=Sophie+Bernard&background=f59e0b&color=fff',
                healthScore: 73,
                trend: 'stable',
                trendLabel: 'Stable',
                alerts: [
                    { id: 3, severity: 'warning', message: 'Sommeil perturbé', icon: 'fa-bed' }
                ],
                lastEntry: 'Hier'
            }
        ],
        
        recentAlerts: [
            {
                id: 1,
                severity: 'critical',
                patientName: 'Jean Martin',
                message: 'Tension artérielle > 140/90',
                time: 'Il y a 10 min'
            },
            {
                id: 2,
                severity: 'warning',
                patientName: 'Sophie Bernard',
                message: '3 médicaments oubliés cette semaine',
                time: 'Il y a 1h'
            },
            {
                id: 3,
                severity: 'critical',
                patientName: 'Pierre Durand',
                message: 'Symptômes cardiaques rapportés',
                time: 'Il y a 2h'
            }
        ],
        
        charts: {},
        
        init() {
            this.$nextTick(() => {
                this.initTreatmentChart();
            });
        },
        
        get filteredPatients() {
            let filtered = this.patients;
            
            if (this.patientSearch) {
                const search = this.patientSearch.toLowerCase();
                filtered = filtered.filter(p => 
                    p.name.toLowerCase().includes(search) || 
                    p.id.toLowerCase().includes(search)
                );
            }
            
            if (this.alertFilter !== 'all') {
                filtered = filtered.filter(p => {
                    if (this.alertFilter === 'stable') return p.alerts.length === 0;
                    return p.alerts.some(a => a.severity === this.alertFilter);
                });
            }
            
            return filtered;
        },
        
        initTreatmentChart() {
            const ctx = document.getElementById('treatmentEffectivenessChart');
            if (!ctx) return;
            
            this.charts.treatment = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'],
                    datasets: [
                        {
                            label: 'Marie D.',
                            data: [65, 70, 75, 78, 82, 85],
                            borderColor: medicalColors.primary,
                            backgroundColor: medicalColors.primaryLight,
                            fill: false,
                            tension: 0.4
                        },
                        {
                            label: 'Jean M.',
                            data: [70, 68, 65, 62, 60, 62],
                            borderColor: medicalColors.danger,
                            backgroundColor: medicalColors.dangerLight,
                            fill: false,
                            tension: 0.4
                        },
                        {
                            label: 'Sophie B.',
                            data: [68, 70, 71, 72, 72, 73],
                            borderColor: medicalColors.warning,
                            backgroundColor: medicalColors.warningLight,
                            fill: false,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Score de santé'
                            }
                        }
                    }
                }
            });
        },
        
        filterPatients() {
            // Reactive update via computed property
        },
        
        filterByAlerts() {
            // Reactive update via computed property
        },
        
        refreshPatientData() {
            this.isRefreshing = true;
            setTimeout(() => {
                this.isRefreshing = false;
            }, 1000);
        },
        
        viewPatientDetails(patientId) {
            this.selectedPatient = this.patients.find(p => p.id === patientId);
            this.showPatientModal = true;
        },
        
        generateReport(patientId) {
            this.reportPatient = patientId;
            // Navigate to report generator or open modal
            alert(`Génération du rapport pour le patient ${patientId}`);
        },
        
        sendMessage(patientId) {
            alert(`Ouverture de la messagerie pour ${patientId}`);
        },
        
        acknowledgeAlert(alertId) {
            this.recentAlerts = this.recentAlerts.filter(a => a.id !== alertId);
        },
        
        generateQuickReport() {
            if (!this.reportPatient) return;
            alert(`Génération du rapport ${this.reportType} pour ${this.reportPatient}`);
        },
        
        exportAllData(format) {
            alert(`Export de toutes les données en format ${format}`);
        },
        
        printDashboard() {
            window.print();
        },
        
        updateTreatmentChart() {
            if (!this.charts.treatment) return;
            // Update chart based on selected metric
            this.charts.treatment.update();
        },
        
        zoomIn() {
            if (this.zoomLevel < 150) {
                this.zoomLevel += 10;
            }
        },
        
        zoomOut() {
            if (this.zoomLevel > 50) {
                this.zoomLevel -= 10;
            }
        }
    }));
    
    // Report Generator Alpine.js Component
    Alpine.data('reportGenerator', () => ({
        selectedPatient: '',
        patientData: null,
        zoomLevel: 100,
        
        reportConfig: {
            type: 'summary',
            language: 'fr',
            format: 'pdf',
            sections: {
                patientInfo: true,
                vitalSigns: true,
                symptoms: true,
                medications: true,
                charts: true,
                aiInsights: true,
                recommendations: true
            }
        },
        
        patients: [
            { id: 'P001', name: 'Marie Dupont', age: 45, gender: 'F', fileNumber: '2024-001', lastVisit: '15/01/2024', birthDate: '12/05/1979', avatar: 'https://ui-avatars.com/api/?name=Marie+Dupont&background=00A790&color=fff' },
            { id: 'P002', name: 'Jean Martin', age: 52, gender: 'M', fileNumber: '2024-002', lastVisit: '10/01/2024', birthDate: '23/08/1972', avatar: 'https://ui-avatars.com/api/?name=Jean+Martin&background=ef4444&color=fff' },
            { id: 'P003', name: 'Sophie Bernard', age: 38, gender: 'F', fileNumber: '2024-003', lastVisit: '20/01/2024', birthDate: '05/03/1986', avatar: 'https://ui-avatars.com/api/?name=Sophie+Bernard&background=f59e0b&color=fff' }
        ],
        
        init() {
            this.$nextTick(() => {
                this.initDatePickers();
            });
        },
        
        get canGenerate() {
            return this.selectedPatient !== '';
        },
        
        initDatePickers() {
            if (typeof flatpickr !== 'undefined') {
                flatpickr(this.$refs.startDate, {
                    dateFormat: 'd/m/Y',
                    locale: 'fr',
                    defaultDate: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
                });
                
                flatpickr(this.$refs.endDate, {
                    dateFormat: 'd/m/Y',
                    locale: 'fr',
                    defaultDate: new Date()
                });
            }
        },
        
        loadPatientData() {
            this.patientData = this.patients.find(p => p.id === this.selectedPatient);
        },
        
        getReportTitle() {
            const titles = {
                consultation: 'Compte-rendu de Consultation',
                summary: 'Résumé Clinique',
                detailed: 'Rapport Détaillé',
                trends: 'Analyse des Tendances',
                medication: 'Bilan Médicamenteux',
                custom: 'Rapport Personnalisé'
            };
            return titles[this.reportConfig.type] || 'Rapport Médical';
        },
        
        generateReport() {
            if (!this.canGenerate) return;
            
            // Simulate report generation
            const button = document.activeElement;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="mr-2 fa-solid fa-spinner fa-spin"></i> Génération...';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
                alert('Rapport généré avec succès!');
            }, 2000);
        },
        
        loadTemplate() {
            alert('Chargement des modèles...');
        },
        
        saveTemplate() {
            alert('Modèle sauvegardé!');
        },
        
        zoomIn() {
            if (this.zoomLevel < 150) {
                this.zoomLevel += 10;
            }
        },
        
        zoomOut() {
            if (this.zoomLevel > 50) {
                this.zoomLevel -= 10;
            }
        }
    }));
});

// Export functions for global access
window.medicalColors = medicalColors;
