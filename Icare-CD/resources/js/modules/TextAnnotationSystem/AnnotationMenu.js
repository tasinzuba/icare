// resources/js/modules/TextAnnotationSystem/AnnotationMenu.js

class AnnotationMenu {
    constructor() {
        this.menu = null;
        this.currentData = null;
        this.listeners = {};
        this.isVisible = false;

        this.createMenu();
        this.setupEventListeners();
    }

    createMenu() {
        // Create menu element
        this.menu = document.createElement('div');
        this.menu.className = 'annotation-menu';
        this.menu.style.display = 'none';
        this.menu.innerHTML = `
            <button class="ann-btn note-btn" id="ann-note-btn" title="Add note">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                    </path>
                </svg>
                <span>Note</span>
            </button>
            <button class="ann-btn highlight-btn" id="ann-highlight-btn" title="Highlight">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
                <span>Highlight</span>
            </button>
        `;

        document.body.appendChild(this.menu);
    }

    setupEventListeners() {
        // Note button click
        const noteBtn = this.menu.querySelector('#ann-note-btn');
        noteBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.emit('note', this.currentData);
        });

        // Highlight button click
        const highlightBtn = this.menu.querySelector('#ann-highlight-btn');
        highlightBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.emit('highlight', this.currentData);
        });

        // Hide menu on document click
        document.addEventListener('mousedown', (e) => {
            if (!this.menu.contains(e.target)) {
                this.hide();
            }
        });

        // Hide menu on scroll
        let scrollTimeout;
        document.addEventListener('scroll', () => {
            if (this.isVisible) {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => this.hide(), 100);
            }
        }, true);

        // Hide on window resize
        window.addEventListener('resize', () => {
            if (this.isVisible) this.hide();
        });
    }

    show(data) {
        this.currentData = data;

        // Update button states
        const highlightBtn = this.menu.querySelector('#ann-highlight-btn');
        if (data.disableHighlight) {
            highlightBtn.disabled = true;
            highlightBtn.classList.add('disabled');
        } else {
            highlightBtn.disabled = false;
            highlightBtn.classList.remove('disabled');
        }

        // Position menu
        this.positionMenu(data.bounds);

        // Show with animation
        this.menu.style.display = 'flex';
        this.menu.style.opacity = '0';
        this.menu.style.transform = 'translateY(5px)';

        requestAnimationFrame(() => {
            this.menu.style.transition = 'opacity 0.2s ease-out, transform 0.2s ease-out';
            this.menu.style.opacity = '1';
            this.menu.style.transform = 'translateY(0)';
        });

        this.isVisible = true;
    }

    hide() {
        if (!this.isVisible) return;

        this.menu.style.opacity = '0';
        this.menu.style.transform = 'translateY(5px)';

        setTimeout(() => {
            this.menu.style.display = 'none';
            this.currentData = null;
            this.isVisible = false;
        }, 200);
    }

    positionMenu(bounds) {
        const menuRect = this.menu.getBoundingClientRect();
        const padding = 10;

        // Calculate initial position (above selection)
        let top = bounds.top + window.scrollY - menuRect.height - padding;
        let left = bounds.left + window.scrollX + (bounds.width / 2) - (menuRect.width / 2);

        // Check if menu goes above viewport
        if (top < window.scrollY + padding) {
            // Position below selection
            top = bounds.bottom + window.scrollY + padding;
            this.menu.classList.add('bottom');
        } else {
            this.menu.classList.remove('bottom');
        }

        // Check horizontal bounds
        if (left < padding) {
            left = padding;
        } else if (left + menuRect.width > window.innerWidth - padding) {
            left = window.innerWidth - menuRect.width - padding;
        }

        this.menu.style.position = 'absolute';
        this.menu.style.top = `${top}px`;
        this.menu.style.left = `${left}px`;
    }

    // Event emitter methods
    on(event, callback) {
        if (!this.listeners[event]) {
            this.listeners[event] = [];
        }
        this.listeners[event].push(callback);
    }

    emit(event, data) {
        if (this.listeners[event]) {
            this.listeners[event].forEach(callback => callback(data));
        }
    }

    destroy() {
        if (this.menu && this.menu.parentNode) {
            this.menu.parentNode.removeChild(this.menu);
        }
        this.listeners = {};
    }
}

export default AnnotationMenu;