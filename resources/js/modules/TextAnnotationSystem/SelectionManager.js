// resources/js/modules/TextAnnotationSystem/SelectionManager.js

class SelectionManager {
    constructor(container) {
        this.container = container;
        this.listeners = {};
        this.currentSelection = null;
        this.isSelecting = false;

        this.init();
    }

    init() {
        // Track mouse events for selection
        this.container.addEventListener('mousedown', () => {
            this.isSelecting = false;
        });

        this.container.addEventListener('mousemove', () => {
            this.isSelecting = true;
        });

        this.container.addEventListener('mouseup', (e) => {
            this.handleMouseUp(e);
        });

        // Handle touch devices
        this.container.addEventListener('touchend', (e) => {
            setTimeout(() => this.handleSelection(e), 100);
        });

        // Clear selection when clicking outside
        document.addEventListener('mousedown', (e) => {
            if (!this.container.contains(e.target) && !e.target.closest('.annotation-menu')) {
                this.clearSelection();
            }
        });
    }

    handleMouseUp(e) {
        // Small delay to ensure selection is complete
        setTimeout(() => {
            if (this.isSelecting) {
                this.handleSelection(e);
            }
        }, 10);
    }

    handleSelection(e) {
        const selection = window.getSelection();
        const selectedText = selection.toString().trim();

        if (selectedText.length === 0) return;

        // Check if selection is within our container
        if (!this.isSelectionInContainer(selection)) return;

        const range = selection.getRangeAt(0);

        // Get selection data
        const selectionData = {
            text: selectedText,
            range: this.serializeRange(range),
            bounds: range.getBoundingClientRect(),
            event: e
        };

        this.currentSelection = selectionData;
        this.emit('selection', selectionData);
    }

    isSelectionInContainer(selection) {
        if (selection.rangeCount === 0) return false;

        const range = selection.getRangeAt(0);
        const commonAncestor = range.commonAncestorContainer;

        // Check if the selection is within our container
        return this.container.contains(commonAncestor) ||
            this.container === commonAncestor;
    }

    serializeRange(range) {
        // Get the container element
        const container = range.commonAncestorContainer.nodeType === Node.TEXT_NODE
            ? range.commonAncestorContainer.parentElement
            : range.commonAncestorContainer;

        // Find the closest identifiable parent
        const identifiableParent = this.findIdentifiableParent(container);

        return {
            startOffset: range.startOffset,
            endOffset: range.endOffset,
            startContainer: this.getNodePath(range.startContainer, identifiableParent),
            endContainer: this.getNodePath(range.endContainer, identifiableParent),
            parentId: identifiableParent.id || identifiableParent.className,
            text: range.toString()
        };
    }

    findIdentifiableParent(element) {
        let current = element;
        while (current && current !== this.container) {
            if (current.id || current.className) {
                return current;
            }
            current = current.parentElement;
        }
        return this.container;
    }

    getNodePath(node, root) {
        const path = [];
        let current = node;

        while (current && current !== root) {
            const parent = current.parentNode;
            if (parent) {
                const index = Array.from(parent.childNodes).indexOf(current);
                path.unshift(index);
            }
            current = parent;
        }

        return path;
    }

    deserializeRange(rangeData) {
        try {
            const parent = document.getElementById(rangeData.parentId) ||
                document.querySelector(`.${rangeData.parentId}`);

            if (!parent) return null;

            const startNode = this.getNodeFromPath(parent, rangeData.startContainer);
            const endNode = this.getNodeFromPath(parent, rangeData.endContainer);

            if (!startNode || !endNode) return null;

            const range = document.createRange();
            range.setStart(startNode, rangeData.startOffset);
            range.setEnd(endNode, rangeData.endOffset);

            return range;
        } catch (e) {
            console.error('Error deserializing range:', e);
            return null;
        }
    }

    getNodeFromPath(root, path) {
        let current = root;
        for (const index of path) {
            if (current.childNodes[index]) {
                current = current.childNodes[index];
            } else {
                return null;
            }
        }
        return current;
    }

    clearSelection() {
        window.getSelection().removeAllRanges();
        this.currentSelection = null;
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
        // Remove event listeners
        this.container.removeEventListener('mouseup', this.handleMouseUp);
        this.container.removeEventListener('mousedown', () => { });
        this.container.removeEventListener('mousemove', () => { });
        this.listeners = {};
    }
}

export default SelectionManager;