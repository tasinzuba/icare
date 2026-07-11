// resources/js/modules/TextAnnotationSystem/HighlightManager.js

class HighlightManager {
    constructor(storage) {
        this.storage = storage;
        this.highlights = [];
        this.listeners = {};
        this.colorPicker = null;
        this.currentSelectionData = null;

        this.colors = {
            yellow: '#fef3c7',
            red: '#fee2e2',
            blue: '#dbeafe'
        };

        this.init();
    }

    init() {
        this.createColorPicker();
        this.loadHighlights();
    }

    createColorPicker() {
        this.colorPicker = document.createElement('div');
        this.colorPicker.className = 'highlight-color-picker';
        this.colorPicker.style.display = 'none';
        this.colorPicker.innerHTML = `
            <button class="color-btn yellow" data-color="yellow" title="Yellow highlight">
                <span class="color-swatch" style="background-color: ${this.colors.yellow}"></span>
            </button>
            <button class="color-btn red" data-color="red" title="Red highlight">
                <span class="color-swatch" style="background-color: ${this.colors.red}"></span>
            </button>
            <button class="color-btn blue" data-color="blue" title="Blue highlight">
                <span class="color-swatch" style="background-color: ${this.colors.blue}"></span>
            </button>
            <button class="color-btn remove" title="Cancel">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;

        document.body.appendChild(this.colorPicker);

        // Setup color picker event listeners
        this.colorPicker.querySelectorAll('.color-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const color = btn.dataset.color;
                if (color) {
                    this.applyHighlight(color);
                }

                this.hideColorPicker();
            });
        });
    }

    loadHighlights() {
        const data = this.storage.getData();
        this.highlights = data.highlights || [];
    }

    showColorPicker(selectionData) {
        this.currentSelectionData = selectionData;

        // Position color picker
        this.positionColorPicker(selectionData.bounds);

        // Show with animation
        this.colorPicker.style.display = 'flex';
        this.colorPicker.style.opacity = '0';
        this.colorPicker.style.transform = 'translateY(5px)';

        requestAnimationFrame(() => {
            this.colorPicker.style.transition = 'opacity 0.2s ease-out, transform 0.2s ease-out';
            this.colorPicker.style.opacity = '1';
            this.colorPicker.style.transform = 'translateY(0)';
        });

        // Hide on document click
        const hideHandler = (e) => {
            if (!this.colorPicker.contains(e.target)) {
                this.hideColorPicker();
                document.removeEventListener('click', hideHandler);
            }
        };

        setTimeout(() => {
            document.addEventListener('click', hideHandler);
        }, 0);
    }

    hideColorPicker() {
        this.colorPicker.style.opacity = '0';
        this.colorPicker.style.transform = 'translateY(5px)';

        setTimeout(() => {
            this.colorPicker.style.display = 'none';
            this.currentSelectionData = null;
        }, 200);
    }

    positionColorPicker(bounds) {
        const pickerRect = this.colorPicker.getBoundingClientRect();
        const padding = 10;

        // Calculate position (prefer above selection)
        let top = bounds.top + window.scrollY - pickerRect.height - padding;
        let left = bounds.left + window.scrollX + (bounds.width / 2) - (pickerRect.width / 2);

        // Check if picker goes above viewport
        if (top < window.scrollY + padding) {
            // Position below selection
            top = bounds.bottom + window.scrollY + padding;
            this.colorPicker.classList.add('bottom');
        } else {
            this.colorPicker.classList.remove('bottom');
        }

        // Check horizontal bounds
        if (left < padding) {
            left = padding;
        } else if (left + pickerRect.width > window.innerWidth - padding) {
            left = window.innerWidth - pickerRect.width - padding;
        }

        this.colorPicker.style.position = 'absolute';
        this.colorPicker.style.top = `${top}px`;
        this.colorPicker.style.left = `${left}px`;
    }

    applyHighlight(color) {
        if (!this.currentSelectionData) return;

        const highlightData = {
            id: `hl_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            text: this.currentSelectionData.text,
            color: color,
            range: this.currentSelectionData.range,
            createdAt: new Date().toISOString()
        };

        // Add to highlights array
        this.highlights.push(highlightData);

        // Apply highlight styling
        this.applyHighlightStyle(highlightData);

        // Save to storage
        this.storage.saveHighlights(this.highlights);

        // Clear selection
        window.getSelection().removeAllRanges();

        // Emit event
        this.emit('highlightAdded', highlightData);
    }

    applyHighlightStyle(highlightData) {
        try {
            // Recreate the range
            const range = this.deserializeRange(highlightData.range);
            if (!range) return;

            // Create wrapper span
            const highlightSpan = document.createElement('span');
            highlightSpan.className = `highlight-${highlightData.color}`;
            highlightSpan.dataset.highlightId = highlightData.id;
            highlightSpan.style.backgroundColor = this.colors[highlightData.color];
            highlightSpan.style.transition = 'background-color 0.3s ease';

            // Extract and wrap contents
            try {
                range.surroundContents(highlightSpan);
            } catch (e) {
                // If surroundContents fails, use alternative method
                const contents = range.extractContents();
                highlightSpan.appendChild(contents);
                range.insertNode(highlightSpan);
            }

            // Add click handler for removal
            highlightSpan.addEventListener('click', (e) => {
                if (e.ctrlKey || e.metaKey) {
                    e.stopPropagation();
                    this.removeHighlight(highlightData.id);
                }
            });

            // Add hover effect
            highlightSpan.addEventListener('mouseenter', function () {
                this.style.opacity = '0.8';
            });

            highlightSpan.addEventListener('mouseleave', function () {
                this.style.opacity = '1';
            });

        } catch (e) {
            console.error('Error applying highlight:', e);
        }
    }

    removeHighlight(highlightId) {
        // Remove from array
        this.highlights = this.highlights.filter(hl => hl.id !== highlightId);

        // Remove styling
        const highlightElement = document.querySelector(`[data-highlight-id="${highlightId}"]`);
        if (highlightElement) {
            // Animate removal
            highlightElement.style.transition = 'background-color 0.3s ease';
            highlightElement.style.backgroundColor = 'transparent';

            setTimeout(() => {
                const text = highlightElement.textContent;
                highlightElement.replaceWith(document.createTextNode(text));
            }, 300);
        }

        // Save to storage
        this.storage.saveHighlights(this.highlights);

        // Emit event
        this.emit('highlightRemoved', highlightId);
    }

    restoreHighlight(highlightData) {
        this.highlights.push(highlightData);
        this.applyHighlightStyle(highlightData);
    }

    hasHighlightInRange(range) {
        return this.highlights.some(highlight => {
            // Simple check - can be improved
            return highlight.text === range.text || range.text.includes(highlight.text);
        });
    }

    getAllHighlights() {
        return [...this.highlights];
    }

    deserializeRange(rangeData) {
        // This is simplified - in real implementation, use SelectionManager's method
        try {
            const parent = document.querySelector('.passage-content');
            if (!parent || !parent.textContent.includes(rangeData.text)) return null;

            // Find text node containing the text
            const walker = document.createTreeWalker(
                parent,
                NodeFilter.SHOW_TEXT,
                null,
                false
            );

            let node;
            while (node = walker.nextNode()) {
                if (node.textContent.includes(rangeData.text)) {
                    const range = document.createRange();
                    const startOffset = node.textContent.indexOf(rangeData.text);
                    range.setStart(node, startOffset);
                    range.setEnd(node, startOffset + rangeData.text.length);
                    return range;
                }
            }

            return null;
        } catch (e) {
            console.error('Error deserializing range:', e);
            return null;
        }
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
        // Remove color picker
        if (this.colorPicker && this.colorPicker.parentNode) {
            this.colorPicker.parentNode.removeChild(this.colorPicker);
        }
        this.listeners = {};
    }
}

export default HighlightManager;