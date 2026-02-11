/**
 * Accessibility JavaScript Module
 * WellCare Connect - Healthcare Application
 * 
 * Features:
 * - Screen reader announcements
 * - Keyboard navigation management
 * - Voice control integration
 * - Focus management
 * - ARIA live regions
 * - Medical data accessibility
 */

// Accessibility Manager Class
class AccessibilityManager {
  constructor() {
    this.settings = {
      highContrast: false,
      largeText: false,
      colorBlindMode: 'none',
      reduceMotion: false,
      focusIndicator: true,
      simplifiedMode: false,
      readingGuide: false,
      voiceControl: false,
    };
    
    this.voiceRecognition = null;
    this.isVoiceControlActive = false;
    this.focusHistory = [];
    this.skipLinks = [];
    
    this.init();
  }

  /**
   * Initialize accessibility features
   */
  init() {
    this.loadSettings();
    this.setupKeyboardNavigation();
    this.setupFocusManagement();
    this.setupSkipLinks();
    this.setupLiveRegions();
    this.setupMedicalDataAccessibility();
    this.setupVoiceControlSupport();
    this.setupSwitchDeviceSupport();
    this.setupEyeTrackingSupport();
    
    // Announce page load to screen readers
    this.announce('Page chargée. Navigation clavier disponible.', 'polite');
  }

  /**
   * Load saved accessibility settings
   */
  loadSettings() {
    const saved = localStorage.getItem('a11y-settings');
    if (saved) {
      this.settings = { ...this.settings, ...JSON.parse(saved) };
    }
    
    // Check system preferences
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      this.settings.reduceMotion = true;
    }
    if (window.matchMedia('(prefers-contrast: high)').matches) {
      this.settings.highContrast = true;
    }
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
      document.documentElement.classList.add('dark');
    }
  }

  /**
   * Save accessibility settings
   */
  saveSettings() {
    localStorage.setItem('a11y-settings', JSON.stringify(this.settings));
  }

  /**
   * Setup enhanced keyboard navigation
   */
  setupKeyboardNavigation() {
    // Track focus for restoration
    document.addEventListener('focusin', (e) => {
      this.focusHistory.push(e.target);
      if (this.focusHistory.length > 10) {
        this.focusHistory.shift();
      }
    });

    // Global keyboard shortcuts
    document.addEventListener('keydown', (e) => {
      // Alt + 1: Skip to main content
      if (e.altKey && e.key === '1') {
        e.preventDefault();
        this.skipToMainContent();
      }
      
      // Alt + A: Toggle accessibility panel
      if (e.altKey && e.key.toLowerCase() === 'a') {
        e.preventDefault();
        this.toggleAccessibilityPanel();
      }
      
      // Alt + H: Go to home
      if (e.altKey && e.key.toLowerCase() === 'h') {
        e.preventDefault();
        window.location.href = '/';
      }
      
      // Alt + L: Go to login
      if (e.altKey && e.key.toLowerCase() === 'l') {
        e.preventDefault();
        window.location.href = '/login';
      }
      
      // Escape: Close modals/popups
      if (e.key === 'Escape') {
        this.closeModals();
      }
      
      // Tab navigation enhancement
      if (e.key === 'Tab') {
        document.body.classList.add('keyboard-navigation');
      }
    });

    // Remove keyboard navigation class on mouse use
    document.addEventListener('mousedown', () => {
      document.body.classList.remove('keyboard-navigation');
    });
  }

  /**
   * Setup focus management for modals and dynamic content
   */
  setupFocusManagement() {
    // Focus trap for modals
    this.focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
    
    // Watch for modal openings
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === 1 && node.classList.contains('modal')) {
            this.trapFocus(node);
          }
        });
      });
    });
    
    observer.observe(document.body, { childList: true, subtree: true });
  }

  /**
   * Trap focus within a modal
   */
  trapFocus(modal) {
    const focusableContent = modal.querySelectorAll(this.focusableElements);
    const firstFocusable = focusableContent[0];
    const lastFocusable = focusableContent[focusableContent.length - 1];

    modal.addEventListener('keydown', (e) => {
      if (e.key !== 'Tab') return;

      if (e.shiftKey) {
        if (document.activeElement === firstFocusable) {
          lastFocusable.focus();
          e.preventDefault();
        }
      } else {
        if (document.activeElement === lastFocusable) {
          firstFocusable.focus();
          e.preventDefault();
        }
      }
    });

    // Focus first element
    if (firstFocusable) {
      firstFocusable.focus();
    }
  }

  /**
   * Setup skip links for keyboard navigation
   */
  setupSkipLinks() {
    const skipLinks = [
      { href: '#main-content', text: 'Aller au contenu principal' },
      { href: '#navigation', text: 'Aller à la navigation' },
      { href: '#search', text: 'Aller à la recherche' },
      { href: '#accessibility-panel', text: 'Aller aux options d\'accessibilité' },
    ];

    const skipNav = document.createElement('nav');
    skipNav.className = 'a11y-skip-links';
    skipNav.setAttribute('aria-label', 'Navigation rapide');
    
    skipLinks.forEach((link) => {
      const a = document.createElement('a');
      a.href = link.href;
      a.className = 'a11y-skip-link';
      a.textContent = link.text;
      skipNav.appendChild(a);
    });

    document.body.insertBefore(skipNav, document.body.firstChild);
  }

  /**
   * Setup ARIA live regions for dynamic content
   */
  setupLiveRegions() {
    // Create live regions if they don't exist
    if (!document.getElementById('a11y-live-polite')) {
      const polite = document.createElement('div');
      polite.id = 'a11y-live-polite';
      polite.setAttribute('aria-live', 'polite');
      polite.setAttribute('aria-atomic', 'true');
      polite.className = 'sr-only';
      document.body.appendChild(polite);
    }

    if (!document.getElementById('a11y-live-assertive')) {
      const assertive = document.createElement('div');
      assertive.id = 'a11y-live-assertive';
      assertive.setAttribute('aria-live', 'assertive');
      assertive.setAttribute('aria-atomic', 'true');
      assertive.className = 'sr-only';
      document.body.appendChild(assertive);
    }
  }

  /**
   * Announce message to screen readers
   */
  announce(message, priority = 'polite') {
    const liveRegion = document.getElementById(`a11y-live-${priority}`);
    if (liveRegion) {
      // Clear previous content
      liveRegion.textContent = '';
      // Force DOM update
      liveRegion.offsetHeight;
      // Set new message
      liveRegion.textContent = message;
      
      // Clear after announcement
      setTimeout(() => {
        liveRegion.textContent = '';
      }, 1000);
    }
  }

  /**
   * Setup medical data accessibility features
   */
  setupMedicalDataAccessibility() {
    // Enhance charts with accessible descriptions
    this.enhanceCharts();
    
    // Enhance form fields with medical context
    this.enhanceMedicalForms();
    
    // Setup emergency alert accessibility
    this.setupEmergencyAlerts();
  }

  /**
   * Enhance charts for accessibility
   */
  enhanceCharts() {
    const charts = document.querySelectorAll('canvas');
    charts.forEach((chart) => {
      // Add role and aria-label
      chart.setAttribute('role', 'img');
      
      // Create or update aria-label
      const chartTitle = chart.closest('.chart-container')?.querySelector('h2, h3, h4')?.textContent;
      const ariaLabel = chartTitle || 'Graphique de données médicales';
      chart.setAttribute('aria-label', ariaLabel);
      
      // Add keyboard navigation for chart data
      chart.setAttribute('tabindex', '0');
      chart.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          this.announceChartData(chart);
        }
      });
      
      // Add screen reader description
      const description = document.createElement('div');
      description.className = 'chart-sr-description';
      description.id = `chart-desc-${Math.random().toString(36).substr(2, 9)}`;
      chart.setAttribute('aria-describedby', description.id);
      
      // Generate description from chart data
      description.textContent = this.generateChartDescription(chart);
      chart.parentNode.insertBefore(description, chart.nextSibling);
    });
  }

  /**
   * Generate text description for chart data
   */
  generateChartDescription(chart) {
    // This would be enhanced with actual chart data
    return 'Graphique montrant les tendances de santé sur une période de 7 jours. ' +
           'Utilisez la touche Entrée pour explorer les données.';
  }

  /**
   * Announce chart data to screen reader
   */
  announceChartData(chart) {
    const description = chart.getAttribute('aria-describedby');
    const descElement = document.getElementById(description);
    if (descElement) {
      this.announce(descElement.textContent, 'polite');
    }
  }

  /**
   * Enhance medical forms with accessibility
   */
  enhanceMedicalForms() {
    // Add context to symptom selectors
    const symptomButtons = document.querySelectorAll('[data-symptom]');
    symptomButtons.forEach((btn) => {
      const symptom = btn.getAttribute('data-symptom');
      btn.setAttribute('aria-describedby', `symptom-help-${symptom}`);
      
      // Add help text if not present
      if (!document.getElementById(`symptom-help-${symptom}`)) {
        const help = document.createElement('span');
        help.id = `symptom-help-${symptom}`;
        help.className = 'sr-only';
        help.textContent = `Symptôme: ${symptom}. Appuyez sur Espace pour sélectionner.`;
        btn.appendChild(help);
      }
    });

    // Enhance vital signs displays
    const vitalSigns = document.querySelectorAll('.vital-sign');
    vitalSigns.forEach((vital) => {
      const value = vital.querySelector('.vital-value')?.textContent;
      const unit = vital.querySelector('.vital-unit')?.textContent;
      const status = vital.querySelector('.vital-status')?.textContent;
      
      if (value && status) {
        vital.setAttribute('aria-label', `${vital.textContent.trim()}: ${value} ${unit || ''}, statut ${status}`);
      }
    });
  }

  /**
   * Setup emergency alert accessibility
   */
  setupEmergencyAlerts() {
    // Monitor for emergency alerts
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === 1 && node.classList.contains('emergency-alert')) {
            // Announce immediately
            this.announce('Alerte médicale: ' + node.textContent, 'assertive');
            
            // Ensure focus
            node.setAttribute('tabindex', '-1');
            node.focus();
          }
        });
      });
    });
    
    observer.observe(document.body, { childList: true, subtree: true });
  }

  /**
   * Setup voice control support
   */
  setupVoiceControlSupport() {
    // Check for Web Speech API support
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
      this.voiceControlSupported = true;
      
      // Add voice command button to forms
      this.addVoiceCommandButtons();
    } else {
      this.voiceControlSupported = false;
    }
  }

  /**
   * Add voice command buttons to forms
   */
  addVoiceCommandButtons() {
    const textAreas = document.querySelectorAll('textarea[data-voice-enabled]');
    textAreas.forEach((textarea) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'voice-input-btn';
      btn.innerHTML = '<i class="fa-solid fa-microphone" aria-hidden="true"></i><span class="sr-only">Saisie vocale</span>';
      btn.setAttribute('aria-label', 'Activer la saisie vocale');
      
      btn.addEventListener('click', () => {
        this.startVoiceInput(textarea);
      });
      
      textarea.parentNode.insertBefore(btn, textarea.nextSibling);
    });
  }

  /**
   * Start voice input for a field
   */
  startVoiceInput(targetElement) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    
    recognition.lang = 'fr-FR';
    recognition.continuous = false;
    recognition.interimResults = false;
    
    this.announce('Saisie vocale activée. Parlez maintenant.', 'polite');
    
    recognition.onresult = (event) => {
      const transcript = event.results[0][0].transcript;
      targetElement.value += (targetElement.value ? ' ' : '') + transcript;
      this.announce('Texte saisi: ' + transcript, 'polite');
      
      // Trigger input event for Alpine.js
      targetElement.dispatchEvent(new Event('input', { bubbles: true }));
    };
    
    recognition.onerror = (event) => {
      this.announce('Erreur de reconnaissance vocale. Veuillez réessayer.', 'assertive');
    };
    
    recognition.start();
  }

  /**
   * Setup switch device support (for motor impairments)
   */
  setupSwitchDeviceSupport() {
    // Enable switch navigation with space/enter
    document.addEventListener('keydown', (e) => {
      if (e.key === ' ' || e.key === 'Enter') {
        const focused = document.activeElement;
        if (focused && focused.classList.contains('switch-navigable')) {
          e.preventDefault();
          focused.click();
        }
      }
    });
  }

  /**
   * Setup eye tracking support
   */
  setupEyeTrackingSupport() {
    // Add dwell click support for eye tracking
    let dwellTimer = null;
    const dwellDuration = 1000; // 1 second
    
    document.addEventListener('mousemove', (e) => {
      const target = e.target;
      
      if (target.classList.contains('eye-trackable')) {
        if (dwellTimer) clearTimeout(dwellTimer);
        
        target.classList.add('dwell-target');
        
        dwellTimer = setTimeout(() => {
          target.click();
          target.classList.remove('dwell-target');
        }, dwellDuration);
      } else {
        if (dwellTimer) {
          clearTimeout(dwellTimer);
          dwellTimer = null;
        }
        document.querySelectorAll('.dwell-target').forEach((el) => {
          el.classList.remove('dwell-target');
        });
      }
    });
  }

  /**
   * Skip to main content
   */
  skipToMainContent() {
    const main = document.getElementById('main-content') || document.querySelector('main');
    if (main) {
      main.setAttribute('tabindex', '-1');
      main.focus();
      this.announce('Contenu principal', 'polite');
    }
  }

  /**
   * Toggle accessibility panel
   */
  toggleAccessibilityPanel() {
    const panel = document.getElementById('accessibility-panel');
    if (panel && window.Alpine) {
      const alpineData = Alpine.$data(panel);
      if (alpineData) {
        alpineData.togglePanel();
      }
    }
  }

  /**
   * Close all modals
   */
  closeModals() {
    const modals = document.querySelectorAll('.modal, [role="dialog"]');
    modals.forEach((modal) => {
      if (window.getComputedStyle(modal).display !== 'none') {
        // Trigger close event or hide modal
        const closeBtn = modal.querySelector('[data-close-modal]');
        if (closeBtn) {
          closeBtn.click();
        } else if (window.Alpine) {
          const alpineData = Alpine.$data(modal);
          if (alpineData && alpineData.showDeleteModal !== undefined) {
            alpineData.showDeleteModal = false;
          }
        }
      }
    });
  }

  /**
   * Get accessible label for medical value
   */
  getMedicalValueLabel(value, type) {
    const labels = {
      heartRate: {
        low: 'Fréquence cardiaque basse',
        normal: 'Fréquence cardiaque normale',
        high: 'Fréquence cardiaque élevée',
      },
      bloodPressure: {
        low: 'Tension artérielle basse',
        normal: 'Tension artérielle normale',
        high: 'Tension artérielle élevée',
      },
      temperature: {
        low: 'Température basse',
        normal: 'Température normale',
        high: 'Fièvre',
      },
      oxygen: {
        low: 'Saturation en oxygène basse',
        normal: 'Saturation en oxygène normale',
      },
    };
    
    return labels[type]?.[value] || value;
  }

  /**
   * Format medical data for screen readers
   */
  formatMedicalData(data) {
    return Object.entries(data)
      .map(([key, value]) => {
        const label = this.getMedicalLabel(key);
        return `${label}: ${value}`;
      })
      .join(', ');
  }

  /**
   * Get human-readable label for medical data key
   */
  getMedicalLabel(key) {
    const labels = {
      heartRate: 'Fréquence cardiaque',
      bloodPressure: 'Tension artérielle',
      systolic: 'systolique',
      diastolic: 'diastolique',
      temperature: 'Température',
      oxygen: 'Saturation en oxygène',
      mood: 'Humeur',
      energy: 'Niveau d\'énergie',
      sleep: 'Durée de sommeil',
    };
    
    return labels[key] || key;
  }
}

// Initialize accessibility manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  window.a11yManager = new AccessibilityManager();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = AccessibilityManager;
}

/**
 * Alpine.js accessibility plugin
 */
document.addEventListener('alpine:init', () => {
  Alpine.directive('a11y-announce', (el, { expression }, { evaluate }) => {
    const message = evaluate(expression);
    if (window.a11yManager) {
      window.a11yManager.announce(message, 'polite');
    }
  });

  Alpine.magic('a11y', () => ({
    announce: (message, priority = 'polite') => {
      if (window.a11yManager) {
        window.a11yManager.announce(message, priority);
      }
    },
    
    skipToMain: () => {
      if (window.a11yManager) {
        window.a11yManager.skipToMainContent();
      }
    },
    
    formatMedicalData: (data) => {
      if (window.a11yManager) {
        return window.a11yManager.formatMedicalData(data);
      }
      return JSON.stringify(data);
    },
  }));
});

/**
 * Utility functions for medical data accessibility
 */
const MedicalA11y = {
  /**
   * Create accessible description for vital signs
   */
  describeVitals(vitals) {
    const descriptions = [];
    
    if (vitals.heartRate) {
      descriptions.push(`Fréquence cardiaque: ${vitals.heartRate.value} battements par minute, ${vitals.heartRate.status}`);
    }
    
    if (vitals.bloodPressure) {
      descriptions.push(`Tension artérielle: ${vitals.bloodPressure.systolic} sur ${vitals.bloodPressure.diastolic} millimètres de mercure, ${vitals.bloodPressure.status}`);
    }
    
    if (vitals.temperature) {
      descriptions.push(`Température corporelle: ${vitals.temperature.value} degrés Celsius, ${vitals.temperature.status}`);
    }
    
    if (vitals.oxygen) {
      descriptions.push(`Saturation en oxygène: ${vitals.oxygen.value} pourcent, ${vitals.oxygen.status}`);
    }
    
    return descriptions.join('. ');
  },

  /**
   * Create accessible description for mood
   */
  describeMood(moodValue) {
    const moods = {
      1: 'Très mal',
      2: 'Mal',
      3: 'Neutre',
      4: 'Bien',
      5: 'Très bien',
    };
    return moods[moodValue] || 'Non spécifié';
  },

  /**
   * Create accessible description for symptoms
   */
  describeSymptoms(symptoms) {
    if (!symptoms || symptoms.length === 0) {
      return 'Aucun symptôme signalé';
    }
    
    const symptomLabels = {
      headache: 'mal de tête',
      fatigue: 'fatigue',
      nausea: 'nausées',
      pain: 'douleur',
      fever: 'fièvre',
      cough: 'toux',
      insomnia: 'insomnie',
      anxiety: 'anxiété',
      appetite_loss: 'perte d\'appétit',
      dizziness: 'vertiges',
    };
    
    const labels = symptoms.map((s) => symptomLabels[s] || s);
    
    if (labels.length === 1) {
      return `Symptôme: ${labels[0]}`;
    }
    
    const last = labels.pop();
    return `Symptômes: ${labels.join(', ')} et ${last}`;
  },

  /**
   * Create accessible date description
   */
  describeDate(date) {
    const d = new Date(date);
    const options = {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    };
    return d.toLocaleDateString('fr-FR', options);
  },
};

// Export MedicalA11y utilities
window.MedicalA11y = MedicalA11y;