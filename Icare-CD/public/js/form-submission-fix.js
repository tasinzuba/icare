// Form Submission Fix for Reading Test
// This script ensures all form data is properly submitted

document.addEventListener('DOMContentLoaded', function() {
    console.log('[Form Fix] Initializing form submission fix...');
    
    const form = document.getElementById('reading-form');
    if (!form) {
        console.error('[Form Fix] Reading form not found!');
        return;
    }
    
    // Override form submission to ensure all data is captured
    form.addEventListener('submit', function(e) {
        console.log('[Form Fix] Form submission intercepted');
        
        // Allow a brief moment for any pending state updates
        e.preventDefault();
        
        // Force save all current answers
        if (window.AnswerManager && window.AnswerManager.saveAllAnswers) {
            console.log('[Form Fix] Forcing answer save...');
            window.AnswerManager.saveAllAnswers();
        }
        
        // Collect all form data
        const formData = new FormData(form);
        const allAnswers = {};
        let answerCount = 0;
        
        // Log all current form values
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('answers[') && value) {
                allAnswers[key] = value;
                answerCount++;
            }
        }
        
        console.log('[Form Fix] Total answers collected:', answerCount);
        console.log('[Form Fix] Answer data:', allAnswers);
        
        // Check if form has action
        if (!form.action) {
            console.error('[Form Fix] Form has no action URL!');
            alert('Error: Form submission URL not found. Please contact support.');
            return;
        }
        
        // Submit the form after brief delay
        setTimeout(() => {
            console.log('[Form Fix] Submitting form to:', form.action);
            HTMLFormElement.prototype.submit.call(form);
        }, 100);
    });
    
    // Monitor select changes for matching headings
    document.querySelectorAll('select[name*="answers["]').forEach(select => {
        select.addEventListener('change', function() {
            console.log('[Form Fix] Select changed:', this.name, '=', this.value);
            
            // Ensure the value is set in the DOM
            if (this.value) {
                this.setAttribute('data-selected-value', this.value);
                
                // Find the selected option and ensure it's marked as selected
                Array.from(this.options).forEach(option => {
                    if (option.value === this.value) {
                        option.selected = true;
                    }
                });
            }
        });
    });
    
    console.log('[Form Fix] Form submission fix initialized');
});
