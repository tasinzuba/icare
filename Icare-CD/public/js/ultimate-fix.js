// Ultimate Fix for Matching Headings Form Submission
(function() {
    'use strict';
    
    console.log('[Ultimate Fix] Starting matching headings fix...');
    
    // Wait for DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        const form = document.getElementById('reading-form');
        if (!form) {
            console.error('[Ultimate Fix] Form not found!');
            return;
        }
        
        // Fix 1: Ensure all selects maintain their values
        fixSelectValues();
        
        // Fix 2: Override form submission
        fixFormSubmission(form);
        
        // Fix 3: Monitor select changes
        monitorSelectChanges();
        
        console.log('[Ultimate Fix] All fixes applied');
    }
    
    function fixSelectValues() {
        // Force all selects to maintain their selected value
        document.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT' && e.target.name.includes('answers[')) {
                const select = e.target;
                const value = select.value;
                
                console.log('[Ultimate Fix] Select changed:', select.name, '=', value);
                
                // Force the value to stick
                setTimeout(() => {
                    select.value = value;
                    
                    // Double-check by setting selected attribute
                    Array.from(select.options).forEach(option => {
                        if (option.value === value) {
                            option.selected = true;
                        }
                    });
                }, 0);
            }
        });
    }
    
    function fixFormSubmission(form) {
        // Store original submit function
        const originalSubmit = HTMLFormElement.prototype.submit;
        
        // Override form submission
        form.addEventListener('submit', function(e) {
            console.log('[Ultimate Fix] Form submission intercepted');
            
            // Don't prevent default immediately - let's see what happens
            
            // Log all form data before submission
            setTimeout(() => {
                const formData = new FormData(form);
                console.log('[Ultimate Fix] Form data being submitted:');
                
                let hasMatchingHeadings = false;
                
                for (let [key, value] of formData.entries()) {
                    if (key.includes('answers[')) {
                        console.log(`  ${key} = ${value}`);
                        if (key.includes('][')) {
                            hasMatchingHeadings = true;
                        }
                    }
                }
                
                if (hasMatchingHeadings) {
                    console.log('[Ultimate Fix] Matching headings data found in submission');
                } else {
                    console.warn('[Ultimate Fix] No matching headings data found!');
                    
                    // Try to manually add the data
                    const selects = form.querySelectorAll('select[name*="answers["][name*="]["]');
                    selects.forEach(select => {
                        if (select.value) {
                            console.log('[Ultimate Fix] Manually checking select:', select.name, '=', select.value);
                        }
                    });
                }
            }, 0);
        });
        
        // Also intercept programmatic submissions
        form.submit = function() {
            console.log('[Ultimate Fix] Programmatic submit called');
            
            // Ensure all selects have their values set
            const selects = this.querySelectorAll('select[name*="answers["]');
            selects.forEach(select => {
                if (select.value) {
                    // Create hidden input as backup
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = select.name;
                    hidden.value = select.value;
                    hidden.setAttribute('data-backup', 'true');
                    
                    // Remove any existing backup for this field
                    const existing = form.querySelector(`input[type="hidden"][name="${select.name}"][data-backup="true"]`);
                    if (existing) {
                        existing.remove();
                    }
                    
                    // Add the backup
                    form.appendChild(hidden);
                    console.log('[Ultimate Fix] Added backup hidden input for:', select.name, '=', select.value);
                }
            });
            
            // Call original submit
            originalSubmit.call(this);
        };
    }
    
    function monitorSelectChanges() {
        // Use MutationObserver to catch any dynamic changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                    const target = mutation.target;
                    if (target.tagName === 'SELECT' && target.name.includes('answers[')) {
                        console.log('[Ultimate Fix] Select value mutated:', target.name, '=', target.value);
                    }
                }
            });
        });
        
        // Observe all selects
        document.querySelectorAll('select[name*="answers["]').forEach(select => {
            observer.observe(select, {
                attributes: true,
                attributeFilter: ['value']
            });
        });
    }
    
    // Global helper to check form state
    window.checkFormState = function() {
        const form = document.getElementById('reading-form');
        if (!form) return;
        
        console.log('=== FORM STATE CHECK ===');
        const formData = new FormData(form);
        
        for (let [key, value] of formData.entries()) {
            if (key.includes('answers[')) {
                console.log(`${key} = ${value}`);
            }
        }
        
        console.log('\n=== SELECT ELEMENTS ===');
        form.querySelectorAll('select[name*="answers["]').forEach(select => {
            console.log(`${select.name} = ${select.value} (selectedIndex: ${select.selectedIndex})`);
        });
    };
    
})();
