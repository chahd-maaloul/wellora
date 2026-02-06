// Trail Module JavaScript - Health Trail Dashboard Functionality
// Alpine.js components for trail discovery, creation, and management

document.addEventListener('alpine:init', () => {
    
    // Trail Discovery Component
    Alpine.data('trailDiscovery', () => ({
        filters: {
            search: '',
            difficulty: [],
            distance: '',
            rating: '',
            amenities: []
        },
        trails: [
            {
                id: 1,
                name: 'Forest Path Adventure',
                location: 'Bali, Indonesia',
                distance: '5.2 km',
                difficulty: 'moderate',
                rating: 4.8,
                reviews: 124,
                image: 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400&h=200&fit=crop',
                amenities: ['parking', 'restrooms', 'water'],
                completionCount: 342
            },
            {
                id: 2,
                name: 'Mountain Summit Trail',
                location: 'Swiss Alps',
                distance: '8.5 km',
                difficulty: 'hard',
                rating: 4.9,
                reviews: 89,
                image: 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=400&h=200&fit=crop',
                amenities: ['parking'],
                completionCount: 156
            },
            {
                id: 3,
                name: 'Lakeside Walk',
                location: 'Banff, Canada',
                distance: '3.1 km',
                difficulty: 'easy',
                rating: 4.6,
                reviews: 203,
                image: 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=400&h=200&fit=crop',
                amenities: ['parking', 'restrooms', 'water', 'picnic'],
                completionCount: 567
            }
        ],
        showMap: false,
        viewMode: 'grid',
        
        init() {
            console.log('Trail discovery initialized');
        },
        
        get filteredTrails() {
            return this.trails.filter(trail => {
                const matchesSearch = trail.name.toLowerCase().includes(this.filters.search.toLowerCase()) ||
                                     trail.location.toLowerCase().includes(this.filters.search.toLowerCase());
                const matchesDifficulty = this.filters.difficulty.length === 0 || 
                                         this.filters.difficulty.includes(trail.difficulty);
                const matchesRating = !this.filters.rating || trail.rating >= parseFloat(this.filters.rating);
                return matchesSearch && matchesDifficulty && matchesRating;
            });
        },
        
        resetFilters() {
            this.filters = {
                search: '',
                difficulty: [],
                distance: '',
                rating: '',
                amenities: []
            };
        },
        
        toggleFavorite(trailId) {
            console.log('Toggle favorite for trail:', trailId);
            // Add favorite logic here
        }
    }));
    
    // Trail Dashboard Component
    Alpine.data('trailDashboard', () => ({
        stats: {
            trailsExplored: 12,
            publications: 8,
            favorites: 24,
            totalDistance: 45.2
        },
        recentTrails: [],
        upcomingEvents: [],
        
        init() {
            console.log('Trail dashboard initialized');
        }
    }));
    
    // Create Trail Wizard Component
    Alpine.data('trailWizard', () => ({
        currentStep: 1,
        totalSteps: 5,
        trailData: {
            name: '',
            description: '',
            location: '',
            distance: '',
            elevation: '',
            difficulty: 'moderate',
            amenities: [],
            photos: []
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
        
        submitTrail() {
            console.log('Submitting trail:', this.trailData);
            // Add API call here
        },
        
        handlePhotoUpload(event) {
            const files = event.target.files;
            for (let file of files) {
                this.trailData.photos.push({
                    name: file.name,
                    url: URL.createObjectURL(file)
                });
            }
        },
        
        removePhoto(index) {
            this.trailData.photos.splice(index, 1);
        }
    }));
    
    // Publication Form Component
    Alpine.data('publicationForm', () => ({
        publication: {
            trailId: null,
            ratingAmbiance: 0,
            ratingSafety: 0,
            visitDate: '',
            conditions: '',
            review: '',
            tips: '',
            photos: []
        },
        
        setRating(type, value) {
            this.publication[`rating${type}`] = value;
        },
        
        submitPublication() {
            console.log('Submitting publication:', this.publication);
            // Add API call here
        },
        
        handlePhotoUpload(event) {
            const files = event.target.files;
            for (let file of files) {
                this.publication.photos.push({
                    name: file.name,
                    url: URL.createObjectURL(file)
                });
            }
        }
    }));
    
    // My Trails Management Component
    Alpine.data('myTrails', () => ({
        trails: [
            {
                id: 1,
                name: 'My Favorite Trail',
                status: 'published',
                views: 1234,
                publications: 45,
                rating: 4.5,
                image: 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=200&h=150&fit=crop'
            },
            {
                id: 2,
                name: 'Morning Walk Path',
                status: 'draft',
                views: 0,
                publications: 0,
                rating: 0,
                image: 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=200&h=150&fit=crop'
            }
        ],
        filterStatus: 'all',
        viewMode: 'grid',
        
        get filteredTrails() {
            if (this.filterStatus === 'all') return this.trails;
            return this.trails.filter(t => t.status === this.filterStatus);
        },
        
        toggleVisibility(trailId) {
            console.log('Toggle visibility for trail:', trailId);
        },
        
        editTrail(trailId) {
            console.log('Edit trail:', trailId);
            window.location.href = `/trail/edit/${trailId}`;
        },
        
        archiveTrail(trailId) {
            console.log('Archive trail:', trailId);
        }
    }));
    
    // Interactive Map Component
    Alpine.data('trailMap', () => ({
        map: null,
        trailLayer: null,
        currentTrail: null,
        
        init() {
            this.initMap();
        },
        
        initMap() {
            // Map will be initialized when Leaflet is available
            console.log('Trail map initialized');
        },
        
        loadTrail(trailId) {
            console.log('Loading trail:', trailId);
            // Load trail data and display on map
        },
        
        showElevationProfile(data) {
            console.log('Showing elevation profile:', data);
        }
    }));
});

// Console log for module initialization
console.log('Trail module initialized');
