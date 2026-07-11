// Simplified Reading Test System - Clean & Simple
// No extra features, just essential functionality

// ========== Security & Copy Protection ==========
document.addEventListener('DOMContentLoaded', function() {
    // Disable right-click context menu
    document.addEventListener('contextmenu', function(e) {
        // Allow in annotation areas but prevent browser menu
        const isAnnotationArea = e.target.closest('.passage-content') || e.target.closest('.question-text');
        if (isAnnotationArea) {
            e.preventDefault();
            return false;
        }
        e.preventDefault();
        return false;
    });
    
    // Disable Ctrl+A (Select All)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
            e.preventDefault();
            return false;
        }
    });
    
    // Disable Ctrl+C (Copy)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
            e.preventDefault();
            return false;
        }
    });
    
    // Disable copy event
    document.addEventListener('copy', function(e) {
        e.preventDefault();
        return false;
    });
});

// ========== Global Variables ==========
let currentColorPicker = null;
let selectedTextRange = null;
let scrollTimeout;
let annotationSystem = null;

// ========== Simple Split Divider System ==========
const SimpleSplitDivider = {
    init() {
        this.container = document.querySelector('.content-area');
        this.divider = document.getElementById('split-divider');
        this.passageSection = document.querySelector('.passage-section');
        this.questionsSection = document.querySelector('.questions-section');

        // Configuration
        this.minWidth = 300;

        // State
        this.isResizing = false;
        this.startX = 0;
        this.startWidth = 0;

        if (!this.divider || !this.passageSection || window.innerWidth <= 1024) {
            return;
        }

        this.setupDivider();
        this.loadSavedLayout();

        console.log('Simple Split Divider initialized');
    },

    setupDivider() {
        // Create divider if it doesn't exist
        if (!this.divider) {
            this.divider = document.createElement('div');
            this.divider.id = 'split-divider';
            this.divider.className = 'split-divider';
            this.divider.title = 'Drag to resize sections';

            this.container.insertBefore(this.divider, this.questionsSection);
        }

        // Mouse events
        this.divider.addEventListener('mousedown', (e) => this.startResize(e));
        document.addEventListener('mousemove', (e) => this.resize(e));
        document.addEventListener('mouseup', () => this.stopResize());

        // Touch events
        this.divider.addEventListener('touchstart', (e) => this.startResize(e.touches[0]));
        document.addEventListener('touchmove', (e) => this.resize(e.touches[0]));
        document.addEventListener('touchend', () => this.stopResize());

        // Double-click to reset
        this.divider.addEventListener('dblclick', () => this.resetLayout());

        // Prevent selection
        this.divider.addEventListener('selectstart', (e) => e.preventDefault());
    },

    startResize(e) {
        this.isResizing = true;
        this.startX = e.clientX;
        this.startWidth = this.passageSection.offsetWidth;

        // Visual feedback
        document.body.classList.add('dragging');

        // Disable animations during resize
        this.passageSection.style.transition = 'none';
        this.questionsSection.style.transition = 'none';

        e.preventDefault();
    },

    resize(e) {
        if (!this.isResizing) return;

        const deltaX = e.clientX - this.startX;
        const newWidth = this.startWidth + deltaX;
        const containerWidth = this.container.offsetWidth;
        const percentage = (newWidth / containerWidth) * 100;

        // Apply constraints
        const minPercent = (this.minWidth / containerWidth) * 100;
        const maxPercent = 100 - minPercent;

        if (percentage >= minPercent && percentage <= maxPercent) {
            const roundedPercent = Math.round(percentage);
            this.passageSection.style.flex = `0 0 ${roundedPercent}%`;
        }
    },

    stopResize() {
        if (!this.isResizing) return;

        this.isResizing = false;

        // Remove visual feedback
        document.body.classList.remove('dragging');

        // Re-enable animations
        this.passageSection.style.transition = '';
        this.questionsSection.style.transition = '';

        // Save layout
        const currentPercent = this.getCurrentPercentage();
        this.saveLayout(currentPercent);
    },

    getCurrentPercentage() {
        const passageWidth = this.passageSection.offsetWidth;
        const containerWidth = this.container.offsetWidth;
        return Math.round((passageWidth / containerWidth) * 100);
    },

    resetLayout() {
        // Visual feedback for reset
        this.divider.style.transition = 'all 0.3s ease';
        this.divider.style.width = '5px';
        this.divider.style.background = '#10b981';
        
        this.passageSection.style.transition = 'flex 0.3s ease';
        this.passageSection.style.flex = '0 0 50%';
        
        setTimeout(() => {
            this.divider.style.width = '';
            this.divider.style.background = '';
            this.divider.style.transition = '';
            this.passageSection.style.transition = '';
        }, 300);
        
        this.saveLayout(50);
    },

    saveLayout(percentage) {
        try {
            localStorage.setItem('readingLayoutWidth', percentage.toString());
        } catch (e) {
            console.warn('Could not save layout preference:', e);
        }
    },

    loadSavedLayout() {
        try {
            const saved = localStorage.getItem('readingLayoutWidth');
            if (saved) {
                const percentage = parseInt(saved);
                if (percentage >= 25 && percentage <= 75) {
                    setTimeout(() => {
                        this.passageSection.style.flex = `0 0 ${percentage}%`;
                    }, 100);
                }
            }
        } catch (e) {
            console.warn('Could not load layout preference:', e);
        }
    },

    destroy() {
        // Remove event listeners
        if (this.divider) {
            this.divider.removeEventListener('mousedown', this.startResize);
            this.divider.removeEventListener('touchstart', this.startResize);
            this.divider.removeEventListener('dblclick', this.resetLayout);
        }

        document.removeEventListener('mousemove', this.resize);
        document.removeEventListener('mouseup', this.stopResize);
        document.removeEventListener('touchmove', this.resize);
        document.removeEventListener('touchend', this.stopResize);

        console.log('Simple Split Divider destroyed');
    }
};

// ========== Navigation Handler ==========
const NavigationHandler = {
    init() {
        this.setupPartNavigation();
        this.setupQuestionNavigation();
        this.setupReviewCheckbox();
    },

    setupPartNavigation() {
        const partButtons = document.querySelectorAll('.part-btn');
        const passageContainers = document.querySelectorAll('.passage-container');
        const questionParts = document.querySelectorAll('.part-questions');

        partButtons.forEach(button => {
            button.addEventListener('click', function () {
                // Update active button
                partButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const partNumber = this.dataset.part;

                // Hide all question parts
                questionParts.forEach(part => {
                    part.style.display = 'none';
                });

                // Show questions for this part
                const targetQuestionPart = document.querySelector(`.part-questions[data-part="${partNumber}"]`);
                if (targetQuestionPart) {
                    targetQuestionPart.style.display = 'block';
                    
                    // Update global header
                    NavigationHandler.updateGlobalHeader(partNumber);
                }

                // Update passage display
                passageContainers.forEach(container => {
                    container.classList.remove('active');
                });

                const partPassage = document.querySelector(`.passage-container[data-part="${partNumber}"]`);
                if (partPassage) {
                    partPassage.classList.add('active');

                    // Reinitialize annotation system for new passage
                    if (annotationSystem && annotationSystem.reinitializeForContainer) {
                        annotationSystem.reinitializeForContainer(partPassage);
                    }
                }

                // Update question numbers display based on part
                NavigationHandler.updateQuestionNumbersDisplay(partNumber);

                // Find first question of this part
                const firstQuestionOfPart = document.querySelector(`.number-btn[data-part="${partNumber}"]:not(.hidden-part)`);
                if (firstQuestionOfPart) {
                    firstQuestionOfPart.click();
                }
            });
        });
        
        // Initialize the first part header
        const firstPartBtn = document.querySelector('.part-btn.active');
        if (firstPartBtn) {
            NavigationHandler.updateGlobalHeader(firstPartBtn.dataset.part);
        }
    },
    
    updateGlobalHeader(partNumber) {
        const headerContainer = document.getElementById('global-part-header');
        const partData = document.querySelector(`.part-questions[data-part="${partNumber}"] .part-questions-inner`);
        
        if (headerContainer && partData) {
            const startNumber = partData.dataset.startNumber;
            const endNumber = partData.dataset.endNumber;
            
            headerContainer.innerHTML = `
                <div class="part-header">
                    <div class="part-title">Part ${partNumber}</div>
                    <div class="part-instruction">Read and answer questions ${startNumber}-${endNumber}.</div>
                </div>
            `;
        }
    },

    updateQuestionNumbersDisplay(activePart) {
        // Hide all question numbers first
        const allNumberButtons = document.querySelectorAll('.number-btn');
        allNumberButtons.forEach(btn => {
            if (btn.dataset.part === activePart) {
                btn.classList.remove('hidden-part');
            } else {
                btn.classList.add('hidden-part');
            }
        });
    },

    setupQuestionNavigation() {
        const navButtons = document.querySelectorAll('.number-btn');

        navButtons.forEach(button => {
            button.addEventListener('click', function () {
                // Update active button
                navButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const questionId = this.dataset.question;
                const blankIndex = this.dataset.blank;
                const partNumber = this.dataset.part;
                const questionElement = document.getElementById(`question-${questionId}`);

                if (questionElement) {
                    // Smooth scroll to question
                    questionElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    // Focus on specific blank if needed
                    if (blankIndex) {
                        const inputs = questionElement.querySelectorAll('.gap-input, .gap-dropdown');
                        if (inputs[blankIndex - 1]) {
                            setTimeout(() => inputs[blankIndex - 1].focus(), 300);
                        }
                    }

                    // Update part if needed
                    if (partNumber) {
                        const currentActivePart = document.querySelector('.part-btn.active');
                        if (!currentActivePart || currentActivePart.dataset.part !== partNumber) {
                            const partBtn = document.querySelector(`.part-btn[data-part="${partNumber}"]`);
                            if (partBtn) partBtn.click();
                        }
                    }
                }

                // Update review checkbox
                const reviewCheckbox = document.getElementById('review-checkbox');
                if (reviewCheckbox) {
                    reviewCheckbox.checked = this.classList.contains('flagged');
                }
            });
        });
    },

    setupReviewCheckbox() {
        const reviewCheckbox = document.getElementById('review-checkbox');
        if (reviewCheckbox) {
            reviewCheckbox.addEventListener('change', function () {
                const currentQuestion = document.querySelector('.number-btn.active');
                if (currentQuestion) {
                    if (this.checked) {
                        currentQuestion.classList.add('flagged');
                    } else {
                        currentQuestion.classList.remove('flagged');
                    }
                }
            });
        }
    }
};

// ========== Answer Manager ==========
const AnswerManager = {
    autoSaveIntervalId: null,

    init(attemptId) {
        this.attemptId = attemptId;
        this.setupAnswerTracking();
        this.loadSavedAnswers();
        this.startAutoSave();
    },

    setupAnswerTracking() {
        // Track fill-in-the-blanks
        document.querySelectorAll('.gap-input, .gap-dropdown').forEach(input => {
            input.addEventListener('change', () => this.trackAnswer(input));
            input.addEventListener('blur', () => this.trackAnswer(input));

            // Auto-width for blanks based on content
            if (input.classList.contains('gap-input')) {
                // Set initial width based on placeholder
                this.adjustInputWidth(input);
                
                input.addEventListener('input', function () {
                    AnswerManager.adjustInputWidth(this);
                });
            }

            // Tab navigation
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    e.preventDefault();
                    const allInputs = document.querySelectorAll('.gap-input, .gap-dropdown, .text-input, .option-radio');
                    const currentIndex = Array.from(allInputs).indexOf(input);
                    const nextIndex = e.shiftKey ? currentIndex - 1 : currentIndex + 1;

                    if (nextIndex >= 0 && nextIndex < allInputs.length) {
                        allInputs[nextIndex].focus();
                    }
                }

                // Enter key to move to next input
                if (e.key === 'Enter' && input.tagName.toLowerCase() === 'input') {
                    e.preventDefault();
                    const allInputs = document.querySelectorAll('.gap-input, .text-input');
                    const currentIndex = Array.from(allInputs).indexOf(input);
                    const nextIndex = currentIndex + 1;

                    if (nextIndex < allInputs.length) {
                        allInputs[nextIndex].focus();
                    }
                }
            });
        });

        // Track regular questions
        document.querySelectorAll('input[type="radio"], input[type="text"]:not(.gap-input), select:not(.gap-dropdown)').forEach(input => {
            input.addEventListener('change', () => this.trackAnswer(input));
        });
        
        // Track checkbox inputs for multiple choice (Choose TWO/THREE type questions)
        document.querySelectorAll('input[type="checkbox"].multiple-choice-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                // Get question info from data attributes
                const questionId = checkbox.dataset.questionId || checkbox.name.replace('answers[', '').replace('][]', '');
                const baseNum = parseInt(checkbox.dataset.questionNumber);
                const correctCount = parseInt(checkbox.dataset.correctCount) || 1;

                // Get all checkboxes for this question
                const allCheckboxes = document.querySelectorAll(`input[name="answers[${questionId}][]"]`);
                const checkedCount = document.querySelectorAll(`input[name="answers[${questionId}][]"]:checked`).length;

                // FIXED: Mark nav buttons SEQUENTIALLY based on number of checked answers
                // For "Choose TWO" (Q14-Q15):
                //   - 1 checked = Q14 answered
                //   - 2 checked = Q14 AND Q15 answered
                for (let i = 0; i < correctCount; i++) {
                    const navButton = document.querySelector(`.number-btn[data-display-number="${baseNum + i}"]`);
                    if (navButton) {
                        if (i < checkedCount) {
                            // Mark as answered if we have enough checked boxes
                            navButton.classList.add('answered');
                        } else {
                            // Remove answered if not enough checked
                            navButton.classList.remove('answered');
                        }
                    }
                }

                console.log(`Multiple choice Q${baseNum}: ${checkedCount}/${correctCount} selected`);

                this.saveAllAnswers();
                this.updateAnswerCount();
            });
        });
        
        // IMPORTANT FIX: Track ALL select dropdowns including matching headings
        // This includes regular selects and those for matching headings with array notation
        document.querySelectorAll('select').forEach(select => {
            // Add change event listener to all selects
            select.addEventListener('change', () => {
                console.log('Select changed:', select.name, select.value);
                this.trackAnswer(select);
                this.saveAllAnswers(); // Force save
            });
            
            // Also add blur event for extra safety
            select.addEventListener('blur', () => {
                if (select.value) {
                    this.trackAnswer(select);
                }
            });
        });
        
        // Special handling for matching headings with array notation
        document.querySelectorAll('select[name*="["][name*="]"]').forEach(select => {
            console.log('Found array notation select:', select.name);
            
            // Ensure this select is properly tracked
            if (!select.hasAttribute('data-tracking-initialized')) {
                select.setAttribute('data-tracking-initialized', 'true');
                
                select.addEventListener('change', (e) => {
                    console.log('Array notation select changed:', e.target.name, e.target.value);
                    this.trackAnswer(e.target);
                    this.saveAllAnswers();
                });
            }
        });
    },

    trackAnswer(input) {
        console.log('Tracking answer for:', input.name || input.id, 'Value:', input.value);
        
        const questionNumber = input.dataset.questionNumber;
        if (questionNumber) {
            const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
            if (navButton) {
                if (input.value && input.value.trim()) {
                    navButton.classList.add('answered');
                } else {
                    navButton.classList.remove('answered');
                }
            }
        }
        
        // Special handling for sentence completion with _q notation
        if (input.name && input.name.includes('_q')) {
            // Extract the sub-question number from name like answers[123_q14]
            const match = input.name.match(/answers\[(\d+)_q(\d+)\]/);
            if (match) {
                const questionId = match[1];
                const subQuestionNum = match[2];
                
                // Find the nav button with the matching display number
                const navButton = document.querySelector(`.number-btn[data-display-number="${subQuestionNum}"]`);
                if (navButton) {
                    if (input.value && input.value.trim()) {
                        navButton.classList.add('answered');
                    } else {
                        navButton.classList.remove('answered');
                    }
                }
            }
        }
        
        // For matching headings, check all related selects
        if (input.name && input.name.includes('[') && input.name.includes(']')) {
            // Extract question ID from name like answers[123][0]
            const match = input.name.match(/answers\[(\d+)\]/);
            if (match) {
                const questionId = match[1];
                // Check all selects for this question
                const relatedSelects = document.querySelectorAll(`select[name^="answers[${questionId}]"]`);
                let hasAnyAnswer = false;
                
                relatedSelects.forEach(select => {
                    if (select.value && select.value.trim()) {
                        hasAnyAnswer = true;
                    }
                });
                
                // Update the navigation button based on any answers
                const navButton = document.querySelector(`.number-btn[data-question="${questionId}"]`);
                if (navButton) {
                    if (hasAnyAnswer) {
                        navButton.classList.add('answered');
                    } else {
                        navButton.classList.remove('answered');
                    }
                }
            }
        }
        
        this.saveAllAnswers();
        this.updateAnswerCount();
    },

    updateAnswerCount() {
        // Count unique answered questions properly
        let answeredCount = 0;
        const processedQuestions = new Set();
        
        // Count all answered navigation buttons
        document.querySelectorAll('.number-btn.answered').forEach(btn => {
            answeredCount++;
        });
        
        // Also check for any filled inputs that might not have updated nav buttons
        // NOTE: Exclude .multiple-choice-checkbox as they are handled separately with sequential counting
        document.querySelectorAll('input[type="text"]:not(.gap-input), input[type="radio"]:checked, input[type="checkbox"]:checked:not(.multiple-choice-checkbox)').forEach(input => {
            const questionNumber = input.dataset.questionNumber;
            if (questionNumber && input.value && input.value.trim()) {
                const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
                if (navButton && !navButton.classList.contains('answered')) {
                    navButton.classList.add('answered');
                    answeredCount++;
                }
            }
        });
        
        // Check all selects including matching headings and sentence completion
        document.querySelectorAll('select').forEach(select => {
            if (select.value && select.value.trim()) {
                const questionNumber = select.dataset.questionNumber;
                if (questionNumber) {
                    const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
                    if (navButton && !navButton.classList.contains('answered')) {
                        navButton.classList.add('answered');
                        answeredCount++;
                    }
                }
            }
        });
        
        // Recount after updates
        answeredCount = document.querySelectorAll('.number-btn.answered').length;
        
        console.log('Total answered questions:', answeredCount);

        // Update submit modal count
        const answeredCountSpan = document.getElementById('answered-count');
        if (answeredCountSpan) {
            answeredCountSpan.textContent = answeredCount;
        }
    },

    adjustInputWidth(input) {
        // Create a temporary span to measure text width
        const span = document.createElement('span');
        span.style.position = 'absolute';
        span.style.visibility = 'hidden';
        span.style.whiteSpace = 'pre';
        span.style.font = window.getComputedStyle(input).font;
        span.style.fontSize = '13px'; // Match input font size
        span.style.fontFamily = window.getComputedStyle(input).fontFamily;
        
        // Use input value or placeholder for measurement
        const textToMeasure = input.value || input.placeholder || '';
        span.textContent = textToMeasure;
        
        document.body.appendChild(span);
        const textWidth = span.offsetWidth;
        document.body.removeChild(span);
        
        // Calculate new width with padding
        const padding = 24; // Account for padding (10px * 2) + extra space
        const minWidth = 80; // Increased minimum width
        const maxWidth = 200; // Increased max width for longer answers
        
        let newWidth = textWidth + padding;
        
        // Apply constraints
        if (newWidth < minWidth) {
            newWidth = minWidth;
        } else if (newWidth > maxWidth) {
            newWidth = maxWidth;
        }
        
        // Apply the new width
        input.style.width = newWidth + 'px';
    },

    saveAllAnswers() {
        const formData = new FormData(document.getElementById('reading-form'));
        const answers = {};

        // Debug: Log all form entries
        console.log('Saving all answers...');

        for (let [key, value] of formData.entries()) {
            if (key.startsWith('answers[') && value) {
                answers[key] = value;
                console.log('Saved:', key, '=', value);
            }
        }

        // Save to localStorage (immediate, offline backup)
        try {
            localStorage.setItem(`testAnswers_${this.attemptId}`, JSON.stringify(answers));
            console.log('Total answers saved to localStorage:', Object.keys(answers).length);
        } catch (e) {
            console.warn('Could not save answers to localStorage:', e);
        }

        // Save to server (persistent, crash-safe)
        this.saveToServer(answers);
    },

    // Server-side auto-save for data safety
    saveToServer(answers) {
        if (!this.attemptId || Object.keys(answers).length === 0) {
            return;
        }

        // Prevent multiple simultaneous server saves
        if (this.isSavingToServer) {
            console.log('Server save already in progress, skipping...');
            return;
        }

        this.isSavingToServer = true;

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        fetch(`/student/test/reading/auto-save/${this.attemptId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ answers: answers }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`Server auto-save successful at ${data.saved_at} (${data.answers_count} answers)`);
                // Show subtle indicator
                this.showSaveIndicator('success');
            } else {
                console.warn('Server auto-save failed:', data.error);
                this.showSaveIndicator('error');
            }
        })
        .catch(error => {
            console.error('Server auto-save error:', error);
            this.showSaveIndicator('error');
        })
        .finally(() => {
            this.isSavingToServer = false;
        });
    },

    // Show subtle save indicator
    showSaveIndicator(status) {
        // Remove existing indicator
        const existing = document.getElementById('save-indicator');
        if (existing) existing.remove();

        const indicator = document.createElement('div');
        indicator.id = 'save-indicator';
        indicator.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
            background: ${status === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        `;
        indicator.textContent = status === 'success' ? 'Saved' : 'Save failed';
        document.body.appendChild(indicator);

        // Fade in
        setTimeout(() => indicator.style.opacity = '1', 10);

        // Fade out and remove
        setTimeout(() => {
            indicator.style.opacity = '0';
            setTimeout(() => indicator.remove(), 300);
        }, 2000);
    },

    loadSavedAnswers() {
        // First try to load from server (most reliable, crash-safe)
        this.loadFromServer().then(serverAnswers => {
            if (serverAnswers && Object.keys(serverAnswers).length > 0) {
                console.log('Loaded answers from server:', Object.keys(serverAnswers).length);
                this.applyAnswers(serverAnswers);
            } else {
                // Fall back to localStorage
                this.loadFromLocalStorage();
            }
        }).catch(error => {
            console.warn('Server load failed, using localStorage:', error);
            this.loadFromLocalStorage();
        });
    },

    // Load saved answers from server
    loadFromServer() {
        return new Promise((resolve, reject) => {
            fetch(`/student/test/reading/draft-answers/${this.attemptId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.answers) {
                    resolve(data.answers);
                } else {
                    resolve(null);
                }
            })
            .catch(error => {
                reject(error);
            });
        });
    },

    // Load from localStorage (fallback)
    loadFromLocalStorage() {
        try {
            const savedAnswers = localStorage.getItem(`testAnswers_${this.attemptId}`);
            if (savedAnswers) {
                const answers = JSON.parse(savedAnswers);
                console.log('Loaded answers from localStorage:', Object.keys(answers).length);
                this.applyAnswers(answers);
            }
        } catch (e) {
            console.error('Error loading from localStorage:', e);
        }
    },

    // Apply answers to form inputs
    applyAnswers(answers) {
        Object.keys(answers).forEach(key => {
            const value = answers[key];
            const input = document.querySelector(`[name="${key}"]`);

            if (input) {
                if (input.type === 'radio') {
                    const radio = document.querySelector(`[name="${key}"][value="${value}"]`);
                    if (radio) {
                        radio.checked = true;
                        this.trackAnswer(radio);
                    }
                } else {
                    input.value = value;
                    this.trackAnswer(input);

                    // Adjust width for gap inputs
                    if (input.classList.contains('gap-input')) {
                        this.adjustInputWidth(input);
                    }
                }
            }
        });

        // Update the answer count after applying
        this.updateAnswerCount();
    },

    startAutoSave() {
        // Clear any existing interval first
        this.stopAutoSave();
        // Store interval ID so it can be cleared later
        this.autoSaveIntervalId = setInterval(() => this.saveAllAnswers(), 30000); // Every 30 seconds
    },

    stopAutoSave() {
        if (this.autoSaveIntervalId) {
            clearInterval(this.autoSaveIntervalId);
            this.autoSaveIntervalId = null;
            console.log('Auto-save interval cleared');
        }
    },

    cleanup() {
        // Stop all intervals
        this.stopAutoSave();
        console.log('AnswerManager cleanup completed');
    }
};

// ========== Submit Handler ==========
const SubmitHandler = {
    init() {
        this.setupSubmitModal();
        this.setupWarnings();
        this.debugFormSubmission();
    },
    
    debugFormSubmission() {
        const form = document.getElementById('reading-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('=== FORM SUBMISSION DEBUG ===');
                console.log('Form is submitting...');
                
                // Check all form data
                const formData = new FormData(form);
                let hasAnswers = false;
                
                console.log('All form data:');
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('answers[')) {
                        console.log(key, ':', value);
                        if (value) hasAnswers = true;
                    }
                }
                
                if (!hasAnswers) {
                    console.error('WARNING: No answers found in form data!');
                }
                
                // Check for any required fields
                const requiredFields = form.querySelectorAll('[required]');
                if (requiredFields.length > 0) {
                    console.log('Required fields found:', requiredFields.length);
                    requiredFields.forEach(field => {
                        if (!field.value) {
                            console.error('Empty required field:', field.name);
                        }
                    });
                }
                
                console.log('=== END DEBUG ===');
            });
        }
    },

    setupSubmitModal() {
        const submitTestBtn = document.getElementById('submit-test-btn');
        const submitModal = document.getElementById('submit-modal');
        const confirmSubmitBtn = document.getElementById('confirm-submit-btn');
        const cancelSubmitBtn = document.getElementById('cancel-submit-btn');
        const submitButton = document.getElementById('submit-button');

        if (submitTestBtn) {
            submitTestBtn.addEventListener('click', () => {
                const answeredCount = document.querySelectorAll('.number-btn.answered').length;
                const totalCount = document.querySelectorAll('.number-btn').length;

                // Update modal content
                const answeredCountSpan = document.getElementById('answered-count');
                if (answeredCountSpan) {
                    answeredCountSpan.textContent = answeredCount;
                }

                submitModal.style.display = 'flex';
            });
        }

        if (confirmSubmitBtn) {
            confirmSubmitBtn.addEventListener('click', () => {
                // CRITICAL: Cleanup all background processes first
                AnswerManager.cleanup();

                // CRITICAL: Remove beforeunload listener to prevent "Leave site?" dialog
                if (SubmitHandler.preventLeaveHandler) {
                    window.removeEventListener('beforeunload', SubmitHandler.preventLeaveHandler);
                }
                window.onbeforeunload = null;

                // Stop timer if exists
                if (window.UniversalTimer) {
                    window.UniversalTimer.stop();
                }

                // Save final answers
                AnswerManager.saveAllAnswers();

                // Clear localStorage after successful save (server will also clear draft_answers)
                try {
                    localStorage.removeItem(`testAnswers_${AnswerManager.attemptId}`);
                    console.log('localStorage cleared for attempt:', AnswerManager.attemptId);
                } catch (e) {
                    console.warn('Could not clear localStorage:', e);
                }

                // Save annotations before submit
                if (annotationSystem && annotationSystem.storage) {
                    annotationSystem.storage.save();
                }

                // Add passage answer inputs to form before submission
                const form = document.getElementById('reading-form');
                if (form) {
                    const passageInputs = document.querySelectorAll('.passage-answer-input');
                    passageInputs.forEach(input => {
                        if (input.value && input.value.trim() !== '') {
                            const clone = input.cloneNode(true);
                            form.appendChild(clone);
                        }
                    });
                }

                // Show loading state
                confirmSubmitBtn.textContent = 'Submitting...';
                confirmSubmitBtn.disabled = true;

                // Set a timeout for submission recovery (60 seconds)
                const submitTimeout = setTimeout(() => {
                    console.error('Form submission timeout - resetting button');
                    confirmSubmitBtn.textContent = 'Submit Test';
                    confirmSubmitBtn.disabled = false;
                    alert('Submission is taking longer than expected. Please try again or check your internet connection.');
                }, 60000);

                // Store timeout ID for cleanup
                SubmitHandler.submitTimeoutId = submitTimeout;

                // Submit form
                setTimeout(() => {
                    submitButton.click();
                }, 500);
            });
        }

        if (cancelSubmitBtn) {
            cancelSubmitBtn.addEventListener('click', () => {
                submitModal.style.display = 'none';
            });
        }

        // Close modal on background click
        if (submitModal) {
            submitModal.addEventListener('click', (e) => {
                if (e.target === submitModal) {
                    submitModal.style.display = 'none';
                }
            });
        }
    },

    setupWarnings() {
        // Store the function reference so we can remove it later
        this.preventLeaveHandler = (e) => {
            const unansweredCount = document.querySelectorAll('.number-btn:not(.answered)').length;
            if (unansweredCount > 0) {
                e.preventDefault();
                e.returnValue = 'You have unanswered questions. Are you sure you want to leave?';
            }
        };
        
        // Warn before leaving page
        window.addEventListener('beforeunload', this.preventLeaveHandler);
    }
};

// ========== Help Guide ==========
const HelpGuide = {
    init() {
        this.setupEventListeners();
    },

    setupEventListeners() {
        const helpButton = document.getElementById('help-button');
        const closeBtn = document.getElementById('help-close-btn');
        const modal = document.getElementById('help-modal');

        if (helpButton) {
            helpButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.open();
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }

        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.close();
            });

            // ESC key to close
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    this.close();
                }
            });
        }
    },

    open() {
        const modal = document.getElementById('help-modal');
        if (modal) {
            modal.style.display = 'flex';
            this.loadContent();
        }
    },

    close() {
        const modal = document.getElementById('help-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    },

    loadContent() {
        const contentArea = document.getElementById('help-content');
        if (!contentArea) return;

        contentArea.innerHTML = `
            <div class="help-section">
                <h3>IELTS Reading Test Guide</h3>
                <p>Welcome to the computer-delivered IELTS Reading Test.</p>
                
                <h4>📖 Test Structure:</h4>
                <ul>
                    <li><strong>Duration:</strong> 60 minutes</li>
                    <li><strong>Questions:</strong> 40 questions in total</li>
                    <li><strong>Sections:</strong> 3 reading passages (Parts 1, 2, 3)</li>
                    <li><strong>Difficulty:</strong> Increases from Part 1 to Part 3</li>
                </ul>
                
                <h4>🖥️ Interface Layout:</h4>
                <ul>
                    <li><strong>Left Side:</strong> Reading passages</li>
                    <li><strong>Right Side:</strong> Questions</li>
                    <li><strong>Resizable:</strong> Drag the center divider to adjust sizes</li>
                    <li><strong>Reset:</strong> Double-click the divider to reset layout</li>
                </ul>
                
                <h4>🧭 Navigation:</h4>
                <ul>
                    <li><strong>Part Navigation:</strong> Click Part 1, 2, or 3 buttons</li>
                    <li><strong>Question Numbers:</strong> Click any number to jump to that question</li>
                    <li><strong>Review Flag:</strong> Check "Review" to flag questions for later</li>
                    <li><strong>Progress:</strong> Green numbers show answered questions</li>
                </ul>
                
                <h4>📝 Answer Types:</h4>
                <ul>
                    <li><strong>Multiple Choice:</strong> Select A, B, C, or D</li>
                    <li><strong>True/False/Not Given:</strong> Choose the correct option</li>
                    <li><strong>Fill in the Blanks:</strong> Type your answer in the input fields</li>
                    <li><strong>Matching:</strong> Select from dropdown menus</li>
                    <li><strong>Short Answer:</strong> Type brief answers</li>
                </ul>
                
                <h4>📝 Text Annotations:</h4>
                <ul>
                    <li><strong>Notes:</strong> Select text in passages and add personal notes</li>
                    <li><strong>View Notes:</strong> Click the "Notes" button to see all your notes</li>
                    <li><strong>Auto-save:</strong> All notes are automatically saved</li>
                </ul>
                
                <h4>💾 Auto-save Features:</h4>
                <ul>
                    <li><strong>Answers:</strong> Automatically saved every 30 seconds</li>
                    <li><strong>Layout:</strong> Your preferred layout is remembered</li>
                    <li><strong>Recovery:</strong> Answers restored if page is refreshed</li>
                </ul>
                
                <h4>💡 Tips for Success:</h4>
                <ul>
                    <li>Read questions before reading the passage</li>
                    <li>Use the review flag for difficult questions</li>
                    <li>Manage your time: roughly 20 minutes per passage</li>
                    <li>Use notes to mark important information</li>
                    <li>Check all answers before submitting</li>
                </ul>
            </div>
        `;
    }
};

// ========== Simple Annotation System ==========
const SimpleAnnotationSystem = {
    init() {
        this.currentMenu = null;
        this.currentModal = null;
        this.currentRange = null;

        this.createNoteModal();
        this.createNotesPanel();
        this.setupAnnotationHandlers();
        this.restoreAnnotations();
        this.addNotesButton();
    },

    createNoteModal() {
        const modal = document.createElement('div');
        modal.id = 'note-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100000;
        `;

        modal.innerHTML = `
            <div style="
                background: white;
                border-radius: 8px;
                width: 90%;
                max-width: 450px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            ">
                <div style="padding: 16px; border-bottom: 1px solid #e5e7eb;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #111827;">Add Note</h3>
                    <p style="margin: 6px 0 0 0; font-size: 13px; color: #6b7280;" id="selected-text-preview"></p>
                </div>
                <div style="padding: 16px;">
                    <textarea 
                        id="note-textarea"
                        placeholder="Type your note here..."
                        style="
                            width: 100%;
                            min-height: 100px;
                            padding: 10px;
                            border: 1px solid #e5e7eb;
                            border-radius: 6px;
                            font-size: 14px;
                            resize: vertical;
                            font-family: inherit;
                            box-sizing: border-box;
                        "
                    ></textarea>
                    <div style="margin-top: 6px; text-align: right; font-size: 12px; color: #9ca3af;">
                        <span id="char-count">0</span>/500
                    </div>
                </div>
                <div style="
                    padding: 12px 16px;
                    background: #f9fafb;
                    border-top: 1px solid #e5e7eb;
                    display: flex;
                    justify-content: flex-end;
                    gap: 10px;
                    border-radius: 0 0 8px 8px;
                ">
                    <button id="close-note-modal-btn" style="
                        padding: 6px 16px;
                        border: 1px solid #e5e7eb;
                        background: white;
                        border-radius: 4px;
                        font-size: 13px;
                        cursor: pointer;
                        transition: all 0.2s;
                    ">Cancel</button>
                    <button id="save-note-btn" style="
                        padding: 6px 16px;
                        background: #3b82f6;
                        color: white;
                        border: none;
                        border-radius: 4px;
                        font-size: 13px;
                        cursor: pointer;
                        transition: all 0.2s;
                    ">Save Note</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        this.noteModal = modal;

        // Setup event listeners
        const textarea = modal.querySelector('#note-textarea');
        const charCount = modal.querySelector('#char-count');
        textarea.addEventListener('input', () => {
            const count = textarea.value.length;
            charCount.textContent = count;
            if (count > 500) {
                textarea.value = textarea.value.substring(0, 500);
                charCount.textContent = 500;
            }
        });

        document.getElementById('close-note-modal-btn').addEventListener('click', () => {
            this.closeNoteModal();
        });

        document.getElementById('save-note-btn').addEventListener('click', () => {
            this.saveNote();
        });

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                this.closeNoteModal();
            }
        });
    },

    createNotesPanel() {
        const panel = document.createElement('div');
        panel.id = 'notes-panel';
        panel.style.cssText = `
            position: fixed;
            top: 0;
            right: -350px;
            width: 350px;
            height: 100%;
            background: white;
            box-shadow: -2px 0 4px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease-out;
            z-index: 99998;
            display: flex;
            flex-direction: column;
        `;

        panel.innerHTML = `
            <div style="
                padding: 16px;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #f8f9fa;
            ">
                <h3 style="margin: 0; font-size: 16px; font-weight: 600; flex: 1;">📝 Your Notes</h3>
                <button id="close-notes-panel-btn" style="
                    background: none;
                    border: none;
                    font-size: 20px;
                    cursor: pointer;
                    color: #6b7280;
                    padding: 0;
                    width: 28px;
                    height: 28px;
                    border-radius: 4px;
                    transition: all 0.2s;
                " onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='none'">×</button>
            </div>
            <div id="notes-list" style="
                flex: 1;
                overflow-y: auto;
                padding: 12px;
            "></div>
        `;

        document.body.appendChild(panel);
        this.notesPanel = panel;

        document.getElementById('close-notes-panel-btn').addEventListener('click', () => {
            this.closeNotesPanel();
        });
    },

    closeNoteModal() {
        this.noteModal.style.display = 'none';
        document.getElementById('note-textarea').value = '';
        document.getElementById('char-count').textContent = '0';
    },

    saveNote() {
        const noteText = document.getElementById('note-textarea').value.trim();
        if (noteText && this.currentRange) {
            const selectedText = this.currentRange.toString();

            // Apply note styling
            const span = document.createElement('span');
            span.style.cssText = 'background-color: #fee2e2; color: #dc2626; border-bottom: 1px solid #dc2626; cursor: pointer; padding: 2px 4px; border-radius: 3px;';
            span.textContent = selectedText;
            span.title = noteText;
            span.dataset.note = noteText;
            span.dataset.noteId = Date.now();

            // Add click handler to show note
            span.onclick = () => this.showNoteTooltip(span, noteText);

            try {
                this.currentRange.deleteContents();
                this.currentRange.insertNode(span);
            } catch (error) {
                console.error('Error applying note:', error);
            }

            // Save to localStorage
            this.saveAnnotation('note', selectedText, noteText);

            this.closeNoteModal();
            window.getSelection().removeAllRanges();
            this.hideMenu();
        }
    },

    closeNotesPanel() {
        this.notesPanel.style.right = '-350px';
    },

    openNotesPanel() {
        this.notesPanel.style.right = '0';
        this.updateNotesList();
    },

    showNoteTooltip(element, noteText) {
        // Remove existing tooltip
        const existingTooltip = document.getElementById('note-tooltip');
        if (existingTooltip) existingTooltip.remove();

        const tooltip = document.createElement('div');
        tooltip.id = 'note-tooltip';
        tooltip.style.cssText = `
            position: absolute;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            max-width: 250px;
            z-index: 99999;
            font-size: 13px;
        `;

        tooltip.innerHTML = `
            <div style="color: #374151; margin-bottom: 6px;">${noteText}</div>
            <div style="font-size: 11px; color: #9ca3af;">Click outside to close</div>
        `;

        document.body.appendChild(tooltip);

        const rect = element.getBoundingClientRect();
        tooltip.style.top = `${rect.bottom + window.scrollY + 4}px`;
        tooltip.style.left = `${rect.left + window.scrollX}px`;

        // Remove on click outside
        setTimeout(() => {
            document.addEventListener('click', function removeTooltip(e) {
                if (!tooltip.contains(e.target) && e.target !== element) {
                    tooltip.remove();
                    document.removeEventListener('click', removeTooltip);
                }
            });
        }, 100);
    },

    updateNotesList() {
        const notesList = document.getElementById('notes-list');
        const attemptId = window.testConfig?.attemptId || 'test';
        const annotations = JSON.parse(localStorage.getItem(`annotations_${attemptId}`) || '[]');
        const notes = annotations.filter(a => a.type === 'note');

        if (notes.length === 0) {
            notesList.innerHTML = `
                <div style="text-align: center; color: #9ca3af; padding: 30px;">
                    <div style="font-size: 36px; margin-bottom: 12px;">📝</div>
                    <p style="font-size: 14px; margin-bottom: 6px;">No notes yet!</p>
                    <p style="font-size: 12px; margin-top: 6px;">Select text and add notes to see them here.</p>
                </div>
            `;
        } else {
            notesList.innerHTML = notes.map((note, index) => `
                <div class="note-item-wrapper" style="
                    background: #f9fafb;
                    border: 1px solid #e5e7eb;
                    border-radius: 6px;
                    padding: 12px;
                    margin-bottom: 10px;
                    position: relative;
                    transition: all 0.2s ease;
                " data-note-text="${encodeURIComponent(note.text)}" 
                   data-note-timestamp="${note.timestamp}">
                    <button class="delete-note-btn" style="
                        position: absolute;
                        top: 8px;
                        right: 8px;
                        background: #fee2e2;
                        border: none;
                        border-radius: 3px;
                        padding: 3px 6px;
                        cursor: pointer;
                        color: #dc2626;
                        font-size: 11px;
                        transition: all 0.2s;
                    " onmouseover="this.style.background='#fca5a5'" onmouseout="this.style.background='#fee2e2'">
                        Delete
                    </button>
                    <div style="
                        font-size: 12px;
                        color: #6b7280;
                        font-style: italic;
                        margin-bottom: 6px;
                        padding: 6px;
                        background: white;
                        border-radius: 3px;
                        margin-right: 50px;
                        border-left: 2px solid #3b82f6;
                    ">"${note.text.substring(0, 80)}${note.text.length > 80 ? '...' : ''}"</div>
                    <div style="font-size: 13px; color: #111827; line-height: 1.4; margin-bottom: 6px;">${note.data}</div>
                    <div style="
                        margin-top: 8px;
                        font-size: 11px;
                        color: #9ca3af;
                    ">📅 ${new Date(note.timestamp).toLocaleString()}</div>
                </div>
            `).join('');

            // Add delete event listeners after rendering
            notesList.querySelectorAll('.delete-note-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const wrapper = btn.closest('.note-item-wrapper');
                    const text = decodeURIComponent(wrapper.dataset.noteText);
                    const timestamp = wrapper.dataset.noteTimestamp;

                    if (confirm('Are you sure you want to delete this note?')) {
                        this.deleteNote(text, timestamp);
                    }
                });
            });
        }
    },

    setupAnnotationHandlers() {
        // Allow annotation in text areas including option text - but NOT in input controls
        const ALLOWED_SELECTORS = [
            '.passage-content',      // Passage text
            '.questions-section',    // Questions section
            '.question-text',        // Question text
            '.question-instruction', // Instructions
            '.question-instructions',// Instructions (alternate class)
            '.part-instruction',     // Part instructions
            '.question-item',        // Question items
            '.ielts-question-item',  // IELTS question items
            '.question-content',     // Question content area
            '.matching-question',    // Matching questions text
            '.form-label',           // Form labels
            '.question-group-header',// Group headers
            '.radio-option',         // Radio option TEXT (not the input)
            '.checkbox-option',      // Checkbox option TEXT (not the input)
            '.option-text',          // Option text
            '.option-label',         // Option labels
            '.option-item',          // Option items
            '.part-questions',       // Part questions container
            '.part-questions-inner', // Inner questions container
            // FIXED: Additional selectors for better highlighting coverage
            '.question-block',       // Question blocks
            '.question-wrapper',     // Question wrappers
            '.ielts-options',        // Options container (text only)
            '.ielts-q-number',       // Question numbers
            '.sentence-completion',  // Sentence completion
            '.fill-blanks-text',     // Fill in blanks text
            '.true-false-question',  // True/False questions
            '.matching-headings',    // Matching headings
            '.dropdown-selection-question', // Dropdown questions
            '.single-choice-question',      // Single choice
            '.multiple-choice-question',    // Multiple choice
            'p',                     // All paragraphs
            'span',                  // All spans (text content)
            'li',                    // List items
            'td',                    // Table cells
            'th'                     // Table headers
        ];
        
        // ONLY block actual input controls - NOT their text content
        const FORBIDDEN_SELECTORS = [
            'input[type="radio"]',   // The radio button itself
            'input[type="checkbox"]',// The checkbox itself
            'input[type="text"]',    // Text input fields
            'select',                // Dropdown menus
            'textarea',              // Text areas
            'button',                // Buttons
            '.drop-box',             // Drop zones
            '.draggable-option',     // Draggable items
            '.number-btn',           // Navigation buttons
            '.answer-input',         // Answer input areas
            '.passage-answer-input'  // Passage answer inputs ⭐ NEW
        ];
        
        // Text selection handler - Works on passage AND question text areas
        document.addEventListener('mouseup', (e) => {
            // Skip if clicking on annotation menu or modal
            if (e.target.closest('#annotation-menu') ||
                e.target.closest('#note-modal') ||
                e.target.closest('#notes-panel')) {
                return;
            }

            // Skip right clicks
            if (e.button === 2) {
                return;
            }

            setTimeout(() => {
                const selection = window.getSelection();
                const selectedText = selection.toString().trim();

                if (selectedText && selectedText.length >= 3) {
                    const range = selection.getRangeAt(0);
                    const container = range.commonAncestorContainer;
                    const element = container.nodeType === 3 ? container.parentElement : container;

                    // Check if selection is DIRECTLY on a forbidden element (actual input/button)
                    const isForbidden = FORBIDDEN_SELECTORS.some(selector => {
                        try {
                            return element.matches(selector) || element.closest(selector) !== null;
                        } catch (e) {
                            return false;
                        }
                    });

                    if (isForbidden) {
                        this.hideMenu();
                        return;
                    }

                    // IMPROVED: Allow highlighting on ANY text content
                    // Only check if we're in the main test area (not in modals, menus, etc.)
                    const isInTestArea = element.closest('.test-container') ||
                                         element.closest('.passage-content') ||
                                         element.closest('.questions-section') ||
                                         element.closest('.part-questions') ||
                                         element.closest('.reading-test-container') ||
                                         element.closest('main') ||
                                         element.closest('.question-item') ||
                                         element.closest('.question-text') ||
                                         element.closest('.ielts-question-item');

                    // Also check allowed selectors for broader coverage
                    const isAllowed = isInTestArea || ALLOWED_SELECTORS.some(selector => {
                        try {
                            return element.closest(selector) !== null;
                        } catch (e) {
                            return false;
                        }
                    });

                    if (!isAllowed) {
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

        // ESC key to exit fullscreen
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && document.fullscreenElement) {
                const fullscreenBtn = document.getElementById('fullscreen-btn');
                if (fullscreenBtn) {
                    fullscreenBtn.click();
                }
            }
        });
    },

    showMenu(rect, selectedText) {
        this.hideMenu();

        const menu = document.createElement('div');
        menu.id = 'annotation-menu';
        menu.style.cssText = `
            position: fixed;
            top: ${rect.top - 50}px;
            left: ${rect.left + (rect.width / 2) - 80}px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 6px;
            display: flex;
            gap: 6px;
            z-index: 99999;
        `;

        // Note button
        const noteBtn = document.createElement('button');
        noteBtn.innerHTML = `
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Note
        `;
        noteBtn.style.cssText = `
            padding: 6px 12px;
            border: none;
            background: #3b82f6;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        `;

        noteBtn.onmouseover = () => { noteBtn.style.background = '#2563eb'; };
        noteBtn.onmouseout = () => { noteBtn.style.background = '#3b82f6'; };

        // FIXED: Prevent mousedown from clearing selection
        noteBtn.onmousedown = (e) => {
            e.preventDefault();
            e.stopPropagation();
        };

        noteBtn.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('selected-text-preview').textContent =
                `"${selectedText.substring(0, 40)}${selectedText.length > 40 ? '...' : ''}"`;
            this.noteModal.style.display = 'flex';
            setTimeout(() => {
                document.getElementById('note-textarea').focus();
            }, 100);
        };

        // Highlight button - SIMPLIFIED (no color picker)
        const highlightBtn = document.createElement('button');
        highlightBtn.innerHTML = `
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Highlight
        `;
        highlightBtn.style.cssText = `
            padding: 6px 12px;
            border: none;
            background: #f59e0b;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        `;

        highlightBtn.onmouseover = () => { highlightBtn.style.background = '#d97706'; };
        highlightBtn.onmouseout = () => { highlightBtn.style.background = '#f59e0b'; };

        // FIXED: Prevent mousedown from clearing selection
        highlightBtn.onmousedown = (e) => {
            e.preventDefault();
            e.stopPropagation();
        };

        highlightBtn.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            // DIRECT YELLOW HIGHLIGHT - NO COLOR PICKER
            this.applyHighlight({ name: 'Yellow', value: '#fde047' });
        };

        // Copy Text button
        const copyBtn = document.createElement('button');
        copyBtn.innerHTML = `
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            Copy
        `;
        copyBtn.style.cssText = `
            padding: 6px 12px;
            border: none;
            background: #10b981;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        `;

        copyBtn.onmouseover = () => { copyBtn.style.background = '#059669'; };
        copyBtn.onmouseout = () => { copyBtn.style.background = '#10b981'; };

        // FIXED: Prevent mousedown from clearing selection
        copyBtn.onmousedown = (e) => {
            e.preventDefault();
            e.stopPropagation();
        };

        copyBtn.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Copy text to clipboard
            navigator.clipboard.writeText(selectedText).then(() => {
                // Show success feedback
                const originalText = copyBtn.innerHTML;
                copyBtn.innerHTML = `
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Copied!
                `;
                copyBtn.style.background = '#059669';
                
                setTimeout(() => {
                    copyBtn.innerHTML = originalText;
                    copyBtn.style.background = '#10b981';
                    this.hideMenu();
                }, 1000);
            }).catch(err => {
                console.error('Failed to copy text:', err);
                // Fallback method
                const textarea = document.createElement('textarea');
                textarea.value = selectedText;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                
                copyBtn.innerHTML = `
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Copied!
                `;
                setTimeout(() => {
                    this.hideMenu();
                }, 1000);
            });
        };

        menu.appendChild(noteBtn);
        menu.appendChild(highlightBtn);
        menu.appendChild(copyBtn);
        document.body.appendChild(menu);
        this.currentMenu = menu;
    },

    applyHighlight(color) {
        if (this.currentRange) {
            // FIXED: Preserve original text including whitespace
            const originalText = this.currentRange.toString();
            const trimmedText = originalText.trim();

            // Don't highlight if empty or just spaces
            if (!trimmedText) {
                this.hideMenu();
                return;
            }

            // FIXED: Check if selection spans multiple block elements (prevents merging options)
            const startContainer = this.currentRange.startContainer;
            const endContainer = this.currentRange.endContainer;
            const startElement = startContainer.nodeType === 3 ? startContainer.parentElement : startContainer;
            const endElement = endContainer.nodeType === 3 ? endContainer.parentElement : endContainer;

            // Find the nearest block-level parent for both start and end
            // STRICT: Prioritize option classes to prevent multi-option selection
            const blockClasses = [
                // Option-level classes (highest priority - each option is a block)
                'ielts-option', 'option-item', 'radio-option', 'checkbox-option',
                'single-choice-option', 'multiple-choice-option',
                'matching-option', 'draggable-option', 'answer-option',
                // Container classes
                'drop-box', 'question-item', 'form-completion-row',
                'part-question', 'reading-question', 'answer-row', 'option-label',
                'form-group', 'input-group', 'question-block',
                'heading-item', 'mh-heading-item', 'passage-drop-zone',
                // Additional for safety
                'ielts-options', 'options-list', 'question-content'
            ];
            const blockElements = ['LI', 'TD', 'TH', 'TR'];

            const findBlockParent = (el) => {
                while (el && el.parentElement) {
                    // First check classes (prioritize option boundaries)
                    if (el.classList && blockClasses.some(cls => el.classList.contains(cls))) {
                        return el;
                    }
                    // Then check element types (but NOT DIV/P/LABEL to avoid false positives)
                    if (blockElements.includes(el.tagName)) {
                        return el;
                    }
                    el = el.parentElement;
                }
                return null;
            };

            const startBlock = findBlockParent(startElement);
            const endBlock = findBlockParent(endElement);

            // If selection spans different block elements, silently prevent (no warning)
            if (startBlock && endBlock && startBlock !== endBlock) {
                this.hideMenu();
                window.getSelection().removeAllRanges();
                return;
            }

            // STRICT: Adjust range to exclude leading/trailing whitespace
            const adjustRangeToTrimmedText = (range) => {
                const text = range.toString();
                const leadingSpaces = text.match(/^\s*/)[0].length;
                const trailingSpaces = text.match(/\s*$/)[0].length;

                if (leadingSpaces > 0) {
                    try {
                        range.setStart(range.startContainer, range.startOffset + leadingSpaces);
                    } catch (e) {
                        // If adjustment fails, continue with original
                    }
                }
                if (trailingSpaces > 0 && trailingSpaces < text.length) {
                    try {
                        range.setEnd(range.endContainer, range.endOffset - trailingSpaces);
                    } catch (e) {
                        // If adjustment fails, continue with original
                    }
                }
                return range;
            };

            // Adjust range to exclude spaces
            adjustRangeToTrimmedText(this.currentRange);

            // Create highlight span
            const span = document.createElement('span');
            span.style.backgroundColor = color.value;
            span.style.cursor = 'pointer';
            span.style.borderRadius = '2px';
            span.dataset.highlight = color.name;
            span.title = 'Click to remove';

            // Store text for removal
            const textForRemoval = trimmedText;

            // DIRECT removal without confirmation
            span.onclick = function (evt) {
                evt.stopPropagation();
                // Replace with original contents (preserves exact text)
                const parent = this.parentNode;
                while (this.firstChild) {
                    parent.insertBefore(this.firstChild, this);
                }
                parent.removeChild(this);
                parent.normalize(); // Merge adjacent text nodes
                SimpleAnnotationSystem.removeAnnotation('highlight', textForRemoval);
            };

            try {
                // Use surroundContents to wrap selection
                this.currentRange.surroundContents(span);
                this.saveAnnotation('highlight', trimmedText, color.name);
            } catch (error) {
                // Fallback: Extract and insert if surroundContents fails
                try {
                    const contents = this.currentRange.extractContents();
                    span.appendChild(contents);
                    this.currentRange.insertNode(span);
                    this.saveAnnotation('highlight', trimmedText, color.name);
                } catch (e) {
                    console.error('Error applying highlight:', e);
                }
            }

            this.hideMenu();
            window.getSelection().removeAllRanges();
        }
    },

    removeAnnotation(type, text) {
        const attemptId = window.testConfig?.attemptId || 'test';
        const key = `annotations_${attemptId}`;
        let annotations = JSON.parse(localStorage.getItem(key) || '[]');

        annotations = annotations.filter(a => !(a.type === type && a.text === text));
        localStorage.setItem(key, JSON.stringify(annotations));
    },

    hideMenu() {
        if (this.currentMenu) {
            this.currentMenu.remove();
            this.currentMenu = null;
        }
    },

    showContextMenu(x, y, selectedText) {
        this.hideMenu();

        const menu = document.createElement('div');
        menu.id = 'annotation-menu';
        menu.style.cssText = `
            position: fixed;
            top: ${y}px;
            left: ${x}px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 4px;
            z-index: 99999;
            min-width: 150px;
        `;

        // If text is selected, show both options
        if (selectedText) {
            // Note option
            const noteOption = document.createElement('button');
            noteOption.innerHTML = `
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Add Note
            `;
            noteOption.style.cssText = `
                display: flex;
                align-items: center;
                width: 100%;
                padding: 8px 12px;
                border: none;
                background: white;
                color: #374151;
                font-size: 13px;
                cursor: pointer;
                transition: all 0.2s;
                text-align: left;
                border-radius: 4px;
            `;
            
            noteOption.onmouseover = () => { noteOption.style.background = '#f3f4f6'; };
            noteOption.onmouseout = () => { noteOption.style.background = 'white'; };
            
            noteOption.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                document.getElementById('selected-text-preview').textContent =
                    `"${selectedText.substring(0, 40)}${selectedText.length > 40 ? '...' : ''}"`;;
                this.noteModal.style.display = 'flex';
                setTimeout(() => {
                    document.getElementById('note-textarea').focus();
                }, 100);
                this.hideMenu();
            };

            // Highlight option
            const highlightOption = document.createElement('button');
            highlightOption.innerHTML = `
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Highlight
            `;
            highlightOption.style.cssText = `
                display: flex;
                align-items: center;
                width: 100%;
                padding: 8px 12px;
                border: none;
                background: white;
                color: #374151;
                font-size: 13px;
                cursor: pointer;
                transition: all 0.2s;
                text-align: left;
                border-radius: 4px;
            `;
            
            highlightOption.onmouseover = () => { highlightOption.style.background = '#f3f4f6'; };
            highlightOption.onmouseout = () => { highlightOption.style.background = 'white'; };
            
            highlightOption.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.applyHighlight({ name: 'Yellow', value: '#fde047' });
                this.hideMenu();
            };

            menu.appendChild(noteOption);
            menu.appendChild(highlightOption);
        } else {
            // No text selected - show instruction
            const instruction = document.createElement('div');
            instruction.innerHTML = `
                <div style="padding: 12px; color: #6b7280; font-size: 13px; text-align: center;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: block; margin: 0 auto 8px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Select text first
                </div>
            `;
            menu.appendChild(instruction);
        }

        // Add separator line
        if (selectedText) {
            const separator = document.createElement('div');
            separator.style.cssText = 'height: 1px; background: #e5e7eb; margin: 4px 0;';
            menu.appendChild(separator);
        }

        // View Notes option (always show)
        const viewNotesOption = document.createElement('button');
        viewNotesOption.innerHTML = `
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            View All Notes
        `;
        viewNotesOption.style.cssText = `
            display: flex;
            align-items: center;
            width: 100%;
            padding: 8px 12px;
            border: none;
            background: white;
            color: #374151;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
            border-radius: 4px;
        `;
        
        viewNotesOption.onmouseover = () => { viewNotesOption.style.background = '#f3f4f6'; };
        viewNotesOption.onmouseout = () => { viewNotesOption.style.background = 'white'; };
        
        viewNotesOption.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.openNotesPanel();
            this.hideMenu();
        };

        menu.appendChild(viewNotesOption);
        document.body.appendChild(menu);
        this.currentMenu = menu;

        // Adjust position if menu goes off screen
        const rect = menu.getBoundingClientRect();
        if (rect.right > window.innerWidth) {
            menu.style.left = `${window.innerWidth - rect.width - 10}px`;
        }
        if (rect.bottom > window.innerHeight) {
            menu.style.top = `${window.innerHeight - rect.height - 10}px`;
        }
    },

    saveAnnotation(type, text, data) {
        const attemptId = window.testConfig?.attemptId || 'test';
        const key = `annotations_${attemptId}`;
        const annotations = JSON.parse(localStorage.getItem(key) || '[]');

        annotations.push({
            type: type,
            text: text,
            data: data,
            timestamp: new Date().toISOString()
        });

        localStorage.setItem(key, JSON.stringify(annotations));
    },

    deleteNote(text, timestamp) {
        const attemptId = window.testConfig?.attemptId || 'test';
        const key = `annotations_${attemptId}`;
        let annotations = JSON.parse(localStorage.getItem(key) || '[]');

        // Remove the specific note
        annotations = annotations.filter(a => !(a.type === 'note' && a.text === text && a.timestamp === timestamp));
        localStorage.setItem(key, JSON.stringify(annotations));

        // Remove from DOM
        const noteElements = document.querySelectorAll('span[data-note]');
        noteElements.forEach(el => {
            if (el.textContent === text) {
                const parent = el.parentNode;
                parent.replaceChild(document.createTextNode(text), el);
            }
        });

        // Update notes list
        this.updateNotesList();
    },

    addNotesButton() {
        const navRight = document.querySelector('.nav-right');
        if (navRight && !document.getElementById('view-notes-btn')) {
            // Notes button
            const notesBtn = document.createElement('button');
            notesBtn.id = 'view-notes-btn';
            notesBtn.innerHTML = `
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Notes
                <span id="notes-count" style="
                    display: none;
                    background: #ef4444;
                    color: white;
                    font-size: 10px;
                    padding: 1px 5px;
                    border-radius: 8px;
                    margin-left: 4px;
                    min-width: 16px;
                    text-align: center;
                ">0</span>
            `;
            notesBtn.style.cssText = `
                padding: 6px 12px;
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 4px;
                cursor: pointer;
                margin-right: 10px;
                font-size: 13px;
                display: flex;
                align-items: center;
                transition: all 0.2s;
            `;
            notesBtn.onmouseover = () => {
                notesBtn.style.borderColor = '#3b82f6';
                notesBtn.style.color = '#3b82f6';
            };
            notesBtn.onmouseout = () => {
                notesBtn.style.borderColor = '#e5e7eb';
                notesBtn.style.color = '';
            };
            notesBtn.onclick = () => this.openNotesPanel();

            // Full Screen button
            const fullscreenBtn = document.createElement('button');
            fullscreenBtn.id = 'fullscreen-btn';
            fullscreenBtn.innerHTML = `
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                </svg>
                Full Screen
            `;
            fullscreenBtn.style.cssText = `
                padding: 6px 12px;
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 4px;
                cursor: pointer;
                margin-right: 10px;
                font-size: 13px;
                display: flex;
                align-items: center;
                transition: all 0.2s;
            `;
            fullscreenBtn.onmouseover = () => {
                fullscreenBtn.style.borderColor = '#3b82f6';
                fullscreenBtn.style.color = '#3b82f6';
            };
            fullscreenBtn.onmouseout = () => {
                fullscreenBtn.style.borderColor = '#e5e7eb';
                fullscreenBtn.style.color = '';
            };
            fullscreenBtn.onclick = () => this.toggleFullScreen(fullscreenBtn);

            const submitBtn = navRight.querySelector('.submit-test-button');
            navRight.insertBefore(notesBtn, submitBtn);
            navRight.insertBefore(fullscreenBtn, submitBtn);

            // Update notes count
            this.updateNotesCount();
        }
    },

    toggleFullScreen(button) {
        if (!document.fullscreenElement) {
            // Enter fullscreen
            document.documentElement.requestFullscreen().then(() => {
                button.innerHTML = `
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V5m0 0h4m-4 0l5 5m-5 6v4m0 0h4m-4 0l5-5m-6-5l-5 5m5-5v4m0-4H5m10 6l5 5m0 0v-4m0 4h-4"></path>
                    </svg>
                    Exit Full Screen
                `;
                button.style.background = '#3b82f6';
                button.style.color = 'white';
                button.style.borderColor = '#3b82f6';
                
                // Add fullscreen class to body
                document.body.classList.add('fullscreen-mode');
            }).catch(err => {
                console.error('Error entering fullscreen:', err);
            });
        } else {
            // Exit fullscreen
            document.exitFullscreen().then(() => {
                button.innerHTML = `
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    </svg>
                    Full Screen
                `;
                button.style.background = 'white';
                button.style.color = '';
                button.style.borderColor = '#e5e7eb';
                
                // Remove fullscreen class from body
                document.body.classList.remove('fullscreen-mode');
            });
        }
    },

    updateNotesCount() {
        const attemptId = window.testConfig?.attemptId || 'test';
        const annotations = JSON.parse(localStorage.getItem(`annotations_${attemptId}`) || '[]');
        const notesCount = annotations.filter(a => a.type === 'note').length;

        const countElement = document.getElementById('notes-count');
        if (countElement) {
            countElement.textContent = notesCount;
            countElement.style.display = notesCount > 0 ? 'inline-block' : 'none';
        }
    },

    restoreAnnotations() {
        const attemptId = window.testConfig?.attemptId || 'test';
        const annotations = JSON.parse(localStorage.getItem(`annotations_${attemptId}`) || '[]');

        annotations.forEach(annotation => {
            if (annotation.type === 'note') {
                this.findAndStyleText(annotation.text, (span) => {
                    span.style.cssText = 'background-color: #fee2e2; color: #dc2626; border-bottom: 1px solid #dc2626; cursor: pointer; padding: 2px 4px; border-radius: 3px;';
                    span.dataset.note = annotation.data;
                    span.dataset.noteId = Date.now();
                    span.onclick = () => this.showNoteTooltip(span, annotation.data);
                });
            } else if (annotation.type === 'highlight') {
                const colors = {
                    'Yellow': '#fde047',
                    'Green': '#d1fae5',
                    'Blue': '#dbeafe',
                    'Pink': '#fce7f3'
                };
                this.findAndStyleText(annotation.text, (span) => {
                    span.style.backgroundColor = colors[annotation.data] || '#fde047';
                    span.style.cursor = 'pointer';
                    span.style.borderRadius = '2px';
                    span.title = `${annotation.data} highlight - Click to remove`;
                    span.onclick = function (evt) {
                        evt.stopPropagation();
                        if (confirm('Remove this highlight?')) {
                            const text = this.textContent;
                            this.style.transition = 'background-color 0.3s ease';
                            this.style.backgroundColor = 'transparent';
                            setTimeout(() => {
                                this.replaceWith(document.createTextNode(text));
                                SimpleAnnotationSystem.removeAnnotation('highlight', text);
                            }, 300);
                        }
                    };
                });
            }
        });

        this.updateNotesCount();
    },

    findAndStyleText(searchText, styleCallback) {
        // Search in both passages AND questions
        const containers = document.querySelectorAll('.passage-content, .questions-section');

        containers.forEach(container => {
            const walker = document.createTreeWalker(
                container,
                NodeFilter.SHOW_TEXT,
                null,
                false
            );

            let node;
            while (node = walker.nextNode()) {
                const text = node.textContent;
                const index = text.indexOf(searchText);

                if (index !== -1) {
                    const span = document.createElement('span');
                    const parent = node.parentNode;

                    // Split the text node
                    const before = document.createTextNode(text.substring(0, index));
                    const after = document.createTextNode(text.substring(index + searchText.length));

                    // Apply styling
                    span.textContent = searchText;
                    styleCallback(span);

                    // Replace in DOM
                    parent.insertBefore(before, node);
                    parent.insertBefore(span, node);
                    parent.insertBefore(after, node);
                    parent.removeChild(node);

                    break;
                }
            }
        });
    }
};

// ========== Initialize Everything ==========
document.addEventListener('DOMContentLoaded', function () {
    console.log('🚀 Initializing Simplified Reading Test System...');
    console.log('Attempt ID:', window.testConfig?.attemptId);
    
    // Log all select elements for debugging
    console.log('Total select elements found:', document.querySelectorAll('select').length);
    document.querySelectorAll('select[name^="answers["]').forEach(select => {
        console.log('Select found:', select.name, 'Options:', select.options.length);
    });
    
    // Force IELTS styles
    const forceIELTSStyles = () => {
        // Fix all radio buttons
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.style.cssText = '-webkit-appearance: radio !important; -moz-appearance: radio !important; appearance: radio !important; margin: 0 !important; margin-right: 8px !important; width: 14px !important; height: 14px !important; cursor: pointer !important; padding: 0 !important;';
        });
        
        // Fix all question numbers
        document.querySelectorAll('.ielts-q-number').forEach(el => {
            el.style.cssText = 'font-weight: 700 !important; font-size: 14px !important; color: #000000 !important; line-height: 1.5 !important; margin-bottom: 10px !important; display: block !important; padding: 0 !important; background: none !important; border: none !important;';
        });
        
        // Fix all options
        document.querySelectorAll('.ielts-option').forEach(el => {
            el.style.cssText = 'margin-bottom: 6px !important; display: flex !important; align-items: center !important; padding: 0 !important; background: none !important; border: none !important;';
        });
        
        // Fix all labels
        document.querySelectorAll('.ielts-option label').forEach(el => {
            el.style.cssText = 'cursor: pointer !important; font-size: 14px !important; color: #000000 !important; font-weight: normal !important; margin: 0 !important; padding: 0 !important; line-height: 1.4 !important;';
        });
        
        // Fix all dropdowns
        document.querySelectorAll('.ielts-options select').forEach(select => {
            select.style.cssText = 'width: 100% !important; max-width: 300px !important; padding: 8px 12px !important; border: 1px solid #d1d5db !important; border-radius: 4px !important; font-size: 14px !important; background-color: white !important; color: #000000 !important; cursor: pointer !important; appearance: auto !important; -webkit-appearance: menulist !important; -moz-appearance: menulist !important; background-image: none !important; line-height: 1.5 !important; height: auto !important;';
        });
    };
    
    // Apply immediately and after delay
    forceIELTSStyles();
    setTimeout(forceIELTSStyles, 100);
    setTimeout(forceIELTSStyles, 500);
    setTimeout(forceIELTSStyles, 1000);

    try {
        // Initialize all modules
        NavigationHandler.init();
        AnswerManager.init(window.testConfig?.attemptId || 'test');
        SubmitHandler.init();
        HelpGuide.init();
        SimpleSplitDivider.init();
        SimpleAnnotationSystem.init();

        // Make split divider globally available
        window.SimpleSplitDivider = SimpleSplitDivider;

        // Restore annotations after a delay to ensure DOM is ready
        setTimeout(() => {
            SimpleAnnotationSystem.restoreAnnotations();
        }, 1000);

        // Initialize part-based navigation display
        const firstActivePart = document.querySelector('.part-btn.active');
        if (firstActivePart) {
            NavigationHandler.updateQuestionNumbersDisplay(firstActivePart.dataset.part);
        }

        console.log('✅ Simplified Reading Test System Ready!');

    } catch (error) {
        console.error('❌ Error initializing Reading Test System:', error);
    }
});

// ========== Handle Window Events ==========
window.addEventListener('resize', () => {
    if (window.innerWidth <= 1024) {
        if (window.SimpleSplitDivider) {
            SimpleSplitDivider.destroy();
        }
    } else if (window.SimpleSplitDivider && !document.getElementById('split-divider')) {
        SimpleSplitDivider.init();
    }
});

// Cleanup all intervals when page unloads
window.addEventListener('unload', () => {
    console.log('Page unloading - cleaning up...');
    if (window.AnswerManager) {
        AnswerManager.cleanup();
    }
    if (SubmitHandler.submitTimeoutId) {
        clearTimeout(SubmitHandler.submitTimeoutId);
    }
});

// ========== Export for external use ==========
window.NavigationHandler = NavigationHandler;
window.AnswerManager = AnswerManager;
window.SubmitHandler = SubmitHandler;
window.HelpGuide = HelpGuide;
window.SimpleAnnotationSystem = SimpleAnnotationSystem;