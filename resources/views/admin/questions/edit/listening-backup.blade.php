<x-layout>
    <x-slot:title>Edit Question - Listening</x-slot>
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="py-4 sm:py-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-semibold">Edit Listening Question</h1>
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
            
            <form action="{{ route('admin.questions.update', $question) }}" method="POST" enctype="multipart/form-data" id="questionForm">
                @csrf
                @method('PUT')
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
                                        <textarea id="instructions" name="instructions" rows="3" 
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 text-sm"
                                                  placeholder="e.g., Questions 1-5: Complete the form below. Write NO MORE THAN TWO WORDS AND/OR A NUMBER for each answer.">{{ old('instructions', $question->instructions) }}</textarea>
                                    </div>
                                    
                                    <!-- Question Content -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Question <span class="text-red-500">*</span>
                                        </label>
                                        <div class="mb-3 flex flex-wrap gap-2">
                                            <button type="button" onclick="insertBlank()" class="px-3 py-1 bg-purple-600 text-white text-xs font-medium rounded hover:bg-purple-700 transition-colors">
                                                Insert Blank
                                            </button>
                                        </div>
                                        <div class="border border-gray-300 rounded-md overflow-hidden" style="height: 350px;">
                                            <textarea id="content" name="content" class="tinymce">{{ old('content', $question->content) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="space-y-4 sm:space-y-6">
                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Question Type -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                                            <select id="question_type" name="question_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                                                <option value="">Select type...</option>
                                                @foreach([
                                                    'multiple_choice' => 'Multiple Choice',
                                                    'form_completion' => 'Form Completion',
                                                    'note_completion' => 'Note Completion',
                                                    'sentence_completion' => 'Sentence Completion',
                                                    'short_answer' => 'Short Answer',
                                                    'matching' => 'Matching',
                                                    'plan_map_diagram' => 'Plan/Map/Diagram Labeling'
                                                ] as $key => $type)
                                                    <option value="{{ $key }}" {{ old('question_type', $question->question_type) == $key ? 'selected' : '' }}>
                                                        {{ $type }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <!-- Question Number -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Number <span class="text-red-500">*</span></label>
                                            <input type="number" name="order_number" value="{{ old('order_number', $question->order_number) }}" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500" min="0" required>
                                        </div>
                                        
                                        <!-- Part Selection -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Part <span class="text-red-500">*</span></label>
                                            <select name="part_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                                                @for($i = 1; $i <= 4; $i++)
                                                    <option value="{{ $i }}" {{ old('part_number', $question->part_number) == $i ? 'selected' : '' }}>
                                                        Part {{ $i }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                        
                                        <!-- Marks -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Marks</label>
                                            <input type="number" name="marks" value="{{ old('marks', $question->marks) }}" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500" 
                                                   min="0" max="40">
                                        </div>
                                    </div>
                                    
                                    <!-- Audio Transcript -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Audio Transcript
                                        </label>
                                        <textarea name="audio_transcript" rows="4" 
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 text-sm"
                                                  placeholder="Enter the transcript of the audio...">{{ old('audio_transcript', $question->audio_transcript) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Regular Options (for non-special types) --}}
                    <div id="options-card" class="bg-white rounded-lg shadow-sm {{ in_array($question->question_type, ['matching', 'form_completion', 'plan_map_diagram']) ? 'hidden' : '' }}">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Answer Options</h3>
                            <button type="button" onclick="showBulkOptions()" class="text-sm text-blue-600 hover:text-blue-700">
                                Add Bulk Options
                            </button>
                        </div>
                        
                        <div class="p-6">
                            <div id="options-container" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @if($question->options->count() > 0)
                                    @foreach($question->options as $index => $option)
                                        <div class="option-item flex items-center gap-3">
                                            <input type="radio" name="correct_option" value="{{ $index }}" 
                                                   {{ $option->is_correct ? 'checked' : '' }}
                                                   class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                            <input type="text" name="options[{{ $index }}][content]" 
                                                   value="{{ $option->content }}"
                                                   placeholder="Option {{ chr(65 + $index) }}"
                                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 text-sm">
                                            <button type="button" onclick="removeOption(this)" 
                                                    class="text-red-500 hover:text-red-700 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                @endif
                    
                    <!-- Blank Answers Display (for fill-in-the-blank questions) -->
                    @if(in_array($question->question_type, ['sentence_completion', 'note_completion', 'form_completion']))
                    <div class="bg-white rounded-lg shadow-sm" id="blank-answers-section">
                        <div class="px-6 py-4 border-b border-gray-200 bg-purple-50">
                            <h3 class="text-lg font-medium text-gray-900">
                                <i class="fas fa-fill-drip mr-2"></i>Fill in the Blank Answers
                            </h3>
                        </div>
                        
                        <div class="p-6">
                            <div id="blank-answers-container" class="space-y-3">
                                @php
                                    // Extract blanks from content
                                    preg_match_all('/\[____(\d+)____\]/', $question->content, $matches);
                                    $blankNumbers = array_unique($matches[1]);
                                    sort($blankNumbers);
                                @endphp
                                
                                @if(count($blankNumbers) > 0)
                                    @foreach($blankNumbers as $index => $blankNum)
                                        @php
                                            $blank = $question->blanks()->where('blank_number', $blankNum)->first();
                                            $answer = '';
                                            
                                            if ($blank) {
                                                $answer = $blank->correct_answer;
                                                if ($blank->alternate_answers) {
                                                    $answer .= '|' . implode('|', $blank->alternate_answers);
                                                }
                                            }
                                        @endphp
                                        
                                        <div class="flex items-center gap-3 bg-white p-3 rounded border border-gray-200">
                                            <label class="text-sm font-medium text-gray-700 w-24">
                                                Blank {{ $blankNum }}:
                                            </label>
                                            <input type="text" 
                                                   name="blank_answers[]" 
                                                   value="{{ $answer }}"
                                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                                   placeholder="Answer (use | for alternatives: answer1|answer2)"
                                                   required>
                                            <span class="text-xs text-gray-500">[____{{ $blankNum }}____]</span>
                                            
                                            @if($blank)
                                                <span class="text-green-600" title="Saved in database">
                                                    <i class="fas fa-check-circle"></i>
                                                </span>
                                            @else
                                                <span class="text-red-600" title="Not saved">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                    
                                    <div class="mt-3 p-3 bg-blue-50 rounded">
                                        <p class="text-sm text-blue-800">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            <strong>Tips:</strong> Use pipe (|) to separate alternative correct answers. 
                                            Example: <code class="bg-white px-1 rounded">color|colour</code>
                                        </p>
                                    </div>
                                @else
                                    <p class="text-gray-500 text-sm">
                                        No blanks found. Use [____1____] format in content to create blanks.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                            </div>
                            
                            <button type="button" id="add-option-btn" 
                                    class="mt-4 w-full md:w-auto px-4 py-2 border-2 border-dashed border-gray-300 text-gray-500 rounded-md hover:border-gray-400 hover:text-gray-600 transition-all">
                                + Add Option
                            </button>
                        </div>
                    </div>
                    
                    {{-- Type-specific panels --}}
                    <div id="type-specific-panels">
                        {{-- Matching Questions Panel --}}
                        <div id="matching-panel" class="type-specific-panel bg-white rounded-lg shadow-sm overflow-hidden" 
                             style="{{ $question->question_type === 'matching' ? '' : 'display: none;' }}">
                            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-yellow-50">
                                <h3 class="text-base sm:text-lg font-medium text-gray-900">
                                    Matching Pairs Setup
                                </h3>
                            </div>
                            
                            <div class="p-4 sm:p-6">
                                <p class="text-sm text-gray-600 mb-4">
                                    Create matching pairs. Students will need to match items from the left with options on the right.
                                </p>
                                
                                <div id="matching-pairs-container">
                                    @if($question->matching_pairs)
                                        @foreach($question->matching_pairs as $index => $pair)
                                            <div class="matching-pair flex gap-3 mb-3">
                                                <div class="flex-1">
                                                    <input type="text" 
                                                           name="matching_pairs[{{ $index }}][left]" 
                                                           value="{{ $pair['left'] }}"
                                                           placeholder="Question/Item {{ $index + 1 }}"
                                                           class="w-full px-3 py-2 border rounded-md text-sm"
                                                           required>
                                                </div>
                                                <div class="flex items-center text-gray-500">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <input type="text" 
                                                           name="matching_pairs[{{ $index }}][right]" 
                                                           value="{{ $pair['right'] }}"
                                                           placeholder="Match {{ chr(65 + $index) }}"
                                                           class="w-full px-3 py-2 border rounded-md text-sm"
                                                           required>
                                                </div>
                                                <button type="button" onclick="QuestionTypeHandlers.removeMatchingPair(this)" 
                                                        class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                                                    Remove
                                                </button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                
                                <button type="button" onclick="QuestionTypeHandlers.addMatchingPair()" 
                                        class="mt-3 px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-sm">
                                    + Add Matching Pair
                                </button>
                            </div>
                        </div>
                        
                        {{-- Form Completion Panel --}}
                        <div id="form-completion-panel" class="type-specific-panel bg-white rounded-lg shadow-sm overflow-hidden" 
                             style="{{ $question->question_type === 'form_completion' ? '' : 'display: none;' }}">
                            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-green-50">
                                <h3 class="text-base sm:text-lg font-medium text-gray-900">
                                    Form Structure Setup
                                </h3>
                            </div>
                            
                            <div class="p-4 sm:p-6">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Form Title</label>
                                    <input type="text" name="form_structure[title]" 
                                           value="{{ old('form_structure.title', $question->form_structure['title'] ?? '') }}"
                                           placeholder="e.g., Student Registration Form"
                                           class="w-full px-3 py-2 border rounded-md text-sm">
                                </div>
                                
                                <p class="text-sm text-gray-600 mb-4">
                                    Add form fields that students need to complete:
                                </p>
                                
                                <div id="form-fields-container">
                                    @if($question->form_structure && isset($question->form_structure['fields']))
                                        @foreach($question->form_structure['fields'] as $index => $field)
                                            <div class="form-field flex gap-3 mb-3">
                                                <div class="flex-1">
                                                    <input type="text" 
                                                           name="form_structure[fields][{{ $index }}][label]" 
                                                           value="{{ $field['label'] }}"
                                                           placeholder="Field Label (e.g., Name, Email)"
                                                           class="w-full px-3 py-2 border rounded-md text-sm"
                                                           required>
                                                </div>
                                                <div class="flex-1">
                                                    <input type="text" 
                                                           name="form_structure[fields][{{ $index }}][answer]" 
                                                           value="{{ $field['answer'] }}"
                                                           placeholder="Correct Answer"
                                                           class="w-full px-3 py-2 border rounded-md text-sm"
                                                           required>
                                                </div>
                                                <button type="button" onclick="QuestionTypeHandlers.removeFormField(this)" 
                                                        class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                                                    Remove
                                                </button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                
                                <button type="button" onclick="QuestionTypeHandlers.addFormField()" 
                                        class="mt-3 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    + Add Form Field
                                </button>
                            </div>
                        </div>
                        
                        {{-- Diagram Labeling Panel --}}
                        <div id="diagram-panel" class="type-specific-panel bg-white rounded-lg shadow-sm overflow-hidden" 
                             style="{{ $question->question_type === 'plan_map_diagram' ? '' : 'display: none;' }}">
                            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-blue-50">
                                <h3 class="text-base sm:text-lg font-medium text-gray-900">
                                    Plan/Map/Diagram Setup
                                </h3>
                            </div>
                            
                            <div class="p-4 sm:p-6">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload Diagram Image</label>
                                    <input type="file" id="diagram-image" name="diagram_image" 
                                           accept="image/*"
                                           class="w-full px-3 py-2 border rounded-md text-sm">
                                    @if($question->media_path && $question->question_type === 'plan_map_diagram')
                                        <p class="mt-2 text-sm text-gray-600">
                                            Current image: <a href="{{ asset('storage/' . $question->media_path) }}" target="_blank" class="text-blue-600 hover:underline">View</a>
                                        </p>
                                    @endif
                                </div>
                                
                                <div id="diagram-preview" class="mb-4 relative">
                                    @if($question->media_path && $question->question_type === 'plan_map_diagram')
                                        <img src="{{ asset('storage/' . $question->media_path) }}" id="diagram-img" class="max-w-full">
                                        @if($question->diagram_hotspots)
                                            @foreach($question->diagram_hotspots as $hotspot)
                                                <div class="hotspot-marker" style="
                                                    position: absolute;
                                                    left: {{ is_array($hotspot) ? $hotspot['x'] : $hotspot->x }}%;
                                                    top: {{ is_array($hotspot) ? $hotspot['y'] : $hotspot->y }}%;
                                                    width: 30px;
                                                    height: 30px;
                                                    background: #3b82f6;
                                                    color: white;
                                                    border-radius: 50%;
                                                    display: flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    font-weight: bold;
                                                    transform: translate(-50%, -50%);
                                                    cursor: pointer;
                                                ">{{ $hotspot['label'] }}</div>
                                            @endforeach
                                        @endif
                                    @endif
                                </div>
                                
                                <div id="hotspots-container">
                                    @if($question->diagram_hotspots)
                                        @foreach($question->diagram_hotspots as $index => $hotspot)
                                            <div class="hotspot-field flex gap-3 mb-3">
                                                <div class="w-16 text-center">
                                                    <span class="inline-block w-8 h-8 bg-blue-500 text-white rounded-full leading-8 font-bold">
                                                        {{ is_array($hotspot) ? $hotspot['label'] : $hotspot->label }}
                                                    </span>
                                                </div>
                                                <input type="hidden" name="diagram_hotspots[{{ $index }}][x]" value="{{ is_array($hotspot) ? $hotspot['x'] : $hotspot->x }}">
                                                <input type="hidden" name="diagram_hotspots[{{ $index }}][y]" value="{{ is_array($hotspot) ? $hotspot['y'] : $hotspot->y }}">
                                                <input type="hidden" name="diagram_hotspots[{{ $index }}][label]" value="{{ is_array($hotspot) ? $hotspot['label'] : $hotspot->label }}">
                                                <div class="flex-1">
                                                    <input type="text" 
                                                           name="diagram_hotspots[{{ $index }}][answer]" 
                                                           value="{{ is_array($hotspot) ? $hotspot['answer'] : $hotspot->answer }}"
                                                           placeholder="What is at point {{ is_array($hotspot) ? $hotspot['label'] : $hotspot->label }}?"
                                                           class="w-full px-3 py-2 border rounded-md text-sm"
                                                           required>
                                                </div>
                                                <button type="button" onclick="QuestionTypeHandlers.removeHotspot(this, '{{ is_array($hotspot) ? $hotspot['label'] : $hotspot->label }}')" 
                                                        class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                                                    Remove
                                                </button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Audio Upload -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-purple-50">
                            <h3 class="text-base sm:text-lg font-medium text-gray-900">
                                Audio Upload
                            </h3>
                        </div>
                        
                        <div class="p-4 sm:p-6">
                            @if($question->media_path && !in_array($question->question_type, ['plan_map_diagram']))
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Audio:</label>
                                    <audio controls class="w-full">
                                        <source src="{{ asset('storage/' . $question->media_path) }}" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                    <div class="mt-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="remove_media" value="1" class="form-checkbox">
                                            <span class="ml-2 text-sm text-red-600">Remove current audio</span>
                                        </label>
                                    </div>
                                </div>
                            @endif
                            
                            <div id="drop-zone" class="border-2 border-dashed border-purple-300 rounded-lg p-6 sm:p-8 text-center hover:border-purple-400 transition-colors cursor-pointer bg-purple-50/30">
                                <svg class="mx-auto h-10 sm:h-12 w-10 sm:w-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">
                                    <label for="media" class="cursor-pointer text-purple-600 hover:text-purple-700 font-medium">
                                        Click to upload new audio
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
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" class="flex-1 py-2.5 sm:py-3 bg-purple-600 text-white font-medium rounded-md hover:bg-purple-700 transition-colors text-sm sm:text-base">
                                Update Question
                            </button>
                            <a href="{{ route('admin.test-sets.show', $testSet) }}" 
                               class="flex-1 py-2.5 sm:py-3 border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 transition-colors text-sm sm:text-base text-center">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('admin.questions.partials.modals')
    
    @push('styles')
    <style>
        /* Same styles as create view */
        .drag-over {
            border-color: #9333EA !important;
            background-color: #FAF5FF !important;
        }
        
        .blank-placeholder {
            background-color: #E9D5FF;
            padding: 2px 8px;
            margin: 0 4px;
            border-bottom: 2px solid #9333EA;
            border-radius: 2px;
            font-weight: 500;
            color: #581C87;
            cursor: not-allowed;
            user-select: none;
            display: inline-block;
            min-width: 60px;
        }
    </style>
    @endpush
    
    @push('scripts')
    <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key', 'no-api-key') }}/tinymce/6/tinymce.min.js"></script>
    <script src="{{ asset('js/admin/question-common.js') }}"></script>
    <script src="{{ asset('js/admin/question-listening.js') }}"></script>
    <script src="{{ asset('js/admin/question-types.js') }}"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize TinyMCE
        tinymce.init({
            selector: '.tinymce',
            plugins: 'lists link table code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | code',
            height: 350,
            menubar: false,
            branding: false
        });
        
        // Question type change handler
        const questionTypeSelect = document.getElementById('question_type');
        questionTypeSelect.addEventListener('change', function() {
            QuestionTypeHandlers.init(this.value);
            
            // Show/hide regular options
            const optionsCard = document.getElementById('options-card');
            if (['matching', 'form_completion', 'plan_map_diagram'].includes(this.value)) {
                optionsCard.classList.add('hidden');
            } else {
                optionsCard.classList.remove('hidden');
            }
        });
        
        // Initialize based on current type
        if (questionTypeSelect.value) {
            QuestionTypeHandlers.init(questionTypeSelect.value);
        }
        
        // File upload handling
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('media');
        
        dropZone.addEventListener('click', () => fileInput.click());
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drag-over');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect(files[0]);
            }
        });
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });
        
        function handleFileSelect(file) {
            const preview = document.getElementById('media-preview');
            preview.innerHTML = `
                <div class="flex items-center p-3 bg-purple-50 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">${file.name}</p>
                        <p class="text-xs text-gray-500">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                    </div>
                </div>
            `;
            preview.classList.remove('hidden');
        }
    });
    </script>
    @endpush
</x-layout>