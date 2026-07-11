// Enhanced Sentence Completion Manager
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
        
        // Add default options
        // const defaultOptions = [
        //     'although', 'because', 'but', 'however', 'if', 'since', 'so', 'therefore', 'when', 'while'
        // ];
        
        // defaultOptions.forEach(option => this.addOption(option));
        
        // Add 3 default sentences
        // for (let i = 0; i < 3; i++) {
        //     this.addSentence();
        // }
        
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Sentence (use [GAP] for dropdown placement)
                    </label>
                    <textarea
                        data-sentence-index="${index}"
                        class="sentence-input w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                        rows="2"
                        placeholder="Example: The meeting was cancelled [GAP] the weather was bad."
                        onkeyup="SentenceCompletionManager.updatePreview()">${text}</textarea>
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
        
        // Update counter
        document.getElementById('sentence-count').textContent = `${this.sentenceCount} sentences`;
        
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
        
        // Update counter
        document.getElementById('sentence-count').textContent = `${this.sentenceCount} sentences`;
        
        // Update preview and data
        this.updatePreview();
        this.updateData();
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
            
            if (textarea && textarea.value.trim()) {
                actualSentenceCount++;
                const text = textarea.value;
                const questionNum = startNum + actualSentenceCount - 1;
                
                // Replace [GAP] with dropdown representation
                const processedText = text.replace(/\[GAP\]/g, '<span class="gap-dropdown">______</span>');
                
                html += `
                    <div class="sentence-preview">
                        <strong>${questionNum}.</strong> ${processedText}
                        ${select && select.value ? `<span class="ml-2 text-green-600 text-xs">(Answer: ${select.value})</span>` : ''}
                    </div>
                `;
            }
        });
        
        html += '</div>';
        
        // Add instruction text with correct count
        const endNum = startNum + actualSentenceCount - 1;
        const instructionText = actualSentenceCount > 0 ? `<div class="mb-4 p-3 bg-blue-50 rounded text-sm">
            <strong>Questions ${startNum}-${endNum}</strong><br>
            Complete the sentences below.<br>
            Choose <strong>NO MORE THAN ONE WORD</strong> from the list for each answer.
        </div>` : '';
        
        previewContainer.innerHTML = instructionText + html;
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
        
        // Collect all sentences
        this.sentences = [];
        const sentenceDivs = document.querySelectorAll('[data-sentence-index]');
        const startNum = parseInt(document.getElementById('sc_start_number')?.value) || 1;
        
        sentenceDivs.forEach((sentenceDiv, index) => {
            const textarea = sentenceDiv.querySelector('textarea');
            const select = sentenceDiv.querySelector('select');
            
            if (textarea) {
                this.sentences.push({
                    questionNumber: startNum + index,
                    text: textarea.value,
                    correctAnswer: select ? select.value : ''
                });
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
        
        let content = `Questions ${startNum}-${endNum}\n\n`;
        content += `Complete the sentences below.\n`;
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
