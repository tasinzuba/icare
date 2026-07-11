// Matching Headings Fix for Reading Test - Version 3
document.addEventListener('DOMContentLoaded', function() {
    console.log('Matching Headings Fix V3 loaded');
    
    // Find all matching headings selects (both legacy and master formats)
    const matchingSelects = document.querySelectorAll('select[name*="_para_"], select[name*="_q"]');
    
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
    
    // Function to update answered count
    function updateAnsweredCount() {
        let totalAnswered = 0;
        
        // Count all answered questions
        const allInputs = document.querySelectorAll('input[name^="answers["], select[name^="answers["]');
        allInputs.forEach(input => {
            if (input.type === 'radio' || input.type === 'checkbox') {
                if (input.checked) totalAnswered++;
            } else if (input.value && input.value.trim() !== '') {
                totalAnswered++;
            }
        });
        
        // Update the display
        const answeredDisplay = document.getElementById('answered-count');
        if (answeredDisplay) {
            answeredDisplay.textContent = totalAnswered;
        }
        
        console.log(`Total answered: ${totalAnswered}`);
    }
    
    // Override form submission to ensure all data is captured
    const form = document.getElementById('reading-form');
    if (form) {
        // Add submit event listener for debugging
        form.addEventListener('submit', function(e) {
            console.log('Form submission intercepted for matching headings check');
            
            // Log all matching heading answers
            const formData = new FormData(form);
            console.log('=== MATCHING HEADINGS ANSWERS ===');
            let matchingHeadingCount = 0;
            
            for (let [key, value] of formData.entries()) {
                if (key.includes('_para_') || key.includes('_q')) {
                    console.log(`${key}: ${value}`);
                    matchingHeadingCount++;
                }
            }
            
            console.log(`Total matching heading answers: ${matchingHeadingCount}`);
            console.log('=== END MATCHING HEADINGS ===');
            
            // Ensure all select values are included
            const selects = form.querySelectorAll('select[name*="_q"]');
            selects.forEach(select => {
                if (select.value) {
                    console.log(`Ensuring ${select.name} = ${select.value} is included`);
                }
            });
        });
    }
    
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
