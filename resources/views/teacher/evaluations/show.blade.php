<x-teacher-layout>
    <x-slot:title>Evaluate - {{ ucfirst($evaluationRequest->studentAttempt->testSet->section->name) }}</x-slot>
    
    <x-slot:header>
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-white">
                Evaluate {{ ucfirst($evaluationRequest->studentAttempt->testSet->section->name) }} Test
            </h1>
            <div class="flex items-center gap-4">
                <div class="text-sm">
                    <span class="text-gray-400">Status:</span>
                    <span class="text-white font-medium ml-1">{{ ucfirst($evaluationRequest->status) }}</span>
                </div>
                <div class="text-sm">
                    <span class="text-gray-400">Deadline:</span>
                    <span class="text-white font-medium ml-1">{{ $evaluationRequest->deadline_at->format('M d, h:i A') }}</span>
                </div>
            </div>
        </div>
    </x-slot>
    
    <!-- Error Type Selection Modal -->
    <div id="errorTypeModal" class="fixed inset-0 z-[9999] overflow-y-auto" style="display: none; z-index: 9999 !important;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-black bg-opacity-20 backdrop-blur-sm transition-opacity" onclick="closeErrorModal()"></div>
            
            <!-- Modal content -->
            <div class="relative bg-white rounded-xl shadow-2xl transform transition-all sm:max-w-md sm:w-full border border-gray-100">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Mark Error Type
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        <span id="selectedTextDisplay" class="font-medium text-gray-900 bg-amber-100 px-2 py-1 rounded"></span>
                    </p>
                    <div class="space-y-2">
                        <button type="button" onclick="markError('task_achievement')" class="w-full text-left px-4 py-3 bg-blue-50 hover:bg-blue-100 rounded-lg border-2 border-transparent hover:border-blue-300 transition-all duration-200 group">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                <div class="flex-1">
                                    <span class="font-medium text-gray-900">Task Response</span>
                                    <p class="text-xs text-gray-600 mt-0.5">Content & addressing the prompt</p>
                                </div>
                            </div>
                        </button>
                        <button type="button" onclick="markError('coherence_cohesion')" class="w-full text-left px-4 py-3 bg-purple-50 hover:bg-purple-100 rounded-lg border-2 border-transparent hover:border-purple-300 transition-all duration-200 group">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-purple-500 rounded-full mr-3"></div>
                                <div class="flex-1">
                                    <span class="font-medium text-gray-900">Coherence & Cohesion</span>
                                    <p class="text-xs text-gray-600 mt-0.5">Organization & flow of ideas</p>
                                </div>
                            </div>
                        </button>
                        <button type="button" onclick="markError('lexical_resource')" class="w-full text-left px-4 py-3 bg-amber-50 hover:bg-amber-100 rounded-lg border-2 border-transparent hover:border-amber-300 transition-all duration-200 group">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-amber-500 rounded-full mr-3"></div>
                                <div class="flex-1">
                                    <span class="font-medium text-gray-900">Lexical Resource</span>
                                    <p class="text-xs text-gray-600 mt-0.5">Vocabulary & word choice</p>
                                </div>
                            </div>
                        </button>
                        <button type="button" onclick="markError('grammar')" class="w-full text-left px-4 py-3 bg-red-50 hover:bg-red-100 rounded-lg border-2 border-transparent hover:border-red-300 transition-all duration-200 group">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-red-500 rounded-full mr-3"></div>
                                <div class="flex-1">
                                    <span class="font-medium text-gray-900">Grammatical Range & Accuracy</span>
                                    <p class="text-xs text-gray-600 mt-0.5">Grammar & sentence structure</p>
                                </div>
                            </div>
                        </button>
                    </div>
                    <button type="button" onclick="closeErrorModal()" class="w-full mt-4 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-all duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Note Edit Modal -->
    <div class="error-note-modal-overlay" id="noteModalOverlay" onclick="closeNoteModal()"></div>
    <div class="error-note-modal" id="noteModal">
        <div style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">
            <span id="noteModalErrorType"></span>
        </div>
        <div style="font-size: 12px; color: #6b7280; margin-bottom: 8px;">
            "<span id="noteModalText"></span>"
        </div>
        <textarea 
            id="noteModalInput"
            class="error-note-input" 
            placeholder="Add note for this error..."
            rows="3"
            style="margin-top: 0;"
        ></textarea>
        <div class="error-note-buttons">
            <button class="error-note-btn error-note-save" onclick="saveNoteFromModal()">
                <i class="fas fa-check" style="font-size: 10px;"></i> Save
            </button>
            <button class="error-note-btn" onclick="closeNoteModal()" style="background: #e5e7eb; color: #374151;">
                Cancel
            </button>
        </div>
    </div>
    
    <div class="container mx-auto px-6 lg:px-8 py-6">
        @if($evaluationRequest->status === 'completed' && $evaluationRequest->humanEvaluation)
            <!-- Completed Evaluation View -->
            <div class="bg-green-50 rounded-xl p-6 mb-6 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-green-900">Evaluation Completed</h3>
                        <p class="text-green-700 text-sm mt-1">
                            Completed on {{ $evaluationRequest->completed_at->format('M d, Y h:i A') }}
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-green-700">Overall Band</p>
                        <p class="text-3xl font-bold text-green-900">{{ $evaluationRequest->humanEvaluation->overall_band_score }}</p>
                    </div>
                </div>
            </div>
        @else
            <!-- Active Evaluation Form -->
            <form action="{{ route('teacher.evaluations.submit', $evaluationRequest) }}" method="POST" id="evaluationForm">
                @csrf
                
                <!-- Hidden field for error markings -->
                <input type="hidden" name="error_markings" id="errorMarkingsInput" value="[]">
                
                <!-- Progress Bar -->
                <div class="bg-white rounded-xl shadow-sm mb-6 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Evaluation Progress</span>
                        <span class="text-sm font-medium text-gray-900" id="progressText">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%" id="progressBar"></div>
                    </div>
                </div>
                
                <!-- Student Info Card -->
                <div class="bg-white rounded-xl shadow-sm mb-6 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Student</p>
                            <p class="font-medium text-gray-900">{{ $evaluationRequest->student->name }}</p>
                            <p class="text-sm text-gray-600">{{ $evaluationRequest->student->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Test</p>
                            <p class="font-medium text-gray-900">{{ $evaluationRequest->studentAttempt->testSet->title }}</p>
                            <p class="text-sm text-gray-600">{{ ucfirst($evaluationRequest->studentAttempt->testSet->section->name) }} Section</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Submitted</p>
                            <p class="font-medium text-gray-900">{{ $evaluationRequest->studentAttempt->created_at->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-600">{{ $evaluationRequest->studentAttempt->created_at->format('h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Priority</p>
                            <p class="font-medium text-gray-900">{{ ucfirst($evaluationRequest->priority) }}</p>
                            <p class="text-sm text-gray-600">{{ $evaluationRequest->tokens_used }} tokens</p>
                        </div>
                    </div>
                </div>
                
                @php
                    $sectionName = $evaluationRequest->studentAttempt->testSet->section->name;
                @endphp
                
                @if($sectionName === 'speaking')
                    <!-- Speaking Tasks - Part Wise Evaluation -->
                    @php
                        $answersByPart = $evaluationRequest->studentAttempt->answers->groupBy(function($answer) {
                            return $answer->question->part_number;
                        });
                    @endphp
                    
                    @foreach($answersByPart as $partNumber => $partAnswers)
                        <div class="bg-white rounded-xl shadow-sm mb-6 overflow-hidden">
                            <!-- Part Header -->
                            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 border-b">
                                <h3 class="font-bold text-white flex items-center text-lg">
                                    <span class="bg-white text-blue-600 w-10 h-10 rounded-lg flex items-center justify-center text-base font-bold mr-3">
                                        {{ $partNumber }}
                                    </span>
                                    Part {{ $partNumber }}
                                    @if($partNumber == 1)
                                        <span class="ml-3 text-sm font-normal text-blue-100">Introduction & Interview</span>
                                    @elseif($partNumber == 2)
                                        <span class="ml-3 text-sm font-normal text-blue-100">Individual Long Turn</span>
                                    @else
                                        <span class="ml-3 text-sm font-normal text-blue-100">Two-way Discussion</span>
                                    @endif
                                </h3>
                            </div>
                            
                            <div class="p-6">
                                <!-- Questions and Audio Recordings -->
                                <div class="space-y-4 mb-6">
                                    @foreach($partAnswers as $answer)
                                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex-1">
                                                    <p class="text-xs font-medium text-gray-500 mb-1">Question {{ $answer->question->order_number }}</p>
                                                    <div class="text-sm text-gray-700">
                                                        {!! $answer->question->content !!}
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Audio Recording -->
                                            @if($answer->speakingRecording)
                                                @php
                                                    $recording = $answer->speakingRecording;
                                                    $audioUrl = $recording->file_url ?? $recording->getFileUrlAttribute();
                                                    $mimeType = $recording->mime_type ?? 'audio/webm';
                                                @endphp
                                                
                                                <div class="audio-player-container-compact">
                                                    <audio controls preload="metadata" class="w-full">
                                                        <source src="{{ $audioUrl }}" type="{{ $mimeType }}">
                                                        Your browser does not support the audio element.
                                                    </audio>
                                                </div>
                                            @else
                                                <div class="no-recording-compact">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    No recording
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                
                                <!-- Part-wise Scoring Grid -->
                                <div class="bg-blue-50 rounded-lg p-5 border border-blue-200">
                                    <p class="text-sm font-semibold text-blue-900 mb-4 flex items-center">
                                        <i class="fas fa-star mr-2"></i>
                                        Part {{ $partNumber }} Band Score
                                    </p>
                                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-2">Fluency & Coherence</label>
                                            <select name="part_scores[{{ $partNumber }}][fluency_coherence]" 
                                                    class="band-score-select"
                                                    onchange="calculatePartBand({{ $partNumber }})"
                                                    required>
                                                <option value="">-</option>
                                                @for($i = 0; $i <= 9; $i += 0.5)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-2">Lexical Resource</label>
                                            <select name="part_scores[{{ $partNumber }}][lexical_resource]" 
                                                    class="band-score-select"
                                                    onchange="calculatePartBand({{ $partNumber }})"
                                                    required>
                                                <option value="">-</option>
                                                @for($i = 0; $i <= 9; $i += 0.5)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-2">Grammar Range & Accuracy</label>
                                            <select name="part_scores[{{ $partNumber }}][grammar]" 
                                                    class="band-score-select"
                                                    onchange="calculatePartBand({{ $partNumber }})"
                                                    required>
                                                <option value="">-</option>
                                                @for($i = 0; $i <= 9; $i += 0.5)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-2">Pronunciation</label>
                                            <select name="part_scores[{{ $partNumber }}][pronunciation]" 
                                                    class="band-score-select"
                                                    onchange="calculatePartBand({{ $partNumber }})"
                                                    required>
                                                <option value="">-</option>
                                                @for($i = 0; $i <= 9; $i += 0.5)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-2">Overall Part Band</label>
                                            <input type="text"
                                                   name="part_scores[{{ $partNumber }}][score]"
                                                   id="part_band_{{ $partNumber }}"
                                                   class="overall-band-input"
                                                   value=""
                                                   readonly
                                                   required>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Part Feedback -->
                                <div class="mt-5">
                                    <label class="block text-sm font-semibold text-gray-800 mb-2">
                                        <i class="fas fa-comment-dots mr-1 text-blue-600"></i>
                                        Part {{ $partNumber }} Detailed Feedback
                                    </label>
                                    <textarea name="part_scores[{{ $partNumber }}][feedback]" 
                                              rows="4"
                                              class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Provide comprehensive feedback for Part {{ $partNumber }}... Include observations on fluency, vocabulary usage, grammar accuracy, and pronunciation."
                                              required></textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @elseif($sectionName === 'writing')
                    <!-- Writing Tasks -->
                    @foreach($evaluationRequest->studentAttempt->answers as $index => $answer)
                        <div class="bg-white rounded-xl shadow-sm mb-6 overflow-hidden">
                            <!-- Task Header -->
                            <div class="bg-gray-50 px-6 py-4 border-b">
                                <h3 class="font-semibold text-gray-900 flex items-center">
                                    <span class="bg-blue-600 text-white w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold mr-3">
                                        {{ $index + 1 }}
                                    </span>
                                    Task {{ $index + 1 }}: {{ $answer->question->title }}
                                </h3>
                            </div>
                            
                            <div class="p-6">
                                <!-- Question -->
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Question</p>
                                    <div class="bg-gray-50 rounded-lg p-4 text-gray-700 text-sm">
                                        {!! $answer->question->content !!}
                                    </div>

                                    @if($answer->question->media_url)
                                        <div class="mt-3">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                                <i class="fas fa-image text-gray-400 mr-1"></i> Reference Image / Chart
                                            </p>
                                            <a href="{{ $answer->question->media_url }}" target="_blank" rel="noopener">
                                                <img src="{{ $answer->question->media_url }}"
                                                     alt="Task {{ $index + 1 }} reference"
                                                     class="rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition" style="max-width: 280px; width: 100%; height: auto;"
                                                     loading="lazy">
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Student Response -->
                                <div class="mb-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-sm font-medium text-gray-700">Student's Response</p>
                                        <div class="flex items-center gap-4 text-xs">
                                            <span class="text-gray-500">
                                                <i class="fas fa-file-word mr-1"></i>
                                                {{ str_word_count($answer->answer) }} words
                                            </span>
                                            <span class="text-blue-600">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Select text to mark errors
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Error Marking Legend -->
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <span class="inline-flex items-center text-xs">
                                            <span class="w-3 h-3 bg-blue-200 rounded mr-1"></span>
                                            Task Response
                                        </span>
                                        <span class="inline-flex items-center text-xs">
                                            <span class="w-3 h-3 bg-purple-200 rounded mr-1"></span>
                                            Coherence & Cohesion
                                        </span>
                                        <span class="inline-flex items-center text-xs">
                                            <span class="w-3 h-3 bg-amber-200 rounded mr-1"></span>
                                            Lexical Resource
                                        </span>
                                        <span class="inline-flex items-center text-xs">
                                            <span class="w-3 h-3 bg-red-200 rounded mr-1"></span>
                                            Grammatical Range & Accuracy
                                        </span>
                                    </div>
                                    
                                    <div class="bg-blue-50 rounded-lg p-4">
                                        <div id="studentResponse_{{ $index }}" 
                                             class="text-gray-800 whitespace-pre-wrap leading-relaxed text-marking-container"
                                             data-task-number="{{ $index + 1 }}"
                                             data-answer-id="{{ $answer->id }}">{{ $answer->answer }}</div>
                                    </div>
                                    
                                    <!-- Error Summary -->
                                    <div id="errorSummary_{{ $index }}" class="mt-3 hidden">
                                        <div class="bg-amber-50 rounded-lg p-3 border border-amber-200">
                                            <p class="text-sm font-medium text-amber-900 mb-2">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Marked Errors
                                            </p>
                                            <div id="errorList_{{ $index }}" class="flex flex-wrap gap-2"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Scoring Grid -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm font-medium text-gray-700 mb-3">Band Score Criteria</p>
                                    @if($index == 0)
                                        <!-- Task 1 Criteria - 4 fields -->
                                        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Task Achievement</label>
                                                <select name="task_scores[{{ $index }}][task_achievement]" 
                                                        class="band-score-select"
                                                        onchange="calculateOverallBand({{ $index }})"
                                                        required>
                                                    <option value="">-</option>
                                                    @for($i = 0; $i <= 9; $i += 0.5)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Coherence & Cohesion</label>
                                                <select name="task_scores[{{ $index }}][coherence_cohesion]" 
                                                        class="band-score-select"
                                                        onchange="calculateOverallBand({{ $index }})"
                                                        required>
                                                    <option value="">-</option>
                                                    @for($i = 0; $i <= 9; $i += 0.5)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Lexical Resource</label>
                                                <select name="task_scores[{{ $index }}][lexical_resource]" 
                                                        class="band-score-select"
                                                        onchange="calculateOverallBand({{ $index }})"
                                                        required>
                                                    <option value="">-</option>
                                                    @for($i = 0; $i <= 9; $i += 0.5)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Grammatical Range & Accuracy</label>
                                                <select name="task_scores[{{ $index }}][grammar]" 
                                                        class="band-score-select"
                                                        onchange="calculateOverallBand({{ $index }})"
                                                        required>
                                                    <option value="">-</option>
                                                    @for($i = 0; $i <= 9; $i += 0.5)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Overall Band</label>
                                                <input type="text" 
                                                       name="task_scores[{{ $index }}][score]" 
                                                       id="overall_band_{{ $index }}"
                                                       class="overall-band-input"
                                                       value=""
                                                       readonly
                                                       required>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Task 2 Criteria -->
                                        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Task Response</label>
                                                <select name="task_scores[{{ $index }}][task_achievement]" 
                                                        class="band-score-select"
                                                        onchange="calculateOverallBand({{ $index }})"
                                                        required>
                                                    <option value="">-</option>
                                                    @for($i = 0; $i <= 9; $i += 0.5)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Coherence & Cohesion</label>
                                                <select name="task_scores[{{ $index }}][coherence_cohesion]" 
                                                        class="band-score-select"
                                                        onchange="calculateOverallBand({{ $index }})"
                                                        required>
                                                    <option value="">-</option>
                                                    @for($i = 0; $i <= 9; $i += 0.5)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Lexical Resource</label>
                                                <select name="task_scores[{{ $index }}][lexical_resource]" 
                                                        class="band-score-select"
                                                        onchange="calculateOverallBand({{ $index }})"
                                                        required>
                                                    <option value="">-</option>
                                                    @for($i = 0; $i <= 9; $i += 0.5)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Grammatical Range & Accuracy</label>
                                                <select name="task_scores[{{ $index }}][grammar]" 
                                                        class="band-score-select"
                                                        onchange="calculateOverallBand({{ $index }})"
                                                        required>
                                                    <option value="">-</option>
                                                    @for($i = 0; $i <= 9; $i += 0.5)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Overall Band</label>
                                                <input type="text" 
                                                       name="task_scores[{{ $index }}][score]" 
                                                       id="overall_band_{{ $index }}"
                                                       class="overall-band-input"
                                                       value=""
                                                       readonly
                                                       required>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Feedback -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Detailed Feedback
                                    </label>
                                    <textarea name="task_scores[{{ $index }}][feedback]" 
                                              rows="3"
                                              class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Provide specific feedback for this task..."
                                              required></textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
                
                <!-- Overall Assessment -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-clipboard-check text-blue-600 mr-2"></i>
                        Overall Assessment
                    </h3>
                    
                    @if($sectionName === 'speaking')
                        <!-- Speaking Overall Band Calculation -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 mb-6 border-2 border-blue-200">
                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                                <!-- Part Scores Display -->
                                <div class="text-center">
                                    <p class="text-xs font-medium text-gray-600 mb-2">Part 1 Score</p>
                                    <div class="text-3xl font-bold text-blue-600" id="display_part_1">-</div>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs font-medium text-gray-600 mb-2">Part 2 Score</p>
                                    <div class="text-3xl font-bold text-blue-600" id="display_part_2">-</div>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs font-medium text-gray-600 mb-2">Part 3 Score</p>
                                    <div class="text-3xl font-bold text-blue-600" id="display_part_3">-</div>
                                </div>
                                <div class="text-center border-l-2 border-blue-300">
                                    <p class="text-xs font-medium text-gray-600 mb-2">Overall Band Score</p>
                                    <div class="text-4xl font-bold text-indigo-700" id="speaking_overall_display">-</div>
                                    <p class="text-xs text-gray-500 mt-1">(Average of 3 parts)</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden input for overall band -->
                        <input type="hidden" name="overall_band_score" id="speaking_overall_band" value="" required>
                    @else
                        <!-- Writing Overall Band Calculation -->
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-6 mb-6 border-2 border-purple-200">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Task Scores Display -->
                                <div class="text-center">
                                    <p class="text-xs font-medium text-gray-600 mb-2">Task 1 Score</p>
                                    <div class="text-3xl font-bold text-purple-600" id="display_task_0">-</div>
                                    <p class="text-xs text-gray-500 mt-1">33.33% weight</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs font-medium text-gray-600 mb-2">Task 2 Score</p>
                                    <div class="text-3xl font-bold text-purple-600" id="display_task_1">-</div>
                                    <p class="text-xs text-gray-500 mt-1">66.67% weight</p>
                                </div>
                                <div class="text-center border-l-2 border-purple-300">
                                    <p class="text-xs font-medium text-gray-600 mb-2">Overall Band Score</p>
                                    <div class="text-4xl font-bold text-pink-700" id="writing_overall_display">-</div>
                                    <p class="text-xs text-gray-500 mt-1">(Task 1×1 + Task 2×2) ÷ 3</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden input for overall band -->
                        <input type="hidden" name="overall_band_score" id="writing_overall_band" value="" required>
                    @endif
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Strengths -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Key Strengths
                            </label>
                            <div id="strengths-container" class="space-y-2">
                                <div class="strength-input">
                                    <input type="text" 
                                           name="strengths[]" 
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="e.g., Good vocabulary range"
                                           required>
                                </div>
                            </div>
                            <button type="button" 
                                    onclick="addStrength()"
                                    class="text-sm text-blue-600 hover:text-blue-700 mt-2">
                                <i class="fas fa-plus-circle mr-1"></i>Add strength
                            </button>
                        </div>
                        
                        <!-- Improvements -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Areas for Improvement
                            </label>
                            <div id="improvements-container" class="space-y-2">
                                <div class="improvement-input">
                                    <input type="text" 
                                           name="improvements[]" 
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="e.g., Work on paragraph structure"
                                           required>
                                </div>
                            </div>
                            <button type="button" 
                                    onclick="addImprovement()"
                                    class="text-sm text-blue-600 hover:text-blue-700 mt-2">
                                <i class="fas fa-plus-circle mr-1"></i>Add improvement
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Section -->
                <div class="sticky bottom-0 bg-white border-t mt-6 px-6 py-4 rounded-xl shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <button type="button" 
                                    onclick="saveDraft()"
                                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                <i class="fas fa-save mr-2"></i>Save Draft
                            </button>
                            <span id="saveStatus" class="text-sm text-gray-500 hidden">
                                <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                Draft saved
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('teacher.evaluations.pending') }}" 
                               class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                                <i class="fas fa-check mr-2"></i>
                                Submit Evaluation
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
    
    @push('styles')
    <style>
        /* Container adjustments */
        .container {
            max-width: 1400px !important;
        }
        
        /* Clean minimal styles */
        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        /* Audio Player Styles */
        .audio-player-container {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin-top: 8px;
        }
        
        .audio-player-container audio {
            width: 100%;
            height: 40px;
            outline: none;
        }
        
        /* Compact Audio Player for Part-wise View */
        .audio-player-container-compact {
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px;
            margin-top: 8px;
        }
        
        .audio-player-container-compact audio {
            width: 100%;
            height: 32px;
            outline: none;
        }
        
        .audio-meta {
            display: flex;
            gap: 12px;
            margin-top: 8px;
            font-size: 12px;
            color: #6b7280;
        }
        
        .audio-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .no-recording {
            padding: 12px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            color: #991b1b;
            font-size: 14px;
            margin-top: 8px;
        }
        
        .no-recording-compact {
            padding: 6px 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 4px;
            color: #991b1b;
            font-size: 12px;
            margin-top: 6px;
            text-align: center;
        }
        
        /* Modal styles */
        #errorTypeModal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            z-index: 99999 !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #errorTypeModal .relative {
            position: relative !important;
            z-index: 100000 !important;
            margin: auto;
            animation: modalFadeIn 0.2s ease-out;
            max-width: 450px !important;
            width: 90% !important;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        /* Error marking styles */
        .error-mark {
            padding: 1px 3px;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.15s;
            position: relative;
            display: inline;
            margin: 0;
            line-height: inherit;
        }
        
        /* Error note tooltip */
        .error-note-tooltip {
            position: absolute;
            bottom: calc(100% + 2px);
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 4px 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            z-index: 10000;
            display: none;
            pointer-events: auto;
            font-size: 10px;
            color: #6b7280;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s;
            font-weight: 500;
        }
        
        .error-note-tooltip:hover {
            background: #f9fafb;
            color: #3b82f6;
        }
        
        .error-mark:hover .error-note-tooltip {
            display: block;
        }
        
        .error-note-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: white;
            filter: drop-shadow(0 2px 1px rgba(0,0,0,0.05));
        }
        
        /* Note editing modal */
        .error-note-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 100000;
            min-width: 320px;
            max-width: 400px;
            display: none;
        }
        
        .error-note-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 99999;
            display: none;
        }
        
        .error-note-input {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 12px;
            margin-top: 6px;
        }
        
        .error-note-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        
        .error-note-buttons {
            display: flex;
            gap: 6px;
            margin-top: 8px;
        }
        
        .error-note-btn {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .error-note-save {
            background: #3b82f6;
            color: white;
            border: none;
        }
        
        .error-note-save:hover {
            background: #2563eb;
        }
        
        .error-note-delete {
            background: #ef4444;
            color: white;
            border: none;
        }
        
        .error-note-delete:hover {
            background: #dc2626;
        }
        
        .error-has-note {
            border-bottom: 2px dotted currentColor;
        }
        
        .error-mark.task_achievement {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        
        .error-mark.coherence_cohesion {
            background-color: #E9D5FF;
            color: #6B21A8;
        }
        
        .error-mark.lexical_resource {
            background-color: #FED7AA;
            color: #92400E;
        }
        
        .error-mark.grammar {
            background-color: #FECACA;
            color: #991B1B;
        }
        
        .error-mark:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Text selection */
        .text-marking-container {
            user-select: text;
            line-height: 1.8;
            cursor: text;
            will-change: contents;
        }
        
        /* Improve scroll performance */
        .text-marking-container * {
            will-change: auto;
        }
        
        ::selection {
            background-color: #FEF3C7;
            color: #1F2937;
        }
        
        /* Form inputs */
        select, input, textarea {
            transition: all 0.2s;
        }

        select:focus, input:focus, textarea:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Score band dropdowns — prominent, easy to read */
        .band-score-select {
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            background-color: #ffffff !important;
            border: 1.5px solid #cbd5e1 !important;
            padding: 8px 12px !important;
            height: auto !important;
            min-height: 42px !important;
            border-radius: 8px !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04) !important;
            text-align: center !important;
            text-align-last: center !important;
            cursor: pointer !important;
        }
        .band-score-select:hover {
            border-color: #3b82f6 !important;
            box-shadow: 0 2px 4px rgba(59,130,246,0.12) !important;
        }
        .band-score-select:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15) !important;
        }
        .band-score-select option {
            font-weight: 600;
            font-size: 14px;
            padding: 6px;
        }

        .band-score-label {
            display: block;
            font-size: 11px !important;
            font-weight: 700 !important;
            color: #475569 !important;
            margin-bottom: 6px !important;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .overall-band-input {
            font-size: 20px !important;
            font-weight: 800 !important;
            color: #C8102E !important;
            background: linear-gradient(135deg, #fef2f4 0%, #fff5f7 100%) !important;
            border: 2px solid #C8102E !important;
            min-height: 42px !important;
            border-radius: 8px !important;
            text-align: center !important;
        }
        
        /* Sticky footer shadow */
        .sticky {
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
        }
    </style>
    @endpush
    
    @push('scripts')
    <script>
        // Error marking functionality
        let errorMarkings = [];
        let currentSelection = null;
        let markingIdCounter = 0;
        let currentEditingMarkingId = null;
        
        // Note management functions
        window.openNoteModal = function(markingId, event) {
            event.stopPropagation();
            event.preventDefault();
            
            const marking = errorMarkings.find(m => m.id === markingId);
            if (!marking) return;
            
            currentEditingMarkingId = markingId;
            
            const errorLabels = {
                'task_achievement': 'Task Response',
                'coherence_cohesion': 'Coherence & Cohesion',
                'lexical_resource': 'Lexical Resource',
                'grammar': 'Grammatical Range & Accuracy'
            };
            
            document.getElementById('noteModalErrorType').textContent = errorLabels[marking.errorType];
            document.getElementById('noteModalText').textContent = marking.text.substring(0, 50) + (marking.text.length > 50 ? '...' : '');
            document.getElementById('noteModalInput').value = marking.note || '';
            
            document.getElementById('noteModalOverlay').style.display = 'block';
            document.getElementById('noteModal').style.display = 'block';
            
            // Focus on textarea
            setTimeout(() => {
                document.getElementById('noteModalInput').focus();
            }, 100);
        };
        
        window.closeNoteModal = function() {
            document.getElementById('noteModalOverlay').style.display = 'none';
            document.getElementById('noteModal').style.display = 'none';
            currentEditingMarkingId = null;
        };
        
        window.saveNoteFromModal = function() {
            if (!currentEditingMarkingId) return;
            
            const noteValue = document.getElementById('noteModalInput').value;
            const marking = errorMarkings.find(m => m.id === currentEditingMarkingId);
            
            if (marking) {
                marking.note = noteValue;
                marking.comment = noteValue; // Save to comment field for database
                updateErrorMarkingsInput();
                renderErrorMarkings();
            }
            
            closeNoteModal();
        };
        
        window.deleteNoteFromModal = function() {
            if (!currentEditingMarkingId) return;
            window.removeMarking(currentEditingMarkingId);
            closeNoteModal();
        };
        
        // Calculate Part Band Score for Speaking (IELTS Official Formula)
        window.calculatePartBand = function(partNumber) {
            // Get all 4 criteria values for speaking
            const fluency = parseFloat(document.querySelector(`select[name="part_scores[${partNumber}][fluency_coherence]"]`)?.value);
            const lexical = parseFloat(document.querySelector(`select[name="part_scores[${partNumber}][lexical_resource]"]`)?.value);
            const grammar = parseFloat(document.querySelector(`select[name="part_scores[${partNumber}][grammar]"]`)?.value);
            const pronunciation = parseFloat(document.querySelector(`select[name="part_scores[${partNumber}][pronunciation]"]`)?.value);
            
            // Check if all criteria are filled
            if (!isNaN(fluency) && !isNaN(lexical) && !isNaN(grammar) && !isNaN(pronunciation)) {
                // Calculate average of the 4 criteria
                const average = (fluency + lexical + grammar + pronunciation) / 4;
                
                // Round to nearest 0.5 (IELTS Official Rounding Rule)
                const rounded = Math.round(average * 2) / 2;
                
                // Set the part band score
                const inputField = document.getElementById(`part_band_${partNumber}`);
                if (inputField) {
                    inputField.value = rounded.toFixed(1);
                }
                
                // Update display
                const displayField = document.getElementById(`display_part_${partNumber}`);
                if (displayField) {
                    displayField.textContent = rounded.toFixed(1);
                }
            } else {
                const inputField = document.getElementById(`part_band_${partNumber}`);
                if (inputField) {
                    inputField.value = '';
                }
                
                const displayField = document.getElementById(`display_part_${partNumber}`);
                if (displayField) {
                    displayField.textContent = '-';
                }
            }
            
            // Calculate overall speaking band
            calculateSpeakingOverallBand();
        };
        
        // Calculate Overall Speaking Band (Average of 3 parts)
        window.calculateSpeakingOverallBand = function() {
            const part1 = parseFloat(document.getElementById('part_band_1')?.value);
            const part2 = parseFloat(document.getElementById('part_band_2')?.value);
            const part3 = parseFloat(document.getElementById('part_band_3')?.value);
            
            if (!isNaN(part1) && !isNaN(part2) && !isNaN(part3)) {
                // Calculate average of 3 parts
                const average = (part1 + part2 + part3) / 3;
                
                // Round to nearest 0.5 (IELTS Official Rounding Rule)
                const rounded = Math.round(average * 2) / 2;
                
                // Set hidden input value
                const hiddenInput = document.getElementById('speaking_overall_band');
                if (hiddenInput) {
                    hiddenInput.value = rounded.toFixed(1);
                }
                
                // Update display
                const display = document.getElementById('speaking_overall_display');
                if (display) {
                    display.textContent = rounded.toFixed(1);
                    display.classList.add('animate-pulse');
                    setTimeout(() => {
                        display.classList.remove('animate-pulse');
                    }, 500);
                }
            } else {
                const hiddenInput = document.getElementById('speaking_overall_band');
                if (hiddenInput) {
                    hiddenInput.value = '';
                }
                
                const display = document.getElementById('speaking_overall_display');
                if (display) {
                    display.textContent = '-';
                }
            }
        };
        
        // Calculate Overall Band Score (IELTS Official Formula)
        window.calculateOverallBand = function(taskIndex) {
            // Get all 4 criteria values
            const taskAchievement = parseFloat(document.querySelector(`select[name="task_scores[${taskIndex}][task_achievement]"]`).value);
            const coherence = parseFloat(document.querySelector(`select[name="task_scores[${taskIndex}][coherence_cohesion]"]`).value);
            const lexical = parseFloat(document.querySelector(`select[name="task_scores[${taskIndex}][lexical_resource]"]`).value);
            const grammar = parseFloat(document.querySelector(`select[name="task_scores[${taskIndex}][grammar]"]`).value);
            
            // Check if all criteria are filled
            if (!isNaN(taskAchievement) && !isNaN(coherence) && !isNaN(lexical) && !isNaN(grammar)) {
                // Calculate average of the 4 criteria (same for both Task 1 and Task 2)
                const average = (taskAchievement + coherence + lexical + grammar) / 4;
                
                // Round to nearest 0.5 (IELTS Official Rounding Rule)
                const rounded = Math.round(average * 2) / 2;
                
                // Set the overall band score
                document.getElementById(`overall_band_${taskIndex}`).value = rounded.toFixed(1);
                
                // Update display
                const displayField = document.getElementById(`display_task_${taskIndex}`);
                if (displayField) {
                    displayField.textContent = rounded.toFixed(1);
                }
            } else {
                document.getElementById(`overall_band_${taskIndex}`).value = '';
                
                const displayField = document.getElementById(`display_task_${taskIndex}`);
                if (displayField) {
                    displayField.textContent = '-';
                }
            }
            
            // Calculate writing overall band
            calculateWritingOverallBand();
        };
        
        // Calculate Overall Writing Band (IELTS Official Weighted Formula)
        // Task 1 = 33.33% (weight 1), Task 2 = 66.67% (weight 2)
        // Formula: (Task1×1 + Task2×2) ÷ 3
        window.calculateWritingOverallBand = function() {
            const task1 = parseFloat(document.getElementById('overall_band_0')?.value);
            const task2 = parseFloat(document.getElementById('overall_band_1')?.value);
            
            if (!isNaN(task1) && !isNaN(task2)) {
                // IELTS Official Writing Formula: Weighted average
                // Task 1 contributes 1/3, Task 2 contributes 2/3
                const weightedAverage = (task1 * 1 + task2 * 2) / 3;
                
                // Round to nearest 0.5 (IELTS Official Rounding Rule)
                const rounded = Math.round(weightedAverage * 2) / 2;
                
                // Set hidden input value
                const hiddenInput = document.getElementById('writing_overall_band');
                if (hiddenInput) {
                    hiddenInput.value = rounded.toFixed(1);
                }
                
                // Update display
                const display = document.getElementById('writing_overall_display');
                if (display) {
                    display.textContent = rounded.toFixed(1);
                    display.classList.add('animate-pulse');
                    setTimeout(() => {
                        display.classList.remove('animate-pulse');
                    }, 500);
                }
            } else {
                const hiddenInput = document.getElementById('writing_overall_band');
                if (hiddenInput) {
                    hiddenInput.value = '';
                }
                
                const display = document.getElementById('writing_overall_display');
                if (display) {
                    display.textContent = '-';
                }
            }
        };
        
        // Make functions available globally
        window.markError = markError;
        window.closeErrorModal = closeErrorModal;
        window.removeMarking = removeMarking;
        window.addStrength = addStrength;
        window.addImprovement = addImprovement;
        window.removeField = removeField;
        window.saveDraft = saveDraft;
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Hide modal on load
            const modal = document.getElementById('errorTypeModal');
            if (modal) modal.style.display = 'none';
            
            // Use event delegation for better performance
            document.body.addEventListener('mouseup', function(event) {
                const container = event.target.closest('.text-marking-container');
                if (container) {
                    // Debounce the selection handler
                    clearTimeout(window.selectionTimeout);
                    window.selectionTimeout = setTimeout(() => {
                        handleTextSelection(event);
                    }, 100);
                }
            });
            
            // Left-click on marked error (not tooltip) to remove
            document.body.addEventListener('click', function(event) {
                const errorMark = event.target.closest('.error-mark');
                // Only remove if NOT clicking on the tooltip
                if (errorMark && !event.target.closest('.error-note-tooltip')) {
                    event.preventDefault();
                    event.stopPropagation();
                    const markingId = parseInt(errorMark.dataset.markingId);
                    if (markingId) {
                        window.removeMarking(markingId);
                    }
                }
            });
            
            // Audio error handling
            const audioElements = document.querySelectorAll('audio');
            audioElements.forEach(audio => {
                audio.addEventListener('error', function(e) {
                    console.error('Audio error:', e);
                    const container = audio.closest('.audio-player-container');
                    if (container) {
                        container.style.borderColor = '#fca5a5';
                        container.style.backgroundColor = '#fef2f2';
                    }
                });
            });
            
            // Progress tracking
            updateProgress();
            document.querySelectorAll('select, input, textarea').forEach(field => {
                field.addEventListener('change', updateProgress);
            });
            
            // Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeErrorModal();
                    closeNoteModal();
                }
            });
            
            // Auto-save with debouncing
            let autoSaveTimer;
            document.getElementById('evaluationForm')?.addEventListener('input', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(saveDraft, 3000);
            });
        });
        
        function handleTextSelection(event) {
            // Prevent handling if clicking on tooltip
            if (event.target.closest('.error-note-tooltip')) {
                return;
            }
            
            const selection = window.getSelection();
            const selectedText = selection.toString().trim();
            
            if (selectedText.length > 0 && selectedText.length < 500) { // Limit selection size
                const container = event.target.closest('.text-marking-container');
                if (!container) return;
                
                const range = selection.getRangeAt(0);
                
                // Check if selection is within the container
                if (!container.contains(range.commonAncestorContainer)) {
                    return;
                }
                
                const startOffset = getTextOffset(container, range.startContainer, range.startOffset);
                const endOffset = getTextOffset(container, range.endContainer, range.endOffset);
                
                currentSelection = {
                    text: selectedText,
                    taskNumber: container.dataset.taskNumber,
                    answerId: container.dataset.answerId,
                    startOffset: startOffset,
                    endOffset: endOffset,
                    container: container
                };
                
                document.getElementById('selectedTextDisplay').textContent = selectedText.substring(0, 50) + (selectedText.length > 50 ? '...' : '');
                const modal = document.getElementById('errorTypeModal');
                modal.style.display = 'flex';
            }
        }
        
        function getTextOffset(container, node, offset) {
            // Walk only TEXT nodes that aren't inside UI chrome like .error-note-tooltip.
            // Without this filter the tooltip text ("Add"/"Note") gets counted in the offset
            // and selections after a marking land on the wrong word.
            const walker = document.createTreeWalker(
                container,
                NodeFilter.SHOW_TEXT,
                {
                    acceptNode(n) {
                        if (n.parentElement && n.parentElement.closest('.error-note-tooltip')) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        return NodeFilter.FILTER_ACCEPT;
                    }
                },
                false
            );

            let textOffset = 0;
            let currentNode;
            while ((currentNode = walker.nextNode())) {
                if (currentNode === node) {
                    return textOffset + offset;
                }
                textOffset += currentNode.textContent.length;
            }
            return textOffset;
        }
        
        function markError(errorType) {
            if (!currentSelection) return;
            
            const marking = {
            id: ++markingIdCounter,
            text: currentSelection.text,
            taskNumber: currentSelection.taskNumber,
            answerId: currentSelection.answerId,
            startOffset: currentSelection.startOffset,
            endOffset: currentSelection.endOffset,
            errorType: errorType,
            note: '', // Initialize with empty note
                comment: '' // Database field name
            };
            
            errorMarkings.push(marking);
            updateErrorMarkingsInput();
            renderErrorMarkings();
            closeErrorModal();
            window.getSelection().removeAllRanges();
        }
        
        function renderErrorMarkings() {
            // Use requestAnimationFrame for smooth rendering
            requestAnimationFrame(() => {
                document.querySelectorAll('.text-marking-container').forEach(container => {
                    const taskNumber = container.dataset.taskNumber;
                    const relevantMarkings = errorMarkings
                        .filter(m => m.taskNumber === taskNumber)
                        .sort((a, b) => b.startOffset - a.startOffset);
                    
                    if (relevantMarkings.length === 0) {
                        const originalText = container.getAttribute('data-original-text') || container.textContent;
                        container.innerHTML = escapeHtml(originalText);
                    } else {
                        if (!container.hasAttribute('data-original-text')) {
                            container.setAttribute('data-original-text', container.textContent);
                        }
                        
                        const originalText = container.getAttribute('data-original-text');
                        const fragments = [];
                        let lastIndex = 0;
                        
                        // Sort markings by start offset for sequential processing
                        const sortedMarkings = [...relevantMarkings].sort((a, b) => a.startOffset - b.startOffset);
                        
                        sortedMarkings.forEach(marking => {
                            // Add text before marking
                            if (marking.startOffset > lastIndex) {
                                fragments.push(escapeHtml(originalText.substring(lastIndex, marking.startOffset)));
                            }
                            
                            const hasNote = marking.note && marking.note.trim() !== '';
                            const noteClass = hasNote ? 'error-has-note' : '';
                            const noteIcon = hasNote ? '<i class="fas fa-sticky-note" style="font-size: 8px; margin-right: 2px;"></i>' : '<i class="fas fa-plus" style="font-size: 7px; margin-right: 2px;"></i>';
                            const noteText = hasNote ? 'Note' : 'Add';
                            
                            const markedText = escapeHtml(originalText.substring(marking.startOffset, marking.endOffset));
                            
                            // Error mark with hover tooltip for adding notes
                            fragments.push(`<span class="error-mark ${marking.errorType} ${noteClass}" data-marking-id="${marking.id}" title="Click to remove">${markedText}<span class="error-note-tooltip" onclick="openNoteModal(${marking.id}, event)">${noteIcon}${noteText}</span></span>`);
                            
                            lastIndex = marking.endOffset;
                        });
                        
                        // Add remaining text
                        if (lastIndex < originalText.length) {
                            fragments.push(escapeHtml(originalText.substring(lastIndex)));
                        }
                        
                        container.innerHTML = fragments.join('');
                    }
                    
                    updateErrorSummary(taskNumber - 1);
                });
            });
        }
        
        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function removeMarking(markingId) {
            const markingIndex = errorMarkings.findIndex(m => m.id === markingId);
            if (markingIndex > -1) {
                errorMarkings.splice(markingIndex, 1);
                updateErrorMarkingsInput();
                renderErrorMarkings();
            }
        }
        
        function updateErrorSummary(index) {
            const taskNumber = index + 1;
            const taskMarkings = errorMarkings.filter(m => m.taskNumber == taskNumber);
            const summaryContainer = document.getElementById(`errorSummary_${index}`);
            const errorList = document.getElementById(`errorList_${index}`);
            
            if (taskMarkings.length > 0) {
                summaryContainer.classList.remove('hidden');
                const grouped = taskMarkings.reduce((acc, marking) => {
                    if (!acc[marking.errorType]) acc[marking.errorType] = 0;
                    acc[marking.errorType]++;
                    return acc;
                }, {});
                
                errorList.innerHTML = Object.entries(grouped).map(([type, count]) => {
                    const labels = {
                        'task_achievement': 'Task Response',
                        'coherence_cohesion': 'Coherence & Cohesion',
                        'lexical_resource': 'Lexical Resource',
                        'grammar': 'Grammatical Range & Accuracy'
                    };
                    const colors = {
                        'task_achievement': 'bg-blue-100 text-blue-700',
                        'coherence_cohesion': 'bg-purple-100 text-purple-700',
                        'lexical_resource': 'bg-amber-100 text-amber-700',
                        'grammar': 'bg-red-100 text-red-700'
                    };
                    return `<span class="text-xs px-2 py-1 rounded-full ${colors[type]}">${labels[type]}: ${count}</span>`;
                }).join('');
            } else {
                summaryContainer.classList.add('hidden');
            }
        }
        
        function updateErrorMarkingsInput() {
            document.getElementById('errorMarkingsInput').value = JSON.stringify(errorMarkings);
        }
        
        function closeErrorModal() {
            const modal = document.getElementById('errorTypeModal');
            modal.style.display = 'none';
            currentSelection = null;
            window.getSelection().removeAllRanges();
        }
        
        function addStrength() {
            const container = document.getElementById('strengths-container');
            const div = document.createElement('div');
            div.className = 'strength-input flex items-center gap-2';
            div.innerHTML = `
                <input type="text" 
                       name="strengths[]" 
                       class="flex-1 rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="Enter a strength..."
                       required>
                <button type="button" onclick="removeField(this)" class="text-red-500 hover:text-red-600">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        function addImprovement() {
            const container = document.getElementById('improvements-container');
            const div = document.createElement('div');
            div.className = 'improvement-input flex items-center gap-2';
            div.innerHTML = `
                <input type="text" 
                       name="improvements[]" 
                       class="flex-1 rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="Enter an improvement..."
                       required>
                <button type="button" onclick="removeField(this)" class="text-red-500 hover:text-red-600">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        function removeField(button) {
            button.closest('.strength-input, .improvement-input').remove();
        }
        
        function updateProgress() {
            const form = document.getElementById('evaluationForm');
            if (!form) return;
            
            const allFields = form.querySelectorAll('select[required], input[required], textarea[required]');
            const filledFields = Array.from(allFields).filter(field => field.value).length;
            const progress = Math.round((filledFields / allFields.length) * 100);
            
            document.getElementById('progressBar').style.width = progress + '%';
            document.getElementById('progressText').textContent = progress + '%';
        }
        
        function saveDraft() {
            const form = document.getElementById('evaluationForm');
            if (!form) return;
            
            const formData = new FormData(form);
            const data = {};
            for (let [key, value] of formData.entries()) {
                if (data[key]) {
                    if (Array.isArray(data[key])) {
                        data[key].push(value);
                    } else {
                        data[key] = [data[key], value];
                    }
                } else {
                    data[key] = value;
                }
            }
            
            localStorage.setItem('evaluation_draft_{{ $evaluationRequest->id }}', JSON.stringify(data));
            
            const saveStatus = document.getElementById('saveStatus');
            saveStatus.classList.remove('hidden');
            setTimeout(() => saveStatus.classList.add('hidden'), 3000);
        }
    </script>
    @endpush
</x-teacher-layout>
