/**
 * Doctor Dashboard JavaScript Module
 * Handles patient list, patient chart, clinical notes, and communication interfaces
 */

// Medical color palette
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
};

// Patient List Component
document.addEventListener('alpine:init', () => {
    
    Alpine.data('patientList', () => ({
        // State
        searchQuery: '',
        statusFilter: 'all',
        conditionFilter: 'all',
        sortBy: 'name',
        currentPage: 1,
        itemsPerPage: 10,
        
        // Stats
        stats: {
            totalPatients: 0,
            criticalAlerts: 0,
            followUp: 0,
            todayAppointments: 0,
        },
        
        // Data
        patients: [],
        filteredPatients: [],
        
        init() {
            this.loadPatients();
            this.updateStats();
        },
        
        loadPatients() {
            // Simulated patient data - replace with actual API call
            this.patients = [
                {
                    id: 'P001',
                    name: 'Marie Dupont',
                    age: 45,
                    gender: 'F',
                    fileNumber: '2024-001',
                    avatar: 'https://ui-avatars.com/api/?name=Marie+Dupont&background=00A790&color=fff',
                    status: 'active',
                    healthScore: 85,
                    conditions: ['Hypertension', 'Diabète type 2'],
                    lastVisitDate: '15/01/2026',
                    lastVisitTime: '14:30',
                },
                {
                    id: 'P002',
                    name: 'Jean Martin',
                    age: 52,
                    gender: 'M',
                    fileNumber: '2024-002',
                    avatar: 'https://ui-avatars.com/api/?name=Jean+Martin&background=ef4444&color=fff',
                    status: 'critical',
                    healthScore: 62,
                    conditions: ['Cardiaque', 'Hypertension'],
                    lastVisitDate: '14/01/2026',
                    lastVisitTime: '10:00',
                },
                {
                    id: 'P003',
                    name: 'Sophie Bernard',
                    age: 38,
                    gender: 'F',
                    fileNumber: '2024-003',
                    avatar: 'https://ui-avatars.com/api/?name=Sophie+Bernard&background=6366f1&color=fff',
                    status: 'stable',
                    healthScore: 92,
                    conditions: ['Asthme'],
                    lastVisitDate: '10/01/2026',
                    lastVisitTime: '16:15',
                },
                {
                    id: 'P004',
                    name: 'Pierre Leroy',
                    age: 65,
                    gender: 'M',
                    fileNumber: '2024-004',
                    avatar: 'https://ui-avatars.com/api/?name=Pierre+Leroy&background=f59e0b&color=fff',
                    status: 'follow-up',
                    healthScore: 78,
                    conditions: ['Diabète', 'Hypercholestérolémie'],
                    lastVisitDate: '13/01/2026',
                    lastVisitTime: '09:30',
                },
                {
                    id: 'P005',
                    name: 'Isabelle Petit',
                    age: 29,
                    gender: 'F',
                    fileNumber: '2024-005',
                    avatar: 'https://ui-avatars.com/api/?name=Isabelle+Petit&background=22c55e&color=fff',
                    status: 'active',
                    healthScore: 95,
                    conditions: [],
                    lastVisitDate: '05/01/2026',
                    lastVisitTime: '11:00',
                },
            ];
            this.filteredPatients = [...this.patients];
        },
        
        updateStats() {
            this.stats.totalPatients = this.patients.length;
            this.stats.criticalAlerts = this.patients.filter(p => p.status === 'critical').length;
            this.stats.followUp = this.patients.filter(p => p.status === 'follow-up').length;
            this.stats.todayAppointments = 3; // Simulated
        },
        
        filterPatients() {
            let filtered = [...this.patients];
            
            // Search filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(p => 
                    p.name.toLowerCase().includes(query) ||
                    p.fileNumber.toLowerCase().includes(query) ||
                    p.conditions.some(c => c.toLowerCase().includes(query))
                );
            }
            
            // Status filter
            if (this.statusFilter !== 'all') {
                filtered = filtered.filter(p => p.status === this.statusFilter);
            }
            
            // Condition filter
            if (this.conditionFilter !== 'all') {
                filtered = filtered.filter(p => 
                    p.conditions.some(c => c.toLowerCase().includes(this.conditionFilter))
                );
            }
            
            this.filteredPatients = filtered;
            this.currentPage = 1;
        },
        
        sortPatients() {
            const sorted = [...this.filteredPatients];
            switch (this.sortBy) {
                case 'name':
                    sorted.sort((a, b) => a.name.localeCompare(b.name));
                    break;
                case 'lastVisit':
                    sorted.sort((a, b) => new Date(b.lastVisitDate) - new Date(a.lastVisitDate));
                    break;
                case 'healthScore':
                    sorted.sort((a, b) => b.healthScore - a.healthScore);
                    break;
                case 'alerts':
                    const statusPriority = { critical: 0, 'follow-up': 1, active: 2, stable: 3 };
                    sorted.sort((a, b) => statusPriority[a.status] - statusPriority[b.status]);
                    break;
            }
            this.filteredPatients = sorted;
        },
        
        getStatusBadgeClass(status) {
            const classes = {
                active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
                critical: 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300',
                'follow-up': 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                stable: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
            };
            return classes[status] || classes.active;
        },
        
        getStatusDotClass(status) {
            const classes = {
                active: 'bg-emerald-500',
                critical: 'bg-rose-500',
                'follow-up': 'bg-amber-500',
                stable: 'bg-blue-500',
            };
            return classes[status] || classes.active;
        },
        
        getStatusLabel(status) {
            const labels = {
                active: 'Actif',
                critical: 'Critique',
                'follow-up': 'Suivi requis',
                stable: 'Stable',
            };
            return labels[status] || status;
        },
        
        getHealthScoreColor(score) {
            if (score >= 80) return 'bg-emerald-500';
            if (score >= 60) return 'bg-amber-500';
            return 'bg-rose-500';
        },
        
        previousPage() {
            if (this.currentPage > 1) this.currentPage--;
        },
        
        nextPage() {
            if (this.currentPage < this.totalPages) this.currentPage++;
        },
        
        get totalPages() {
            return Math.ceil(this.filteredPatients.length / this.itemsPerPage);
        },
        
        openAddPatientModal() {
            alert('Fonctionnalité à implémenter : Ajouter un nouveau patient');
        },
        
        openMessageModal(patient) {
            window.location.href = `/doctor/patient/${patient.id}/communication`;
        },
        
        scheduleAppointment(patient) {
            alert(`Planifier un rendez-vous pour ${patient.name}`);
        },
        
        printPatientChart(patient) {
            window.print();
        },
    }));
    
    // Patient Chart Component
    Alpine.data('patientChart', () => ({
        // State
        activeTab: 'timeline',
        timelineFilter: 'all',
        vitalsPeriod: '7d',
        
        // Patient Data
        patient: {
            id: 'P001',
            name: 'Marie Dupont',
            age: 45,
            gender: 'F',
            fileNumber: '2024-001',
            avatar: 'https://ui-avatars.com/api/?name=Marie+Dupont&background=00A790&color=fff',
            status: 'active',
            healthScore: 85,
            birthDate: '15/03/1980',
            bloodType: 'A+',
            height: 165,
            weight: 68,
            bmi: 25.0,
            phone: '+33 6 12 34 56 78',
            email: 'marie.dupont@email.com',
            address: '123 Rue de Paris, 75001 Paris',
            emergencyContact: {
                name: 'Jean Dupont',
                relation: 'Mari',
                phone: '+33 6 98 76 54 32',
            },
            allergies: ['Pénicilline', 'Arachides'],
            conditions: ['Hypertension', 'Diabète type 2'],
            medications: [
                { id: 1, name: 'Amlodipine', dosage: '5mg', frequency: '1x/jour', active: true },
                { id: 2, name: 'Metformine', dosage: '500mg', frequency: '2x/jour', active: true },
            ],
            lastVisitDate: '15/01/2026',
            nextAppointment: '15/02/2026',
        },
        
        // Timeline Data
        timeline: [
            { id: 1, type: 'symptom', typeLabel: 'Symptôme', title: 'Maux de tête', description: 'Légers maux de tête rapportés', date: 'Aujourd\'hui, 09:00', severity: 2 },
            { id: 2, type: 'medication', typeLabel: 'Médicament', title: 'Amlodipine', description: 'Prise quotidienne confirmée', date: 'Aujourd\'hui, 08:00' },
            { id: 3, type: 'appointment', typeLabel: 'Rendez-vous', title: 'Consultation de suivi', description: 'Contrôle tension artérielle', date: '15/01/2026, 14:30' },
            { id: 4, type: 'lab', typeLabel: 'Résultat Labo', title: 'Bilan lipidique', description: 'Cholestérol total: 1.95 g/L', date: '10/01/2026' },
            { id: 5, type: 'symptom', typeLabel: 'Symptôme', title: 'Fatigue', description: 'Fatigue persistante signalée', date: '08/01/2026', severity: 4 },
        ],
        
        // Vital Signs
        vitalSigns: [
            { type: 'bp', label: 'Tension', value: '120/80', unit: 'mmHg', icon: 'fa-solid fa-heart-pulse', trend: 'stable', change: '0', normalRange: '90-120/60-80', alert: false },
            { type: 'pulse', label: 'Pouls', value: '72', unit: 'bpm', icon: 'fa-solid fa-heart', trend: 'up', change: '+2', normalRange: '60-100', alert: false },
            { type: 'temp', label: 'Température', value: '36.6', unit: '°C', icon: 'fa-solid fa-temperature-half', trend: 'stable', change: '-0.1', normalRange: '36.1-37.2', alert: false },
            { type: 'spo2', label: 'SpO2', value: '98', unit: '%', icon: 'fa-solid fa-lungs', trend: 'up', change: '+1', normalRange: '95-100', alert: false },
        ],
        
        // Symptoms
        symptoms: [
            { id: 1, name: 'Fatigue', date: '08/01/2026', intensity: 6, duration: '3 jours', status: 'active' },
            { id: 2, name: 'Maux de tête', date: '10/01/2026', intensity: 3, duration: '1 jour', status: 'resolved' },
            { id: 3, name: 'Douleur thoracique', date: '05/01/2026', intensity: 4, duration: '2 heures', status: 'resolved' },
        ],
        
        // Treatment
        treatment: {
            adherence: 87,
            goals: [
                { id: 1, description: 'Réduire la tension artérielle à 130/80', completed: false, deadline: '15/03/2026' },
                { id: 2, description: 'Perdre 3 kg', completed: false, deadline: '15/04/2026' },
                { id: 3, description: 'Marcher 30 min/jour', completed: true, deadline: 'En cours' },
            ],
            followUps: [
                { id: 1, type: 'Consultation de suivi', date: '15/02/2026', time: '14:30', status: 'scheduled' },
                { id: 2, type: 'Bilan sanguin', date: '15/03/2026', time: '08:00', status: 'pending' },
            ],
        },
        
        init() {
            this.$nextTick(() => {
                this.initCharts();
            });
        },
        
        initCharts() {
            // Initialize charts if needed
        },
        
        get filteredTimeline() {
            if (this.timelineFilter === 'all') return this.timeline;
            return this.timeline.filter(t => t.type === this.timelineFilter);
        },
        
        getStatusBadgeClass(status) {
            const classes = {
                active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
                critical: 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300',
                'follow-up': 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                stable: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
            };
            return classes[status] || classes.active;
        },
        
        getStatusLabel(status) {
            const labels = {
                active: 'Actif',
                critical: 'Critique',
                'follow-up': 'Suivi requis',
                stable: 'Stable',
            };
            return labels[status] || status;
        },
        
        getHealthScoreColor(score) {
            if (score >= 80) return 'text-emerald-600 dark:text-emerald-400';
            if (score >= 60) return 'text-amber-600 dark:text-amber-400';
            return 'text-rose-600 dark:text-rose-400';
        },
        
        getBMIColor(bmi) {
            if (bmi < 18.5) return 'text-amber-600';
            if (bmi < 25) return 'text-emerald-600';
            if (bmi < 30) return 'text-amber-600';
            return 'text-rose-600';
        },
        
        getTimelineDotClass(type) {
            const classes = {
                symptom: 'bg-amber-500',
                medication: 'bg-blue-500',
                appointment: 'bg-wellcare-500',
                lab: 'bg-purple-500',
            };
            return classes[type] || 'bg-gray-500';
        },
        
        getTimelineBadgeClass(type) {
            const classes = {
                symptom: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                medication: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                appointment: 'bg-wellcare-100 text-wellcare-800 dark:bg-wellcare-900/30 dark:text-wellcare-300',
                lab: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
            };
            return classes[type] || 'bg-gray-100 text-gray-800';
        },
        
        getSeverityColor(severity) {
            if (severity <= 2) return 'text-emerald-500';
            if (severity <= 4) return 'text-amber-500';
            return 'text-rose-500';
        },
        
        getIntensityColor(intensity) {
            if (intensity <= 3) return 'bg-emerald-500';
            if (intensity <= 6) return 'bg-amber-500';
            return 'bg-rose-500';
        },
        
        openMessageModal() {
            window.location.href = `/doctor/patient/${this.patient.id}/communication`;
        },
        
        printChart() {
            window.print();
        },
        
        openAddSymptomModal() {
            alert('Ajouter un symptôme');
        },
        
        openAddTreatmentModal() {
            alert('Modifier le traitement');
        },
        
        toggleGoal(goal) {
            goal.completed = !goal.completed;
        },
        
        updateVitalsChart() {
            // Update chart based on selected period
        },
    }));
    
    // Clinical Notes Component
    Alpine.data('clinicalNotes', () => ({
        // State
        selectedNote: null,
        newDiagnosis: '',
        newLabTest: '',
        
        // Patient
        patient: {
            id: 'P001',
            name: 'Marie Dupont',
            fileNumber: '2024-001',
            avatar: 'https://ui-avatars.com/api/?name=Marie+Dupont&background=00A790&color=fff',
            status: 'active',
        },
        
        // Notes List
        notes: [
            { id: 1, type: 'SOAP', typeLabel: 'Note SOAP', date: '15/01/2026', author: 'Dr. Martin', summary: 'Consultation de suivi hypertension', isComplete: true },
            { id: 2, type: 'SOAP', typeLabel: 'Note SOAP', date: '01/01/2026', author: 'Dr. Martin', summary: 'Bilan annuel', isComplete: true },
            { id: 3, type: 'Prescription', typeLabel: 'Ordonnance', date: '15/12/2025', author: 'Dr. Martin', summary: 'Renouvellement Amlodipine', isComplete: true },
        ],
        
        // Current Note (SOAP)
        currentNote: {
            chiefComplaint: '',
            subjective: '',
            objective: '',
            assessment: '',
            plan: '',
            vitals: {
                bpSystolic: '',
                bpDiastolic: '',
                pulse: '',
                temperature: '',
                spo2: '',
            },
            diagnoses: [],
            medications: [],
            labTests: [],
            followUp: {
                date: '',
                type: 'consultation',
                priority: 'routine',
            },
        },
        
        init() {
            // Initialize
        },
        
        selectNote(note) {
            this.selectedNote = note;
            // Load note data
        },
        
        getStatusBadgeClass(status) {
            const classes = {
                active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
                critical: 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300',
                'follow-up': 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                stable: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
            };
            return classes[status] || classes.active;
        },
        
        getStatusLabel(status) {
            const labels = {
                active: 'Actif',
                critical: 'Critique',
                'follow-up': 'Suivi requis',
                stable: 'Stable',
            };
            return labels[status] || status;
        },
        
        addDiagnosis() {
            if (this.newDiagnosis.trim()) {
                this.currentNote.diagnoses.push({
                    code: '',
                    description: this.newDiagnosis,
                });
                this.newDiagnosis = '';
            }
        },
        
        removeDiagnosis(index) {
            this.currentNote.diagnoses.splice(index, 1);
        },
        
        addLabTest() {
            if (this.newLabTest) {
                this.currentNote.labTests.push(this.newLabTest);
                this.newLabTest = '';
            }
        },
        
        removeLabTest(index) {
            this.currentNote.labTests.splice(index, 1);
        },
        
        removeMedication(index) {
            this.currentNote.medications.splice(index, 1);
        },
        
        openNewNoteModal() {
            this.selectedNote = null;
            this.currentNote = {
                chiefComplaint: '',
                subjective: '',
                objective: '',
                assessment: '',
                plan: '',
                vitals: { bpSystolic: '', bpDiastolic: '', pulse: '', temperature: '', spo2: '' },
                diagnoses: [],
                medications: [],
                labTests: [],
                followUp: { date: '', type: 'consultation', priority: 'routine' },
            };
        },
        
        openPrescriptionModal() {
            alert('Nouvelle ordonnance');
        },
        
        openLabOrderModal() {
            alert('Nouvelle ordonnance labo');
        },
        
        openReferralModal() {
            alert('Lettre de recommandation');
        },
        
        openAddMedicationModal() {
            const name = prompt('Nom du médicament:');
            if (name) {
                this.currentNote.medications.push({
                    name,
                    dosage: '',
                    frequency: '',
                    duration: '',
                });
            }
        },
        
        saveNote() {
            alert('Note enregistrée avec succès');
        },
        
        printNote() {
            window.print();
        },
    }));
    
    // Doctor Communication Component
    Alpine.data('doctorCommunication', () => ({
        // State
        patientSearch: '',
        selectedPatient: null,
        newMessage: '',
        isTyping: false,
        replyingTo: null,
        showFilesPanel: false,
        showTeleconsultationModal: false,
        
        // Stats
        stats: {
            unreadMessages: 5,
            teleconsultations: 12,
            sharedFiles: 24,
        },
        
        // Data
        patients: [],
        filteredPatients: [],
        messages: [],
        sharedFiles: [],
        
        // Quick replies
        quickReplies: [
            'Bonjour, comment allez-vous?',
            'Prenez bien vos médicaments',
            'N\'oubliez pas votre RDV',
            'Vos résultats sont disponibles',
            'Appelez-moi si nécessaire',
        ],
        
        // Teleconsultation
        teleconsultation: {
            patientId: '',
            date: '',
            time: '',
            reason: '',
            sendReminder: true,
        },
        
        init() {
            this.loadPatients();
            this.loadMessages();
            this.loadSharedFiles();
        },
        
        loadPatients() {
            this.patients = [
                { id: 'P001', name: 'Marie Dupont', avatar: 'https://ui-avatars.com/api/?name=Marie+Dupont&background=00A790&color=fff', lastMessage: 'Bonjour docteur, merci pour vos conseils', lastMessageTime: '10:30', unreadCount: 2, isOnline: true },
                { id: 'P002', name: 'Jean Martin', avatar: 'https://ui-avatars.com/api/?name=Jean+Martin&background=ef4444&color=fff', lastMessage: 'Je ressens une douleur', lastMessageTime: 'Hier', unreadCount: 1, isOnline: false },
                { id: 'P003', name: 'Sophie Bernard', avatar: 'https://ui-avatars.com/api/?name=Sophie+Bernard&background=6366f1&color=fff', lastMessage: 'D\'accord, à bientôt', lastMessageTime: 'Hier', unreadCount: 0, isOnline: true },
            ];
            this.filteredPatients = [...this.patients];
        },
        
        loadMessages() {
            this.messages = [
                { id: 1, content: 'Bonjour docteur, j\'ai une question concernant mes médicaments', isDoctor: false, time: '10:00', read: true },
                { id: 2, content: 'Bonjour Marie, bien sûr, je vous écoute', isDoctor: true, time: '10:05', read: true },
                { id: 3, content: 'Dois-je prendre le médicament avant ou après les repas?', isDoctor: false, time: '10:10', read: true },
                { id: 4, content: 'Prenez-le après le repas pour éviter les maux d\'estomac. N\'oubliez pas de bien vous hydrater.', isDoctor: true, time: '10:15', read: true },
                { id: 5, content: 'Merci beaucoup pour vos conseils', isDoctor: false, time: '10:30', read: false },
            ];
        },
        
        loadSharedFiles() {
            this.sharedFiles = [
                { id: 1, name: 'Bilan_2026.pdf', type: 'pdf', date: '15/01/2026', size: '245 KB' },
                { id: 2, name: 'Radio_poumon.jpg', type: 'image', date: '10/01/2026', size: '1.2 MB' },
                { id: 3, name: 'Ordonnance.pdf', type: 'pdf', date: '05/01/2026', size: '120 KB' },
            ];
        },
        
        filterPatients() {
            if (!this.patientSearch) {
                this.filteredPatients = [...this.patients];
                return;
            }
            const query = this.patientSearch.toLowerCase();
            this.filteredPatients = this.patients.filter(p => 
                p.name.toLowerCase().includes(query)
            );
        },
        
        selectPatient(patient) {
            this.selectedPatient = patient;
            this.loadMessages();
            this.$nextTick(() => {
                this.scrollToBottom();
            });
        },
        
        sendMessage() {
            if (!this.newMessage.trim()) return;
            
            this.messages.push({
                id: Date.now(),
                content: this.newMessage,
                isDoctor: true,
                time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
                read: false,
            });
            
            this.newMessage = '';
            this.replyingTo = null;
            this.$nextTick(() => {
                this.scrollToBottom();
            });
            
            // Simulate patient typing and response
            setTimeout(() => {
                this.isTyping = true;
                setTimeout(() => {
                    this.isTyping = false;
                    this.messages.push({
                        id: Date.now() + 1,
                        content: 'Merci docteur, j\'ai bien noté',
                        isDoctor: false,
                        time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
                        read: false,
                    });
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                }, 2000);
            }, 1000);
        },
        
        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },
        
        adjustTextareaHeight() {
            const textarea = this.$refs.messageInput;
            if (textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
            }
        },
        
        cancelReply() {
            this.replyingTo = null;
        },
        
        openFilePicker() {
            alert('Sélectionner un fichier');
        },
        
        openImagePicker() {
            alert('Sélectionner une image');
        },
        
        downloadFile(file) {
            alert(`Téléchargement de ${file.name}`);
        },
        
        previewFile(file) {
            alert(`Prévisualisation de ${file.name}`);
        },
        
        getFileIcon(type) {
            const icons = {
                pdf: 'fa-solid fa-file-pdf text-red-500',
                image: 'fa-solid fa-file-image text-blue-500',
                doc: 'fa-solid fa-file-word text-blue-700',
            };
            return icons[type] || 'fa-solid fa-file text-gray-500';
        },
        
        openImageModal(image) {
            alert(`Afficher l'image: ${image}`);
        },
        
        startVoiceCall() {
            alert('Démarrer un appel vocal');
        },
        
        startVideoCall() {
            alert('Démarrer un appel vidéo');
        },
        
        viewPatientChart() {
            if (this.selectedPatient) {
                window.location.href = `/doctor/patient/${this.selectedPatient.id}/chart`;
            }
        },
        
        blockPatient() {
            alert('Patient bloqué');
        },
        
        reportConversation() {
            alert('Conversation signalée');
        },
        
        scheduleTeleconsultation() {
            this.showTeleconsultationModal = true;
        },
        
        scheduleTeleconsultationConfirm() {
            alert('Téléconsultation planifiée');
            this.showTeleconsultationModal = false;
        },
    }));
    
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { medicalColors };
}
