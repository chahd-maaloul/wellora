/**
 * Trail Community Module - Interactive Features
 * Handles reviews, discussions, groups, profiles, and safety tools
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Trail Community Module loaded');
    
    // Initialize all community features
    initRatingSystem();
    initPhotoUpload();
    initReviewWizard();
    initDiscussions();
    initSOSButton();
    initWeatherWidget();
    initTrailConditions();
});

/**
 * Star Rating System
 */
function initRatingSystem() {
    const ratingContainers = document.querySelectorAll('[class*="rating"], [x-data*="rating"]');
    
    ratingContainers.forEach(container => {
        const stars = container.querySelectorAll('button svg');
        if (stars.length === 0) return;
        
        stars.forEach((star, index) => {
            star.closest('button').addEventListener('click', function() {
                const rating = index + 1;
                updateStarDisplay(stars, rating);
                
                // Emit custom event for form handling
                container.dispatchEvent(new CustomEvent('ratingchange', {
                    detail: { rating: rating }
                }));
            });
            
            star.closest('button').addEventListener('mouseenter', function() {
                const rating = index + 1;
                previewStars(stars, rating);
            });
            
            star.closest('button').addEventListener('mouseleave', function() {
                resetStars(stars);
            });
        });
    });
}

function updateStarDisplay(stars, rating) {
    stars.forEach((star, index) => {
        const button = star.closest('button');
        if (index < rating) {
            star.classList.remove('text-gray-300', 'text-gray-400');
            star.classList.add('text-amber-400');
            button.classList.add('scale-110');
        } else {
            star.classList.remove('text-amber-400');
            star.classList.add('text-gray-300', 'text-gray-400');
            button.classList.remove('scale-110');
        }
    });
}

function previewStars(stars, rating) {
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('text-amber-300');
        }
    });
}

function resetStars(stars) {
    stars.forEach(star => {
        star.classList.remove('text-amber-300');
    });
}

/**
 * Photo Upload with Drag & Drop
 */
function initPhotoUpload() {
    const dropzones = document.querySelectorAll('[class*="dropzone"], input[type="file"]');
    
    dropzones.forEach(dropzone => {
        const container = dropzone.closest('div[class*="dropzone"]') || dropzone.parentElement;
        if (!container) return;
        
        // Drag events
        container.addEventListener('dragover', handleDragOver);
        container.addEventListener('dragleave', handleDragLeave);
        container.addEventListener('drop', handleDrop);
        
        // File input change
        dropzone.addEventListener('change', handleFileSelect);
    });
}

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.add('border-wellcare-500', 'bg-wellcare-50');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove('border-wellcare-500', 'bg-wellcare-50');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove('border-wellcare-500', 'bg-wellcare-50');
    
    const files = e.dataTransfer.files;
    handleFiles(files);
}

function handleFileSelect(e) {
    const files = e.target.files;
    handleFiles(files);
}

function handleFiles(files) {
    const previewContainer = document.querySelector('[class*="photo-preview"], [class*="preview-grid"]');
    
    Array.from(files).forEach(file => {
        if (!file.type.startsWith('image/')) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            if (previewContainer) {
                addPhotoPreview(previewContainer, e.target.result, file);
            }
        };
        reader.readAsDataURL(file);
    });
}

function addPhotoPreview(container, src, file) {
    const div = document.createElement('div');
    div.className = 'relative group';
    div.innerHTML = `
        <img src="${src}" class="w-full h-24 object-cover rounded-lg">
        <button class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    container.appendChild(div);
}

/**
 * Write Review Wizard
 */
function initReviewWizard() {
    // Alpine.js handles most wizard logic through x-data
    // This provides additional JavaScript enhancements
    
    const wizard = document.querySelector('[x-data*="writeReviewWizard"]');
    if (!wizard) return;
    
    // Character counter
    const reviewText = wizard.querySelector('textarea');
    if (reviewText) {
        reviewText.addEventListener('input', function() {
            const length = this.value.length;
            const counter = wizard.querySelector('[x-text*="reviewText.length"]');
            if (counter) {
                counter.textContent = length + ' caractères';
            }
            
            // Validate minimum length
            const submitBtn = wizard.querySelector('button[disabled]');
            if (submitBtn && length < 50) {
                submitBtn.disabled = true;
            } else if (submitBtn) {
                submitBtn.disabled = false;
            }
        });
    }
}

/**
 * Discussions Features
 */
function initDiscussions() {
    // Topic expansion
    const topics = document.querySelectorAll('[class*="topic"], [class*="discussion"]');
    topics.forEach(topic => {
        topic.addEventListener('click', function(e) {
            if (e.target.closest('button, a, input')) return;
            expandTopic(this);
        });
    });
}

function expandTopic(topic) {
    const content = topic.querySelector('p[class*="line-clamp"]');
    if (content) {
        content.classList.remove('line-clamp-2');
        content.classList.add('line-clamp-none');
    }
}

/**
 * SOS Emergency Button
 */
function initSOSButton() {
    const sosButtons = document.querySelectorAll('[class*="sos"], button:contains("SOS")');
    
    sosButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            triggerSOS();
        });
    });
}

function triggerSOS() {
    // Check if confirmation is needed
    if (!confirm('Êtes-vous sûr de vouloir envoyer une alerte d\'urgence à vos contacts ?')) {
        return;
    }
    
    // Get current position
    if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(
            position => {
                const { latitude, longitude } = position.coords;
                sendEmergencyAlert(latitude, longitude);
            },
            error => {
                console.error('Geolocation error:', error);
                alert('Impossible d\'obtenir votre position. Les services d\'urgence ont été prévenus.');
                window.location.href = 'tel:112';
            }
        );
    } else {
        alert('La géolocalisation n\'est pas disponible. Veuillez appeler le 112 directement.');
        window.location.href = 'tel:112';
    }
}

function sendEmergencyAlert(lat, lng) {
    // In production, this would send to backend API
    console.log('Emergency alert sent:', { lat, lng });
    
    // Visual feedback
    const btn = document.querySelector('[class*="sos"] button, .bg-red-500 button');
    if (btn) {
        btn.innerHTML = `
            <svg class="w-6 h-6 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Envoi en cours...
        `;
    }
    
    // Simulate API call
    setTimeout(() => {
        alert('Alerte d\'urgence envoyée à vos contacts ! Les services d\'urgence ont été prévenus.');
        window.location.href = 'tel:112';
    }, 2000);
}

/**
 * Weather Widget
 */
function initWeatherWidget() {
    // Weather data would be loaded from API
    // For demo, we simulate weather updates
    setInterval(() => {
        updateWeatherDisplay();
    }, 300000); // Update every 5 minutes
}

function updateWeatherDisplay() {
    const tempDisplay = document.querySelector('[class*="weather"] [class*="temperature"], [class*="weather"] .text-4xl');
    if (tempDisplay) {
        // In production, fetch from weather API
        console.log('Weather updated');
    }
}

/**
 * Trail Conditions
 */
function initTrailConditions() {
    // Auto-refresh trail conditions
    setInterval(() => {
        refreshTrailConditions();
    }, 300000); // Update every 5 minutes
}

function refreshTrailConditions() {
    const conditionsContainer = document.querySelector('[class*="trail-condition"], [class*="conditions"]');
    if (conditionsContainer) {
        // In production, fetch from API
        console.log('Trail conditions refreshed');
    }
}

/**
 * Like/Unlike functionality
 */
function toggleLike(button) {
    const icon = button.querySelector('svg');
    const countSpan = button.querySelector('span:last-child');
    
    if (button.classList.contains('text-wellcare-600')) {
        // Unlike
        button.classList.remove('text-wellcare-600');
        icon.classList.remove('fill-current');
        if (countSpan) {
            countSpan.textContent = parseInt(countSpan.textContent) - 1;
        }
    } else {
        // Like
        button.classList.add('text-wellcare-600');
        icon.classList.add('fill-current');
        if (countSpan) {
            countSpan.textContent = parseInt(countSpan.textContent) + 1;
        }
    }
}

/**
 * Follow/Unfollow functionality
 */
function toggleFollow(button) {
    if (button.classList.contains('bg-wellcare-500')) {
        // Unfollow
        button.classList.remove('bg-wellcare-500', 'text-white');
        button.classList.add('bg-gray-100', 'text-gray-700');
        button.querySelector('span').textContent = 'Suivre';
    } else {
        // Follow
        button.classList.add('bg-wellcare-500', 'text-white');
        button.classList.remove('bg-gray-100', 'text-gray-700');
        button.querySelector('span').textContent = 'Suivi';
    }
}

/**
 * Report content functionality
 */
function reportContent(contentId, reason) {
    const reasons = [
        'Contenu inapproprié',
        'Spam',
        'Harcèlement',
        'Information fausse',
        'Autre'
    ];
    
    const selectedReason = reason || prompt('Raison du signalement:\n' + reasons.map((r, i) => i + 1 + '. ' + r).join('\n'));
    
    if (selectedReason) {
        console.log('Content reported:', { contentId, reason: selectedReason });
        alert('Merci. Le contenu a été signalé à nos modérateurs.');
    }
}

/**
 * Share functionality
 */
function shareContent(title, url) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url || window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url || window.location.href);
        alert('Lien copié dans le presse-papier !');
    }
}

/**
 * Filter and Search functionality
 */
function applyFilters(filters) {
    const items = document.querySelectorAll('[class*="review"], [class*="topic"], [class*="trail"]');
    
    items.forEach(item => {
        let shouldShow = true;
        
        // Apply filters
        Object.keys(filters).forEach(key => {
            const value = filters[key];
            if (value && shouldShow) {
                // Check if item matches filter
                shouldShow = item.dataset[key] === value;
            }
        });
        
        item.style.display = shouldShow ? '' : 'none';
    });
}

/**
 * Pagination
 */
function loadMore(containerSelector) {
    const container = document.querySelector(containerSelector);
    if (!container) return;
    
    // In production, load from API
    console.log('Loading more items...');
    
    // Add loading state
    container.classList.add('opacity-50', 'pointer-events-none');
    
    setTimeout(() => {
        container.classList.remove('opacity-50', 'pointer-events-none');
    }, 1000);
}

// Export functions for Alpine.js integration
window.trailCommunity = {
    toggleLike,
    toggleFollow,
    reportContent,
    shareContent,
    applyFilters,
    loadMore,
    triggerSOS
};
