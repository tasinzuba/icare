// Sentence Completion Test Handler
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing sentence completion handlers...');
    
    // Track all sentence completion dropdowns
    const scDropdowns = document.querySelectorAll('.sc-dropdown');
    
    scDropdowns.forEach(dropdown => {
        dropdown.addEventListener('change', function() {
            const questionNumber = this.getAttribute('data-question-number');
            const value = this.value;
            
            console.log('Sentence completion answer changed:', {
                questionNumber: questionNumber,
                value: value
            });
            
            // Update the navigation button
            updateNavigationButton(questionNumber, value);
            
            // Update dropdown styling
            if (value) {
                this.style.backgroundColor = '#d1fae5';
                this.style.borderColor = '#10b981';
                this.setAttribute('data-answered', 'true');
            } else {
                this.style.backgroundColor = '#fffbeb';
                this.style.borderColor = '#cbd5e1';
                this.removeAttribute('data-answered');
            }
        });
    });
    
    function updateNavigationButton(questionNumber, hasValue) {
        const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
        if (navButton) {
            if (hasValue) {
                navButton.classList.add('answered');
            } else {
                navButton.classList.remove('answered');
            }
        }
    }
    
    // Handle navigation button clicks for sentence completion
    document.querySelectorAll('.number-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const questionId = this.getAttribute('data-question');
            const subQuestion = this.getAttribute('data-sub-question');
            const displayNumber = this.getAttribute('data-display-number');
            
            if (subQuestion !== null) {
                // This is a sub-question (sentence completion or matching heading)
                const targetDropdown = document.querySelector(`.sc-dropdown[data-question-number="${displayNumber}"]`);
                if (targetDropdown) {
                    // Scroll to the dropdown
                    targetDropdown.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Highlight the dropdown
                    targetDropdown.style.boxShadow = '0 0 0 3px rgba(245, 158, 11, 0.5)';
                    setTimeout(() => {
                        targetDropdown.style.boxShadow = '';
                    }, 2000);
                    
                    // Focus on the dropdown
                    targetDropdown.focus();
                }
            }
        });
    });
    
    // Add styles for answered dropdowns
    const style = document.createElement('style');
    style.textContent = `
        .sc-dropdown {
            transition: all 0.2s ease;
            font-weight: 500;
        }
        
        .sc-dropdown:focus {
            outline: none;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background-color: #ffffff !important;
        }
        
        .sc-dropdown:hover {
            border-color: #94a3b8 !important;
            background-color: #f8fafc !important;
        }
        
        .sc-dropdown[data-answered="true"] {
            background-color: #d1fae5 !important;
            border-color: #10b981 !important;
            color: #065f46;
        }
        
        .sc-dropdown option {
            padding: 4px 8px;
        }
        
        /* Word list beautiful styles */
        .word-list-box {
            background: linear-gradient(to right, #f0f9ff, #e0f2fe);
            border: 1px solid #0891b2;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .word-list-item {
            background: white;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            transition: all 0.2s;
        }
        
        .word-list-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .number-btn.answered {
            background-color: #10b981 !important;
            color: white !important;
        }
    `;
    document.head.appendChild(style);
});
