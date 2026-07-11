// Multiple Choice Form Handler
window.MultipleChoiceHandler = {
    init: function() {
        const form = document.getElementById('questionForm');
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            const questionType = document.getElementById('question_type').value;
            
            if (questionType === 'multiple_choice') {
                // Get all checked checkboxes
                const checkedBoxes = form.querySelectorAll('input[name="correct_option[]"]:checked');
                
                console.log('Multiple choice submission - checked boxes:', checkedBoxes.length);
                
                // Create hidden inputs for each checked option
                // Remove any existing hidden inputs first
                form.querySelectorAll('input[type="hidden"][name^="correct_option["]').forEach(el => el.remove());
                
                checkedBoxes.forEach((checkbox, index) => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `correct_option[${index}]`;
                    hiddenInput.value = checkbox.value;
                    form.appendChild(hiddenInput);
                    
                    console.log(`Added hidden input: correct_option[${index}] = ${checkbox.value}`);
                });
                
                // Also add a single array field
                const arrayInput = document.createElement('input');
                arrayInput.type = 'hidden';
                arrayInput.name = 'correct_option';
                arrayInput.value = JSON.stringify(Array.from(checkedBoxes).map(cb => cb.value));
                form.appendChild(arrayInput);
                
                console.log('Added JSON array:', arrayInput.value);
            }
        });
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    MultipleChoiceHandler.init();
});
