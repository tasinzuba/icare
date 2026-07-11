// resources/js/modules/TextAnnotationSystem/NoteManager.js

class NoteManager {
    constructor(storage) {
        this.storage = storage;
        this.notes = [];
        this.listeners = {};
        this.noteModal = null;
        this.noteTooltip = null;

        this.init();
    }

    init() {
        this.createNoteModal();
        this.createNoteTooltip();
        this.loadNotes();
    }

    createNoteModal() {
        this.noteModal = document.createElement('div');
        this.noteModal.className = 'note-modal-overlay';
        this.noteModal.style.display = 'none';
        this.noteModal.innerHTML = `
            <div class="note-modal">
                <div class="note-modal-content">
                    <div class="note-header">
                        <h3>Add Note</h3>
                        <button class="close-btn" id="note-modal-close">×</button>
                    </div>
                    <div class="selected-text" id="note-selected-text"></div>
                    <textarea 
                        class="note-input" 
                        id="note-input"
                        placeholder="Type your note here..."
                        maxlength="500"
                        rows="4"
                    ></textarea>
                    <div class="note-footer">
                        <span class="char-count" id="note-char-count">0/500</span>
                        <button class="save-note-btn" id="save-note-btn">Save Note</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(this.noteModal);

        // Setup modal event listeners
        const closeBtn = this.noteModal.querySelector('#note-modal-close');
        closeBtn.addEventListener('click', () => this.closeNoteModal());

        const textarea = this.noteModal.querySelector('#note-input');
        textarea.addEventListener('input', (e) => {
            const count = e.target.value.length;
            this.noteModal.querySelector('#note-char-count').textContent = `${count}/500`;
        });

        const saveBtn = this.noteModal.querySelector('#save-note-btn');
        saveBtn.addEventListener('click', () => this.saveNote());

        // Close on background click
        this.noteModal.addEventListener('click', (e) => {
            if (e.target === this.noteModal) {
                this.closeNoteModal();
            }
        });
    }

    createNoteTooltip() {
        this.noteTooltip = document.createElement('div');
        this.noteTooltip.className = 'note-tooltip';
        this.noteTooltip.style.display = 'none';
        document.body.appendChild(this.noteTooltip);
    }

    loadNotes() {
        const data = this.storage.getData();
        this.notes = data.notes || [];
    }

    createNote(selectionData) {
        this.currentSelectionData = selectionData;

        // Show modal
        this.noteModal.querySelector('#note-selected-text').textContent =
            `"${this.truncateText(selectionData.text, 100)}"`;
        this.noteModal.querySelector('#note-input').value = '';
        this.noteModal.querySelector('#note-char-count').textContent = '0/500';

        this.noteModal.style.display = 'flex';

        // Focus textarea
        setTimeout(() => {
            this.noteModal.querySelector('#note-input').focus();
        }, 100);
    }

    saveNote() {
        const noteText = this.noteModal.querySelector('#note-input').value.trim();
        if (!noteText) return;

        const noteData = {
            id: `note_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            text: this.currentSelectionData.text,
            note: noteText,
            range: this.currentSelectionData.range,
            createdAt: new Date().toISOString(),
            partNumber: this.detectPartNumber(this.currentSelectionData.range)
        };

        // Add to notes array
        this.notes.push(noteData);

        // Apply note styling
        this.applyNoteStyle(noteData);

        // Save to storage
        this.storage.saveNotes(this.notes);

        // Close modal
        this.closeNoteModal();

        // Clear selection
        window.getSelection().removeAllRanges();

        // Emit event
        this.emit('noteAdded', noteData);
    }

    applyNoteStyle(noteData) {
        try {
            // Recreate the range
            const range = this.deserializeRange(noteData.range);
            if (!range) return;

            // Create wrapper span
            const noteSpan = document.createElement('span');
            noteSpan.className = 'noted-text';
            noteSpan.dataset.noteId = noteData.id;

            // Extract and wrap contents
            try {
                range.surroundContents(noteSpan);
            } catch (e) {
                // If surroundContents fails, use alternative method
                const contents = range.extractContents();
                noteSpan.appendChild(contents);
                range.insertNode(noteSpan);
            }

            // Add click handler
            noteSpan.addEventListener('click', (e) => {
                e.stopPropagation();
                this.showNoteTooltip(noteData, e.target);
            });

        } catch (e) {
            console.error('Error applying note style:', e);
        }
    }

    showNoteTooltip(noteData, element) {
        // Update tooltip content
        this.noteTooltip.innerHTML = `
            <div class="note-tooltip-content">
                <div class="note-tooltip-text">${noteData.note}</div>
                <div class="note-tooltip-footer">
                    <span class="note-date">${this.formatDate(noteData.createdAt)}</span>
                    <button class="note-delete-btn" data-note-id="${noteData.id}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        // Position tooltip
        const rect = element.getBoundingClientRect();
        this.noteTooltip.style.display = 'block';
        this.noteTooltip.style.position = 'absolute';
        this.noteTooltip.style.top = `${rect.bottom + window.scrollY + 5}px`;
        this.noteTooltip.style.left = `${rect.left + window.scrollX}px`;

        // Add delete handler
        const deleteBtn = this.noteTooltip.querySelector('.note-delete-btn');
        deleteBtn.addEventListener('click', () => {
            this.deleteNote(noteData.id);
            this.hideNoteTooltip();
        });

        // Hide on document click
        const hideHandler = (e) => {
            if (!this.noteTooltip.contains(e.target) && e.target !== element) {
                this.hideNoteTooltip();
                document.removeEventListener('click', hideHandler);
            }
        };

        setTimeout(() => {
            document.addEventListener('click', hideHandler);
        }, 0);
    }

    hideNoteTooltip() {
        this.noteTooltip.style.display = 'none';
    }

    deleteNote(noteId) {
        // Remove from array
        this.notes = this.notes.filter(note => note.id !== noteId);

        // Remove styling
        const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
        if (noteElement) {
            const text = noteElement.textContent;
            noteElement.replaceWith(document.createTextNode(text));
        }

        // Save to storage
        this.storage.saveNotes(this.notes);

        // Emit event
        this.emit('noteDeleted', noteId);
    }

    restoreNote(noteData) {
        this.notes.push(noteData);
        this.applyNoteStyle(noteData);
    }

    hasNoteInRange(range) {
        return this.notes.some(note => {
            // Simple check - can be improved
            return note.text === range.text || range.text.includes(note.text);
        });
    }

    getNotesCount() {
        return this.notes.length;
    }

    getAllNotes() {
        return [...this.notes];
    }

    closeNoteModal() {
        this.noteModal.style.display = 'none';
        this.currentSelectionData = null;
    }

    truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substr(0, maxLength) + '...';
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;

        if (diff < 60000) return 'just now';
        if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`;
        if (diff < 86400000) return `${Math.floor(diff / 3600000)}h ago`;

        return date.toLocaleDateString();
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
        // Remove modal and tooltip
        if (this.noteModal && this.noteModal.parentNode) {
            this.noteModal.parentNode.removeChild(this.noteModal);
        }
        if (this.noteTooltip && this.noteTooltip.parentNode) {
            this.noteTooltip.parentNode.removeChild(this.noteTooltip);
        }
        this.listeners = {};
    }
}

export default NoteManager;