// Final Fix for Matching Headings Array Notation Issue
document.addEventListener('DOMContentLoaded', function() {
    console.log('[MH Fix] Initializing matching headings fix...');
    
    const form = document.getElementById('reading-form');
    if (!form) return;
    
    // Find all matching headings selects with array notation
    const matchingHeadingSelects = form.querySelectorAll('select[name*="answers["][name*="]["]');
    console.log('[MH Fix] Found matching heading selects:', matchingHeadingSelects.length);
    
    // Convert array notation to flat notation before submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('[MH Fix] Intercepting form submission...');
        
        // Create hidden inputs for each matching heading select
        matchingHeadingSelects.forEach(select => {
            if (select.value) {
                // Extract question ID and index from name like "answers[123][0]"
                const match = select.name.match(/answers\[(\d+)\]\[(\d+)\]/);
                if (match) {
                    const questionId = match[1];
                    const index = match[2];
                    
                    // Create a hidden input with flat name structure
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `answers[${questionId}][]`;
                    hiddenInput.value = select.value;
                    form.appendChild(hiddenInput);
                    
                    console.log('[MH Fix] Created hidden input:', hiddenInput.name, '=', hiddenInput.value);
                    
                    // Disable the original select to prevent double submission
                    select.disabled = true;
                }
            }
        });
        
        // Re-enable selects after a moment (for browser back button)
        setTimeout(() => {
            matchingHeadingSelects.forEach(select => {
                select.disabled = false;
            });
        }, 100);
        
        // Submit the form
        setTimeout(() => {
            console.log('[MH Fix] Submitting form...');
            HTMLFormElement.prototype.submit.call(form);
        }, 50);
    });
    
    console.log('[MH Fix] Fix applied successfully');
});
