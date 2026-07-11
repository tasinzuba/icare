{{-- Simple Diagram Question Component for Students --}}
<div class="diagram-question-container">
    {{-- Question Instructions --}}
    @if($question->instructions)
        <div class="mb-4 p-3 bg-blue-50 rounded-lg">
            <p class="text-sm text-gray-800">{{ $question->instructions }}</p>
        </div>
    @endif
    
    {{-- Diagram Display --}}
    @if($question->media_path)
        <div class="diagram-wrapper mb-6">
            <img src="{{ Storage::url($question->media_path) }}" 
                 alt="Diagram" 
                 class="max-w-full rounded-lg border-2 border-gray-300"
                 style="max-height: 500px;">
        </div>
    @endif
    
    {{-- Answer Options --}}
    @php
        $diagramData = $question->section_specific_data ?? [];
        $dropdownOptions = $diagramData['dropdown_options'] ?? [];
        $startNumber = $diagramData['start_number'] ?? 1;
        
        // If we have diagram_hotspots for backward compatibility
        if (isset($question->diagram_hotspots)) {
            $hotspotData = $question->diagram_hotspots;
            $dropdownOptions = $hotspotData['dropdown_options'] ?? $dropdownOptions;
            $startNumber = $hotspotData['start_number'] ?? $startNumber;
        }
        
        // Determine how many questions based on content
        $questionCount = 0;
        if (preg_match_all('/\b(\d+)\b/', $question->content, $matches)) {
            $numbers = array_map('intval', $matches[1]);
            $questionCount = count($numbers);
        } else {
            // Default to number of options if no numbers found
            $questionCount = count($dropdownOptions);
        }
    @endphp
    
    {{-- Answer Input Section --}}
    <div class="answer-section">
        <h4 class="font-medium text-gray-900 mb-4">Label the diagram. Choose from the options below:</h4>
        
        {{-- Options Reference Box --}}
        @if(count($dropdownOptions) > 0)
            <div class="mb-4 p-4 bg-yellow-50 rounded-lg">
                <h5 class="text-sm font-medium text-gray-800 mb-2">Available Options:</h5>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach($dropdownOptions as $index => $option)
                        <div class="text-sm text-gray-700">
                            <span class="font-medium">{{ chr(65 + $index) }}.</span> {{ $option }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        {{-- Answer Dropdowns --}}
        <div class="space-y-3">
            @for($i = 0; $i < $questionCount; $i++)
                @php
                    $questionNum = $startNumber + $i;
                @endphp
                <div class="answer-row flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    {{-- Question Number --}}
                    <div class="question-number">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full font-bold text-sm">
                            {{ $questionNum }}
                        </span>
                    </div>
                    
                    {{-- Answer Dropdown --}}
                    <div class="flex-1">
                        <select name="answers[{{ $question->id }}][{{ $i }}]" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                data-question-id="{{ $question->id }}"
                                data-question-number="{{ $questionNum }}">
                            <option value="">Choose your answer</option>
                            @foreach($dropdownOptions as $optIndex => $option)
                                <option value="{{ $optIndex }}">
                                    {{ chr(65 + $optIndex) }} - {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>

<style>
/* Simple diagram styles */
.answer-row {
    transition: background-color 0.2s ease;
}

.answer-row:hover {
    background-color: #e5e7eb;
}

.answer-row:has(select:focus) {
    background-color: #dbeafe;
    box-shadow: 0 0 0 2px #3b82f6;
}

/* Mobile responsive */
@media (max-width: 640px) {
    .diagram-wrapper img {
        max-height: 300px !important;
    }
    
    .answer-section {
        margin-top: 1rem;
    }
}
</style>
