<x-layout>
    <x-slot:title>Edit Writing Question</x-slot>
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold">✍️ Edit Writing Question #{{ $question->order_number }}</h1>
                        <p class="text-indigo-100 text-sm mt-1">{{ $testSet->title }}</p>
                    </div>
                    <a href="{{ route('admin.test-sets.show', $testSet) }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur border border-white/20 text-white text-sm font-medium rounded-md hover:bg-white/20 transition-all">
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
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Question Info Card -->
            <div class="bg-white rounded-lg shadow-sm mb-6 border-l-4 border-indigo-500">
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Editing Question</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                <span class="font-medium text-indigo-600">{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</span>
                                <span class="mx-2">•</span>
                                <span>Task {{ $question->part_number }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-indigo-600">
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
                
                <div class="space-y-6">
                    <!-- Task Details -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Task Details</h3>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-6">
                                    <!-- Question Type (Read-only) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Task Type</label>
                                        <input type="text" value="{{ $question->question_type }}" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                                        <input type="hidden" name="question_type" value="{{ $question->question_type }}">
                                    </div>

                                    <!-- Question Title (Optional) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Question Title <span class="text-gray-400 text-xs">(Optional)</span>
                                        </label>
                                        <input type="text" name="title" value="{{ old('title', $question->title) }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                               maxlength="255"
                                               placeholder="e.g., Internet Usage Trends (1999-2009)">
                                        <p class="text-xs text-gray-500 mt-1">Used for display in question lists. Leave blank to auto-generate from type.</p>
                                    </div>

                                    <!-- Question Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Number <span class="text-red-500">*</span></label>
                                        <input type="number" name="order_number" value="{{ old('order_number', $question->order_number) }}" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" min="1" required>
                                    </div>
                                    
                                    <!-- Task (Part) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Task <span class="text-red-500">*</span></label>
                                        <select name="part_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                                            <option value="1" {{ $question->part_number == 1 ? 'selected' : '' }}>Task 1</option>
                                            <option value="2" {{ $question->part_number == 2 ? 'selected' : '' }}>Task 2</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Word Limit -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Word Limit <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" name="word_limit" value="{{ old('word_limit', $question->word_limit) }}" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" 
                                               min="50" max="500" required>
                                        <p class="text-xs text-gray-500 mt-1">Task 1: 150 words, Task 2: 250 words</p>
                                    </div>
                                    
                                    <!-- Time Limit -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Time Limit (minutes) <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" name="time_limit" value="{{ old('time_limit', $question->time_limit) }}" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" 
                                               min="1" max="60" required>
                                        <p class="text-xs text-gray-500 mt-1">Task 1: 20 minutes, Task 2: 40 minutes</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-6">
                                    <!-- Task Instructions -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Task Instructions <span class="text-red-500">*</span>
                                        </label>
                                        <textarea id="content" name="content" rows="8" 
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                                  placeholder="e.g., The chart below shows the percentage of households in owned and rented accommodation..."
                                                  required>{{ old('content', $question->content) }}</textarea>
                                    </div>
                                    
                                    <!-- Sample Answer Points -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Sample Answer Points / Band Descriptors
                                        </label>
                                        <textarea name="instructions" rows="4" 
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                                  placeholder="Key points students should cover...">{{ old('instructions', $question->instructions) }}</textarea>
                                    </div>
                                    
                                    <!-- Marks -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Marks</label>
                                        <input type="number" name="marks" value="{{ old('marks', $question->marks) }}" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" min="0" max="40">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Upload for Task 1 -->
                    @if(str_starts_with($question->question_type, 'task1_'))
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Visual Data (Task 1) <span class="text-red-500">*</span></h3>
                        </div>
                        
                        <div class="p-6">
                            @if($question->media_path)
                            <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                                <p class="text-sm text-gray-700 mb-2">Current image:</p>
                                <img src="{{ $question->media_url }}" alt="Task 1 Visual" class="max-h-64 rounded shadow">
                                <div class="mt-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="remove_media" value="1" class="mr-2">
                                        <span class="text-sm text-red-600">Remove current image</span>
                                    </label>
                                </div>
                            </div>
                            @endif
                            
                            <div class="border-2 border-dashed border-gray-300 rounded-md p-8 text-center hover:border-gray-400 transition-colors cursor-pointer"
                                 id="drop-zone" onclick="document.getElementById('media').click()">
                                <input type="file" id="media" name="media" class="hidden" accept="image/*">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">
                                    <span class="font-medium text-blue-600 hover:text-blue-500">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Upload graph, chart, diagram, or map image (PNG, JPG, GIF - max 10MB)
                                </p>
                            </div>
                            <div id="media-preview" class="mt-4 hidden">
                                <!-- Preview will be shown here -->
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Model Answer & Evaluation Criteria -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200 bg-indigo-50">
                            <h3 class="text-lg font-medium text-gray-900">Model Answer & Evaluation Criteria</h3>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <!-- Band 7-9 Sample -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Band 7-9 Sample Answer (Optional)
                                </label>
                                <textarea name="model_answer_high" rows="6" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                          placeholder="Provide a high-scoring sample answer...">{{ old('model_answer_high', $question->section_specific_data['model_answer_high'] ?? '') }}</textarea>
                            </div>
                            
                            <!-- Band 5-6 Sample -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Band 5-6 Sample Answer (Optional)
                                </label>
                                <textarea name="model_answer_mid" rows="6" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                          placeholder="Provide a mid-range sample answer...">{{ old('model_answer_mid', $question->section_specific_data['model_answer_mid'] ?? '') }}</textarea>
                            </div>
                            
                            <!-- Key Vocabulary -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Key Vocabulary & Phrases
                                </label>
                                <textarea name="key_vocabulary" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                          placeholder="List important vocabulary and phrases students should use...">{{ old('key_vocabulary', $question->section_specific_data['key_vocabulary'] ?? '') }}</textarea>
                            </div>
                            
                            <!-- Common Errors -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Common Errors to Avoid
                                </label>
                                <textarea name="common_errors" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                          placeholder="List common mistakes students make...">{{ old('common_errors', $question->section_specific_data['common_errors'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-6">
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button type="submit" class="flex-1 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                                    Update Question
                                </button>
                                <button type="button" onclick="previewQuestion()" class="flex-1 py-3 border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 transition-colors">
                                    Preview
                                </button>
                                <a href="{{ route('admin.test-sets.show', $testSet) }}" 
                                   class="flex-1 py-3 border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 text-center transition-colors">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('admin.questions.partials.modals')
    
    @push('scripts')
    <script src="{{ asset('js/admin/question-common.js') }}"></script>
    <script src="{{ asset('js/admin/question-writing.js') }}"></script>
    <script>
        // Handle section specific data for writing
        document.getElementById('questionForm').addEventListener('submit', function(e) {
            // Collect writing-specific data
            const modelAnswerHigh = document.querySelector('[name="model_answer_high"]');
            const modelAnswerMid = document.querySelector('[name="model_answer_mid"]');
            const keyVocabulary = document.querySelector('[name="key_vocabulary"]');
            const commonErrors = document.querySelector('[name="common_errors"]');
            
            // Create hidden input for section_specific_data
            const sectionData = {
                model_answer_high: modelAnswerHigh ? modelAnswerHigh.value : '',
                model_answer_mid: modelAnswerMid ? modelAnswerMid.value : '',
                key_vocabulary: keyVocabulary ? keyVocabulary.value : '',
                common_errors: commonErrors ? commonErrors.value : ''
            };
            
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'section_specific_data';
            hiddenInput.value = JSON.stringify(sectionData);
            this.appendChild(hiddenInput);
        });
    </script>
    @endpush
</x-layout>