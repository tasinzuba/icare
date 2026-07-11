{{-- Diagram Manager --}}
<div id="diagram-card" class="bg-white rounded-lg shadow-sm overflow-hidden ring-2 ring-blue-500/30 my-4" style="display: none;">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-blue-50">
        <div class="flex items-center justify-between">
            <h3 class="text-base sm:text-lg font-bold text-blue-900 flex items-center gap-2">
                <i class="fas fa-map-marked-alt text-blue-600"></i> Plan / Map / Diagram — Upload Image &amp; Mark Hotspots
            </h3>
            <span id="hotspots-count" class="text-sm font-semibold text-blue-700 bg-white px-3 py-1 rounded-full border border-blue-200">0 hotspots</span>
        </div>
    </div>

    <div class="p-4 sm:p-6">
        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-white text-xs font-bold mr-1">1</span>
                Upload Diagram Image (floor plan / map / diagram)
            </label>
            <input type="file"
                   name="diagram_image"
                   accept="image/*"
                   id="diagram-image-upload"
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer border border-gray-200 rounded p-2">
            <p class="mt-2 text-xs text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                After upload, click anywhere on the image to drop a numbered hotspot at that location.
            </p>
        </div>
        
        <div id="diagram-preview-container" class="hidden">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-white text-xs font-bold mr-1">2</span>
                Click on image to add hotspots
            </label>
            <p class="text-xs text-gray-500 mb-3"><i class="fas fa-mouse-pointer mr-1"></i> Click anywhere on the image to drop a hotspot. Double-click a hotspot to remove.</p>
            <div class="diagram-preview mb-4 relative inline-block border-2 border-dashed border-blue-300 rounded p-1 bg-white">
                <img id="diagram-preview-image" class="max-w-full h-auto rounded block" alt="Diagram preview">
                <div id="hotspots-overlay" class="absolute inset-0" style="pointer-events: none;"></div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-white text-xs font-bold mr-1">3</span>
                    Type correct answer for each hotspot
                </label>
                <div id="hotspot-labels-container" class="space-y-2">
                    <!-- Labels will be added dynamically -->
                </div>
            </div>
        </div>
        
        <input type="hidden" name="diagram_hotspots" id="diagram-hotspots-data">
    </div>
</div>

@push('scripts')
<script>
const DiagramManager = {
    hotspots: [],
    imageWidth: 0,
    imageHeight: 0,
    
    init() {
        if (this._initialized) return;
        this._initialized = true;

        const fileInput = document.getElementById('diagram-image-upload');
        const previewImage = document.getElementById('diagram-preview-image');

        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleImageUpload(e));
        }

        if (previewImage) {
            previewImage.addEventListener('click', (e) => this.addHotspot(e));
        }
    },
    
    handleImageUpload(e) {
        const file = e.target.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            const previewContainer = document.getElementById('diagram-preview-container');
            const previewImage = document.getElementById('diagram-preview-image');
            
            previewImage.src = e.target.result;
            previewContainer.classList.remove('hidden');
            
            previewImage.onload = () => {
                this.imageWidth = previewImage.naturalWidth;
                this.imageHeight = previewImage.naturalHeight;
            };
        };
        reader.readAsDataURL(file);
    },
    
    addHotspot(e) {
        const rect = e.target.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        
        const hotspot = {
            id: Date.now(),
            x: x,
            y: y,
            label: ''
        };
        
        this.hotspots.push(hotspot);
        this.renderHotspots();
        this.renderLabels();
        this.updateData();
    },
    
    renderHotspots() {
        const overlay = document.getElementById('hotspots-overlay');
        if (!overlay) return;
        
        overlay.innerHTML = this.hotspots.map((hotspot, index) => `
            <div class="diagram-hotspot" 
                 style="left: ${hotspot.x}%; top: ${hotspot.y}%;"
                 data-hotspot-id="${hotspot.id}">
                ${index + 1}
            </div>
        `).join('');
        
        // Add click handlers to remove hotspots
        overlay.querySelectorAll('.diagram-hotspot').forEach(el => {
            el.addEventListener('dblclick', (e) => {
                e.stopPropagation();
                const id = parseInt(el.dataset.hotspotId);
                this.removeHotspot(id);
            });
        });
        
        this.updateCount();
    },
    
    renderLabels() {
        const container = document.getElementById('hotspot-labels-container');
        if (!container) return;
        
        container.innerHTML = this.hotspots.map((hotspot, index) => `
            <div class="flex items-center gap-2">
                <span class="diagram-label">${index + 1}</span>
                <input type="text" 
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 text-sm"
                       placeholder="Enter correct answer for point ${index + 1}"
                       value="${hotspot.label}"
                       onchange="DiagramManager.updateLabel(${hotspot.id}, this.value)">
            </div>
        `).join('');
    },
    
    updateLabel(id, value) {
        const hotspot = this.hotspots.find(h => h.id === id);
        if (hotspot) {
            hotspot.label = value;
            this.updateData();
        }
    },
    
    removeHotspot(id) {
        this.hotspots = this.hotspots.filter(h => h.id !== id);
        this.renderHotspots();
        this.renderLabels();
        this.updateData();
    },
    
    updateData() {
        const dataInput = document.getElementById('diagram-hotspots-data');
        if (dataInput) {
            dataInput.value = JSON.stringify({
                hotspots: this.hotspots,
                imageWidth: this.imageWidth,
                imageHeight: this.imageHeight
            });
        }
    },
    
    updateCount() {
        const countSpan = document.getElementById('hotspots-count');
        if (countSpan) {
            countSpan.textContent = `${this.hotspots.length} hotspots`;
        }
    }
};

window.DiagramManager = DiagramManager;
</script>
@endpush

@push('styles')
<style>
.diagram-preview {
    position: relative;
    display: inline-block;
    cursor: crosshair;
}

.diagram-hotspot {
    position: absolute;
    width: 32px;
    height: 32px;
    background: #3b82f6;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
    transform: translate(-50%, -50%);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    transition: all 0.2s ease;
    pointer-events: auto;
}

.diagram-hotspot:hover {
    transform: translate(-50%, -50%) scale(1.1);
    background: #2563eb;
}

.diagram-label {
    display: inline-flex;
    width: 28px;
    height: 28px;
    background: #3b82f6;
    color: white;
    border-radius: 50%;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 12px;
    flex-shrink: 0;
}
</style>
@endpush