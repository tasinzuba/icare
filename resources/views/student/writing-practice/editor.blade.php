<x-test-layout>
    <x-slot:title>Writing Practice - {{ $question->question_type }}</x-slot>

    <x-slot:meta>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    </x-slot:meta>

    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            overflow: hidden;
        }

        .main-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .practice-header {
            background-color: white;
            border-bottom: 2px solid #e5e7eb;
            padding: 16px 24px;
            flex-shrink: 0;
        }

        .content-wrapper {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        .left-panel {
            width: 45%;
            background-color: #f8f9fa;
            border-right: 1px solid #e5e7eb;
            overflow-y: auto;
            padding: 24px;
        }

        .right-panel {
            flex: 1;
            background-color: white;
            display: flex;
            flex-direction: column;
        }

        .editor-header {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 24px;
            flex-shrink: 0;
        }

        .editor-area {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
        }

        .editor-textarea {
            width: 100%;
            height: 100%;
            min-height: 500px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            font-size: 16px;
            line-height: 1.8;
            font-family: 'Times New Roman', Times, serif;
            resize: none;
            outline: none;
        }

        .editor-textarea:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .bottom-actions {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: white;
            border-top: 1px solid #e5e7eb;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
        }

        .question-prompt {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .task-image {
            width: 100%;
            max-width: 600px;
            height: auto;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-top: 15px;
        }

        .word-count-number {
            font-weight: bold;
            color: #3b82f6;
        }

        /* Vocabulary Helper Styles */
        .vocab-word {
            cursor: pointer;
            padding: 1px;
            border-radius: 3px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            display: inline;
        }

        .vocab-word:hover {
            color: #C8102E;
            border-color: #C8102E;
            background-color: rgba(200, 16, 46, 0.05);
        }

        @media (max-width: 1024px) {
            .left-panel {
                width: 50%;
            }
        }

        @media (max-width: 768px) {
            .content-wrapper {
                flex-direction: column;
            }

            .left-panel {
                width: 100%;
                height: 40%;
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }

            .right-panel {
                height: 60%;
            }
        }
    </style>

    <div class="main-container">
        <!-- Header -->
        <div class="practice-header">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Writing Practice</h1>
                    <p class="text-sm text-gray-600">
                        @if($question->part_number == 1)
                            Task 1 • 150 words minimum • 20 minutes suggested
                        @else
                            Task 2 • 250 words minimum • 40 minutes suggested
                        @endif
                    </p>
                </div>
                <a href="{{ $question->part_number == 1 ? route('student.writing-practice.task1') : route('student.writing-practice.task2') }}"
                   class="px-6 py-2 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                    Exit
                </a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-wrapper">
            <!-- Left Panel - Question -->
            <div class="left-panel">
                <!-- Instructions at Top -->
                @if($question->instructions)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="text-sm text-gray-700 leading-relaxed">
                        {!! nl2br(e($question->instructions)) !!}
                    </div>
                </div>
                @endif

                <!-- Question Content -->
                <div class="question-prompt">
                    <div class="prose max-w-none vocab-content text-gray-900">
                        {!! nl2br(e($question->content)) !!}
                    </div>

                    @if($question->media_path)
                        <img src="{{ $question->media_url }}"
                             alt="Question Visual"
                             class="task-image">
                    @endif
                </div>
            </div>

            <!-- Right Panel - Editor -->
            <div class="right-panel">
                <form id="practice-form" action="{{ route('student.writing-practice.submit', $attempt) }}" method="POST" style="height: 100%; display: flex; flex-direction: column;">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $question->id }}">

                    <div class="editor-header">
                        <div class="flex items-center justify-between">
                            <div class="word-count">
                                Word count: <span class="word-count-number" id="current-word-count">0</span>
                            </div>
                            <div class="autosave-status text-sm text-green-600" id="autosave-status">
                                <span id="save-text"></span>
                            </div>
                        </div>
                    </div>

                    <div class="editor-area">
                        @php
                            $existingAnswer = $attempt->answers->where('question_id', $question->id)->first();
                        @endphp
                        <textarea
                            id="editor"
                            name="answer"
                            class="editor-textarea"
                            placeholder="Start writing your answer here..."
                            spellcheck="false"
                            autocomplete="off"
                        >{{ $existingAnswer->answer ?? '' }}</textarea>
                    </div>

                    <button type="submit" id="submit-button" class="hidden">Submit</button>
                </form>
            </div>
        </div>

        <!-- Bottom Actions -->
        <div class="bottom-actions">
            <div class="flex items-center gap-4">
                <!-- Empty left space -->
            </div>
            <div class="flex items-center gap-3">
                <button type="button" id="submit-practice-btn"
                        class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                    Submit Practice
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/vocab-helper.js') }}"></script>
    <script>
    let autosaveTimer = null;
    let wordCount = 0;
    const wordLimit = {{ $question->part_number == 1 ? 150 : 250 }};

    document.addEventListener('DOMContentLoaded', function() {
        const editor = document.getElementById('editor');
        const submitPracticeBtn = document.getElementById('submit-practice-btn');
        const submitButton = document.getElementById('submit-button');
        const form = document.getElementById('practice-form');

        // Initialize word count
        updateWordCount(editor.value);

        // Word count on input
        editor.addEventListener('input', function() {
            updateWordCount(this.value);
            setupAutosave(this.value);
        });

        // Submit handler
        submitPracticeBtn.addEventListener('click', function() {
            if (confirm('Are you ready to submit your practice? You won\'t be able to edit after submission.')) {
                submitButton.click();
            }
        });

        // Handle form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(error => {
                alert('Error submitting practice. Please try again.');
                console.error(error);
            });
        });
    });

    function updateWordCount(text) {
        const words = text.trim().split(/\s+/).filter(word => word.length > 0);
        wordCount = text.trim() === '' ? 0 : words.length;

        document.getElementById('current-word-count').textContent = wordCount;

        // Update color based on requirement
        const countElement = document.getElementById('current-word-count');
        if (wordCount >= wordLimit) {
            countElement.style.color = '#10b981';
        } else if (wordCount >= wordLimit * 0.8) {
            countElement.style.color = '#f59e0b';
        } else {
            countElement.style.color = '#3b82f6';
        }
    }

    function setupAutosave(content) {
        clearTimeout(autosaveTimer);

        document.getElementById('save-text').textContent = 'Saving...';

        autosaveTimer = setTimeout(() => {
            autosave(content);
        }, 2000);
    }

    function autosave(content) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('{{ route("student.writing-practice.autosave", [$attempt->id, $question->id]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                content: content
            })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('save-text').textContent = 'Saved';

            setTimeout(() => {
                document.getElementById('save-text').textContent = '';
            }, 2000);
        })
        .catch(error => {
            document.getElementById('save-text').textContent = 'Error saving';
        });
    }

    // Initialize Vocabulary Helper
    let vocabHelper;
    document.addEventListener('DOMContentLoaded', function() {
        vocabHelper = new VocabHelper({
            enabled: true,
            containerSelector: '.vocab-content'
        });
        vocabHelper.init();
    });
    </script>
    @endpush
</x-test-layout>
