// resources/js/modules/TextAnnotationSystem/NotesPanel.js

class NotesPanel {
    constructor(storage, noteManager) {
        this.storage = storage;
        this.noteManager = noteManager;
        this.panel = null;
        this.isOpen = false;

        this.createPanel();
        this.setupEventListeners();
    }

    createPanel() {
        this.panel = document.createElement('div');
        this.panel.className = 'notes-panel';
        this.panel.style.display = 'none';
        this.panel.innerHTML = `
            <div class="notes-panel-content">
                <div class="panel-header">
                    <h3>
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Your Notes
                    </h3>
                    <button class="close-panel-btn" id="close-notes-panel">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="notes-list" id="notes-list">
                    <!-- Notes will be populated here -->
                </div>
                <div class="panel-footer" id="panel-footer" style="display: none;">
                    <p class="empty-state">No notes yet. Select text and click "Note" to add one.</p>
                </div>
            </div>
        `;

        document.body.appendChild(this.panel);
    }

    setupEventListeners() {
        // Close button
        const closeBtn = this.panel.querySelector('#close-notes-panel');
        closeBtn.addEventListener('click', () => this.close());

        // Close on background click
        this.panel.addEventListener('click', (e) => {
            if (e.target === this.panel) {
                this.close();
            }
        });

        // Update notes when changed
        this.noteManager.on('noteAdded', () => this.updateNotesList());
        this.noteManager.on('noteDeleted', () => this.updateNotesList());
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.panel.style.display = 'flex';
        this.panel.style.opacity = '0';

        requestAnimationFrame(() => {
            this.panel.style.transition = 'opacity 0.3s ease-out';
            this.panel.style.opacity = '1';
        });

        this.isOpen = true;
        this.updateNotesList();

        // Add class to body to prevent scrolling
        document.body.classList.add('notes-panel-open');
    }

    close() {
        this.panel.style.opacity = '0';

        setTimeout(() => {
            this.panel.style.display = 'none';
            this.isOpen = false;

            // Remove class from body
            document.body.classList.remove('notes-panel-open');
        }, 300);
    }

    updateNotesList() {
        const notesList = this.panel.querySelector('#notes-list');
        const footer = this.panel.querySelector('#panel-footer');
        const notes = this.noteManager.getAllNotes();

        if (notes.length === 0) {
            notesList.innerHTML = '';
            footer.style.display = 'block';
            return;
        }

        footer.style.display = 'none';

        // Sort notes by creation date (newest first)
        const sortedNotes = notes.sort((a, b) =>
            new Date(b.createdAt) - new Date(a.createdAt)
        );

        notesList.innerHTML = sortedNotes.map(note => `
            <div class="note-item" data-note-id="${note.id}">
                <div class="note-item-header">
                    <span class="note-time">${this.formatTime(note.createdAt)}</span>
                    ${note.partNumber ? `<span class="note-part">Part ${note.partNumber}</span>` : ''}
                </div>
                <div class="note-text">
                    "${this.truncateText(note.text, 150)}"
                </div>
                <div class="note-content">
                    ${this.escapeHtml(note.note)}
                </div>
                <div class="note-actions">
                    <button class="jump-to-btn" data-note-id="${note.id}" title="Jump to text">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                            </path>
                        </svg>
                        Jump to text
                    </button>
                    <button class="delete-note-btn" data-note-id="${note.id}" title="Delete note">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        `).join('');

        // Add event listeners to buttons
        notesList.querySelectorAll('.jump-to-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const noteId = btn.dataset.noteId;
                this.jumpToNote(noteId);
            });
        });

        notesList.querySelectorAll('.delete-note-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const noteId = btn.dataset.noteId;
                if (confirm('Are you sure you want to delete this note?')) {
                    this.noteManager.deleteNote(noteId);
                }
            });
        });
    }

    jumpToNote(noteId) {
        const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
        if (noteElement && noteElement.classList.contains('noted-text')) {
            // Close panel
            this.close();

            // Scroll to element
            noteElement.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            // Highlight briefly
            noteElement.style.transition = 'background-color 0.3s ease';
            const originalBg = noteElement.style.backgroundColor;
            noteElement.style.backgroundColor = '#fbbf24';

            setTimeout(() => {
                noteElement.style.backgroundColor = originalBg;
            }, 1500);
        }
    }

    formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;

        if (diff < 60000) return 'just now';
        if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`;
        if (diff < 86400000) return `${Math.floor(diff / 3600000)}h ago`;

        // Format as date and time
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substr(0, maxLength) + '...';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    destroy() {
        if (this.panel && this.panel.parentNode) {
            this.panel.parentNode.removeChild(this.panel);
        }
    }
}

export default NotesPanel;