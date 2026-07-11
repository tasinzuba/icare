// Professional IELTS Diagram Question Handler with Multiple Sub-Questions
const ProfessionalDiagramHandler = {
    // Current diagram data structure to support multiple sub-questions
    currentDiagram: {
        imageUrl: null,
        questions: [], // Array of sub-questions
        dropdownOptions: []
    },

    // Initialize the handler
    init() {
        console.log('Initializing Professional Diagram Handler');
        const panel = document.getElementById('diagram-panel');
        if (panel) {
            panel.style.display = 'block';
            this.setupDiagramInterface();
            this.hideUnnecessaryFields();
        }
    },

    // Get next question number from the form
    getNextQuestionNumber() {
        const questionNumberInput = document.querySelector('input[name="order_number"]');
        return questionNumberInput ? questionNumberInput.value : '1';
    },

    // Hide unnecessary fields for diagram questions
    hideUnnecessaryFields() {
        // Hide question number field
        const questionNumberDiv = document.querySelector('input[name="order_number"]')?.closest('.mb-4');
        if (questionNumberDiv) {
            questionNumberDiv.style.display = 'none';
        }
        
        // Hide marks field
        const marksDiv = document.querySelector('input[name="marks"]')?.closest('.mb-4');
        if (marksDiv) {
            marksDiv.style.display = 'none';
        }
    },

    // Setup the professional diagram interface
    setupDiagramInterface() {
        const container = document.getElementById('hotspots-container');
        if (!container) return;

        container.innerHTML = `
            <div class="space-y-6">
                <!-- Image Upload Section -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Upload Diagram/Map/Plan Image <span class="text-red-500">*</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                        <input type="file" 
                               id="diagram-image" 
                               name="diagram_image" 
                               accept="image/*"
                               onchange="ProfessionalDiagramHandler.handleImageUpload(event)"
                               class="w-full px-3 py-2 text-sm">
                        <p class="text-xs text-gray-500 mt-2">
                            Upload a diagram with numbered/lettered locations
                        </p>
                    </div>
                </div>

                <!-- Diagram Preview -->
                <div id="diagram-preview-area" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Diagram Preview
                    </label>
                    <div class="relative inline-block border rounded-lg overflow-hidden">
                        <img id="diagram-preview-img" class="max-w-full" style="max-height: 400px;">
                    </div>
                </div>

                <!-- Questions Configuration -->
                <div id="questions-config" class="hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <h4 class="text-sm font-semibold text-blue-900 mb-2">Question Setup</h4>
                        <p class="text-sm text-blue-800">
                            This will create multiple sub-questions (e.g., 11-15) for labeling the diagram.
                        </p>
                    </div>

                    <!-- Starting Question Number -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Starting Question Number
                        </label>
                        <input type="number" 
                               id="start-question-number" 
                               value="${this.getNextQuestionNumber()}" 
                               min="1"
                               class="w-32 px-3 py-2 border rounded-md text-sm"
                               onchange="ProfessionalDiagramHandler.updateQuestionNumbers()">
                    </div>

                    <!-- Number of Questions -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Number of Locations to Label
                        </label>
                        <input type="number" 
                               id="num-questions" 
                               value="5" 
                               min="2"
                               max="10"
                               class="w-32 px-3 py-2 border rounded-md text-sm"
                               onchange="ProfessionalDiagramHandler.updateSubQuestions()">
                    </div>

                    <!-- Answer Options -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Available Answer Options
                        </label>
                        <div id="answer-options-list" class="space-y-2 mb-3">
                            <!-- Options will be added here -->
                        </div>
                        <button type="button" 
                                onclick="ProfessionalDiagramHandler.addAnswerOption()" 
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                            + Add Answer Option
                        </button>
                    </div>

                    <!-- Sub-Questions Setup -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Configure Each Question
                        </label>
                        <div id="sub-questions-list" class="space-y-3">
                            <!-- Sub-questions will be added here -->
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Initialize with default options
        this.addAnswerOption('entrance');
        this.addAnswerOption('reception');
        this.addAnswerOption('gift shop');
        this.addAnswerOption('café');
        this.addAnswerOption('exhibition hall');
        
        // Initialize sub-questions
        this.updateSubQuestions();
    },

    // Handle image upload
    handleImageUpload(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.currentDiagram.imageUrl = e.target.result;
                document.getElementById('diagram-preview-img').src = e.target.result;
                document.getElementById('diagram-preview-area').classList.remove('hidden');
                document.getElementById('questions-config').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    },

    // Add answer option
    addAnswerOption(defaultValue = '') {
        const container = document.getElementById('answer-options-list');
        const index = this.currentDiagram.dropdownOptions.length;
        
        const optionDiv = document.createElement('div');
        optionDiv.className = 'flex gap-2';
        optionDiv.innerHTML = `
            <span class="w-8 text-center py-2 bg-gray-100 rounded font-medium">
                ${String.fromCharCode(65 + index)}
            </span>
            <input type="text" 
                   value="${defaultValue}"
                   placeholder="Enter location name"
                   class="flex-1 px-3 py-2 border rounded-md text-sm"
                   onchange="ProfessionalDiagramHandler.updateOption(${index}, this.value)"
                   required>
            <button type="button" 
                    onclick="ProfessionalDiagramHandler.removeOption(${index})"
                    class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                ×
            </button>
        `;
        
        container.appendChild(optionDiv);
        this.currentDiagram.dropdownOptions.push(defaultValue);
        
        // Update all sub-question selectors
        this.updateAllAnswerSelectors();
    },

    // Update option value
    updateOption(index, value) {
        this.currentDiagram.dropdownOptions[index] = value;
        this.updateAllAnswerSelectors();
    },

    // Remove option
    removeOption(index) {
        this.currentDiagram.dropdownOptions.splice(index, 1);
        this.refreshOptions();
        this.updateAllAnswerSelectors();
    },

    // Refresh options display
    refreshOptions() {
        const container = document.getElementById('answer-options-list');
        container.innerHTML = '';
        
        const tempOptions = [...this.currentDiagram.dropdownOptions];
        this.currentDiagram.dropdownOptions = [];
        
        tempOptions.forEach(option => {
            this.addAnswerOption(option);
        });
    },

    // Update sub-questions based on count
    updateSubQuestions() {
        const numQuestions = parseInt(document.getElementById('num-questions').value) || 5;
        const startNum = parseInt(document.getElementById('start-question-number').value) || 1;
        const container = document.getElementById('sub-questions-list');
        
        // Auto-update marks when number of questions changes
        const marksInput = document.querySelector('input[name="marks"]');
        if (marksInput) {
            marksInput.value = numQuestions;
        }
        
        // Clear existing questions
        container.innerHTML = '';
        this.currentDiagram.questions = [];
        
        // Create sub-questions
        for (let i = 0; i < numQuestions; i++) {
            const questionNum = startNum + i;
            const subQuestion = {
                number: questionNum,
                label: String.fromCharCode(65 + i), // A, B, C, etc.
                correctAnswer: null
            };
            
            this.currentDiagram.questions.push(subQuestion);
            
            const questionDiv = document.createElement('div');
            questionDiv.className = 'bg-gray-50 border rounded-lg p-4';
            questionDiv.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                            ${subQuestion.label}
                        </span>
                        <span class="font-medium">Question ${questionNum}</span>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-sm text-gray-600 mb-1">Correct Answer:</label>
                    <select class="w-full px-3 py-2 border rounded-md text-sm"
                            onchange="ProfessionalDiagramHandler.updateCorrectAnswer(${i}, this.value)">
                        <option value="">Select correct location</option>
                        ${this.getOptionsHtml()}
                    </select>
                </div>
            `;
            
            container.appendChild(questionDiv);
        }
    },

    // Update question numbers when start number changes
    updateQuestionNumbers() {
        this.updateSubQuestions();
    },

    // Get options HTML for selectors
    getOptionsHtml() {
        return this.currentDiagram.dropdownOptions.map((option, idx) => 
            `<option value="${idx}">${String.fromCharCode(65 + idx)} - ${option}</option>`
        ).join('');
    },

    // Update all answer selectors when options change
    updateAllAnswerSelectors() {
        const selects = document.querySelectorAll('#sub-questions-list select');
        const optionsHtml = this.getOptionsHtml();
        
        selects.forEach((select, index) => {
            const currentValue = select.value;
            select.innerHTML = '<option value="">Select correct location</option>' + optionsHtml;
            select.value = currentValue;
        });
    },

    // Update correct answer for a sub-question
    updateCorrectAnswer(questionIndex, answerValue) {
        if (this.currentDiagram.questions[questionIndex]) {
            this.currentDiagram.questions[questionIndex].correctAnswer = 
                answerValue ? parseInt(answerValue) : null;
        }
    },

    // Prepare data for submission
    prepareSubmissionData() {
        const startNum = parseInt(document.getElementById('start-question-number').value) || 1;
        const numQuestions = parseInt(document.getElementById('num-questions').value) || 5;
        
        // Update hidden order_number field with start number (only in create mode)
        if (!window.isEditMode) {
            const orderNumberInput = document.querySelector('input[name="order_number"]');
            if (orderNumberInput) {
                orderNumberInput.value = startNum;
            }
        }
        
        // Create the data structure that supports multiple sub-questions
        const data = {
            answer_type: 'dropdown',
            start_number: startNum,
            dropdown_options: this.currentDiagram.dropdownOptions.filter(opt => opt.trim() !== ''),
            correct_answers: {},
            sub_questions: []
        };
        
        // Build correct answers and sub-questions
        this.currentDiagram.questions.forEach((question, index) => {
            data.correct_answers[index] = question.correctAnswer;
            data.sub_questions.push({
                number: question.number,
                label: question.label,
                correct_answer_index: question.correctAnswer
            });
        });
        
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
        
        // Auto-generate question content if not provided
        const contentTextarea = document.getElementById('content');
        if (contentTextarea && !contentTextarea.value.trim()) {
            const endNum = startNum + numQuestions - 1;
            contentTextarea.value = `Label the diagram below.\nWrite the correct letter, A-${String.fromCharCode(65 + this.currentDiagram.dropdownOptions.length - 1)}, next to questions ${startNum}-${endNum}.`;
            
            // Trigger TinyMCE to update
            if (typeof tinymce !== 'undefined') {
                const editor = tinymce.get('content');
                if (editor) {
                    editor.setContent(contentTextarea.value);
                }
            }
        }
        
        // Update marks to reflect number of sub-questions (auto-calculate)
        const marksInput = document.querySelector('input[name="marks"]');
        if (marksInput) {
            marksInput.value = numQuestions;
        }
        
        console.log('Diagram submission data:', data);
        return data;
    }
};

// Make it globally available
window.ProfessionalDiagramHandler = ProfessionalDiagramHandler;