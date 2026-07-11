{{-- Student Diagram Question Component --}}
<div class="diagram-question-container">
    {{-- Question Instructions --}}
    @if($question->instructions)
        <div class="mb-4 p-3 bg-blue-50 rounded-lg">
            <p class="text-sm text-gray-800">{{ $question->instructions }}</p>
        </div>
    @endif
    
    {{-- Diagram Display --}}
    <div class="diagram-wrapper mb-6">
        <div class="relative inline-block">
            {{-- Main Diagram Image --}}
            <img src="{{ Storage::url($question->media_path) }}" 
                 alt="Diagram" 
                 class="max-w-full rounded-lg border-2 border-gray-300"
                 style="max-height: 500px;">
            
            {{-- Label Markers --}}
            <div class="label-markers-container">
                @php
                    $diagramData = $question->diagram_hotspots ?? [];
                    $answerType = $diagramData['answer_type'] ?? 'dropdown';
                    $dropdownOptions = $diagramData['dropdown_options'] ?? [];
                    $labels = $diagramData['labels'] ?? [];
                @endphp
                
                @foreach($labels as $index => $label)
                    <div class="student-label-marker" 
                         style="position: absolute; 
                                left: {{ $label['x'] }}%; 
                                top: {{ $label['y'] }}%;
                                transform: translate(-50%, -50%);">
                        <div class="marker-circle">
                            {{ $label['question_number'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    {{-- Answer Input Section --}}
    <div class="answer-section">
        <h4 class="font-medium text-gray-900 mb-4">Complete the labels on the diagram:</h4>
        
        <div class="space-y-3">
            @foreach($labels as $index => $label)
                <div class="answer-row flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    {{-- Question Number --}}
                    <div class="question-number">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full font-bold text-sm">
                            {{ $label['question_number'] }}
                        </span>
                    </div>
                    
                    {{-- Answer Input --}}
                    <div class="flex-1">
                        @if($answerType === 'dropdown')
                            {{-- Dropdown Answer --}}
                            <select name="answers[{{ $question->id }}][{{ $index }}]" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    data-question-id="{{ $question->id }}"
                                    data-label-index="{{ $index }}">
                                <option value="">Choose from the list</option>
                                @foreach($dropdownOptions as $optIndex => $option)
                                    <option value="{{ $optIndex }}">
                                        {{ chr(65 + $optIndex) }} - {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            {{-- Text Input Answer --}}
                            <input type="text" 
                                   name="answers[{{ $question->id }}][{{ $index }}]"
                                   placeholder="Type your answer"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   data-question-id="{{ $question->id }}"
                                   data-label-index="{{ $index }}">
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        {{-- Answer Options Reference (for dropdown type) --}}
        @if($answerType === 'dropdown' && count($dropdownOptions) > 0)
            <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
                <h5 class="text-sm font-medium text-gray-800 mb-2">Available Options:</h5>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach($dropdownOptions as $optIndex => $option)
                        <div class="text-sm text-gray-700">
                            <span class="font-medium">{{ chr(65 + $optIndex) }}.</span> {{ $option }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<style>
/* Student diagram styles */
.student-label-marker {
    z-index: 10;
}

.marker-circle {
    width: 24px;
    height: 24px;
    background: #1e40af;
    color: white;
    border: 2px solid white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.answer-row {
    transition: background-color 0.2s ease;
}

.answer-row:hover {
    background-color: #e5e7eb;
}

/* Highlight corresponding marker on input focus */
.answer-row:has(input:focus),
.answer-row:has(select:focus) {
    background-color: #dbeafe;
    box-shadow: 0 0 0 2px #3b82f6;
}

/* Mobile responsive */
@media (max-width: 640px) {
    .diagram-wrapper img {
        max-height: 300px !important;
    }
    
    .marker-circle {
        width: 20px;
        height: 20px;
        font-size: 10px;
    }
    
    .answer-section {
        margin-top: 1rem;
    }
}
</style>

<script>
// Interactive highlighting when focusing on answer inputs
document.addEventListener('DOMContentLoaded', function() {
    const answerInputs = document.querySelectorAll('[data-label-index]');
    
    answerInputs.forEach(input => {
        input.addEventListener('focus', function() {
            const labelIndex = this.dataset.labelIndex;
            // Highlight corresponding marker
            const markers = document.querySelectorAll('.student-label-marker');
            markers.forEach((marker, index) => {
                if (index == labelIndex) {
                    marker.querySelector('.marker-circle').style.backgroundColor = '#f59e0b';
                    marker.querySelector('.marker-circle').style.transform = 'scale(1.2)';
                } else {
                    marker.querySelector('.marker-circle').style.backgroundColor = '#1e40af';
                    marker.querySelector('.marker-circle').style.transform = 'scale(1)';
                }
            });
        });
        
        input.addEventListener('blur', function() {
            // Reset all markers
            const markers = document.querySelectorAll('.marker-circle');
            markers.forEach(marker => {
                marker.style.backgroundColor = '#1e40af';
                marker.style.transform = 'scale(1)';
            });
        });
    });
});
</script>
