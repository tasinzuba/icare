// Reading Question Types Handler
window.ReadingQuestionTypes = {
    dragDropData: {
        dropZones: [],
        options: [],
        allowReuse: true
    },
    
    init(questionType) {
        console.log('ReadingQuestionTypes.init:', questionType);
        
        // Hide all reading type-specific panels
        document.querySelectorAll('.type-specific-panel').forEach(panel => {
            panel.style.display = 'none';
        });
        
        // Reset form state
        this.resetFormState();
        
        // Show appropriate panel based on question type
        if (questionType === 'drag_drop') {
            this.initDragDrop();
        }
    },
    
    resetFormState() {
        // Reset drag drop data
        this.dragDropData = {
            dropZones: [],
            options: [],
            allowReuse: true
        };
    },
    
    // Initialize Drag & Drop
    initDragDrop() {
        const panel = document.getElementById('reading-drag-drop-panel');
        if (panel) {
            panel.style.display = 'block';
            console.log('Reading drag-drop panel shown');
        }
        
        // Clear containers first
        const dropZonesContainer = document.getElementById('reading-drop-zones-container');
        const optionsContainer = document.getElementById('reading-draggable-options-container');
        
        if (dropZonesContainer) dropZonesContainer.innerHTML = '';
        if (optionsContainer) optionsContainer.innerHTML = '';
        
        // Reset drag drop data
        this.dragDropData = {
            dropZones: [],
            options: [],
            allowReuse: true
        };
        
        // Add default drop zones and options
        for (let i = 0; i < 3; i++) {
            this.addDropZone();
        }
        
        for (let i = 0; i < 5; i++) {
            this.addDraggableOption();
        }
        
        console.log('Drag-drop initialized with', this.dragDropData.dropZones.length, 'drop zones and', this.dragDropData.options.length, 'options');
    },
    
    // Add Drop Zone
    addDropZone() {
        const container = document.getElementById('reading-drop-zones-container');
        if (!container) {
            console.error('Drop zones container not found');
            return;
        }
        
        const index = this.dragDropData.dropZones.length;
        this.dragDropData.dropZones.push({ label: '', correctAnswer: '' });
        
        const dropZoneDiv = document.createElement('div');
        dropZoneDiv.className = 'border border-gray-300 rounded-lg p-3 bg-gray-50';
        dropZoneDiv.dataset.dropZoneIndex = index;
        dropZoneDiv.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                    ${index + 1}
                </div>
                <div class="flex-1 space-y-2">
                    <input type="text" 
                           name="drag_drop_zones[${index}][label]" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                           placeholder="Drop zone label (e.g., 'Capital of France is...')"
                           value=""
                           onchange="ReadingQuestionTypes.updateDropZoneLabel(${index}, this.value)"
                           required>
                    <input type="text" 
                           name="drag_drop_zones[${index}][answer]" 
                           class="w-full px-3 py-2 border border-indigo-300 rounded-md text-sm bg-indigo-50"
                           placeholder="Correct answer (must match one of the draggable options)"
                           value=""
                           onchange="ReadingQuestionTypes.updateDropZoneAnswer(${index}, this.value)"
                           required>
                </div>
                <button type="button" 
                        onclick="ReadingQuestionTypes.removeDropZone(${index})" 
                        class="text-red-500 hover:text-red-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        container.appendChild(dropZoneDiv);
        console.log('Added drop zone', index + 1);
    },
    
    // Add Draggable Option
    addDraggableOption() {
        const container = document.getElementById('reading-draggable-options-container');
        if (!container) {
            console.error('Draggable options container not found');
            return;
        }
        
        const index = this.dragDropData.options.length;
        this.dragDropData.options.push('');
        
        const optionDiv = document.createElement('div');
        optionDiv.className = 'flex items-center gap-3 p-2 bg-gray-50 rounded border border-gray-200';
        optionDiv.dataset.optionIndex = index;
        optionDiv.innerHTML = `
            <span class="font-medium text-gray-700 text-sm">${String.fromCharCode(65 + index)}.</span>
            <input type="text" 
                   name="drag_drop_options[]" 
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm"
                   placeholder="Option text"
                   value=""
                   onchange="ReadingQuestionTypes.updateDraggableOption(${index}, this.value)"
                   required>
            <button type="button" 
                    onclick="ReadingQuestionTypes.removeDraggableOption(${index})" 
                    class="text-red-500 hover:text-red-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        container.appendChild(optionDiv);
        console.log('Added draggable option', String.fromCharCode(65 + index));
    },
    
    // Update Drop Zone Label
    updateDropZoneLabel(index, value) {
        if (this.dragDropData.dropZones[index]) {
            this.dragDropData.dropZones[index].label = value;
            console.log('Updated drop zone', index, 'label:', value);
        }
    },
    
    // Update Drop Zone Answer
    updateDropZoneAnswer(index, value) {
        if (this.dragDropData.dropZones[index]) {
            this.dragDropData.dropZones[index].correctAnswer = value;
            console.log('Updated drop zone', index, 'answer:', value);
        }
    },
    
    // Update Draggable Option
    updateDraggableOption(index, value) {
        if (this.dragDropData.options[index] !== undefined) {
            this.dragDropData.options[index] = value;
            console.log('Updated option', index, ':', value);
        }
    },
    
    // Remove Drop Zone
    removeDropZone(index) {
        const container = document.getElementById('reading-drop-zones-container');
        const dropZone = container.querySelector(`[data-drop-zone-index="${index}"]`);
        if (dropZone) {
            dropZone.remove();
        }
        
        // Remove from data
        this.dragDropData.dropZones.splice(index, 1);
        
        // Reindex remaining drop zones
        this.reindexDropZones();
    },
    
    // Remove Draggable Option
    removeDraggableOption(index) {
        const container = document.getElementById('reading-draggable-options-container');
        const option = container.querySelector(`[data-option-index="${index}"]`);
        if (option) {
            option.remove();
        }
        
        // Remove from data
        this.dragDropData.options.splice(index, 1);
        
        // Reindex remaining options
        this.reindexDraggableOptions();
    },
    
    // Reindex Drop Zones
    reindexDropZones() {
        const container = document.getElementById('reading-drop-zones-container');
        const dropZones = container.querySelectorAll('[data-drop-zone-index]');
        
        this.dragDropData.dropZones = [];
        
        dropZones.forEach((zone, newIndex) => {
            zone.dataset.dropZoneIndex = newIndex;
            
            const numberBadge = zone.querySelector('.bg-indigo-600');
            if (numberBadge) numberBadge.textContent = newIndex + 1;
            
            const inputs = zone.querySelectorAll('input');
            inputs[0].name = `drag_drop_zones[${newIndex}][label]`;
            inputs[0].setAttribute('onchange', `ReadingQuestionTypes.updateDropZoneLabel(${newIndex}, this.value)`);
            inputs[1].name = `drag_drop_zones[${newIndex}][answer]`;
            inputs[1].setAttribute('onchange', `ReadingQuestionTypes.updateDropZoneAnswer(${newIndex}, this.value)`);
            
            this.dragDropData.dropZones.push({
                label: inputs[0].value,
                correctAnswer: inputs[1].value
            });
            
            const removeBtn = zone.querySelector('button');
            removeBtn.setAttribute('onclick', `ReadingQuestionTypes.removeDropZone(${newIndex})`);
        });
        
        console.log('Reindexed drop zones, now have:', this.dragDropData.dropZones.length);
    },
    
    // Reindex Draggable Options
    reindexDraggableOptions() {
        const container = document.getElementById('reading-draggable-options-container');
        const options = container.querySelectorAll('[data-option-index]');
        
        this.dragDropData.options = [];
        
        options.forEach((option, newIndex) => {
            option.dataset.optionIndex = newIndex;
            
            const label = option.querySelector('span');
            if (label) label.textContent = String.fromCharCode(65 + newIndex) + '.';
            
            const input = option.querySelector('input[type="text"]');
            input.setAttribute('onchange', `ReadingQuestionTypes.updateDraggableOption(${newIndex}, this.value)`);
            
            this.dragDropData.options.push(input.value);
            
            const removeBtn = option.querySelector('button');
            removeBtn.setAttribute('onclick', `ReadingQuestionTypes.removeDraggableOption(${newIndex})`);
        });
        
        console.log('Reindexed options, now have:', this.dragDropData.options.length);
    },
    
    // Prepare submission data
    prepareSubmissionData() {
        const questionType = document.getElementById('question_type').value;
        
        if (questionType === 'drag_drop') {
            // Get allow reuse value
            const allowReuseCheckbox = document.getElementById('reading-allow-reuse');
            this.dragDropData.allowReuse = allowReuseCheckbox ? allowReuseCheckbox.checked : true;
            
            const data = {
                type: questionType,
                dragDrop: this.dragDropData
            };
            
            // Store in hidden input
            const hiddenInput = document.getElementById('reading-question-data');
            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(data);
                console.log('Reading question data prepared:', data);
            }
            
            return data;
        }
        
        return null;
    }
};

// Make it globally available
window.ReadingQuestionTypes = ReadingQuestionTypes;

console.log('ReadingQuestionTypes loaded successfully');
