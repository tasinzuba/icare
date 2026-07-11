{{-- Diagram Question Results Component --}}
<div class="diagram-results-container">
    {{-- Question Display --}}
    <div class="mb-6">
        @if($question->instructions)
            <p class="text-sm text-gray-700 mb-3">{{ $question->instructions }}</p>
        @endif
        
        {{-- Diagram with Results --}}
        <div class="diagram-results-wrapper">
            <div class="relative inline-block">
                <img src="{{ Storage::url($question->media_path) }}" 
                     alt="Diagram" 
                     class="max-w-full rounded-lg border-2 border-gray-300"
                     style="max-height: 400px;">
                
                {{-- Result Markers --}}
                @php
                    $diagramData = $question->diagram_hotspots ?? [];
                    $labels = $diagramData['labels'] ?? [];
                    $detailedResults = $studentAnswer->detailed_results ?? [];
                    $labelResults = $detailedResults['label_results'] ?? [];
                @endphp
                
                @foreach($labels as $index => $label)
                    @php
                        $result = $labelResults[$index] ?? null;
                        $isCorrect = $result['is_correct'] ?? false;
                    @endphp
                    <div class="result-label-marker" 
                         style="position: absolute; 
                                left: {{ $label['x'] }}%; 
                                top: {{ $label['y'] }}%;
                                transform: translate(-50%, -50%);">
                        <div class="marker-circle {{ $isCorrect ? 'correct' : 'incorrect' }}">
                            {{ $label['question_number'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    {{-- Results Summary --}}
    <div class="results-summary mb-6">
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">Your Results</h4>
                <span class="text-lg font-bold {{ $detailedResults['percentage'] >= 70 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $detailedResults['correct_count'] ?? 0 }} / {{ $detailedResults['total_labels'] ?? 0 }}
                </span>
            </div>
            
            {{-- Progress Bar --}}
            <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                <div class="h-2 rounded-full transition-all duration-500 {{ $detailedResults['percentage'] >= 70 ? 'bg-green-500' : 'bg-red-500' }}" 
                     style="width: {{ $detailedResults['percentage'] ?? 0 }}%"></div>
            </div>
        </div>
    </div>
    
    {{-- Detailed Answer Review --}}
    <div class="answer-review">
        <h4 class="font-medium text-gray-900 mb-4">Answer Review:</h4>
        
        <div class="space-y-2">
            @foreach($labelResults as $index => $result)
                <div class="answer-review-row flex items-center gap-3 p-3 rounded-lg {{ $result['is_correct'] ? 'bg-green-50' : 'bg-red-50' }}">
                    {{-- Question Number --}}
                    <div class="flex-shrink-0">
                        <span class="inline-flex items-center justify-center w-8 h-8 {{ $result['is_correct'] ? 'bg-green-600' : 'bg-red-600' }} text-white rounded-full font-bold text-sm">
                            {{ $result['question_number'] }}
                        </span>
                    </div>
                    
                    {{-- Answer Details --}}
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div>
                            <span class="text-xs text-gray-600">Your answer:</span>
                            <p class="font-medium {{ $result['is_correct'] ? 'text-green-800' : 'text-red-800' }}">
                                @if($diagramData['answer_type'] === 'dropdown' && is_numeric($result['student_answer']))
                                    {{ chr(65 + $result['student_answer']) }} - {{ $diagramData['dropdown_options'][$result['student_answer']] ?? 'Not answered' }}
                                @elseif(empty($result['student_answer']))
                                    <span class="text-gray-500">Not answered</span>
                                @else
                                    {{ $result['student_answer'] }}
                                @endif
                            </p>
                        </div>
                        
                        @if(!$result['is_correct'])
                            <div>
                                <span class="text-xs text-gray-600">Correct answer:</span>
                                <p class="font-medium text-green-800">
                                    @if($diagramData['answer_type'] === 'dropdown' && is_numeric($result['correct_answer']))
                                        {{ chr(65 + $result['correct_answer']) }} - {{ $diagramData['dropdown_options'][$result['correct_answer']] ?? '' }}
                                    @else
                                        {{ $result['correct_answer'] }}
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Status Icon --}}
                    <div class="flex-shrink-0">
                        @if($result['is_correct'])
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @else
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    {{-- Audio Transcript (if available) --}}
    @if($question->audio_transcript)
        <div class="mt-6">
            <button type="button" 
                    onclick="toggleTranscript({{ $question->id }})"
                    class="flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                View Audio Transcript
            </button>
            
            <div id="transcript-{{ $question->id }}" class="hidden mt-3 p-4 bg-gray-50 rounded-lg">
                <h5 class="font-medium text-gray-900 mb-2">Audio Transcript:</h5>
                <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $question->audio_transcript }}</div>
            </div>
        </div>
    @endif
</div>

<style>
/* Result marker styles */
.result-label-marker .marker-circle {
    width: 26px;
    height: 26px;
    border: 3px solid white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    color: white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
}

.result-label-marker .marker-circle.correct {
    background-color: #16a34a;
}

.result-label-marker .marker-circle.incorrect {
    background-color: #dc2626;
}

/* Answer review styling */
.answer-review-row {
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

.answer-review-row:hover {
    border-color: currentColor;
    transform: translateX(2px);
}

/* Mobile responsive */
@media (max-width: 640px) {
    .diagram-results-wrapper img {
        max-height: 250px !important;
    }
    
    .result-label-marker .marker-circle {
        width: 22px;
        height: 22px;
        font-size: 10px;
    }
}
</style>

<script>
function toggleTranscript(questionId) {
    const transcript = document.getElementById('transcript-' + questionId);
    if (transcript) {
        transcript.classList.toggle('hidden');
    }
}
</script>
