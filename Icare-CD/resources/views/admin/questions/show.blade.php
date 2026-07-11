<x-admin-layout>
    <x-slot:title>Question Details - {{ $question->testSet->title }}</x-slot>
    
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.questions.index', ['test_set' => $question->test_set_id]) }}" 
                           class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-arrow-left text-lg"></i>
                        </a>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Question Details</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                <span class="font-medium">{{ $question->testSet->title }}</span> • 
                                <span class="capitalize">{{ $question->testSet->section->name }}</span> Section
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('admin.questions.edit', $question) }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Question
                        </a>
                        <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" class="inline" 
                              onsubmit="return confirm('Are you sure you want to delete this question? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                                <i class="fas fa-trash-alt mr-2"></i>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Question Content Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-question-circle text-indigo-500 mr-2"></i>
                            Question Content
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($question->instructions)
                            <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    {{ $question->instructions }}
                                </p>
                            </div>
                        @endif

                        <div class="prose max-w-none text-gray-700">
                            {!! $question->content !!}
                        </div>
                        
                        @if($question->media_path)
                            <div class="mt-6">
                                @if(in_array(pathinfo($question->media_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                    <a href="{{ $question->media_url }}" target="_blank" rel="noopener" class="inline-block rounded-lg overflow-hidden border border-gray-200 hover:shadow-md transition">
                                        <img src="{{ $question->media_url }}"
                                             alt="Question Media"
                                             style="max-width: 320px; width: 100%; height: auto;">
                                    </a>
                                    <p class="text-xs text-gray-400 mt-1.5">Click to open full size</p>
                                @else
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <p class="text-sm text-gray-600 mb-2">
                                            <i class="fas fa-volume-up mr-2"></i>Audio File
                                        </p>
                                        <audio controls class="w-full">
                                            <source src="{{ $question->media_url }}" type="audio/mpeg">
                                            Your browser does not support the audio element.
                                        </audio>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Answer Section -->
                @if($question->options->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                Answer Options
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                @foreach($question->options as $index => $option)
                                    <div class="flex items-start p-4 rounded-lg {{ $option->is_correct ? 'bg-green-50 border-2 border-green-300' : 'bg-gray-50 border border-gray-200' }}">
                                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full {{ $option->is_correct ? 'bg-green-600 text-white' : 'bg-gray-300 text-gray-700' }} text-sm font-bold">
                                            {{ chr(65 + $index) }}
                                        </span>
                                        <div class="ml-3 flex-1">
                                            <span class="text-gray-700 {{ $option->is_correct ? 'font-medium' : '' }}">
                                                {{ $option->content }}
                                            </span>
                                            @if($option->is_correct)
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Correct Answer
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Fill in the Blanks -->
                @if($question->blanks->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-purple-50">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-fill-drip text-purple-600 mr-2"></i>
                                Fill in the Blank Answers
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            @foreach($question->blanks as $blank)
                                <div class="flex items-start space-x-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <span class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-purple-600 text-white font-bold">
                                        {{ $blank->blank_number }}
                                    </span>
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">
                                            Primary Answer: 
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800 font-medium">
                                                {{ $blank->correct_answer }}
                                            </span>
                                        </div>
                                        @if($blank->alternate_answers && count($blank->alternate_answers) > 0)
                                            <div class="mt-2">
                                                <span class="text-sm text-gray-600">Alternative Answers:</span>
                                                <div class="mt-1 flex flex-wrap gap-2">
                                                    @foreach($blank->alternate_answers as $alt)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $alt }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            
                            <!-- Preview with answers -->
                            <div class="mt-6 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">
                                    <i class="fas fa-eye mr-2 text-indigo-600"></i>
                                    Preview with Answers
                                </h4>
                                <div class="text-gray-700">
                                    @php
                                        $previewContent = $question->content;
                                        foreach($question->blanks as $blank) {
                                            $replacement = '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-300">' . $blank->correct_answer . '</span>';
                                            $previewContent = str_replace("[____{$blank->blank_number}____]", $replacement, $previewContent);
                                        }
                                    @endphp
                                    {!! $previewContent !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Matching Type Questions -->
                @if($question->matching_pairs)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-orange-50">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-random text-orange-600 mr-2"></i>
                                Matching Pairs
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-semibold text-gray-700 mb-3">Items</h4>
                                    <div class="space-y-2">
                                        @foreach($question->matching_pairs as $index => $pair)
                                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 flex items-center">
                                                <span class="font-medium text-gray-600 mr-2">{{ $index + 1 }}.</span>
                                                {{ $pair['left'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-700 mb-3">Matches</h4>
                                    <div class="space-y-2">
                                        @foreach($question->matching_pairs as $index => $pair)
                                            <div class="p-3 bg-green-50 rounded-lg border border-green-200 flex items-center">
                                                <span class="font-medium text-green-700 mr-2">{{ chr(65 + $index) }}.</span>
                                                {{ $pair['right'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Audio Transcript -->
                @if($question->audio_transcript)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-file-audio text-blue-600 mr-2"></i>
                                Audio Transcript
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="prose max-w-none text-gray-700 bg-gray-50 rounded-lg p-4">
                                {{ $question->audio_transcript }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Question Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-6">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-info-circle text-gray-600 mr-2"></i>
                            Question Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Question Type</p>
                            <p class="mt-1">
                                @php
                                    $typeColors = [
                                        'multiple_choice' => 'blue',
                                        'true_false' => 'purple',
                                        'fill_blank' => 'indigo',
                                        'matching' => 'orange',
                                        'form' => 'green',
                                        'diagram' => 'pink',
                                    ];
                                    $typeLabels = [
                                        'dropdown_selection' => 'Matching Letters',
                                    ];
                                    $color = $typeColors[$question->question_type] ?? 'gray';
                                    $label = $typeLabels[$question->question_type] ?? str_replace('_', ' ', ucfirst($question->question_type));
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                    <i class="fas fa-tag mr-1.5"></i>
                                    {{ $label }}
                                </span>
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Question #</p>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $question->order_number }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Part</p>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $question->part_number }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Marks</p>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $question->marks }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Section</p>
                                <p class="mt-1 text-lg font-semibold text-gray-900 capitalize">{{ $question->testSet->section->name }}</p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-500">Created</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $question->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $question->created_at->diffForHumans() }}</p>
                        </div>

                        @if($question->updated_at != $question->created_at)
                            <div>
                                <p class="text-sm text-gray-500">Last Updated</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $question->updated_at->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-500">{{ $question->updated_at->diffForHumans() }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('admin.questions.create', ['test_set' => $question->test_set_id]) }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Add Another Question
                        </a>
                        <a href="{{ route('admin.test-sets.show', $question->testSet) }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="fas fa-layer-group mr-2"></i>
                            View Test Set
                        </a>
                        <button onclick="window.print()" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="fas fa-print mr-2"></i>
                            Print Question
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            .prose {
                max-width: none !important;
            }
        }
    </style>
    @endpush
</x-admin-layout>
