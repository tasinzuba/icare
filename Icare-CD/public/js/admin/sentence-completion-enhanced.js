// Enhanced Sentence Completion Manager - PERFECT VERSION
const SentenceCompletionManager = {
    optionCount: 0,
    sentenceCount: 0,
    options: [],
    sentences: [],
    
    init() {
        console.log('SentenceCompletionManager.init() called');
        
        // Clear any existing data
        this.optionCount = 0;
        this.sentenceCount = 0;
        this.options = [];
        this.sentences = [];
        
        // Clear containers
        const optionsContainer = document.getElementById('answer-options-container');
        const sentencesContainer = document.getElementById('sentences-container');
        if (optionsContainer) optionsContainer.innerHTML = '';
        if (sentencesContainer) sentencesContainer.innerHTML = '';
        
        // Setup event listeners
        const addOptionBtn = document.getElementById('add-answer-option-btn');
        const addSentenceBtn = document.getElementById('add-sentence-btn');
        
        if (addOptionBtn) {
            addOptionBtn.onclick = () => this.addOption();
        }
        
        if (addSentenceBtn) {
            addSentenceBtn.onclick = () => this.addSentence();
        }
        
        console.log('SentenceCompletionManager initialized');
    },
    
    addOption(text = '') {
        const container = document.getElementById('answer-options-container');
        if (!container) return;
        
        const index = this.optionCount;
        const letter = String.fromCharCode(65 + index);
        
        const optionDiv = document.createElement('div');
        optionDiv.className = 'flex items-center gap-2 p-2 bg-white rounded border border-gray-200';
        optionDiv.setAttribute('data-option-index', index);
        
        optionDiv.innerHTML = `
            <span class="option-letter">${letter}</span>
            <input type="text" 
                   data-option-id="${letter}"
                   value="${text}" 
                   class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                   placeholder="Enter option text..." 
                   onkeyup="SentenceCompletionManager.updateDropdowns()"
                   required>
            <button type="button" onclick="SentenceCompletionManager.removeOption(${index})" 
                    class="text-red-500 hover:text-red-700 p-1">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        container.appendChild(optionDiv);
        this.optionCount++;
        
        // Update all dropdowns
        this.updateDropdowns();
        
        // Enable sentence button if we have at least 2 options
        const addSentenceBtn = document.getElementById('add-sentence-btn');
        if (addSentenceBtn && this.optionCount >= 2) {
            addSentenceBtn.disabled = false;
        }
    },
    
    removeOption(index) {
        if (this.optionCount <= 2) {
            alert('You must have at least 2 answer options.');
            return;
        }
        
        const container = document.getElementById('answer-options-container');
        const optionDiv = container.querySelector(`[data-option-index="${index}"]`);
        if (optionDiv) {
            optionDiv.remove();
            this.reindexOptions();
        }
    },
    
    reindexOptions() {
        const container = document.getElementById('answer-options-container');
        const options = container.querySelectorAll('[data-option-index]');
        this.optionCount = 0;
        
        options.forEach((option, index) => {
            const letter = String.fromCharCode(65 + index);
            option.setAttribute('data-option-index', index);
            option.querySelector('.option-letter').textContent = letter;
            option.querySelector('input').setAttribute('data-option-id', letter);
            
            const btn = option.querySelector('button');
            btn.setAttribute('onclick', `SentenceCompletionManager.removeOption(${index})`);
            
            this.optionCount++;
        });
        
        // Update all dropdowns
        this.updateDropdowns();
        
        // Disable add sentence button if less than 2 options
        const addSentenceBtn = document.getElementById('add-sentence-btn');
        if (addSentenceBtn && this.optionCount < 2) {
            addSentenceBtn.disabled = true;
        }
    },
    
    addSentence(text = '', correctAnswer = '') {
        const container = document.getElementById('sentences-container');
        if (!container) return;
        
        const index = this.sentenceCount;
        const questionNum = (parseInt(document.getElementById('sc_start_number')?.value) || 1) + index;
        
        const sentenceDiv = document.createElement('div');
        sentenceDiv.className = 'p-4 bg-gray-50 rounded-lg border border-gray-200';
        sentenceDiv.setAttribute('data-sentence-index', index);
        
        sentenceDiv.innerHTML = `
            <div class="flex items-start justify-between mb-3">
                <span class="font-medium text-gray-700">Question ${questionNum}</span>
                <button type="button" onclick="SentenceCompletionManager.removeSentence(${index})" 
                        class="text-red-500 hover:text-red-700 p-1">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-3">
                <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mb-3">
                    <p class="text-sm text-yellow-800">
                        <strong>Instructions:</strong> Use [GAP] to mark where the dropdown should appear in your sentence.
                    </p>
                    <p class="text-xs text-yellow-600 mt-1">
                        Example: "The weather is [GAP] today." or "She completed the task [GAP]."
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Sentence with [GAP] placeholder <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        data-sentence-index="${index}"
                        class="sentence-input w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                        rows="2"
                        placeholder="Example: The meeting was cancelled [GAP] the weather was bad."
                        onkeyup="SentenceCompletionManager.updatePreview()"
                        required>${text || (text === '' ? 'Complete this sentence [GAP].' : text)}</textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Correct Answer
                    </label>
                    <select class="sentence-answer-select w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                            data-sentence-index="${index}"
                            onchange="SentenceCompletionManager.updateData()">
                        <option value="">Select correct answer</option>
                        ${this.getOptionsList(correctAnswer)}
                    </select>
                </div>
            </div>
        `;
        
        container.appendChild(sentenceDiv);
        this.sentenceCount++;
        
        // Update counter - FIXED: Only count sentences with content
        this.updateSentenceCount();
        
        // Update preview and data
        this.updatePreview();
        this.updateData();
    },
    
    removeSentence(index) {
        const container = document.getElementById('sentences-container');
        const sentenceDiv = container.querySelector(`[data-sentence-index="${index}"]`);
        if (sentenceDiv) {
            sentenceDiv.remove();
            this.reindexSentences();
        }
    },
    
    reindexSentences() {
        const container = document.getElementById('sentences-container');
        const sentences = container.querySelectorAll('[data-sentence-index]');
        this.sentenceCount = 0;
        
        const startNum = parseInt(document.getElementById('sc_start_number')?.value) || 1;
        
        sentences.forEach((sentence, index) => {
            const questionNum = startNum + index;
            sentence.setAttribute('data-sentence-index', index);
            sentence.querySelector('.font-medium').textContent = `Question ${questionNum}`;
            
            // Update textarea and select indices
            sentence.querySelector('textarea').setAttribute('data-sentence-index', index);
            sentence.querySelector('select').setAttribute('data-sentence-index', index);
            
            const btn = sentence.querySelector('button');
            btn.setAttribute('onclick', `SentenceCompletionManager.removeSentence(${index})`);
            
            this.sentenceCount++;
        });
        
        // Update counter - FIXED: Only count sentences with content
        this.updateSentenceCount();
        
        // Update preview and data
        this.updatePreview();
        this.updateData();
    },
    
    // FIXED: New method to update sentence count correctly
    updateSentenceCount() {
        const sentences = document.querySelectorAll('[data-sentence-index]');
        let actualSentenceCount = 0;
        
        sentences.forEach(sentenceDiv => {
            const textarea = sentenceDiv.querySelector('textarea');
            if (textarea && textarea.value.trim()) {
                actualSentenceCount++;
            }
        });
        
        document.getElementById('sentence-count').textContent = `${actualSentenceCount} sentences`;
    },
    
    getOptionsList(selectedValue = '') {
        const options = document.querySelectorAll('[data-option-id]');
        let html = '';
        
        options.forEach((input, index) => {
            const letter = String.fromCharCode(65 + index);
            const text = input.value || `Option ${letter}`;
            const selected = letter === selectedValue ? 'selected' : '';
            html += `<option value="${letter}" ${selected}>${letter}. ${text}</option>`;
        });
        
        return html;
    },
    
    updateDropdowns() {
        const selects = document.querySelectorAll('.sentence-answer-select');
        const newOptions = '<option value="">Select correct answer</option>' + this.getOptionsList();
        
        selects.forEach(select => {
            const currentValue = select.value;
            select.innerHTML = newOptions;
            select.value = currentValue; // Restore previous selection
        });
        
        // Update preview and data
        this.updatePreview();
        this.updateData();
    },
    
    updatePreview() {
        const previewContainer = document.getElementById('sentence-completion-preview');
        if (!previewContainer) return;
        
        const sentences = document.querySelectorAll('[data-sentence-index]');
        const startNum = parseInt(document.getElementById('sc_start_number')?.value) || window.nextQuestionNumber || 1;
        
        if (sentences.length === 0) {
            previewContainer.innerHTML = '<p class="text-gray-500 italic">Add sentences to see preview...</p>';
            return;
        }
        
        let html = '<div class="space-y-3">';
        let actualSentenceCount = 0;
        
        sentences.forEach((sentenceDiv, index) => {
            const textarea = sentenceDiv.querySelector('textarea');
            const select = sentenceDiv.querySelector('select');
            
            // FIXED: Only count and display sentences with content
            if (textarea && textarea.value.trim()) {
                actualSentenceCount++;
                const text = textarea.value;
                const questionNum = startNum + actualSentenceCount - 1;
                
                // Replace [GAP] with dropdown representation
                const processedText = text.replace(/\[GAP\]/g, '<span class="gap-dropdown">______</span>');
                
                // Validate [GAP] existence
                const hasGap = text.includes('[GAP]');
                const gapWarning = !hasGap ? '<span class="ml-2 text-red-600 text-xs">⚠️ Missing [GAP]</span>' : '';
                
                html += `
                    <div class="sentence-preview">
                        <strong>${questionNum}.</strong> ${processedText}
                        ${select && select.value ? `<span class="ml-2 text-green-600 text-xs">(Answer: ${select.value})</span>` : ''}
                        ${gapWarning}
                    </div>
                `;
            }
        });
        
        html += '</div>';
        
        // Add instruction text with correct count - FIXED: Use actual sentence count
        const endNum = actualSentenceCount > 0 ? (startNum + actualSentenceCount - 1) : startNum;
        const instructionText = actualSentenceCount > 0 ? `<div class="mb-4 p-3 bg-blue-50 rounded text-sm">
            <strong>Questions ${startNum}${actualSentenceCount > 1 ? '-' + endNum : ''}</strong><br>
            Complete the sentence${actualSentenceCount > 1 ? 's' : ''} below.<br>
            Choose <strong>NO MORE THAN ONE WORD</strong> from the list for each answer.
        </div>` : '';
        
        previewContainer.innerHTML = instructionText + html;
        
        // Update sentence count display - FIXED: Use actual count
        document.getElementById('sentence-count').textContent = `${actualSentenceCount} sentences`;
    },
    
    updateData() {
        // Collect all options
        this.options = [];
        document.querySelectorAll('[data-option-id]').forEach((input, index) => {
            const letter = String.fromCharCode(65 + index);
            this.options.push({
                id: letter,
                text: input.value || ''
            });
        });
        
        // Collect all sentences - FIXED: Only include sentences with content and [GAP]
        this.sentences = [];
        const sentenceDivs = document.querySelectorAll('[data-sentence-index]');
        const startNum = parseInt(document.getElementById('sc_start_number')?.value) || 1;
        
        let actualIndex = 0;
        sentenceDivs.forEach((sentenceDiv, index) => {
            const textarea = sentenceDiv.querySelector('textarea');
            const select = sentenceDiv.querySelector('select');
            
            // FIXED: Only include sentences with content
            if (textarea && textarea.value.trim()) {
                // Ensure [GAP] exists in sentence
                let sentenceText = textarea.value;
                if (!sentenceText.includes('[GAP]')) {
                    // Auto-add [GAP] at the end if missing
                    sentenceText = sentenceText.trim() + ' [GAP]';
                    textarea.value = sentenceText;
                }
                
                this.sentences.push({
                    questionNumber: startNum + actualIndex,
                    text: sentenceText,
                    correctAnswer: select ? select.value : ''
                });
                actualIndex++;
            }
        });
        
        // Update hidden input with JSON data
        const dataInput = document.getElementById('sentence_completion_data');
        if (dataInput) {
            dataInput.value = JSON.stringify({
                options: this.options,
                sentences: this.sentences,
                startNumber: startNum
            });
        }
        
        console.log('Updated sentence completion data:', {
            options: this.options,
            sentences: this.sentences
        });
    },
    
    generateQuestionContent() {
        const startNum = parseInt(document.getElementById('sc_start_number')?.value) || 1;
        const count = this.sentences.length;
        const endNum = startNum + count - 1;
        
        let content = `Questions ${startNum}${count > 1 ? '-' + endNum : ''}\n\n`;
        content += `Complete the sentence${count > 1 ? 's' : ''} below.\n`;
        content += `Choose NO MORE THAN ONE WORD from the list for each answer.\n\n`;
        
        // Add sentences
        this.sentences.forEach((sentence, index) => {
            const questionNum = startNum + index;
            const processedText = sentence.text.replace(/\[GAP\]/g, '[____' + questionNum + '____]');
            content += `${questionNum}. ${processedText}\n`;
        });
        
        return content;
    }
};

// Make it globally available
window.SentenceCompletionManager = SentenceCompletionManager;

// Auto-initialize when question type changes to sentence_completion
document.addEventListener('DOMContentLoaded', function() {
    const questionType = document.getElementById('question_type');
    if (questionType && questionType.value === 'sentence_completion') {
        setTimeout(() => {
            const card = document.getElementById('sentence-completion-card');
            if (card && card.style.display !== 'none') {
                SentenceCompletionManager.init();
            }
        }, 100);
    }
});
