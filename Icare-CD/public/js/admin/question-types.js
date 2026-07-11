// Question Type Specific Handlers
const QuestionTypeHandlers = {
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
                this.initDiagramLabeling();
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
    },

    // Diagram Labeling Handler
    initDiagramLabeling() {
        console.log('Initializing diagram labeling question');
        const panel = document.getElementById('diagram-panel');
        if (panel) {
            panel.style.display = 'block';
        }

        // Initialize diagram uploader
        this.setupDiagramUploader();
    },

    setupDiagramUploader() {
        const input = document.getElementById('diagram-image');
        const preview = document.getElementById('diagram-preview');

        if (!input || !preview) {
            console.error('Diagram input or preview not found');
            return;
        }

        // Remove old event listeners
        const newInput = input.cloneNode(true);
        input.parentNode.replaceChild(newInput, input);

        newInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.innerHTML = `
                        <div style="position: relative;">
                            <img src="${e.target.result}" id="diagram-img" class="max-w-full" style="cursor: crosshair;">
                            <div class="mt-3 text-sm text-gray-600">
                                Click on the image to add hotspots
                            </div>
                        </div>
                    `;
                    this.setupHotspotCreator();
                };
                reader.readAsDataURL(file);
            }
        });
    },

    setupHotspotCreator() {
        const img = document.getElementById('diagram-img');
        if (!img) {
            console.error('Diagram image not found');
            return;
        }

        // Wait for image to load
        img.onload = () => {
            console.log('Image loaded, ready for hotspots');
        };

        img.addEventListener('click', (e) => {
            e.preventDefault();
            const rect = img.getBoundingClientRect();
            const x = Math.round(((e.clientX - rect.left) / rect.width) * 100);
            const y = Math.round(((e.clientY - rect.top) / rect.height) * 100);

            console.log('Click position:', x, y);
            this.addHotspot(x, y);
        });
    },

    addHotspot(x, y) {
        const container = document.getElementById('hotspots-container');
        const preview = document.getElementById('diagram-preview');

        if (!container || !preview) {
            console.error('Hotspots container or preview not found');
            return;
        }

        const index = container.children.length;
        const label = String.fromCharCode(65 + index);

        // Add visual marker on image
        const marker = document.createElement('div');
        marker.className = 'hotspot-marker';
        marker.style.cssText = `
            position: absolute;
            left: ${x}%;
            top: ${y}%;
            width: 30px;
            height: 30px;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            transform: translate(-50%, -50%);
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        `;
        marker.textContent = label;
        marker.dataset.label = label;

        // Make sure preview container is relatively positioned
        const imgContainer = preview.querySelector('div');
        if (imgContainer) {
            imgContainer.style.position = 'relative';
            imgContainer.appendChild(marker);
        }

        // Add form field
        const fieldDiv = document.createElement('div');
        fieldDiv.className = 'hotspot-field flex gap-3 mb-3';
        fieldDiv.dataset.label = label;
        fieldDiv.innerHTML = `
            <div class="w-16 text-center">
                <span class="inline-block w-8 h-8 bg-blue-500 text-white rounded-full leading-8 font-bold">
                    ${label}
                </span>
            </div>
            <input type="hidden" name="diagram_hotspots[${index}][x]" value="${x}">
            <input type="hidden" name="diagram_hotspots[${index}][y]" value="${y}">
            <input type="hidden" name="diagram_hotspots[${index}][label]" value="${label}">
            <div class="flex-1">
                <input type="text" 
                       name="diagram_hotspots[${index}][answer]" 
                       placeholder="What is at point ${label}?"
                       class="w-full px-3 py-2 border rounded-md text-sm"
                       required>
            </div>
            <button type="button" onclick="QuestionTypeHandlers.removeHotspot(this, '${label}')" 
                    class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                Remove
            </button>
        `;

        container.appendChild(fieldDiv);
        console.log('Added hotspot', label, 'at', x, y);
    },

    removeHotspot(button, label) {
        // Remove form field
        button.closest('.hotspot-field').remove();

        // Remove visual marker
        const markers = document.querySelectorAll('.hotspot-marker');
        markers.forEach(marker => {
            if (marker.dataset.label === label) {
                marker.remove();
            }
        });

        // Reindex
        this.reindexHotspots();
    },

    reindexHotspots() {
        const fields = document.querySelectorAll('.hotspot-field');
        const markers = document.querySelectorAll('.hotspot-marker');

        // First, collect all existing data
        const hotspotsData = [];
        fields.forEach((field) => {
            const xInput = field.querySelector('input[name*="[x]"]');
            const yInput = field.querySelector('input[name*="[y]"]');
            const answerInput = field.querySelector('input[name*="[answer]"]');

            if (xInput && yInput) {
                hotspotsData.push({
                    x: xInput.value,
                    y: yInput.value,
                    answer: answerInput ? answerInput.value : ''
                });
            }
        });

        // Clear and rebuild
        const container = document.getElementById('hotspots-container');
        container.innerHTML = '';

        // Remove all markers
        markers.forEach(marker => marker.remove());

        // Rebuild with new indices
        hotspotsData.forEach((data, index) => {
            const label = String.fromCharCode(65 + index);

            // Add marker back
            const preview = document.getElementById('diagram-preview');
            const imgContainer = preview.querySelector('div');
            if (imgContainer) {
                const marker = document.createElement('div');
                marker.className = 'hotspot-marker';
                marker.style.cssText = `
                    position: absolute;
                    left: ${data.x}%;
                    top: ${data.y}%;
                    width: 30px;
                    height: 30px;
                    background: #3b82f6;
                    color: white;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    transform: translate(-50%, -50%);
                    cursor: pointer;
                    z-index: 10;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                `;
                marker.textContent = label;
                marker.dataset.label = label;
                imgContainer.appendChild(marker);
            }

            // Add field back
            const fieldDiv = document.createElement('div');
            fieldDiv.className = 'hotspot-field flex gap-3 mb-3';
            fieldDiv.dataset.label = label;
            fieldDiv.innerHTML = `
                <div class="w-16 text-center">
                    <span class="inline-block w-8 h-8 bg-blue-500 text-white rounded-full leading-8 font-bold">
                        ${label}
                    </span>
                </div>
                <input type="hidden" name="diagram_hotspots[${index}][x]" value="${data.x}">
                <input type="hidden" name="diagram_hotspots[${index}][y]" value="${data.y}">
                <input type="hidden" name="diagram_hotspots[${index}][label]" value="${label}">
                <div class="flex-1">
                    <input type="text" 
                           name="diagram_hotspots[${index}][answer]" 
                           value="${data.answer}"
                           placeholder="What is at point ${label}?"
                           class="w-full px-3 py-2 border rounded-md text-sm"
                           required>
                </div>
                <button type="button" onclick="QuestionTypeHandlers.removeHotspot(this, '${label}')" 
                        class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                    Remove
                </button>
            `;
            container.appendChild(fieldDiv);
        });
    }
};

// Make it globally available
window.QuestionTypeHandlers = QuestionTypeHandlers;