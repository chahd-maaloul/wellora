/**
 * Food Logging JavaScript Module
 * Handles all food logging functionality including:
 * - Real-time nutrition calculations
 * - Food search with autocomplete
 * - Meal management
 * - Local storage persistence
 */

class FoodLogger {
    constructor() {
        this.currentDate = new Date();
        this.selectedMealType = null;
        this.foodLog = {
            breakfast: [],
            lunch: [],
            dinner: [],
            snack: []
        };
        this.dailyGoals = {
            calories: 2000,
            proteins: 120,
            carbs: 250,
            fats: 70
        };
        
        this.init();
    }
    
    init() {
        this.loadFoodLog();
        this.setupEventListeners();
        this.updateDisplay();
    }
    
    loadFoodLog() {
        const dateKey = this.getDateKey();
        const savedLog = localStorage.getItem(`foodLog_${dateKey}`);
        
        if (savedLog) {
            this.foodLog = JSON.parse(savedLog);
        }
        
        // Load goals
        const savedGoals = localStorage.getItem('nutritionGoals');
        if (savedGoals) {
            this.dailyGoals = JSON.parse(savedGoals);
        }
    }
    
    saveFoodLog() {
        const dateKey = this.getDateKey();
        localStorage.setItem(`foodLog_${dateKey}`, JSON.stringify(this.foodLog));
    }
    
    getDateKey() {
        return this.currentDate.toISOString().split('T')[0];
    }
    
    setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('foodSearch');
        if (searchInput) {
            searchInput.addEventListener('input', this.handleSearch.bind(this));
            searchInput.addEventListener('blur', () => {
                setTimeout(() => this.hideSearchResults(), 200);
            });
        }
        
        // Modal close on outside click
        const modal = document.getElementById('addFoodModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.closeModal();
            });
        }
        
        // Form submission
        const form = document.getElementById('addFoodForm');
        if (form) {
            form.addEventListener('submit', this.handleFormSubmit.bind(this));
        }
    }
    
    handleSearch(event) {
        const query = event.target.value.toLowerCase();
        const resultsContainer = document.getElementById('searchResults');
        
        if (query.length < 2) {
            this.hideSearchResults();
            return;
        }
        
        // Get recent and frequent foods
        const recentFoods = this.getRecentFoods();
        const frequentFoods = this.getFrequentFoods();
        
        // Filter foods matching query
        const allFoods = [...recentFoods, ...frequentFoods];
        const results = allFoods.filter(food => 
            food.name.toLowerCase().includes(query)
        ).slice(0, 10);
        
        if (results.length > 0) {
            this.showSearchResults(results, resultsContainer);
        } else {
            resultsContainer.innerHTML = '<div class="search-result-item">No foods found</div>';
            resultsContainer.classList.add('active');
        }
    }
    
    showSearchResults(results, container) {
        container.innerHTML = results.map(food => `
            <div class="search-result-item" data-food='${JSON.stringify(food)}' onclick="foodLogger.quickAddFood('${food.name}', ${food.calories})">
                <strong>${food.name}</strong>
                <small>${food.calories} cal</small>
            </div>
        `).join('');
        container.classList.add('active');
    }
    
    hideSearchResults() {
        const resultsContainer = document.getElementById('searchResults');
        if (resultsContainer) {
            resultsContainer.classList.remove('active');
        }
    }
    
    getRecentFoods() {
        // Return recently logged foods (simulated)
        return [
            { name: 'Pomme', calories: 95, proteins: 0.5, carbs: 25, fats: 0.3 },
            { name: 'Yaourt grec', calories: 120, proteins: 10, carbs: 6, fats: 6 },
            { name: 'Poulet grillé', calories: 165, proteins: 31, carbs: 0, fats: 3.6 },
            { name: 'Riz blanc', calories: 130, proteins: 2.7, carbs: 28, fats: 0.3 }
        ];
    }
    
    getFrequentFoods() {
        // Return frequently logged foods (simulated)
        return [
            { name: 'Café noir', calories: 5, proteins: 0.3, carbs: 0, fats: 0 },
            { name: 'Pomme', calories: 95, proteins: 0.5, carbs: 25, fats: 0.3 },
            { name: 'Yaourt', calories: 120, proteins: 10, carbs: 6, fats: 6 },
            { name: 'Oeuf', calories: 78, proteins: 6, carbs: 0.6, fats: 5 },
            { name: 'Pain complet', calories: 80, proteins: 4, carbs: 14, fats: 1 }
        ];
    }
    
    openModal(mealType) {
        this.selectedMealType = mealType;
        document.getElementById('mealType').value = mealType;
        document.getElementById('addFoodModal').classList.add('active');
        document.getElementById('foodName').focus();
    }
    
    closeModal() {
        document.getElementById('addFoodModal').classList.remove('active');
        document.getElementById('addFoodForm').reset();
        this.selectedMealType = null;
    }
    
    handleFormSubmit(event) {
        event.preventDefault();
        
        const foodData = {
            name: document.getElementById('foodName').value,
            quantity: parseFloat(document.getElementById('quantity').value) || 1,
            unit: document.getElementById('unit').value,
            calories: parseFloat(document.getElementById('calories').value) || 0,
            proteins: parseFloat(document.getElementById('proteins').value) || 0,
            carbs: parseFloat(document.getElementById('carbs').value) || 0,
            fats: parseFloat(document.getElementById('fats').value) || 0,
            notes: document.getElementById('notes').value,
            timestamp: new Date().toISOString()
        };
        
        this.addFood(this.selectedMealType, foodData);
        this.closeModal();
    }
    
    addFood(mealType, foodData) {
        if (!this.foodLog[mealType]) {
            this.foodLog[mealType] = [];
        }
        
        this.foodLog[mealType].push(foodData);
        this.saveFoodLog();
        this.renderMealItems(mealType);
        this.updateMealCalories(mealType);
        this.updateTotalNutrition();
        this.updateProgressBars();
    }
    
    deleteFood(mealType, index) {
        this.foodLog[mealType].splice(index, 1);
        this.saveFoodLog();
        this.renderMealItems(mealType);
        this.updateMealCalories(mealType);
        this.updateTotalNutrition();
        this.updateProgressBars();
    }
    
    editFood(mealType, index) {
        const food = this.foodLog[mealType][index];
        this.openModal(mealType);
        
        document.getElementById('foodName').value = food.name;
        document.getElementById('quantity').value = food.quantity;
        document.getElementById('unit').value = food.unit;
        document.getElementById('calories').value = food.calories;
        document.getElementById('proteins').value = food.proteins;
        document.getElementById('carbs').value = food.carbs;
        document.getElementById('fats').value = food.fats;
        document.getElementById('notes').value = food.notes || '';
        
        this.deleteFood(mealType, index);
    }
    
    renderMealItems(mealType) {
        const container = document.getElementById(`${mealType}-items`);
        const emptyState = document.getElementById(`${mealType}-empty`);
        
        if (!container) return;
        
        const foods = this.foodLog[mealType] || [];
        
        if (foods.length === 0) {
            if (emptyState) emptyState.style.display = 'block';
            container.querySelectorAll('.food-item').forEach(el => el.remove());
            return;
        }
        
        if (emptyState) emptyState.style.display = 'none';
        container.querySelectorAll('.food-item').forEach(el => el.remove());
        
        foods.forEach((food, index) => {
            const item = document.createElement('div');
            item.className = 'food-item';
            item.innerHTML = `
                <div class="food-info">
                    <div class="food-name">${food.name}</div>
                    <div class="food-quantity">${food.quantity} ${food.unit}${food.notes ? ' • ' + food.notes : ''}</div>
                </div>
                <div class="food-calories">${Math.round(food.calories)} cal</div>
                <div class="food-actions">
                    <button onclick="foodLogger.editFood('${mealType}', ${index})" title="Edit">✏️</button>
                    <button class="delete" onclick="foodLogger.deleteFood('${mealType}', ${index})" title="Delete">🗑️</button>
                </div>
            `;
            container.appendChild(item);
        });
    }
    
    updateMealCalories(mealType) {
        const foods = this.foodLog[mealType] || [];
        const total = foods.reduce((sum, food) => sum + food.calories, 0);
        const caloriesEl = document.getElementById(`${mealType}-calories`);
        if (caloriesEl) {
            caloriesEl.textContent = `${Math.round(total)} cal`;
        }
    }
    
    updateTotalNutrition() {
        let totals = { calories: 0, proteins: 0, carbs: 0, fats: 0 };
        
        Object.values(this.foodLog).forEach(mealFoods => {
            mealFoods.forEach(food => {
                totals.calories += food.calories;
                totals.proteins += food.proteins;
                totals.carbs += food.carbs;
                totals.fats += food.fats;
            });
        });
        
        // Update display
        document.getElementById('total-calories').textContent = Math.round(totals.calories);
        document.getElementById('total-proteins').textContent = Math.round(totals.proteins);
        document.getElementById('total-carbs').textContent = Math.round(totals.carbs);
        document.getElementById('total-fats').textContent = Math.round(totals.fats);
        
        return totals;
    }
    
    updateProgressBars() {
        const totals = this.updateTotalNutrition();
        
        const macros = ['calories', 'proteins', 'carbs', 'fats'];
        macros.forEach(macro => {
            const progressEl = document.querySelector(`.macro-fill.${macro === 'calories' ? 'calories' : macro}`);
            if (progressEl) {
                const percentage = Math.min((totals[macro] / this.dailyGoals[macro]) * 100, 100);
                progressEl.style.width = `${percentage}%`;
            }
        });
    }
    
    updateDisplay() {
        ['breakfast', 'lunch', 'dinner', 'snack'].forEach(mealType => {
            this.renderMealItems(mealType);
            this.updateMealCalories(mealType);
        });
        this.updateProgressBars();
    }
    
    quickAddFood(name, calories) {
        this.openModal('snack');
        document.getElementById('foodName').value = name;
        document.getElementById('calories').value = calories;
    }
    
    changeDate(days) {
        this.currentDate.setDate(this.currentDate.getDate() + days);
        this.loadFoodLog();
        this.updateDisplay();
        
        // Update date display
        const dateEl = document.getElementById('currentDate');
        if (dateEl) {
            dateEl.textContent = this.currentDate.toLocaleDateString('en-US', { 
                weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' 
            });
        }
    }
    
    goToToday() {
        this.currentDate = new Date();
        this.loadFoodLog();
        this.updateDisplay();
        
        const dateEl = document.getElementById('currentDate');
        if (dateEl) {
            dateEl.textContent = this.currentDate.toLocaleDateString('en-US', { 
                weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' 
            });
        }
    }
    
    getDailySummary() {
        const totals = this.updateTotalNutrition();
        return {
            ...totals,
            goals: this.dailyGoals,
            meals: this.foodLog
        };
    }
}

// Initialize the food logger
let foodLogger;
document.addEventListener('DOMContentLoaded', function() {
    foodLogger = new FoodLogger();
    
    // Check for pending recipe data
    const pendingRecipe = localStorage.getItem('pendingRecipe');
    if (pendingRecipe) {
        const recipe = JSON.parse(pendingRecipe);
        // Show modal to add recipe to log
        foodLogger.openModal('lunch');
        document.getElementById('foodName').value = recipe.name;
        document.getElementById('calories').value = recipe.calories;
        document.getElementById('proteins').value = recipe.proteins;
        document.getElementById('carbs').value = recipe.carbs;
        document.getElementById('fats').value = recipe.fats;
        localStorage.removeItem('pendingRecipe');
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FoodLogger;
}
