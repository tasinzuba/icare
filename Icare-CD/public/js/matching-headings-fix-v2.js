// Matching Headings Fix for Reading Test
document.addEventListener('DOMContentLoaded', function() {
    console.log('Matching Headings Fix loaded');
    
    // Find all matching headings selects
    const matchingSelects = document.querySelectorAll('select[name*="_para_"]');
    
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
            });
        });
    }
    
    // Override form submission to ensure all data is captured
    const form = document.getElementById('reading-form');
    if (form) {
        const originalSubmit = form.submit;
        
        form.submit = function() {
            console.log('Form submission intercepted for matching headings check');
            
            // Log all matching heading answers
            const formData = new FormData(form);
            console.log('=== MATCHING HEADINGS ANSWERS ===');
            for (let [key, value] of formData.entries()) {
                if (key.includes('_para_')) {
                    console.log(`${key}: ${value}`);
                }
            }
            console.log('=== END MATCHING HEADINGS ===');
            
            // Call original submit
            originalSubmit.call(form);
        };
    }
});
