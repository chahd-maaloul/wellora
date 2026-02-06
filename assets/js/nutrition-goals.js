/**
 * Nutrition Goals - JavaScript Module
 * Handles goal wizard, progress tracking, adjustments, and calculations
 */

// Goal Wizard Component
function goalWizard() {
    return {
        currentStep: 1,
        totalSteps: 5,
        loading: false,
        formData: {
            goalType: 'WEIGHT_LOSS',
            name: '',
            description: '',
            currentWeight: 80,
            targetWeight: 75,
            targetDate: '',
            dailyCalories: 1800,
            macroPreset: 'balanced',
            priority: 'MEDIUM',
            activityLevel: 'MODERATE',
            gender: 'MALE',
            height: 175,
            age: 30,
            milestones: []
        },
        
        init() {
            // Set default target date to 60 days from now
            const today = new Date();
            const targetDate = new Date(today);
            targetDate.setDate(today.getDate() + 60);
            this.formData.targetDate = targetDate.toISOString().split('T')[0];
        },

        get progress() {
            return (this.currentStep / this.totalSteps) * 100;
        },

        nextStep() {
            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
            }
        },

        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },

        goToStep(step) {
            if (step >= 1 && step <= this.totalSteps) {
                this.currentStep = step;
            }
        },

        // BMR Calculation using Mifflin-St Jeor Equation
        calculateBMR() {
            let bmr;
            if (this.formData.gender === 'MALE') {
                bmr = 10 * this.formData.currentWeight + 6.25 * this.formData.height - 5 * this.formData.age + 5;
            } else {
                bmr = 10 * this.formData.currentWeight + 6.25 * this.formData.height - 5 * this.formData.age - 161;
            }
            return Math.round(bmr);
        },

        // TDEE Calculation
        calculateTDEE() {
            const bmr = this.calculateBMR();
            const activityMultipliers = {
                'SEDENTARY': 1.2,
                'LIGHT': 1.375,
                'MODERATE': 1.55,
                'ACTIVE': 1.725,
                'VERY_ACTIVE': 1.9
            };
            const multiplier = activityMultipliers[this.formData.activityLevel] || 1.55;
            return Math.round(bmr * multiplier);
        },

        // Calculate recommended calories based on goal type
        calculateRecommendedCalories() {
            const tdee = this.calculateTDEE();
            const goalType = this.formData.goalType;
            
            let recommendedCalories;
            switch (goalType) {
                case 'WEIGHT_LOSS':
                    recommendedCalories = tdee - 500; // 500 kcal deficit
                    break;
                case 'WEIGHT_GAIN':
                    recommendedCalories = tdee + 500; // 500 kcal surplus
                    break;
                case 'MUSCLE_GAIN':
                    recommendedCalories = tdee + 300; // 300 kcal surplus with high protein
                    break;
                case 'MAINTENANCE':
                    recommendedCalories = tdee;
                    break;
                case 'HEALTH_IMPROVEMENT':
                    recommendedCalories = tdee - 300; // Moderate deficit
                    break;
                default:
                    recommendedCalories = tdee;
            }
            
            // Ensure minimum 1200 kcal for women, 1500 for men
            const minCalories = this.formData.gender === 'MALE' ? 1500 : 1200;
            return Math.max(recommendedCalories, minCalories);
        },

        // Macro calculations based on preset
        calculateMacros() {
            const calories = this.formData.dailyCalories;
            const preset = this.formData.macroPreset;
            
            let proteinRatio, carbsRatio, fatsRatio;
            
            switch (preset) {
                case 'HIGH_PROTEIN':
                    proteinRatio = 0.35;
                    carbsRatio = 0.35;
                    fatsRatio = 0.30;
                    break;
                case 'LOW_CARB':
                    proteinRatio = 0.30;
                    carbsRatio = 0.20;
                    fatsRatio = 0.50;
                    break;
                case 'BALANCED':
                default:
                    proteinRatio = 0.25;
                    carbsRatio = 0.45;
                    fatsRatio = 0.30;
                    break;
            }
            
            return {
                protein: Math.round((calories * proteinRatio) / 4),
                carbs: Math.round((calories * carbsRatio) / 4),
                fats: Math.round((calories * fatsRatio) / 9)
            };
        },

        // Calculate expected weight loss/gain per week
        calculateWeeklyChange() {
            const deficit = this.calculateTDEE() - this.formData.dailyCalories;
            // 7700 kcal ≈ 1kg of body weight
            const weeklyChange = (deficit * 7) / 7700;
            return weeklyChange; // Positive = weight gain, Negative = weight loss
        },

        // Calculate estimated time to reach goal
        calculateEstimatedWeeks() {
            const weightDiff = this.formData.targetWeight - this.formData.currentWeight;
            const weeklyChange = this.calculateWeeklyChange();
            
            if (weeklyChange === 0) return Infinity;
            
            const weeks = Math.abs(weightDiff / weeklyChange);
            return Math.ceil(weeks);
        },

        addMilestone() {
            const weightToLose = this.formData.targetWeight - this.formData.currentWeight;
            const milestoneCount = this.formData.milestones.length + 1;
            const milestoneWeight = this.formData.currentWeight + (weightToLose * milestoneCount / 4);
            
            const today = new Date();
            const targetDate = new Date(today);
            targetDate.setDate(today.getDate() + (this.calculateEstimatedWeeks() * 7 * milestoneCount / 4));
            
            this.formData.milestones.push({
                id: Date.now(),
                name: `Jalon ${milestoneCount}`,
                targetWeight: milestoneWeight.toFixed(1),
                targetDate: targetDate.toISOString().split('T')[0]
            });
        },

        removeMilestone(id) {
            this.formData.milestones = this.formData.milestones.filter(m => m.id !== id);
        },

        async submitGoal() {
            this.loading = true;
            
            try {
                const macros = this.calculateMacros();
                const formData = {
                    ...this.formData,
                    bmr: this.calculateBMR(),
                    tdee: this.calculateTDEE(),
                    macros: macros,
                    weeklyChange: this.calculateWeeklyChange(),
                    estimatedWeeks: this.calculateEstimatedWeeks()
                };
                
                // In a real app, this would be an AJAX call
                console.log('Submitting goal:', formData);
                
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Redirect to goal detail page
                window.location.href = '/nutrition/goals/1';
            } catch (error) {
                console.error('Error submitting goal:', error);
            } finally {
                this.loading = false;
            }
        }
    };
}

// Goal Detail Component
function goalDetail() {
    return {
        loading: false,
        goalId: null,
        
        async init() {
            // Load goal data from API
            await this.loadGoalData();
        },
        
        async loadGoalData() {
            // Simulate loading goal data
            console.log('Loading goal data for ID:', this.goalId);
        },
        
        markMilestoneComplete(milestoneId) {
            console.log('Marking milestone as complete:', milestoneId);
        }
    };
}

// Progress Tracking Component
function progressTracking() {
    return {
        loading: false,
        dateRange: 'week',
        newEntry: {
            weight: null,
            calories: null,
            protein: null,
            date: new Date().toISOString().split('T')[0]
        },
        
        async recordProgress() {
            this.loading = true;
            
            try {
                console.log('Recording progress:', this.newEntry);
                await new Promise(resolve => setTimeout(resolve, 500));
                
                // Show success message
                alert('Progression enregistrée avec succès !');
                
                // Reset form
                this.newEntry = {
                    weight: null,
                    calories: null,
                    protein: null,
                    date: new Date().toISOString().split('T')[0]
                };
                
                // Reload data
                // location.reload();
            } catch (error) {
                console.error('Error recording progress:', error);
                alert('Erreur lors de l\'enregistrement');
            } finally {
                this.loading = false;
            }
        },
        
        changeDateRange(range) {
            this.dateRange = range;
            this.loadData();
        },
        
        async loadData() {
            console.log('Loading data for range:', this.dateRange);
        },
        
        // Initialize charts when component is ready
        initCharts() {
            // This would be called after the DOM is ready
            if (typeof Chart !== 'undefined') {
                this.renderWeightChart();
                this.renderCaloriesChart();
            }
        },
        
        renderWeightChart() {
            const ctx = document.getElementById('weightChart');
            if (!ctx) return;
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                    datasets: [{
                        label: '体重 (kg)',
                        data: [79.2, 79.0, 78.8, 78.7, 78.6, 78.5, 78.5],
                        borderColor: '#00A790',
                        backgroundColor: 'rgba(0, 167, 144, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
        },
        
        renderCaloriesChart() {
            const ctx = document.getElementById('caloriesChart');
            if (!ctx) return;
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                    datasets: [{
                        label: 'Calories',
                        data: [1820, 1900, 1750, 1780, 1850, 1920, 1800],
                        backgroundColor: '#3B82F6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    };
}

// Goal Adjustment Component
function goalAdjustment() {
    return {
        adjustment: {
            reason: '',
            dailyCalories: 1800,
            macroPreset: 'balanced',
            targetDate: '',
            targetWeight: 72,
            notes: ''
        },
        
        init() {
            // Initialize with default values
            this.adjustment.dailyCalories = 1800;
            this.adjustment.targetWeight = 72;
            
            const today = new Date();
            const targetDate = new Date(today);
            targetDate.setDate(today.getDate() + 45);
            this.adjustment.targetDate = targetDate.toISOString().split('T')[0];
        },
        
        getMacros() {
            const calories = this.adjustment.dailyCalories;
            const preset = this.adjustment.macroPreset;
            
            let proteinRatio, carbsRatio, fatsRatio;
            
            switch (preset) {
                case 'HIGH_PROTEIN':
                    proteinRatio = 0.35;
                    carbsRatio = 0.35;
                    fatsRatio = 0.30;
                    break;
                case 'LOW_CARB':
                    proteinRatio = 0.30;
                    carbsRatio = 0.20;
                    fatsRatio = 0.50;
                    break;
                case 'BALANCED':
                default:
                    proteinRatio = 0.25;
                    carbsRatio = 0.45;
                    fatsRatio = 0.30;
                    break;
            }
            
            return {
                protein: Math.round((calories * proteinRatio) / 4),
                proteinPercent: Math.round(proteinRatio * 100),
                carbs: Math.round((calories * carbsRatio) / 4),
                carbsPercent: Math.round(carbsRatio * 100),
                fats: Math.round((calories * fatsRatio) / 9),
                fatsPercent: Math.round(fatsRatio * 100)
            };
        },
        
        getDaysRemaining() {
            if (!this.adjustment.targetDate) return 0;
            
            const today = new Date();
            const target = new Date(this.adjustment.targetDate);
            const diffTime = target - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            return diffDays > 0 ? diffDays : 0;
        },
        
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR');
        },
        
        getAdvice() {
            const reason = this.adjustment.reason;
            
            const advice = {
                'plateau': 'Essayez de varier vos exercices ou de légèrement augmenter votre activité quotidienne pour surmonter ce plateau.',
                'too_fast': 'Une perte de poids rapide peut être malsaine. Considérez augmentez légèrement vos apports caloriques.',
                'difficulty': 'Vos objectifs semblent trop stricts. Essayez de les ajuster pour les rendre plus réalisables.',
                'lifestyle': 'Un changement de mode de vie est une bonne opportunité pour recalculer vos besoins.',
                '': 'Sélectionnez une raison pour obtenir des conseils personnalisés.'
            };
            
            return advice[reason] || advice[''];
        },
        
        async saveDraft() {
            console.log('Saving adjustment draft:', this.adjustment);
            alert('Brouillon enregistré !');
        },
        
        async submitAdjustment() {
            if (!this.adjustment.reason) {
                alert('Veuillez sélectionner une raison pour l\'ajustement.');
                return;
            }
            
            this.loading = true;
            
            try {
                console.log('Submitting adjustment:', this.adjustment);
                await new Promise(resolve => setTimeout(resolve, 500));
                
                alert('Ajustement enregistré avec succès !');
                window.location.href = '/nutrition/goals/1';
            } catch (error) {
                console.error('Error submitting adjustment:', error);
                alert('Erreur lors de l\'enregistrement');
            } finally {
                this.loading = false;
            }
        }
    };
}

// Success Metrics Component
function successMetrics() {
    return {
        loading: false,
        metrics: {
            overallCompletion: 65,
            completedCriteria: 5,
            totalCriteria: 8,
            remainingDays: 45,
            prediction: {
                successRate: 78,
                estimatedDate: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
                estimatedWeight: 71.5
            }
        },
        criteria: {
            weight: { current: 78.5, target: 72.0, progress: 65, completed: false },
            calories: { adherence: 87, completed: false },
            timeline: { daysOnTrack: 28, totalDays: 30, completed: false },
            macros: { protein: 88, carbs: 95, fats: 97, adherence: 92, completed: false },
            consistency: { streak: 12, completed: false },
            hydration: { adherence: 75, completed: false }
        },
        recommendations: [
            {
                title: 'Améliorer l\'hydratation',
                description: 'Vous atteignez seulement 75% de votre objectif d\'hydratation. Essayez de boire un verre d\'eau à chaque repas.',
                priority: 'medium',
                actionText: 'Voir les conseils',
                actionUrl: '#'
            }
        ],
        
        async loadMetrics() {
            console.log('Loading metrics...');
        },
        
        getCriteriaProgress(criterion) {
            return this.criteria[criterion]?.progress || 0;
        }
    };
}

// Utility Functions
const NutritionGoalsUtils = {
    // Calculate BMI
    calculateBMI(weight, height) {
        const heightInMeters = height / 100;
        return (weight / (heightInMeters * heightInMeters)).toFixed(1);
    },
    
    // Get BMI Category
    getBMICategory(bmi) {
        if (bmi < 18.5) return 'Sous-poids';
        if (bmi < 25) return 'Normal';
        if (bmi < 30) return 'Surpoids';
        return 'Obèse';
    },
    
    // Format date to French locale
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    },
    
    // Calculate weeks between dates
    weeksBetween(date1, date2) {
        const start = new Date(date1);
        const end = new Date(date2);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return Math.round(diffDays / 7);
    },
    
    // Weight category based on BMI
    getWeightCategory(bmi) {
        const categories = [
            { max: 18.5, label: 'Sous-poids', color: 'blue' },
            { max: 25, label: 'Normal', color: 'green' },
            { max: 30, label: 'Surpoids', color: 'yellow' },
            { max: Infinity, label: 'Obèse', color: 'red' }
        ];
        
        return categories.find(cat => bmi < cat.max) || categories[categories.length - 1];
    }
};

// Export for global use
window.NutritionGoals = {
    wizard: goalWizard,
    detail: goalDetail,
    tracking: progressTracking,
    adjustment: goalAdjustment,
    metrics: successMetrics,
    utils: NutritionGoalsUtils
};
