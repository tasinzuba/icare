{{-- Fill in the Gap Question Editor Component --}}
<div id="fill-gap-editor" class="space-y-4">
    {{-- Content Editor --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Question Content
        </label>
        <div class="mb-2">
            <button type="button" 
                    onclick="insertBlankV2()" 
                    class="px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
                <i class="fas fa-plus-square mr-1"></i> Insert Blank
            </button>
            <span class="text-xs text-gray-500 ml-2">
                Click to insert blanks in the content
            </span>
        </div>
        <textarea 
            id="fill-gap-content" 
            name="content" 
            rows="4" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
            placeholder="Example: The capital of Bangladesh is [____1____] and it has [____2____] million people."
            required>{{ old('content', $question->content ?? '') }}</textarea>
    </div>

    {{-- Blanks Configuration --}}
    <div id="blanks-config" class="hidden">
        <h4 class="text-sm font-medium text-gray-700 mb-3">
            <i class="fas fa-list-ol mr-1"></i> Configure Blank Answers
        </h4>
        <div id="blanks-list" class="space-y-3">
            {{-- Blanks will be dynamically added here --}}
        </div>
    </div>
</div>

<script>
let blankCountV2 = 0;

function insertBlankV2() {
    const textarea = document.getElementById('fill-gap-content');
    const cursorPos = textarea.selectionStart;
    const textBefore = textarea.value.substring(0, cursorPos);
    const textAfter = textarea.value.substring(cursorPos);
    
    blankCountV2++;
    const blankText = `[____${blankCountV2}____]`;
    
    textarea.value = textBefore + blankText + textAfter;
    textarea.focus();
    
    // Set cursor after inserted blank
    const newPos = cursorPos + blankText.length;
    textarea.setSelectionRange(newPos, newPos);
    
    updateBlanksConfig();
}

function updateBlanksConfig() {
    const content = document.getElementById('fill-gap-content').value;
    const blankMatches = content.match(/\[____(\d+)____\]/g) || [];
    
    const configDiv = document.getElementById('blanks-config');
    const listDiv = document.getElementById('blanks-list');
    
    if (blankMatches.length === 0) {
        configDiv.classList.add('hidden');
        return;
    }
    
    configDiv.classList.remove('hidden');
    listDiv.innerHTML = '';
    
    // Extract blank numbers and sort them
    const blankNumbers = blankMatches.map(match => {
        const num = match.match(/\d+/)[0];
        return parseInt(num);
    }).sort((a, b) => a - b);
    
    // Create input fields for each blank
    blankNumbers.forEach(num => {
        const blankDiv = document.createElement('div');
        blankDiv.className = 'bg-gray-50 p-3 rounded-md border border-gray-200';
        blankDiv.innerHTML = `
            <div class="flex items-start gap-3">
                <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-600 text-sm font-semibold rounded">
                    ${num}
                </span>
                <div class="flex-1 space-y-2">
                    <div>
                        <label class="text-xs font-medium text-gray-700">
                            Correct Answer for Blank ${num}
                        </label>
                        <input type="text" 
                               name="blanks[${num}][correct_answer]" 
                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500"
                               placeholder="Enter the correct answer"
                               required>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">
                            Alternative Answers (optional, comma-separated)
                        </label>
                        <input type="text" 
                               name="blanks[${num}][alternate_answers]" 
                               class="w-full px-2 py-1 text-sm border border-gray-200 rounded focus:ring-1 focus:ring-blue-500"
                               placeholder="e.g., 20, twenty">
                        <p class="text-xs text-gray-500 mt-1">
                            Add variations that should also be marked correct
                        </p>
                    </div>
                </div>
                <button type="button" 
                        onclick="removeBlankV2(${num})" 
                        class="text-red-500 hover:text-red-700 p-1">
                    <i class="fas fa-trash text-sm"></i>
                </button>
            </div>
        `;
        listDiv.appendChild(blankDiv);
    });
    
    // Update blank count
    blankCountV2 = Math.max(...blankNumbers, 0);
}

function removeBlankV2(num) {
    const textarea = document.getElementById('fill-gap-content');
    const regex = new RegExp(`\\[____${num}____\\]`, 'g');
    textarea.value = textarea.value.replace(regex, '');
    updateBlanksConfig();
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    const contentTextarea = document.getElementById('fill-gap-content');
    if (contentTextarea) {
        contentTextarea.addEventListener('input', updateBlanksConfig);
        updateBlanksConfig(); // Initial check
    }
});
</script>