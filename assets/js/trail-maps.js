/**
 * Trail Maps Module - WellCare Connect
 * Handles all map-related functionality including interactive maps,
 * trail detail maps, navigation, and elevation profiles.
 */

// Initialize Alpine.js components for trail maps
document.addEventListener('alpine:init', () => {
    
    /**
     * Interactive Trail Map Component
     * Full-featured map for discovering and exploring trails
     */
    Alpine.data('interactiveTrailMap', () => ({
        map: null,
        trails: [],
        markers: [],
        activeLayer: 'all',
        searchQuery: '',
        showFilters: false,
        
        init() {
            this.$nextTick(() => {
                this.initMap();
                this.loadTrails();
            });
        },
        
        initMap() {
            const mapElement = document.getElementById('interactive-trail-map');
            if (!mapElement) return;
            
            this.map = L.map('interactive-trail-map').setView([45.5234, -122.6762], 12);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);
            
            // Add scale control
            L.control.scale().addTo(this.map);
            
            // Initialize layer groups
            this.trailLayer = L.layerGroup().addTo(this.map);
            this.poiLayer = L.layerGroup().addTo(this.map);
            
            // Handle map clicks
            this.map.on('click', (e) => {
                this.$dispatch('map-click', { lat: e.latlng.lat, lng: e.latlng.lng });
            });
        },
        
        loadTrails() {
            // Sample trail data - in production, fetch from API
            this.trails = [
                {
                    id: 1,
                    name: 'Pine Ridge Trail',
                    coordinates: [[45.5234, -122.6762], [45.5245, -122.6780], [45.5260, -122.6770]],
                    difficulty: 'moderate',
                    distance: 12.5,
                    rating: 4.8
                },
                {
                    id: 2,
                    name: 'Crystal Springs',
                    coordinates: [[45.5300, -122.6800], [45.5320, -122.6820], [45.5340, -122.6790]],
                    difficulty: 'easy',
                    distance: 8.3,
                    rating: 4.5
                }
            ];
            
            this.renderTrails();
        },
        
        renderTrails() {
            // Clear existing markers
            this.trailLayer.clearLayers();
            
            this.trails.forEach(trail => {
                if (this.activeLayer !== 'all' && trail.difficulty !== this.activeLayer) return;
                
                // Create trail polyline
                const polyline = L.polyline(trail.coordinates, {
                    color: this.getDifficultyColor(trail.difficulty),
                    weight: 5,
                    opacity: 0.8
                });
                
                // Add popup
                polyline.bindPopup(`
                    <div class="trail-popup">
                        <h3>${trail.name}</h3>
                        <p>📏 ${trail.distance} km</p>
                        <p>⭐ ${trail.rating}/5</p>
                        <a href="/trail/detail/${trail.id}" class="btn-view-trail">View Trail</a>
                    </div>
                `);
                
                this.trailLayer.addLayer(polyline);
            });
        },
        
        getDifficultyColor(difficulty) {
            const colors = {
                easy: '#22c55e',
                moderate: '#f59e0b',
                hard: '#ef4444',
                expert: '#7c3aed'
            };
            return colors[difficulty] || '#00A790';
        },
        
        setActiveLayer(layer) {
            this.activeLayer = layer;
            this.renderTrails();
        },
        
        fitToTrails() {
            if (this.trails.length === 0) return;
            
            const allCoords = this.trails.flatMap(t => t.coordinates);
            const bounds = L.latLngBounds(allCoords);
            this.map.fitBounds(bounds, { padding: [50, 50] });
        },
        
        toggleLayer(layerName) {
            if (layerName === 'trails') {
                if (this.map.hasLayer(this.trailLayer)) {
                    this.map.removeLayer(this.trailLayer);
                } else {
                    this.trailLayer.addTo(this.map);
                }
            } else if (layerName === 'poi') {
                if (this.map.hasLayer(this.poiLayer)) {
                    this.map.removeLayer(this.poiLayer);
                } else {
                    this.poiLayer.addTo(this.map);
                }
            }
        }
    }));
    
    /**
     * Trail Detail Map Component
     * Detailed map for a specific trail with waypoints and amenities
     */
    Alpine.data('trailDetailMap', () => ({
        map: null,
        trail: null,
        waypoints: [],
        currentPosition: null,
        progress: 0,
        weather: null,
        
        init() {
            this.$nextTick(() => {
                this.initMap();
                this.loadTrailData();
                this.loadWeather();
            });
        },
        
        initMap() {
            const mapElement = document.getElementById('trail-detail-map');
            if (!mapElement) return;
            
            this.map = L.map('trail-detail-map', {
                zoomControl: false
            });
            
            L.control.zoom({
                position: 'bottomright'
            }).addTo(this.map);
            
            // Add tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);
        },
        
        loadTrailData() {
            // Sample data - in production, fetch from API
            this.trail = {
                name: 'Pine Ridge Trail',
                coordinates: [
                    [45.5234, -122.6762],
                    [45.5240, -122.6770],
                    [45.5245, -122.6780],
                    [45.5250, -122.6790],
                    [45.5255, -122.6780],
                    [45.5260, -122.6770],
                    [45.5265, -122.6760]
                ],
                waypoints: [
                    { lat: 45.5234, lng: -122.6762, name: 'Trailhead', type: 'start' },
                    { lat: 45.5245, lng: -122.6780, name: 'Crystal Spring', type: 'water' },
                    { lat: 45.5255, lng: -122.6780, name: 'Viewpoint', type: 'view' },
                    { lat: 45.5265, lng: -122.6760, name: 'Summit', type: 'end' }
                ]
            };
            
            // Render trail
            const trailLine = L.polyline(this.trail.coordinates, {
                color: '#00A790',
                weight: 8,
                opacity: 0.9
            }).addTo(this.map);
            
            this.map.fitBounds(trailLine.getBounds(), { padding: [30, 30] });
            
            // Render waypoints
            this.trail.waypoints.forEach((wp, index) => {
                const marker = L.marker([wp.lat, wp.lng], {
                    icon: L.divIcon({
                        html: this.getWaypointIcon(wp.type),
                        className: 'waypoint-marker',
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    })
                }).addTo(this.map);
                
                marker.bindPopup(`<b>${wp.name}</b><br>Waypoint ${index + 1}`);
            });
            
            // Add current position marker
            this.currentPosition = L.marker([45.5242, -122.6775], {
                icon: L.divIcon({
                    html: '<div style="background: #002F5C; width: 40px; height: 40px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; font-size: 18px;">📍</div>',
                    className: 'position-marker'
                })
            }).addTo(this.map);
        },
        
        getWaypointIcon(type) {
            const icons = {
                start: '<div style="background: #22c55e; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">🚩</div>',
                water: '<div style="background: #3b82f6; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">💧</div>',
                view: '<div style="background: #f59e0b; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">📷</div>',
                rest: '<div style="background: #8b5cf6; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">🪑</div>',
                end: '<div style="background: #ef4444; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">🏁</div>'
            };
            return icons[type] || icons.start;
        },
        
        loadWeather() {
            // Simulated weather data
            this.weather = {
                temperature: '18°C',
                condition: 'Partly Cloudy',
                humidity: '65%',
                wind: '12 km/h'
            };
        },
        
        updateProgress(percent) {
            this.progress = percent;
            // Update position marker on trail
            const coords = this.trail.coordinates;
            const index = Math.floor((percent / 100) * (coords.length - 1));
            if (coords[index]) {
                this.currentPosition.setLatLng(coords[index]);
            }
        },
        
        centerOnPosition() {
            if (this.currentPosition) {
                this.map.setView(this.currentPosition.getLatLng(), 16);
            }
        },
        
        toggleFullscreen() {
            const mapContainer = document.getElementById('trail-detail-map');
            if (!document.fullscreenElement) {
                mapContainer.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }
    }));
    
    /**
     * Trail Navigation Component
     * Turn-by-turn navigation with GPS tracking
     */
    Alpine.data('trailNavigation', () => ({
        map: null,
        isNavigating: false,
        isPaused: false,
        watchId: null,
        currentPosition: null,
        instructions: [],
        currentInstruction: 0,
        
        init() {
            this.$nextTick(() => {
                this.initMap();
                this.loadInstructions();
            });
        },
        
        initMap() {
            this.map = L.map('nav-map').setView([45.5234, -122.6762], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);
        },
        
        loadInstructions() {
            this.instructions = [
                { text: 'Head north on Pine Ridge Trail', distance: '0.3 km', icon: '⬆️' },
                { text: 'Continue straight at junction', distance: '0.5 km', icon: '➡️' },
                { text: 'Turn right onto Forest Path', distance: '0.8 km', icon: '↗️' },
                { text: 'Continue to Crystal Spring', distance: '1.2 km', icon: '💧' },
                { text: 'Turn left onto Summit Trail', distance: '0.5 km', icon: '⬅️' }
            ];
        },
        
        startNavigation() {
            this.isNavigating = true;
            this.isPaused = false;
            
            // Request GPS permission and start tracking
            if ('geolocation' in navigator) {
                this.watchId = navigator.geolocation.watchPosition(
                    (position) => this.updatePosition(position),
                    (error) => this.handleGpsError(error),
                    { enableHighAccuracy: true }
                );
            }
        },
        
        pauseNavigation() {
            this.isPaused = true;
            if (this.watchId) {
                navigator.geolocation.clearWatch(this.watchId);
            }
        },
        
        resumeNavigation() {
            this.isPaused = false;
            this.startNavigation();
        },
        
        stopNavigation() {
            this.isNavigating = false;
            this.isPaused = false;
            if (this.watchId) {
                navigator.geolocation.clearWatch(this.watchId);
            }
        },
        
        updatePosition(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            this.currentPosition = { lat, lng };
            
            // Update marker position
            if (this.navMarker) {
                this.navMarker.setLatLng([lat, lng]);
            } else {
                this.navMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        html: '<div style="background: #002F5C; width: 50px; height: 50px; border-radius: 50%; border: 4px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; font-size: 20px;">🚶</div>',
                        className: 'nav-marker'
                    })
                }).addTo(this.map);
            }
            
            // Center map on position
            this.map.setView([lat, lng], this.map.getZoom());
        },
        
        handleGpsError(error) {
            console.error('GPS Error:', error.message);
            alert('Unable to get GPS location. Please check your device settings.');
        },
        
        focusInstruction(index) {
            this.currentInstruction = index;
        }
    }));
    
    /**
     * Trail Planning Tools Component
     */
    Alpine.data('trailPlanningTools', () => ({
        map: null,
        startPoint: '',
        endPoint: '',
        targetDistance: 10,
        maxElevation: 500,
        fitnessLevel: 'intermediate',
        routeLine: null,
        
        init() {
            this.$nextTick(() => {
                this.initMap();
            });
        },
        
        initMap() {
            const mapElement = document.getElementById('planning-map');
            if (!mapElement) return;
            
            this.map = L.map('planning-map').setView([45.5234, -122.6762], 12);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);
        },
        
        calculateRoute() {
            // Placeholder for route calculation
            alert('Calculating route...');
        },
        
        exportToGPX() {
            // Generate and download GPX file
            alert('GPX file download started!');
        },
        
        sharePlan() {
            if (navigator.share) {
                navigator.share({
                    title: 'Trail Plan',
                    url: window.location.href
                });
            } else {
                alert('Link copied to clipboard!');
            }
        }
    }));
    
    /**
     * Offline Maps Manager Component
     */
    Alpine.data('offlineMapsManager', () => ({
        downloadedMaps: [],
        isDownloading: false,
        
        init() {
            this.loadDownloadedMaps();
        },
        
        loadDownloadedMaps() {
            // Load from localStorage
            const stored = localStorage.getItem('offlineMaps');
            if (stored) {
                this.downloadedMaps = JSON.parse(stored);
            }
        },
        
        downloadMap(mapId) {
            this.isDownloading = true;
            
            // Simulate download
            setTimeout(() => {
                const map = {
                    id: mapId,
                    downloadedAt: new Date().toISOString(),
                    size: '45 MB'
                };
                
                this.downloadedMaps.push(map);
                this.saveDownloadedMaps();
                this.isDownloading = false;
                
                alert('Map downloaded successfully!');
            }, 2000);
        },
        
        removeMap(mapId) {
            if (confirm('Are you sure you want to remove this offline map?')) {
                this.downloadedMaps = this.downloadedMaps.filter(m => m.id !== mapId);
                this.saveDownloadedMaps();
            }
        },
        
        saveDownloadedMaps() {
            localStorage.setItem('offlineMaps', JSON.stringify(this.downloadedMaps));
        },
        
        isMapDownloaded(mapId) {
            return this.downloadedMaps.some(m => m.id === mapId);
        }
    }));
});

/**
 * Elevation Profile Chart Component
 */
class ElevationProfileChart {
    constructor(containerId, data, options = {}) {
        this.container = document.getElementById(containerId);
        this.data = data;
        this.options = options;
        this.chart = null;
        
        if (this.container) {
            this.init();
        }
    }
    
    init() {
        const ctx = this.container.getContext('2d');
        
        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.data.map((_, i) => i),
                datasets: [{
                    label: this.options.label || 'Elevation',
                    data: this.data,
                    borderColor: '#00A790',
                    backgroundColor: 'rgba(0, 167, 144, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: (items) => `Distance: ${items[0].label} km`,
                            label: (item) => `Elevation: ${item.raw}m`
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: this.options.xLabel || 'Distance (km)'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: this.options.yLabel || 'Elevation (m)'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
    
    destroy() {
        if (this.chart) {
            this.chart.destroy();
        }
    }
}

/**
 * Utility functions for trail maps
 */
const TrailMapUtils = {
    /**
     * Format distance for display
     */
    formatDistance(km) {
        if (km < 1) {
            return `${Math.round(km * 1000)} m`;
        }
        return `${km.toFixed(1)} km`;
    },
    
    /**
     * Format duration for display
     */
    formatDuration(minutes) {
        const hours = Math.floor(minutes / 60);
        const mins = Math.round(minutes % 60);
        
        if (hours > 0) {
            return `${hours}h ${mins}m`;
        }
        return `${mins} min`;
    },
    
    /**
     * Calculate difficulty level
     */
    calculateDifficulty(distance, elevation, fitnessLevel) {
        const baseScore = distance * 0.3 + (elevation / 100) * 0.5;
        const fitnessMultiplier = {
            beginner: 1.5,
            intermediate: 1.0,
            advanced: 0.7,
            expert: 0.5
        };
        
        const score = baseScore * (fitnessMultiplier[fitnessLevel] || 1);
        
        if (score < 3) return 'easy';
        if (score < 5) return 'moderate';
        if (score < 7) return 'hard';
        return 'expert';
    },
    
    /**
     * Export trail to GPX format
     */
    exportToGPX(trail) {
        let gpx = `<?xml version="1.0" encoding="UTF-8"?>
<gpx version="1.1" creator="WellCare Connect">
  <trk>
    <name>${trail.name}</name>
    <trkseg>
`;
        
        trail.coordinates.forEach(coord => {
            gpx += `      <trkpt lat="${coord[0]}" lon="${coord[1]}">
      </trkpt>
`;
        });
        
        gpx += `    </trkseg>
  </trk>
</gpx>`;
        
        return gpx;
    },
    
    /**
     * Download GPX file
     */
    downloadGPX(gpx, filename) {
        const blob = new Blob([gpx], { type: 'application/gpx+xml' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
};

// Export for use in other modules
window.TrailMapUtils = TrailMapUtils;
window.ElevationProfileChart = ElevationProfileChart;
