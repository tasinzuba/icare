<x-layout>
    <x-slot:title>{{ $testSet->title }} - Test Set Details</x-slot>
    
    <!-- Header -->
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">{{ $testSet->title }}</h1>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ ucfirst($testSet->section->name) }} Section
                            @if($testSet->active)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        @if($testSet->section->name === 'listening')
                            <a href="{{ route('admin.test-sets.part-audios', $testSet) }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                </svg>
                                Part Audios
                            </a>
                        @endif
                        
                        <a href="{{ route('admin.questions.create', ['test_set' => $testSet->id]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Question
                        </a>
                        
                        <a href="{{ route('admin.test-sets.preview', $testSet) }}" target="_blank"
                           class="inline-flex items-center px-4 py-2 border border-amber-300 text-sm font-medium rounded-lg text-amber-700 bg-amber-50 hover:bg-amber-100">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Preview
                        </a>

                        <a href="{{ route('admin.test-sets.edit', $testSet) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </a>
                        
                        <a href="{{ route('admin.test-sets.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-indigo-100">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Questions</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ $testSet->questions()->where('question_type', '!=', 'passage')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-blue-100">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Multiple Choice</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ $testSet->questions->where('question_type', 'multiple_choice')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-green-100">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">With Options</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ $testSet->questions()->has('options')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-purple-100">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p class="text-lg font-semibold {{ $testSet->active ? 'text-green-600' : 'text-red-600' }}">
                            {{ $testSet->active ? 'Active' : 'Inactive' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Part Audio Status for Listening -->
        @if($testSet->section->name === 'listening')
            <div class="mb-8 bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Part Audio Status</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @for($i = 1; $i <= 4; $i++)
                            <div class="text-center bg-gray-50 rounded-lg p-4 border">
                                <div class="text-sm font-medium text-gray-600 mb-2">Part {{ $i }}</div>
                                @if($testSet->hasPartAudio($i))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 0016 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Uploaded
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Not uploaded
                                    </span>
                                @endif
                                
                                @php
                                    $partQuestionCount = $testSet->questions()->where('part_number', $i)->count();
                                @endphp
                                <div class="text-xs text-gray-500 mt-2">
                                    {{ $partQuestionCount }} {{ Str::plural('question', $partQuestionCount) }}
                                </div>
                            </div>
                        @endfor
                    </div>
                    <div class="mt-4 text-right">
                        <a href="{{ route('admin.test-sets.part-audios', $testSet) }}" 
                           class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            Manage Part Audios →
                        </a>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Questions Section -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">
                        Questions ({{ $testSet->questions()->where('question_type', '!=', 'passage')->count() }})
                    </h3>
                </div>
            </div>
            
            <div class="p-6">
                @if($testSet->section->name === 'reading')
                    @php
                        $passage = $testSet->questions()->where('question_type', 'passage')->first();
                        $questions = $testSet->questions()->where('question_type', '!=', 'passage')->orderBy('order_number')->get();
                    @endphp
                    
                    <!-- Reading Passage Status -->
                    @if($passage)
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-green-800">Reading Passage Added</span>
                                </div>
                                <a href="{{ route('admin.questions.edit', $passage) }}" 
                                   class="text-sm text-green-600 hover:text-green-800">
                                    Edit Passage
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-yellow-800">No Reading Passage</span>
                                </div>
                                <a href="{{ route('admin.questions.create', ['test_set' => $testSet->id]) }}" 
                                   class="text-sm text-yellow-600 hover:text-yellow-800">
                                    Add Passage
                                </a>
                            </div>
                        </div>
                    @endif
                @endif
                
                @if($testSet->questions->count() > 0)
                    <div class="space-y-4">
                        @php
                            $displayQuestions = $testSet->section->name === 'reading' 
                                ? $testSet->questions()->where('question_type', '!=', 'passage')->orderBy('order_number')->get()
                                : $testSet->questions->sortBy('order_number');
                        @endphp
                        
                        @foreach ($displayQuestions as $question)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border hover:border-gray-300 transition-colors">
                                <div class="flex items-center space-x-4 flex-1">
                                    <div class="flex items-center justify-center w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg text-sm font-semibold">
                                        {{ $question->question_range }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ Str::limit(strip_tags($question->content), 80) }}
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                {{ $question->question_type === 'dropdown_selection' ? 'Matching Letters' : ucfirst(str_replace('_', ' ', $question->question_type)) }}
                                            </span>
                                            @if($question->question_type === 'matching_headings' && $question->isMasterMatchingHeading())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">
                                                    Master ({{ $question->getActualQuestionCount() }} questions)
                                                </span>
                                            @elseif($question->question_type === 'drag_drop')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">
                                                    {{ $question->countBlanks() }} drag zones
                                                </span>
                                            @elseif($question->countBlanks() > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                                                    {{ $question->countBlanks() }} blanks
                                                </span>
                                            @endif
                                            @if($question->options->count() > 0)
                                                <span class="text-xs text-gray-500">
                                                    {{ $question->options->count() }} options
                                                </span>
                                            @endif
                                            @if($testSet->section->name === 'listening')
                                                @if($question->use_part_audio)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">
                                                        Part {{ $question->part_number }} Audio
                                                    </span>
                                                @elseif($question->media_path)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                                        Custom Audio
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
                                                        No Audio
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2 ml-4">
                                    <a href="{{ route('admin.questions.show', $question) }}" 
                                       class="p-2 text-gray-400 hover:text-gray-600" 
                                       title="View">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.questions.edit', $question) }}" 
                                       class="p-2 text-gray-400 hover:text-indigo-600" 
                                       title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.questions.destroy', $question) }}" 
                                          method="POST" 
                                          class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this question?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 text-gray-400 hover:text-red-600" 
                                                title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No questions found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first question.</p>
                        <div class="mt-6">
                            <a href="{{ route('admin.questions.create', ['test_set' => $testSet->id]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Question
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>