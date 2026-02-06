/**
 * Barcode Scanner JavaScript Module
 * Handles all barcode scanning functionality including:
 * - Camera access and video streaming
 * - Barcode detection (using device camera or manual entry)
 * - Product lookup and nutrition display
 * - Recent scans history
 */

class BarcodeScanner {
    constructor() {
        this.mediaStream = null;
        this.currentProduct = null;
        this.selectedServing = { size: 100, unit: 'ml' };
        this.recentScans = this.loadRecentScans();
        this.isScanning = false;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.renderRecentScans();
    }
    
    loadRecentScans() {
        const saved = localStorage.getItem('recentBarcodeScans');
        return saved ? JSON.parse(saved) : [];
    }
    
    saveRecentScans() {
        localStorage.setItem('recentBarcodeScans', JSON.stringify(this.recentScans));
    }
    
    setupEventListeners() {
        // Manual barcode entry
        const manualInput = document.getElementById('manualBarcode');
        if (manualInput) {
            manualInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.lookupBarcode(manualInput.value);
                }
            });
        }
        
        // Serving size selection
        document.querySelectorAll('.serving-option').forEach(option => {
            option.addEventListener('click', () => {
                document.querySelectorAll('.serving-option').forEach(o => o.classList.remove('active'));
                option.classList.add('active');
                
                this.selectedServing = {
                    size: parseFloat(option.dataset.size),
                    unit: option.dataset.unit
                };
                this.updateNutritionDisplay();
            });
        });
        
        // Custom serving input
        const customServing = document.getElementById('customServing');
        if (customServing) {
            customServing.addEventListener('change', () => {
                document.querySelectorAll('.serving-option').forEach(o => o.classList.remove('active'));
                this.selectedServing.size = parseFloat(customServing.value) || 100;
                this.updateNutritionDisplay();
            });
        }
        
        // Add to log button
        const addToLogBtn = document.querySelector('.add-to-log');
        if (addToLogBtn) {
            addToLogBtn.addEventListener('click', () => this.addToFoodLog());
        }
    }
    
    async startCamera() {
        const preview = document.getElementById('cameraPreview');
        const placeholder = document.getElementById('cameraPlaceholder');
        const scanArea = document.getElementById('scanArea');
        const startBtn = document.getElementById('startScanBtn');
        const stopBtn = document.getElementById('stopScanBtn');
        
        try {
            // Check for camera support
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('Camera not supported on this device');
            }
            
            // Request camera access
            this.mediaStream = await navigator.mediaDevices.getUserMedia({
                video: { 
                    facingMode: 'environment',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            });
            
            // Create video element
            const video = document.createElement('video');
            video.srcObject = this.mediaStream;
            video.autoplay = true;
            video.playsInline = true;
            video.style.width = '100%';
            video.style.height = '100%';
            video.style.objectFit = 'cover';
            video.style.borderRadius = '12px';
            
            // Update UI
            placeholder.style.display = 'none';
            scanArea.style.display = 'block';
            startBtn.style.display = 'none';
            stopBtn.style.display = 'flex';
            
            preview.insertBefore(video, scanArea);
            
            // Start barcode detection
            this.startBarcodeDetection(video);
            this.isScanning = true;
            
        } catch (error) {
            console.error('Camera access error:', error);
            this.showStatus('Camera access denied. Please use manual entry.', 'error');
            
            // Show fallback UI
            placeholder.innerHTML = `
                <div style="text-align: center; padding: 20px;">
                    <div style="font-size: 3rem; margin-bottom: 12px;">🚫</div>
                    <p style="color: #9ca3af; margin-bottom: 8px;">Camera access denied</p>
                    <p style="color: #6b7280; font-size: 0.875rem;">Please enter the barcode manually below</p>
                </div>
            `;
        }
    }
    
    startBarcodeDetection(video) {
        // In production, use a proper barcode detection library like:
        // - ZXing (https://github.com/zxing-js/library)
        // - Dynamsoft Barcode Reader
        // - Scandit
        
        console.log('Barcode detection started. In production, integrate ZXing or similar library.');
    }
    
    onBarcodeDetected(barcode) {
        console.log('Barcode detected:', barcode);
        this.lookupBarcode(barcode);
    }
    
    stopCamera() {
        if (this.mediaStream) {
            this.mediaStream.getTracks().forEach(track => track.stop());
            this.mediaStream = null;
        }
        
        const preview = document.getElementById('cameraPreview');
        const placeholder = document.getElementById('cameraPlaceholder');
        const scanArea = document.getElementById('scanArea');
        const video = preview.querySelector('video');
        const startBtn = document.getElementById('startScanBtn');
        const stopBtn = document.getElementById('stopScanBtn');
        
        if (video) {
            video.remove();
        }
        
        placeholder.style.display = 'block';
        placeholder.innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 3rem; margin-bottom: 12px;">📷</div>
                <p style="color: #9ca3af; margin-bottom: 8px;">Camera preview will appear here</p>
                <p style="color: #6b7280; font-size: 0.875rem;">Position the barcode within the frame</p>
            </div>
        `;
        scanArea.style.display = 'none';
        startBtn.style.display = 'flex';
        stopBtn.style.display = 'none';
        
        this.isScanning = false;
    }
    
    captureImage() {
        if (!this.mediaStream) {
            this.showStatus('Please start the camera first', 'error');
            return;
        }
        
        const video = document.querySelector('#cameraPreview video');
        if (!video) return;
        
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        console.log('Image captured for barcode detection');
        this.showStatus('Processing image...', 'success');
    }
    
    async lookupBarcode(barcode) {
        if (!barcode || barcode.trim() === '') {
            this.showStatus('Please enter a barcode number', 'error');
            return;
        }
        
        barcode = barcode.trim();
        this.showLoading(true);
        
        try {
            const product = await this.fetchProductData(barcode);
            
            if (product) {
                this.displayProduct(barcode, product);
                this.addToRecentScans(barcode, product);
                this.showStatus('Product found!', 'success');
            } else {
                this.showStatus('Product not found in database', 'error');
                this.showEmptyState();
            }
        } catch (error) {
            console.error('Product lookup error:', error);
            this.showStatus('Error looking up product', 'error');
        } finally {
            this.showLoading(false);
        }
    }
    
    async fetchProductData(barcode) {
        // Simulated API response
        const productDatabase = {
            '5000293781234': {
                name: 'Coca-Cola Zero',
                brand: 'Coca-Cola',
                calories: 0,
                proteins: 0,
                carbs: 0,
                fats: 0,
                servingSize: 100,
                servingUnit: 'ml',
                fiber: 0,
                sugar: 0,
                sodium: 0
            },
            '8718112311111': {
                name: 'Lait demi-écrémé',
                brand: 'Candia',
                calories: 46,
                proteins: 3.4,
                carbs: 5,
                fats: 1.5,
                servingSize: 100,
                servingUnit: 'ml',
                fiber: 0,
                sugar: 5,
                sodium: 44
            },
            '7622301278923': {
                name: 'Barre chocolatée',
                brand: 'Kinder',
                calories: 230,
                proteins: 4,
                carbs: 26,
                fats: 12,
                servingSize: 1,
                servingUnit: 'piece',
                fiber: 1,
                sugar: 22,
                sodium: 45
            }
        };
        
        await new Promise(resolve => setTimeout(resolve, 500));
        return productDatabase[barcode] || null;
    }
    
    displayProduct(barcode, product) {
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('productInfo').style.display = 'block';
        
        this.currentProduct = { barcode, ...product };
        
        document.getElementById('productName').textContent = product.name;
        document.getElementById('productBrand').textContent = product.brand || 'Unknown';
        document.getElementById('productBarcode').textContent = barcode;
        
        this.selectedServing = {
            size: product.servingSize,
            unit: product.servingUnit
        };
        
        document.querySelectorAll('.serving-option').forEach(opt => {
            opt.classList.toggle('active', 
                parseFloat(opt.dataset.size) === product.servingSize && 
                opt.dataset.unit === product.servingUnit
            );
        });
        
        this.updateNutritionDisplay();
    }
    
    showEmptyState() {
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('productInfo').style.display = 'none';
        this.currentProduct = null;
    }
    
    updateNutritionDisplay() {
        if (!this.currentProduct) return;
        
        const product = this.currentProduct;
        const baseSize = product.servingSize;
        const ratio = this.selectedServing.size / baseSize;
        
        const calories = Math.round(product.calories * ratio);
        const proteins = (product.proteins * ratio).toFixed(1);
        const carbs = (product.carbs * ratio).toFixed(1);
        const fats = (product.fats * ratio).toFixed(1);
        
        document.getElementById('servingCalories').textContent = calories;
        document.getElementById('servingProteins').textContent = proteins + 'g';
        document.getElementById('servingCarbs').textContent = carbs + 'g';
        document.getElementById('servingFats').textContent = fats + 'g';
        
        document.getElementById('totalProteins').textContent = proteins;
        document.getElementById('totalCarbs').textContent = carbs;
        document.getElementById('totalFats').textContent = fats;
        
        const proteinBar = document.getElementById('proteinBar');
        const carbsBar = document.getElementById('carbsBar');
        const fatsBar = document.getElementById('fatsBar');
        
        if (proteinBar) proteinBar.style.width = Math.min(parseFloat(proteins), 50) * 2 + '%';
        if (carbsBar) carbsBar.style.width = Math.min(parseFloat(carbs), 100) + '%';
        if (fatsBar) fatsBar.style.width = Math.min(parseFloat(fats), 50) * 2 + '%';
    }
    
    addToRecentScans(barcode, product) {
        this.recentScans.unshift({
            barcode,
            name: product.name,
            calories: Math.round(product.calories * (this.selectedServing.size / product.servingSize)),
            scannedAt: new Date().toISOString()
        });
        
        if (this.recentScans.length > 20) {
            this.recentScans = this.recentScans.slice(0, 20);
        }
        
        this.saveRecentScans();
        this.renderRecentScans();
    }
    
    renderRecentScans() {
        const container = document.getElementById('recentScans');
        if (!container) return;
        
        if (this.recentScans.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 32px; color: #9ca3af;">
                    <div style="font-size: 3rem; margin-bottom: 12px;">📊</div>
                    <p>No recent scans</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = this.recentScans.slice(0, 10).map(scan => `
            <div class="recent-item" onclick="barcodeScanner.lookupBarcode('${scan.barcode}')">
                <div>
                    <div class="recent-name">${scan.name}</div>
                    <div class="recent-barcode">${scan.barcode}</div>
                </div>
                <div style="text-align: right;">
                    <div class="recent-calories">${scan.calories} cal</div>
                    <div class="recent-time">${this.formatTime(scan.scannedAt)}</div>
                </div>
            </div>
        `).join('');
    }
    
    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'Just now';
        if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
        if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
        
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
    
    addToFoodLog() {
        if (!this.currentProduct) {
            this.showStatus('No product selected', 'error');
            return;
        }
        
        const mealType = document.getElementById('mealTypeSelect').value;
        const quantity = parseFloat(document.getElementById('productQuantity').value) || 1;
        
        const product = this.currentProduct;
        const baseSize = product.servingSize;
        const ratio = this.selectedServing.size / baseSize;
        
        const productData = {
            name: product.name,
            brand: product.brand,
            quantity: quantity * this.selectedServing.size,
            unit: this.selectedServing.unit,
            calories: Math.round(product.calories * ratio * quantity),
            proteins: parseFloat((product.proteins * ratio * quantity).toFixed(1)),
            carbs: parseFloat((product.carbs * ratio * quantity).toFixed(1)),
            fats: parseFloat((product.fats * ratio * quantity).toFixed(1)),
            barcode: product.barcode,
            timestamp: new Date().toISOString()
        };
        
        const logKey = `foodLog_${new Date().toISOString().split('T')[0]}`;
        let foodLog = JSON.parse(localStorage.getItem(logKey) || '{}');
        
        if (!foodLog[mealType]) {
            foodLog[mealType] = [];
        }
        
        foodLog[mealType].push(productData);
        localStorage.setItem(logKey, JSON.stringify(foodLog));
        
        this.showStatus(`${product.name} added to ${mealType}!`, 'success');
        
        setTimeout(() => {
            window.location.href = '/nutrition/food-logger';
        }, 1000);
    }
    
    showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.toggle('active', show);
        }
    }
    
    showStatus(message, type = 'success') {
        const statusEl = document.getElementById('statusMessage');
        if (!statusEl) return;
        
        statusEl.textContent = message;
        statusEl.className = 'status-message active ' + type;
        
        setTimeout(() => {
            statusEl.classList.remove('active');
        }, 3000);
    }
}

// Initialize the barcode scanner
let barcodeScanner;
document.addEventListener('DOMContentLoaded', function() {
    barcodeScanner = new BarcodeScanner();
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = BarcodeScanner;
}
