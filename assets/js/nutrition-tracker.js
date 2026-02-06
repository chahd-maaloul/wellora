/**
 * Nutrition Tracker Module
 * Handles food logging, barcode scanning, and nutrition tracking
 */

document.addEventListener('alpine:init', () => {
    // Global nutrition state
    window.nutritionState = {
        todayCalories: 1850,
        calorieTarget: 2000,
        waterIntake: 5,
        waterTarget: 8,
        macros: {
            proteins: 85,
            proteinTarget: 120,
            carbs: 180,
            carbTarget: 250,
            fats: 60,
            fatTarget: 70
        }
    };

    // Quick Log Component
    Alpine.data('quickLog', () => ({
        showQuickLog: false,
        foodSearch: '',
        selectedMeal: 'lunch',
        searchResults: [],
        scannedProduct: null,
        manualBarcode: '',
        isScanning: false,
        scannerActive: false,
        
        // Quick add foods (common foods)
        quickAddFoods: [
            { name: 'Pomme', calories: 95, unit: 'pièce' },
            { name: 'Yaourt', calories: 150, unit: 'pièce' },
            { name: 'Pain', calories: 265, unit: 'tranche' },
            { name: 'Oeuf', calories: 78, unit: 'pièce' },
            { name: 'Poulet', calories: 165, unit: '100g' },
            { name: 'Riz', calories: 130, unit: '100g' },
            { name: 'Salade', calories: 20, unit: 'portion' },
            { name: 'Banane', calories: 105, unit: 'pièce' },
            { name: 'Lait', calories: 120, unit: 'verre' },
            { name: 'Fromage', calories: 110, unit: 'portion' }
        ],
        
        // Manual food entry
        manualFood: {
            name: '',
            calories: '',
            proteins: '',
            carbs: '',
            fats: '',
            quantity: 1
        },
        
        init() {
            // Listen for open quick log events
            this.$watch('showQuickLog', (value) => {
                if (value) {
                    this.resetForm();
                }
            });
        },
        
        resetForm() {
            this.foodSearch = '';
            this.searchResults = [];
            this.manualFood = {
                name: '',
                calories: '',
                proteins: '',
                carbs: '',
                fats: '',
                quantity: 1
            };
        },
        
        // Search foods from API
        async searchFoods() {
            if (this.foodSearch.length < 2) {
                this.searchResults = [];
                return;
            }
            
            try {
                const response = await fetch(`/api/foods/search?q=${encodeURIComponent(this.foodSearch)}`);
                const data = await response.json();
                this.searchResults = data.results || [];
            } catch (error) {
                console.error('Food search error:', error);
                // Use mock data for demo
                this.searchResults = this.getMockFoods(this.foodSearch);
            }
        },
        
        // Mock food data for demo
        getMockFoods(query) {
            const mockFoods = [
                { id: 1, name: 'Pomme rouge', calories: 95, portion: 180, unit: 'g', brand: 'Fruits frais' },
                { id: 2, name: 'Banane mûre', calories: 105, portion: 120, unit: 'g', brand: 'Fruits tropicaux' },
                { id: 3, name: 'Poulet rôti', calories: 165, portion: 100, unit: 'g', brand: 'Viandes fraîches' },
                { id: 4, name: 'Riz blanc', calories: 130, portion: 100, unit: 'g', brand: 'Céréales' },
                { id: 5, name: 'Salade composée', calories: 250, portion: 250, unit: 'g', brand: 'Frais & Bon' },
                { id: 6, name: 'Yaourt nature', calories: 85, portion: 125, unit: 'g', brand: 'Danone' },
                { id: 7, name: 'Pain complet', calories: 81, portion: 50, unit: 'g', brand: 'Le Moulin' },
                { id: 8, name: 'Oeuf dur', calories: 78, portion: 50, unit: 'g', brand: 'Oeufs frais' }
            ];
            
            return mockFoods.filter(food => 
                food.name.toLowerCase().includes(query.toLowerCase())
            );
        },
        
        // Select food from search results
        selectFood(food) {
            this.manualFood.name = food.name;
            this.manualFood.calories = food.calories;
            this.searchResults = [];
        },
        
        // Add quick food
        addQuickFood(name, calories, unit) {
            this.submitFoodEntry(name, calories, this.selectedMeal, this.manualFood.quantity);
        },
        
        // Submit food entry
        submitFood() {
            const { name, calories, quantity } = this.manualFood;
            
            if (!name || !calories) {
                alert('Veuillez remplir le nom et les calories');
                return;
            }
            
            this.submitFoodEntry(name, calories, this.selectedMeal, quantity);
            this.showQuickLog = false;
        },
        
        // Submit to API
        async submitFoodEntry(name, calories, mealType, quantity = 1) {
            try {
                const response = await fetch('/api/nutrition/log', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        foodName: name,
                        calories: parseInt(calories) * quantity,
                        mealType: mealType,
                        date: new Date().toISOString()
                    })
                });
                
                if (response.ok) {
                    // Emit event to update UI
                    this.$dispatch('food-logged', { name, calories, mealType });
                    this.showToast('Aliment ajouté avec succès !', 'success');
                }
            } catch (error) {
                console.error('Error logging food:', error);
                this.showToast('Erreur lors de l\'ajout', 'error');
            }
        },
        
        // Barcode scanner methods
        async startScanner() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' } 
                });
                this.$refs.scannerVideo.srcObject = stream;
                this.isScanning = true;
                this.scannerActive = true;
            } catch (error) {
                console.error('Camera access denied:', error);
                this.showToast('Impossible d\'accéder à la caméra', 'error');
            }
        },
        
        stopScanner() {
            const stream = this.$refs.scannerVideo.srcObject;
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                this.$refs.scannerVideo.srcObject = null;
            }
            this.isScanning = false;
            this.scannerActive = false;
        },
        
        // Lookup barcode
        async lookupBarcode(barcode) {
            if (!barcode) return;
            
            try {
                const response = await fetch(`/api/foods/barcode/${barcode}`);
                const data = await response.json();
                
                if (data.found) {
                    this.scannedProduct = {
                        name: data.name,
                        brand: data.brand,
                        calories: data.calories,
                        portion: data.portion
                    };
                } else {
                    this.showToast('Produit non trouvé', 'error');
                }
            } catch (error) {
                console.error('Barcode lookup error:', error);
                this.showToast('Erreur de recherche', 'error');
            }
        },
        
        addScannedProduct() {
            if (this.scannedProduct) {
                this.submitFoodEntry(
                    this.scannedProduct.name,
                    this.scannedProduct.calories,
                    this.selectedMeal
                );
                this.scannedProduct = null;
                this.showBarcodeScanner = false;
            }
        },
        
        // Toast notification
        showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 animate-slide-up ${
                type === 'success' ? 'bg-green-500 text-white' : 
                type === 'error' ? 'bg-red-500 text-white' : 'bg-wellcare-500 text-white'
            }`;
            toast.innerHTML = `
                <div class="flex items-center gap-2">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('opacity-0', 'transition-opacity');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    }));

    // Water Tracker Component
    Alpine.data('waterTracker', () => ({
        waterIntake: 5,
        waterTarget: 8,
        
        toggleWater(level) {
            if (level <= this.waterTarget) {
                this.waterIntake = level;
                this.saveWaterIntake();
            }
        },
        
        async saveWaterIntake() {
            try {
                await fetch('/api/nutrition/water', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ intake: this.waterIntake })
                });
            } catch (error) {
                console.error('Error saving water:', error);
            }
        }
    }));

    // Nutrition Charts
    function initNutritionCharts() {
        // Weekly calories chart
        const caloriesCtx = document.getElementById('weeklyCaloriesChart');
        if (caloriesCtx) {
            new Chart(caloriesCtx, {
                type: 'bar',
                data: {
                    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                    datasets: [{
                        label: 'Calories',
                        data: [1850, 2100, 1950, 2200, 1800, 1650, 2000],
                        backgroundColor: 'rgba(0, 167, 144, 0.8)',
                        borderRadius: 6
                    }, {
                        label: 'Objectif',
                        data: [2000, 2000, 2000, 2000, 2000, 2000, 2000],
                        type: 'line',
                        borderColor: 'rgba(156, 163, 175, 0.5)',
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(156, 163, 175, 0.1)' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Macro distribution chart
        const macroCtx = document.getElementById('macroChart');
        if (macroCtx) {
            new Chart(macroCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Protéines', 'Glucides', 'Lipides'],
                    datasets: [{
                        data: [85, 180, 60],
                        backgroundColor: ['#f59e0b', '#22c55e', '#a855f7'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    cutout: '60%'
                }
            });
        }
    }

    // Initialize charts on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNutritionCharts);
    } else {
        initNutritionCharts();
    }
});
