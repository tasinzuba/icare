<x-layout>
    <x-slot:title>Edit Question</x-slot>
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-gray-600 to-gray-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold">Edit Question #{{ $question->order_number }}</h1>
                        <p class="text-gray-100 text-sm mt-1">{{ $question->testSet->title }}</p>
                    </div>
                    <a href="{{ route('admin.test-sets.show', $question->testSet) }}" 
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
            
            <form action="{{ route('admin.questions.update', $question) }}" method="POST" enctype="multipart/form-data" id="questionForm">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <!-- Question Content -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Question Content</h3>
                        </div>
                        
                        <div class="p-6">
                            <!-- Instructions -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Instructions</label>
                                <textarea name="instructions" class="tinymce-editor-simple">{{ old('instructions', $question->instructions) }}</textarea>
                            </div>
                            
                            <!-- Question Content -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Question <span class="text-red-500">*</span>
                                </label>
                                <textarea id="content" name="content" class="tinymce-editor" required>{{ old('content', $question->content) }}</textarea>
                            </div>
                            
                            <!-- Settings -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                    <input type="text" value="{{ $question->question_type }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                                    <input type="hidden" name="question_type" value="{{ $question->question_type }}">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Order Number</label>
                                    <input type="number" name="order_number" value="{{ old('order_number', $question->order_number) }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Part/Task</label>
                                    <input type="number" name="part_number" value="{{ old('part_number', $question->part_number) }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Marks</label>
                                    <input type="number" name="marks" value="{{ old('marks', $question->marks) }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Options if applicable -->
                    @if($question->options->count() > 0 || in_array($question->question_type, ['multiple_choice', 'true_false', 'yes_no']))
                        <div class="bg-white rounded-lg shadow-sm">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Answer Options</h3>
                            </div>
                            <div class="p-6">
                                <div id="options-container" class="space-y-3">
                                    @foreach($question->options as $index => $option)
                                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                            <input type="radio" name="correct_option" value="{{ $index }}" 
                                                   class="h-4 w-4 text-blue-600" {{ $option->is_correct ? 'checked' : '' }}>
                                            <span class="font-medium text-gray-700">{{ chr(65 + $index) }}.</span>
                                            <input type="text" name="options[{{ $index }}][content]" value="{{ $option->content }}" 
                                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Media -->
                    @include('admin.questions.partials.media-upload')
                    
                    <!-- Actions -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-6">
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button type="submit" class="flex-1 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700">
                                    Update Question
                                </button>
                                <a href="{{ route('admin.test-sets.show', $question->testSet) }}" 
                                   class="flex-1 py-3 border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 text-center">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    @push('scripts')
    <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key', 'no-api-key') }}/tinymce/6/tinymce.min.js"></script>
    <script src="{{ asset('js/admin/question-common.js') }}"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize TinyMCE for instructions (simple editor)
        tinymce.init({
            selector: '.tinymce-editor-simple',
            height: 150,
            menubar: false,
            plugins: [
                'lists', 'link', 'charmap', 'code'
            ],
            toolbar: 'bold italic underline | fontsize | bullist numlist | alignleft aligncenter alignright | link | removeformat code',
            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt',
            content_css: '//www.tiny.cloud/css/codepen.min.css',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });
        
        // Initialize TinyMCE for main content
        tinymce.init({
            selector: '.tinymce-editor',
            height: 350,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                'preview', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | formatselect | fontsize | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | removeformat code',
            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt',
            content_css: '//www.tiny.cloud/css/codepen.min.css',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });
    });
    </script>
    @endpush
</x-layout>