@if(isset($selectedTestSet))
    <!-- Test Set Header -->
    <div class="bg-white shadow rounded-lg mb-4">
        <div class="px-4 py-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $selectedTestSet->title }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ ucfirst($selectedTestSet->section->name) }} Section • 
                        {{ $questions->count() }} questions
                    </p>
                </div>
                <a href="{{ route('admin.test-sets.show', $selectedTestSet) }}" 
                   class="mt-2 sm:mt-0 text-sm text-indigo-600 hover:text-indigo-900">
                    View Test Set →
                </a>
            </div>
        </div>
    </div>

    <!-- Questions List -->
    @if($questions->count() > 0)
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <ul class="divide-y divide-gray-200">
                @foreach($questions->sortBy(['part_number', 'order_number']) as $question)
                    <li class="hover:bg-gray-50">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                                        <span class="text-indigo-700 font-bold text-lg">{{ $question->order_number }}</span>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            Part {{ $question->part_number }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $question->question_type === 'dropdown_selection' ? 'Matching Letters' : ucwords(str_replace('_', ' ', $question->question_type)) }}
                                        </span>
                                        @if($question->media_path)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Media
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-900 line-clamp-2">
                                        {{ Str::limit(strip_tags($question->content), 150) }}
                                    </p>
                                </div>
                                <div class="ml-4 flex items-center space-x-2">
                                    <a href="{{ route('admin.questions.show', $question) }}" 
                                       class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.questions.edit', $question) }}" 
                                       class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.questions.destroy', $question) }}" 
                                          method="POST" 
                                          class="inline" 
                                          onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No questions found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new question for this test set.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.questions.create', ['test_set' => $selectedTestSet->id]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Question
                    </a>
                </div>
            </div>
        </div>
    @endif
@endif