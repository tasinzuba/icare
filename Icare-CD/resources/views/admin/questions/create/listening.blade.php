<x-admin-layout>
    <x-slot:title>Add Question - Listening</x-slot>

    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Add Listening Question</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $testSet->title }}</p>
            </div>
            <a href="{{ route('admin.test-sets.show', $testSet) }}"
               class="inline-flex items-center rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="text-sm font-medium text-gray-900">Back to Test Set</span>
            </a>
        </div>
    </div>

    @include('admin.questions.partials.question-header')

    <form action="{{ route('admin.questions.store') }}" method="POST" enctype="multipart/form-data" id="questionForm" novalidate>
        @csrf
        <input type="hidden" name="test_set_id" value="{{ $testSet->id }}">

        <div class="space-y-6">
            <!-- Question Content -->
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Question Content</h3>

                <!-- Top Settings Row - 4 Columns -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Question Type <span class="text-red-500">*</span>
                                </label>
                                <select id="question_type" name="question_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm" required>
                                    <option value="">Select type...</option>
                                    <option value="fill_blanks">Fill in the Blanks</option>
                                    <option value="single_choice">Single Choice</option>
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="dropdown_selection">Matching Letters</option>
                                    <option value="drag_drop">Drag & Drop</option>
                                </select>
                            </div>

                            <!-- Number -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Question Number <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="order_number" value="{{ old('order_number', $nextQuestionNumber) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm" min="0" required>
                            </div>

                            <!-- Part -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Part <span class="text-red-500">*</span>
                                </label>
                                <select name="part_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm" required>
                                    <option value="1">Part 1 (Social)</option>
                                    <option value="2">Part 2 (Monologue)</option>
                                    <option value="3">Part 3 (Discussion)</option>
                                    <option value="4">Part 4 (Lecture)</option>
                                </select>
                            </div>

                            <!-- Marks -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Marks</label>
                                <input type="number" name="marks" value="{{ old('marks', 1) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm" min="0" max="40">
                            </div>
                </div>

                <!-- Audio Transcript - Full Width -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Audio Transcript</label>
                    <textarea name="audio_transcript" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 text-sm"
                              placeholder="Enter the transcript of the audio...">{{ old('audio_transcript') }}</textarea>
                </div>

                <!-- Instructions -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Instructions</label>
                    <textarea id="instructions" name="instructions" class="tinymce-editor-simple">{{ old('instructions') }}</textarea>
                </div>

                <!-- Question Content -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Question <span class="text-red-500">*</span>
                    </label>

                    <!-- Insert Buttons -->
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

                    <div class="mb-3 flex flex-wrap gap-2" id="drag-zone-buttons" style="display: none;">
                        <button type="button" onclick="insertDragZone()" class="px-3 py-1 bg-indigo-600 text-white text-xs font-medium rounded hover:bg-indigo-700 transition-colors">
                            Insert Drag Zone
                        </button>
                        <span class="text-xs text-gray-500 flex items-center">
                            <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">Alt+G</kbd>
                        </span>
                    </div>

                    <textarea id="content" name="content" class="tinymce-editor">{{ old('content') }}</textarea>
                </div>
            </div>

            @include('admin.questions.partials.listening-question-types')

            {{-- Type-specific panels --}}
            <div id="type-specific-panels">
                {{-- Existing panels will be handled by respective handlers --}}
            </div>

            <!-- Audio Management -->
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Audio Settings</h3>
                <div id="part-audio-status"></div>
            </div>

            <!-- Action Buttons -->
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" name="action" value="save" class="flex-1 py-2.5 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-colors">
                        Save Question
                    </button>
                    <button type="submit" name="action" value="save_and_new" class="flex-1 py-2.5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                        Save & Add Another
                    </button>
                </div>
            </div>
        </div>
    </form>

    @include('admin.questions.partials.modals')
    
    @push('styles')
    <style>
        .drag-over {
            border-color: #9333EA !important;
            background-color: #FAF5FF !important;
        }
        
        .type-specific-panel {
            transition: all 0.3s ease;
        }
    </style>
    @endpush
    
    @push('scripts')
    <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key', 'no-api-key') }}/tinymce/6/tinymce.min.js"></script>
    <script src="{{ asset('js/admin/listening-question-types.js') }}"></script>
    <script src="{{ asset('js/student/listening-drag-drop.js') }}"></script>

    <script>
    let contentEditor = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize TinyMCE - Instructions
        tinymce.init({
            selector: '.tinymce-editor-simple',
            height: 120,
            menubar: false,
            plugins: ['lists', 'link', 'charmap', 'code', 'table', 'image', 'media'],
            toolbar: 'bold italic underline | fontsize | bullist numlist | alignleft aligncenter alignright | link image | table | removeformat code',
            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt',
            content_css: '//www.tiny.cloud/css/codepen.min.css',
            images_upload_url: '/admin/questions/upload-image',
            images_upload_base_path: '/',
            images_upload_credentials: true,
            automatic_uploads: true,
            file_picker_types: 'image',
            images_upload_handler: function (blobInfo, success, failure, progress) {
                return new Promise(function(resolve, reject) {
                    const xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', '/admin/questions/upload-image');
                    
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
                            failure('Upload failed: ' + xhr.status);
                            return;
                        }
                        
                        try {
                            const json = JSON.parse(xhr.responseText);
                            if (!json || !json.success) {
                                reject('Upload failed: ' + (json.message || 'Unknown error'));
                                failure('Upload failed: ' + (json.message || 'Unknown error'));
                                return;
                            }
                            resolve(json.url);
                            success(json.url);
                        } catch (e) {
                            reject('Invalid JSON response: ' + xhr.responseText);
                            failure('Invalid server response');
                        }
                    };
                    
                    xhr.onerror = function () {
                        reject('Image upload failed due to a network error.');
                        failure('Image upload failed due to a network error.');
                    };
                    
                    const formData = new FormData();
                    formData.append('image', blobInfo.blob(), blobInfo.filename());
                    xhr.send(formData);
                });
            },
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });
        
        // Initialize TinyMCE - Question Content
        tinymce.init({
            selector: '.tinymce-editor',
            height: 500,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                'preview', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | formatselect | fontsize | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | link image media | removeformat code',
            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt',
            content_css: '//www.tiny.cloud/css/codepen.min.css',
            table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
            table_default_styles: {
                'border-collapse': 'collapse',
                'width': '100%'
            },
            table_default_attributes: {
                border: '1'
            },
            images_upload_url: '/admin/questions/upload-image',
            images_upload_base_path: '/',
            images_upload_credentials: true,
            automatic_uploads: true,
            file_picker_types: 'image',
            images_upload_handler: function (blobInfo, success, failure, progress) {
                return new Promise(function(resolve, reject) {
                    const xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', '/admin/questions/upload-image');
                    
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
                            failure('Upload failed: ' + xhr.status);
                            return;
                        }
                        
                        try {
                            const json = JSON.parse(xhr.responseText);
                            if (!json || !json.success) {
                                reject('Upload failed: ' + (json.message || 'Unknown error'));
                                failure('Upload failed: ' + (json.message || 'Unknown error'));
                                return;
                            }
                            resolve(json.url);
                            success(json.url);
                        } catch (e) {
                            reject('Invalid JSON response: ' + xhr.responseText);
                            failure('Invalid server response');
                        }
                    };
                    
                    xhr.onerror = function () {
                        reject('Image upload failed due to a network error.');
                        failure('Image upload failed due to a network error.');
                    };
                    
                    const formData = new FormData();
                    formData.append('image', blobInfo.blob(), blobInfo.filename());
                    xhr.send(formData);
                });
            },
            setup: function(editor) {
                contentEditor = editor;
                editor.on('change', function() {
                    editor.save();
                    
                    const questionType = document.getElementById('question_type')?.value;
                    if (['fill_blanks', 'note_completion', 'sentence_completion'].includes(questionType)) {
                        if (window.ListeningQuestionTypes) {
                            window.ListeningQuestionTypes.updateBlanks();
                        }
                    } else if (['dropdown_selection', 'form_completion'].includes(questionType)) {
                        if (window.ListeningQuestionTypes) {
                            window.ListeningQuestionTypes.updateDropdowns();
                        }
                    } else if (questionType === 'drag_drop') {
                        if (window.ListeningQuestionTypes) {
                            window.ListeningQuestionTypes.updateDragZones();
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
                
                document.querySelectorAll('.type-specific-panel').forEach(panel => {
                    panel.style.display = 'none';
                });

                if (window.ListeningQuestionTypes) {
                    window.ListeningQuestionTypes.init(selectedType);
                }

                const blankButtons = document.getElementById('blank-buttons');
                const dropdownButtons = document.getElementById('dropdown-buttons');
                const dragZoneButtons = document.getElementById('drag-zone-buttons');

                if (blankButtons) blankButtons.style.display = 'none';
                if (dropdownButtons) dropdownButtons.style.display = 'none';
                if (dragZoneButtons) dragZoneButtons.style.display = 'none';

                if (selectedType === 'fill_blanks' && blankButtons) {
                    blankButtons.style.display = 'flex';
                } else if (selectedType === 'dropdown_selection' && dropdownButtons) {
                    dropdownButtons.style.display = 'flex';
                } else if (selectedType === 'drag_drop' && dragZoneButtons) {
                    dragZoneButtons.style.display = 'flex';
                }
            });
            
            if (questionTypeSelect.value) {
                questionTypeSelect.dispatchEvent(new Event('change'));
            }
        }
        
        // Form submission
        const questionForm = document.getElementById('questionForm');
        if (questionForm) {
            questionForm.addEventListener('submit', function(e) {
                try {
                    if (typeof tinymce !== 'undefined') {
                        tinymce.triggerSave();
                    }
                    if (window.ListeningQuestionTypes) {
                        window.ListeningQuestionTypes.prepareSubmissionData();
                    }
                } catch (err) {
                    console.warn('Submit preparation error:', err);
                }
            });
        }
        
        // Part audio check
        function checkPartAudio(partNumber) {
            fetch(`/admin/test-sets/{{ $testSet->id }}/check-part-audio/${partNumber}`)
                .then(response => response.json())
                .then(data => {
                    const statusDiv = document.getElementById('part-audio-status');
                    if (data.hasAudio) {
                        statusDiv.innerHTML = '<div class="text-green-600">✅ Audio available</div>';
                    } else {
                        statusDiv.innerHTML = '<div class="text-red-600">❌ No audio uploaded</div>';
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        const partSelect = document.querySelector('[name="part_number"]');
        if (partSelect) {
            checkPartAudio(partSelect.value || 1);
            partSelect.addEventListener('change', function() {
                checkPartAudio(this.value);
            });
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.altKey) {
                const questionType = document.getElementById('question_type')?.value;
                
                if (questionType === 'fill_blanks' && (e.key === 'b' || e.key === 'B')) {
                    e.preventDefault();
                    insertListeningBlank();
                }
                
                if (questionType === 'dropdown_selection' && (e.key === 'd' || e.key === 'D')) {
                    e.preventDefault();
                    insertListeningDropdown();
                }
                
                if (questionType === 'drag_drop' && (e.key === 'g' || e.key === 'G')) {
                    e.preventDefault();
                    insertDragZone();
                }
            }
        });
    });
    
    // Global functions
    window.insertListeningBlank = function() {
        if (window.ListeningQuestionTypes) {
            const editor = window.contentEditor || tinymce.activeEditor;
            if (!editor) return;
            
            if (!window.listeningBlankCounter) {
                window.listeningBlankCounter = 0;
            }
            
            window.listeningBlankCounter++;
            const blankText = `[____${window.listeningBlankCounter}____]`;
            editor.insertContent(blankText);
            
            setTimeout(() => window.ListeningQuestionTypes.updateBlanks(), 100);
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
    
    window.insertDragZone = function() {
        if (window.ListeningQuestionTypes) {
            const editor = window.contentEditor || tinymce.activeEditor;
            if (!editor) return;
            
            if (!window.dragZoneCounter) {
                window.dragZoneCounter = 0;
            }
            
            window.dragZoneCounter++;
            const dragZoneText = `[DRAG_${window.dragZoneCounter}]`;
            editor.insertContent(dragZoneText);
            
            setTimeout(() => window.ListeningQuestionTypes.updateDragZones(), 100);
        }
    }
    </script>
    @endpush
</x-admin-layout>
