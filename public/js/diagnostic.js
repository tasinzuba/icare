// Diagnostic script for matching headings issue
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== MATCHING HEADINGS DIAGNOSTIC ===');
    
    // Find all matching headings selects
    const matchingHeadingSelects = document.querySelectorAll('select[name*="answers["][name*="]["]');
    
    console.log('Total matching heading selects found:', matchingHeadingSelects.length);
    
    matchingHeadingSelects.forEach((select, index) => {
        console.log(`Select #${index + 1}:`, {
            name: select.name,
            id: select.id,
            value: select.value,
            options: select.options.length,
            parent: select.parentElement.className
        });
    });
    
    // Test form serialization
    const form = document.getElementById('reading-form');
    if (form) {
        console.log('\n=== FORM SERIALIZATION TEST ===');
        
        // Method 1: FormData
        const formData = new FormData(form);
        console.log('FormData entries:');
        for (let [key, value] of formData.entries()) {
            if (key.includes('answers[')) {
                console.log(`  ${key} = ${value}`);
            }
        }
        
        // Method 2: jQuery serialize (if available)
        if (typeof jQuery !== 'undefined') {
            const serialized = jQuery(form).serialize();
            console.log('\njQuery serialize:', serialized);
        }
        
        // Method 3: Manual check
        console.log('\n=== MANUAL ELEMENT CHECK ===');
        const allInputs = form.querySelectorAll('input[name*="answers["], select[name*="answers["], textarea[name*="answers["]');
        allInputs.forEach(input => {
            if (input.value) {
                console.log(`${input.tagName} ${input.name} = ${input.value}`);
            }
        });
    }
    
    console.log('\n=== END DIAGNOSTIC ===');
});

// Add global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e);
});
