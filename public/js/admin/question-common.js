// Common functionality for all question types
let editor;

// Initialize common components
document.addEventListener('DOMContentLoaded', function () {
    initializeEventListeners();
    initializeQuestionNumbering();
    initializeFileUpload();
});

// Initialize TinyMCE for content that needs it
function initializeTinyMCE(selector = '.tinymce') {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: selector,
            height: 350,
            menubar: true,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; line-height: 1.6; }',
            images_upload_url: '/admin/questions/upload-image',
            images_upload_base_path: '/',
            images_upload_credentials: true,
            automatic_uploads: true,
            images_upload_handler: function (blobInfo, success, failure, progress) {
                return new Promise(function(resolve, reject) {
                    const xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', '/admin/questions/upload-image');
                    
                    // Get CSRF token from meta tag
                    const token = document.querySelector('meta[name="csrf-token"]');
                    if (token) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', token.content);
                    }
                    
                    xhr.upload.onprogress = function (e) {
                        progress(e.loaded / e.total * 100);
                    };
                    
                    xhr.onload = function() {
                        if (xhr.status < 200 || xhr.status >= 300) {
                            reject('HTTP Error: ' + xhr.status);
                            return;
                        }
                        
                        try {
                            const json = JSON.parse(xhr.responseText);
                            console.log('Upload response:', json);
                            
                            if (!json || !json.success) {
                                reject('Upload failed: ' + (json.message || 'Unknown error'));
                                return;
                            }
                            
                            // Return the URL directly
                            resolve(json.url);
                        } catch (e) {
                            reject('Invalid JSON response: ' + xhr.responseText);
                        }
                    };
                    
                    xhr.onerror = function () {
                        reject('Image upload failed due to a network error.');
                    };
                    
                    const formData = new FormData();
                    formData.append('image', blobInfo.blob(), blobInfo.filename());
                    
                    xhr.send(formData);
                });
            },
            setup: function (ed) {
                editor = ed;
            }
        });
    }
}

// Event Listeners
function initializeEventListeners() {
    const questionTypeSelect = document.getElementById('question_type');
    if (questionTypeSelect) {
        questionTypeSelect.addEventListener('change', handleQuestionTypeChange);
    }

    const addOptionBtn = document.getElementById('add-option-btn');
    if (addOptionBtn) {
        addOptionBtn.addEventListener('click', () => addOption());
    }

    const questionForm = document.getElementById('questionForm');
    if (questionForm) {
        // Enhanced form submit handler
        questionForm.addEventListener('submit', function (e) {
            // Debug logging
            console.log('Form submission started');

            // Save all TinyMCE content
            if (typeof tinymce !== 'undefined') {
                console.log('Saving TinyMCE content...');
                tinymce.triggerSave();

                // Log all editors
                tinymce.editors.forEach(function (ed) {
                    console.log('Editor ID:', ed.id, 'Content length:', ed.getContent().length);
                });
            }

            // Validate basic requirements
            const questionType = document.getElementById('question_type');
            if (questionType && !questionType.value) {
                e.preventDefault();
                alert('Please select a question type');
                return false;
            }

            // Additional validation for content based on question type
            const questionTypeValue = questionType ? questionType.value : '';

            if (questionTypeValue === 'passage') {
                // For passage type, check if we have content
                const passageTextField = document.getElementById('passage-text');
                const contentField = document.getElementById('content');

                let hasContent = false;

                if (passageTextField && passageTextField.value.trim()) {
                    hasContent = true;
                    console.log('Passage text field has content:', passageTextField.value.length);
                }

                if (contentField && contentField.value.trim()) {
                    hasContent = true;
                    console.log('Content field has content:', contentField.value.length);
                }

                if (!hasContent) {
                    e.preventDefault();
                    alert('Please enter passage content');
                    return false;
                }
            }

            console.log('Form validation passed, submitting...');
            // Let form submit normally
            return true;
        });
    }
}

// Question type change handler
function handleQuestionTypeChange() {
    const type = this.value;
    const optionsCard = document.getElementById('options-card');

    const optionTypes = ['multiple_choice', 'true_false', 'yes_no', 'matching',
        'matching_headings', 'matching_information', 'matching_features'];

    if (optionTypes.includes(type)) {
        optionsCard.classList.remove('hidden');
        setupDefaultOptions(type);
    } else {
        optionsCard.classList.add('hidden');
    }

    // Trigger section-specific handlers
    if (typeof handleSectionSpecificChange === 'function') {
        handleSectionSpecificChange(type);
    }
}


// Options Management
function setupDefaultOptions(type) {
    const container = document.getElementById('options-container');
    if (!container) return;

    container.innerHTML = '';

    if (type === 'true_false') {
        addOption('TRUE', true);
        addOption('FALSE', false);
        addOption('NOT GIVEN', false);
        document.getElementById('add-option-btn').style.display = 'none';
    } else if (type === 'yes_no') {
        addOption('YES', true);
        addOption('NO', false);
        addOption('NOT GIVEN', false);
        document.getElementById('add-option-btn').style.display = 'none';
    } else {
        for (let i = 0; i < 4; i++) {
            addOption('', i === 0);
        }
        document.getElementById('add-option-btn').style.display = 'inline-block';
    }
}

function addOption(content = '', isCorrect = false) {
    const container = document.getElementById('options-container');
    if (!container) return;

    const index = container.children.length;

    const optionDiv = document.createElement('div');
    optionDiv.className = 'flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-200';

    optionDiv.innerHTML = `
        <input type="radio" name="correct_option" value="${index}" 
               class="h-4 w-4 text-blue-600" ${isCorrect ? 'checked' : ''}>
        <span class="font-medium text-gray-700">${String.fromCharCode(65 + index)}.</span>
        <input type="text" name="options[${index}][content]" value="${content}" 
               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
               placeholder="Enter option text..." required>
        <button type="button" onclick="removeOption(this)" class="text-red-500 hover:text-red-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;

    container.appendChild(optionDiv);
}

window.removeOption = function (btn) {
    btn.parentElement.remove();
    reindexOptions();
};

function reindexOptions() {
    const options = document.querySelectorAll('#options-container > div');
    options.forEach((option, index) => {
        option.querySelector('input[type="radio"]').value = index;
        option.querySelector('input[type="text"]').name = `options[${index}][content]`;
        option.querySelector('span.font-medium').textContent = String.fromCharCode(65 + index) + '.';
    });
}

// File Upload
function initializeFileUpload() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('media');

    if (!dropZone || !fileInput) return;

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
    });

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFiles(files);
        }
    }

    fileInput.addEventListener('change', function (e) {
        handleFiles(this.files);
    });
}

function handleFiles(files) {
    if (files.length > 0) {
        const file = files[0];
        const preview = document.getElementById('media-preview');
        if (!preview) return;

        preview.innerHTML = '';
        preview.classList.remove('hidden');

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = `
                    <div class="relative inline-block">
                        <img src="${e.target.result}" class="max-h-48 rounded">
                        <button type="button" onclick="clearMedia()" class="absolute top-0 right-0 bg-red-500 text-white p-1 rounded-full transform translate-x-1/2 -translate-y-1/2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</p>
                `;
            };
            reader.readAsDataURL(file);
        } else if (file.type.startsWith('audio/')) {
            preview.innerHTML = `
                <div class="relative">
                    <audio controls class="w-full">
                        <source src="${URL.createObjectURL(file)}" type="${file.type}">
                    </audio>
                    <button type="button" onclick="clearMedia()" class="absolute top-0 right-0 bg-red-500 text-white p-1 rounded-full transform translate-x-1/2 -translate-y-1/2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 mt-2">${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</p>
            `;
        }
    }
}

window.clearMedia = function () {
    const fileInput = document.getElementById('media');
    const preview = document.getElementById('media-preview');

    if (fileInput) fileInput.value = '';
    if (preview) {
        preview.innerHTML = '';
        preview.classList.add('hidden');
    }
};

// Question Numbering
function initializeQuestionNumbering() {
    const orderInput = document.querySelector('input[name="order_number"]');
    const numberDisplay = document.getElementById('question-number-display');

    if (orderInput && numberDisplay) {
        orderInput.addEventListener('input', function () {
            const value = this.value || '?';
            numberDisplay.textContent = '#' + value;
        });
    }
}

// Modal Functions
window.showTemplates = function () {
    const modal = document.getElementById('template-modal');
    if (modal) {
        loadTemplatesForSection();
        modal.classList.remove('hidden');
    }
};

window.closeTemplates = function () {
    const modal = document.getElementById('template-modal');
    if (modal) modal.classList.add('hidden');
};

window.useTemplate = function (template) {
    const instructionsEl = document.getElementById('instructions');
    if (instructionsEl) instructionsEl.value = template;
    closeTemplates();
};

window.showTemplates = function () {
    const modal = document.getElementById('template-modal');
    if (modal) {
        loadTemplatesForSection();
        modal.classList.remove('hidden');
    }
};

window.closeTemplates = function () {
    const modal = document.getElementById('template-modal');
    if (modal) modal.classList.add('hidden');
};

window.useTemplate = function (template) {
    const instructionsEl = document.getElementById('instructions');
    if (instructionsEl) instructionsEl.value = template;
    closeTemplates();
};

window.previewQuestion = function () {
    const modal = document.getElementById('preview-modal');
    const content = document.getElementById('preview-content');

    if (!modal || !content) return;

    // Build preview HTML
    let previewHtml = '<div class="space-y-4">';

    const instructions = document.getElementById('instructions');
    if (instructions && instructions.value) {
        previewHtml += `<div class="text-sm text-gray-600 italic">${instructions.value}</div>`;
    }

    const questionContent = document.getElementById('content');
    if (questionContent) {
        const contentValue = editor ? editor.getContent() : questionContent.value;
        previewHtml += `<div class="text-gray-900">${contentValue}</div>`;
    }

    const optionsContainer = document.getElementById('options-container');
    if (optionsContainer && optionsContainer.children.length > 0) {
        previewHtml += '<div class="mt-4 space-y-2">';
        const options = optionsContainer.querySelectorAll('input[type="text"]');
        options.forEach((option, index) => {
            if (option.value) {
                previewHtml += `
                    <div class="flex items-center space-x-2">
                        <span class="font-medium">${String.fromCharCode(65 + index)}.</span>
                        <span>${option.value}</span>
                    </div>
                `;
            }
        });
        previewHtml += '</div>';
    }

    previewHtml += '</div>';

    content.innerHTML = previewHtml;
    modal.classList.remove('hidden');
};

window.closePreview = function () {
    const modal = document.getElementById('preview-modal');
    if (modal) modal.classList.add('hidden');
};

// Bulk Options
window.showBulkOptions = function () {
    const modal = document.getElementById('bulk-modal');
    if (modal) modal.classList.remove('hidden');
};

window.closeBulkOptions = function () {
    const modal = document.getElementById('bulk-modal');
    if (modal) modal.classList.add('hidden');
    const bulkText = document.getElementById('bulk-text');
    if (bulkText) bulkText.value = '';
};

window.addBulkOptions = function () {
    const bulkText = document.getElementById('bulk-text');
    if (bulkText && bulkText.value) {
        const container = document.getElementById('options-container');
        if (container) {
            container.innerHTML = '';

            const options = bulkText.value.split('\n').filter(opt => opt.trim());
            options.forEach((opt, index) => {
                addOption(opt.trim(), index === 0);
            });
        }

        closeBulkOptions();
    }
};

// Insert blank function
window.insertBlank = function () {
    if (editor) {
        const blankCounter = document.querySelectorAll('[data-blank]').length + 1;
        const blankHtml = `<span class="blank-placeholder" data-blank="${blankCounter}" contenteditable="false">[____${blankCounter}____]</span>`;
        editor.insertContent(blankHtml);
    }
};

// Load templates based on section
function loadTemplatesForSection() {
    const templateList = document.getElementById('template-list');
    if (!templateList) return;

    // This would be populated based on the current section
    // For now, showing common templates
    const templates = [
        { text: 'Multiple Choice', value: 'Choose the correct letter, A, B, C or D.' },
        { text: 'True/False/NG', value: 'Write TRUE if the statement agrees with the information, FALSE if it contradicts, or NOT GIVEN.' },
        { text: 'Short Answer', value: 'Write NO MORE THAN THREE WORDS AND/OR A NUMBER for each answer.' },
        { text: 'Sentence Completion', value: 'Complete the sentences below. Write NO MORE THAN TWO WORDS from the passage.' }
    ];

    templateList.innerHTML = templates.map(t => `
        <button onclick="useTemplate('${t.value}')" class="w-full text-left px-3 py-2 hover:bg-gray-100 rounded">
            ${t.text}
        </button>
    `).join('');
}

// Close modals on ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeTemplates();
        closePreview();
        closeBulkOptions();
    }
});