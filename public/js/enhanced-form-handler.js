// Enhanced Form Submission Handler for Reading Test
// Specifically handles matching headings and array notation fields

document.addEventListener('DOMContentLoaded', function() {
    console.log('[Enhanced Form Handler] Initializing...');
    
    const form = document.getElementById('reading-form');
    if (!form) {
        console.error('[Enhanced Form Handler] Form not found!');
        return;
    }
    
    // Function to manually serialize form data including array notation
    function serializeFormData(form) {
        const formData = new FormData();
        const processedFields = new Set();
        
        // Process all form elements
        const elements = form.elements;
        
        for (let i = 0; i < elements.length; i++) {
            const element = elements[i];
            
            // Skip if no name or already processed
            if (!element.name || processedFields.has(element.name)) {
                continue;
            }
            
            // Handle different input types
            if (element.type === 'radio') {
                if (element.checked) {
                    formData.append(element.name, element.value);
                    console.log('[Form Serializer] Radio:', element.name, '=', element.value);
                }
            } else if (element.type === 'checkbox') {
                if (element.checked) {
                    formData.append(element.name, element.value);
                    console.log('[Form Serializer] Checkbox:', element.name, '=', element.value);
                }
            } else if (element.type === 'select-one' || element.type === 'select-multiple') {
                if (element.value) {
                    formData.append(element.name, element.value);
                    console.log('[Form Serializer] Select:', element.name, '=', element.value);
                }
            } else if (element.type !== 'submit' && element.type !== 'button') {
                if (element.value) {
                    formData.append(element.name, element.value);
                    console.log('[Form Serializer] Input:', element.name, '=', element.value);
                }
            }
            
            processedFields.add(element.name);
        }
        
        // Also check for any select elements with array notation
        const allSelects = form.querySelectorAll('select[name*="["][name*="]"]');
        allSelects.forEach(select => {
            if (select.value && !processedFields.has(select.name)) {
                formData.append(select.name, select.value);
                console.log('[Form Serializer] Array Select:', select.name, '=', select.value);
                processedFields.add(select.name);
            }
        });
        
        return formData;
    }
    
    // Override form submission
    let isSubmitting = false;
    
    form.addEventListener('submit', function(e) {
        if (isSubmitting) {
            console.log('[Enhanced Form Handler] Already submitting, skip duplicate');
            return;
        }
        
        e.preventDefault();
        isSubmitting = true;
        
        console.log('[Enhanced Form Handler] Form submission intercepted');
        
        // Manual form serialization
        const formData = serializeFormData(form);
        
        // Convert FormData to regular object for logging
        const dataObject = {};
        for (let [key, value] of formData.entries()) {
            dataObject[key] = value;
        }
        
        console.log('[Enhanced Form Handler] Serialized data:', dataObject);
        
        // Create a new form submission with proper data
        const xhr = new XMLHttpRequest();
        xhr.open('POST', form.action);
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.content || '');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                // Success - redirect
                if (xhr.responseURL && xhr.responseURL !== window.location.href) {
                    window.location.href = xhr.responseURL;
                } else {
                    // Try to parse response for redirect
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    } catch (e) {
                        // If not JSON, likely HTML redirect
                        document.open();
                        document.write(xhr.responseText);
                        document.close();
                    }
                }
            } else {
                console.error('[Enhanced Form Handler] Submission failed:', xhr.status);
                alert('Error submitting form. Please try again.');
                isSubmitting = false;
            }
        };
        
        xhr.onerror = function() {
            console.error('[Enhanced Form Handler] Network error');
            alert('Network error. Please check your connection and try again.');
            isSubmitting = false;
        };
        
        // Send the form data
        xhr.send(formData);
    });
    
    // Monitor all select changes
    form.addEventListener('change', function(e) {
        if (e.target.tagName === 'SELECT') {
            console.log('[Enhanced Form Handler] Select changed:', e.target.name, '=', e.target.value);
            
            // Force value attribute update
            if (e.target.value) {
                e.target.setAttribute('data-value', e.target.value);
                
                // Ensure option is selected
                Array.from(e.target.options).forEach(option => {
                    if (option.value === e.target.value) {
                        option.selected = true;
                        option.setAttribute('selected', 'selected');
                    } else {
                        option.removeAttribute('selected');
                    }
                });
            }
        }
    });
    
    console.log('[Enhanced Form Handler] Ready');
});
