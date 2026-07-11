<x-dashboard-layout>
    <x-slot:title>AI Evaluation Details</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('student.full-test.results', $fullTestAttempt) }}"
               class="inline-flex items-center text-sm text-gray-500 hover:text-[#C8102E] mb-4 transition-colors group">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Back to Results
            </a>

            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-[#C8102E] to-[#E91E3A] rounded-2xl flex items-center justify-center shadow-lg shadow-red-200">
                    <i class="fas fa-robot text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">AI Evaluation Details</h1>
                    <p class="text-gray-500">{{ $fullTestAttempt->fullTest->title }}</p>
                </div>
            </div>
        </div>

        <!-- AI Evaluation Badge -->
        <div class="bg-gradient-to-r from-[#C8102E]/5 via-red-500/5 to-rose-500/5 border border-[#C8102E]/20 rounded-2xl p-5 mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-[#C8102E] to-[#A00D24] rounded-xl flex items-center justify-center">
                        <i class="fas fa-robot text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">CD IELTS AI Evaluation</h3>
                        <p class="text-sm text-gray-500">Smart & Accurate Analysis</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">Evaluated</p>
                    <p class="text-sm font-medium text-gray-700">
                        @php
                            $evaluatedAt = null;
                            foreach ($fullTestAttempt->sectionAttempts as $sa) {
                                if ($sa->studentAttempt->ai_evaluated_at) {
                                    $evaluatedAt = $sa->studentAttempt->ai_evaluated_at;
                                    break;
                                }
                            }
                        @endphp
                        {{ $evaluatedAt ? $evaluatedAt->format('M d, Y h:i A') : 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        @php
            $sections = [
                'writing' => ['icon' => 'fa-pen-fancy', 'label' => 'Writing', 'color' => 'violet', 'gradient' => 'from-violet-500 to-purple-600'],
                'speaking' => ['icon' => 'fa-microphone', 'label' => 'Speaking', 'color' => 'orange', 'gradient' => 'from-orange-500 to-red-500']
            ];
            $hasEvaluations = false;
        @endphp

        <!-- Writing & Speaking Sections Only -->
        @foreach($fullTestAttempt->sectionAttempts as $sectionAttempt)
            @php
                $studentAttempt = $sectionAttempt->studentAttempt;
                $sectionType = $sectionAttempt->section_type;

                // Only show writing and speaking
                if (!in_array($sectionType, ['writing', 'speaking'])) continue;

                $sectionData = $sections[$sectionType] ?? null;
                if (!$sectionData) continue;

                $humanEvaluation = $studentAttempt->humanEvaluationRequest?->humanEvaluation;
            @endphp

            @if($studentAttempt->ai_evaluated_at)
                @php $hasEvaluations = true; @endphp

                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 shadow-sm hover:shadow-md transition-shadow">
                    <!-- Section Header -->
                    <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br {{ $sectionData['gradient'] }} flex items-center justify-center shadow-lg">
                                    <i class="fas {{ $sectionData['icon'] }} text-white text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900 capitalize">{{ $sectionType }} Evaluation</h2>
                                    <p class="text-sm text-gray-500">AI-powered detailed analysis</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Band Score</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-3xl font-bold text-[#C8102E]">
                                        {{ $studentAttempt->ai_band_score ? bandScoreRange($studentAttempt->ai_band_score) : 'N/A' }}
                                    </span>
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i>AI
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        {{-- AI Evaluation Details --}}
                        @if($sectionType === 'writing')
                            @include('student.full-test.partials.ai-writing-evaluation', ['studentAttempt' => $studentAttempt])
                        @else
                            @include('student.full-test.partials.ai-speaking-evaluation', ['studentAttempt' => $studentAttempt])
                        @endif
                    </div>
                </div>

                {{-- Human Evaluation if available --}}
                @if($humanEvaluation)
                    <div class="bg-white border border-purple-200 rounded-2xl overflow-hidden mb-6 shadow-sm">
                        <div class="px-6 py-4 border-b border-purple-100 bg-gradient-to-r from-purple-50 to-white">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-user-tie text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900">{{ ucfirst($sectionType) }} - Human Evaluation</h3>
                                    <p class="text-xs text-gray-500">By {{ $humanEvaluation->evaluator->name ?? 'Teacher' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            @include('student.full-test.partials.human-evaluation', ['humanEvaluation' => $humanEvaluation, 'sectionType' => $sectionType, 'studentAttempt' => $studentAttempt])
                        </div>
                    </div>
                @endif

            @elseif($humanEvaluation)
                {{-- Only Human Evaluation (No AI) --}}
                @php $hasEvaluations = true; @endphp

                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 shadow-sm">
                    <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br {{ $sectionData['gradient'] }} flex items-center justify-center shadow-lg">
                                    <i class="fas {{ $sectionData['icon'] }} text-white text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900 capitalize">{{ $sectionType }} Evaluation</h2>
                                    <p class="text-sm text-gray-500">Human expert evaluation</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Band Score</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-3xl font-bold text-[#C8102E]">
                                        {{ $studentAttempt->band_score ? bandScoreRange($studentAttempt->band_score) : 'N/A' }}
                                    </span>
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs font-medium rounded-full">
                                        <i class="fas fa-user-check mr-1"></i>Human
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        @include('student.full-test.partials.human-evaluation', ['humanEvaluation' => $humanEvaluation, 'sectionType' => $sectionType, 'studentAttempt' => $studentAttempt])
                    </div>
                </div>
            @endif
        @endforeach

        @if(!$hasEvaluations)
            <!-- No Evaluations Yet -->
            <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center">
                <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-hourglass-half text-amber-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">No Evaluations Available</h3>
                <p class="text-gray-500 max-w-md mx-auto">
                    Your Writing and Speaking sections haven't been evaluated yet. Please request an AI or human evaluation from the results page.
                </p>
                <a href="{{ route('student.full-test.results', $fullTestAttempt) }}"
                   class="inline-flex items-center px-6 py-3 bg-[#C8102E] hover:bg-[#A00D24] text-white font-semibold rounded-xl transition-colors mt-6">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Results
                </a>
            </div>
        @endif

    </div>
</x-dashboard-layout>
