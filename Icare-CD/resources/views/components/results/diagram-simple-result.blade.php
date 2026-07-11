{{-- Simple Diagram Question Results Component --}}
<div class="diagram-results-container">
    {{-- Question Display --}}
    <div class="mb-6">
        @if($question->instructions)
            <p class="text-sm text-gray-700 mb-3">{{ $question->instructions }}</p>
        @endif
        
        {{-- Diagram Display --}}
        @if($question->media_path)
            <div class="diagram-results-wrapper mb-4">
                <img src="{{ Storage::url($question->media_path) }}" 
                     alt="Diagram" 
                     class="max-w-full rounded-lg border-2 border-gray-300"
                     style="max-height: 400px;">
            </div>
        @endif
    </div>
    
    @php
        $diagramData = $question->section_specific_data ?? [];
        $dropdownOptions = $diagramData['dropdown_options'] ?? [];
        $startNumber = $diagramData['start_number'] ?? 1;
        $correctAnswers = $diagramData['correct_answers'] ?? [];
        
        // Get student answers
        $studentAnswers = $studentAnswer->answer ?? [];
        
        // Calculate score
        $totalQuestions = count($studentAnswers);
        $correctCount = 0;
        
        foreach ($studentAnswers as $index => $answer) {
            if (isset($correctAnswers[$index]) && $answer == $correctAnswers[$index]) {
                $correctCount++;
            }
        }
        
        $percentage = $totalQuestions > 0 ? ($correctCount / $totalQuestions) * 100 : 0;
    @endphp
    
    {{-- Results Summary --}}
    <div class="results-summary mb-6">
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">Your Results</h4>
                <span class="text-lg font-bold {{ $percentage >= 70 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $correctCount }} / {{ $totalQuestions }}
                </span>
            </div>
            
            {{-- Progress Bar --}}
            <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                <div class="h-2 rounded-full transition-all duration-500 {{ $percentage >= 70 ? 'bg-green-500' : 'bg-red-500' }}" 
                     style="width: {{ $percentage }}%"></div>
            </div>
        </div>
    </div>
    
    {{-- Detailed Answer Review --}}
    <div class="answer-review">
        <h4 class="font-medium text-gray-900 mb-4">Answer Review:</h4>
        
        <div class="space-y-2">
            @foreach($studentAnswers as $index => $answer)
                @php
                    $questionNum = $startNumber + $index;
                    $correctAnswer = $correctAnswers[$index] ?? null;
                    $isCorrect = ($answer == $correctAnswer);
                @endphp
                
                <div class="answer-review-row flex items-center gap-3 p-3 rounded-lg {{ $isCorrect ? 'bg-green-50' : 'bg-red-50' }}">
                    {{-- Question Number --}}
                    <div class="flex-shrink-0">
                        <span class="inline-flex items-center justify-center w-8 h-8 {{ $isCorrect ? 'bg-green-600' : 'bg-red-600' }} text-white rounded-full font-bold text-sm">
                            {{ $questionNum }}
                        </span>
                    </div>
                    
                    {{-- Answer Details --}}
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div>
                            <span class="text-xs text-gray-600">Your answer:</span>
                            <p class="font-medium {{ $isCorrect ? 'text-green-800' : 'text-red-800' }}">
                                @if(is_numeric($answer) && isset($dropdownOptions[$answer]))
                                    {{ chr(65 + $answer) }} - {{ $dropdownOptions[$answer] }}
                                @elseif(empty($answer))
                                    <span class="text-gray-500">Not answered</span>
                                @else
                                    {{ $answer }}
                                @endif
                            </p>
                        </div>
                        
                        @if(!$isCorrect && $correctAnswer !== null)
                            <div>
                                <span class="text-xs text-gray-600">Correct answer:</span>
                                <p class="font-medium text-green-800">
                                    @if(is_numeric($correctAnswer) && isset($dropdownOptions[$correctAnswer]))
                                        {{ chr(65 + $correctAnswer) }} - {{ $dropdownOptions[$correctAnswer] }}
                                    @else
                                        {{ $correctAnswer }}
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Status Icon --}}
                    <div class="flex-shrink-0">
                        @if($isCorrect)
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
