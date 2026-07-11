// Multiple Choice Handler for IELTS Tests
// NOTE: Checkbox change handling is now in reading-test.js AnswerManager
// This file only handles: navigation clicks, saveAllAnswers override, loadSavedAnswers override
document.addEventListener('DOMContentLoaded', function() {
    console.log('Multiple Choice Handler initializing (navigation & save/load only)...');

    // Handle navigation for multiple choice sub-questions
    document.querySelectorAll('.number-btn[data-sub-question]').forEach(button => {
        button.addEventListener('click', function() {
            const questionId = this.dataset.question;
            const subQuestion = parseInt(this.dataset.subQuestion);
            const questionElement = document.getElementById(`question-${questionId}`);
            
            if (questionElement) {
                // Smooth scroll to question
                questionElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                
                // Focus on the checkbox corresponding to this sub-question
                const checkboxes = questionElement.querySelectorAll('.multiple-choice-checkbox');
                if (checkboxes[subQuestion]) {
                    setTimeout(() => {
                        checkboxes[subQuestion].focus();
                        // Add visual highlight
                        checkboxes[subQuestion].parentElement.style.backgroundColor = '#fef3c7';
                        setTimeout(() => {
                            checkboxes[subQuestion].parentElement.style.backgroundColor = '';
                        }, 2000);
                    }, 300);
                }
            }
        });
    });
    
    // Override the answer tracking for multiple choice questions
    if (window.AnswerManager && window.AnswerManager.setupAnswerTracking) {
        const originalSetup = window.AnswerManager.setupAnswerTracking;
        
        window.AnswerManager.setupAnswerTracking = function() {
            // Call original setup
            originalSetup.call(this);
            
            // Add specific handling for checkboxes
            document.querySelectorAll('input[type="checkbox"][name*="answers"]').forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    console.log('Checkbox changed in answer tracking:', checkbox.name, checkbox.checked);
                    this.trackAnswer(checkbox);
                });
            });
        };
    }
    
    // Override saveAllAnswers to properly handle checkbox arrays
    if (window.AnswerManager && window.AnswerManager.saveAllAnswers) {
        const originalSave = window.AnswerManager.saveAllAnswers;
        
        window.AnswerManager.saveAllAnswers = function() {
            const form = document.getElementById('reading-form');
            if (!form) return;
            
            const formData = new FormData(form);
            const answers = {};
            
            console.log('Saving all answers (with multiple choice support)...');
            
            // First, handle regular answers
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('answers[') && value) {
                    // Check if this is an array (checkbox) answer
                    if (key.endsWith('[]')) {
                        // Initialize array if not exists
                        if (!answers[key]) {
                            answers[key] = [];
                        }
                        answers[key].push(value);
                    } else {
                        answers[key] = value;
                    }
                    console.log('Saved:', key, '=', value);
                }
            }
            
            // Also check for checked checkboxes that might not be in FormData
            document.querySelectorAll('input[type="checkbox"][name*="answers"]:checked').forEach(checkbox => {
                const key = checkbox.name;
                const value = checkbox.value;
                
                if (!answers[key]) {
                    answers[key] = [];
                }
                
                if (!answers[key].includes(value)) {
                    answers[key].push(value);
                }
                
                console.log('Checkbox saved:', key, '=', value);
            });
            
            try {
                localStorage.setItem(`testAnswers_${this.attemptId}`, JSON.stringify(answers));
                console.log('Total answers saved:', Object.keys(answers).length);
                console.log('Saved answers object:', answers);
            } catch (e) {
                console.warn('Could not save answers:', e);
            }
        };
    }
    
    // Override loadSavedAnswers to properly restore checkbox arrays
    if (window.AnswerManager && window.AnswerManager.loadSavedAnswers) {
        const originalLoad = window.AnswerManager.loadSavedAnswers;
        
        window.AnswerManager.loadSavedAnswers = function() {
            try {
                const savedAnswers = localStorage.getItem(`testAnswers_${this.attemptId}`);
                
                if (savedAnswers) {
                    const answers = JSON.parse(savedAnswers);
                    
                    Object.keys(answers).forEach(key => {
                        const value = answers[key];
                        
                        // Handle checkbox arrays
                        if (Array.isArray(value) && key.endsWith('[]')) {
                            // Uncheck all first
                            document.querySelectorAll(`[name="${key}"]`).forEach(checkbox => {
                                checkbox.checked = false;
                            });
                            
                            // Check the saved ones
                            value.forEach(val => {
                                const checkbox = document.querySelector(`[name="${key}"][value="${val}"]`);
                                if (checkbox) {
                                    checkbox.checked = true;
                                    this.trackAnswer(checkbox);
                                }
                            });
                            
                            // Update navigation buttons for multiple choice
                            const questionMatch = key.match(/answers\[(\d+)\]/);
                            if (questionMatch) {
                                const questionId = questionMatch[1];
                                // Get base number and correct count from the first checkbox
                                const firstCheckbox = document.querySelector(`input[name="${key}"]`);
                                if (firstCheckbox) {
                                    const baseNum = parseInt(firstCheckbox.dataset.questionNumber);
                                    const correctCount = parseInt(firstCheckbox.dataset.correctCount) || 1;
                                    const checkedCount = value.length;

                                    // Mark nav buttons sequentially
                                    for (let i = 0; i < correctCount; i++) {
                                        const navBtn = document.querySelector(`.number-btn[data-display-number="${baseNum + i}"]`);
                                        if (navBtn) {
                                            if (i < checkedCount) {
                                                navBtn.classList.add('answered');
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            // Handle regular inputs
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
                        }
                    });
                }
            } catch (e) {
                console.error('Error restoring saved answers:', e);
            }
        };
    }
    
    console.log('Multiple Choice Handler initialized successfully');
});
