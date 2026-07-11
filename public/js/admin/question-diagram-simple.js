// Simplified IELTS Diagram Question Handler with Answers
const SimpleDiagramHandler = {
    // Current diagram data
    currentDiagram: {
        imageUrl: null,
        dropdownOptions: [],
        startNumber: 1,
        correctAnswers: {}
    },

    // Initialize the diagram handler
    init() {
        console.log('Initializing Simple Diagram Handler');
        const panel = document.getElementById('diagram-panel');
        if (panel) {
            panel.style.display = 'block';
            this.setupDiagramUploader();
            this.setupOptionsManager();
        }
    },

    // Setup diagram image uploader
    setupDiagramUploader() {
        const input = document.getElementById('diagram-image');
        const preview = document.getElementById('diagram-preview');
        
        if (!input || !preview) {
            console.error('Diagram elements not found');
            return;
        }

        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.currentDiagram.imageUrl = e.target.result;
                    this.renderDiagramPreview();
                };
                reader.readAsDataURL(file);
            }
        });
    },

    // Render the diagram preview
    renderDiagramPreview() {
        const preview = document.getElementById('diagram-preview');
        preview.innerHTML = `
            <div class="diagram-container">
                <img src="${this.currentDiagram.imageUrl}" 
                     id="diagram-img" 
                     class="max-w-full border-2 border-gray-300 rounded-lg"
                     style="max-height: 400px;">
            </div>
        `;
    },

    // Setup the options management panel
    setupOptionsManager() {
        const container = document.getElementById('hotspots-container');
        if (!container) return;

        container.innerHTML = `
            <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Starting Question Number
                    </label>
                    <input type="number" 
                           id="start-question-number" 
                           name="start_question_number"
                           value="1" 
                           min="1"
                           class="w-full px-3 py-2 border rounded-md text-sm"
                           onchange="SimpleDiagramHandler.updateStartNumber()">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Answer Options (A, B, C, D...)
                    </label>
                    <div id="dropdown-options-list" class="space-y-2 mb-3">
                        <!-- Options will be added here -->
                    </div>
                    <button type="button" 
                            onclick="SimpleDiagramHandler.addOption()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        + Add Option
                    </button>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Correct Answers for Each Question
                    </label>
                    <div id="correct-answers-list" class="space-y-2">
                        <!-- Correct answers will be shown here -->
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-sm text-gray-600 mb-2">
                    <strong>Note:</strong> After adding options, specify which option (A, B, C, etc.) is correct for each question number.
                </p>
            </div>
        `;

        // Add some default options
        this.addOption('entrance');
        this.addOption('car park');
        this.addOption('reception');
        this.addOption('shop');
        
        // Update answer selectors
        this.updateAnswerSelectors();
    },

    // Update start number
    updateStartNumber() {
        this.currentDiagram.startNumber = parseInt(document.getElementById('start-question-number').value) || 1;
        this.updateAnswerSelectors();
    },

    // Add an option
    addOption(defaultValue = '') {
        const container = document.getElementById('dropdown-options-list');
        const index = this.currentDiagram.dropdownOptions.length;
        
        const optionDiv = document.createElement('div');
        optionDiv.className = 'flex gap-2';
        optionDiv.innerHTML = `
            <span class="w-8 text-center py-2 bg-gray-100 rounded font-medium">
                ${String.fromCharCode(65 + index)}
            </span>
            <input type="text" 
                   name="dropdown_options[]"
                   value="${defaultValue}"
                   placeholder="Enter option ${String.fromCharCode(65 + index)}"
                   class="flex-1 px-3 py-2 border rounded-md text-sm"
                   onchange="SimpleDiagramHandler.updateOption(${index}, this.value)"
                   required>
            <button type="button" 
                    onclick="SimpleDiagramHandler.removeOption(${index})"
                    class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                Remove
            </button>
        `;
        
        container.appendChild(optionDiv);
        this.currentDiagram.dropdownOptions.push(defaultValue);
        
        // Update answer selectors when options change
        this.updateAnswerSelectors();
    },

    // Update option value
    updateOption(index, value) {
        this.currentDiagram.dropdownOptions[index] = value;
    },

    // Remove option
    removeOption(index) {
        this.currentDiagram.dropdownOptions.splice(index, 1);
        this.refreshOptions();
        this.updateAnswerSelectors();
    },

    // Refresh options display
    refreshOptions() {
        const container = document.getElementById('dropdown-options-list');
        container.innerHTML = '';
        
        const tempOptions = [...this.currentDiagram.dropdownOptions];
        this.currentDiagram.dropdownOptions = [];
        
        tempOptions.forEach(option => {
            this.addOption(option);
        });
    },

    // Update answer selectors
    updateAnswerSelectors() {
        const container = document.getElementById('correct-answers-list');
        if (!container) return;
        
        container.innerHTML = '';
        
        const numQuestions = this.currentDiagram.dropdownOptions.length;
        const startNum = this.currentDiagram.startNumber;
        
        for (let i = 0; i < numQuestions; i++) {
            const questionNum = startNum + i;
            const answerDiv = document.createElement('div');
            answerDiv.className = 'flex items-center gap-3';
            
            let optionsHtml = '<option value="">Select correct answer</option>';
            this.currentDiagram.dropdownOptions.forEach((option, idx) => {
                const selected = this.currentDiagram.correctAnswers[i] === idx ? 'selected' : '';
                optionsHtml += `<option value="${idx}" ${selected}>${String.fromCharCode(65 + idx)} - ${option}</option>`;
            });
            
            answerDiv.innerHTML = `
                <span class="w-16 text-sm font-medium">Q${questionNum}:</span>
                <select name="correct_answers[${i}]" 
                        class="flex-1 px-3 py-2 border rounded-md text-sm"
                        onchange="SimpleDiagramHandler.updateCorrectAnswer(${i}, this.value)"
                        required>
                    ${optionsHtml}
                </select>
            `;
            
            container.appendChild(answerDiv);
        }
    },

    // Update correct answer
    updateCorrectAnswer(questionIndex, answerValue) {
        this.currentDiagram.correctAnswers[questionIndex] = answerValue ? parseInt(answerValue) : null;
    },

    // Prepare data for form submission
    prepareSubmissionData() {
        const data = {
            answer_type: 'dropdown',
            start_number: this.currentDiagram.startNumber,
            dropdown_options: this.currentDiagram.dropdownOptions.filter(opt => opt.trim() !== ''),
            correct_answers: this.currentDiagram.correctAnswers
        };
        
        // Create hidden input for JSON data
        let hiddenInput = document.getElementById('diagram_data_json');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.id = 'diagram_data_json';
            hiddenInput.name = 'diagram_hotspots_json';
            document.getElementById('questionForm').appendChild(hiddenInput);
        }
        hiddenInput.value = JSON.stringify(data);
        
        console.log('Diagram submission data:', data);
        return data;
    }
};

// Make it globally available
window.SimpleDiagramHandler = SimpleDiagramHandler;