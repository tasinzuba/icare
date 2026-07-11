// Enhanced Matching Headings Fix for Reading Test - Version 5
document.addEventListener('DOMContentLoaded', function() {
    console.log('Enhanced Matching Headings Fix V5 loaded');
    
    // Find all matching headings selects (including both old and new patterns)
    const matchingSelects = document.querySelectorAll(
        'select[name*="_para_"], select[name*="_q"], select.matching-heading-select, ' +
        'select[data-question-number][name^="answers["]'
    );
    
    if (matchingSelects.length > 0) {
        console.log(`Found ${matchingSelects.length} matching headings dropdowns`);
        
        // Enhance dropdown styling and functionality
        matchingSelects.forEach((select, index) => {
            console.log(`Processing dropdown ${index}:`, {
                name: select.name,
                id: select.id,
                questionNumber: select.getAttribute('data-question-number')
            });
            
            // Ensure dropdown is visible and properly styled
            select.style.display = 'inline-block';
            select.style.visibility = 'visible';
            select.style.opacity = '1';
            
            // Add enhanced styling
            if (!select.classList.contains('enhanced-dropdown')) {
                select.classList.add('enhanced-dropdown');
                
                // Apply base styling
                select.style.padding = '5px 8px';
                select.style.border = '1px solid #cbd5e1';
                select.style.borderRadius = '4px';
                select.style.backgroundColor = '#ffffff';
                select.style.fontSize = '14px';
                select.style.minWidth = '80px';
                select.style.cursor = 'pointer';
                select.style.transition = 'all 0.2s ease';
            }
            
            // Add change event listener
            select.addEventListener('change', function() {
                console.log(`Matching heading changed: ${this.name} = ${this.value}`);
                
                // Update dropdown styling based on selection
                if (this.value) {
                    this.style.backgroundColor = '#d1fae5';
                    this.style.borderColor = '#10b981';
                    this.style.color = '#065f46';
                    this.style.fontWeight = '600';
                    this.setAttribute('data-answered', 'true');
                } else {
                    this.style.backgroundColor = '#ffffff';
                    this.style.borderColor = '#cbd5e1';
                    this.style.color = '#374151';
                    this.style.fontWeight = '500';
                    this.removeAttribute('data-answered');
                }
                
                // Update navigation button state
                const questionNumber = this.getAttribute('data-question-number');
                if (questionNumber) {
                    const navBtn = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
                    if (navBtn) {
                        if (this.value) {
                            navBtn.classList.add('answered');
                        } else {
                            navBtn.classList.remove('answered');
                        }
                    }
                }
                
                // Update answered count
                updateAnsweredCount();
            });
            
            // Add hover effects
            select.addEventListener('mouseenter', function() {
                if (!this.hasAttribute('data-answered')) {
                    this.style.backgroundColor = '#f8fafc';
                    this.style.borderColor = '#94a3b8';
                }
            });
            
            select.addEventListener('mouseleave', function() {
                if (!this.hasAttribute('data-answered')) {
                    this.style.backgroundColor = '#ffffff';
                    this.style.borderColor = '#cbd5e1';
                } else {
                    this.style.backgroundColor = '#d1fae5';
                    this.style.borderColor = '#10b981';
                }
            });
            
            // Add focus effects
            select.addEventListener('focus', function() {
                this.style.outline = 'none';
                this.style.borderColor = '#3b82f6';
                this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
            });
            
            select.addEventListener('blur', function() {
                this.style.boxShadow = '';
                if (this.hasAttribute('data-answered')) {
                    this.style.borderColor = '#10b981';
                } else {
                    this.style.borderColor = '#cbd5e1';
                }
            });
        });
    }
    
    // Enhanced navigation button click handlers
    document.querySelectorAll('.number-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const questionId = this.getAttribute('data-question');
            const subQuestion = this.getAttribute('data-sub-question');
            const displayNumber = this.getAttribute('data-display-number');
            
            if (subQuestion !== null || displayNumber) {
                // Look for matching heading dropdown
                const targetSelect = document.querySelector(
                    `select[data-question-number="${displayNumber}"], ` +
                    `select[name="answers[${questionId}_q${displayNumber}]"], ` +
                    `select[name="answers[${questionId}]"][data-question-number="${displayNumber}"]`
                );
                
                if (targetSelect) {
                    // Scroll to the dropdown
                    targetSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Highlight the dropdown
                    targetSelect.style.boxShadow = '0 0 0 3px rgba(245, 158, 11, 0.5)';
                    setTimeout(() => {
                        targetSelect.style.boxShadow = '';
                    }, 2000);
                    
                    // Focus on the dropdown
                    setTimeout(() => {
                        targetSelect.focus();
                    }, 300);
                }
            }
        });
    });
    
    // Function to count unique answered questions
    function updateAnsweredCount() {
        const answeredQuestions = new Set();
        
        // Count all answered regular inputs
        const allInputs = document.querySelectorAll('input[name^="answers["], select[name^="answers["]');
        
        allInputs.forEach(input => {
            const questionNumber = input.getAttribute('data-question-number');
            
            if (input.type === 'radio' || input.type === 'checkbox') {
                if (input.checked && questionNumber) {
                    answeredQuestions.add(questionNumber);
                }
            } else if (input.value && input.value.trim() !== '' && questionNumber) {
                answeredQuestions.add(questionNumber);
            }
        });
        
        const totalAnswered = answeredQuestions.size;
        
        // Update the display
        const answeredDisplay = document.getElementById('answered-count');
        if (answeredDisplay) {
            answeredDisplay.textContent = totalAnswered;
        }
        
        console.log(`Total unique questions answered: ${totalAnswered}`);
    }
    
    // Override form submission for debugging
    const form = document.getElementById('reading-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('=== ENHANCED FORM SUBMISSION DEBUG ===');
            
            const formData = new FormData(form);
            const allAnswers = {};
            let matchingHeadingCount = 0;
            let sentenceCompletionCount = 0;
            
            for (let [key, value] of formData.entries()) {
                allAnswers[key] = value;
                
                if (key.includes('_para_') || key.includes('_q')) {
                    console.log(`Matching heading: ${key} = ${value}`);
                    matchingHeadingCount++;
                } else if (key.includes('answers[') && key.includes('_q')) {
                    console.log(`Sentence completion: ${key} = ${value}`);
                    sentenceCompletionCount++;
                }
            }
            
            console.log('All form answers:', allAnswers);
            console.log(`Total matching heading answers: ${matchingHeadingCount}`);
            console.log(`Total sentence completion answers: ${sentenceCompletionCount}`);
            console.log('=== END ENHANCED FORM SUBMISSION DEBUG ===');
        });
    }
    
    // Add enhanced styles to the page
    const style = document.createElement('style');
    style.textContent = `
        .enhanced-dropdown {
            appearance: auto !important;
            -webkit-appearance: menulist !important;
            -moz-appearance: menulist !important;
        }
        
        .enhanced-dropdown:focus {
            outline: none !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }
        
        .enhanced-dropdown[data-answered="true"] {
            background-color: #d1fae5 !important;
            border-color: #10b981 !important;
            color: #065f46 !important;
            font-weight: 600 !important;
        }
        
        .enhanced-dropdown option {
            padding: 4px 8px;
            font-size: 14px;
            background-color: white;
            color: #374151;
        }
        
        .enhanced-dropdown option:first-child {
            color: #9ca3af;
            font-style: italic;
        }
        
        /* Ensure all dropdowns in matching headings are properly visible */
        .matching-heading-select, 
        select[name*="_q"], 
        select[name*="_para_"],
        .sc-dropdown {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* Enhanced navigation button styles */
        .number-btn.answered {
            background-color: #10b981 !important;
            color: white !important;
            font-weight: 600 !important;
        }
    `;
    document.head.appendChild(style);
    
    // Add event listeners to all question inputs for live counting
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[name^="answers["], select[name^="answers["]')) {
            updateAnsweredCount();
        }
    });
    
    // Initial count update
    updateAnsweredCount();
    
    // Force visibility check for all dropdowns after page load
    setTimeout(() => {
        const allDropdowns = document.querySelectorAll('select[name^="answers["], .sc-dropdown, .matching-heading-select');
        console.log('Final dropdown visibility check - found:', allDropdowns.length);
        
        allDropdowns.forEach((dropdown, index) => {
            // Force visibility
            dropdown.style.display = 'inline-block';
            dropdown.style.visibility = 'visible';
            dropdown.style.opacity = '1';
            
            console.log(`Dropdown ${index} visibility:`, {
                name: dropdown.name || dropdown.className,
                visible: dropdown.offsetParent !== null,
                display: window.getComputedStyle(dropdown).display,
                visibility: window.getComputedStyle(dropdown).visibility
            });
        });
    }, 1500);
    
    // Monitor for dynamic content changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                // Re-attach listeners if new elements are added
                const newSelects = document.querySelectorAll('select[name^="answers["]:not(.enhanced-dropdown)');
                newSelects.forEach(select => {
                    select.classList.add('enhanced-dropdown');
                    // Apply all the event listeners and styling as above
                    console.log('Enhanced new dropdown:', select.name);
                });
            }
        });
    });
    
    // Start observing the questions section
    const questionsSection = document.querySelector('.questions-section');
    if (questionsSection) {
        observer.observe(questionsSection, { childList: true, subtree: true });
    }
});
