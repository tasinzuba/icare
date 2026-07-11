<x-layout>
    <x-slot:title>Add Question - Listening</x-slot>
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="py-4 sm:py-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-semibold">Add Listening Question</h1>
                        <p class="text-purple-100 text-sm mt-1">{{ $testSet->title }}</p>
                    </div>
                    <a href="{{ route('admin.test-sets.show', $testSet) }}" 
                       class="inline-flex items-center px-3 sm:px-4 py-2 bg-white/10 backdrop-blur border border-white/20 text-white text-sm font-medium rounded-md hover:bg-white/20 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
            
            @include('admin.questions.partials.question-header')
            
            <form action="{{ route('admin.questions.store') }}" method="POST" enctype="multipart/form-data" id="questionForm">
                @csrf
                <input type="hidden" name="test_set_id" value="{{ $testSet->id }}">
                
                <div class="space-y-4 sm:space-y-6">
                    <!-- Question Content -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                            <h3 class="text-base sm:text-lg font-medium text-gray-900">Question Content</h3>
                        </div>
                        
                        <div class="p-4 sm:p-6">
                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6">
                                <div class="space-y-4 sm:space-y-6">
                                    <!-- Instructions -->
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-sm font-medium text-gray-700">Instructions</label>
                                            <button type="button" onclick="showTemplates()" class="text-sm text-purple-600 hover:text-purple-700">
                                                Use Template
                                            </button>
                                        </div>
                                        <textarea id="instructions" name="instructions" class="tinymce-editor-simple">{{ old('instructions') }}</textarea>
                                    </div>
                                    
                                    <!-- Question Content -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Question <span class="text-red-500">*</span>
                                        </label>
                                        <div class="mb-3 flex flex-wrap gap-2" id="blank-buttons" style="display: none;">
                                            <button type="button" onclick="insertListeningBlank()" class="px-3 py-1 bg-amber-600 text-white text-xs font-medium rounded hover:bg-amber-700 transition-colors">
                                                Insert Blank
                                            </button>
                                            <span class="text-xs text-gray-500 flex items-center">
                                                <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">Alt+B</kbd>
                                            </span>
                                        </div>
                                        <div class="mb-3 flex flex-wrap gap-2" id="dropdown-buttons" style="display: none;">
                                            <button type="button" onclick="insertListeningDropdown()" class="px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition-colors">
                                                Insert Dropdown
                                            </button>
                                            <span class="text-xs text-gray-500 flex items-center">
                                                <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">Alt+D</kbd>
                                            </span>
                                        </div>
                                        <textarea id="content" name="content" class="tinymce-editor">{{ old('content') }}</textarea>
                                    </div>
                                </div>
                                
                                <div class="space-y-4 sm:space-y-6">
                                    @include('admin.questions.partials.question-settings', [
                                        'questionTypes' => [
                                            'single_choice' => 'Single Choice (Radio)',
                                            'multiple_choice' => 'Multiple Choice (Checkbox)',
                                            'true_false' => 'True/False/Not Given',
                                            'yes_no' => 'Yes/No/Not Given',
                                            'fill_blanks' => 'Fill in the Blanks',
                                            'dropdown_selection' => 'Dropdown Selection',
                                            'form_completion' => 'Form Completion',
                                            'note_completion' => 'Note Completion',
                                            'sentence_completion' => 'Sentence Completion',
                                            'short_answer' => 'Short Answer',
                                            'matching' => 'Matching',
                                            'plan_map_diagram' => 'Plan/Map/Diagram Labeling'
                                        ]
                                    ])
                                    
                                    <!-- Audio Transcript -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Audio Transcript
                                        </label>
                                        <textarea name="audio_transcript" rows="4" 
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 text-sm"
                                                  placeholder="Enter the transcript of the audio...">{{ old('audio_transcript') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Include listening question type panels --}}
                    @include('admin.questions.partials.listening-question-types')
                    
                    {{-- Type-specific panels --}}
                    <div id="type-specific-panels">
                        {{-- Existing panels will be handled by respective handlers --}}
                    </div>
                    
                    <!-- Audio Management -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-purple-50">
                            <h3 class="text-base sm:text-lg font-medium text-gray-900">
                                Audio Settings
                            </h3>
                        </div>
                        
                        <div class="p-4 sm:p-6">
                            {{-- Part Audio Status Check --}}
                            <div id="part-audio-status" class="mb-4">
                                {{-- This will be updated dynamically via JavaScript --}}
                            </div>
                            
                            {{-- Audio Upload Zone --}}
                            <div id="audio-upload-section">
                                <div id="drop-zone" class="border-2 border-dashed border-purple-300 rounded-lg p-6 sm:p-8 text-center hover:border-purple-400 transition-colors cursor-pointer bg-purple-50/30">
                                    <svg class="mx-auto h-10 sm:h-12 w-10 sm:w-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-600">
                                        <label for="media" class="cursor-pointer text-purple-600 hover:text-purple-700 font-medium">
                                            Click to upload custom audio
                                        </label>
                                        <span class="hidden sm:inline"> or drag and drop</span>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">MP3, WAV, OGG up to 50MB</p>
                                    <input id="media" name="media" type="file" class="hidden" accept=".mp3,.wav,.ogg">
                                </div>
                                
                                <div id="media-preview" class="mt-4 hidden">
                                    <!-- Preview will be shown here -->
                                </div>
                            </div>
                            
                            {{-- Hidden field to track if using custom audio --}}
                            <input type="hidden" name="use_custom_audio" id="use_custom_audio" value="0">
                        </div>
                    </div>
                    
                    <!-- Action Buttons - Sticky on Mobile -->
                    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 sticky bottom-0 z-10 border-t sm:border-t-0 sm:relative">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" name="action" value="save" class="flex-1 py-2.5 sm:py-3 bg-purple-600 text-white font-medium rounded-md hover:bg-purple-700 transition-colors text-sm sm:text-base">
                                Save Question
                            </button>
                            <button type="submit" name="action" value="save_and_new" class="flex-1 py-2.5 sm:py-3 bg-green-600 text-white font-medium rounded-md hover:bg-green-700 transition-colors text-sm sm:text-base">
                                Save & Add Another
                            </button>
                            <button type="button" onclick="previewQuestion()" class="flex-1 py-2.5 sm:py-3 border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 transition-colors text-sm sm:text-base">
                                Preview
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('admin.questions.partials.modals')
    
    @push('styles')
    <style>
        /* Professional styles */
        .drag-over {
            border-color: #9333EA !important;
            background-color: #FAF5FF !important;
        }
        
        /* Type specific panel styles */
        .type-specific-panel {
            transition: all 0.3s ease;
        }
        
        /* Notification styles */
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #7C3AED;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            max-width: 90%;
        }
        
        .notification.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .notification.success {
            background: #059669;
        }
        
        .notification.error {
            background: #DC2626;
        }
    </style>
    @endpush
    
    @push('scripts')
    <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key', 'no-api-key') }}/tinymce/6/tinymce.min.js"></script>
    <script src="{{ asset('js/admin/listening-question-types.js') }}"></script>
    <script src="{{ asset('js/admin/question-types.js') }}"></script>
    <script src="{{ asset('js/admin/question-diagram-simple.js') }}"></script>

    <script>
    // Global variables
    let contentEditor = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize TinyMCE
        tinymce.init({
            selector: '.tinymce-editor-simple',
            height: 150,
            menubar: false,
            plugins: ['lists', 'link', 'charmap', 'code'],
            toolbar: 'bold italic underline | fontsize | bullist numlist | alignleft aligncenter alignright | link | removeformat code',
            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt',
            content_css: '//www.tiny.cloud/css/codepen.min.css',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });
        
        tinymce.init({
            selector: '.tinymce-editor',
            height: 350,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                'preview', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | formatselect | fontsize | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | removeformat code',
            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt',
            content_css: '//www.tiny.cloud/css/codepen.min.css',
            setup: function(editor) {
                contentEditor = editor;
                editor.on('change', function() {
                    editor.save();
                    
                    // Update blanks/dropdowns based on question type
                    const questionType = document.getElementById('question_type')?.value;
                    if (['fill_blanks', 'note_completion', 'sentence_completion'].includes(questionType)) {
                        if (window.ListeningQuestionTypes) {
                            window.ListeningQuestionTypes.updateBlanks();
                        }
                    } else if (['dropdown_selection', 'form_completion'].includes(questionType)) {
                        if (window.ListeningQuestionTypes) {
                            window.ListeningQuestionTypes.updateDropdowns();
                        }
                    }
                });
            }
        });
        
        // Question type change handler
        const questionTypeSelect = document.getElementById('question_type');
        if (questionTypeSelect) {
            questionTypeSelect.addEventListener('change', function() {
                const selectedType = this.value;
                console.log('Question type changed to:', selectedType);
                
                // Hide all type-specific panels first
                document.querySelectorAll('.type-specific-panel').forEach(panel => {
                    panel.style.display = 'none';
                });
                
                // Initialize ListeningQuestionTypes handler
                if (window.ListeningQuestionTypes) {
                    window.ListeningQuestionTypes.init(selectedType);
                }
                
                // Special handling for specific types that have their own handlers
                if (selectedType === 'plan_map_diagram' && window.SimpleDiagramHandler) {
                    window.SimpleDiagramHandler.init();
                } else if (selectedType === 'matching' && window.QuestionTypeHandlers) {
                    window.QuestionTypeHandlers.init(selectedType);
                }
            });
            
            // Initialize on load if type is already selected
            if (questionTypeSelect.value) {
                questionTypeSelect.dispatchEvent(new Event('change'));
            }
        }
        
        // Form submission handler
        const questionForm = document.getElementById('questionForm');
        if (questionForm) {
            questionForm.addEventListener('submit', function(e) {
                console.log('=== FORM SUBMISSION STARTED ===');
                
                // Save TinyMCE content
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                }
                
                // Get question type
                const questionType = document.getElementById('question_type').value;
                console.log('Question Type:', questionType);
                
                // Prepare submission data for listening question types
                if (window.ListeningQuestionTypes) {
                    window.ListeningQuestionTypes.prepareSubmissionData();
                }
                
                // Handle special types
                if (questionType === 'matching') {
                    handleMatchingSubmission();
                } else if (questionType === 'form_completion') {
                    handleFormCompletionSubmission();
                } else if (questionType === 'plan_map_diagram') {
                    if (window.SimpleDiagramHandler) {
                        window.SimpleDiagramHandler.prepareSubmissionData();
                    }
                }
                
                console.log('=== FORM SUBMISSION COMPLETED ===');
            });
        }
        
        // Part audio check functionality
        function checkPartAudio(partNumber) {
            fetch(`/admin/test-sets/{{ $testSet->id }}/check-part-audio/${partNumber}`)
                .then(response => response.json())
                .then(data => {
                    const statusDiv = document.getElementById('part-audio-status');
                    const uploadSection = document.getElementById('audio-upload-section');
                    const mediaInput = document.getElementById('media');
                    const useCustomAudio = document.getElementById('use_custom_audio');
                    
                    if (data.hasAudio) {
                        statusDiv.innerHTML = `
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-green-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 0016 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm text-green-800 font-medium">
                                            Part ${partNumber} audio is available!
                                        </p>
                                        <p class="text-xs text-green-700 mt-1">
                                            This question will automatically use the Part ${partNumber} audio unless you upload a custom audio file.
                                        </p>
                                        <label class="inline-flex items-center mt-2">
                                            <input type="checkbox" id="custom-audio-checkbox" class="form-checkbox text-purple-600" onchange="toggleCustomAudioUpload()">
                                            <span class="ml-2 text-sm text-gray-700">Upload custom audio for this question only</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        uploadSection.style.opacity = '0.5';
                        uploadSection.style.pointerEvents = 'none';
                        mediaInput.removeAttribute('required');
                        useCustomAudio.value = '0';
                    } else {
                        statusDiv.innerHTML = `
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-yellow-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm text-yellow-800 font-medium">
                                            No audio uploaded for Part ${partNumber}
                                        </p>
                                        <p class="text-xs text-yellow-700 mt-1">
                                            You must upload audio for this question.
                                        </p>
                                        <a href="{{ route('admin.test-sets.part-audios', $testSet) }}" 
                                           target="_blank"
                                           class="inline-flex items-center mt-2 text-xs text-yellow-600 hover:text-yellow-700 font-medium">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                            Upload Part ${partNumber} Audio
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        uploadSection.style.opacity = '1';
                        uploadSection.style.pointerEvents = 'auto';
                        mediaInput.setAttribute('required', 'required');
                        useCustomAudio.value = '1';
                    }
                })
                .catch(error => {
                    console.error('Error checking part audio:', error);
                });
        }
        
        // Check initial part audio status
        const partSelect = document.querySelector('[name="part_number"]');
        if (partSelect) {
            const initialPart = partSelect.value || 1;
            checkPartAudio(initialPart);
            
            partSelect.addEventListener('change', function() {
                checkPartAudio(this.value);
                const customCheckbox = document.getElementById('custom-audio-checkbox');
                if (customCheckbox) {
                    customCheckbox.checked = false;
                }
            });
        }
        
        // File upload handling
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('media');
        
        if (dropZone && fileInput) {
            dropZone.addEventListener('click', () => {
                if (dropZone.parentElement.parentElement.style.pointerEvents !== 'none') {
                    fileInput.click();
                }
            });
            
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                if (dropZone.parentElement.parentElement.style.pointerEvents !== 'none') {
                    dropZone.classList.add('drag-over');
                }
            });
            
            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('drag-over');
            });
            
            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('drag-over');
                
                if (dropZone.parentElement.parentElement.style.pointerEvents !== 'none') {
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        fileInput.files = files;
                        handleFileSelect(files[0]);
                    }
                }
            });
            
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFileSelect(e.target.files[0]);
                }
            });
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.altKey) {
                const questionType = document.getElementById('question_type')?.value;
                
                if ((questionType === 'fill_blanks' || questionType === 'note_completion' || questionType === 'sentence_completion') && 
                    (e.key === 'b' || e.key === 'B')) {
                    e.preventDefault();
                    insertListeningBlank();
                }
                
                if ((questionType === 'dropdown_selection' || questionType === 'form_completion') && 
                    (e.key === 'd' || e.key === 'D')) {
                    e.preventDefault();
                    insertListeningDropdown();
                }
            }
        });
    });
    
    // Global functions
    function handleFileSelect(file) {
        const mediaPreview = document.getElementById('media-preview');
        if (mediaPreview) {
            mediaPreview.innerHTML = `
                <div class="flex items-center p-3 bg-purple-50 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">${file.name}</p>
                        <p class="text-xs text-gray-500">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                    </div>
                    <button type="button" onclick="clearAudioUpload()" class="ml-3 text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
            mediaPreview.classList.remove('hidden');
        }
    }
    
    window.toggleCustomAudioUpload = function() {
        const checkbox = document.getElementById('custom-audio-checkbox');
        const uploadSection = document.getElementById('audio-upload-section');
        const mediaInput = document.getElementById('media');
        const useCustomAudio = document.getElementById('use_custom_audio');
        
        if (checkbox && checkbox.checked) {
            uploadSection.style.opacity = '1';
            uploadSection.style.pointerEvents = 'auto';
            mediaInput.setAttribute('required', 'required');
            useCustomAudio.value = '1';
        } else {
            uploadSection.style.opacity = '0.5';
            uploadSection.style.pointerEvents = 'none';
            mediaInput.removeAttribute('required');
            useCustomAudio.value = '0';
            mediaInput.value = '';
            document.getElementById('media-preview').classList.add('hidden');
        }
    }
    
    window.clearAudioUpload = function() {
        const fileInput = document.getElementById('media');
        const mediaPreview = document.getElementById('media-preview');
        
        if (fileInput) fileInput.value = '';
        if (mediaPreview) {
            mediaPreview.innerHTML = '';
            mediaPreview.classList.add('hidden');
        }
    }
    
    window.insertListeningBlank = function() {
        if (window.ListeningQuestionTypes && window.contentEditor) {
            window.ListeningQuestionTypes.setupBlankInsertion();
            if (window.insertListeningBlank) {
                window.insertListeningBlank();
            }
        }
    }
    
    window.insertListeningDropdown = function() {
        if (window.ListeningQuestionTypes && window.contentEditor) {
            window.ListeningQuestionTypes.setupDropdownInsertion();
            if (window.insertListeningDropdown) {
                window.insertListeningDropdown();
            }
        }
    }
    
    function handleMatchingSubmission() {
        // Handle matching question submission
        const pairs = [];
        document.querySelectorAll('.matching-pair').forEach((pair, index) => {
            const leftInput = pair.querySelector('input[name*="[left]"]');
            const rightInput = pair.querySelector('input[name*="[right]"]');
            
            if (leftInput && rightInput) {
                const left = leftInput.value.trim();
                const right = rightInput.value.trim();
                
                if (left && right) {
                    pairs.push({ left: left, right: right });
                }
            }
        });
        
        if (pairs.length > 0) {
            let hiddenInput = document.getElementById('matching_pairs_json');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'matching_pairs_json';
                hiddenInput.name = 'matching_pairs_json';
                document.getElementById('questionForm').appendChild(hiddenInput);
            }
            hiddenInput.value = JSON.stringify(pairs);
        }
    }
    
    function handleFormCompletionSubmission() {
        // Handle form completion submission
        const titleInput = document.querySelector('input[name="form_structure[title]"]');
        const formData = {
            title: titleInput ? titleInput.value.trim() : 'Form',
            fields: []
        };
        
        document.querySelectorAll('.form-field').forEach((field, index) => {
            const labelInput = field.querySelector('input[name*="[label]"]');
            const answerInput = field.querySelector('input[name*="[answer]"]');
            
            if (labelInput && answerInput) {
                const label = labelInput.value.trim();
                const answer = answerInput.value.trim();
                
                if (label && answer) {
                    formData.fields.push({
                        label: label,
                        blank_id: index + 1,
                        answer: answer
                    });
                }
            }
        });
        
        if (formData.fields.length > 0) {
            let hiddenInput = document.getElementById('form_structure_json');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'form_structure_json';
                hiddenInput.name = 'form_structure_json';
                document.getElementById('questionForm').appendChild(hiddenInput);
            }
            hiddenInput.value = JSON.stringify(formData);
        }
    }
    
    function previewQuestion() {
        alert('Preview functionality to be implemented');
    }
    
    function showTemplates() {
        alert('Template functionality to be implemented');
    }
    
    function showBulkOptions() {
        const modal = document.getElementById('bulk-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
    </script>
    @endpush
</x-layout>