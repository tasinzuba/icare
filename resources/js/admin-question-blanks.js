// Dynamic Blank Answer Fields for Fill-in-the-Gap Questions

document.addEventListener('DOMContentLoaded', function() {
    const questionTypeSelect = document.getElementById('question_type');
    const contentTextarea = document.getElementById('content');
    const blankAnswersContainer = document.getElementById('blank-answers-container');
    
    // Question types that support blanks
    const blankSupportedTypes = [
        'sentence_completion',
        'note_completion', 
        'summary_completion',
        'form_completion'
    ];
    
    // Function to extract blanks from content
    function extractBlanks() {
        if (!contentTextarea || !blankAnswersContainer) return;
        
        const content = contentTextarea.value;
        const blankMatches = content.match(/\[____(\d+)____\]/g) || [];
        const dropdownMatches = content.match(/\[DROPDOWN_\d+\]/g) || [];
        
        // Clear existing fields
        blankAnswersContainer.innerHTML = '';
        
        if (blankMatches.length === 0 && dropdownMatches.length === 0) {
            blankAnswersContainer.innerHTML = '<p class="text-gray-500 text-sm">No blanks found in content. Use [____1____] for blanks or [DROPDOWN_1] for dropdowns.</p>';
            return;
        }
        
        // Add blank answer fields
        if (blankMatches.length > 0) {
            const blanksDiv = document.createElement('div');
            blanksDiv.className = 'space-y-3';
            blanksDiv.innerHTML = '<h4 class="font-medium text-gray-700">Blank Answers:</h4>';
            
            // Extract blank numbers and sort them
            const blankNumbers = [];
            blankMatches.forEach(match => {
                const num = parseInt(match.match(/\d+/)[0]);
                if (!blankNumbers.includes(num)) {
                    blankNumbers.push(num);
                }
            });
            blankNumbers.sort((a, b) => a - b);
            
            // Create input for each unique blank number
            blankNumbers.forEach((blankNum, index) => {
                const fieldDiv = document.createElement('div');
                fieldDiv.className = 'flex items-center gap-3';
                fieldDiv.innerHTML = `
                    <label class="text-sm font-medium text-gray-600 w-24">Blank ${blankNum}:</label>
                    <input type="text" 
                           name="blank_answers[]" 
                           data-blank-number="${blankNum}"
                           class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Correct answer (use | for alternatives: answer1|answer2)"
                           required>
                    <span class="text-xs text-gray-500">[____${blankNum}____]</span>
                `;
                blanksDiv.appendChild(fieldDiv);
            });
            
            blankAnswersContainer.appendChild(blanksDiv);
            
            // Add help text
            const helpText = document.createElement('p');
            helpText.className = 'text-xs text-gray-500 mt-2';
            helpText.innerHTML = 'Tip: Use forward slash (/) to specify alternative correct answers. Example: <code class="bg-gray-100 px-1">color/colour</code>';
            blankAnswersContainer.appendChild(helpText);
        }
        
        // Add dropdown fields
        if (dropdownMatches.length > 0) {
            const dropdownDiv = document.createElement('div');
            dropdownDiv.className = 'space-y-3 mt-4';
            dropdownDiv.innerHTML = '<h4 class="font-medium text-gray-700">Dropdown Options:</h4>';
            
            dropdownMatches.forEach((match, index) => {
                const dropdownNum = index + 1;
                const fieldDiv = document.createElement('div');
                fieldDiv.className = 'border border-gray-200 rounded-lg p-3 space-y-2';
                fieldDiv.innerHTML = `
                    <div class="font-medium text-sm text-gray-700">Dropdown ${dropdownNum} ${match}</div>
                    <div>
                        <label class="text-sm text-gray-600">Options (comma-separated):</label>
                        <input type="text" 
                               name="dropdown_options[]" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="option1, option2, option3"
                               required>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Correct Answer Index (0-based):</label>
                        <input type="number" 
                               name="dropdown_correct[]" 
                               class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="0"
                               min="0"
                               required>
                        <span class="text-xs text-gray-500 ml-2">(0 = first option, 1 = second option, etc.)</span>
                    </div>
                `;
                dropdownDiv.appendChild(fieldDiv);
            });
            
            blankAnswersContainer.appendChild(dropdownDiv);
        }
    }
    
    // Check if question type supports blanks
    function checkQuestionType() {
        if (!questionTypeSelect || !blankAnswersContainer) return;
        
        const selectedType = questionTypeSelect.value;
        const container = document.getElementById('blank-answers-section');
        
        if (blankSupportedTypes.includes(selectedType)) {
            if (container) container.style.display = 'block';
            extractBlanks();
        } else {
            if (container) container.style.display = 'none';
        }
    }
    
    // Event listeners
    if (questionTypeSelect) {
        questionTypeSelect.addEventListener('change', checkQuestionType);
    }
    
    if (contentTextarea) {
        // Debounce the input event
        let debounceTimer;
        contentTextarea.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (blankSupportedTypes.includes(questionTypeSelect?.value)) {
                    extractBlanks();
                }
            }, 500);
        });
    }
    
    // Initial check
    checkQuestionType();
    
    // Add helper text
    const contentField = document.querySelector('[name="content"]');
    if (contentField && questionTypeSelect) {
        questionTypeSelect.addEventListener('change', function() {
            const helpText = contentField.parentElement.querySelector('.help-text');
            if (blankSupportedTypes.includes(this.value)) {
                if (!helpText) {
                    const help = document.createElement('p');
                    help.className = 'help-text text-sm text-gray-500 mt-1';
                    help.innerHTML = 'Use <code class="bg-gray-100 px-1">[____1____]</code> for text blanks or <code class="bg-gray-100 px-1">[DROPDOWN_1]</code> for dropdown options. Number blanks sequentially.';
                    contentField.parentElement.appendChild(help);
                }
            } else if (helpText) {
                helpText.remove();
            }
        });
    }
});

// Function to add blank manually
function addBlank() {
    const content = document.getElementById('content');
    if (!content) return;
    
    const currentContent = content.value;
    
    // Find all existing blank numbers
    const existingBlanks = [...currentContent.matchAll(/\[____(\d+)____\]/g)];
    const usedNumbers = existingBlanks.map(match => parseInt(match[1]));
    
    // Find the next available number
    let nextBlankNum = 1;
    while (usedNumbers.includes(nextBlankNum)) {
        nextBlankNum++;
    }
    
    // Insert at cursor position or at end
    const cursorPos = content.selectionStart;
    const textBefore = currentContent.substring(0, cursorPos);
    const textAfter = currentContent.substring(cursorPos);
    
    content.value = textBefore + `[____${nextBlankNum}____]` + textAfter;
    
    // Trigger input event
    content.dispatchEvent(new Event('input'));
    
    // Set cursor after the inserted blank
    const newCursorPos = cursorPos + `[____${nextBlankNum}____]`.length;
    content.setSelectionRange(newCursorPos, newCursorPos);
    content.focus();
}

// Function to add dropdown
function addDropdown() {
    const content = document.getElementById('content');
    if (!content) return;
    
    const currentContent = content.value;
    const dropdownCount = (currentContent.match(/\[DROPDOWN_\d+\]/g) || []).length;
    const nextDropdownNum = dropdownCount + 1;
    
    // Insert at cursor position or at end
    const cursorPos = content.selectionStart;
    const textBefore = currentContent.substring(0, cursorPos);
    const textAfter = currentContent.substring(cursorPos);
    
    content.value = textBefore + `[DROPDOWN_${nextDropdownNum}]` + textAfter;
    
    // Trigger input event
    content.dispatchEvent(new Event('input'));
    
    // Set cursor after the inserted dropdown
    const newCursorPos = cursorPos + `[DROPDOWN_${nextDropdownNum}]`.length;
    content.setSelectionRange(newCursorPos, newCursorPos);
    content.focus();
}