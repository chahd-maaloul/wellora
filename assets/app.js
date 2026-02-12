// assets/app.js
import './app.css';
import '../assets/styles/accessibility.css';

// Import libraries
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

// Import health analytics components
import './js/health-analytics.js';
// Import doctor dashboard components (patient chart, etc.)
import './js/doctor-dashboard.js';

// Import accessibility module
import './js/accessibility.js';

// Make libraries globally available
window.Alpine = Alpine;
window.Chart = Chart;

/**
 * Apply theme to document root
 * @param {boolean} isDark - Whether to apply dark mode
 */
function applyTheme(isDark) {
  const root = document.documentElement;
  if (isDark) {
    root.classList.add('dark');
  } else {
    root.classList.remove('dark');
  }
}

/**
 * Get the saved theme preference or detect system preference
 * @returns {boolean} - true if dark mode should be applied
 */
function getPreferredTheme() {
  // Check localStorage first
  const saved = localStorage.getItem('theme');
  if (saved === 'dark') return true;
  if (saved === 'light') return false;
  
  // Fall back to system preference
  return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
}

// Apply theme IMMEDIATELY on script load to prevent FOUC (Flash of Unstyled Content)
// This runs before Alpine starts to ensure theme is applied before DOM rendering
document.addEventListener('DOMContentLoaded', function() {
  const theme = getPreferredTheme();
  applyTheme(theme);
});

// Also apply theme immediately on script load (runs before DOMContentLoaded)
// This handles cases where script is in <head>
(function() {
  try {
    const saved = localStorage.getItem('theme');
    if (saved === 'dark') {
      document.documentElement.classList.add('dark');
    } else if (saved === 'light') {
      document.documentElement.classList.remove('dark');
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      document.documentElement.classList.add('dark');
    }
  } catch (e) {
    // localStorage might not be available
    console.warn('Theme initialization error:', e);
  }
})();

document.addEventListener('alpine:init', () => {
  Alpine.store('theme', {
    isDark: false,
    
    init() {
      // This is called automatically by Alpine after the store is created
      this.isDark = getPreferredTheme();
      applyTheme(this.isDark);
    },
    
    toggle() {
      this.isDark = !this.isDark;
      localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
      applyTheme(this.isDark);
      
      // Dispatch event for other components to react
      window.dispatchEvent(new CustomEvent('theme-change', { 
        detail: { isDark: this.isDark } 
      }));
    },
    
    setDark(value) {
      this.isDark = value;
      localStorage.setItem('theme', value ? 'dark' : 'light');
      applyTheme(this.isDark);
      
      // Dispatch event for other components to react
      window.dispatchEvent(new CustomEvent('theme-change', { 
        detail: { isDark: this.isDark } 
      }));
    }
  });
});

// Start Alpine - this must be called after all store registrations
Alpine.start();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  console.log('WellCare Connect loaded');
});
