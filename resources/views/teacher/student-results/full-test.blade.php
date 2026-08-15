<x-teacher-layout>
    <x-slot:title>Full Test Result</x-slot>

    <div class="container mx-auto px-4 py-8">
        {{-- Header --}}
        <div class="mb-6 flex items-center gap-3">
            <a href="{{ route('teacher.student-results.index', ['test_type' => 'full']) }}"
               class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Full Test Result</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $fullTestAttempt->user->name ?? 'Unknown student' }}
                    &middot; {{ optional($fullTestAttempt->fullTest)->title ?? 'Full Test' }}
                </p>
            </div>
        </div>

        {{-- Overall summary --}}
        @php
            $sectionOrder = ['listening', 'reading', 'writing', 'speaking'];
            $bandColor = function ($band) {
                if ($band === null) return '#6b7280';
                if ($band >= 7) return '#15803d';
                if ($band >= 6) return '#1d4ed8';
                if ($band >= 5) return '#b45309';
                return '#b91c1c';
            };
            // Recomputed from effective section scores; the stored column is unreliable.
            $overall = \App\Services\StudentSectionResultService::effectiveOverall($fullTestAttempt);
        @endphp

        <div class="bg-white shadow rounded-lg mb-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">Overall Band</p>
                    @if($overall !== null)
                        <p style="font-size:36px;font-weight:800;line-height:1.1;color:{{ $bandColor($overall) }};">
                            {{ number_format($overall, 1) }}
                        </p>
                    @else
                        <p class="text-2xl font-bold text-amber-600">Pending</p>
                        <p class="text-xs text-gray-400 mt-1">Available once every section is evaluated</p>
                    @endif
                </div>

                {{-- Per-section bands at a glance --}}
                <div class="flex flex-wrap gap-3">
                    @foreach($sectionOrder as $type)
                        @continue(!$sections->has($type))
                        @php
                            $sa = $sections[$type]->studentAttempt;
                            $band = $sa->band_score;
                        @endphp
                        <div class="rounded-lg border border-gray-200 px-4 py-2 text-center min-w-[92px]">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-500">{{ $type }}</p>
                            @if($band !== null)
                                <p style="font-size:20px;font-weight:800;color:{{ $bandColor($band) }};">{{ number_format($band, 1) }}</p>
                            @else
                                <p class="text-sm font-semibold text-amber-600 mt-1">Pending</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Section tabs --}}
        <div class="bg-white shadow rounded-lg">
            <div class="border-b border-gray-200 px-4 sm:px-6">
                <nav class="-mb-px flex gap-6 overflow-x-auto" aria-label="Sections">
                    @foreach($sectionOrder as $type)
                        @continue(!$sections->has($type))
                        @php
                            $icons = ['listening' => 'fa-headphones', 'reading' => 'fa-book-open', 'writing' => 'fa-pen-fancy', 'speaking' => 'fa-microphone'];
                            $isActive = $activeSection === $type;
                        @endphp
                        <a href="{{ route('teacher.student-results.full-test', ['fullTestAttempt' => $fullTestAttempt->id, 'section' => $type]) }}"
                           class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium {{ $isActive ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                            <i class="fas {{ $icons[$type] ?? 'fa-file-lines' }} mr-1.5"></i>{{ ucfirst($type) }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="p-6">
                {{-- Section header --}}
                <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ ucfirst($activeSection) }} Section</h2>
                        <p class="text-sm text-gray-500">{{ optional($studentAttempt->testSet)->title }}</p>
                    </div>
                    <a href="{{ route('teacher.student-results.show', $studentAttempt) }}"
                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-emerald-700 bg-emerald-100 hover:bg-emerald-200">
                        <i class="fas fa-up-right-from-square mr-1"></i>Open section result
                    </a>
                </div>

                @if(!empty($responses))
                    {{-- Auto-scored section: per-question breakdown --}}
                    <div class="flex flex-wrap items-center gap-3 mb-5 text-sm">
                        <span class="px-3 py-1 rounded-full" style="background:#dcfce7;color:#166534;font-weight:600;">
                            {{ collect($responses)->where('is_correct', true)->count() }} correct
                        </span>
                        <span class="px-3 py-1 rounded-full" style="background:#fee2e2;color:#991b1b;font-weight:600;">
                            {{ collect($responses)->where('is_answered', true)->where('is_correct', false)->count() }} wrong
                        </span>
                        <span class="px-3 py-1 rounded-full" style="background:#f1f5f9;color:#475569;font-weight:600;">
                            {{ collect($responses)->where('is_answered', false)->count() }} unanswered
                        </span>
                        <span class="text-gray-500">out of {{ count($responses) }} questions</span>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($responses as $item)
                            <div class="py-3 flex items-start gap-4">
                                <span class="w-8 shrink-0 text-sm font-bold text-gray-700">{{ $item['number'] }}</span>

                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-gray-700">
                                        {{ Str::limit(trim(strip_tags(html_entity_decode($item['content'] ?? ''))), 120) }}
                                    </p>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-x-6 gap-y-1 text-sm">
                                        <span>
                                            <span class="text-gray-500">Answer:</span>
                                            @if($item['is_answered'] && $item['student_answer'] !== 'No answer')
                                                <span style="font-weight:600;color:{{ $item['is_correct'] ? '#166534' : '#991b1b' }};">{{ $item['student_answer'] }}</span>
                                            @else
                                                <span class="text-gray-400 italic">No answer</span>
                                            @endif
                                        </span>
                                        @if(!$item['is_correct'] && $item['correct_answer'] !== '' && $item['correct_answer'] !== null)
                                            <span>
                                                <span class="text-gray-500">Correct:</span>
                                                <span class="font-medium text-green-700">{{ $item['correct_answer'] }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    @if(!$item['is_answered'])
                                        <span class="text-gray-400 text-sm">&mdash;</span>
                                    @elseif($item['is_correct'])
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>Correct
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times mr-1"></i>Incorrect
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Writing / speaking: human-evaluated, so summarise the submission --}}
                    @php
                        $evaluation = optional($studentAttempt->humanEvaluationRequest)->humanEvaluation;
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-xs text-gray-500">Band Score</p>
                            <p class="text-xl font-bold" style="color:{{ $bandColor($studentAttempt->band_score) }};">
                                {{ $studentAttempt->band_score !== null ? number_format($studentAttempt->band_score, 1) : 'Pending' }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-xs text-gray-500">Completion</p>
                            <p class="text-xl font-bold text-gray-900">{{ round($studentAttempt->completion_rate ?? 0) }}%</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-xs text-gray-500">Evaluation</p>
                            <p class="text-sm font-semibold {{ $evaluation ? 'text-green-600' : 'text-amber-600' }}">
                                {{ $evaluation ? 'Completed' : (optional($studentAttempt->humanEvaluationRequest)->status ? ucfirst($studentAttempt->humanEvaluationRequest->status) : 'No request') }}
                            </p>
                        </div>
                    </div>

                    @forelse($studentAttempt->answers->sortBy('question.order_number') as $answer)
                        <div class="py-4 border-b border-gray-100 last:border-0">
                            <p class="text-sm font-medium text-gray-900 mb-2">
                                Task {{ $answer->question->order_number ?? $loop->iteration }}
                            </p>
                            <div class="prose prose-sm max-w-none text-gray-600 mb-3">
                                {!! $answer->question->content ?? '' !!}
                            </div>

                            @if($answer->speakingRecording)
                                <audio controls class="w-full max-w-md">
                                    <source src="{{ $answer->speakingRecording->file_url }}">
                                </audio>
                            @elseif($answer->answer)
                                <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-800 whitespace-pre-line">{{ $answer->answer }}</div>
                                <p class="text-xs text-gray-400 mt-1">{{ str_word_count($answer->answer) }} words</p>
                            @else
                                <p class="text-sm text-gray-400 italic">No response submitted</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-8">No responses recorded for this section.</p>
                    @endforelse
                @endif
            </div>
        </div>
    </div>
</x-teacher-layout>
