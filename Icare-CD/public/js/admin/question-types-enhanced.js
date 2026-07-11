// Enhanced IELTS Map/Plan/Diagram Question Handler
const IELTSDiagramHandler = {
    // Current diagram data
    currentDiagram: {
        imageUrl: null,
        labels: [],
        dropdownOptions: [],
        questionNumbers: []
    },

    // Initialize the diagram handler
    init() {
        console.log('Initializing IELTS Diagram Handler');
        const panel = document.getElementById('diagram-panel');
        if (panel) {
            panel.style.display = 'block';
            this.setupDiagramUploader();
            this.setupLabelManager();
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

    // Render the diagram preview with interactive features
    renderDiagramPreview() {
        const preview = document.getElementById('diagram-preview');
        preview.innerHTML = `
            <div class="diagram-container" style="position: relative; display: inline-block;">
                <img src="${this.currentDiagram.imageUrl}" 
                     id="diagram-img" 
                     class="max-w-full border-2 border-gray-300 rounded-lg"
                     style="max-height: 500px;">
                <div id="label-markers"></div>
            </div>
            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-800 font-medium mb-2">Instructions:</p>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Click on the image to place a label marker</li>
                    <li>• Each marker will be assigned a question number</li>
                    <li>• Configure the answer options below</li>
                    <li>• You can drag markers to adjust their position</li>
                </ul>
            </div>
        `;

        this.setupImageClickHandler();
        this.refreshLabelMarkers();
    },

    // Setup click handler for adding labels
    setupImageClickHandler() {
        const img = document.getElementById('diagram-img');
        if (!img) return;

        img.addEventListener('click', (e) => {
            const rect = img.getBoundingClientRect();
            const x = Math.round(((e.clientX - rect.left) / rect.width) * 100);
            const y = Math.round(((e.clientY - rect.top) / rect.height) * 100);
            
            this.addLabel(x, y);
        });
    },

    // Add a new label to the diagram
    addLabel(x, y) {
        const questionNumber = this.getNextQuestionNumber();
        const label = {
            id: Date.now(),
            x: x,
            y: y,
            questionNumber: questionNumber,
            correctAnswer: ''
        };

        this.currentDiagram.labels.push(label);
        this.refreshLabelMarkers();
        this.updateLabelsPanel();
    },

    // Get the next available question number
    getNextQuestionNumber() {
        const startNumber = parseInt(document.getElementById('start-question-number')?.value || 1);
        return startNumber + this.currentDiagram.labels.length - 1;
    },

    // Refresh all label markers on the diagram
    refreshLabelMarkers() {
        const container = document.getElementById('label-markers');
        if (!container) return;

        container.innerHTML = '';
        this.currentDiagram.labels.forEach((label, index) => {
            const marker = this.createLabelMarker(label, index);
            container.appendChild(marker);
        });
    },

    // Create a single label marker
    createLabelMarker(label, index) {
        const marker = document.createElement('div');
        marker.className = 'label-marker';
        marker.dataset.labelId = label.id;
        marker.style.cssText = `
            position: absolute;
            left: ${label.x}%;
            top: ${label.y}%;
            width: 24px;
            height: 24px;
            background: #1e40af;
            color: white;
            border: 2px solid white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            transform: translate(-50%, -50%);
            cursor: move;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            z-index: 10;
            user-select: none;
        `;
        marker.textContent = label.questionNumber;

        // Make marker draggable
        this.makeDraggable(marker, label);

        // Add click handler for selection
        marker.addEventListener('click', (e) => {
            e.stopPropagation();
            this.selectLabel(label.id);
        });

        return marker;
    },

    // Make a marker draggable
    makeDraggable(marker, label) {
        let isDragging = false;
        let startX, startY;

        marker.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            marker.style.cursor = 'grabbing';
            e.preventDefault();
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            const img = document.getElementById('diagram-img');
            const rect = img.getBoundingClientRect();
            
            const newX = Math.round(((e.clientX - rect.left) / rect.width) * 100);
            const newY = Math.round(((e.clientY - rect.top) / rect.height) * 100);
            
            // Keep within bounds
            if (newX >= 0 && newX <= 100 && newY >= 0 && newY <= 100) {
                label.x = newX;
                label.y = newY;
                marker.style.left = newX + '%';
                marker.style.top = newY + '%';
            }
        });

        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                marker.style.cursor = 'move';
                this.updateLabelsPanel();
            }
        });
    },

    // Setup the labels management panel
    setupLabelManager() {
        // Create the enhanced labels panel
        const container = document.getElementById('hotspots-container');
        if (!container) return;

        container.innerHTML = `
            <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Starting Question Number
                        </label>
                        <input type="number" 
                               id="start-question-number" 
                               name="start_question_number"
                               value="1" 
                               min="1"
                               class="w-full px-3 py-2 border rounded-md text-sm"
                               onchange="IELTSDiagramHandler.updateQuestionNumbers()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Answer Type
                        </label>
                        <select id="answer-type" 
                                name="diagram_answer_type"
                                class="w-full px-3 py-2 border rounded-md text-sm"
                                onchange="IELTSDiagramHandler.updateAnswerType()">
                            <option value="dropdown">Dropdown List (Official IELTS Style)</option>
                            <option value="text">Text Input</option>
                        </select>
                    </div>
                </div>
                
                <div id="dropdown-options-section" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Dropdown Options (students will select from these)
                    </label>
                    <div id="dropdown-options-list" class="space-y-2 mb-3">
                        <!-- Options will be added here -->
                    </div>
                    <button type="button" 
                            onclick="IELTSDiagramHandler.addDropdownOption()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        + Add Option
                    </button>
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="font-medium text-gray-900">Label Configuration</h4>
                <div id="labels-list">
                    <!-- Label configurations will be added here -->
                </div>
            </div>
        `;

        // Add some default dropdown options
        this.addDropdownOption('entrance');
        this.addDropdownOption('car park');
        this.addDropdownOption('reception');
        this.addDropdownOption('disabled access');
    },

    // Add a dropdown option
    addDropdownOption(defaultValue = '') {
        const container = document.getElementById('dropdown-options-list');
        const index = this.currentDiagram.dropdownOptions.length;
        
        const optionDiv = document.createElement('div');
        optionDiv.className = 'flex gap-2';
        optionDiv.innerHTML = `
            <span class="w-8 text-center py-2 bg-gray-100 rounded font-medium">
                ${String.fromCharCode(65 + index)}
            </span>
            <input type="text" 
                   name="dropdown_options[${index}]"
                   value="${defaultValue}"
                   placeholder="Enter option ${String.fromCharCode(65 + index)}"
                   class="flex-1 px-3 py-2 border rounded-md text-sm"
                   onchange="IELTSDiagramHandler.updateDropdownOption(${index}, this.value)"
                   required>
            <button type="button" 
                    onclick="IELTSDiagramHandler.removeDropdownOption(${index})"
                    class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                Remove
            </button>
        `;
        
        container.appendChild(optionDiv);
        this.currentDiagram.dropdownOptions.push(defaultValue);
        this.updateLabelsPanel();
    },

    // Update dropdown option value
    updateDropdownOption(index, value) {
        this.currentDiagram.dropdownOptions[index] = value;
        this.updateLabelsPanel();
    },

    // Remove dropdown option
    removeDropdownOption(index) {
        this.currentDiagram.dropdownOptions.splice(index, 1);
        this.refreshDropdownOptions();
        this.updateLabelsPanel();
    },

    // Refresh dropdown options display
    refreshDropdownOptions() {
        const container = document.getElementById('dropdown-options-list');
        container.innerHTML = '';
        
        this.currentDiagram.dropdownOptions.forEach((option, index) => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'flex gap-2';
            optionDiv.innerHTML = `
                <span class="w-8 text-center py-2 bg-gray-100 rounded font-medium">
                    ${String.fromCharCode(65 + index)}
                </span>
                <input type="text" 
                       name="dropdown_options[${index}]"
                       value="${option}"
                       placeholder="Enter option ${String.fromCharCode(65 + index)}"
                       class="flex-1 px-3 py-2 border rounded-md text-sm"
                       onchange="IELTSDiagramHandler.updateDropdownOption(${index}, this.value)"
                       required>
                <button type="button" 
                        onclick="IELTSDiagramHandler.removeDropdownOption(${index})"
                        class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                    Remove
                </button>
            `;
            container.appendChild(optionDiv);
        });
    },

    // Update all question numbers when start number changes
    updateQuestionNumbers() {
        const startNumber = parseInt(document.getElementById('start-question-number').value);
        
        this.currentDiagram.labels.forEach((label, index) => {
            label.questionNumber = startNumber + index;
        });
        
        this.refreshLabelMarkers();
        this.updateLabelsPanel();
    },

    // Update answer type (dropdown vs text)
    updateAnswerType() {
        const answerType = document.getElementById('answer-type').value;
        const dropdownSection = document.getElementById('dropdown-options-section');
        
        if (answerType === 'dropdown') {
            dropdownSection.style.display = 'block';
        } else {
            dropdownSection.style.display = 'none';
        }
        
        this.updateLabelsPanel();
    },

    // Update the labels configuration panel
    updateLabelsPanel() {
        const container = document.getElementById('labels-list');
        const answerType = document.getElementById('answer-type').value;
        
        container.innerHTML = '';
        
        if (this.currentDiagram.labels.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-sm">Click on the diagram to add labels</p>';
            return;
        }
        
        this.currentDiagram.labels.forEach((label, index) => {
            const labelDiv = document.createElement('div');
            labelDiv.className = 'flex items-center gap-3 p-3 bg-white border rounded-lg';
            labelDiv.dataset.labelId = label.id;
            
            let answerInput = '';
            if (answerType === 'dropdown') {
                // Create dropdown selector
                answerInput = `
                    <select name="diagram_answers[${index}]" 
                            class="flex-1 px-3 py-2 border rounded-md text-sm"
                            onchange="IELTSDiagramHandler.updateLabelAnswer(${label.id}, this.value)"
                            required>
                        <option value="">Select correct answer</option>
                        ${this.currentDiagram.dropdownOptions.map((option, optIndex) => `
                            <option value="${optIndex}" ${label.correctAnswer == optIndex ? 'selected' : ''}>
                                ${String.fromCharCode(65 + optIndex)} - ${option}
                            </option>
                        `).join('')}
                    </select>
                `;
            } else {
                // Create text input
                answerInput = `
                    <input type="text" 
                           name="diagram_answers[${index}]"
                           value="${label.correctAnswer || ''}"
                           placeholder="Enter correct answer"
                           class="flex-1 px-3 py-2 border rounded-md text-sm"
                           onchange="IELTSDiagramHandler.updateLabelAnswer(${label.id}, this.value)"
                           required>
                `;
            }
            
            labelDiv.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                        ${label.questionNumber}
                    </span>
                    <span class="text-sm text-gray-600">
                        Position: (${label.x}%, ${label.y}%)
                    </span>
                </div>
                <input type="hidden" name="diagram_labels[${index}][x]" value="${label.x}">
                <input type="hidden" name="diagram_labels[${index}][y]" value="${label.y}">
                <input type="hidden" name="diagram_labels[${index}][number]" value="${label.questionNumber}">
                ${answerInput}
                <button type="button" 
                        onclick="IELTSDiagramHandler.removeLabel(${label.id})"
                        class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                    Remove
                </button>
            `;
            
            container.appendChild(labelDiv);
        });
    },

    // Update label answer
    updateLabelAnswer(labelId, value) {
        const label = this.currentDiagram.labels.find(l => l.id === labelId);
        if (label) {
            label.correctAnswer = value;
        }
    },

    // Remove a label
    removeLabel(labelId) {
        this.currentDiagram.labels = this.currentDiagram.labels.filter(l => l.id !== labelId);
        this.updateQuestionNumbers(); // This will refresh everything with correct numbers
    },

    // Select a label (highlight it)
    selectLabel(labelId) {
        // Remove previous selection
        document.querySelectorAll('.label-marker').forEach(marker => {
            marker.style.border = '2px solid white';
        });
        
        // Highlight selected
        const marker = document.querySelector(`[data-label-id="${labelId}"]`);
        if (marker) {
            marker.style.border = '2px solid #fbbf24';
        }
        
        // Scroll to label config
        const labelConfig = document.querySelector(`[data-label-id="${labelId}"]`);
        if (labelConfig) {
            labelConfig.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    },

    // Prepare data for form submission
    prepareSubmissionData() {
        const data = {
            diagram_type: 'map_plan_diagram',
            answer_type: document.getElementById('answer-type').value,
            start_number: parseInt(document.getElementById('start-question-number').value),
            dropdown_options: this.currentDiagram.dropdownOptions,
            labels: this.currentDiagram.labels.map((label, index) => ({
                x: label.x,
                y: label.y,
                question_number: label.questionNumber,
                correct_answer: label.correctAnswer
            }))
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
window.IELTSDiagramHandler = IELTSDiagramHandler;

// Export the existing handlers with modifications
window.QuestionTypeHandlers = {
    // Initialize based on question type
    init(questionType) {
        console.log('Initializing handler for:', questionType);

        // Hide all special panels first
        document.querySelectorAll('.type-specific-panel').forEach(panel => {
            panel.style.display = 'none';
        });

        // Show relevant panel
        switch (questionType) {
            case 'matching':
                this.initMatching();
                break;
            case 'form_completion':
                this.initFormCompletion();
                break;
            case 'plan_map_diagram':
                IELTSDiagramHandler.init();
                break;
        }
    },

    // Matching Questions Handler
    initMatching() {
        console.log('Initializing matching question');
        const panel = document.getElementById('matching-panel');
        if (panel) {
            panel.style.display = 'block';
        }

        // Create default pairs if empty
        const container = document.getElementById('matching-pairs-container');
        if (container && container.children.length === 0) {
            // Add 3 default pairs
            for (let i = 0; i < 3; i++) {
                this.addMatchingPair();
            }
        }
    },

    addMatchingPair() {
        const container = document.getElementById('matching-pairs-container');
        if (!container) {
            console.error('Matching pairs container not found');
            return;
        }

        const index = container.children.length;

        const pairDiv = document.createElement('div');
        pairDiv.className = 'matching-pair flex gap-3 mb-3';
        pairDiv.innerHTML = `
            <div class="flex-1">
                <input type="text" 
                       name="matching_pairs[${index}][left]" 
                       placeholder="Question/Item ${index + 1}"
                       class="w-full px-3 py-2 border rounded-md text-sm"
                       required>
            </div>
            <div class="flex items-center text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </div>
            <div class="flex-1">
                <input type="text" 
                       name="matching_pairs[${index}][right]" 
                       placeholder="Match ${String.fromCharCode(65 + index)}"
                       class="w-full px-3 py-2 border rounded-md text-sm"
                       required>
            </div>
            <button type="button" onclick="QuestionTypeHandlers.removeMatchingPair(this)" 
                    class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                Remove
            </button>
        `;

        container.appendChild(pairDiv);
        console.log('Added matching pair', index + 1);
    },

    removeMatchingPair(button) {
        button.closest('.matching-pair').remove();
        this.reindexMatchingPairs();
    },

    reindexMatchingPairs() {
        const pairs = document.querySelectorAll('.matching-pair');
        pairs.forEach((pair, index) => {
            pair.querySelector('input[name*="[left]"]').name = `matching_pairs[${index}][left]`;
            pair.querySelector('input[name*="[right]"]').name = `matching_pairs[${index}][right]`;
            pair.querySelector('input[name*="[left]"]').placeholder = `Question/Item ${index + 1}`;
            pair.querySelector('input[name*="[right]"]').placeholder = `Match ${String.fromCharCode(65 + index)}`;
        });
    },

    // Form Completion Handler
    initFormCompletion() {
        console.log('Initializing form completion question');
        const panel = document.getElementById('form-completion-panel');
        if (panel) {
            panel.style.display = 'block';
        }

        // Initialize form builder
        const container = document.getElementById('form-fields-container');
        if (container && container.children.length === 0) {
            // Add 3 default fields
            for (let i = 0; i < 3; i++) {
                this.addFormField();
            }
        }
    },

    addFormField() {
        const container = document.getElementById('form-fields-container');
        if (!container) {
            console.error('Form fields container not found');
            return;
        }

        const index = container.children.length;

        const fieldDiv = document.createElement('div');
        fieldDiv.className = 'form-field flex gap-3 mb-3';
        fieldDiv.innerHTML = `
            <div class="flex-1">
                <input type="text" 
                       name="form_structure[fields][${index}][label]" 
                       placeholder="Field Label (e.g., Name, Email)"
                       class="w-full px-3 py-2 border rounded-md text-sm"
                       required>
            </div>
            <div class="flex-1">
                <input type="text" 
                       name="form_structure[fields][${index}][answer]" 
                       placeholder="Correct Answer"
                       class="w-full px-3 py-2 border rounded-md text-sm"
                       required>
            </div>
            <button type="button" onclick="QuestionTypeHandlers.removeFormField(this)" 
                    class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                Remove
            </button>
        `;

        container.appendChild(fieldDiv);
        console.log('Added form field', index + 1);
    },

    removeFormField(button) {
        button.closest('.form-field').remove();
        this.reindexFormFields();
    },

    reindexFormFields() {
        const fields = document.querySelectorAll('.form-field');
        fields.forEach((field, index) => {
            const labelInput = field.querySelector('input[name*="[label]"]');
            const answerInput = field.querySelector('input[name*="[answer]"]');

            if (labelInput) {
                labelInput.name = `form_structure[fields][${index}][label]`;
            }
            if (answerInput) {
                answerInput.name = `form_structure[fields][${index}][answer]`;
            }
        });
    }
};
