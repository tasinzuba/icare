// Shared blank handling functionality for admin question pages

// Store blank answers
const blankAnswersStore = {};

// Insert blank function
window.insertBlank = function() {
    const editor = tinymce.get('content');
    if (!editor) return;
    
    const existingBlanks = (editor.getContent().match(/\[____\d+____\]/g) || []).length;
    const nextNum = existingBlanks + 1;
    
    editor.insertContent(`[____${nextNum}____]`);
    
    // Update blanks display after a short delay
    setTimeout(updateBlanks, 100);
};

// Update blanks display
function updateBlanks() {
    const editor = tinymce.get('content');
    if (!editor) return;
    
    // Save current values first
    saveCurrentBlankValues();
    
    const content = editor.getContent();
    const blankMatches = content.match(/\[____(\d+)____\]/g) || [];
    
    const container = document.getElementById('blank-answers-container');
    if (!container) {
        // Create container if it doesn't exist
        createBlankAnswersContainer();
    }
    
    const blanksList = document.getElementById('blanks-list');
    if (!blanksList) return;
    
    if (blankMatches.length > 0) {
        container.style.display = 'block';
        blanksList.innerHTML = '';
        
        blankMatches.forEach((match, index) => {
            const num = index + 1;
            const storedValue = blankAnswersStore[num] || '';
            
            const itemDiv = document.createElement('div');
            itemDiv.className = 'flex items-center space-x-2 p-2 bg-white rounded border border-gray-200';
            
            itemDiv.innerHTML = `
                <span class="text-sm font-medium text-gray-700 min-w-[80px]">Blank ${num}:</span>
                <input type="text" 
                       name="blank_answers[]" 
                       class="blank-answer-input flex-1 px-3 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-purple-500" 
                       placeholder="Enter correct answer"
                       value="${storedValue}"
                       data-blank-num="${num}"
                       required>
            `;
            
            blanksList.appendChild(itemDiv);
            
            // Add event listener to save value
            const input = itemDiv.querySelector('input');
            input.addEventListener('input', function() {
                blankAnswersStore[num] = this.value;
            });
        });
    } else {
        container.style.display = 'none';
    }
}

// Save current blank values
function saveCurrentBlankValues() {
    document.querySelectorAll('.blank-answer-input').forEach(input => {
        const num = input.getAttribute('data-blank-num');
        if (num) {
            blankAnswersStore[num] = input.value;
        }
    });
}

// Create blank answers container
function createBlankAnswersContainer() {
    const formGroups = document.querySelectorAll('.space-y-4.sm\\:space-y-6');
    if (formGroups.length < 1) return;
    
    const containerDiv = document.createElement('div');
    containerDiv.id = 'blank-answers-container';
    containerDiv.style.display = 'none';
    containerDiv.className = 'mt-4';
    
    containerDiv.innerHTML = `
        <div class="bg-purple-50 border border-purple-200 rounded-md p-4">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Fill in the Blank Answers</h4>
            <div id="blanks-list" class="space-y-2">
                <!-- Blank inputs will be added here -->
            </div>
        </div>
    `;
    
    // Insert after the content field
    const contentField = document.getElementById('content');
    if (contentField && contentField.parentElement) {
        contentField.parentElement.appendChild(containerDiv);
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to content editor
    if (typeof tinymce !== 'undefined') {
        const checkEditor = setInterval(() => {
            const editor = tinymce.get('content');
            if (editor) {
                clearInterval(checkEditor);
                
                editor.on('NodeChange KeyUp', function() {
                    const questionType = document.getElementById('question_type')?.value;
                    if (['sentence_completion', 'note_completion', 'form_completion'].includes(questionType)) {
                        updateBlanks();
                    }
                });
                
                // Initial check
                updateBlanks();
            }
        }, 100);
    }
    
    // Show/hide insert blank button based on question type
    const questionTypeSelect = document.getElementById('question_type');
    if (questionTypeSelect) {
        questionTypeSelect.addEventListener('change', function() {
            const blankTypes = ['sentence_completion', 'note_completion', 'form_completion'];
            const insertButton = document.querySelector('button[onclick="insertBlank()"]');
            
            if (insertButton) {
                if (blankTypes.includes(this.value)) {
                    insertButton.style.display = 'inline-block';
                    updateBlanks();
                } else {
                    insertButton.style.display = 'none';
                    const container = document.getElementById('blank-answers-container');
                    if (container) {
                        container.style.display = 'none';
                    }
                }
            }
        });
        
        // Trigger initial check
        questionTypeSelect.dispatchEvent(new Event('change'));
    }
});