// Text Size Control Module
export const TextSizeControl = {
    sizes: [75, 85, 100, 115, 125, 150],
    currentSize: 100,
    
    init() {
        this.loadSavedSize();
        this.setupEventListeners();
        this.applySize(this.currentSize);
    },
    
    setupEventListeners() {
        const control = document.getElementById('text-size-control');
        if (!control) return;
        
        control.addEventListener('click', (e) => {
            const btn = e.target.closest('.size-btn');
            if (!btn) return;
            
            const action = btn.dataset.action;
            
            switch(action) {
                case 'increase':
                    this.increaseSize();
                    break;
                case 'decrease':
                    this.decreaseSize();
                    break;
                case 'reset':
                    this.resetSize();
                    break;
            }
        });
    },
    
    increaseSize() {
        const currentIndex = this.sizes.indexOf(this.currentSize);
        if (currentIndex < this.sizes.length - 1) {
            this.currentSize = this.sizes[currentIndex + 1];
            this.applySize(this.currentSize);
            this.saveSize();
        }
    },
    
    decreaseSize() {
        const currentIndex = this.sizes.indexOf(this.currentSize);
        if (currentIndex > 0) {
            this.currentSize = this.sizes[currentIndex - 1];
            this.applySize(this.currentSize);
            this.saveSize();
        }
    },
    
    resetSize() {
        this.currentSize = 100;
        this.applySize(this.currentSize);
        this.saveSize();
    },
    
    applySize(size) {
        // Remove all size classes
        this.sizes.forEach(s => {
            document.body.classList.remove(`text-size-${s}`);
        });
        
        // Add current size class
        document.body.classList.add(`text-size-${size}`);
        
        // Update indicator
        const indicator = document.getElementById('size-indicator');
        if (indicator) {
            indicator.textContent = `${size}%`;
        }
        
        // Disable/enable buttons at limits
        const decreaseBtn = document.querySelector('.size-btn[data-action="decrease"]');
        const increaseBtn = document.querySelector('.size-btn[data-action="increase"]');
        
        if (decreaseBtn) {
            decreaseBtn.disabled = size === this.sizes[0];
            decreaseBtn.style.opacity = size === this.sizes[0] ? '0.5' : '1';
            decreaseBtn.style.cursor = size === this.sizes[0] ? 'not-allowed' : 'pointer';
        }
        
        if (increaseBtn) {
            increaseBtn.disabled = size === this.sizes[this.sizes.length - 1];
            increaseBtn.style.opacity = size === this.sizes[this.sizes.length - 1] ? '0.5' : '1';
            increaseBtn.style.cursor = size === this.sizes[this.sizes.length - 1] ? 'not-allowed' : 'pointer';
        }
    },
    
    saveSize() {
        try {
            localStorage.setItem('readingTextSize', this.currentSize.toString());
        } catch (e) {
            console.warn('Could not save text size preference:', e);
        }
    },
    
    loadSavedSize() {
        try {
            const saved = localStorage.getItem('readingTextSize');
            if (saved && this.sizes.includes(parseInt(saved))) {
                this.currentSize = parseInt(saved);
            }
        } catch (e) {
            console.warn('Could not load text size preference:', e);
        }
    }
};

// Auto-initialize if DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => TextSizeControl.init());
} else {
    TextSizeControl.init();
}