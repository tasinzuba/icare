// Matching Headings Fix for Reading Test - Version 4
document.addEventListener('DOMContentLoaded', function() {
    console.log('Matching Headings Fix V4 loaded');
    
    // Find all matching headings selects
    const matchingSelects = document.querySelectorAll('select[name*="_para_"], select[name*="_q"], select.matching-heading-select');
    
    if (matchingSelects.length > 0) {
        console.log(`Found ${matchingSelects.length} matching headings dropdowns`);
        
        // Add change event listeners
        matchingSelects.forEach(select => {
            select.addEventListener('change', function() {
                console.log(`Matching heading changed: ${this.name} = ${this.value}`);
                
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
        });
    }
    
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
        console.log('Answered questions:', Array.from(answeredQuestions).sort((a, b) => parseInt(a) - parseInt(b)));
    }
    
    // Override form submission to ensure all data is captured
    const form = document.getElementById('reading-form');
    if (form) {
        // Add submit event listener for debugging
        form.addEventListener('submit', function(e) {
            console.log('=== FORM SUBMISSION DEBUG ===');
            
            // Log all form data
            const formData = new FormData(form);
            const allAnswers = {};
            let matchingHeadingCount = 0;
            
            for (let [key, value] of formData.entries()) {
                allAnswers[key] = value;
                
                if (key.includes('_para_') || key.includes('_q')) {
                    console.log(`Matching heading: ${key} = ${value}`);
                    matchingHeadingCount++;
                }
            }
            
            console.log('All form answers:', allAnswers);
            console.log(`Total matching heading answers: ${matchingHeadingCount}`);
            
            // Count all dropdowns that should have values
            const allMatchingSelects = form.querySelectorAll('select.matching-heading-select, select[name*="_q"]');
            let filledCount = 0;
            allMatchingSelects.forEach(select => {
                if (select.value) {
                    filledCount++;
                    console.log(`${select.name} has value: ${select.value}`);
                } else {
                    console.warn(`${select.name} is empty!`);
                }
            });
            
            console.log(`Filled matching headings: ${filledCount}/${allMatchingSelects.length}`);
            console.log('=== END FORM SUBMISSION DEBUG ===');
        });
    }
    
    // Add event listeners to all question inputs for live counting
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[name^="answers["], select[name^="answers["]')) {
            updateAnsweredCount();
        }
    });
    
    // Initial count update
    updateAnsweredCount();
    
    // Monitor for dynamic content changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                // Re-attach listeners if new matching heading elements are added
                const newSelects = document.querySelectorAll('select[name*="_para_"]:not([data-listener]), select[name*="_q"]:not([data-listener])');
                newSelects.forEach(select => {
                    select.setAttribute('data-listener', 'true');
                    select.addEventListener('change', function() {
                        console.log(`New matching heading changed: ${this.name} = ${this.value}`);
                        updateAnsweredCount();
                    });
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
