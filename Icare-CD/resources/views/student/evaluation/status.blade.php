<x-dashboard-layout>
    <x-slot:title>Evaluation Status</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('student.results.show', $attempt) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
                <i class="fas fa-arrow-left mr-2"></i>Back to Results
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Evaluation Status</h1>
            <p class="text-gray-500 mt-1">{{ $attempt->testSet->title }} - {{ ucfirst($attempt->testSet->section->name) }} Section</p>
        </div>

        <!-- Status Card -->
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 shadow-sm">
            <!-- Status Header -->
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-[#C8102E] flex items-center justify-center shadow-lg">
                            @if($evaluationRequest->status === 'completed')
                                <i class="fas fa-check-circle text-white text-2xl"></i>
                            @elseif($evaluationRequest->status === 'in_progress')
                                <i class="fas fa-spinner fa-pulse text-white text-2xl"></i>
                            @else
                                <i class="fas fa-clock text-white text-2xl"></i>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Human Evaluation</h2>
                            <p class="text-sm text-gray-500">
                                @if($evaluationRequest->status === 'completed')
                                    Evaluation Completed
                                @elseif($evaluationRequest->status === 'in_progress')
                                    Evaluation In Progress
                                @else
                                    Waiting for Teacher
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="px-4 py-2 rounded-xl {{ $evaluationRequest->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($evaluationRequest->status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                        <span class="text-sm font-semibold capitalize">{{ str_replace('_', ' ', $evaluationRequest->status) }}</span>
                    </div>
                </div>
            </div>

            <!-- Progress Timeline -->
            <div class="px-6 py-6 bg-white">
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <!-- Step 1: Requested -->
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center text-white relative z-10 shadow">
                                <i class="fas fa-check text-sm"></i>
                            </div>
                            <p class="text-xs font-medium text-gray-700 mt-2">Requested</p>
                            <p class="text-xs text-gray-500">{{ $evaluationRequest->requested_at->format('M d, h:i A') }}</p>
                        </div>

                        <!-- Step 2: Assigned -->
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-10 h-10 {{ $evaluationRequest->status != 'pending' ? 'bg-emerald-500' : 'bg-gray-300' }} rounded-full flex items-center justify-center text-white relative z-10 shadow">
                                @if($evaluationRequest->status != 'pending')
                                    <i class="fas fa-check text-sm"></i>
                                @else
                                    <i class="fas fa-user text-sm"></i>
                                @endif
                            </div>
                            <p class="text-xs font-medium text-gray-700 mt-2">Assigned</p>
                            @if($evaluationRequest->assigned_at)
                                <p class="text-xs text-gray-500">{{ $evaluationRequest->assigned_at->format('M d, h:i A') }}</p>
                            @endif
                        </div>

                        <!-- Step 3: In Progress -->
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-10 h-10 {{ $evaluationRequest->status === 'in_progress' || $evaluationRequest->status === 'completed' ? 'bg-emerald-500' : 'bg-gray-300' }} rounded-full flex items-center justify-center text-white relative z-10 shadow">
                                @if($evaluationRequest->status === 'in_progress')
                                    <i class="fas fa-spinner fa-pulse text-sm"></i>
                                @elseif($evaluationRequest->status === 'completed')
                                    <i class="fas fa-check text-sm"></i>
                                @else
                                    <i class="fas fa-edit text-sm"></i>
                                @endif
                            </div>
                            <p class="text-xs font-medium text-gray-700 mt-2">Evaluating</p>
                        </div>

                        <!-- Step 4: Completed -->
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-10 h-10 {{ $evaluationRequest->status === 'completed' ? 'bg-emerald-500' : 'bg-gray-300' }} rounded-full flex items-center justify-center text-white relative z-10 shadow">
                                @if($evaluationRequest->status === 'completed')
                                    <i class="fas fa-check text-sm"></i>
                                @else
                                    <i class="fas fa-flag text-sm"></i>
                                @endif
                            </div>
                            <p class="text-xs font-medium text-gray-700 mt-2">Completed</p>
                            @if($evaluationRequest->completed_at)
                                <p class="text-xs text-gray-500">{{ $evaluationRequest->completed_at->format('M d, h:i A') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Progress Line -->
                    <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200" style="z-index: 1;">
                        <div class="h-full bg-emerald-500 transition-all duration-500"
                             style="width: {{ $evaluationRequest->status === 'completed' ? '100%' : ($evaluationRequest->status === 'in_progress' ? '66%' : ($evaluationRequest->status === 'assigned' ? '33%' : '0%')) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Teacher Information -->
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-user-tie text-[#C8102E]"></i>
                        Assigned Teacher
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        @if($evaluationRequest->teacher->user->avatar_url)
                            <img src="{{ $evaluationRequest->teacher->user->avatar_url }}"
                                 alt="{{ $evaluationRequest->teacher->user->name }}"
                                 class="w-16 h-16 rounded-xl object-cover border border-gray-200">
                        @else
                            <div class="w-16 h-16 rounded-xl bg-gray-900 flex items-center justify-center text-white font-bold text-xl">
                                {{ substr($evaluationRequest->teacher->user->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="flex-1">
                            <h4 class="font-bold text-gray-900 text-lg">{{ $evaluationRequest->teacher->user->name }}</h4>

                            <!-- Rating -->
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $evaluationRequest->teacher->rating ? 'text-amber-400' : 'text-gray-200' }} text-xs"></i>
                                    @endfor
                                </div>
                                <span class="text-sm text-gray-600">{{ number_format($evaluationRequest->teacher->rating, 1) }}</span>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-500">Evaluations</p>
                                    <p class="font-bold text-gray-900">{{ number_format($evaluationRequest->teacher->total_evaluations_done) }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-500">Avg. Time</p>
                                    <p class="font-bold text-gray-900">{{ $evaluationRequest->teacher->average_turnaround_hours }}h</p>
                                </div>
                            </div>

                            <!-- Qualifications -->
                            @if($evaluationRequest->teacher->qualifications && count($evaluationRequest->teacher->qualifications) > 0)
                                <div class="mt-4">
                                    <p class="text-xs text-gray-500 mb-2">Qualifications</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($evaluationRequest->teacher->qualifications, 0, 3) as $qual)
                                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">{{ $qual }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evaluation Details -->
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-info-circle text-[#C8102E]"></i>
                        Evaluation Details
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Priority & Tokens -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Priority Level</p>
                            <p class="font-bold {{ $evaluationRequest->priority === 'urgent' ? 'text-amber-600' : 'text-gray-900' }}">
                                <i class="fas fa-{{ $evaluationRequest->priority === 'urgent' ? 'bolt' : 'clock' }} mr-1"></i>
                                {{ ucfirst($evaluationRequest->priority) }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Tokens Used</p>
                            <p class="font-bold text-gray-900">
                                <i class="fas fa-coins text-amber-500 mr-1"></i>
                                {{ $evaluationRequest->tokens_used }}
                            </p>
                        </div>
                    </div>

                    <!-- Deadline -->
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 mb-1">Expected Completion</p>
                        <p class="font-bold text-gray-900">{{ $evaluationRequest->deadline_at->format('M d, Y h:i A') }}</p>
                        @if($evaluationRequest->status !== 'completed')
                            @if($evaluationRequest->deadline_at->isPast())
                                <p class="text-sm text-red-600 mt-2">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Overdue by {{ $evaluationRequest->deadline_at->diffForHumans(null, true) }}
                                </p>
                            @else
                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                        <span>Time Remaining</span>
                                        <span>{{ $evaluationRequest->deadline_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        @php
                                            $totalHours = $evaluationRequest->requested_at->diffInHours($evaluationRequest->deadline_at);
                                            $passedHours = $evaluationRequest->requested_at->diffInHours(now());
                                            $progress = min(100, ($passedHours / $totalHours) * 100);
                                        @endphp
                                        <div class="bg-[#C8102E] h-2 rounded-full" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    @if($evaluationRequest->status === 'completed' && $evaluationRequest->humanEvaluation)
                        <!-- Evaluation Result -->
                        <div class="bg-emerald-50 rounded-xl p-5 border border-emerald-200">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-emerald-700 font-bold text-lg">Evaluation Complete!</p>
                                    <p class="text-sm text-emerald-600 mt-1">
                                        Completed {{ $evaluationRequest->completed_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-gray-600">Overall Band</p>
                                    <p class="text-3xl font-bold text-emerald-700">
                                        {{ bandScoreRange($evaluationRequest->humanEvaluation->overall_band_score) }}
                                    </p>
                                </div>
                            </div>

                            <a href="{{ route('student.evaluation.result', $attempt) }}"
                               class="block w-full text-center py-3 rounded-xl bg-gray-900 text-white font-semibold hover:bg-gray-800 transition-colors">
                                <i class="fas fa-eye mr-2"></i>
                                View Detailed Evaluation
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($evaluationRequest->status !== 'completed')
            <!-- What to Expect -->
            <div class="mt-6 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-lightbulb text-[#C8102E]"></i>
                    What to Expect
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center flex-shrink-0 border border-gray-200">
                            <i class="fas fa-search text-[#C8102E]"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">Detailed Review</p>
                            <p class="text-xs text-gray-500 mt-1">Your teacher will carefully review each task</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center flex-shrink-0 border border-gray-200">
                            <i class="fas fa-chart-line text-[#C8102E]"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">Band Scores</p>
                            <p class="text-xs text-gray-500 mt-1">Individual scores for each assessment criteria</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center flex-shrink-0 border border-gray-200">
                            <i class="fas fa-comments text-[#C8102E]"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">Personalized Feedback</p>
                            <p class="text-xs text-gray-500 mt-1">Specific suggestions for improvement</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Help Section -->
        <div class="mt-6 text-center">
            <p class="text-gray-400 text-sm mb-4">Need assistance with your evaluation?</p>
            <div class="flex items-center justify-center gap-4">
                <a href="#" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors text-sm font-medium">
                    <i class="fas fa-question-circle mr-2"></i>
                    FAQs
                </a>
                <a href="#" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-xl hover:bg-gray-800 transition-colors text-sm font-medium">
                    <i class="fas fa-headset mr-2"></i>
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</x-dashboard-layout>
