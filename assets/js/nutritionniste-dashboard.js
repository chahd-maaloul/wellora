// Nutritionniste Dashboard - Professional Clinical Tools
// This file handles all interactive functionality for the nutritionist back office

// ============================================
// NUTRITIONNISTE DASHBOARD
// ============================================

function nutritionnisteDashboard() {
    return {
        globalSearch: '',
        showQuickActions: false,
        activeTab: 'overview',
        
        stats: {
            activePatients: 47,
            todayConsultations: 8,
            activePlans: 32,
            pendingReviews: 5,
            unreadMessages: 12
        },
        
        todayConsultations: [
            { id: 1, patientId: 1, patientName: 'Marie Lambert', time: '09:00', type: 'Consultation initiale', status: 'completed', statusDisplay: 'Terminé' },
            { id: 2, patientId: 2, patientName: 'Jean Dupont', time: '10:30', type: 'Suivi mensuel', status: 'in-progress', statusDisplay: 'En cours' },
            { id: 3, patientId: 3, patientName: 'Sophie Martin', time: '11:30', type: 'Téléconsultation', status: 'pending', statusDisplay: 'En attente' },
            { id: 4, patientId: 4, patientName: 'Pierre Durand', time: '14:00', type: 'Suivi hebdomadaire', status: 'pending', statusDisplay: 'En attente' },
            { id: 5, patientId: 5, patientName: 'Émilie Dubois', time: '15:30', type: 'Consultation de contrôle', status: 'pending', statusDisplay: 'En attente' },
        ],
        
        recentMessages: [
            { id: 1, patientName: 'Marie Lambert', initials: 'ML', preview: 'Bonjour Docteur, j\'ai une question sur mon plan...', time: '10 min', unread: true },
            { id: 2, patientName: 'Jean Dupont', initials: 'JD', preview: 'Merci pour les modifications du plan!', time: '1h', unread: false },
            { id: 3, patientName: 'Sophie Martin', initials: 'SM', preview: 'Voici mes mesures de la semaine', time: '2h', unread: true },
            { id: 4, patientName: 'Pierre Durand', initials: 'PD', preview: 'Je voulais vous remercier pour...', time: '5h', unread: false },
        ],
        
        recentPatients: [
            { id: 1, name: 'Marie Lambert', initials: 'ML', age: 34, goal: 'Perte de poids', progress: 75, status: 'active', statusDisplay: 'Actif' },
            { id: 2, name: 'Jean Dupont', initials: 'JD', age: 45, goal: 'Gestion diabète', progress: 60, status: 'active', statusDisplay: 'Actif' },
            { id: 3, name: 'Sophie Martin', initials: 'SM', age: 28, goal: 'Prise de masse', progress: 40, status: 'pending', statusDisplay: 'En attente' },
            { id: 4, name: 'Pierre Durand', initials: 'PD', age: 52, goal: 'Cardiovasculaire', progress: 85, status: 'active', statusDisplay: 'Actif' },
            { id: 5, name: 'Émilie Dubois', initials: 'ED', age: 31, goal: 'Équilibre alimentaire', progress: 50, status: 'active', statusDisplay: 'Actif' },
        ],
        
        activities: [
            { id: 1, user: 'Marie Lambert', action: 'a complété son journal alimentaire', type: 'food_log', time: 'Il y a 15 min' },
            { id: 2, user: 'Jean Dupont', action: 'a demandé une modification de son plan', type: 'plan_request', time: 'Il y a 1h' },
            { id: 3, user: 'Sophie Martin', action: 'a envoyé ses mesures hebdomadaires', type: 'measurements', time: 'Il y a 2h' },
            { id: 4, user: 'Dr. Dubois (Médecin)', action: 'a partagé des résultats de laboratoire pour Pierre Durand', type: 'lab_results', time: 'Il y a 3h' },
            { id: 5, user: 'Pierre Durand', action: 'a noté une amélioration de son énergie', type: 'progress_note', time: 'Il y a 5h' },
        ],
        
        init() {
            this.loadFromStorage();
        },
        
        loadFromStorage() {
            // Load dashboard state if needed
        },
        
        getConsultationTypeColor(type) {
            const colors = {
                'Consultation initiale': 'bg-blue-500',
                'Suivi mensuel': 'bg-green-500',
                'Suivi hebdomadaire': 'bg-green-500',
                'Téléconsultation': 'bg-purple-500',
                'Consultation de contrôle': 'bg-amber-500'
            };
            return colors[type] || 'bg-gray-500';
        },
        
        getActivityIconClass(type) {
            const classes = {
                'food_log': 'bg-green-500',
                'plan_request': 'bg-amber-500',
                'measurements': 'bg-blue-500',
                'lab_results': 'bg-purple-500',
                'progress_note': 'bg-green-500'
            };
            return classes[type] || 'bg-gray-500';
        },
        
        getActivityIcon(type) {
            const icons = {
                'food_log': 'fas fa-utensils',
                'plan_request': 'fas fa-clipboard-list',
                'measurements': 'fas fa-ruler',
                'lab_results': 'fas fa-flask',
                'progress_note': 'fas fa-chart-line'
            };
            return icons[type] || 'fas fa-circle';
        },
        
        viewPatient(patientId) {
            window.location.href = '/nutritionniste/patient/' + patientId;
        },
        
        navigateTo(page) {
            const routes = {
                'new-patient': '/nutritionniste/patients/new',
                'meal-plan': '/nutritionniste/meal-plan/new',
                'analysis': '/nutritionniste/analysis',
                'reports': '/nutritionniste/reports'
            };
            if (routes[page]) {
                window.location.href = routes[page];
            }
        }
    };
}

// ============================================
// PATIENT LIST
// ============================================

function patientList() {
    return {
        search: '',
        filterStatus: '',
        filterGoal: '',
        sortBy: 'name',
        showNewPatientModal: false,
        
        stats: {
            total: 47,
            active: 38,
            pending: 7,
            inactive: 2,
            newThisMonth: 5
        },
        
        patients: [
            { id: 1, name: 'Marie Lambert', initials: 'ML', age: 34, gender: 'Femme', goal: 'Perte de poids', progress: 75, status: 'active', statusDisplay: 'Actif', bmi: 24.2, consultations: 8, activeDays: 120 },
            { id: 2, name: 'Jean Dupont', initials: 'JD', age: 45, gender: 'Homme', goal: 'Gestion diabète', progress: 60, status: 'active', statusDisplay: 'Actif', bmi: 28.1, consultations: 12, activeDays: 200 },
            { id: 3, name: 'Sophie Martin', initials: 'SM', age: 28, gender: 'Femme', goal: 'Prise de masse', progress: 40, status: 'pending', statusDisplay: 'En attente', bmi: 21.5, consultations: 4, activeDays: 45 },
            { id: 4, name: 'Pierre Durand', initials: 'PD', age: 52, gender: 'Homme', goal: 'Cardiovasculaire', progress: 85, status: 'active', statusDisplay: 'Actif', bmi: 26.8, consultations: 15, activeDays: 300 },
            { id: 5, name: 'Émilie Dubois', initials: 'ED', age: 31, gender: 'Femme', goal: 'Équilibre alimentaire', progress: 50, status: 'active', statusDisplay: 'Actif', bmi: 23.4, consultations: 6, activeDays: 90 },
            { id: 6, name: 'Thomas Bernard', initials: 'TB', age: 38, gender: 'Homme', goal: 'Perte de poids', progress: 30, status: 'pending', statusDisplay: 'En attente', bmi: 29.5, consultations: 3, activeDays: 30 },
        ],
        
        get filteredPatients() {
            return this.patients.filter(patient => {
                const matchesSearch = !this.search || 
                    patient.name.toLowerCase().includes(this.search.toLowerCase());
                const matchesStatus = !this.filterStatus || patient.status === this.filterStatus;
                const matchesGoal = !this.filterGoal || patient.goal.toLowerCase().replace(' ', '-') === this.filterGoal;
                return matchesSearch && matchesStatus && matchesGoal;
            }).sort((a, b) => {
                if (this.sortBy === 'name') return a.name.localeCompare(b.name);
                if (this.sortBy === 'progress') return b.progress - a.progress;
                return 0;
            });
        },
        
        viewPatient(patientId) {
            window.location.href = '/nutritionniste/patient/' + patientId;
        },
        
        messagePatient(patientId) {
            window.location.href = '/nutritionniste/messages?patient=' + patientId;
        },
        
        scheduleAppointment(patientId) {
            window.location.href = '/nutritionniste/appointment/new?patient=' + patientId;
        },
        
        viewPlan(patientId) {
            window.location.href = '/nutritionniste/plan/' + patientId;
        },
        
        exportPatients() {
            alert('Export des patients en cours...');
        }
    };
}

// ============================================
// MEAL PLAN BUILDER
// ============================================

function mealPlanBuilder() {
    return {
        selectedPatientId: '',
        selectedPlanType: 'balanced',
        planDuration: '4',
        dailyCalories: 2000,
        macroSplit: { protein: 25, carbs: 45, fats: 30 },
        selectedDay: 0,
        recipeSearch: '',
        recipeCategory: '',
        
        weekDays: ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Saturday', 'Dimanche'],
        
        mealTypes: [
            { id: 'breakfast', name: 'Petit-déjeuner', icon: 'fas fa-coffee', iconBg: 'bg-amber-100 dark:bg-amber-900/30', iconColor: 'text-amber-500', calories: 400 },
            { id: 'lunch', name: 'Déjeuner', icon: 'fas fa-utensils', iconBg: 'bg-green-100 dark:bg-green-900/30', iconColor: 'text-green-500', calories: 600 },
            { id: 'dinner', name: 'Dîner', icon: 'fas fa-moon', iconBg: 'bg-blue-100 dark:bg-blue-900/30', iconColor: 'text-blue-500', calories: 500 },
            { id: 'snacks', name: 'Collation', icon: 'fas fa-cookie', iconBg: 'bg-purple-100 dark:bg-purple-900/30', iconColor: 'text-purple-500', calories: 200 }
        ],
        
        planTypes: [
            { id: 'balanced', name: 'Plan équilibré', description: 'Répartition standard recommandée' },
            { id: 'low-carb', name: 'Low-carb', description: 'Réduction des glucides' },
            { id: 'high-protein', name: 'Haute protéine', description: 'Focus sur les protéines' },
            { id: 'mediterranean', name: 'Méditerranéen', description: 'Inspiré du régime méditerranéen' },
            { id: 'ketogenic', name: 'Cétogène', description: 'Very low carb, high fat' }
        ],
        
        templates: [
            { id: 1, name: 'Équilibré standard', calories: 2000, description: 'Plan classique pour perte de poids' },
            { id: 2, name: 'Athlète', calories: 2800, description: 'Pour performance sportive' },
            { id: 3, name: 'Diabète contrôle', calories: 1800, description: 'Gestion de la glycémie' },
            { id: 4, name: 'Cardio protégé', calories: 2000, description: 'Santé cardiovasculaire' }
        ],
        
        recipes: [
            { id: 1, name: 'Avoine aux fruits', icon: 'fas fa-bowl-rice', calories: 350, category: 'breakfast' },
            { id: 2, name: 'Omelette légumes', icon: 'fas fa-egg', calories: 280, category: 'breakfast' },
            { id: 3, name: 'Salade méditerranéenne', icon: 'fas fa-leaf', calories: 420, category: 'lunch' },
            { id: 4, name: 'Poulet quinoa', icon: 'fas fa-drumstick-bite', calories: 480, category: 'lunch' },
            { id: 5, name: 'Saumon brocoli', icon: 'fas fa-fish', calories: 450, category: 'dinner' },
            { id: 6, name: 'Yaourt grec', icon: 'fas fa-ice-cream', calories: 150, category: 'snack' },
        ],
        
        mealPlan: {},
        
        get selectedPatient() {
            return this.patients.find(p => p.id == this.selectedPatientId);
        },
        
        patients: [
            { id: 1, name: 'Marie Lambert', goal: 'Perte de poids', initials: 'ML' },
            { id: 2, name: 'Jean Dupont', goal: 'Gestion diabète', initials: 'JD' },
            { id: 3, name: 'Sophie Martin', goal: 'Prise de masse', initials: 'SM' },
        ],
        
        get filteredRecipes() {
            return this.recipes.filter(recipe => {
                const matchesSearch = !this.recipeSearch || 
                    recipe.name.toLowerCase().includes(this.recipeSearch.toLowerCase());
                const matchesCategory = !this.recipeCategory || recipe.category === this.recipeCategory;
                return matchesSearch && matchesCategory;
            });
        },
        
        getMealsForDay(dayIndex, mealType) {
            const dayName = this.weekDays[dayIndex];
            return (this.mealPlan[dayName]?.[mealType] || []);
        },
        
        get dailySummary() {
            let summary = { calories: 0, protein: 0, carbs: 0, fats: 0 };
            const dayName = this.weekDays[this.selectedDay];
            
            Object.values(this.mealPlan[dayName] || {}).forEach(meals => {
                meals.forEach(meal => {
                    summary.calories += meal.calories || 0;
                    summary.protein += meal.protein || 0;
                    summary.carbs += meal.carbs || 0;
                    summary.fats += meal.fats || 0;
                });
            });
            
            return summary;
        },
        
        addMeal(mealTypeId) {
            alert('Ajouter un repas pour ' + mealTypeId);
        },
        
        addRecipeToMeal(recipe) {
            const dayName = this.weekDays[this.selectedDay];
            if (!this.mealPlan[dayName]) this.mealPlan[dayName] = {};
            if (!this.mealPlan[dayName]['lunch']) this.mealPlan[dayName]['lunch'] = [];
            
            this.mealPlan[dayName]['lunch'].push({
                ...recipe,
                id: Date.now()
            });
        },
        
        editMeal(meal) {
            alert('Modifier: ' + meal.name);
        },
        
        removeMeal(dayIndex, mealTypeId, mealIndex) {
            const dayName = this.weekDays[dayIndex];
            this.mealPlan[dayName]?.[mealTypeId]?.splice(mealIndex, 1);
        },
        
        applyTemplate(template) {
            this.dailyCalories = template.calories;
            // Apply template logic
        },
        
        clearPlan() {
            this.mealPlan = {};
        },
        
        savePlan() {
            alert('Plan enregistré avec succès!');
        },
        
        previewPlan() {
            alert('Aperçu du plan');
        }
    };
}

// ============================================
// NUTRITION ANALYSIS
// ============================================

function nutritionAnalysis() {
    return {
        selectedPatientId: '',
        activeTab: 'overview',
        
        patients: [
            { id: 1, name: 'Marie Lambert' },
            { id: 2, name: 'Jean Dupont' },
        ],
        
        analysisTabs: [
            { id: 'overview', name: 'Aperçu', icon: 'fas fa-chart-pie' },
            { id: 'diary', name: 'Journal alimentaire', icon: 'fas fa-book' },
            { id: 'progress', name: 'Progression', icon: 'fas fa-chart-line' }
        ],
        
        nutritionData: {
            calories: { consumed: 1850, target: 2000 },
            protein: { consumed: 95, target: 120 },
            carbs: { consumed: 180, target: 250 },
            fats: { consumed: 65, target: 70 }
        },
        
        micronutrients: [
            { name: 'Vitamine A', value: '85%', status: 'good' },
            { name: 'Vitamine C', value: '92%', status: 'good' },
            { name: 'Vitamine D', value: '45%', status: 'low' },
            { name: 'Calcium', value: '78%', status: 'good' },
            { name: 'Fer', value: '65%', status: 'low' },
            { name: 'Zinc', value: '88%', status: 'good' },
        ],
        
        foodDiary: [
            {
                date: '15/02/2024',
                dayName: 'Mardi',
                totalCalories: 1850,
                meals: [
                    {
                        type: 'Petit-déjeuner',
                        foods: [
                            { name: 'Avoine aux fruits', icon: 'fas fa-bowl-rice', calories: 350 },
                            { name: 'Café noir', icon: 'fas fa-coffee', calories: 5 }
                        ]
                    },
                    {
                        type: 'Déjeuner',
                        foods: [
                            { name: 'Salade composée', icon: 'fas fa-leaf', calories: 280 },
                            { name: 'Poulet grillé', icon: 'fas fa-drumstick-bite', calories: 250 }
                        ]
                    }
                ]
            }
        ],
        
        generateReport() {
            alert('Génération du rapport...');
        }
    };
}

// ============================================
// COMMUNICATION
// ============================================

function nutritionistCommunication() {
    return {
        activeTab: 'messages',
        searchConversations: '',
        selectedConversation: null,
        newMessage: '',
        unreadMessages: 3,
        pendingRequests: 2,
        
        conversations: [
            {
                id: 1,
                patientId: 1,
                patientName: 'Marie Lambert',
                initials: 'ML',
                lastMessage: 'Merci pour les modifications du plan!',
                time: '10 min',
                unread: true,
                since: 'Janvier 2024',
                messages: [
                    { id: 1, sender: 'patient', content: 'Bonjour Docteur, j\'ai une question sur mon plan alimentaire...', time: '09:00' },
                    { id: 2, sender: 'me', content: 'Bonjour Marie, avec plaisir! Quelle est votre question?', time: '09:15' },
                    { id: 3, sender: 'patient', content: 'Concernant les glucides le soir, dois-je les supprimer?', time: '09:20' },
                    { id: 4, sender: 'me', content: 'Non, pas besoin de les supprimer. Réduisez simplement les portions.', time: '09:30' },
                    { id: 5, sender: 'patient', content: 'Merci pour les modifications du plan!', time: '10:00' }
                ]
            },
            {
                id: 2,
                patientId: 2,
                patientName: 'Jean Dupont',
                initials: 'JD',
                lastMessage: 'Voici mes mesures de la semaine',
                time: '2h',
                unread: false,
                since: 'Novembre 2023',
                messages: []
            }
        ],
        
        myPatients: [
            { id: 1, name: 'Marie Lambert', initials: 'ML', lastVisit: 'Dernière visite: il y a 3 jours' },
            { id: 2, name: 'Jean Dupont', initials: 'JD', lastVisit: 'Dernière visite: il y a 1 semaine' },
            { id: 3, name: 'Sophie Martin', initials: 'SM', lastVisit: 'Dernière visite: hier' },
        ],
        
        requests: [
            {
                id: 1,
                patientId: 6,
                patientName: 'Thomas Bernard',
                initials: 'TB',
                message: 'Demande de prise en charge nutritionnelle',
                date: 'Il y a 2 jours'
            },
            {
                id: 2,
                patientId: 7,
                patientName: 'Julie Moreau',
                initials: 'JM',
                message: 'Référence du Dr. Dubois pour gestion du poids',
                date: 'Il y a 5 jours'
            }
        ],
        
        get filteredConversations() {
            if (!this.searchConversations) return this.conversations;
            return this.conversations.filter(c => 
                c.patientName.toLowerCase().includes(this.searchConversations.toLowerCase())
            );
        },
        
        selectConversation(conversation) {
            this.selectedConversation = conversation;
            conversation.unread = false;
        },
        
        sendMessage() {
            if (this.newMessage.trim()) {
                this.selectedConversation.messages.push({
                    id: Date.now(),
                    sender: 'me',
                    content: this.newMessage,
                    time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
                });
                this.newMessage = '';
            }
        },
        
        sendQuickMessage(patient) {
            window.location.href = '/nutritionniste/messages?patient=' + patient.id;
        },
        
        acceptRequest(request) {
            this.requests = this.requests.filter(r => r.id !== request.id);
            this.pendingRequests = this.requests.length;
        },
        
        declineRequest(request) {
            this.requests = this.requests.filter(r => r.id !== request.id);
            this.pendingRequests = this.requests.length;
        }
    };
}

// ============================================
// REPORTING
// ============================================

function nutritionistReporting() {
    return {
        selectedReportType: null,
        
        reportTypes: [
            { id: 'progress', name: 'Rapport de progression', description: 'Suivi des objectifs et résultats', icon: 'fas fa-chart-line', bgColor: 'bg-blue-100 dark:bg-blue-900/30', iconColor: 'text-blue-500' },
            { id: 'nutrition', name: 'Bilan nutritionnel', description: 'Analyse des apports alimentaires', icon: 'fas fa-utensils', bgColor: 'bg-green-100 dark:bg-green-900/30', iconColor: 'text-green-500' },
            { id: 'medical', name: 'Rapport médical', description: 'Documentation pour professionnels', icon: 'fas fa-file-medical', bgColor: 'bg-purple-100 dark:bg-purple-900/30', iconColor: 'text-purple-500' },
            { id: 'insurance', name: 'Rapport assurance', description: 'Justificatifs et feuilles de soins', icon: 'fas fa-file-invoice-dollar', bgColor: 'bg-amber-100 dark:bg-amber-900/30', iconColor: 'text-amber-500' }
        ],
        
        reportConfig: {
            patientId: '',
            period: '30',
            startDate: '',
            endDate: '',
            sections: ['summary', 'nutrition', 'progress']
        },
        
        reportSections: [
            { id: 'summary', name: 'Résumé exécutif' },
            { id: 'nutrition', name: 'Analyse nutritionnelle' },
            { id: 'progress', name: 'Progression des objectifs' },
            { id: 'measurements', name: 'Mesures corporelles' },
            { id: 'recommendations', name: 'Recommandations' },
            { id: 'food_diary', name: 'Journal alimentaire' }
        ],
        
        recentReports: [
            { id: 1, date: '15/02/2024', type: 'progress', typeName: 'Progression', patientName: 'Marie Lambert', period: 'Février 2024', status: 'completed' },
            { id: 2, date: '14/02/2024', type: 'nutrition', typeName: 'Nutrition', patientName: 'Jean Dupont', period: 'Janvier 2024', status: 'completed' },
            { id: 3, date: '12/02/2024', type: 'medical', typeName: 'Médical', patientName: 'Pierre Durand', period: 'Janvier 2024', status: 'completed' },
        ],
        
        reportTemplates: [
            { id: 1, name: 'Rapport mensuel standard', description: 'Format classique pour suivi mensuel', icon: 'fas fa-file-alt', used: 45 },
            { id: 2, name: 'Bilan trimestriel', description: 'Analyse approfondie sur 3 mois', icon: 'fas fa-chart-bar', used: 23 },
            { id: 3, name: 'Rapport d\'orientation', description: 'Pour médecin traitant', icon: 'fas fa-user-md', used: 18 },
        ],
        
        selectReportType(reportType) {
            this.selectedReportType = reportType;
        },
        
        clearSelection() {
            this.selectedReportType = null;
        },
        
        createNewReport() {
            this.selectedReportType = this.reportTypes[0];
        },
        
        previewReport() {
            alert('Aperçu du rapport');
        },
        
        generateReport() {
            alert('Génération du rapport PDF en cours...');
        },
        
        viewReport(report) {
            alert('Visualisation du rapport: ' + report.id);
        },
        
        downloadReport(report) {
            alert('Téléchargement du rapport: ' + report.id);
        },
        
        shareReport(report) {
            alert('Partage du rapport: ' + report.id);
        },
        
        getReportTypeClass(type) {
            const classes = {
                'progress': 'bg-blue-100 text-blue-700',
                'nutrition': 'bg-green-100 text-green-700',
                'medical': 'bg-purple-100 text-purple-700',
                'insurance': 'bg-amber-100 text-amber-700'
            };
            return classes[type] || 'bg-gray-100 text-gray-700';
        }
    };
}

// ============================================
// EXPORT FOR ALPINE.JS
// ============================================

document.addEventListener('alpine:init', () => {
    Alpine.data('nutritionnisteDashboard', nutritionnisteDashboard);
    Alpine.data('patientList', patientList);
    Alpine.data('mealPlanBuilder', mealPlanBuilder);
    Alpine.data('nutritionAnalysis', nutritionAnalysis);
    Alpine.data('nutritionistCommunication', nutritionistCommunication);
    Alpine.data('nutritionistReporting', nutritionistReporting);
});
