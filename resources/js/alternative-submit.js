// Alternative submission method for Reading Test
// This bypasses form validation issues

window.AlternativeSubmit = {
    init() {
        console.log('Initializing Alternative Submit Handler...');
        this.setupAlternativeSubmit();
    },

    setupAlternativeSubmit() {
        // Override the confirm submit button
        const confirmBtn = document.getElementById('confirm-submit-btn');
        if (confirmBtn) {
            confirmBtn.removeEventListener('click', null);
            confirmBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.submitViaAjax();
            });
        }

        // Add alternative submit button
        this.addAlternativeButton();
    },

    addAlternativeButton() {
        const navRight = document.querySelector('.nav-right');
        if (navRight && !document.getElementById('alt-submit-btn')) {
            const altBtn = document.createElement('button');
            altBtn.id = 'alt-submit-btn';
            altBtn.type = 'button';
            altBtn.className = 'px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 mr-2';
            altBtn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Quick Submit';
            altBtn.onclick = () => this.quickSubmit();
            
            const submitBtn = navRight.querySelector('.submit-test-button');
            navRight.insertBefore(altBtn, submitBtn);
        }
    },

    collectAllAnswers() {
        const answers = {};
        
        // Collect radio button answers
        document.querySelectorAll('input[type="radio"]:checked').forEach(input => {
            const match = input.name.match(/answers\[(\d+)\]/);
            if (match) {
                answers[match[1]] = input.value;
            }
        });

        // Collect text input answers
        document.querySelectorAll('input[type="text"][name^="answers"]').forEach(input => {
            if (input.value.trim()) {
                const nameMatch = input.name.match(/answers\[(\d+)\]$/);
                if (nameMatch) {
                    answers[nameMatch[1]] = input.value;
                }
            }
        });

        // Collect select answers
        document.querySelectorAll('select[name^="answers"]').forEach(select => {
            if (select.value) {
                const match = select.name.match(/answers\[(\d+)\]/);
                if (match) {
                    answers[match[1]] = select.value;
                }
            }
        });

        // Collect fill-in-the-gap answers
        document.querySelectorAll('.gap-input').forEach(input => {
            if (input.value.trim()) {
                const match = input.name.match(/answers\[(\d+)\]\[blank_(\d+)\]/);
                if (match) {
                    const questionId = match[1];
                    const blankNum = match[2];
                    
                    if (!answers[questionId]) {
                        answers[questionId] = {};
                    }
                    
                    if (typeof answers[questionId] !== 'object') {
                        answers[questionId] = {};
                    }
                    
                    answers[questionId][`blank_${blankNum}`] = input.value;
                }
            }
        });

        // Collect dropdown answers
        document.querySelectorAll('.gap-dropdown').forEach(select => {
            if (select.value) {
                const match = select.name.match(/answers\[(\d+)\]\[dropdown_(\d+)\]/);
                if (match) {
                    const questionId = match[1];
                    const dropdownNum = match[2];

                    if (!answers[questionId]) {
                        answers[questionId] = {};
                    }

                    if (typeof answers[questionId] !== 'object') {
                        answers[questionId] = {};
                    }

                    answers[questionId][`dropdown_${dropdownNum}`] = select.value;
                }
            }
        });

        // Collect passage answer inputs (matching headings drag & drop)
        document.querySelectorAll('.passage-answer-input').forEach(input => {
            if (input.value && input.value.trim() !== '') {
                // Parse the name format: answers[questionId_qNumber]
                const match = input.name.match(/answers\[(\d+)_q(\d+)\]/);
                if (match) {
                    const questionId = match[1];
                    const subQuestion = match[2];
                    const key = `${questionId}_q${subQuestion}`;
                    answers[key] = input.value;
                }
            }
        });
        return answers;
    },

    quickSubmit() {
        if (!confirm('Submit your test now?')) {
            return;
        }

        this.submitViaAjax();
    },

    async submitViaAjax() {
        const submitBtn = document.getElementById('confirm-submit-btn') || document.getElementById('alt-submit-btn');
        const originalText = submitBtn.innerHTML;
        
        try {
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...';

            // Collect all answers
            const answers = this.collectAllAnswers();
            
            // Get attempt ID and CSRF token
            const attemptId = window.testConfig?.attemptId;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!attemptId) {
                throw new Error('No attempt ID found');
            }

            // Prepare form data
            const formData = new FormData();
            formData.append('_token', csrfToken);
            
            // Add answers to form data
            Object.keys(answers).forEach(questionId => {
                const answer = answers[questionId];
                if (typeof answer === 'object') {
                    // Fill-in-the-gap answers
                    Object.keys(answer).forEach(key => {
                        formData.append(`answers[${questionId}][${key}]`, answer[key]);
                    });
                } else {
                    // Regular answers
                    formData.append(`answers[${questionId}]`, answer);
                }
            });

            // Submit via fetch
            const response = await fetch(`/student/test/reading/submit/${attemptId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                // If JSON response fails, try form submission
                console.log('AJAX failed, trying form submission...');
                this.submitViaForm();
                return;
            }

            const result = await response.json();
            
            if (result.success || result.redirect) {
                // Success - redirect to results
                window.location.href = result.redirect || result.redirect_url || '/student/test/results';
            } else {
                throw new Error(result.message || 'Submission failed');
            }

        } catch (error) {
            console.error('Submission error:', error);
            
            // Try alternative form submission
            if (confirm('Ajax submission failed. Try alternative method?')) {
                this.submitViaForm();
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    },

    submitViaForm() {
        try {
            // Create a hidden form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = document.getElementById('reading-form').action;
            form.style.display = 'none';

            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrfToken;
            form.appendChild(tokenInput);

            // Collect and add all answers
            const answers = this.collectAllAnswers();
            
            Object.keys(answers).forEach(questionId => {
                const answer = answers[questionId];
                
                if (typeof answer === 'object') {
                    // Fill-in-the-gap answers
                    Object.keys(answer).forEach(key => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `answers[${questionId}][${key}]`;
                        input.value = answer[key];
                        form.appendChild(input);
                    });
                } else {
                    // Regular answers
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `answers[${questionId}]`;
                    input.value = answer;
                    form.appendChild(input);
                }
            });

            // Append and submit
            document.body.appendChild(form);
            console.log('Submitting via form...');
            form.submit();

        } catch (error) {
            console.error('Form submission error:', error);
            alert('Failed to submit. Please try again or contact support.');
        }
    }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => AlternativeSubmit.init());
} else {
    AlternativeSubmit.init();
}