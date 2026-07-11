{{-- Human Evaluation Partial for Full Test --}}
<div class="space-y-6">
    <!-- Overall Feedback -->
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
        <h3 class="text-md font-semibold text-gray-900 mb-4">Overall Assessment</h3>

        <!-- Band Score Breakdown -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @if($sectionType === 'writing')
                @php
                    $firstTask = collect($humanEvaluation->task_scores)->first();
                @endphp
                <div class="text-center bg-white rounded-lg p-3 border border-gray-100">
                    <p class="text-xs text-gray-500">Task Achievement</p>
                    <p class="text-lg font-bold text-gray-900">{{ bandScoreRange($firstTask['task_achievement'] ?? 0) }}</p>
                </div>
                <div class="text-center bg-white rounded-lg p-3 border border-gray-100">
                    <p class="text-xs text-gray-500">Coherence/Cohesion</p>
                    <p class="text-lg font-bold text-gray-900">{{ bandScoreRange($firstTask['coherence_cohesion'] ?? 0) }}</p>
                </div>
                <div class="text-center bg-white rounded-lg p-3 border border-gray-100">
                    <p class="text-xs text-gray-500">Lexical Resource</p>
                    <p class="text-lg font-bold text-gray-900">{{ bandScoreRange($firstTask['lexical_resource'] ?? 0) }}</p>
                </div>
                <div class="text-center bg-white rounded-lg p-3 border border-gray-100">
                    <p class="text-xs text-gray-500">Grammar</p>
                    <p class="text-lg font-bold text-gray-900">{{ bandScoreRange($firstTask['grammar'] ?? 0) }}</p>
                </div>
            @else
                @php
                    $firstTask = collect($humanEvaluation->task_scores)->first();
                @endphp
                <div class="text-center bg-white rounded-lg p-3 border border-gray-100">
                    <p class="text-xs text-gray-500">Fluency/Coherence</p>
                    <p class="text-lg font-bold text-gray-900">{{ bandScoreRange($firstTask['fluency_coherence'] ?? 0) }}</p>
                </div>
                <div class="text-center bg-white rounded-lg p-3 border border-gray-100">
                    <p class="text-xs text-gray-500">Lexical Resource</p>
                    <p class="text-lg font-bold text-gray-900">{{ bandScoreRange($firstTask['lexical_resource'] ?? 0) }}</p>
                </div>
                <div class="text-center bg-white rounded-lg p-3 border border-gray-100">
                    <p class="text-xs text-gray-500">Grammar</p>
                    <p class="text-lg font-bold text-gray-900">{{ bandScoreRange($firstTask['grammar'] ?? 0) }}</p>
                </div>
                <div class="text-center bg-white rounded-lg p-3 border border-gray-100">
                    <p class="text-xs text-gray-500">Pronunciation</p>
                    <p class="text-lg font-bold text-gray-900">{{ bandScoreRange($firstTask['pronunciation'] ?? 0) }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Task-by-Task Feedback -->
    @foreach($humanEvaluation->task_scores as $taskIndex => $taskScore)
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
            <h4 class="text-md font-semibold text-gray-900 mb-3">
                {{ $sectionType === 'writing' ? 'Task ' . ($taskIndex + 1) : 'Part ' . ($taskIndex + 1) }}
                <span class="text-[#C8102E] ml-2">Band {{ bandScoreRange($taskScore['score'] ?? 0) }}</span>
            </h4>

            <!-- Teacher's Feedback -->
            @if(isset($taskScore['feedback']))
                <div class="mb-4">
                    <p class="text-sm text-gray-500 mb-2">Teacher's Feedback:</p>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $taskScore['feedback'] }}</p>
                    </div>
                </div>
            @endif

            <!-- Error Markings (Writing only) -->
            @if($sectionType === 'writing' && $humanEvaluation->errorMarkings->isNotEmpty())
                @php
                    $taskErrors = $humanEvaluation->errorMarkings->where('task_number', $taskIndex + 1);
                @endphp
                @if($taskErrors->isNotEmpty())
                    <div>
                        <p class="text-sm text-gray-500 mb-2">Error Markings:</p>
                        <div class="space-y-2">
                            @foreach($taskErrors as $error)
                                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                    <p class="text-sm font-medium text-red-700">"{{ $error->marked_text }}"</p>
                                    <p class="text-xs text-red-600 mt-1 capitalize">{{ str_replace('_', ' ', $error->error_type) }}</p>
                                    @if($error->comment)
                                        <p class="text-sm text-gray-600 mt-2">{{ $error->comment }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endforeach

    <!-- Strengths and Improvements -->
    <div class="grid md:grid-cols-2 gap-4">
        <!-- Strengths -->
        @if(!empty($humanEvaluation->strengths))
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
            <h4 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
                Strengths
            </h4>
            <ul class="space-y-2">
                @foreach($humanEvaluation->strengths as $strength)
                    <li class="text-sm text-gray-700 flex items-start">
                        <span class="text-emerald-500 mr-2">•</span>
                        <span>{{ $strength }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Areas for Improvement -->
        @if(!empty($humanEvaluation->improvements))
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <h4 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-lightbulb text-amber-500 mr-2"></i>
                Areas for Improvement
            </h4>
            <ul class="space-y-2">
                @foreach($humanEvaluation->improvements as $improvement)
                    <li class="text-sm text-gray-700 flex items-start">
                        <span class="text-amber-500 mr-2">•</span>
                        <span>{{ $improvement }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <!-- Evaluated By -->
    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
        <div class="flex items-center">
            <img src="{{ $humanEvaluation->evaluator->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($humanEvaluation->evaluator->name ?? 'Teacher') . '&background=C8102E&color=fff' }}"
                 alt="{{ $humanEvaluation->evaluator->name ?? 'Teacher' }}"
                 class="w-10 h-10 rounded-full mr-3 border-2 border-gray-100">
            <div>
                <p class="text-xs text-gray-500">Evaluated by</p>
                <p class="text-sm font-medium text-gray-900">{{ $humanEvaluation->evaluator->name ?? 'Teacher' }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500">Evaluated on</p>
            <p class="text-sm text-gray-700">{{ $humanEvaluation->evaluated_at ? $humanEvaluation->evaluated_at->format('M d, Y') : 'N/A' }}</p>
        </div>
    </div>
</div>
