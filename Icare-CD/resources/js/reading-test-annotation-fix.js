// ⭐ CRITICAL FIX: Annotation System - Block Options Area Completely
// This file patches the annotation handler to prevent highlighting/notes on option text

document.addEventListener('DOMContentLoaded', function() {
    // Wait for annotation system to load
    setTimeout(() => {
        if (window.SimpleAnnotationSystem) {
            console.log('🔧 Applying annotation fix - blocking options area...');
            
            // Override the setupAnnotationHandlers function
            const originalSetup = SimpleAnnotationSystem.setupAnnotationHandlers;
            
            SimpleAnnotationSystem.setupAnnotationHandlers = function() {
                // ⭐ ALLOWED: Only passage content and question NUMBER/INSTRUCTIONS
                const ALLOWED_SELECTORS = [
                    '.passage-content',      // ✅ Passage text
                    '.question-content',     // ✅ Question content (NOT options)
                    '.question-instructions',// ✅ Instructions
                    '.question-instruction', // ✅ Instructions (alternate)
                    '.part-instruction',     // ✅ Part instructions
                    '.question-group-header',// ✅ Group headers
                    '.word-list-box',        // ✅ Word lists
                    '.ielts-q-number'        // ✅ Question numbers
                ];
                
                // ⭐ FORBIDDEN: Block entire options area + all interactive elements
                const FORBIDDEN_SELECTORS = [
                    'input',                 // ⭐ ALL inputs
                    'select',                // ⭐ ALL selects
                    'textarea',              // ⭐ ALL textareas
                    'button',                // ⭐ ALL buttons
                    'label',                 // ⭐ ALL labels
                    '.ielts-options',        // ⭐ CRITICAL: Entire options container
                    '.ielts-option',         // ⭐ Individual option items
                    '.option-text',          // Option text
                    '.option-label',         // Option labels
                    '.drop-box',             // Drop zones
                    '.draggable-option',     // Draggable items
                    '.number-btn',           // Navigation buttons
                    '.answer-input',         // Answer inputs
                    '.passage-answer-input', // Passage answers
                    '.mh-heading-item',      // Matching heading items
                    '.passage-drop-zone'     // Passage drop zones
                ];
                
                // Text selection handler
                document.addEventListener('mouseup', (e) => {
                    // Skip if clicking on menus/modals
                    if (e.target.closest('#annotation-menu') ||
                        e.target.closest('#note-modal') ||
                        e.target.closest('#notes-panel')) {
                        return;
                    }

                    // Skip right clicks
                    if (e.button === 2) return;

                    setTimeout(() => {
                        const selection = window.getSelection();
                        const selectedText = selection.toString().trim();

                        if (selectedText && selectedText.length >= 3) {
                            const range = selection.getRangeAt(0);
                            const container = range.commonAncestorContainer;
                            const element = container.nodeType === 3 ? container.parentElement : container;

                            // ⭐ CRITICAL: Check if selection is in ANY forbidden element
                            let isForbidden = false;
                            
                            // Check the element itself
                            for (const selector of FORBIDDEN_SELECTORS) {
                                if (element.matches(selector) || element.closest(selector)) {
                                    isForbidden = true;
                                    console.log('❌ Selection blocked - forbidden area:', selector);
                                    break;
                                }
                            }

                            if (isForbidden) {
                                this.hideMenu();
                                return;
                            }

                            // ⭐ Check if selection is in ALLOWED area
                            let isAllowed = false;
                            for (const selector of ALLOWED_SELECTORS) {
                                if (element.closest(selector)) {
                                    isAllowed = true;
                                    console.log('✅ Selection allowed in:', selector);
                                    break;
                                }
                            }

                            if (!isAllowed) {
                                console.log('❌ Selection not in allowed area');
                                this.hideMenu();
                                return;
                            }

                            const rect = range.getBoundingClientRect();
                            this.currentRange = range;
                            this.showMenu(rect, selectedText);
                        } else {
                            this.hideMenu();
                        }
                    }, 10);
                });

                // Hide menu on document click
                document.addEventListener('mousedown', (e) => {
                    if (this.currentMenu && !this.currentMenu.contains(e.target)) {
                        this.hideMenu();
                    }
                });

                console.log('✅ Annotation fix applied successfully!');
            };
            
            // Re-initialize with fixed handlers
            SimpleAnnotationSystem.setupAnnotationHandlers();
            
            console.log('✅ Options area is now fully protected from annotations!');
        }
    }, 2000); // Wait 2 seconds for system to load
});