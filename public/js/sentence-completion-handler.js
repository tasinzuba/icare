// Professional Sentence Completion Test Handler - PERFECT VERSION
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing professional sentence completion handlers...');
    
    // Enhanced dropdown initialization
    function initializeSentenceCompletionDropdowns() {
        const scDropdowns = document.querySelectorAll('.sc-dropdown, .visible-dropdown, select[name*="_q"]');
        
        console.log('Found sentence completion dropdowns:', scDropdowns.length);
        
        scDropdowns.forEach((dropdown, index) => {
            console.log(`Processing dropdown ${index}:`, {
                name: dropdown.name,
                className: dropdown.className,
                questionNumber: dropdown.getAttribute('data-question-number')
            });
            
            // Ensure dropdown is visible with professional styling
            dropdown.style.display = 'inline-block';
            dropdown.style.visibility = 'visible';
            dropdown.style.opacity = '1';
            
            // Add enhanced change event listener
            dropdown.addEventListener('change', function() {
                const questionNumber = this.getAttribute('data-question-number');
                const value = this.value;
                
                console.log('Sentence completion answer changed:', {
                    questionNumber: questionNumber,
                    value: value,
                    name: this.name
                });
                
                // Update the navigation button
                updateNavigationButton(questionNumber, value);
                
                // Update dropdown styling based on selection
                if (value && value !== '') {
                    this.setAttribute('data-answered', 'true');
                    // Professional answered state is handled by CSS
                } else {
                    this.removeAttribute('data-answered');
                }
            });
            
            // Enhanced hover and focus effects are handled by CSS
        });
    }
    
    function updateNavigationButton(questionNumber, hasValue) {
        const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
        if (navButton) {
            if (hasValue && hasValue !== '') {
                navButton.classList.add('answered');
            } else {
                navButton.classList.remove('answered');
            }
        }
    }
    
    // Enhanced navigation button click handlers
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('number-btn')) {
            const questionId = e.target.getAttribute('data-question');
            const subQuestion = e.target.getAttribute('data-sub-question');
            const displayNumber = e.target.getAttribute('data-display-number');
            
            console.log('Navigation button clicked:', {
                questionId,
                subQuestion,
                displayNumber
            });
            
            if (subQuestion !== null || displayNumber) {
                // Look for sentence completion dropdown with multiple selectors
                const selectors = [
                    `.sc-dropdown[data-question-number="${displayNumber}"]`,
                    `.visible-dropdown[data-question-number="${displayNumber}"]`,
                    `select[name*="_q${displayNumber}"]`,
                    `select[data-question-number="${displayNumber}"]`,
                    `select[name="answers[${questionId}_q${displayNumber}]"]`
                ];
                
                let targetDropdown = null;
                for (const selector of selectors) {
                    targetDropdown = document.querySelector(selector);
                    if (targetDropdown) {
                        console.log('Found dropdown with selector:', selector);
                        break;
                    }
                }
                
                if (targetDropdown) {
                    // Smooth scroll to the dropdown
                    targetDropdown.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center',
                        inline: 'center' 
                    });
                    
                    // Professional highlight animation
                    targetDropdown.style.boxShadow = '0 0 0 4px rgba(59, 130, 246, 0.4)';
                    targetDropdown.style.transform = 'scale(1.05)';
                    
                    setTimeout(() => {
                        targetDropdown.style.boxShadow = '';
                        targetDropdown.style.transform = '';
                    }, 2000);
                    
                    // Focus on the dropdown after animation
                    setTimeout(() => {
                        targetDropdown.focus();
                        console.log('Focused on dropdown');
                    }, 300);
                } else {
                    console.warn('No dropdown found for question number:', displayNumber);
                }
            }
        }
    });
    
    // Initialize dropdowns
    initializeSentenceCompletionDropdowns();
    
    // Re-initialize after delays to catch dynamic content
    setTimeout(initializeSentenceCompletionDropdowns, 500);
    setTimeout(initializeSentenceCompletionDropdowns, 1000);
    
    // Monitor for dynamic content changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                console.log('DOM changed, re-initializing dropdowns...');
                setTimeout(initializeSentenceCompletionDropdowns, 100);
            }
        });
    });
    
    // Start observing
    const questionsSection = document.querySelector('.questions-section, .content-area');
    if (questionsSection) {
        observer.observe(questionsSection, { 
            childList: true, 
            subtree: true
        });
    }
    
    // Enhanced answered count tracking
    function updateAnsweredCount() {
        const answeredQuestions = new Set();
        
        // Count all answered inputs including sentence completion
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
        
        console.log(`Total answered questions: ${totalAnswered}`);
    }
    
    // Add event listeners for live counting
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[name^="answers["], select[name^="answers["]')) {
            updateAnsweredCount();
        }
    });
    
    // Initial count update
    updateAnsweredCount();
    
    // Final visibility check with professional logging
    setTimeout(() => {
        console.log('=== PROFESSIONAL DROPDOWN FINAL CHECK ===');
        const allDropdowns = document.querySelectorAll('.sc-dropdown, .visible-dropdown, select[name*="_q"]');
        
        allDropdowns.forEach((dropdown, index) => {
            const rect = dropdown.getBoundingClientRect();
            const styles = window.getComputedStyle(dropdown);
            
            console.log(`Professional dropdown ${index}:`, {
                name: dropdown.name,
                visible: dropdown.offsetParent !== null,
                display: styles.display,
                visibility: styles.visibility,
                opacity: styles.opacity,
                width: rect.width,
                height: rect.height,
                hasAnsweredClass: dropdown.hasAttribute('data-answered')
            });
        });
        console.log('=== END PROFESSIONAL DROPDOWN CHECK ===');
    }, 2000);
});
