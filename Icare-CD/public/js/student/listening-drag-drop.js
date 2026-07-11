/**
 * Listening Test - Drag & Drop Handler
 * Handles drag and drop functionality for both matching and drag-drop question types
 */

window.ListeningDragDrop = {
    init() {
        console.log('Initializing Listening Drag & Drop');
        this.setupDraggableOptions();
        this.setupDropZones();
        
        // INSTANT INITIAL COUNT
        this.instantUpdateCount();
    },
    
    instantUpdateCount() {
        // Fast direct count
        const totalAnswered = document.querySelectorAll('.number-btn.answered').length;
        const answeredSpan = document.getElementById('answered-count');
        if (answeredSpan) {
            answeredSpan.textContent = totalAnswered;
        }
    },
    
    initializeAnswerCount() {
        // IMPORTANT: First clear all drag-drop question answered states
        const dropBoxes = document.querySelectorAll('.drop-box');
        
        dropBoxes.forEach(box => {
            const questionNumber = box.dataset.questionNumber;
            const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
            
            // Check if box ACTUALLY has answer (not just placeholder)
            const hasRealAnswer = box.classList.contains('has-answer') || 
                                 (box.textContent && 
                                  box.textContent.trim() !== questionNumber && 
                                  box.textContent.trim() !== '' &&
                                  !box.querySelector('.placeholder-text'));
            
            if (navButton) {
                if (hasRealAnswer) {
                    navButton.classList.add('answered');
                } else {
                    // IMPORTANT: Remove answered if no real answer
                    navButton.classList.remove('answered');
                }
            }
        });
        
        // INSTANT UPDATE
        this.instantUpdateCount();
    },

    setupDraggableOptions() {
        const draggableOptions = document.querySelectorAll('.draggable-option');
        console.log('Found draggable options:', draggableOptions.length);
        
        // Also setup the options container to handle drops properly
        const optionsContainer = document.querySelector('.draggable-options-grid');
        if (optionsContainer) {
            // Prevent default behavior when dragging over the options area
            optionsContainer.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
            });
            
            // Handle drop on the options container (to restore option)
            optionsContainer.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                // Don't do anything - the dragend event will handle restoration
                console.log('Dropped on options container - restoration will be handled by dragend');
            });
        }

        draggableOptions.forEach(option => {
            // Debug: Log the data attributes
            console.log('Option data:', {
                optionValue: option.dataset.optionValue,
                optionLetter: option.dataset.optionLetter,
                innerHTML: option.innerHTML,
                textContent: option.textContent
            });
            
            option.addEventListener('dragstart', (e) => {
                // Get the actual text value from the element - clean up whitespace
                const optionValue = option.dataset.optionValue || option.textContent.trim().replace(/\s+/g, ' ');
                const optionLetter = option.dataset.optionLetter || '';
                
                console.log('🚀 Drag started from option list:', { 
                    optionValue, 
                    optionLetter,
                    textContent: option.textContent.trim(),
                    dataset: option.dataset 
                });
                
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', optionValue);
                e.dataTransfer.setData('option-letter', optionLetter);
                e.dataTransfer.setData('full-text', option.innerHTML);
                e.dataTransfer.setData('clean-text', optionValue); // Add clean text as backup
                
                option.classList.add('dragging');
            });

            option.addEventListener('dragend', () => {
                option.classList.remove('dragging');
            });
        });
    },

    setupDropZones() {
        const dropBoxes = document.querySelectorAll('.drop-box');
        console.log('Found drop boxes:', dropBoxes.length);

        dropBoxes.forEach(box => {
            // Dragover event
            box.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                box.classList.add('drag-over');
                // Force border style change to dashed
                box.style.borderStyle = 'dashed';
                box.style.borderColor = '#000000';
                console.log('Drag over - class added:', box.classList.contains('drag-over'));
            });

            // Dragleave event
            box.addEventListener('dragleave', () => {
                box.classList.remove('drag-over');
                // Reset border style to solid
                box.style.borderStyle = 'solid';
                box.style.borderColor = '#000000';
                console.log('Drag leave - class removed');
            });

            // Drop event
            box.addEventListener('drop', (e) => {
                e.preventDefault();
                box.classList.remove('drag-over');
                box.style.borderStyle = 'solid';
                box.style.borderColor = '#000000';

                // Get option value with fallbacks
                let optionValue = e.dataTransfer.getData('text/plain');
                const optionLetter = e.dataTransfer.getData('option-letter');
                const fullText = e.dataTransfer.getData('full-text');
                const cleanText = e.dataTransfer.getData('clean-text');
                const fromBox = e.dataTransfer.getData('from-box') === 'true';
                
                // Use fallbacks if optionValue is undefined or empty
                if (!optionValue || optionValue === 'undefined') {
                    optionValue = cleanText || fullText?.trim()?.replace(/\s+/g, ' ') || 'Error';
                }
                
                const questionId = box.dataset.questionId;
                const zoneIndex = box.dataset.zoneIndex;
                const index = box.dataset.index; // For matching questions
                const questionNumber = box.dataset.questionNumber;
                const allowReuse = box.dataset.allowReuse === '1';

                console.log('📍 Drop event:', {
                    optionValue,
                    cleanText,
                    fullText,
                    fromBox,
                    questionId,
                    zoneIndex,
                    index,
                    allowReuse,
                    questionNumber
                });

                // Handle existing answer - this will restore old option to drag list
                if (box.classList.contains('has-answer')) {
                    this.removeExistingAnswer(box, allowReuse);
                }

                // Add new answer
                this.addAnswerToDropBox(box, optionValue, optionLetter, fullText);

                // Update hidden input
                this.updateHiddenInput(box, questionId, zoneIndex, index, optionValue, questionNumber);

                // Always hide option after placing (to avoid confusion)
                // Student can drag from drop box to reuse if needed
                this.markOptionAsPlaced(optionValue);
                
                // Mark that drag was successful - cancel clearing of source box if from another box
                if (fromBox) {
                    const sourceBoxId = e.dataTransfer.getData('source-box-id');
                    if (sourceBoxId) {
                        const [qId, zIndex] = sourceBoxId.split('_');
                        const sourceBox = document.querySelector(`.drop-box[data-question-id="${qId}"][data-zone-index="${zIndex}"]`);
                        if (sourceBox) {
                            delete sourceBox.dataset.shouldClear;
                            console.log('✅ Cancelled clearing of source box - successful drop');
                        }
                    }
                }

                // Save and update UI - INSTANT UPDATE
                if (typeof saveAllAnswers === 'function') saveAllAnswers();
                
                // INSTANT UPDATE BOTTOM COUNT
                const answeredButtons = document.querySelectorAll('.number-btn.answered').length;
                const answeredSpan = document.getElementById('answered-count');
                if (answeredSpan) {
                    answeredSpan.textContent = answeredButtons;
                }
            });

            // Setup click-to-remove on drop box
            this.setupAnswerRemoval(box);
        });
    },

    removeExistingAnswer(box, allowReuse) {
        const answerText = box.textContent.trim();
        const questionNumber = box.dataset.questionNumber;
        
        // Check if there's actually an existing answer (not just placeholder)
        if (answerText && answerText !== questionNumber && !box.querySelector('.placeholder-text')) {
            const oldValue = answerText;
            
            console.log('🔄 REPLACING existing answer:', oldValue, 'in drop zone:', questionNumber);
            
            // Always restore the old option to make it available again
            this.restoreOption(oldValue);
            
            // Clear the box
            box.innerHTML = `<span class="placeholder-text">${questionNumber}</span>`;
            box.classList.remove('has-answer');
            box.removeAttribute('draggable');
            
            // Remove answered state for this specific box temporarily (will be re-added when new answer is placed)
            const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
            if (navButton) {
                navButton.classList.remove('answered');
            }
            
            console.log('✅ Old answer restored to drag list:', oldValue);
        }
    },

    addAnswerToDropBox(box, optionValue, optionLetter, fullText) {
        const questionNumber = box.dataset.questionNumber;
        
        // Debug logging
        console.log('✅ Adding answer to box:', { optionValue, optionLetter, fullText });
        
        // Clean the option value - remove letter prefix if present
        let cleanValue = optionValue;
        if (!cleanValue || cleanValue === 'undefined') {
            // Extract text from fullText if optionValue is undefined
            cleanValue = fullText ? fullText.replace(/^[A-Z]\.\s*/, '').trim() : 'Error';
        }
        
        // Clear the box and add answer with proper width
        box.style.display = 'inline-flex';
        box.style.alignItems = 'center';
        box.style.justifyContent = 'center';
        box.style.minWidth = '150px';
        box.style.width = 'auto';
        box.style.padding = '0 15px';
        
        box.textContent = cleanValue;
        box.classList.add('has-answer');
        box.setAttribute('draggable', 'true');
        
        // Add drag handlers to filled box for re-dragging
        this.setupFilledBoxDrag(box, cleanValue, optionLetter);
    },
    
    setupFilledBoxDrag(box, optionValue, optionLetter) {
        // Make filled box draggable
        box.addEventListener('dragstart', (e) => {
            console.log('🚀 Dragging FROM drop box:', optionValue);
            
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', optionValue);
            e.dataTransfer.setData('option-letter', optionLetter || '');
            e.dataTransfer.setData('full-text', optionValue);
            e.dataTransfer.setData('clean-text', optionValue);
            e.dataTransfer.setData('from-box', 'true');
            e.dataTransfer.setData('source-box-id', box.dataset.questionId + '_' + box.dataset.zoneIndex);
            
            box.classList.add('dragging-from-box');
            
            // Mark box for clearing - it WILL be cleared regardless of where it's dropped
            box.dataset.shouldClear = 'true';
        });
        
        box.addEventListener('dragend', (e) => {
            console.log('🏁 Drag ended from drop box:', optionValue);
            box.classList.remove('dragging-from-box');
            
            // Small delay to ensure drop events complete
            setTimeout(() => {
                // ALWAYS clear the box when dragged from it (regardless of where dropped)
                // The only exception is if it was successfully dropped in another drop zone
                if (box.dataset.shouldClear === 'true') {
                    const questionNumber = box.dataset.questionNumber;
                    const zoneNumber = box.dataset.zoneNumber;
                    const questionId = box.dataset.questionId;
                    
                    console.log('🔄 Clearing drop box and restoring option:', optionValue);
                    
                    // Reset box to empty state with proper styling
                    box.style.display = 'inline-flex';
                    box.style.alignItems = 'center';
                    box.style.justifyContent = 'center';
                    box.innerHTML = `<span class="placeholder-text">${questionNumber}</span>`;
                    box.classList.remove('has-answer');
                    box.removeAttribute('draggable');
                    delete box.dataset.shouldClear;
                    
                    // Clear the hidden input
                    const inputName = zoneNumber !== undefined 
                        ? `answers[${questionId}][zone_${zoneNumber}]`
                        : `answers[${questionId}][zone_${box.dataset.zoneIndex}]`;
                    
                    const hiddenInput = document.querySelector(`input[name="${inputName}"]`);
                    if (hiddenInput) {
                        hiddenInput.value = '';
                        hiddenInput.dispatchEvent(new Event('change'));
                    }
                    
                    // Remove answered state from navigation button
                    const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
                    if (navButton) {
                        navButton.classList.remove('answered');
                        
                        // INSTANT BOTTOM COUNT UPDATE
                        const totalAnswered = document.querySelectorAll('.number-btn.answered').length;
                        const answeredSpan = document.getElementById('answered-count');
                        if (answeredSpan) {
                            answeredSpan.textContent = totalAnswered;
                        }
                    }
                    
                    // ALWAYS restore the option to the draggable list
                    this.restoreOption(optionValue);
                    
                    // Save the removal
                    if (typeof saveAllAnswers === 'function') {
                        saveAllAnswers();
                    }
                    
                    console.log('✅ Option restored to drag list:', optionValue);
                }
            }, 50); // Small delay to let drop events complete
        });
    },

    updateHiddenInput(box, questionId, zoneIndex, index, optionValue, questionNumber) {
        // Get the actual zone number from data attribute for backend matching
        const zoneNumber = box.dataset.zoneNumber;
        let inputName;

        if (zoneNumber !== undefined) {
            // Use zone number from [DRAG_X] for backend matching
            inputName = `answers[${questionId}][zone_${zoneNumber}]`;
        } else if (zoneIndex !== undefined) {
            // Fallback: Drag-drop question with zone index
            inputName = `answers[${questionId}][zone_${zoneIndex}]`;
        } else if (index !== undefined) {
            // Matching question
            inputName = `answers[${questionId}_${index}]`;
        } else {
            console.error('No zone identifier found');
            return;
        }

        let hiddenInput = document.querySelector(`input[name="${inputName}"]`);

        // ⭐ CREATE HIDDEN INPUT IF IT DOESN'T EXIST
        if (!hiddenInput) {
            console.log('🔧 Creating hidden input:', inputName);
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = inputName;
            hiddenInput.value = '';

            // Append to form
            const form = document.getElementById('listening-form');
            if (form) {
                form.appendChild(hiddenInput);
                console.log('✅ Hidden input created and added to form');
            } else {
                console.error('Form not found!');
                return;
            }
        }

        // Update the value
        hiddenInput.value = optionValue;
        console.log('💾 Updated input:', inputName, '=', optionValue);

        // Update navigation button for THIS specific zone - INSTANT
        const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
        if (navButton && !navButton.classList.contains('answered')) {
            navButton.classList.add('answered');

            // INSTANT BOTTOM COUNT UPDATE
            const totalAnswered = document.querySelectorAll('.number-btn.answered').length;
            const answeredSpan = document.getElementById('answered-count');
            if (answeredSpan) {
                answeredSpan.textContent = totalAnswered;
            }
        }
    },

    markOptionAsPlaced(optionValue) {
        // Find and hide all options with this value
        const options = document.querySelectorAll(`.draggable-option`);
        console.log('🔍 markOptionAsPlaced called with:', optionValue);
        console.log('🔍 Found draggable options:', options.length);
        
        let foundMatch = false;
        
        options.forEach((option, index) => {
            const optVal = option.dataset.optionValue || option.dataset.option || option.textContent.trim().replace(/\s+/g, ' ');
            console.log(`🔍 Option ${index}:`, {
                optVal,
                textContent: option.textContent.trim(),
                dataset: option.dataset,
                comparing: optionValue
            });
            
            // Exact match with the option value
            if (optVal === optionValue || option.textContent.trim().replace(/\s+/g, ' ') === optionValue) {
                console.log('✅ HIDING option:', optVal);
                foundMatch = true;
                
                // Just hide it, don't remove from DOM
                option.classList.add('placed');
                option.style.display = 'none';
                option.style.visibility = 'hidden';
            }
        });
        
        if (!foundMatch) {
            console.log('❌ No matching option found for:', optionValue);
        }
    },

    restoreOption(optionValue) {
        console.log('🔄 restoreOption called with:', optionValue);
        
        // Find the hidden option and show it
        const options = document.querySelectorAll('.draggable-option');
        let restored = false;
        
        options.forEach(option => {
            const optVal = option.dataset.optionValue || option.textContent.trim().replace(/\s+/g, ' ');
            
            if (optVal === optionValue || option.textContent.trim().replace(/\s+/g, ' ') === optionValue) {
                console.log('🔄 Option found, making visible:', optionValue);
                
                // Remove placed class and restore visibility
                option.classList.remove('placed');
                option.style.display = '';
                option.style.visibility = '';
                option.removeAttribute('hidden');
                
                restored = true;
            }
        });
        
        if (!restored) {
            console.log('❌ Option not found in DOM, cannot restore:', optionValue);
        } else {
            console.log('✅ Successfully restored option to drag list:', optionValue);
        }
    },

    setupAnswerRemoval(box) {
        // Allow click to remove answer from drop box
        box.addEventListener('click', (e) => {
            if (box.classList.contains('has-answer') && e.target === box) {
                const optionValue = box.textContent.trim();
                const questionNumber = box.dataset.questionNumber;
                const questionId = box.dataset.questionId;
                
                console.log('👆 Click to remove answer:', optionValue);
                
                // Restore the option
                this.restoreOption(optionValue);
                
                // Clear the box
                box.innerHTML = `<span class="placeholder-text">${questionNumber}</span>`;
                box.classList.remove('has-answer');
                box.removeAttribute('draggable');
                
                // Clear hidden input
                const zoneNumber = box.dataset.zoneNumber;
                const zoneIndex = box.dataset.zoneIndex;
                const inputName = zoneNumber !== undefined 
                    ? `answers[${questionId}][zone_${zoneNumber}]`
                    : `answers[${questionId}][zone_${zoneIndex}]`;
                
                const hiddenInput = document.querySelector(`input[name="${inputName}"]`);
                if (hiddenInput) {
                    hiddenInput.value = '';
                }
                
                // Remove answered state
                const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
                if (navButton) {
                    navButton.classList.remove('answered');
                }
                
                // Update counts
                this.instantUpdateCount();
                
                if (typeof saveAllAnswers === 'function') {
                    saveAllAnswers();
                }
            }
        });
    }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (document.querySelector('.draggable-option') || document.querySelector('.drop-box')) {
            window.ListeningDragDrop.init();
        }
    });
} else {
    // DOM already loaded
    if (document.querySelector('.draggable-option') || document.querySelector('.drop-box')) {
        window.ListeningDragDrop.init();
    }
}
