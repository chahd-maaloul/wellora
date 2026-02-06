// assets/js/trail-analytics.js
// Trail Analytics Module JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all chart instances when DOM is ready
    initAllCharts();
    
    // Setup Alpine.js data functions if Alpine is available
    setupAlpineData();
});

// Chart.js default configuration
Chart.defaults.font.family = "'Open Sans', 'Roboto', 'system-ui', sans-serif";
Chart.defaults.color = '#6b7280';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.padding = 20;

// Initialize all charts on the page
function initAllCharts() {
    const chartElements = document.querySelectorAll('[data-chart]');
    chartElements.forEach(el => {
        const chartType = el.dataset.chart;
        const chartData = JSON.parse(el.dataset.chartData || '{}');
        const chartOptions = JSON.parse(el.dataset.chartOptions || '{}');
        
        createChart(el.id, chartType, chartData, chartOptions);
    });
}

// Create a chart instance
function createChart(canvasId, type, data, options = {}) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;
    
    const ctx = canvas.getContext('2d');
    
    // Default options
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 20
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 47, 92, 0.9)',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 12,
                cornerRadius: 8,
                displayColors: true
            }
        },
        scales: type === 'line' || type === 'bar' ? {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        } : {}
    };
    
    const mergedOptions = { ...defaultOptions, ...options };
    
    return new Chart(ctx, {
        type: type,
        data: data,
        options: mergedOptions
    });
}

// Export chart as image
function exportChart(canvasId, filename = 'chart.png') {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    const link = document.createElement('a');
    link.download = filename;
    link.href = canvas.toDataURL('image/png');
    link.click();
}

// Export table data as CSV
function exportTable(tableId, filename = 'data.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}

// Setup Alpine.js data functions
function setupAlpineData() {
    // This function adds global data and methods for Alpine.js components
    if (typeof Alpine !== 'undefined') {
        Alpine.data('trailAnalyticsFilters', () => ({
            dateRange: '30',
            selectedTrail: '',
            difficulty: '',
            region: '',
            
            applyFilters() {
                // Trigger custom event for filters change
                window.dispatchEvent(new CustomEvent('trail-analytics:filters-change', {
                    detail: {
                        dateRange: this.dateRange,
                        trail: this.selectedTrail,
                        difficulty: this.difficulty,
                        region: this.region
                    }
                }));
            },
            
            resetFilters() {
                this.dateRange = '30';
                this.selectedTrail = '';
                this.difficulty = '';
                this.region = '';
                this.applyFilters();
            }
        }));
        
        Alpine.data('trailAnalyticsExport', () => ({
            exportFormat: 'pdf',
            dateRange: '30',
            
            exportData() {
                const format = this.exportFormat;
                window.dispatchEvent(new CustomEvent('trail-analytics:export', {
                    detail: { format: format, dateRange: this.dateRange }
                }));
            }
        }));
    }
}

// Real-time data update simulation
class AnalyticsDataUpdater {
    constructor() {
        this.updateInterval = null;
        this.listeners = [];
    }
    
    startAutoUpdate(intervalMs = 60000) {
        this.stopAutoUpdate();
        this.updateInterval = setInterval(() => {
            this.fetchUpdate();
        }, intervalMs);
    }
    
    stopAutoUpdate() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }
    
    async fetchUpdate() {
        // Simulate fetching new data
        const update = {
            timestamp: new Date().toISOString(),
            metrics: {
                activeUsers: Math.floor(Math.random() * 1000) + 12000,
                completions: Math.floor(Math.random() * 100) + 45000,
                newPublications: Math.floor(Math.random() * 50) + 200
            }
        };
        
        this.notifyListeners(update);
    }
    
    addListener(callback) {
        this.listeners.push(callback);
    }
    
    removeListener(callback) {
        this.listeners = this.listeners.filter(l => l !== callback);
    }
    
    notifyListeners(update) {
        this.listeners.forEach(callback => callback(update));
    }
}

// Make it globally available
window.trailAnalytics = {
    createChart,
    exportChart,
    exportTable,
    DataUpdater: AnalyticsDataUpdater
};

// Notification system for analytics alerts
class AnalyticsNotifications {
    constructor() {
        this.notifications = [];
        this.container = null;
    }
    
    init() {
        this.container = document.createElement('div');
        this.container.className = 'fixed z-50 space-y-2 top-4 right-4';
        document.body.appendChild(this.container);
    }
    
    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `
            px-4 py-3 rounded-lg shadow-lg text-white text-sm
            ${type === 'success' ? 'bg-emerald-500' : ''}
            ${type === 'error' ? 'bg-red-500' : ''}
            ${type === 'warning' ? 'bg-amber-500' : ''}
            ${type === 'info' ? 'bg-wellcare-500' : ''}
        `;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${this.getIcon(type)} mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        this.container.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('opacity-0', 'transition-opacity');
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }
    
    getIcon(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        return icons[type] || icons.info;
    }
}

window.analyticsNotifications = new AnalyticsNotifications();
