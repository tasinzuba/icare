{{-- resources/views/student/test/writing/test.blade.php --}}
<x-test-layout>
    <x-slot:title>IELTS Writing Test</x-slot>
    
    <x-slot:meta>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
    </x-slot:meta>

    <!-- Universal Loading Screen Component -->
    <x-test-loading-screen />

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
        
        .ielts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            background-color: white;
            border-bottom: 1px solid #e5e7eb;
            flex-shrink: 0;
        }
        
        .ielts-header-left {
            display: flex;
            align-items: center;
        }
        
        .user-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #1a1a1a;
            color: white;
            height: 50px;
            flex-shrink: 0;
            position: relative;
        }
        
        /* Timer Center Wrapper */
        .timer-center-wrapper {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .content-wrapper {
            flex: 1;
            display: flex;
            overflow: hidden;
            margin-bottom: 60px; /* Space for bottom nav */
        }
        
        .left-panel {
            width: 45%;
            background-color: #f8f9fa;
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .question-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        /* Global Part Header - Full Width */
        .global-part-header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 5%;
            z-index: 200;
            flex-shrink: 0;
        }
        
        .global-part-header .part-header-inner {
            background: white;
            padding: 16px 24px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .global-part-header .part-header-inner:hover {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }
        
        .part-header-content {
            flex: 1;
        }
        
        .part-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 4px;
        }
        
        .part-instruction {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.5;
        }
        
        .part-timer {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 14px;
        }
        
        .question-prompt {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .question-prompt h4 {
            margin-top: 0;
            color: #1f2937;
        }
        
        .prompt-text {
            line-height: 1.6;
            color: #374151;
        }

        /* TinyMCE Content Styles */
        .prompt-text strong,
        .prompt-text b,
        .question-content strong,
        .question-content b {
            font-weight: 700 !important;
            color: #000000 !important;
        }

        .prompt-text em,
        .prompt-text i,
        .question-content em,
        .question-content i {
            font-style: italic !important;
        }

        .prompt-text u,
        .question-content u {
            text-decoration: underline !important;
        }

        .prompt-text table,
        .question-content table {
            width: auto !important;
            max-width: 100% !important;
            border-collapse: collapse !important;
            margin: 10px 0 !important;
            font-size: 14px !important;
        }

        .prompt-text table th,
        .question-content table th {
            background-color: #f3f4f6 !important;
            padding: 8px 12px !important;
            font-weight: 700 !important;
            border: 1px solid #000000 !important;
            color: #000000 !important;
        }

        .prompt-text table td,
        .question-content table td {
            padding: 6px 12px !important;
            border: 1px solid #000000 !important;
            color: #1f2937 !important;
        }

        .prompt-text ul,
        .prompt-text ol,
        .question-content ul,
        .question-content ol {
            margin: 10px 0 10px 20px !important;
            padding-left: 20px !important;
        }

        .prompt-text li,
        .question-content li {
            margin-bottom: 5px !important;
            line-height: 1.6 !important;
        }

        .task-image {
            width: 100%;
            max-width: 500px;
            height: auto;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-top: 15px;
        }
        
        .right-panel {
            flex: 1;
            background-color: white;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .editor-header {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        
        .word-count-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .word-count {
            font-weight: 500;
            color: #374151;
            font-size: 16px;
        }
        
        .word-count-number {
            font-weight: bold;
            color: #3b82f6;
        }
        
        .word-requirement {
            font-size: 14px;
            color: #6b7280;
        }
        
        .autosave-status {
            font-size: 14px;
            color: #10b981;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .editor-area {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .editor-textarea {
            width: 100%;
            height: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            font-size: 16px;
            line-height: 1.8;
            font-family: 'Times New Roman', Times, serif;
            resize: none;
            outline: none;
            transition: border-color 0.3s;
        }
        
        .editor-textarea:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: white;
            border-top: 1px solid #e5e7eb;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
            height: 60px;
        }
        
        .nav-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .task-nav {
            display: flex;
            gap: 10px;
        }
        
        .task-btn {
            padding: 8px 16px;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            color: #374151;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .task-btn.active {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }
        
        .task-btn:hover:not(.active) {
            background-color: #f3f4f6;
        }
        
        .nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .btn-secondary {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: white;
            color: #374151;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-secondary:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #eff6ff;
        }
        
        .submit-btn {
            background-color: #10b981;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .submit-btn:hover {
            background-color: #059669;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.75);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 32px;
            border-radius: 12px;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .modal-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 16px;
            color: #1f2937;
        }
        
        .modal-message {
            font-size: 16px;
            margin-bottom: 24px;
            line-height: 1.6;
            color: #4b5563;
        }
        
        .word-summary {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        
        .word-summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .word-summary-item:last-child {
            border-bottom: none;
        }
        
        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 24px;
        }
        
        .modal-button {
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .modal-button.primary {
            background-color: #10b981;
            color: white;
        }
        
        .modal-button.primary:hover {
            background-color: #059669;
        }
        
        .modal-button.secondary {
            background-color: #e5e7eb;
            color: #4b5563;
        }
        
        .modal-button.secondary:hover {
            background-color: #d1d5db;
        }
        
        /* Smooth scrollbar for question panel */
        .question-content::-webkit-scrollbar {
            width: 8px;
        }
        
        .question-content::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .question-content::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .question-content::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
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
       

        <!-- User Info Bar WITH Integrated Timer -->
        <div class="user-bar" style="height: 50px;">
            <div class="user-info">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
                <span>{{ auth()->user()->name }} - BI {{ str_pad(auth()->id(), 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            
            {{-- Integrated Timer Component - Center Position --}}
            <div class="timer-center-wrapper">
                <x-test-timer 
                    :attempt="$attempt" 
                    auto-submit-form-id="writing-form"
                    position="integrated"
                    :warning-time="600"
                    :danger-time="300"
                />
            </div>
            
            <div class="user-controls">
                <button class="bg-gray-200 text-gray-800 px-3 py-1 rounded text-sm help-button" id="help-button">Help ?</button>
                <button class="bg-gray-200 text-gray-800 px-3 py-1 rounded text-sm no-nav">Hide</button>
            </div>
        </div>

        @php
            // Get all questions and sort by order_number
            $questions = $testSet->questions()->orderBy('order_number')->get();
            
            // If we have less than 2 questions, show error
            if ($questions->count() < 2) {
                echo '<div style="padding: 20px; color: red;">This writing test needs at least 2 questions. Currently has: ' . $questions->count() . '</div>';
                return;
            }
            
            // Take first 2 questions as Task 1 and Task 2
            $taskOneQuestion = $questions->first();
            $taskTwoQuestion = $questions->skip(1)->first();
            
            // Get existing answers
            $taskOneAnswer = $attempt->answers->where('question_id', $taskOneQuestion->id)->first();
            $taskTwoAnswer = $attempt->answers->where('question_id', $taskTwoQuestion->id)->first();
        @endphp

        <!-- Global Part Header -->
        <div class="global-part-header" id="global-part-header">
            <!-- Part header will be updated by JavaScript -->
        </div>

        <!-- Main Content Area -->
        <div class="content-wrapper">
            <!-- Left Panel - Questions -->
            <div class="left-panel">
                <!-- Task 1 Content -->
                <div class="question-content" id="task-1-content" style="display: block;">
                    
                    <div class="question-prompt">
                        <h4>Task</h4>
                        <div class="prompt-text">
                            {!! nl2br(e($taskOneQuestion->content)) !!}
                        </div>
                        
                        @if($taskOneQuestion->media_path)
                            <img src="{{ $taskOneQuestion->media_url }}"
                                 alt="Task 1 Visual"
                                 class="task-image">
                        @endif
                    </div>
                    
                    @if($taskOneQuestion->instructions)
                    <div class="question-prompt">
                        <h4>Instructions</h4>
                        <div class="prompt-text">
                            {!! nl2br(e($taskOneQuestion->instructions)) !!}
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Task 2 Content -->
                <div class="question-content" id="task-2-content" style="display: none;">
                    
                    <div class="question-prompt">
                        <h4>Essay Task</h4>
                        <div class="prompt-text">
                            {!! nl2br(e($taskTwoQuestion->content)) !!}
                        </div>
                    </div>
                    
                    @if($taskTwoQuestion->instructions)
                    <div class="question-prompt">
                        <h4>Instructions</h4>
                        <div class="prompt-text">
                            {!! nl2br(e($taskTwoQuestion->instructions)) !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Right Panel - Writing Area -->
            <div class="right-panel">
                <form id="writing-form" action="{{ route('student.writing.submit', $attempt) }}" method="POST" style="height: 100%; display: flex; flex-direction: column;">
                    @csrf
                    
                    <div class="editor-header">
                        <div class="word-count-info">
                            <div class="word-count">
                                Word count: <span class="word-count-number" id="current-word-count">0</span>
                            </div>
                            
                        </div>
                        <div class="autosave-status" id="autosave-status">
                            <span id="save-text"></span>
                        </div>
                    </div>
                    
                    <div class="editor-area">
                        <!-- Task 1 Editor -->
                        <textarea 
                            id="editor-task-1" 
                            name="answers[{{ $taskOneQuestion->id }}]" 
                            class="editor-textarea"
                            placeholder="Start writing your Task 1 response here..."
                            spellcheck="false"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="off"
                        >{{ old('answers.' . $taskOneQuestion->id, $taskOneAnswer->answer ?? '') }}</textarea>
                        
                        <!-- Task 2 Editor -->
                        <textarea 
                            id="editor-task-2" 
                            name="answers[{{ $taskTwoQuestion->id }}]" 
                            class="editor-textarea"
                            style="display: none;"
                            placeholder="Start writing your Task 2 essay here..."
                            spellcheck="false"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="off"
                        >{{ old('answers.' . $taskTwoQuestion->id, $taskTwoAnswer->answer ?? '') }}</textarea>
                    </div>
                    
                    <button type="submit" id="submit-button" class="hidden">Submit</button>
                </form>
            </div>
        </div>

        <!-- Bottom Navigation -->
        <div class="bottom-nav" style="height: 60px;">
            <div class="nav-left">
                <div class="task-nav">
                    <button type="button" class="task-btn active" onclick="switchTask(1)">Task 1</button>
                    <button type="button" class="task-btn" onclick="switchTask(2)">Task 2</button>
                </div>
            </div>
            <div class="nav-right">
                <button type="button" class="btn-secondary" id="fullscreen-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    Fullscreen
                </button>
                <button type="button" id="submit-test-btn" class="submit-btn">
                    Submit Test
                </button>
            </div>
        </div>
    </div>

    <!-- Submit Modal -->
    <div id="submit-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-title">Ready to Submit?</div>
            <div class="modal-message">
                Please review your word count before submitting:
            </div>
            <div class="word-summary">
                <div class="word-summary-item">
                    <span><strong>Task 1:</strong></span>
                    <span id="modal-task1-words">0 words</span>
                </div>
                <div class="word-summary-item">
                    <span><strong>Task 2:</strong></span>
                    <span id="modal-task2-words">0 words</span>
                </div>
            </div>
            <div class="modal-message">
                Once submitted, you cannot change your answers.
            </div>
            <div class="modal-buttons">
                <button class="modal-button primary" id="confirm-submit-btn">Submit Test</button>
                <button class="modal-button secondary" id="cancel-submit-btn">Continue Writing</button>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
    // ====================================
    // Prevent Back Navigation During Test
    // ====================================
    (function() {
        // Push a dummy state to prevent going back
        history.pushState(null, null, location.href);

        window.addEventListener('popstate', function(event) {
            // Push state again to keep user on the test page
            history.pushState(null, null, location.href);

            // Show submit modal instead of allowing back navigation
            const submitModal = document.getElementById('submit-modal');
            if (submitModal) {
                submitModal.style.display = 'flex';
            }
        });

        // Also prevent on initial load
        window.addEventListener('load', function() {
            history.pushState(null, null, location.href);
        });
    })();
    </script>

    <script>
    let currentTask = 1;
    const wordCounts = {1: 0, 2: 0};
    let autosaveTimers = {1: null, 2: null};
    const wordLimits = {
        1: {{ $taskOneQuestion->word_limit ?? 150 }},
        2: {{ $taskTwoQuestion->word_limit ?? 250 }}
    };
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize part header
        updatePartHeader(1);
        const editor1 = document.getElementById('editor-task-1');
        const editor2 = document.getElementById('editor-task-2');
        const submitTestBtn = document.getElementById('submit-test-btn');
        const submitModal = document.getElementById('submit-modal');
        const confirmSubmitBtn = document.getElementById('confirm-submit-btn');
        const cancelSubmitBtn = document.getElementById('cancel-submit-btn');
        const submitButton = document.getElementById('submit-button');
        const fullscreenBtn = document.getElementById('fullscreen-btn');
        
        // Initialize word counts
        updateWordCount(1, editor1.value);
        updateWordCount(2, editor2.value);
        
        // Setup word count listeners
        editor1.addEventListener('input', function() {
            updateWordCount(1, this.value);
            setupAutosave(1, this.value);
        });
        
        editor2.addEventListener('input', function() {
            updateWordCount(2, this.value);
            setupAutosave(2, this.value);
        });
        
        // Fullscreen functionality
        fullscreenBtn.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                // Enter fullscreen
                document.documentElement.requestFullscreen().catch(err => {
                    console.log(`Error attempting to enable fullscreen: ${err.message}`);
                });
                // Update button text and icon
                this.innerHTML = `
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V5m0 0h4m-4 0l5 5m-5 10v-4m0 4h4m-4 0l5-5m5-5v4m0-4h-4m4 0l-5 5m-5 5h4m0 0v4m0-4l-5-5"/>
                    </svg>
                    Exit Fullscreen
                `;
            } else {
                // Exit fullscreen
                document.exitFullscreen();
                // Update button text and icon
                this.innerHTML = `
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    Fullscreen
                `;
            }
        });
        
        // Update button when fullscreen changes (e.g., user presses ESC)
        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                fullscreenBtn.innerHTML = `
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    Fullscreen
                `;
            }
        });
        
        // Submit handlers
        submitTestBtn.addEventListener('click', function() {
            document.getElementById('modal-task1-words').textContent = wordCounts[1] + ' words';
            document.getElementById('modal-task2-words').textContent = wordCounts[2] + ' words';
            submitModal.style.display = 'flex';
        });
        
        confirmSubmitBtn.addEventListener('click', function() {
            if (window.UniversalTimer) {
                window.UniversalTimer.stop();
            }
            submitButton.click();
        });
        
        cancelSubmitBtn.addEventListener('click', function() {
            submitModal.style.display = 'none';
        });
    });
    
    function switchTask(taskNumber) {
        currentTask = taskNumber;
        
        // Update bottom nav buttons
        document.querySelectorAll('.task-btn').forEach((btn, index) => {
            if (index === taskNumber - 1) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Update global part header
        updatePartHeader(taskNumber);
        
        // Update question content
        document.getElementById('task-1-content').style.display = taskNumber === 1 ? 'block' : 'none';
        document.getElementById('task-2-content').style.display = taskNumber === 2 ? 'block' : 'none';
        
        // Update editor
        document.getElementById('editor-task-1').style.display = taskNumber === 1 ? 'block' : 'none';
        document.getElementById('editor-task-2').style.display = taskNumber === 2 ? 'block' : 'none';
        
        // Update word count display
        document.getElementById('current-word-count').textContent = wordCounts[taskNumber];
        document.getElementById('word-requirement').textContent = 
            'Minimum: ' + wordLimits[taskNumber] + ' words';
    }
    
    function updatePartHeader(taskNumber) {
        const partHeader = document.getElementById('global-part-header');
        const taskData = {
            1: {
                title: 'Writing Task 1',
                time: {{ $taskOneQuestion->time_limit ?? 20 }},
                words: {{ $taskOneQuestion->word_limit ?? 150 }}
            },
            2: {
                title: 'Writing Task 2', 
                time: {{ $taskTwoQuestion->time_limit ?? 40 }},
                words: {{ $taskTwoQuestion->word_limit ?? 250 }}
            }
        };
        
        const task = taskData[taskNumber];
        partHeader.innerHTML = `
            <div class="part-header-inner">
                <div class="part-header-content">
                    <div class="part-title">${task.title}</div>
                    <div class="part-instruction">Suggested time: ${task.time} minutes | Minimum ${task.words} words</div>
                </div>
            </div>
        `;
    }
    
    function updateWordCount(taskNumber, text) {
        const words = text.trim().split(/\s+/).filter(word => word.length > 0);
        const count = text.trim() === '' ? 0 : words.length;
        wordCounts[taskNumber] = count;
        
        if (taskNumber === currentTask) {
            document.getElementById('current-word-count').textContent = count;
            
            // Update color based on requirement
            const requirement = wordLimits[taskNumber];
            const countElement = document.getElementById('current-word-count');
            if (count >= requirement) {
                countElement.style.color = '#10b981';
            } else if (count >= requirement * 0.8) {
                countElement.style.color = '#f59e0b';
            } else {
                countElement.style.color = '#3b82f6';
            }
        }
    }
    
    function setupAutosave(taskNumber, content) {
        clearTimeout(autosaveTimers[taskNumber]);
        
        const statusElement = document.getElementById('autosave-status');
        document.getElementById('save-text').textContent = 'Saving...';
        
        autosaveTimers[taskNumber] = setTimeout(() => {
            autosave(taskNumber, content);
        }, 2000);
    }
    
    function autosave(taskNumber, content) {
        const questionId = taskNumber === 1 ? {{ $taskOneQuestion->id }} : {{ $taskTwoQuestion->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch(`{{ route('student.writing.autosave', [$attempt->id, '__QUESTION_ID__']) }}`.replace('__QUESTION_ID__', questionId), {
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
    </script>
    
    {{-- Disable Ctrl+F Find During Writing Test --}}
    <script>
    // AGGRESSIVE Ctrl+F Find Disabler for Writing Test
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey === true || e.metaKey === true) && (e.key === 'f' || e.key === 'F')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
    }, true);
    
    document.addEventListener('keyup', function(e) {
        if ((e.ctrlKey === true || e.metaKey === true) && (e.key === 'f' || e.key === 'F')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
    }, true);
    
    window.addEventListener('keydown', function(e) {
        if ((e.metaKey || e.ctrlKey) && (e.key === 'f' || e.keyCode === 70)) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
    }, true);
    </script>
    @endpush
</x-test-layout>