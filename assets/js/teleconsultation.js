/**
 * Teleconsultation Platform JavaScript Module
 * Handles video consultations, medical tools, and secure data sharing
 */

// Medical colors for teleconsultation
const teleconsultColors = {
    primary: '#00A790',
    primaryLight: 'rgba(0, 167, 144, 0.1)',
    success: '#22c55e',
    successLight: 'rgba(34, 197, 94, 0.1)',
    warning: '#f59e0b',
    danger: '#ef4444',
    info: '#3b82f6',
};

document.addEventListener('alpine:init', () => {
    
    // ============================================
    // VIRTUAL WAITING ROOM COMPONENT
    // ============================================
    Alpine.data('waitingRoom', () => ({
        // State
        isReady: false,
        connectionTestPassed: false,
        preparationSteps: [],
        currentStep: 0,
        
        // Questionnaire data
        symptoms: '',
        medications: '',
        allergies: '',
        
        // Documents
        uploadedFiles: [],
        dragOver: false,
        
        // Patient data
        patient: {
            id: 'P001',
            name: 'Marie Dupont',
            avatar: 'https://ui-avatars.com/api/?name=Marie+Dupont&background=00A790&color=fff',
            appointmentTime: '14:30',
            estimatedWait: 5,
            doctorName: 'Dr. Jean Martin',
            consultationType: 'Follow-up',
        },
        
        // Consultation info
        consultation: {
            id: 'TC001',
            scheduledTime: '14:30',
            duration: 30,
            type: 'Follow-up',
            reason: 'Blood pressure follow-up',
        },
        
        // Connection test
        connectionStatus: {
            video: false,
            audio: false,
            internet: false,
        },
        
        // Computed
        get canJoin() {
            return this.connectionTestPassed;
        },
        
        get estimatedWait() {
            return this.patient.estimatedWait;
        },
        
        init() {
            this.loadPreparationSteps();
            this.runConnectionTest();
        },
        
        loadPreparationSteps() {
            this.preparationSteps = [
                { id: 1, title: 'Connection Check', completed: false, icon: 'fa-wifi' },
                { id: 2, title: 'Camera Test', completed: false, icon: 'fa-video' },
                { id: 3, title: 'Microphone Test', completed: false, icon: 'fa-microphone' },
                { id: 4, title: 'Health Questionnaire', completed: false, icon: 'fa-clipboard-list' },
                { id: 5, title: 'Documents', completed: false, icon: 'fa-file-upload' },
            ];
        },
        
        async runConnectionTest() {
            // Test internet first
            await this.delay(800);
            this.connectionStatus.internet = true;
            this.updateStep(1, true);
            
            // Test camera
            await this.testCamera();
            
            // Test microphone
            await this.testMicrophone();
            
            this.connectionTestPassed = true;
        },
        
        async testCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                this.connectionStatus.video = true;
                this.updateStep(2, true);
                
                // Stop the test stream
                stream.getTracks().forEach(track => track.stop());
                
                // Set up video preview if element exists
                this.$nextTick(() => {
                    const videoEl = document.getElementById('video-preview');
                    if (videoEl) {
                        navigator.mediaDevices.getUserMedia({ video: true })
                            .then(s => {
                                videoEl.srcObject = s;
                            });
                    }
                });
            } catch (error) {
                console.error('Camera test failed:', error);
                this.connectionStatus.video = false;
            }
        },
        
        async testMicrophone() {
            try {
                await navigator.mediaDevices.getUserMedia({ audio: true });
                this.connectionStatus.audio = true;
                this.updateStep(3, true);
            } catch (error) {
                console.error('Microphone test failed:', error);
                this.connectionStatus.audio = false;
            }
        },
        
        testConnection() {
            this.connectionStatus.internet = navigator.onLine;
            this.updateStep(1, this.connectionStatus.internet);
        },
        
        updateStep(stepId, completed) {
            const step = this.preparationSteps.find(s => s.id === stepId);
            if (step) {
                step.completed = completed;
            }
        },
        
        saveQuestionnaire() {
            this.updateStep(4, true);
            // Auto-save to localStorage
            localStorage.setItem('waitingRoom_questionnaire', JSON.stringify({
                symptoms: this.symptoms,
                medications: this.medications,
                allergies: this.allergies,
            }));
        },
        
        // File upload handlers
        handleDrop(event) {
            this.dragOver = false;
            const files = event.dataTransfer.files;
            this.addFiles(files);
        },
        
        handleFileSelect(event) {
            const files = event.target.files;
            this.addFiles(files);
        },
        
        addFiles(files) {
            Array.from(files).forEach(file => {
                if (file.size > 10 * 1024 * 1024) {
                    alert(`File ${file.name} is too large. Maximum size is 10MB.`);
                    return;
                }
                this.uploadedFiles.push({
                    name: file.name,
                    type: file.type,
                    size: file.size,
                });
            });
            this.updateStep(5, this.uploadedFiles.length > 0);
        },
        
        removeFile(index) {
            this.uploadedFiles.splice(index, 1);
            this.updateStep(5, this.uploadedFiles.length > 0);
        },
        
        async joinConsultation() {
            if (!this.connectionTestPassed) {
                alert('Please complete all connection tests before joining.');
                return;
            }
            // Save questionnaire if not already saved
            if (this.symptoms || this.medications || this.allergies) {
                this.saveQuestionnaire();
            }
            // Navigate to consultation room
            window.location.href = `/teleconsultation/room/${this.consultation.id}`;
        },
        
        delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        },
    }));

    // ============================================
    // VIDEO CONSULTATION ROOM COMPONENT
    // ============================================
    Alpine.data('consultationRoom', () => ({
        // State
        isConnected: false,
        isMuted: false,
        isVideoOff: false,
        isScreenSharing: false,
        isRecording: false,
        showChat: false,
        showNotes: false,
        showTools: false,
        activeTool: null,
        connectionQuality: 'good',
        elapsedTime: 0,
        timerInterval: null,
        
        // Room info
        room: {
            id: 'TC001',
            patientId: 'P001',
            patientName: 'Marie Dupont',
            patientAvatar: 'https://ui-avatars.com/api/?name=Marie+Dupont&background=00A790&color=fff',
            doctorName: 'Dr. Jean Martin',
            scheduledTime: '14:30',
            duration: 30,
            type: 'followUp',
        },
        
        // Video/Audio state
        localStream: null,
        remoteStream: null,
        
        // Chat messages
        messages: [],
        
        // Shared documents
        sharedDocuments: [],
        
        // Collaborative whiteboard
        whiteboardData: [],
        
        init() {
            this.loadPatientInfo();
            this.loadPresets();
        },
        
        loadPatientInfo() {
            this.patientInfo = {
                id: this.room.patientId,
                name: this.room.patientName,
                age: 45,
                gender: 'F',
                bloodType: 'A+',
                allergies: ['Pénicilline'],
                conditions: ['Hypertension', 'Diabète type 2'],
                currentMedications: [
                    { name: 'Amlodipine', dosage: '5mg', frequency: '1x/jour' },
                    { name: 'Metformine', dosage: '500mg', frequency: '2x/jour' },
                ],
                lastVisit: '15/01/2026',
                reason: this.room.reason || 'Évaluation du traitement antihypertenseur',
            };
        },
        
        loadPresets() {
            this.symptoms = [
                { id: 1, name: 'Maux de tête', severity: null },
                { id: 2, name: 'Étourdissements', severity: null },
                { id: 3, name: 'Fatigue', severity: null },
                { id: 4, name: 'Palpitations', severity: null },
                { id: 5, name: 'Douleurs thoraciques', severity: null },
            ];
        },
        
        async joinRoom() {
            try {
                // Request media permissions
                this.localStream = await navigator.mediaDevices.getUserMedia({ 
                    video: true, 
                    audio: true 
                });
                
                this.isConnected = true;
                this.startTimer();
                
                // Simulate remote connection (in production, use WebRTC signaling)
                setTimeout(() => {
                    this.remoteConnected = true;
                }, 2000);
                
            } catch (error) {
                console.error('Failed to access media devices:', error);
                alert('Impossible d\'accéder à la caméra ou au microphone. Vérifiez les permissions.');
            }
        },
        
        startTimer() {
            this.timerInterval = setInterval(() => {
                this.elapsedTime++;
            }, 1000);
        },
        
        stopTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
            }
        },
        
        toggleMute() {
            this.isMuted = !this.isMuted;
            if (this.localStream) {
                this.localStream.getAudioTracks().forEach(track => {
                    track.enabled = !this.isMuted;
                });
            }
        },
        
        toggleVideo() {
            this.isVideoOff = !this.isVideoOff;
            if (this.localStream) {
                this.localStream.getVideoTracks().forEach(track => {
                    track.enabled = !this.isVideoOff;
                });
            }
        },
        
        async toggleScreenShare() {
            if (!this.isScreenSharing) {
                try {
                    this.screenStream = await navigator.mediaDevices.getDisplayMedia({ 
                        video: true 
                    });
                    this.isScreenSharing = true;
                    
                    // Listen for screen share stop
                    this.screenStream.getVideoTracks()[0].onended = () => {
                        this.isScreenSharing = false;
                    };
                } catch (error) {
                    console.error('Failed to share screen:', error);
                }
            } else {
                this.screenStream.getTracks().forEach(track => track.stop());
                this.isScreenSharing = false;
            }
        },
        
        toggleRecording() {
            this.isRecording = !this.isRecording;
            if (this.isRecording) {
                alert('L\'enregistrement a commencé (avec consentement du patient)');
            } else {
                alert('L\'enregistrement a été arrêté');
            }
        },
        
        toggleChat() {
            this.showChat = !this.showChat;
        },
        
        toggleNotes() {
            this.showNotes = !this.showNotes;
        },
        
        toggleTools() {
            this.showTools = !this.showTools;
        },
        
        selectTool(tool) {
            this.activeTool = this.activeTool === tool ? null : tool;
        },
        
        sendMessage(content) {
            if (!content.trim()) return;
            
            this.messages.push({
                id: Date.now(),
                sender: 'doctor',
                content,
                time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
            });
        },
        
        endConsultation() {
            if (confirm('Êtes-vous sûr de vouloir mettre fin à la consultation ?')) {
                this.stopTimer();
                if (this.localStream) {
                    this.localStream.getTracks().forEach(track => track.stop());
                }
                window.location.href = `/teleconsultation/summary/${this.room.id}`;
            }
        },
        
        requestInPerson() {
            alert('Le patient sera contacté pour programmer une consultation en personne.');
        },
        
        getFormattedTime() {
            const hours = Math.floor(this.elapsedTime / 3600);
            const minutes = Math.floor((this.elapsedTime % 3600) / 60);
            const seconds = this.elapsedTime % 60;
            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        },
        
        getConnectionQualityClass() {
            return {
                'text-emerald-500': this.connectionQuality === 'good',
                'text-amber-500': this.connectionQuality === 'fair',
                'text-red-500': this.connectionQuality === 'poor',
            };
        },
    }));

    // ============================================
    // MEDICAL TOOLS COMPONENT
    // ============================================
    Alpine.data('medicalTools', () => ({
        // State
        activeTool: null,
        
        // Body map
        bodyMapRegions: [
            { id: 'head', name: 'Tête', x: 50, y: 10 },
            { id: 'neck', name: 'Cou', x: 50, y: 22 },
            { id: 'chest', name: 'Poitrine', x: 50, y: 35 },
            { id: 'abdomen', name: 'Abdomen', x: 50, y: 50 },
            { id: 'left-arm', name: 'Bras gauche', x: 25, y: 35 },
            { id: 'right-arm', name: 'Bras droit', x: 75, y: 35 },
            { id: 'left-hand', name: 'Main gauche', x: 15, y: 55 },
            { id: 'right-hand', name: 'Main droite', x: 85, y: 55 },
            { id: 'pelvis', name: 'Bassin', x: 50, y: 65 },
            { id: 'left-leg', name: 'Jambe gauche', x: 40, y: 80 },
            { id: 'right-leg', name: 'Jambe droite', x: 60, y: 80 },
            { id: 'left-foot', name: 'Pied gauche', x: 38, y: 95 },
            { id: 'right-foot', name: 'Pied droit', x: 62, y: 95 },
        ],
        selectedRegions: [],
        symptomDescriptions: {},
        
        // Visual acuity
        visualAcuity: {
            left: { value: null, unit: '20/20' },
            right: { value: null, unit: '20/20' },
        },
        
        // Dermatology
        uploadedImages: [],
        imageAnnotations: [],
        
        // Shared whiteboard
        whiteboardCanvas: null,
        whiteboardContext: null,
        isDrawing: false,
        brushColor: '#000000',
        brushSize: 2,
        
        init() {
            this.$nextTick(() => {
                this.initWhiteboard();
            });
        },
        
        selectTool(tool) {
            this.activeTool = this.activeTool === tool ? null : tool;
        },
        
        // Body map functions
        toggleRegion(regionId) {
            const index = this.selectedRegions.indexOf(regionId);
            if (index > -1) {
                this.selectedRegions.splice(index, 1);
            } else {
                this.selectedRegions.push(regionId);
            }
        },
        
        isRegionSelected(regionId) {
            return this.selectedRegions.includes(regionId);
        },
        
        getRegionName(regionId) {
            const region = this.bodyMapRegions.find(r => r.id === regionId);
            return region ? region.name : regionId;
        },
        
        setSymptomSeverity(regionId, severity) {
            if (!this.symptomDescriptions[regionId]) {
                this.symptomDescriptions[regionId] = {};
            }
            this.symptomDescriptions[regionId].severity = severity;
        },
        
        addSymptomNote(regionId, note) {
            if (!this.symptomDescriptions[regionId]) {
                this.symptomDescriptions[regionId] = {};
            }
            this.symptomDescriptions[regionId].note = note;
        },
        
        // Visual acuity functions
        testVisualAcuity(side, line) {
            if (this.visualAcuity[side]) {
                this.visualAcuity[side].value = line;
            }
        },
        
        // Dermatology functions
        uploadImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.uploadedImages.push({
                        id: Date.now(),
                        src: e.target.result,
                        name: file.name,
                        annotations: [],
                    });
                };
                reader.readAsDataURL(file);
            }
        },
        
        addAnnotation(imageId, annotation) {
            const image = this.uploadedImages.find(img => img.id === imageId);
            if (image) {
                image.annotations.push(annotation);
            }
        },
        
        // Whiteboard functions
        initWhiteboard() {
            const canvas = document.getElementById('whiteboard-canvas');
            if (canvas) {
                this.whiteboardCanvas = canvas;
                this.whiteboardContext = canvas.getContext('2d');
                
                // Set canvas size
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
                
                // Mouse events
                canvas.addEventListener('mousedown', this.startDrawing.bind(this));
                canvas.addEventListener('mousemove', this.draw.bind(this));
                canvas.addEventListener('mouseup', this.stopDrawing.bind(this));
                canvas.addEventListener('mouseout', this.stopDrawing.bind(this));
                
                // Touch events
                canvas.addEventListener('touchstart', this.startDrawing.bind(this));
                canvas.addEventListener('touchmove', this.draw.bind(this));
                canvas.addEventListener('touchend', this.stopDrawing.bind(this));
            }
        },
        
        startDrawing(e) {
            this.isDrawing = true;
            const pos = this.getPosition(e);
            this.whiteboardContext.beginPath();
            this.whiteboardContext.moveTo(pos.x, pos.y);
        },
        
        draw(e) {
            if (!this.isDrawing) return;
            const pos = this.getPosition(e);
            this.whiteboardContext.lineTo(pos.x, pos.y);
            this.whiteboardContext.strokeStyle = this.brushColor;
            this.whiteboardContext.lineWidth = this.brushSize;
            this.whiteboardContext.lineCap = 'round';
            this.whiteboardContext.stroke();
        },
        
        stopDrawing() {
            this.isDrawing = false;
        },
        
        getPosition(e) {
            const rect = this.whiteboardCanvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - rect.left,
                y: clientY - rect.top,
            };
        },
        
        clearWhiteboard() {
            if (this.whiteboardContext && this.whiteboardCanvas) {
                this.whiteboardContext.clearRect(0, 0, this.whiteboardCanvas.width, this.whiteboardCanvas.height);
            }
        },
        
        setBrushColor(color) {
            this.brushColor = color;
        },
        
        setBrushSize(size) {
            this.brushSize = size;
        },
        
        saveWhiteboard() {
            const dataUrl = this.whiteboardCanvas.toDataURL('image/png');
            this.whiteboardData.push({
                id: Date.now(),
                image: dataUrl,
                timestamp: new Date().toLocaleString(),
            });
        },
    }));

    // ============================================
    // SOAP NOTES COMPONENT
    // ============================================
    Alpine.data('soapNotes', () => ({
        // State
        activeSection: 'subjective',
        isSaving: false,
        lastSaved: null,
        
        // Patient info
        patient: {
            id: 'P001',
            name: 'Marie Dupont',
            fileNumber: '2024-001',
            age: 45,
            gender: 'F',
        },
        
        // Consultation info
        consultation: {
            id: 'TC001',
            date: new Date().toLocaleDateString('fr-FR'),
            type: 'followUp',
            duration: 30,
        },
        
        // SOAP notes
        subjective: {
            chiefComplaint: '',
            historyOfPresentIllness: '',
            reviewOfSystems: {
                general: '',
                cardiovascular: '',
                respiratory: '',
                gastrointestinal: '',
                neurological: '',
            },
            medications: '',
            allergies: '',
            familyHistory: '',
            socialHistory: '',
        },
        
        objective: {
            vitals: {
                bloodPressure: '',
                heartRate: '',
                respiratoryRate: '',
                temperature: '',
                oxygenSaturation: '',
                weight: '',
                height: '',
                bmi: '',
            },
            generalAppearance: '',
            head: '',
            eyes: '',
                ears: '',
            nose: '',
            throat: '',
            neck: '',
            cardiovascular: '',
            respiratory: '',
            abdominal: '',
            extremities: '',
            neurological: '',
            skin: '',
            psychiatric: '',
        },
        
        assessment: {
            diagnoses: [],
            differentialDiagnoses: [],
            clinicalImpression: '',
        },
        
        plan: {
            medications: [],
            procedures: [],
            labOrders: [],
            imagingOrders: [],
            referrals: [],
            patientEducation: '',
            followUp: {
                date: '',
                instructions: '',
            },
        },
        
        // Vital signs calculation
        calculateBMI() {
            const weight = parseFloat(this.objective.vitals.weight);
            const height = parseFloat(this.objective.vitals.height) / 100; // Convert cm to m
            if (weight && height) {
                this.objective.vitals.bmi = (weight / (height * height)).toFixed(1);
            }
        },
        
        addDiagnosis() {
            this.assessment.diagnoses.push({
                id: Date.now(),
                code: '',
                description: '',
                severity: 'mild',
            });
        },
        
        removeDiagnosis(id) {
            this.assessment.diagnoses = this.assessment.diagnoses.filter(d => d.id !== id);
        },
        
        addMedication() {
            this.plan.medications.push({
                id: Date.now(),
                name: '',
                dosage: '',
                frequency: '',
                duration: '',
                instructions: '',
            });
        },
        
        removeMedication(id) {
            this.plan.medications = this.plan.medications.filter(m => m.id !== id);
        },
        
        addLabOrder() {
            this.plan.labOrders.push({
                id: Date.now(),
                test: '',
                urgency: 'routine',
                instructions: '',
            });
        },
        
        removeLabOrder(id) {
            this.plan.labOrders = this.plan.labOrders.filter(l => l.id !== id);
        },
        
        addReferral() {
            this.plan.referrals.push({
                id: Date.now(),
                specialty: '',
                reason: '',
                urgency: 'routine',
            });
        },
        
        removeReferral(id) {
            this.plan.referrals = this.plan.referrals.filter(r => r.id !== id);
        },
        
        async saveNotes() {
            this.isSaving = true;
            
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            this.isSaving = false;
            this.lastSaved = new Date();
        },
        
        generateSummary() {
            alert('Génération du résumé de consultation...');
        },
        
        exportToEHR() {
            alert('Exportation vers le dossier médical électronique...');
        },
    }));

    // ============================================
    // PRESCRIPTION WRITER COMPONENT
    // ============================================
    Alpine.data('prescriptionWriter', () => ({
        // State
        isSaving: false,
        searchQuery: '',
        selectedPharmacy: null,
        
        // Patient info
        patient: {
            id: 'P001',
            name: 'Marie Dupont',
            age: 45,
            weight: 68,
            allergies: ['Pénicilline'],
            conditions: ['Hypertension', 'Diabète type 2'],
            currentMedications: ['Amlodipine 5mg 1x/jour', 'Metformine 500mg 2x/jour'],
        },
        
        // Prescription
        prescription: {
            date: new Date().toLocaleDateString('fr-FR'),
            type: 'renewal', // new, renewal, modification
            medications: [],
            notes: '',
            diagnosis: '',
        },
        
        // Drug database (simulated)
        drugDatabase: [],
        
        // Drug interactions
        interactions: [],
        
        // Pharmacies
        pharmacies: [
            { id: 'PH001', name: 'Pharmacie Centrale', address: '123 Rue de la Pharmacie, 75001 Paris', phone: '01 23 45 67 89' },
            { id: 'PH002', name: 'Pharmacie du Marché', address: '456 Avenue des Arts, 75002 Paris', phone: '01 98 76 54 32' },
            { id: 'PH003', name: 'Pharmacie Plus', address: '789 Boulevard Santé, 75003 Paris', phone: '01 55 44 33 22' },
        ],
        
        // Common medications
        commonMedications: [
            { id: 1, name: 'Amlodipine', dosage: '5mg', form: 'Comprimé', frequency: '1x/jour', interactions: ['Simvastatine'] },
            { id: 2, name: 'Metformine', dosage: '500mg', form: 'Comprimé', frequency: '2x/jour', interactions: ['Alcool'] },
            { id: 3, name: 'Lisinopril', dosage: '10mg', form: 'Comprimé', frequency: '1x/jour', interactions: ['AINS', 'Potassium'] },
            { id: 4, name: 'Oméprazole', dosage: '20mg', form: 'Gélule', frequency: '1x/jour', interactions: ['Clopidogrel'] },
            { id: 5, name: 'Atorvastatine', dosage: '40mg', form: 'Comprimé', frequency: '1x/jour', interactions: ['érythromycine', 'Grapefruit'] },
        ],
        
        searchMedications(query) {
            if (query.length < 2) return;
            
            // Simulate search
            this.searchResults = this.commonMedications.filter(med => 
                med.name.toLowerCase().includes(query.toLowerCase())
            );
        },
        
        selectMedication(medication) {
            this.prescription.medications.push({
                id: Date.now(),
                name: medication.name,
                dosage: medication.dosage,
                form: medication.form,
                frequency: medication.frequency,
                quantity: 30,
                refills: 0,
                instructions: '',
                isControlled: false,
            });
            
            this.searchQuery = '';
            this.searchResults = [];
            
            this.checkInteractions();
        },
        
        removeMedication(id) {
            this.prescription.medications = this.prescription.medications.filter(m => m.id !== id);
            this.checkInteractions();
        },
        
        checkInteractions() {
            this.interactions = [];
            
            // Check for drug-drug interactions
            const medicationNames = this.prescription.medications.map(m => m.name);
            
            // Simulate interaction check
            if (medicationNames.includes('Amlodipine') && medicationNames.includes('Atorvastatine')) {
                this.interactions.push({
                    severity: 'moderate',
                    message: 'Interaction possible: риск усиления побочных эффектов при приёме вместе',
                    medications: ['Amlodipine', 'Atorvastatine'],
                });
            }
            
            // Check for allergies
            this.patient.allergies.forEach(allergy => {
                medicationNames.forEach(medName => {
                    if (medName.toLowerCase().includes(allergy.toLowerCase())) {
                        this.interactions.push({
                            severity: 'critical',
                            message: `Allergie connue: ${allergy}`,
                            medications: [medName],
                        });
                    }
                });
            });
            
            // Check for contraindications
            if (this.patient.conditions.includes('Hypertension') && this.prescription.medications.some(m => m.name === 'Oméprazole')) {
                this.interactions.push({
                    severity: 'minor',
                    message: 'Oméprazole peut réduire l\'efficacité de certains antihypertenseurs',
                    medications: ['Oméprazole'],
                });
            }
        },
        
        selectPharmacy(pharmacy) {
            this.selectedPharmacy = pharmacy;
        },
        
        sendToPharmacy() {
            if (!this.selectedPharmacy) {
                alert('Veuillez sélectionner une pharmacie');
                return;
            }
            
            if (this.prescription.medications.length === 0) {
                alert('Veuillez ajouter au moins un médicament');
                return;
            }
            
            alert(`Ordonnance envoyé à ${this.selectedPharmacy.name}`);
        },
        
        printPrescription() {
            window.print();
        },
        
        async savePrescription() {
            this.isSaving = true;
            
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            this.isSaving = false;
            alert('Ordonnance enregistrée avec succès');
        },
        
        requestRefill(medication) {
            alert(`Demande de renouvellement pour ${medication.name}`);
        },
        
        getInteractionSeverityClass(severity) {
            return {
                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': severity === 'critical',
                'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300': severity === 'moderate',
                'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': severity === 'minor',
            };
        },
    }));

    // ============================================
    // TELECONSULTATION CONTROLLER
    // ============================================
    Alpine.data('teleconsultationController', () => ({
        currentView: 'dashboard',
        
        switchView(view) {
            this.currentView = view;
        },
        
        startConsultation(consultationId) {
            window.location.href = `/teleconsultation/room/${consultationId}`;
        },
        
        joinWaitingRoom(appointmentId) {
            window.location.href = `/teleconsultation/waiting/${appointmentId}`;
        },
    }));
});

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Format duration in minutes to readable format
 */
function formatDuration(minutes) {
    if (minutes < 60) return `${minutes} min`;
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return mins > 0 ? `${hours}h ${mins}min` : `${hours}h`;
}

/**
 * Get time remaining until appointment
 */
function getTimeRemaining(scheduledTime) {
    const now = new Date();
    const [hours, minutes] = scheduledTime.split(':').map(Number);
    const scheduled = new Date();
    scheduled.setHours(hours, minutes, 0, 0);
    
    const diff = scheduled - now;
    if (diff < 0) return 'En retard';
    
    const mins = Math.floor(diff / 60000);
    if (mins < 60) return `${mins} min`;
    
    const hoursRemaining = Math.floor(mins / 60);
    return `${hoursRemaining}h ${mins % 60}min`;
}

/**
 * Calculate age from birthdate
 */
function calculateAge(birthdate) {
    const today = new Date();
    const birth = new Date(birthdate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
}

/**
 * Validate ICD-10 code format
 */
function validateICD10(code) {
    const pattern = /^[A-Z]\d{2}(\.\d{1,4})?$/;
    return pattern.test(code);
}

/**
 * Check drug interaction (simulated)
 */
function checkDrugInteraction(drug1, drug2) {
    const interactions = {
        'amlodipine-atorvastatin': { severity: 'moderate', description: ' риск миопатии' },
        'metformin-alcohol': { severity: 'severe', description: ' риск лактоацидоза' },
        'lisinopril-potassium': { severity: 'moderate', description: ' гиперкалиемия' },
    };
    
    const key1 = `${drug1.toLowerCase()}-${drug2.toLowerCase()}`;
    const key2 = `${drug2.toLowerCase()}-${drug1.toLowerCase()}`;
    
    return interactions[key1] || interactions[key2] || null;
}

// ============================================
// KEYBOARD SHORTCUTS
// ============================================

document.addEventListener('keydown', (e) => {
    // M: Toggle mute
    if (e.key.toLowerCase() === 'm' && e.ctrlKey) {
        e.preventDefault();
        // Toggle mute in consultation room
    }
    
    // V: Toggle video
    if (e.key.toLowerCase() === 'v' && e.ctrlKey) {
        e.preventDefault();
        // Toggle video in consultation room
    }
    
    // S: Screen share
    if (e.key.toLowerCase() === 's' && e.ctrlKey) {
        e.preventDefault();
        // Toggle screen share
    }
    
    // Escape: End consultation
    if (e.key === 'Escape') {
        // Show end consultation confirmation
    }
    
    // C: Chat
    if (e.key.toLowerCase() === 'c' && e.ctrlKey) {
        e.preventDefault();
        // Toggle chat panel
    }
    
    // N: Notes
    if (e.key.toLowerCase() === 'n' && e.ctrlKey) {
        e.preventDefault();
        // Toggle notes panel
    }
});
