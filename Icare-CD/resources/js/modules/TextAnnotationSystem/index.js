// resources/js/modules/TextAnnotationSystem/index.js

import SelectionManager from './SelectionManager';
import AnnotationMenu from './AnnotationMenu';
import NoteManager from './NoteManager';
import HighlightManager from './HighlightManager';
import NotesPanel from './NotesPanel';
import StorageManager from './StorageManager';
import './styles.css';

class TextAnnotationSystem {
    constructor(config) {
        this.config = {
            containerId: config.containerId || 'passage-content',
            attemptId: config.attemptId,
            testType: config.testType || 'reading',
            maxNotes: config.maxNotes || 100,
            ...config
        };

        this.container = null;
        this.modules = {};
        this.isInitialized = false;
    }

    init() {
        if (this.isInitialized) return;

        this.container = document.getElementById(this.config.containerId);
        if (!this.container) {
            console.error('TextAnnotationSystem: Container not found');
            return;
        }

        // Initialize storage
        this.storage = new StorageManager(this.config.attemptId, this.config.testType);

        // Initialize modules
        this.modules.selectionManager = new SelectionManager(this.container);
        this.modules.annotationMenu = new AnnotationMenu();
        this.modules.noteManager = new NoteManager(this.storage);
        this.modules.highlightManager = new HighlightManager(this.storage);
        this.modules.notesPanel = new NotesPanel(this.storage, this.modules.noteManager);

        // Setup event listeners
        this.setupEventListeners();

        // Restore saved annotations
        this.restoreAnnotations();

        // Add notes button to navigation
        this.addNotesButton();

        this.isInitialized = true;
    }

    setupEventListeners() {
        // Listen for text selection
        this.modules.selectionManager.on('selection', (selectionData) => {
            this.handleSelection(selectionData);
        });

        // Listen for annotation menu actions
        this.modules.annotationMenu.on('note', (data) => {
            this.modules.noteManager.createNote(data);
            this.modules.annotationMenu.hide();
        });

        this.modules.annotationMenu.on('highlight', (data) => {
            this.modules.highlightManager.showColorPicker(data);
            this.modules.annotationMenu.hide();
        });

        // Listen for note updates
        this.modules.noteManager.on('noteAdded', () => {
            this.updateNotesCount();
        });

        this.modules.noteManager.on('noteDeleted', () => {
            this.updateNotesCount();
        });

        // Clean up on page unload
        window.addEventListener('beforeunload', () => {
            this.storage.save();
        });
    }

    handleSelection(selectionData) {
        // Check if selection overlaps with existing annotations
        const hasNote = this.modules.noteManager.hasNoteInRange(selectionData.range);
        const hasHighlight = this.modules.highlightManager.hasHighlightInRange(selectionData.range);

        // Show annotation menu
        this.modules.annotationMenu.show({
            ...selectionData,
            disableHighlight: hasNote, // Can't highlight noted text
            disableNote: false // Can always add notes
        });
    }

    restoreAnnotations() {
        const data = this.storage.load();

        // Restore notes
        if (data.notes && data.notes.length > 0) {
            data.notes.forEach(noteData => {
                this.modules.noteManager.restoreNote(noteData);
            });
        }

        // Restore highlights
        if (data.highlights && data.highlights.length > 0) {
            data.highlights.forEach(highlightData => {
                this.modules.highlightManager.restoreHighlight(highlightData);
            });
        }

        this.updateNotesCount();
    }

    addNotesButton() {
        const navRight = document.querySelector('.nav-right');
        if (!navRight) return;

        // Check if button already exists
        if (document.getElementById('notes-panel-btn')) return;

        const notesButton = document.createElement('button');
        notesButton.id = 'notes-panel-btn';
        notesButton.className = 'notes-panel-btn';
        notesButton.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            <span>Notes</span>
            <span class="notes-count" id="notes-count">0</span>
        `;

        notesButton.addEventListener('click', () => {
            this.modules.notesPanel.toggle();
        });

        // Insert before submit button
        const submitBtn = navRight.querySelector('.submit-test-button');
        if (submitBtn) {
            navRight.insertBefore(notesButton, submitBtn);
        } else {
            navRight.appendChild(notesButton);
        }
    }

    updateNotesCount() {
        const count = this.modules.noteManager.getNotesCount();
        const countElement = document.getElementById('notes-count');
        if (countElement) {
            countElement.textContent = count;
            countElement.style.display = count > 0 ? 'inline-flex' : 'none';
        }
    }

    reinitializeForContainer(container) {
        // Simple implementation for single container mode
        if (!this.isInitialized) return;

        // Re-apply annotations when switching containers/parts
        const data = this.storage.load();

        // Clear existing highlights/notes from DOM
        document.querySelectorAll('.noted-text, .highlight-yellow, .highlight-red, .highlight-blue').forEach(el => {
            const text = el.textContent;
            el.replaceWith(document.createTextNode(text));
        });

        // Re-apply saved annotations
        setTimeout(() => {
            this.restoreAnnotations();
        }, 100);
    }

    destroy() {
        // Clean up all modules
        Object.values(this.modules).forEach(module => {
            if (module.destroy) module.destroy();
        });

        // Save data
        this.storage.save();

        this.isInitialized = false;
    }
}

export default TextAnnotationSystem;