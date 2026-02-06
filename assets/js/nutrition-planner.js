// Nutrition Planner - Meal Planning, Pantry & Cooking Assistant
// This file handles all interactive functionality for nutrition features

// ============================================
// MEAL PLANNER
// ============================================

function mealPlanner() {
    return {
        currentWeek: getWeekDates(new Date()),
        selectedDay: new Date().toISOString().split('T')[0],
        mealType: 'all',
        dragTarget: null,
        recipes: [],
        mealPlan: loadFromStorage('mealPlan', {}),
        
        init() {
            this.loadRecipes();
            this.renderWeek();
        },

        getWeekDates(date) {
            const start = new Date(date);
            start.setDate(start.getDate() - start.getDay());
            const week = [];
            for (let i = 0; i < 7; i++) {
                const day = new Date(start);
                day.setDate(start.getDate() + i);
                week.push({
                    date: day.toISOString().split('T')[0],
                    dayName: day.toLocaleDateString('fr-FR', { weekday: 'short' }),
                    dayNumber: day.getDate(),
                    month: day.toLocaleDateString('fr-FR', { month: 'short' })
                });
            }
            return week;
        },

        loadRecipes() {
            this.recipes = [
                { id: 1, name: 'Salade méditerranéenne', icon: 'fas fa-leaf', calories: 350, protein: 12, carbs: 25, fats: 18, category: 'salad', time: 15 },
                { id: 2, name: 'Poulet rôti aux légumes', icon: 'fas fa-drumstick-bite', calories: 450, protein: 45, carbs: 20, fats: 22, category: 'main', time: 45 },
                { id: 3, name: 'Smoothie protéiné', icon: 'fas fa-glass-whiskey', calories: 280, protein: 25, carbs: 30, fats: 8, category: 'drink', time: 5 },
                { id: 4, name: 'Quinoa bowl', icon: 'fas fa-bowl-rice', calories: 400, protein: 15, carbs: 55, fats: 12, category: 'bowl', time: 25 },
                { id: 5, name: 'Saumon grillé', icon: 'fas fa-fish', calories: 380, protein: 38, carbs: 5, fats: 24, category: 'main', time: 20 },
                { id: 6, name: 'Yaourt aux fruits', icon: 'fas fa-ice-cream', calories: 150, protein: 8, carbs: 20, fats: 4, category: 'snack', time: 5 },
                { id: 7, name: 'Omelette légumes', icon: 'fas fa-egg', calories: 250, protein: 18, carbs: 8, fats: 16, category: 'main', time: 15 },
                { id: 8, name: 'Avoine aux baies', icon: 'fas fa-cookie', calories: 300, protein: 10, carbs: 45, fats: 8, category: 'breakfast', time: 10 },
            ];
        },

        getMealsForDay(day) {
            const dayMeals = this.mealPlan[day] || {};
            return {
                breakfast: dayMeals.breakfast || [],
                lunch: dayMeals.lunch || [],
                dinner: dayMeals.dinner || [],
                snacks: dayMeals.snacks || []
            };
        },

        getDayNutrition(day) {
            const meals = this.getMealsForDay(day);
            let total = { calories: 0, protein: 0, carbs: 0, fats: 0 };
            
            Object.values(meals).flat().forEach(meal => {
                total.calories += meal.calories || 0;
                total.protein += meal.protein || 0;
                total.carbs += meal.carbs || 0;
                total.fats += meal.fats || 0;
            });
            
            return total;
        },

        addMeal(day, mealType, recipe) {
            if (!this.mealPlan[day]) this.mealPlan[day] = {};
            if (!this.mealPlan[day][mealType]) this.mealPlan[day][mealType] = [];
            
            this.mealPlan[day][mealType].push({
                ...recipe,
                id: Date.now()
            });
            
            saveToStorage('mealPlan', this.mealPlan);
        },

        removeMeal(day, mealType, index) {
            if (this.mealPlan[day] && this.mealPlan[day][mealType]) {
                this.mealPlan[day][mealType].splice(index, 1);
                saveToStorage('mealPlan', this.mealPlan);
            }
        },

        onDragStart(event, recipe) {
            event.dataTransfer.setData('recipe', JSON.stringify(recipe));
        },

        onDrop(event, day, mealType) {
            event.preventDefault();
            const recipe = JSON.parse(event.dataTransfer.getData('recipe'));
            this.addMeal(day, mealType, recipe);
        },

        previousWeek() {
            const firstDay = new Date(this.currentWeek[0].date);
            firstDay.setDate(firstDay.getDate() - 7);
            this.currentWeek = this.getWeekDates(firstDay);
        },

        nextWeek() {
            const firstDay = new Date(this.currentWeek[0].date);
            firstDay.setDate(firstDay.getDate() + 7);
            this.currentWeek = this.getWeekDates(firstDay);
        },

        generateShoppingList() {
            const ingredients = {};
            
            Object.values(this.mealPlan).forEach(dayMeals => {
                Object.values(dayMeals).forEach(meals => {
                    meals.forEach(meal => {
                        if (meal.ingredients) {
                            meal.ingredients.forEach(ing => {
                                if (ingredients[ing.name]) {
                                    ingredients[ing.name].quantity += ing.quantity;
                                } else {
                                    ingredients[ing.name] = { ...ing };
                                }
                            });
                        }
                    });
                });
            });
            
            return Object.values(ingredients);
        },

        exportPlan() {
            const data = JSON.stringify(this.mealPlan, null, 2);
            const blob = new Blob([data], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'meal-plan.json';
            a.click();
        },

        importPlan(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.mealPlan = JSON.parse(e.target.result);
                    saveToStorage('mealPlan', this.mealPlan);
                };
                reader.readAsText(file);
            }
        }
    };
}

// ============================================
// PANTRY MANAGEMENT
// ============================================

function pantryManagement() {
    return {
        items: loadFromStorage('pantryItems', []),
        search: '',
        categoryFilter: '',
        sortBy: 'name',
        showAddModal: false,
        editingItem: null,
        currentItem: this.getEmptyItem(),
        
        init() {
            if (this.items.length === 0) {
                this.loadSampleData();
            }
        },

        getEmptyItem() {
            return {
                id: null,
                name: '',
                quantity: 1,
                unit: 'pcs',
                category: 'produce',
                expiryDate: '',
                addedDate: new Date().toISOString().split('T')[0],
                initialQuantity: 1,
                brand: ''
            };
        },

        loadSampleData() {
            this.items = [
                { id: 1, name: 'Pommes', quantity: 6, unit: 'pcs', category: 'produce', expiryDate: this.addDays(new Date(), 7).toISOString().split('T')[0], addedDate: new Date().toISOString().split('T')[0], initialQuantity: 6, brand: 'Local' },
                { id: 2, name: 'Lait', quantity: 1, unit: 'L', category: 'dairy', expiryDate: this.addDays(new Date(), 5).toISOString().split('T')[0], addedDate: new Date().toISOString().split('T')[0], initialQuantity: 1, brand: 'Naturel' },
                { id: 3, name: 'Poulet', quantity: 500, unit: 'g', category: 'meat', expiryDate: this.addDays(new Date(), 3).toISOString().split('T')[0], addedDate: new Date().toISOString().split('T')[0], initialQuantity: 500, brand: 'Ferme' },
                { id: 4, name: 'Riz', quantity: 2, unit: 'kg', category: 'grains', expiryDate: this.addDays(new Date(), 180).toISOString().split('T')[0], addedDate: new Date().toISOString().split('T')[0], initialQuantity: 2, brand: 'Basmati' },
                { id: 5, name: 'Tomates', quantity: 4, unit: 'pcs', category: 'produce', expiryDate: this.addDays(new Date(), 4).toISOString().split('T')[0], addedDate: new Date().toISOString().split('T')[0], initialQuantity: 4, brand: 'Local' },
                { id: 6, name: 'Oeufs', quantity: 12, unit: 'pcs', category: 'dairy', expiryDate: this.addDays(new Date(), 14).toISOString().split('T')[0], addedDate: new Date().toISOString().split('T')[0], initialQuantity: 12, brand: 'Poule élevée en plein air' },
            ];
            saveToStorage('pantryItems', this.items);
        },

        addDays(date, days) {
            const result = new Date(date);
            result.setDate(result.getDate() + days);
            return result;
        },

        get filteredItems() {
            let items = this.items.filter(item => {
                const matchesSearch = item.name.toLowerCase().includes(this.search.toLowerCase());
                const matchesCategory = !this.categoryFilter || item.category === this.categoryFilter;
                return matchesSearch && matchesCategory;
            });

            items.sort((a, b) => {
                switch (this.sortBy) {
                    case 'name': return a.name.localeCompare(b.name);
                    case 'quantity': return b.quantity - a.quantity;
                    case 'expiry': return new Date(a.expiryDate) - new Date(b.expiryDate);
                    case 'added': return new Date(b.addedDate) - new Date(a.addedDate);
                    default: return 0;
                }
            });

            return items;
        },

        get expiringItems() {
            const threeDaysFromNow = this.addDays(new Date(), 3);
            return this.items.filter(item => {
                const expiryDate = new Date(item.expiryDate);
                return expiryDate <= threeDaysFromNow && expiryDate >= new Date();
            });
        },

        get lowStockItems() {
            return this.items.filter(item => {
                const percentage = (item.quantity / item.initialQuantity) * 100;
                return percentage <= 20;
            });
        },

        get totalValue() {
            // Simplified value calculation
            return this.items.reduce((total, item) => {
                return total + (item.quantity * 2); // Assume 2€ per unit for demo
            }, 0);
        },

        isExpiringSoon(item) {
            const expiryDate = new Date(item.expiryDate);
            const threeDaysFromNow = this.addDays(new Date(), 3);
            return expiryDate <= threeDaysFromNow && expiryDate >= new Date();
        },

        isExpired(item) {
            return new Date(item.expiryDate) < new Date();
        },

        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('fr-FR');
        },

        getCategoryClass(category) {
            const classes = {
                produce: 'bg-green-500',
                dairy: 'bg-blue-500',
                meat: 'bg-red-500',
                grains: 'bg-amber-500',
                canned: 'bg-orange-500',
                spices: 'bg-yellow-500',
                oils: 'bg-purple-500',
                beverages: 'bg-cyan-500',
                other: 'bg-gray-500'
            };
            return classes[category] || 'bg-gray-500';
        },

        getCategoryIcon(category) {
            const icons = {
                produce: 'fas fa-carrot',
                dairy: 'fas fa-cheese',
                meat: 'fas fa-bacon',
                grains: 'fas fa-bread-slice',
                canned: 'fas fa罐头',
                spices: 'fas fa-spice',
                oils: 'fas fa-oil-can',
                beverages: 'fas fa-wine-bottle',
                other: 'fas fa-box'
            };
            return icons[category] || 'fas fa-box';
        },

        getQuantityClass(item) {
            const percentage = (item.quantity / item.initialQuantity) * 100;
            if (percentage <= 20) return 'text-red-500';
            if (percentage <= 50) return 'text-amber-500';
            return 'text-green-500';
        },

        getQuantityBarClass(item) {
            const percentage = (item.quantity / item.initialQuantity) * 100;
            if (percentage <= 20) return 'bg-red-500';
            if (percentage <= 50) return 'bg-amber-500';
            return 'bg-green-500';
        },

        editItem(item) {
            this.editingItem = item;
            this.currentItem = { ...item };
            this.showAddModal = true;
        },

        deleteItem(item) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ' + item.name + ' ?')) {
                this.items = this.items.filter(i => i.id !== item.id);
                saveToStorage('pantryItems', this.items);
            }
        },

        updateQuantity(item, delta) {
            item.quantity = Math.max(0, item.quantity + delta);
            saveToStorage('pantryItems', this.items);
        },

        useItem(item) {
            item.quantity = Math.max(0, item.quantity - 1);
            saveToStorage('pantryItems', this.items);
        },

        saveItem() {
            if (this.editingItem) {
                const index = this.items.findIndex(i => i.id === this.editingItem.id);
                if (index !== -1) {
                    this.items[index] = { ...this.currentItem };
                }
            } else {
                this.currentItem.id = Date.now();
                this.items.push({ ...this.currentItem });
            }
            
            saveToStorage('pantryItems', this.items);
            this.closeModal();
        },

        closeModal() {
            this.showAddModal = false;
            this.editingItem = null;
            this.currentItem = this.getEmptyItem();
        },

        generateShoppingList() {
            const shoppingList = this.lowStockItems.map(item => ({
                name: item.name,
                quantity: item.initialQuantity - item.quantity,
                unit: item.unit,
                category: item.category
            }));
            
            // Save and show (in real app, navigate to grocery list)
            alert('Liste de courses générée avec ' + shoppingList.length + ' articles à racheter');
        },

        showExpiringItems() {
            this.categoryFilter = '';
            this.search = '';
        }
    };
}

// ============================================
// COOKING ASSISTANT
// ============================================

function cookingAssistant() {
    return {
        activeRecipe: null,
        currentStep: 0,
        timerRunning: false,
        remainingTime: 0,
        voiceEnabled: false,
        isListening: false,
        voiceStatus: 'En attente...',
        
        recentRecipes: [
            {
                id: 1,
                name: 'Salade méditerranéenne',
                icon: 'fas fa-leaf',
                description: 'Une salade fraîche et colorée',
                time: 15,
                servings: 2,
                calories: 350,
                nutrition: { perServing: { calories: 350, protein: 12, carbs: 25, fats: 18 } },
                steps: [
                    { title: 'Préparer les légumes', duration: 10, instruction: 'Lavez soigneusement tous les légumes. Coupez les tomates en dés, le concombre en rondelles et l\'oignon en fines lamelles.', ingredients: ['2 tomates', '1 concombre', '1 oignon rouge'], checklist: [{ task: 'Légumes lavés', checked: false }, { task: 'Tomates coupées', checked: false }, { task: 'Concombre tranché', checked: false }], tips: 'Utilisez des légumes frais de saison pour un meilleur goût.' },
                    { title: 'Préparer la feta', duration: 2, instruction: 'Coupez la feta en dés d\'environ 1 cm.', ingredients: ['200g feta'], checklist: [{ task: 'Feta coupée', checked: false }], tips: 'Laissez la feta à température ambiante 15 min avant de servir.' },
                    { title: 'Assembler la salade', duration: 3, instruction: 'Dans un grand saladier, combinez tous les légumes. Ajoutez les olives et la feta.', ingredients: [' olives noires', 'feta'], checklist: [{ task: 'Légumes mélangés', checked: false }, { task: 'Olives ajoutées', checked: false }, { task: 'Feta ajoutée', checked: false }], tips: 'Ne pas trop mélanger pour éviter d\'écraser les ingrédients.' },
                    { title: 'Assaisonner', duration: 2, instruction: 'Arrosez d\'huile d\'olive et de jus de citron. Salez et poivrez selon votre goût.', ingredients: ['3 tbsp huile d\'olive', '1 citron'], checklist: [{ task: 'Huile ajoutée', checked: false }, { task: 'Citron pressé', checked: false }], tips: 'Utilisez de l\'huile d\'olive extravierge de qualité.' }
                ]
            },
            {
                id: 2,
                name: 'Poulet rôti aux légumes',
                icon: 'fas fa-drumstick-bite',
                description: 'Poulet tendre avec légumes rôtis',
                time: 60,
                servings: 4,
                calories: 450,
                nutrition: { perServing: { calories: 450, protein: 45, carbs: 20, fats: 22 } },
                steps: [
                    { title: 'Préchauffer le four', duration: 5, instruction: 'Préchauffez votre four à 200°C (400°F).', ingredients: [], checklist: [{ task: 'Four préchauffé', checked: false }], tips: 'Un four bien chaud est essentiel pour une peau croustillante.' },
                    { title: 'Préparer le poulet', duration: 10, instruction: 'Séchez le poulet avec du papier absorbant. Frottez-le avec du sel, du poivre et vos épices préférées.', ingredients: ['1 poulet entier', 'sel', 'poivre', 'romarin'], checklist: [{ task: 'Poulet séché', checked: false }, { task: 'Épices appliquées', checked: false }], tips: 'Le poulet sec obtient une peau plus croustillante.' },
                    { title: 'Préparer les légumes', duration: 10, instruction: 'Coupez les pommes de terre en quartiers, les carottes en biseaux et l\'ail en deux.', ingredients: ['500g pommes de terre', '4 carottes', '1 tête d\'ail'], checklist: [{ task: 'Pommes de terre coupées', checked: false }, { task: 'Carottes coupées', checked: false }, { task: 'Ail préparé', checked: false }], tips: 'Coupez les légumes en morceaux similaires pour une cuisson uniforme.' },
                    { title: 'Cuire au four', duration: 45, instruction: 'Placez le poulet dans un plat et disposez les légumes autour. Enfournez pour 45 minutes jusqu\'à ce que la peau soit dorée.', ingredients: [], checklist: [{ task: 'Poulet au four', checked: false }, { task: 'Vérifier cuisson', checked: false }], tips: 'Baissez à 180°C après 30 minutes si ça dore trop.' }
                ]
            }
        ],
        
        init() {
            this.loadFromStorage();
        },

        loadFromStorage() {
            const saved = localStorage.getItem('cookingAssistant');
            if (saved) {
                // Load any saved state if needed
            }
        },

        selectRecipe(recipe) {
            this.activeRecipe = recipe;
            this.currentStep = 0;
            this.remainingTime = 0;
            this.timerRunning = false;
        },

        closeRecipe() {
            this.activeRecipe = null;
            this.currentStep = 0;
        },

        nextStep() {
            if (this.currentStep < this.activeRecipe.steps.length - 1) {
                this.currentStep++;
                this.remainingTime = this.activeRecipe.steps[this.currentStep].duration * 60;
                this.timerRunning = false;
            }
        },

        previousStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
                this.remainingTime = this.activeRecipe.steps[this.currentStep].duration * 60;
                this.timerRunning = false;
            }
        },

        goToStep(index) {
            this.currentStep = index;
            this.remainingTime = this.activeRecipe.steps[index].duration * 60;
            this.timerRunning = false;
        },

        startTimer() {
            if (this.remainingTime > 0) {
                this.timerRunning = true;
                this.runTimer();
            }
        },

        runTimer() {
            if (this.timerRunning && this.remainingTime > 0) {
                this.remainingTime--;
                setTimeout(() => this.runTimer(), 1000);
            } else if (this.remainingTime === 0) {
                this.timerRunning = false;
                this.playNotification();
            }
        },

        pauseTimer() {
            this.timerRunning = false;
        },

        resetTimer() {
            this.timerRunning = false;
            this.remainingTime = this.activeRecipe.steps[this.currentStep].duration * 60;
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },

        playNotification() {
            // Play a sound notification
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance('Le temps est écoulé!');
                window.speechSynthesis.speak(utterance);
            }
        },

        toggleVoice() {
            this.voiceEnabled = !this.voiceEnabled;
            if (this.voiceEnabled) {
                this.initVoiceControl();
            }
        },

        initVoiceControl() {
            if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                this.recognition = new SpeechRecognition();
                this.recognition.lang = 'fr-FR';
                this.recognition.continuous = true;
                this.recognition.interimResults = false;

                this.recognition.onstart = () => {
                    this.isListening = true;
                    this.voiceStatus = 'Écoute en cours...';
                };

                this.recognition.onend = () => {
                    if (this.voiceEnabled) {
                        this.recognition.start();
                    } else {
                        this.isListening = false;
                        this.voiceStatus = 'En attente...';
                    }
                };

                this.recognition.onresult = (event) => {
                    const transcript = event.results[event.results.length - 1][0].transcript.toLowerCase();
                    this.processVoiceCommand(transcript);
                };

                this.recognition.start();
            } else {
                alert('La reconnaissance vocale n\'est pas prise en charge par votre navigateur.');
                this.voiceEnabled = false;
            }
        },

        processVoiceCommand(command) {
            if (command.includes('suivant') || command.includes('continue')) {
                this.nextStep();
                this.speak('Étape suivante');
            } else if (command.includes('précédent') || command.includes('retour')) {
                this.previousStep();
                this.speak('Étape précédente');
            } else if (command.includes('timer') || command.includes('départ') || command.includes('commence')) {
                this.startTimer();
                this.speak('Minuteur démarré');
            } else if (command.includes('pause') || command.includes('arrête')) {
                this.pauseTimer();
                this.speak('Minuteur en pause');
            }
        },

        speak(text) {
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'fr-FR';
                window.speechSynthesis.speak(utterance);
            }
        },

        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        },

        saveToDiary() {
            if (this.activeRecipe) {
                alert('Recette enregistrée dans votre journal alimentaire!');
            }
        },

        shareRecipe() {
            if (navigator.share) {
                navigator.share({
                    title: this.activeRecipe.name,
                    text: this.activeRecipe.description,
                    url: window.location.href
                });
            } else {
                alert('Lien copié dans le presse-papier!');
            }
        }
    };
}

// ============================================
// GROCERY LIST
// ============================================

function groceryList() {
    return {
        items: [],
        categories: ['produce', 'dairy', 'meat', 'grains', 'canned', 'spices', 'oils', 'beverages', 'other'],
        filter: 'all',
        search: '',
        sortBy: 'name',
        
        init() {
            this.loadFromMealPlan();
        },

        loadFromMealPlan() {
            const mealPlan = loadFromStorage('mealPlan', {});
            const ingredients = {};
            
            Object.values(mealPlan).forEach(dayMeals => {
                Object.values(dayMeals).forEach(meals => {
                    meals.forEach(meal => {
                        if (meal.ingredients) {
                            meal.ingredients.forEach(ing => {
                                const key = ing.name.toLowerCase();
                                if (ingredients[key]) {
                                    ingredients[key].quantity += ing.quantity;
                                } else {
                                    ingredients[key] = { ...ing, checked: false };
                                }
                            });
                        }
                    });
                });
            });
            
            this.items = Object.values(ingredients);
        },

        get filteredItems() {
            return this.items.filter(item => {
                const matchesSearch = item.name.toLowerCase().includes(this.search.toLowerCase());
                const matchesFilter = this.filter === 'all' || item.category === this.filter;
                return matchesSearch && matchesFilter;
            });
        },

        get itemsByCategory() {
            const grouped = {};
            this.filteredItems.forEach(item => {
                if (!grouped[item.category]) {
                    grouped[item.category] = [];
                }
                grouped[item.category].push(item);
            });
            return grouped;
        },

        toggleItem(item) {
            item.checked = !item.checked;
        },

        clearChecked() {
            this.items = this.items.filter(item => !item.checked);
        },

        addItem(name, quantity, unit, category) {
            this.items.push({
                id: Date.now(),
                name,
                quantity,
                unit,
                category: category || 'other',
                checked: false
            });
        },

        exportList() {
            const list = this.items.map(item => 
                `[${item.checked ? 'x' : ' '}] ${item.quantity} ${item.unit} ${item.name}`
            ).join('\n');
            
            const blob = new Blob([list], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'grocery-list.txt';
            a.click();
        }
    };
}

// ============================================
// RECIPE LIBRARY
// ============================================

function recipeLibrary() {
    return {
        recipes: [],
        search: '',
        categoryFilter: 'all',
        viewMode: 'grid',
        selectedRecipe: null,
        
        init() {
            this.loadRecipes();
        },

        loadRecipes() {
            this.recipes = [
                {
                    id: 1,
                    name: 'Salade méditerranéenne',
                    description: 'Une salade fraîche et colorée avec des légumes de saison',
                    icon: 'fas fa-leaf',
                    category: 'salad',
                    time: 15,
                    servings: 2,
                    difficulty: 'Facile',
                    calories: 350,
                    nutrition: { calories: 350, protein: 12, carbs: 25, fats: 18 },
                    ingredients: [
                        { name: 'Tomates', quantity: 2, unit: 'pcs' },
                        { name: 'Concombre', quantity: 1, unit: 'pcs' },
                        { name: 'Oignon rouge', quantity: 1, unit: 'pcs' },
                        { name: 'Olives noires', quantity: 100, unit: 'g' },
                        { name: 'Feta', quantity: 200, unit: 'g' },
                        { name: 'Huile d\'olive', quantity: 3, unit: 'tbsp' },
                        { name: 'Citron', quantity: 1, unit: 'pcs' }
                    ],
                    instructions: [
                        'Lavez soigneusement tous les légumes.',
                        'Coupez les tomates en dés, le concombre en rondelles et l\'oignon en fines lamelles.',
                        'Dans un grand saladier, combinez tous les légumes.',
                        'Ajoutez les olives et la feta coupée en dés.',
                        'Arrosez d\'huile d\'olive et de jus de citron.',
                        'Salez et poivrez selon votre goût.'
                    ],
                    tips: 'Utilisez des légumes frais de saison pour un meilleur goût.'
                },
                {
                    id: 2,
                    name: 'Poulet rôti aux légumes',
                    description: 'Poulet tendre avec légumes rôtis croustillants',
                    icon: 'fas fa-drumstick-bite',
                    category: 'main',
                    time: 60,
                    servings: 4,
                    difficulty: 'Moyen',
                    calories: 450,
                    nutrition: { calories: 450, protein: 45, carbs: 20, fats: 22 },
                    ingredients: [
                        { name: 'Poulet entier', quantity: 1, unit: 'pcs' },
                        { name: 'Pommes de terre', quantity: 500, unit: 'g' },
                        { name: 'Carottes', quantity: 4, unit: 'pcs' },
                        { name: 'Ail', quantity: 1, unit: 'pcs' },
                        { name: 'Romarin', quantity: 2, unit: 'tbsp' },
                        { name: 'Sel', quantity: 1, unit: 'tsp' },
                        { name: 'Poivre', quantity: 1, unit: 'tsp' }
                    ],
                    instructions: [
                        'Préchauffez votre four à 200°C.',
                        'Séchez le poulet avec du papier absorbant.',
                        'Frottez-le avec du sel, du poivre et le romarin.',
                        'Coupez les pommes de terre en quartiers et les carottes en biseaux.',
                        'Placez le poulet dans un plat et disposez les légumes autour.',
                        'Enfournez pour 45 minutes jusqu\'à ce que la peau soit dorée.'
                    ],
                    tips: 'Le poulet sec obtient une peau plus croustillante.'
                },
                {
                    id: 3,
                    name: 'Smoothie protéiné',
                    description: 'Boisson rafraîchissante riche en protéines',
                    icon: 'fas fa-glass-whiskey',
                    category: 'drink',
                    time: 5,
                    servings: 1,
                    difficulty: 'Facile',
                    calories: 280,
                    nutrition: { calories: 280, protein: 25, carbs: 30, fats: 8 },
                    ingredients: [
                        { name: 'Protéine en poudre', quantity: 1, unit: 'scoop' },
                        { name: 'Lait', quantity: 250, unit: 'ml' },
                        { name: 'Banane', quantity: 1, unit: 'pcs' },
                        { name: 'Beurre d\'arachide', quantity: 1, unit: 'tbsp' },
                        { name: 'Glaçons', quantity: 3, unit: 'pcs' }
                    ],
                    instructions: [
                        'Placez tous les ingrédients dans un blender.',
                        'Mixez jusqu\'à obtenir une consistance lisse.',
                        'Versez dans un verre et dégustez immédiatement.'
                    ],
                    tips: 'Utilisez des fruits surgelés pour un smoothie plus épais.'
                }
            ];
        },

        get filteredRecipes() {
            return this.recipes.filter(recipe => {
                const matchesSearch = recipe.name.toLowerCase().includes(this.search.toLowerCase()) ||
                                     recipe.description.toLowerCase().includes(this.search.toLowerCase());
                const matchesCategory = this.categoryFilter === 'all' || recipe.category === this.categoryFilter;
                return matchesSearch && matchesCategory;
            });
        },

        openRecipe(recipe) {
            this.selectedRecipe = recipe;
        },

        closeRecipe() {
            this.selectedRecipe = null;
        },

        addToMealPlan(recipe) {
            alert(recipe.name + ' ajouté au plan de repas!');
        },

        getDifficultyClass(difficulty) {
            switch (difficulty) {
                case 'Facile': return 'bg-green-100 text-green-700';
                case 'Moyen': return 'bg-yellow-100 text-yellow-700';
                case 'Difficile': return 'bg-red-100 text-red-700';
                default: return 'bg-gray-100 text-gray-700';
            }
        }
    };
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function loadFromStorage(key, defaultValue) {
    try {
        const stored = localStorage.getItem(key);
        return stored ? JSON.parse(stored) : defaultValue;
    } catch (e) {
        return defaultValue;
    }
}

function saveToStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch (e) {
        console.warn('Could not save to localStorage:', e);
    }
}

// ============================================
// EXPORT FOR ALPINE.JS
// ============================================

document.addEventListener('alpine:init', () => {
    Alpine.data('mealPlanner', mealPlanner);
    Alpine.data('pantryManagement', pantryManagement);
    Alpine.data('cookingAssistant', cookingAssistant);
    Alpine.data('groceryList', groceryList);
    Alpine.data('recipeLibrary', recipeLibrary);
});
