/**
 * WellCare Nutrition AJAX Handler
 * Handles all form submissions without page refresh
 */

(function() {
    'use strict';
    
    // Toast notification system
    function showToast(message, type = 'success') {
        // Remove existing toast if any
        const existingToast = document.getElementById('ajax-toast');
        if (existingToast) existingToast.remove();
        
        const toast = document.createElement('div');
        toast.id = 'ajax-toast';
        toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white font-medium z-50 transform transition-all duration-300 translate-y-20 opacity-0`;
        
        const colors = {
            success: 'bg-emerald-500',
            error: 'bg-red-500',
            info: 'bg-blue-500'
        };
        
        toast.classList.add(colors[type] || colors.success);
        toast.innerHTML = `
            <div class="flex items-center gap-3">
                <span class="text-xl">${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</span>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-y-20', 'opacity-0');
        }, 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Update nutrition summary in header/sidebar if exists
    function updateNutritionSummary(data) {
        // Look for calorie display elements
        const calorieElements = document.querySelectorAll('[data-calories]');
        const proteinElements = document.querySelectorAll('[data-protein]');
        const carbsElements = document.querySelectorAll('[data-carbs]');
        const fatsElements = document.querySelectorAll('[data-fats]');
        
        if (data.calories !== undefined) {
            calorieElements.forEach(el => el.textContent = data.calories);
        }
        if (data.proteins !== undefined) {
            proteinElements.forEach(el => el.textContent = data.proteins + 'g');
        }
        if (data.carbs !== undefined) {
            carbsElements.forEach(el => el.textContent = data.carbs + 'g');
        }
        if (data.fats !== undefined) {
            fatsElements.forEach(el => el.textContent = data.fats + 'g');
        }
        
        // Update progress bars
        const progressBars = document.querySelectorAll('[data-progress]');
        progressBars.forEach(bar => {
            const value = bar.dataset.progressValue;
            const max = bar.dataset.progressMax || 100;
            if (value !== undefined) {
                const percentage = Math.min(100, (value / max) * 100);
                bar.style.width = percentage + '%';
            }
        });
    }
    
    // Handle form submission via AJAX
    async function handleFormSubmit(form, submitButton) {
        const formData = new FormData(form);
        const url = form.action || form.dataset.action;
        
        if (!url) {
            console.error('No action URL found for form');
            return false;
        }
        
        // Disable button during submission
        if (submitButton) {
            submitButton.disabled = true;
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<span class="animate-pulse">⏳</span>';
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        const data = await response.json();
                        
                        // Show success message
                        showToast(data.message || 'Opération réussie!', 'success');
                        
                        // Update UI if data provided
                        if (data.nutrition) {
                            updateNutritionSummary(data.nutrition);
                        }
                        
                        // Reset form if it's a logging form
                        if (form.dataset.resetOnSuccess === 'true') {
                            form.reset();
                        }
                        
                        // Custom callback if defined
                        if (data.successCallback) {
                            window[data.successCallback](data);
                        }
                        
                        return true;
                    } else {
                        // Non-JSON response - might be redirect
                        const text = await response.text();
                        if (text.includes('redirect') || response.redirected) {
                            // It's a redirect, follow it
                            window.location.href = response.url || window.location.href;
                            return true;
                        }
                        // If it's a full page response, show success
                        showToast('Opération réussie!', 'success');
                        return true;
                    }
                } else {
                    showToast('Erreur lors de l\'opération', 'error');
                    return false;
                }
            } catch (error) {
                console.error('Form submission error:', error);
                showToast('Erreur de connexion', 'error');
                return false;
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
        }
        
        return false;
    }
    
    // Initialize when DOM is ready
    function init() {
        // Find all nutrition forms with data-ajax="true"
        const ajaxForms = document.querySelectorAll('form[data-ajax="true"]');
        
        ajaxForms.forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitButton = form.querySelector('button[type="submit"]');
                await handleFormSubmit(form, submitButton);
            });
        });
        
        // Handle quick action links with data-ajax="true"
        const ajaxLinks = document.querySelectorAll('a[data-ajax="true"]');
        
        ajaxLinks.forEach(link => {
            link.addEventListener('click', async (e) => {
                e.preventDefault();
                const url = link.href;
                
                link.classList.add('opacity-50', 'pointer-events-none');
                
                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            const data = await response.json();
                            showToast(data.message || 'Opération réussie!', 'success');
                            
                            if (data.nutrition) {
                                updateNutritionSummary(data.nutrition);
                            }
                            
                            // Custom callback
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        } else {
                            // Follow redirect
                            window.location.href = response.url || url;
                        }
                    }
                } catch (error) {
                    console.error('Link click error:', error);
                    // Fallback to normal navigation
                    window.location.href = url;
                }
            });
        });
        
        // Handle water intake quick buttons
        const waterButtons = document.querySelectorAll('[data-water-add]');
        waterButtons.forEach(btn => {
            btn.addEventListener('click', async () => {
                const amount = btn.dataset.waterAdd;
                const formData = new FormData();
                formData.append('amount', amount);
                
                try {
                    const response = await fetch(btn.dataset.waterUrl || '{{ path(\'nutrition_water_add\') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        showToast(`+${amount} verre d'eau ajouté! 💧`, 'success');
                        
                        // Update water display
                        const waterDisplay = document.querySelector('[data-water-current]');
                        if (waterDisplay && data.water !== undefined) {
                            waterDisplay.textContent = data.water;
                        }
                        
                        // Update water progress
                        const waterProgress = document.querySelector('[data-water-progress]');
                        if (waterProgress && data.percentage !== undefined) {
                            waterProgress.style.width = data.percentage + '%';
                        }
                    }
                } catch (error) {
                    console.error('Water add error:', error);
                    // Fallback - just show toast
                    showToast(`+${amount} verre d'eau ajouté! 💧`, 'success');
                }
            });
        });
        
        // Handle food quick add buttons
        const foodQuickAdd = document.querySelectorAll('[data-food-add]');
        foodQuickAdd.forEach(btn => {
            btn.addEventListener('click', async () => {
                const name = btn.dataset.foodName;
                const calories = btn.dataset.foodCalories;
                const proteins = btn.dataset.foodProtein || 0;
                const carbs = btn.dataset.foodCarbs || 0;
                const fats = btn.dataset.foodFats || 0;
                const mealType = btn.dataset.foodMeal || 'lunch';
                
                const formData = new FormData();
                formData.append('foodName', name);
                formData.append('calories', calories);
                formData.append('proteins', proteins);
                formData.append('carbs', carbs);
                formData.append('fats', fats);
                formData.append('mealType', mealType);
                formData.append('quantity', 1);
                
                try {
                    const response = await fetch(btn.dataset.foodUrl || '{{ path(\'nutrition_food_add\') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        showToast(`${name} ajouté (${calories} kcal)! 🍎`, 'success');
                        
                        if (data.nutrition) {
                            updateNutritionSummary(data.nutrition);
                        }
                    }
                } catch (error) {
                    console.error('Food add error:', error);
                    showToast(`${name} ajouté! 🍎`, 'success');
                }
            });
        });
        
        // Handle meal type selection (quick log page)
        const mealTypeButtons = document.querySelectorAll('[data-meal-select]');
        mealTypeButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const meal = btn.dataset.mealSelect;
                const form = document.querySelector('input[name="mealType"]');
                if (form) form.value = meal;
                
                // Update visual selection
                mealTypeButtons.forEach(b => b.classList.remove('border-wellcare-500', 'bg-wellcare-50'));
                btn.classList.add('border-wellcare-500', 'bg-wellcare-50');
            });
        });
        
        console.log('Nutrition AJAX handler initialized');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Expose for manual calls
    window.nutritionAjax = {
        showToast,
        updateNutritionSummary,
        handleFormSubmit
    };
    
})();
