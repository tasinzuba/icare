<x-admin-layout>
    <x-slot:title>Edit Question - Reading</x-slot>

    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Edit Reading Question</h1>
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

    <!-- Question Info Card -->
    <div class="bg-white rounded-lg shadow-sm mb-6 border-l-4 border-green-500">
        <div class="p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Editing Question</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        <span class="font-medium text-green-600">{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</span>
                        <span class="mx-2">•</span>
                        <span>Passage {{ $question->part_number }}</span>
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-green-600">
                        #{{ $question->order_number }}
                    </div>
                    <p class="text-xs text-gray-500">Question Number</p>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.questions.update', $question) }}" method="POST" enctype="multipart/form-data" id="questionForm" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="test_set_id" value="{{ $testSet->id }}">

        <div class="space-y-6">
            <!-- Question Content -->
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Question Content</h3>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="space-y-6">
                        <!-- Instructions -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Instructions / Notes
                            </label>
                            <textarea id="instructions" name="instructions" class="tinymce-editor-simple">{{ old('instructions', $question->instructions) }}</textarea>
                        </div>

                        <!-- Passage Title Field (for passages only) -->
                        <div id="passage-title-field" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Passage Title
                            </label>
                            <input type="text" name="passage_title" id="passage_title"
                                   value="{{ old('passage_title', $question->passage_title ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500"
                                   placeholder="e.g., The History of Aviation">
                        </div>

                        <!-- Question Content -->
                        <div id="question-content-field">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Question <span class="text-red-500">*</span>
                            </label>
                            <div class="mb-3 flex flex-wrap gap-2" id="blank-buttons" style="display: none;">
                                <button type="button" onclick="insertBlank()" class="px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                                    Insert Blank
                                </button>
                                <span class="text-xs text-gray-500 flex items-center">
                                    <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">Alt+B</kbd>
                                </span>
                            </div>

                            <div class="mb-3 flex flex-wrap gap-2" id="dropdown-buttons" style="display: none;">
                                <button type="button" onclick="insertDropdown()" class="px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition-colors">
                                    Insert Dropdown
                                </button>
                                <span class="text-xs text-gray-500 flex items-center">
                                    <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">Alt+D</kbd>
                                </span>
                            </div>

                            <textarea id="content" name="content" class="tinymce-editor">{{ old('content', $question->content) }}</textarea>
                        </div>

                        <!-- Blanks Manager -->
                        <div id="blanks-manager" class="hidden">
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        <h4 class="text-sm font-medium text-gray-900">Fill in the Blanks Configuration</h4>
                                        <span id="blank-counter" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">0</span>
                                    </div>
                                    <button type="button" onclick="refreshBlanks()" class="text-xs text-blue-600 hover:text-blue-800">
                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Refresh
                                    </button>
                                </div>
                                <div id="blanks-list" class="space-y-2 max-h-64 overflow-y-auto">
                                    <!-- Dynamically populated -->
                                </div>
                            </div>
                        </div>

                        <!-- Passage Content (Hidden by default) -->
                        <div id="passage-content-field" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Passage Content <span class="text-red-500">*</span>
                            </label>
                            <textarea id="passage_text" name="passage_text" class="tinymce-editor">{{ old('passage_text', $question->content) }}</textarea>
                        </div>
                    </div>

                    <div class="space-y-4 sm:space-y-6">
                        @include('admin.questions.partials.question-settings', [
                            'questionTypes' => [
                                'passage' => '📄 Reading Passage',
                                'single_choice' => 'Single Choice (Radio)',
                                'multiple_choice' => 'Multiple Choice (Checkbox)',
                                'true_false' => 'True/False/Not Given',
                                'yes_no' => 'Yes/No/Not Given',
                                'matching_headings' => 'Matching Headings',
                                'matching_information' => 'Matching Information',
                                'matching_features' => 'Matching Features',
                                'sentence_completion' => 'Sentence Completion',
                                'summary_completion' => 'Summary Completion',
                                'short_answer' => 'Short Answer',
                                'fill_blanks' => 'Fill in the Blanks',
                                'dropdown_selection' => 'Dropdown / Summary (Inline)',
                                'matching_grid' => 'Matching Grid (Radio)'
                            ],
                            'question' => $question
                        ])

                        <!-- Question Group Field -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Question Group (Optional)
                            </label>
                            <input type="text" name="question_group"
                                   value="{{ old('question_group', $question->question_group ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                   placeholder="e.g., Questions 1-5">
                            <p class="text-xs text-gray-500 mt-1">
                                Group related questions together
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Options Manager -->
            @include('admin.questions.partials.options-manager')

            <!-- Enhanced Matching Headings Manager -->
            @include('admin.questions.partials.matching-headings-enhanced')

            <!-- Enhanced Sentence Completion Manager -->
            @include('admin.questions.partials.sentence-completion-enhanced')

            <!-- Action Buttons -->
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 sticky bottom-0 z-10 border-t sm:border-t-0 sm:relative">
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" name="action" value="save" class="flex-1 py-2.5 sm:py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors text-sm sm:text-base">
                        Update Question
                    </button>
                    <a href="{{ route('admin.questions.create', ['test_set' => $testSet->id]) }}" class="flex-1 py-2.5 sm:py-3 bg-green-600 text-white font-medium rounded-md hover:bg-green-700 transition-colors text-sm sm:text-base text-center">
                        Add New Question
                    </a>
                    <button type="button" onclick="previewQuestion()" class="flex-1 py-2.5 sm:py-3 border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 transition-colors text-sm sm:text-base">
                        Preview
                    </button>
                </div>
            </div>
        </div>
    </form>

    @include('admin.questions.partials.modals')

    @push('styles')
    <style>
        /* Matching Headings Styles */
        #matching-headings-card {
            transition: all 0.3s ease;
        }

        #matching-headings-container .heading-input:focus,
        #question-mappings-container .question-select:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .heading-input {
            transition: all 0.2s ease;
        }

        .question-select {
            cursor: pointer;
        }

        #matching-headings-container > div:hover,
        #question-mappings-container > div:hover {
            transform: translateX(2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Professional styles */
        .blank-placeholder {
            background-color: #FEF3C7;
            padding: 2px 8px;
            margin: 0 4px;
            border-bottom: 2px solid #F59E0B;
            border-radius: 2px;
            font-weight: 500;
            color: #92400E;
            cursor: not-allowed;
            user-select: none;
            display: inline-block;
            min-width: 60px;
            transition: background-color 0.2s ease;
        }

        .blank-placeholder:hover {
            background-color: #FDE68A;
        }

        .dropdown-placeholder {
            background-color: #D1FAE5;
            border: 1px solid #10B981;
            padding: 2px 8px;
            margin: 0 4px;
            border-radius: 4px;
            font-weight: 500;
            color: #064E3B;
            cursor: not-allowed;
            user-select: none;
            display: inline-block;
            min-width: 80px;
            transition: background-color 0.2s ease;
        }

        .dropdown-placeholder:hover {
            background-color: #A7F3D0;
        }

        /* Hide order field for passages */
        .passage-type #order-number-wrapper {
            display: none !important;
        }

        /* Professional success indicator */
        .blank-answer-input {
            transition: all 0.2s ease;
        }

        .blank-answer-input.validated {
            border-color: #10B981;
            background-color: #F0FDF4;
        }

        /* Clean notification style */
        .success-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #10B981;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .success-notification.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
    @endpush

    @push('scripts')
    <!-- TinyMCE CDN -->
    <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key', 'no-api-key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <!-- Matching Headings Fix -->
    <script src="{{ asset('js/admin/matching-headings-fix.js') }}" defer></script>
    <script src="{{ asset('js/admin/matching-headings-enhanced-fix.js') }}" defer></script>

    <!-- Sentence Completion Enhanced - FIXED VERSION -->
    <script src="{{ asset('js/admin/sentence-completion-enhanced.js') }}" defer></script>

    <script>
    // Pass question data to JavaScript for edit mode
    window.questionData = {
        id: {{ $question->id }},
        question_type: '{{ $question->question_type }}',
        order_number: {{ $question->order_number }},
        part_number: {{ $question->part_number }},
        marks: {{ $question->marks }},
        section_specific_data: @json($question->section_specific_data ?? []),
        options: @json($question->options ?? []),
        blanks: @json($question->blanks ?? [])
    };
    window.isEditMode = true;

    // Global variables
    let contentEditor = null;
    let passageEditor = null;
    let instructionEditor = null;
    let blankCounter = 0;
    let dropdownCounter = 0;


    // Initialize simple TinyMCE for instructions
    function initSimpleTinyMCE(selector) {
        const config = {
            selector: selector,
            height: 150,
            menubar: false,
            plugins: [
                'lists', 'link', 'charmap', 'code', 'table'
            ],
            toolbar: 'bold italic underline | fontsize | bullist numlist | alignleft aligncenter alignright | table tableprops tabledelete | link | removeformat code',
            table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
            table_appearance_options: true,
            table_grid: true,
            table_default_styles: {
                'width': '100%',
                'borderCollapse': 'collapse',
                'border': '1px solid #000000'
            },
            table_default_attributes: {
                'border': '1'
            },
            table_class_list: [
                {title: 'None', value: ''},
                {title: 'With Border', value: 'table-bordered'}
            ],
            extended_valid_elements: 'table[border|cellpadding|cellspacing|width|style|class],td[style|class],th[style|class],tr[style|class]',
            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt',
            content_css: '//www.tiny.cloud/css/codepen.min.css',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });

                if (selector.includes('instructions')) {
                    instructionEditor = editor;
                }
            }
        };

        tinymce.init(config);
    }

    // Initialize TinyMCE
    function initTinyMCE(selector, fillBlanksMode = false) {
        const config = {
            selector: selector,
            height: selector.includes('passage') ? 500 : 400,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                'preview', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: fillBlanksMode ?
                'undo redo | bold italic underline | fontsize | alignleft aligncenter alignright | bullist numlist | table tableprops tabledelete | removeformat code' :
                'undo redo | formatselect | fontsize | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table tableprops tabledelete | link image | removeformat code',
            table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
            table_appearance_options: true,
            table_grid: true,
            table_default_styles: {
                'width': '100%',
                'borderCollapse': 'collapse',
                'border': '1px solid #000000'
            },
            table_default_attributes: {
                'border': '1'
            },
            table_class_list: [
                {title: 'None', value: ''},
                {title: 'With Border', value: 'table-bordered'}
            ],
            extended_valid_elements: 'table[border|cellpadding|cellspacing|width|style|class],td[style|class],th[style|class],tr[style|class]',
            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt',
            images_upload_url: '{{ route("admin.questions.upload.image") }}',
            images_upload_base_path: '/',
            images_upload_credentials: true,
            automatic_uploads: true,
            images_upload_handler: function (blobInfo, success, failure, progress) {
                return new Promise(function(resolve, reject) {
                    const xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', '{{ route("admin.questions.upload.image") }}');

                    // Set the X-CSRF-TOKEN header
                    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

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
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();

                    // Check for fill blanks or dropdown selection
                    const questionType = document.getElementById('question_type')?.value;
                    if (fillBlanksMode || questionType === 'dropdown_selection' || questionType === 'matching_grid') {
                        updateBlanks();
                    }
                });

                // Store editor reference
                if (selector.includes('content')) {
                    contentEditor = editor;
                } else if (selector.includes('passage')) {
                    passageEditor = editor;
                }
            }
        };

        tinymce.init(config);
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize TinyMCE for content
        const questionType = document.getElementById('question_type');
        const isFillBlanks = questionType && questionType.value === 'fill_blanks';
        const isDropdownSelection = questionType && (questionType.value === 'dropdown_selection' || questionType.value === 'matching_grid');

        initTinyMCE('#content', isFillBlanks || isDropdownSelection);

        // Initialize simple editor for instructions
        initSimpleTinyMCE('#instructions');

        // Setup question type handler
        if (questionType) {
            questionType.addEventListener('change', handleReadingQuestionTypeChange);
            if (questionType.value) {
                handleReadingQuestionTypeChange.call(questionType);
            }
        }

        // Add option button handler
        const addOptionBtn = document.getElementById('add-option-btn');
        if (addOptionBtn) {
            addOptionBtn.addEventListener('click', function() {
                addOption();
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.altKey) {
                const questionType = document.getElementById('question_type')?.value;

                if ((questionType === 'fill_blanks' || questionType === 'sentence_completion') && (e.key === 'b' || e.key === 'B')) {
                    e.preventDefault();
                    insertBlank();
                }

                if ((questionType === 'dropdown_selection' || questionType === 'matching_grid') && (e.key === 'd' || e.key === 'D')) {
                    e.preventDefault();
                    insertDropdown();
                }

                if ((questionType === 'dropdown_selection' || questionType === 'matching_grid') && (e.key === 'b' || e.key === 'B')) {
                    e.preventDefault();
                    insertDropdown(); // For dropdown/matching-grid, Alt+B also inserts dropdown
                }
            }
        });

        // Form submission handler
        const form = document.getElementById('questionForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                const questionType = document.getElementById('question_type').value;

                if (!questionType) {
                    e.preventDefault();
                    alert('Please select a question type');
                    return false;
                }

                // Save TinyMCE content
                try {
                    if (contentEditor) contentEditor.save();
                    if (passageEditor) passageEditor.save();
                    if (instructionEditor) instructionEditor.save();
                } catch (err) {
                    console.warn('TinyMCE save error:', err);
                }

                if (questionType === 'passage') {
                    const passageContent = document.getElementById('passage_text').value;
                    if (!passageContent.trim()) {
                        e.preventDefault();
                        alert('Please enter passage content');
                        return false;
                    }
                    document.getElementById('content').value = passageContent;
                }

                // Handle matching headings data
                if (questionType === 'matching_headings') {
                    const dataInput = document.getElementById('matching_headings_data');
                    if (dataInput) {
                        const data = JSON.parse(dataInput.value || '{}');

                        // Validate headings
                        if (!data.headings || data.headings.length < 2) {
                            e.preventDefault();
                            alert('Please add at least 2 headings');
                            return false;
                        }

                        // Validate mappings
                        if (!data.mappings || data.mappings.length === 0) {
                            e.preventDefault();
                            alert('Please map all questions to headings');
                            return false;
                        }

                        // Check if all headings have text
                        const emptyHeadings = data.headings.filter(h => !h.text.trim());
                        if (emptyHeadings.length > 0) {
                            e.preventDefault();
                            alert('Please fill in all heading texts');
                            return false;
                        }

                        // Check if all mappings have correct answers
                        const incompleteMappings = data.mappings.filter(m => !m.correct);
                        if (incompleteMappings.length > 0) {
                            e.preventDefault();
                            alert('Please select correct heading for all questions');
                            return false;
                        }

                        // Store headings in options format for backward compatibility
                        data.headings.forEach((heading, index) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `options[${index}][content]`;
                            input.value = heading.text;
                            form.appendChild(input);
                        });

                        // Store the complete JSON data
                        const jsonInput = document.createElement('input');
                        jsonInput.type = 'hidden';
                        jsonInput.name = 'matching_headings_json';
                        jsonInput.value = JSON.stringify(data);
                        form.appendChild(jsonInput);

                        // Add a dummy correct_option to satisfy validation (not used for matching_headings)
                        const dummyCorrectOption = document.createElement('input');
                        dummyCorrectOption.type = 'hidden';
                        dummyCorrectOption.name = 'correct_option';
                        dummyCorrectOption.value = '0';
                        form.appendChild(dummyCorrectOption);

                        // Set marks based on number of questions
                        const marksInput = document.querySelector('input[name="marks"]');
                        if (marksInput) {
                            marksInput.value = data.mappings.length;
                        }

                        // IMPORTANT: Set content field if empty
                        const contentField = document.getElementById('content');
                        if (contentField) {
                            const startNum = parseInt(document.getElementById('mh_start_number')?.value) || 1;
                            const count = data.mappings.length || 5;
                            const endNum = startNum + count - 1;
                            const defaultContent = `Questions ${startNum}-${endNum}\n\nChoose the correct heading for each paragraph from the list of headings below.`;

                            // Set content in TinyMCE if exists
                            if (contentEditor) {
                                contentEditor.setContent(defaultContent);
                                contentEditor.save();
                                console.log('Content set via TinyMCE');
                            } else {
                                contentField.value = defaultContent;
                                console.log('Content set directly');
                            }

                            console.log('Content field set to:', defaultContent);
                        }

                        console.log('Matching Headings submission data:', data);
                        console.log('Form data being submitted:', new FormData(form));
                        }
                        }

        // Handle sentence completion data
        if (questionType === 'sentence_completion') {
            const dataInput = document.getElementById('sentence_completion_data');
            if (dataInput) {
                const data = JSON.parse(dataInput.value || '{}');

                // Validate options
                if (!data.options || data.options.length < 2) {
                    e.preventDefault();
                    alert('Please add at least 2 answer options');
                    return false;
                }

                // Validate sentences
                if (!data.sentences || data.sentences.length === 0) {
                    e.preventDefault();
                    alert('Please add at least one sentence');
                    return false;
                }

                // Check if all sentences have correct answers
                const incompleteSentences = data.sentences.filter(s => !s.correctAnswer);
                if (incompleteSentences.length > 0) {
                    e.preventDefault();
                    alert('Please select correct answer for all sentences');
                    return false;
                }

                // Sync sc_start_number to main form's order_number
                const scStartNum = document.getElementById('sc_start_number');
                const mainOrderInput = document.querySelector('#order-number-wrapper input[name="order_number"]');
                if (scStartNum && mainOrderInput) {
                    mainOrderInput.value = scStartNum.value;
                }

                // Generate question content
                const content = SentenceCompletionManager.generateQuestionContent();

                // Set content field
                if (contentEditor) {
                    contentEditor.setContent(content);
                    contentEditor.save();
                } else {
                    document.getElementById('content').value = content;
                }

                // Store options in the format expected by backend
                data.options.forEach((option, index) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `options[${index}][content]`;
                    input.value = option.text;
                    form.appendChild(input);
                });

                // Store correct answers as blank_answers
                data.sentences.forEach((sentence, index) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `blank_answers[]`;
                    input.value = sentence.correctAnswer;
                    form.appendChild(input);
                });

                // Set marks based on number of sentences
                const marksInput = document.querySelector('input[name="marks"]');
                if (marksInput) {
                    marksInput.value = data.sentences.length;
                }

                // Add dummy correct_option
                const dummyCorrectOption = document.createElement('input');
                dummyCorrectOption.type = 'hidden';
                dummyCorrectOption.name = 'correct_option';
                dummyCorrectOption.value = '0';
                form.appendChild(dummyCorrectOption);

                console.log('Sentence Completion submission data:', data);
            }
        }

                return true;
            });
        }

        // Load existing data for edit mode
        setTimeout(loadExistingData, 500);
    });

    // Handle question type changes
    function handleReadingQuestionTypeChange() {
        const type = this.value;
        const questionContentField = document.getElementById('question-content-field');
        const passageContentField = document.getElementById('passage-content-field');
        const passageTitleField = document.getElementById('passage-title-field');
        const blanksManager = document.getElementById('blanks-manager');
        const blankButtons = document.getElementById('blank-buttons');
        const dropdownButtons = document.getElementById('dropdown-buttons');

        const optionsCard = document.getElementById('options-card');
        const matchingHeadingsCard = document.getElementById('matching-headings-card');
        const sentenceCompletionCard = document.getElementById('sentence-completion-card');

        // Find order number wrapper correctly
        const orderNumberInput = document.querySelector('input[name="order_number"]');
        const orderNumberWrapper = orderNumberInput ? orderNumberInput.closest('div') : null;

        // Reset displays
        questionContentField?.classList.remove('hidden');
        passageContentField?.classList.add('hidden');
        passageTitleField?.classList.add('hidden');
        blanksManager?.classList.add('hidden');
        if (blankButtons) blankButtons.style.display = 'none';
        if (dropdownButtons) dropdownButtons.style.display = 'none';

        if (orderNumberWrapper) orderNumberWrapper.style.display = 'block';
        if (matchingHeadingsCard) matchingHeadingsCard.style.display = 'none';
        if (sentenceCompletionCard) sentenceCompletionCard.style.display = 'none';

        // Add/remove passage class to form
        const form = document.getElementById('questionForm');
        if (type === 'passage') {
            form?.classList.add('passage-type');
        } else {
            form?.classList.remove('passage-type');
        }

        // Define option types that need the options card
        const optionTypes = ['single_choice', 'multiple_choice', 'true_false', 'yes_no', 'matching',
            'matching_information', 'matching_features'];

        // Handle options card visibility
        if (optionsCard) {
            if (optionTypes.includes(type)) {
                optionsCard.classList.remove('hidden');
                // Don't setup default options in edit mode if we have existing options
                if (!window.isEditMode || !window.questionData?.options?.length) {
                    setupDefaultOptions(type);
                }
            } else {
                optionsCard.classList.add('hidden');
            }
        }

        // Special handling for matching_headings
        if (type === 'matching_headings') {
            console.log('=== Matching Headings Selected ===');

            if (optionsCard) {
                optionsCard.classList.add('hidden');
                console.log('Options card hidden');
            }

            // Hide order number, marks and question content fields for matching headings
            const orderNumberWrapper = document.querySelector('input[name="order_number"]')?.closest('div');
            const marksWrapper = document.querySelector('input[name="marks"]')?.closest('div');

            if (orderNumberWrapper) {
                orderNumberWrapper.style.display = 'none';
                console.log('Order number field hidden');
            }
            if (marksWrapper) {
                marksWrapper.style.display = 'none';
                console.log('Marks field hidden');
            }
            if (questionContentField) {
                questionContentField.style.display = 'none';
                console.log('Question content field hidden');
            }

            if (matchingHeadingsCard) {
                console.log('Matching headings card found, showing...');
                matchingHeadingsCard.style.display = 'block';

                // Small delay to ensure DOM is ready
                setTimeout(() => {
                    // Initialize matching headings manager
                    if (window.MatchingHeadingsEnhanced) {
                        console.log('Initializing MatchingHeadingsEnhanced...');
                        window.MatchingHeadingsEnhanced.init();
                        console.log('MatchingHeadingsEnhanced initialized successfully');
                    } else if (window.MatchingHeadingsManager) {
                        console.log('Initializing MatchingHeadingsManager (fallback)...');

                        // Reset before init to prevent duplicates
                        window.MatchingHeadingsManager.headingCount = 0;
                        window.MatchingHeadingsManager.questionCount = 0;
                        window.MatchingHeadingsManager.headings = [];
                        window.MatchingHeadingsManager.mappings = [];

                        // Clear containers
                        const headingsContainer = document.getElementById('matching-headings-container');
                        const mappingsContainer = document.getElementById('question-mappings-container');
                        if (headingsContainer) headingsContainer.innerHTML = '';
                        if (mappingsContainer) mappingsContainer.innerHTML = '';

                        window.MatchingHeadingsManager.init();
                        console.log('MatchingHeadingsManager initialized successfully');

                        // Hide fallback button if shown
                        const fallbackBtn = document.getElementById('matching-headings-init-fallback');
                        if (fallbackBtn) {
                            fallbackBtn.style.display = 'none';
                        }
                    } else {
                        console.error('No matching headings manager found in window!');

                        // Show fallback button
                        const fallbackBtn = document.getElementById('matching-headings-init-fallback');
                        if (fallbackBtn) {
                            fallbackBtn.style.display = 'block';
                        }
                    }
                }, 100);
            } else {
                console.error('Matching headings card element not found!');
            }
        } else {
            // Show order number and marks fields for other question types
            const orderNumberWrapper = document.querySelector('input[name="order_number"]')?.closest('div');
            const marksWrapper = document.querySelector('input[name="marks"]')?.closest('div');

            if (orderNumberWrapper && type !== 'passage') {
                orderNumberWrapper.style.display = 'block';
            }
            if (marksWrapper) {
                marksWrapper.style.display = 'block';
            }
            if (questionContentField && type !== 'passage') {
                questionContentField.style.display = 'block';
            }
        }

        if (type === 'passage') {
            // Hide order number for passages
            if (orderNumberWrapper) {
                orderNumberWrapper.style.display = 'none';
            }

            questionContentField?.classList.add('hidden');
            passageContentField?.classList.remove('hidden');
            passageTitleField?.classList.remove('hidden');

            // Initialize passage editor with TinyMCE
            if (!passageEditor) {
                setTimeout(() => {
                    initTinyMCE('#passage_text', false);
                }, 100);
            }

            // Set defaults for passage
            const orderInput = document.querySelector('input[name="order_number"]');
            const marksInput = document.querySelector('input[name="marks"]');
            if (orderInput) orderInput.value = '0';
            if (marksInput) marksInput.value = '0';

        } else if (type === 'dropdown_selection' || type === 'matching_grid') {
            // Show dropdown buttons and manager (matching_grid authors identically to dropdown_selection)
            if (dropdownButtons) dropdownButtons.style.display = 'flex';
            blanksManager?.classList.remove('hidden');

            // In edit mode, loadExistingData handles blank loading
            if (!window.isEditMode) {
                setTimeout(updateBlanks, 500);
            }
        } else if (type === 'fill_blanks') {
            // Show blank buttons and manager
            if (blankButtons) blankButtons.style.display = 'flex';
            blanksManager?.classList.remove('hidden');

            // Re-initialize editor for fill blanks mode
            if (contentEditor) {
                contentEditor.destroy();
                contentEditor = null;
                setTimeout(() => {
                    initTinyMCE('#content', true);
                }, 100);
            }

            // In edit mode, loadExistingData handles blank loading
            if (!window.isEditMode) {
                setTimeout(updateBlanks, 500);
            }
        } else if (type === 'sentence_completion') {
            console.log('=== Sentence Completion Selected ===');

            // Hide regular options card
            if (optionsCard) {
                optionsCard.classList.add('hidden');
            }

            // Hide order number, marks and question content fields
            const orderNumberWrapper = document.querySelector('input[name="order_number"]')?.closest('div');
            const marksWrapper = document.querySelector('input[name="marks"]')?.closest('div');

            if (orderNumberWrapper) {
                orderNumberWrapper.style.display = 'none';
            }
            if (marksWrapper) {
                marksWrapper.style.display = 'none';
            }
            if (questionContentField) {
                questionContentField.style.display = 'none';
            }

            // Set default instruction for sentence completion if empty
            if (instructionEditor) {
                const currentContent = instructionEditor.getContent();
                if (!currentContent || currentContent.trim() === '') {
                    instructionEditor.setContent('Complete the sentences below. Choose NO MORE THAN ONE WORD from the list for each answer.');
                }
            } else {
                const instructionField = document.getElementById('instructions');
                if (instructionField && (!instructionField.value || instructionField.value.trim() === '')) {
                    instructionField.value = 'Complete the sentences below. Choose NO MORE THAN ONE WORD from the list for each answer.';
                }
            }

            // Show sentence completion card
            if (sentenceCompletionCard) {
                console.log('Sentence completion card found, showing...');
                sentenceCompletionCard.style.display = 'block';

                // Initialize sentence completion manager
                setTimeout(() => {
                    if (window.SentenceCompletionManager) {
                        console.log('Initializing SentenceCompletionManager...');
                        window.SentenceCompletionManager.init();
                        console.log('SentenceCompletionManager initialized successfully');
                    } else {
                        console.error('SentenceCompletionManager not found!');
                    }
                }, 100);
            }
        } else {
            // Re-initialize normal editor if coming from fill blanks
            if (contentEditor && blankButtons && blankButtons.style.display === 'flex') {
                const content = contentEditor.getContent();
                contentEditor.destroy();
                contentEditor = null;
                setTimeout(() => {
                    initTinyMCE('#content', false);
                    if (contentEditor) {
                        contentEditor.setContent(content);
                    }
                }, 100);
            }
        }
    }

    // Setup default options based on question type
    function setupDefaultOptions(type) {
        const container = document.getElementById('options-container');
        if (!container) return;

        container.innerHTML = '';

        if (type === 'true_false') {
            addOption('TRUE', true);
            addOption('FALSE', false);
            addOption('NOT GIVEN', false);
            const addBtn = document.getElementById('add-option-btn');
            if (addBtn) addBtn.style.display = 'none';
        } else if (type === 'yes_no') {
            addOption('YES', true);
            addOption('NO', false);
            addOption('NOT GIVEN', false);
            const addBtn = document.getElementById('add-option-btn');
            if (addBtn) addBtn.style.display = 'none';
        } else if (type === 'sentence_completion') {
            // Add default options for sentence completion
            // These will be the choices available in dropdowns
            for (let i = 0; i < 8; i++) {
                addOption('', false);
            }
            const addBtn = document.getElementById('add-option-btn');
            if (addBtn) {
                addBtn.style.display = 'inline-block';
                addBtn.textContent = 'Add Option';
            }
        } else {
            // Default to 4 empty options
            for (let i = 0; i < 4; i++) {
                addOption('', i === 0);
            }
            const addBtn = document.getElementById('add-option-btn');
            if (addBtn) addBtn.style.display = 'inline-block';
        }
    }

    // Add option function
    function addOption(content = '', isCorrect = false) {
        const container = document.getElementById('options-container');
        if (!container) return;

        const index = container.children.length;
        const questionType = document.getElementById('question_type').value;

        const optionDiv = document.createElement('div');
        optionDiv.className = 'option-item flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-200';

        if (questionType === 'multiple_choice') {
            // Checkbox for multiple choice
            optionDiv.innerHTML = `
                <input type="checkbox" name="correct_options[]" value="${index}"
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
        } else {
            // Radio button for single choice
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
        }

        container.appendChild(optionDiv);
    }

    // Remove option
    window.removeOption = function(btn) {
        btn.parentElement.remove();
        reindexOptions();
    };

    // Reindex options after removal
    function reindexOptions() {
        const options = document.querySelectorAll('#options-container > div');
        options.forEach((option, index) => {
            const radio = option.querySelector('input[type="radio"]');
            const checkbox = option.querySelector('input[type="checkbox"]');

            if (radio) radio.value = index;
            if (checkbox) checkbox.value = index;

            option.querySelector('input[type="text"]').name = `options[${index}][content]`;
            option.querySelector('span.font-medium').textContent = String.fromCharCode(65 + index) + '.';
        });
    }

    // Insert blank function
    window.insertBlank = function() {
        if (!contentEditor) {
            return;
        }

        blankCounter++;
        const blankText = `[____${blankCounter}____]`;

        contentEditor.insertContent(blankText);

        showNotification(`Blank ${blankCounter} added`, 'success');

        setTimeout(updateBlanks, 100);
    };

    // Insert dropdown function for dropdown_selection question type
    window.insertDropdown = function() {
        console.log('insertDropdown called');
        if (!contentEditor) {
            console.error('No content editor!');
            return;
        }

        dropdownCounter++;
        const dropdownText = `[DROPDOWN_${dropdownCounter}]`;
        console.log('Inserting dropdown:', dropdownText);

        contentEditor.insertContent(dropdownText);

        showNotification(`Dropdown ${dropdownCounter} added`, 'success');

        setTimeout(updateBlanks, 100);
    };

    // Professional notification function
    function showNotification(message, type = 'info') {
        const existing = document.querySelector('.success-notification');
        if (existing) existing.remove();

        const notification = document.createElement('div');
        notification.className = 'success-notification';
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 2000);
    }

    // Store blank answers
    const blankAnswersStore = {};
    const dropdownStore = {
        options: {},
        correct: {}
    };
    let editDataLoaded = false;

    // Wait for TinyMCE to be ready, then call updateBlanks
    function waitForEditorThenUpdateBlanks() {
        editDataLoaded = true;
        let attempts = 0;
        const maxAttempts = 30;
        const interval = setInterval(() => {
            attempts++;
            if (contentEditor) {
                clearInterval(interval);
                updateBlanks();
            } else if (attempts >= maxAttempts) {
                clearInterval(interval);
                console.error('TinyMCE editor did not initialize in time');
            }
        }, 200);
    }

    // Update blanks display
    window.updateBlanks = function() {
        if (!contentEditor) {
            console.log('No content editor found');
            return;
        }

        saveCurrentBlankValues();

        const content = contentEditor.getContent({ format: 'text' });
        console.log('Content:', content);

        // Find all blanks, dropdowns, and heading dropdowns using regex
        const blankMatches = content.match(/\[____\d+____\]/g) || [];
        const dropdownMatches = content.match(/\[DROPDOWN_\d+\]/g) || [];

        console.log('Found dropdowns:', dropdownMatches);

        const blanksManager = document.getElementById('blanks-manager');
        const blanksList = document.getElementById('blanks-list');

        if (!blanksManager || !blanksList) return;

        if (blankMatches.length > 0 || dropdownMatches.length > 0) {
            blanksManager.classList.remove('hidden');
            blanksList.innerHTML = '';

            // Process blanks
            blankMatches.forEach((match) => {
                const num = match.match(/\d+/)[0];
                const itemDiv = document.createElement('div');
                itemDiv.className = 'flex items-center space-x-2 p-2 bg-white rounded border border-gray-200';

                const storedValue = blankAnswersStore[num] || '';

                itemDiv.innerHTML = `
                    <span class="text-sm font-medium text-gray-700 min-w-[80px]">Blank ${num}:</span>
                    <input type="text"
                           id="blank_answer_${num}"
                           name="blank_answers[]"
                           class="blank-answer-input flex-1 px-3 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter correct answer"
                           value="${storedValue}"
                           data-blank-num="${num}"
                           required>
                    <button type="button" onclick="removeBlank(${num})" class="text-red-500 hover:text-red-700 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;

                blanksList.appendChild(itemDiv);

                const input = itemDiv.querySelector(`#blank_answer_${num}`);
                if (input) {
                    input.addEventListener('input', function() {
                        blankAnswersStore[num] = this.value;

                        if (this.value.trim()) {
                            this.classList.add('validated');
                        } else {
                            this.classList.remove('validated');
                        }
                    });

                    if (input.value.trim()) {
                        input.classList.add('validated');
                    }
                }
            });

            // Process dropdowns
            dropdownMatches.forEach((match) => {
                const num = match.match(/\d+/)[0];
                console.log('Processing dropdown:', num);

                const itemDiv = document.createElement('div');
                itemDiv.className = 'flex items-center space-x-2 p-2 bg-white rounded border border-gray-200';

                const storedOptions = dropdownStore.options[num] || '';
                const storedCorrect = dropdownStore.correct[num] || '0';
                console.log('Stored options for dropdown', num, ':', storedOptions);

                itemDiv.innerHTML = `
                    <span class="text-sm font-medium text-gray-700 min-w-[80px]">Dropdown ${num}:</span>
                    <input type="text"
                           id="dropdown_options_${num}"
                           value="${storedOptions}"
                           name="dropdown_options[]"
                           class="flex-1 px-3 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Options (comma separated, e.g: good, better, best)"
                           data-dropdown-num="${num}" required>
                    <select id="dropdown_correct_${num}" name="dropdown_correct[]"
                            class="px-3 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                            data-dropdown-num="${num}">
                        ${storedOptions ? storedOptions.split(',').map((opt, idx) => `<option value="${idx}" ${idx == storedCorrect ? 'selected' : ''}>${opt.trim()}</option>`).join('') : '<option value="">Enter options first</option>'}
                    </select>
                    <button type="button" onclick="removeDropdown(${num})" class="text-red-500 hover:text-red-700 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;

                blanksList.appendChild(itemDiv);

                const optionsInput = itemDiv.querySelector(`#dropdown_options_${num}`);
                const correctSelect = itemDiv.querySelector(`#dropdown_correct_${num}`);

                if (optionsInput) {
                    optionsInput.addEventListener('input', function() {
                        dropdownStore.options[num] = this.value;
                        updateDropdownSelect(num, this.value);
                    });
                }

                if (correctSelect) {
                    correctSelect.addEventListener('change', function() {
                        dropdownStore.correct[num] = this.value;
                    });
                }
            });

            const counterBadge = document.getElementById('blank-counter');
            if (counterBadge) {
                const total = blankMatches.length + dropdownMatches.length;
                counterBadge.textContent = total;
                counterBadge.style.display = total > 0 ? 'inline-flex' : 'none';
            }

            // Update counters
            if (blankMatches.length > 0) {
                blankCounter = Math.max(...blankMatches.map(m => parseInt(m.match(/\d+/)[0])));
            }
            if (dropdownMatches.length > 0) {
                dropdownCounter = Math.max(...dropdownMatches.map(m => parseInt(m.match(/DROPDOWN_(\d+)/)[1])));
            }

        } else {
            blanksManager.classList.add('hidden');
            const counterBadge = document.getElementById('blank-counter');
            if (counterBadge) {
                counterBadge.style.display = 'none';
            }
        }
    };

    // Save current blank values
    function saveCurrentBlankValues() {
        document.querySelectorAll('.blank-answer-input').forEach(input => {
            const num = input.getAttribute('data-blank-num');
            if (num) {
                const val = input.value;
                if (val || !blankAnswersStore[num]) {
                    blankAnswersStore[num] = val;
                }
            }
        });

        document.querySelectorAll('[id^="dropdown_options_"]').forEach(input => {
            const num = input.getAttribute('data-dropdown-num');
            if (num) {
                const val = input.value;
                if (val || !dropdownStore.options[num]) {
                    dropdownStore.options[num] = val;
                }
            }
        });

        document.querySelectorAll('[id^="dropdown_correct_"]').forEach(select => {
            const num = select.getAttribute('data-dropdown-num');
            if (num) {
                const val = select.value;
                if (val || !dropdownStore.correct[num]) {
                    dropdownStore.correct[num] = val;
                }
            }
        });
    }

    // Update dropdown select options
    function updateDropdownSelect(num, optionsString) {
        const select = document.querySelector(`#dropdown_correct_${num}`);
        if (select) {
            const currentValue = select.value;
            const options = optionsString.split(',').map(opt => opt.trim());

            select.innerHTML = options.map((opt, idx) =>
                `<option value="${idx}" ${idx == currentValue ? 'selected' : ''}>${opt}</option>`
            ).join('');
        }
    }

    // Remove blank
    window.removeBlank = function(num) {
        if (contentEditor) {
            delete blankAnswersStore[num];

            let content = contentEditor.getContent();
            const regex = new RegExp(`\\[____${num}____\\]`, 'g');
            content = content.replace(regex, '');
            contentEditor.setContent(content);

            renumberBlanks();
            showNotification('Blank removed', 'info');
        }
    };

    // Remove dropdown
    window.removeDropdown = function(num) {
        if (contentEditor) {
            delete dropdownStore.options[num];
            delete dropdownStore.correct[num];

            let content = contentEditor.getContent();
            const regex = new RegExp(`\\[DROPDOWN_${num}\\]`, 'g');
            content = content.replace(regex, '');
            contentEditor.setContent(content);

            renumberDropdowns();
            showNotification('Dropdown removed', 'info');
        }
    };


    // Renumber blanks after deletion
    function renumberBlanks() {
        if (!contentEditor) return;

        let content = contentEditor.getContent();

        // Find all blank patterns
        const blankRegex = /\[____\d+____\]/g;
        const matches = content.match(blankRegex) || [];

        // Create a new mapping for renumbering
        const newAnswersStore = {};

        // Renumber blanks
        matches.forEach((match, index) => {
            const oldNum = match.match(/\d+/)[0];
            const newNum = index + 1;
            content = content.replace(match, `[____${newNum}____]`);

            // Transfer answer data to new number
            if (blankAnswersStore[oldNum]) {
                newAnswersStore[newNum] = blankAnswersStore[oldNum];
            }
        });

        Object.assign(blankAnswersStore, newAnswersStore);
        blankCounter = matches.length;
        contentEditor.setContent(content);

        setTimeout(updateBlanks, 100);
    }

    // Renumber dropdowns after deletion
    function renumberDropdowns() {
        if (!contentEditor) return;

        let content = contentEditor.getContent();

        // Find all dropdown patterns
        const dropdownRegex = /\[DROPDOWN_\d+\]/g;
        const matches = content.match(dropdownRegex) || [];

        const newOptionsStore = {};
        const newCorrectStore = {};

        // Renumber dropdowns
        matches.forEach((match, index) => {
            const oldNum = match.match(/\d+/)[0];
            const newNum = index + 1;

            content = content.replace(match, `[DROPDOWN_${newNum}]`);

            // Transfer dropdown data to new number
            if (dropdownStore.options[oldNum]) {
                newOptionsStore[newNum] = dropdownStore.options[oldNum];
            }
            if (dropdownStore.correct[oldNum]) {
                newCorrectStore[newNum] = dropdownStore.correct[oldNum];
            }
        });

        dropdownStore.options = newOptionsStore;
        dropdownStore.correct = newCorrectStore;

        dropdownCounter = matches.length;
        contentEditor.setContent(content);

        setTimeout(updateBlanks, 100);
    }


    // Refresh blanks
    window.refreshBlanks = function() {
        updateBlanks();
        showNotification('Configuration refreshed', 'info');
    };

    // Make add option available globally
    window.addOption = addOption;

    // #5: "Add Bulk Options" for single/multiple choice — handlers were referenced by the button
    // but never defined on this page (lived in an unloaded JS file), so the button did nothing.
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
        const container = document.getElementById('options-container');
        if (!bulkText || !container) return;
        const options = bulkText.value.split('\n').map(o => o.trim()).filter(o => o.length);
        if (options.length === 0) return;
        container.innerHTML = '';
        options.forEach((opt, index) => addOption(opt, index === 0));
        closeBulkOptions();
    };

    // Enhanced Matching Headings Implementation
    const MatchingHeadingsManager = {
        headingCount: 0,
        questionCount: 0,
        headings: [],
        mappings: [],

        init() {
            console.log('MatchingHeadingsManager.init() called');

            // Clear any existing event listeners
            const addHeadingBtn = document.getElementById('add-heading-btn');
            const addQuestionBtn = document.getElementById('add-question-mapping-btn');

            if (addHeadingBtn) {
                // Remove existing listeners
                const newBtn = addHeadingBtn.cloneNode(true);
                addHeadingBtn.parentNode.replaceChild(newBtn, addHeadingBtn);

                // Add new listener
                document.getElementById('add-heading-btn').addEventListener('click', () => {
                    console.log('Add heading button clicked');
                    this.addHeading();
                });
            } else {
                console.error('Add heading button not found!');
            }

            if (addQuestionBtn) {
                // Remove existing listeners
                const newBtn = addQuestionBtn.cloneNode(true);
                addQuestionBtn.parentNode.replaceChild(newBtn, addQuestionBtn);

                // Add new listener
                document.getElementById('add-question-mapping-btn').addEventListener('click', () => {
                    console.log('Add question mapping button clicked');
                    this.addQuestionMapping();
                });
            } else {
                console.error('Add question mapping button not found!');
            }

            // Add default items only if containers are empty
            const headingsContainer = document.getElementById('matching-headings-container');
            const mappingsContainer = document.getElementById('question-mappings-container');

            if (headingsContainer && headingsContainer.children.length === 0) {
                console.log('Adding default headings...');
                // Add 5 headings by default
                for (let i = 0; i < 5; i++) {
                    this.addHeading();
                }
            }

            if (mappingsContainer && mappingsContainer.children.length === 0) {
                console.log('Adding default question mappings...');
                // Add 3 question mappings by default
                for (let i = 0; i < 3; i++) {
                    this.addQuestionMapping();
                }
            }

            console.log('MatchingHeadingsManager.init() completed');
        },

        addHeading(content = '') {
            const container = document.getElementById('matching-headings-container');
            if (!container) return;

            const index = this.headingCount;
            const letter = String.fromCharCode(65 + index);

            const headingDiv = document.createElement('div');
            headingDiv.className = 'flex items-center gap-2 p-3 bg-white rounded border border-gray-200';
            headingDiv.setAttribute('data-heading-index', index);
            headingDiv.innerHTML = `
                <span class="font-semibold text-gray-700 min-w-[30px]">${letter}.</span>
                <input type="text"
                       data-heading-id="${letter}"
                       value="${content}"
                       class="heading-input flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                       placeholder="Enter heading text..."
                       onkeyup="MatchingHeadingsManager.updateDropdowns()"
                       required>
                <button type="button" onclick="MatchingHeadingsManager.removeHeading(${index})"
                        class="text-red-500 hover:text-red-700 p-1">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;

            container.appendChild(headingDiv);
            this.headingCount++;

            // Update counter
            document.getElementById('heading-count').textContent = `${this.headingCount} headings`;

            // Enable question mapping button
            const addQuestionBtn = document.getElementById('add-question-mapping-btn');
            if (addQuestionBtn && this.headingCount >= 2) {
                addQuestionBtn.disabled = false;
            }

            // Update all dropdowns
            this.updateDropdowns();
        },

        removeHeading(index) {
            if (this.headingCount <= 2) {
                alert('You must have at least 2 headings.');
                return;
            }

            const container = document.getElementById('matching-headings-container');
            const headingDiv = container.querySelector(`[data-heading-index="${index}"]`);
            if (headingDiv) {
                headingDiv.remove();
                this.reindexHeadings();
            }
        },

        reindexHeadings() {
            const container = document.getElementById('matching-headings-container');
            const headings = container.querySelectorAll('div');
            this.headingCount = 0;

            headings.forEach((heading, index) => {
                const letter = String.fromCharCode(65 + index);
                heading.setAttribute('data-heading-index', index);
                heading.querySelector('span').textContent = letter + '.';
                heading.querySelector('.heading-input').setAttribute('data-heading-id', letter);

                const btn = heading.querySelector('button');
                btn.setAttribute('onclick', `MatchingHeadingsManager.removeHeading(${index})`);

                this.headingCount++;
            });

            // Update counter
            document.getElementById('heading-count').textContent = `${this.headingCount} headings`;

            // Update all dropdowns
            this.updateDropdowns();

            // Disable add question button if less than 2 headings
            const addQuestionBtn = document.getElementById('add-question-mapping-btn');
            if (addQuestionBtn && this.headingCount < 2) {
                addQuestionBtn.disabled = true;
            }
        },

        addQuestionMapping() {
            const container = document.getElementById('question-mappings-container');
            if (!container) return;

            const index = this.questionCount;
            const paragraphLetter = String.fromCharCode(65 + index);

            const mappingDiv = document.createElement('div');
            mappingDiv.className = 'flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200';
            mappingDiv.setAttribute('data-question-index', index);
            mappingDiv.innerHTML = `
                <span class="font-medium text-gray-700 min-w-[140px]">
                    Question ${index + 1} - Paragraph ${paragraphLetter}:
                </span>
                <select class="question-select flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                        data-question-index="${index}"
                        onchange="MatchingHeadingsManager.updateMappingData()">
                    <option value="">Select correct heading</option>
                    ${this.getHeadingOptions()}
                </select>
                <button type="button" onclick="MatchingHeadingsManager.removeQuestionMapping(${index})"
                        class="text-red-500 hover:text-red-700 p-1">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;

            container.appendChild(mappingDiv);
            this.questionCount++;

            // Update counter
            document.getElementById('question-count').textContent = `${this.questionCount} questions`;

            // Update mapping data
            this.updateMappingData();
        },

        removeQuestionMapping(index) {
            const container = document.getElementById('question-mappings-container');
            const mappingDiv = container.querySelector(`[data-question-index="${index}"]`);
            if (mappingDiv) {
                mappingDiv.remove();
                this.reindexQuestions();
            }
        },

        reindexQuestions() {
            const container = document.getElementById('question-mappings-container');
            const questions = container.querySelectorAll('div');
            this.questionCount = 0;

            questions.forEach((question, index) => {
                const paragraphLetter = String.fromCharCode(65 + index);
                question.setAttribute('data-question-index', index);
                question.querySelector('span').textContent = `Question ${index + 1} - Paragraph ${paragraphLetter}:`;
                question.querySelector('.question-select').setAttribute('data-question-index', index);

                const btn = question.querySelector('button');
                btn.setAttribute('onclick', `MatchingHeadingsManager.removeQuestionMapping(${index})`);

                this.questionCount++;
            });

            // Update counter
            document.getElementById('question-count').textContent = `${this.questionCount} questions`;

            // Update mapping data
            this.updateMappingData();
        },

        getHeadingOptions() {
            const headings = document.querySelectorAll('.heading-input');
            let options = '';

            headings.forEach((heading, index) => {
                const letter = String.fromCharCode(65 + index);
                const text = heading.value || `Heading ${letter}`;
                options += `<option value="${letter}">${letter}. ${text}</option>`;
            });

            return options;
        },

        updateDropdowns() {
            const selects = document.querySelectorAll('.question-select');
            const newOptions = '<option value="">Select correct heading</option>' + this.getHeadingOptions();

            selects.forEach(select => {
                const currentValue = select.value;
                select.innerHTML = newOptions;
                select.value = currentValue; // Restore previous selection
            });

            // Update mapping data
            this.updateMappingData();
        },

        updateMappingData() {
            // Collect all headings
            this.headings = [];
            document.querySelectorAll('.heading-input').forEach((input, index) => {
                const letter = String.fromCharCode(65 + index);
                this.headings.push({
                    id: letter,
                    text: input.value || ''
                });
            });

            // Collect all mappings
            this.mappings = [];
            document.querySelectorAll('.question-select').forEach((select, index) => {
                const paragraphLetter = String.fromCharCode(65 + index);
                if (select.value) {
                    this.mappings.push({
                        question: index + 1,
                        paragraph: paragraphLetter,
                        correct: select.value
                    });
                }
            });

            // Update hidden input with JSON data
            const dataInput = document.getElementById('matching_headings_data');
            if (dataInput) {
                dataInput.value = JSON.stringify({
                    headings: this.headings,
                    mappings: this.mappings
                });
            }

            console.log('Updated matching headings data:', {
                headings: this.headings,
                mappings: this.mappings
            });
        }
    };

    // Make it globally available
    window.MatchingHeadingsManager = MatchingHeadingsManager;

    // Load existing data for edit mode
    function loadExistingData() {
        if (!window.isEditMode || !window.questionData) return;

        console.log('Loading existing data for edit mode:', window.questionData);

        const existingType = window.questionData.question_type;

        // Load existing options for single/multiple choice, true_false, yes_no
        if (['single_choice', 'multiple_choice', 'true_false', 'yes_no', 'matching_information', 'matching_features'].includes(existingType)) {
            if (window.questionData.options && window.questionData.options.length > 0) {
                loadExistingOptions(existingType, window.questionData.options);
            }
        }

        // Load existing blank answers for fill_blanks
        if (existingType === 'fill_blanks') {
            const blankAnswers = window.questionData.section_specific_data?.blank_answers || {};
            // Also check blanks relation
            if (window.questionData.blanks && window.questionData.blanks.length > 0) {
                window.questionData.blanks.forEach(blank => {
                    blankAnswersStore[blank.blank_number] = blank.correct_answer;
                });
            } else {
                Object.keys(blankAnswers).forEach(key => {
                    blankAnswersStore[key] = blankAnswers[key];
                });
            }
            waitForEditorThenUpdateBlanks();
        }

        // Load existing dropdown data
        if ((existingType === 'dropdown_selection' || existingType === 'matching_grid') && window.questionData.section_specific_data) {
            const dropdownOptions = window.questionData.section_specific_data.dropdown_options || {};
            const dropdownCorrect = window.questionData.section_specific_data.dropdown_correct || {};

            Object.keys(dropdownOptions).forEach(key => {
                dropdownStore.options[key] = dropdownOptions[key];
            });
            Object.keys(dropdownCorrect).forEach(key => {
                dropdownStore.correct[key] = dropdownCorrect[key];
            });

            waitForEditorThenUpdateBlanks();
        }

        // Load existing matching headings data
        if (existingType === 'matching_headings' && window.questionData.section_specific_data) {
            loadExistingMatchingHeadingsData(window.questionData.section_specific_data);
        }

        // Load existing sentence completion data
        if (existingType === 'sentence_completion' && window.questionData.section_specific_data?.sentence_completion) {
            loadExistingSentenceCompletionData(window.questionData.section_specific_data.sentence_completion);
        }
    }

    // Helper function to load existing options
    function loadExistingOptions(questionType, options) {
        if (!options || !Array.isArray(options)) return;

        const container = document.getElementById('options-container');
        if (!container) return;

        container.innerHTML = '';

        options.forEach((option, index) => {
            const isCorrect = option.is_correct || false;
            addOption(option.content || '', isCorrect);
        });

        console.log(`Loaded ${options.length} existing options for ${questionType}`);
    }

    // Helper function to load existing matching headings data
    function loadExistingMatchingHeadingsData(sectionData) {
        if (!sectionData || !sectionData.headings || !sectionData.mappings) return;

        console.log('Loading existing matching headings data:', sectionData);

        const card = document.getElementById('matching-headings-card');
        if (!card) return;

        card.style.display = 'block';

        setTimeout(() => {
            // Use MatchingHeadingsEnhanced (correct container IDs: mh-*)
            if (window.MatchingHeadingsEnhanced) {
                const headingsContainer = document.getElementById('mh-headings-container');
                const questionsContainer = document.getElementById('mh-questions-container');

                if (headingsContainer) headingsContainer.innerHTML = '';
                if (questionsContainer) questionsContainer.innerHTML = '';

                window.MatchingHeadingsEnhanced.headings = [];
                window.MatchingHeadingsEnhanced.questions = [];

                // Load headings with existing text
                sectionData.headings.forEach(heading => {
                    window.MatchingHeadingsEnhanced.addHeading(heading.text || '');
                });

                // Update heading count display
                const headingCountEl = document.getElementById('mh-heading-count');
                if (headingCountEl) headingCountEl.textContent = `${sectionData.headings.length} headings`;

                // Update question count input
                const questionCountInput = document.getElementById('mh_question_count');
                if (questionCountInput) questionCountInput.value = sectionData.mappings.length;

                // Generate questions based on mapping count
                window.MatchingHeadingsEnhanced.generateQuestions();

                // Set correct answers after questions are generated
                setTimeout(() => {
                    sectionData.mappings.forEach((mapping, index) => {
                        const select = document.getElementById(`mh-correct-${index}`);
                        if (select && mapping.correct) {
                            select.value = mapping.correct;
                        }
                        // Set paragraph title if exists
                        const titleInput = document.getElementById(`mh-para-title-${index}`);
                        if (titleInput && mapping.title) {
                            titleInput.value = mapping.title;
                        }
                        // Update internal state
                        if (window.MatchingHeadingsEnhanced.questions[index]) {
                            window.MatchingHeadingsEnhanced.questions[index].correct = mapping.correct || '';
                            window.MatchingHeadingsEnhanced.questions[index].title = mapping.title || '';
                        }
                    });

                    window.MatchingHeadingsEnhanced.updateAllDropdowns();
                    window.MatchingHeadingsEnhanced.updateData();
                    window.MatchingHeadingsEnhanced.updatePreview();
                    console.log('Loaded matching headings data via MatchingHeadingsEnhanced');
                }, 200);

            } else if (window.MatchingHeadingsManager) {
                // Fallback to old manager (uses matching-headings-container)
                const headingsContainer = document.getElementById('matching-headings-container');
                const mappingsContainer = document.getElementById('question-mappings-container');

                if (headingsContainer) headingsContainer.innerHTML = '';
                if (mappingsContainer) mappingsContainer.innerHTML = '';

                window.MatchingHeadingsManager.headingCount = 0;
                window.MatchingHeadingsManager.questionCount = 0;

                sectionData.headings.forEach(heading => {
                    window.MatchingHeadingsManager.addHeading(heading.text || '');
                });

                sectionData.mappings.forEach(mapping => {
                    window.MatchingHeadingsManager.addQuestionMapping();
                });

                setTimeout(() => {
                    sectionData.mappings.forEach((mapping, index) => {
                        const select = document.querySelector(`select[data-question-index="${index}"]`);
                        if (select && mapping.correct) {
                            select.value = mapping.correct;
                        }
                    });
                    window.MatchingHeadingsManager.updateMappingData();
                }, 200);
            }

            console.log('Loaded matching headings data successfully');
        }, 300);
    }

    // Helper function to load existing sentence completion data
    function loadExistingSentenceCompletionData(scData) {
        if (!scData || !scData.options || !scData.sentences) return;

        console.log('Loading existing sentence completion data:', scData);

        setTimeout(() => {
            if (!window.SentenceCompletionManager) return;

            const card = document.getElementById('sentence-completion-card');
            if (!card) return;

            card.style.display = 'block';

            // Clear existing (correct container IDs from partial)
            const optionsContainer = document.getElementById('answer-options-container');
            const sentencesContainer = document.getElementById('sentences-container');

            if (optionsContainer) optionsContainer.innerHTML = '';
            if (sentencesContainer) sentencesContainer.innerHTML = '';

            window.SentenceCompletionManager.optionCount = 0;
            window.SentenceCompletionManager.sentenceCount = 0;
            window.SentenceCompletionManager.options = [];
            window.SentenceCompletionManager.sentences = [];

            // Load options
            scData.options.forEach(option => {
                window.SentenceCompletionManager.addOption(option.text || '');
            });

            // Load sentences with correct answers
            scData.sentences.forEach(sentence => {
                window.SentenceCompletionManager.addSentence(sentence.text || '', sentence.correctAnswer || '');
            });

            // Update start number if available
            if (scData.startNumber) {
                const startNumInput = document.getElementById('sc_start_number');
                if (startNumInput) startNumInput.value = scData.startNumber;
            }

            window.SentenceCompletionManager.updateData();
            window.SentenceCompletionManager.updatePreview();

            console.log('Loaded sentence completion data successfully');
        }, 300);
    }
    </script>
    @endpush
</x-admin-layout>
