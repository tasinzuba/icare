@extends('layouts.offline-student')

@section('title', 'Dashboard')

@section('content')
@php
    $progressPercent = $enrollment->full_tests_allowed > 0
        ? min(round(($enrollment->full_tests_taken / $enrollment->full_tests_allowed) * 100), 100)
        : 0;
@endphp

{{-- Welcome + Account Info --}}
<div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-brand-50 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-brand-600 font-bold text-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
            </div>
            <div>
                <h1 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h1>
                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                    <span class="text-[11px] text-gray-400 font-medium">{{ $enrollment->student_id }}</span>
                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                    <span class="text-[11px] text-gray-400 font-medium flex items-center gap-1">
                        @if($enrollment->evaluation_type === 'ai')
                            <i class="fas fa-robot text-[9px]"></i> AI Evaluation
                        @elseif($enrollment->evaluation_type === 'human')
                            <i class="fas fa-user-tie text-[9px]"></i> Human Evaluation
                        @else
                            <i class="fas fa-balance-scale text-[9px]"></i> AI + Human
                        @endif
                    </span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4 sm:gap-5 text-center">
            <div>
                <p class="text-xl font-bold text-gray-900">{{ $enrollment->remaining_full_tests }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Tests Left</p>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <div>
                <p class="text-xl font-bold {{ $enrollment->days_remaining <= 7 ? 'text-red-600' : 'text-gray-900' }}">{{ $enrollment->days_remaining }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Days Left</p>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <div>
                <p class="text-xl font-bold text-gray-900">{{ $stats['average_score'] ?? '-' }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Avg. Band</p>
            </div>
        </div>
    </div>
    {{-- Progress --}}
    <div class="mt-4 pt-4 border-t border-gray-100">
        <div class="flex justify-between text-[11px] text-gray-400 mb-1.5">
            <span>{{ $enrollment->full_tests_taken }}/{{ $enrollment->full_tests_allowed }} full tests completed</span>
            <span>Valid until {{ $enrollment->valid_until->format('M d, Y') }}</span>
        </div>
        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-brand-500 rounded-full transition-all" style="width: {{ $progressPercent }}%"></div>
        </div>
    </div>
</div>

{{-- Quick Stats --}}
<div class="grid grid-cols-3 gap-3 mb-5">
    <div class="bg-white border border-gray-200 rounded-lg px-4 py-3">
        <p class="text-lg font-bold text-gray-900">{{ $stats['total_tests'] }}</p>
        <p class="text-[11px] text-gray-400">Total Attempts</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg px-4 py-3">
        <p class="text-lg font-bold text-emerald-600">{{ $stats['completed_tests'] }}</p>
        <p class="text-[11px] text-gray-400">Completed</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg px-4 py-3">
        <p class="text-lg font-bold text-amber-600">{{ $stats['total_tests'] - $stats['completed_tests'] }}</p>
        <p class="text-[11px] text-gray-400">In Progress</p>
    </div>
</div>

{{-- Per-Section Test Limits --}}
@if($enrollment->hasPerSectionLimits())
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    @php
        $secStyles = [
            'listening' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'bar' => 'bg-blue-500', 'icon' => 'fa-headphones'],
            'reading' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'bar' => 'bg-emerald-500', 'icon' => 'fa-book-open'],
            'writing' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'bar' => 'bg-amber-500', 'icon' => 'fa-pen-fancy'],
            'speaking' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-700', 'bar' => 'bg-violet-500', 'icon' => 'fa-microphone'],
        ];
    @endphp
    @foreach(['listening', 'reading', 'writing', 'speaking'] as $secType)
        @php
            $secLimit = $enrollment->getSectionTestLimit($secType);
            $secTaken = $enrollment->getSectionTestsTaken($secType);
            $sc = $secStyles[$secType];
            $secPct = $secLimit > 0 ? round(($secTaken / $secLimit) * 100) : 0;
        @endphp
        @if($secLimit > 0)
        <button type="button"
                onclick="scrollToSection('{{ $secType }}')"
                class="text-left bg-white border border-gray-200 rounded-lg p-3 hover:border-gray-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-150 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-{{ $secType === 'listening' ? 'blue' : ($secType === 'reading' ? 'emerald' : ($secType === 'writing' ? 'amber' : 'violet')) }}-400">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-6 h-6 {{ $sc['bg'] }} rounded flex items-center justify-center">
                    <i class="fas {{ $sc['icon'] }} {{ $sc['text'] }} text-[10px]"></i>
                </div>
                <span class="text-xs font-medium text-gray-700">{{ ucfirst($secType) }}</span>
            </div>
            <p class="text-sm font-bold text-gray-900">{{ $secTaken }}/{{ $secLimit }}</p>
            <div class="h-1 bg-gray-100 rounded-full mt-1.5 overflow-hidden">
                <div class="h-full {{ $sc['bar'] }} rounded-full" style="width: {{ $secPct }}%"></div>
            </div>
        </button>
        @endif
    @endforeach
</div>
@endif

{{-- Full Mock Tests --}}
@if($fullTests->count() > 0)
<div class="bg-white border border-gray-200 rounded-xl mb-5 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-brand-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-layer-group text-brand-600 text-sm"></i>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Full Mock Tests</h2>
                <p class="text-[11px] text-gray-400">4 sections per test</p>
            </div>
        </div>
        <span class="text-[11px] font-medium text-gray-400">{{ $fullTests->count() }} available</span>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($fullTests as $fullTest)
                @php
                    $attempts = $fullTestAttempts->get($fullTest->id) ?? collect();
                    $completedAttempts = $attempts->where('status', 'completed');
                    $inProgressAttempt = $attempts->where('status', 'in_progress')->first();
                    $latestCompleted = $completedAttempts->sortByDesc('created_at')->first();
                    $bestScore = $completedAttempts->max('overall_band_score');
                    $isPreviouslyCompleted = in_array($fullTest->id, $previouslyCompletedFullTests ?? []);
                    $testAssignment = isset($testAssignments) ? $testAssignments->get($fullTest->id) : null;
                    $testExpiryDate = $testAssignment ? $testAssignment->valid_until : $enrollment->valid_until;
                    $testDaysRemaining = $testAssignment ? $testAssignment->days_remaining : $enrollment->days_remaining;
                @endphp
                <div class="border border-gray-200 rounded-lg p-4 {{ $isPreviouslyCompleted ? 'bg-gray-50 opacity-75' : 'hover:border-brand-300' }} transition">
                    {{-- Title + Score --}}
                    <div class="flex items-start justify-between mb-2">
                        <div class="min-w-0 flex-1 mr-2">
                            <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $fullTest->title }}</h3>
                            @if(!$isPreviouslyCompleted && $completedAttempts->count() === 0)
                                <p class="text-[10px] {{ $testDaysRemaining <= 7 ? 'text-red-500' : 'text-gray-400' }} mt-0.5">
                                    Expires {{ $testExpiryDate->format('M d, Y') }}
                                    @if($testDaysRemaining <= 7)
                                        <span class="font-medium">({{ $testDaysRemaining }}d left)</span>
                                    @endif
                                </p>
                            @endif
                        </div>
                        @if($bestScore)
                            <span class="text-lg font-bold text-brand-600 flex-shrink-0">{{ $bestScore }}</span>
                        @endif
                    </div>

                    {{-- Status badges --}}
                    @if($isPreviouslyCompleted)
                        <span class="inline-block px-2 py-0.5 text-[10px] font-medium rounded-full bg-gray-100 text-gray-500 mb-2.5">
                            <i class="fas fa-lock mr-0.5"></i> Previous Package
                        </span>
                    @elseif($completedAttempts->count() > 0)
                        <span class="inline-block px-2 py-0.5 text-[10px] font-medium rounded-full bg-emerald-50 text-emerald-600 mb-2.5">
                            <i class="fas fa-check-circle mr-0.5"></i> Completed
                        </span>
                    @elseif($inProgressAttempt)
                        <span class="inline-block px-2 py-0.5 text-[10px] font-medium rounded-full bg-blue-50 text-blue-600 mb-2.5">
                            <i class="fas fa-spinner fa-spin mr-0.5"></i> In Progress
                        </span>
                    @endif

                    {{-- Time warning --}}
                    @if($inProgressAttempt && $inProgressAttempt->remaining_time_formatted)
                    <div class="mb-2.5 flex items-center gap-1.5 text-[11px] text-amber-600 bg-amber-50 border border-amber-100 rounded px-2.5 py-1.5">
                        <i class="fas fa-clock text-[10px]"></i>
                        <span class="font-medium">{{ $inProgressAttempt->remaining_time_formatted }}</span> to complete
                    </div>
                    @endif

                    {{-- Action --}}
                    @if($isPreviouslyCompleted)
                        <p class="text-[11px] text-gray-400 text-center py-1">Cannot retake after renewal</p>
                    @elseif($inProgressAttempt)
                        @if($inProgressAttempt->isExpiredForOfflineStudent())
                            <p class="text-[11px] text-red-500 text-center py-1"><i class="fas fa-exclamation-circle mr-1"></i>Attempt expired (24h limit)</p>
                        @else
                            <a href="{{ route('student.full-test.section', ['fullTestAttempt' => $inProgressAttempt, 'section' => $inProgressAttempt->current_section]) }}"
                               class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-blue-600 text-white text-xs font-medium hover:bg-blue-700 transition">
                                <i class="fas fa-play text-[9px]"></i> Continue Test
                            </a>
                        @endif
                    @elseif($completedAttempts->count() > 0)
                        @if($enrollment->branchAllowsRetakes() && $enrollment->canAccessFullTest($fullTest->id))
                            <div class="flex gap-1.5">
                                <a href="{{ route('student.full-test.results', $latestCompleted) }}"
                                   class="flex-1 inline-flex items-center justify-center gap-1 px-2 py-2 rounded-lg border border-gray-200 text-gray-600 text-xs font-medium hover:bg-gray-50 transition">
                                    <i class="fas fa-chart-bar text-[9px]"></i> Results
                                </a>
                                <a href="{{ route('student.full-test.onboarding', $fullTest) }}"
                                   class="flex-1 inline-flex items-center justify-center gap-1 px-2 py-2 rounded-lg bg-brand-500 text-white text-xs font-medium hover:bg-brand-600 transition">
                                    <i class="fas fa-redo text-[9px]"></i> Retake
                                </a>
                            </div>
                        @else
                            <a href="{{ route('student.full-test.results', $latestCompleted) }}"
                               class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 text-gray-600 text-xs font-medium hover:bg-gray-50 transition">
                                <i class="fas fa-chart-bar text-[9px]"></i> View Results
                            </a>
                        @endif
                    @else
                        <a href="{{ route('student.full-test.onboarding', $fullTest) }}"
                           class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-brand-500 text-white text-xs font-medium hover:bg-brand-600 transition">
                            <i class="fas fa-play text-[9px]"></i> Start Test
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Section Practice --}}
@if(count($sectionTests) > 0)
@php
    $sectionConfig = [
        'listening' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-200', 'hoverBorder' => 'hover:border-blue-300', 'icon' => 'fa-headphones', 'desc' => 'Audio comprehension', 'duration' => '40 min', 'sectionLabel' => 'Listening'],
        'reading' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200', 'hoverBorder' => 'hover:border-emerald-300', 'icon' => 'fa-book-open', 'desc' => 'Text analysis', 'duration' => '60 min', 'sectionLabel' => 'Reading'],
        'writing' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200', 'hoverBorder' => 'hover:border-amber-300', 'icon' => 'fa-pen-fancy', 'desc' => 'Essay & report writing', 'duration' => '60 min', 'sectionLabel' => 'Writing'],
        'speaking' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'border' => 'border-violet-200', 'hoverBorder' => 'hover:border-violet-300', 'icon' => 'fa-microphone', 'desc' => 'Verbal communication', 'duration' => '14 min', 'sectionLabel' => 'Speaking'],
    ];
@endphp
<div class="bg-white border border-gray-200 rounded-xl mb-5 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2.5">
        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
            <i class="fas fa-puzzle-piece text-gray-500 text-sm"></i>
        </div>
        <div>
            <h2 class="text-sm font-semibold text-gray-900">Section Practice</h2>
            <p class="text-[11px] text-gray-400">Practice individual sections</p>
        </div>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($sectionTests as $sectionName => $data)
            @php
                $sectionSlug = strtolower($sectionName);
                $hasPerSectionLimits = $enrollment->hasPerSectionLimits();
                $sectionLimit = $enrollment->getSectionTestLimit($sectionSlug);
                $sectionTaken = $enrollment->getSectionTestsTaken($sectionSlug);
                $sectionRemaining = $enrollment->getRemainingSectionTestsOfType($sectionSlug);

                if ($hasPerSectionLimits && $sectionLimit <= 0) continue;

                $style = $sectionConfig[$sectionSlug] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'border' => 'border-gray-200', 'hoverBorder' => 'hover:border-gray-300', 'icon' => 'fa-file', 'desc' => 'Practice', 'duration' => '— min', 'sectionLabel' => ucfirst($sectionSlug)];
            @endphp

            <div x-data="{ open: true }"
                 id="section-block-{{ $sectionSlug }}"
                 @open-section.window="if ($event.detail === '{{ $sectionSlug }}') open = true">
                <button @click="open = !open"
                        class="w-full px-5 py-3.5 flex items-center gap-3 hover:bg-gray-50 transition text-left">
                    <div class="w-8 h-8 {{ $style['bg'] }} rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas {{ $style['icon'] }} {{ $style['text'] }} text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-medium text-gray-800">{{ ucfirst($sectionName) }}</h3>
                        <p class="text-[11px] text-gray-400">{{ $style['desc'] }}</p>
                    </div>
                    @if($hasPerSectionLimits)
                        <span class="px-2 py-0.5 {{ $sectionRemaining > 0 ? $style['bg'] . ' ' . $style['text'] : 'bg-red-50 text-red-500' }} rounded-full text-[10px] font-medium flex-shrink-0">
                            {{ $sectionTaken }}/{{ $sectionLimit }}
                        </span>
                    @else
                        <span class="text-[11px] text-gray-400 flex-shrink-0">{{ $data['testSets']->count() }} tests</span>
                    @endif
                    <i class="fas fa-chevron-down text-gray-300 text-xs transition-transform"
                       :class="{ 'rotate-180': open }"></i>
                </button>

                <div x-show="open" x-collapse x-cloak class="px-5 pb-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-1">
                        @foreach($data['testSets']->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)->values() as $testSet)
                            @php
                                $attempts = $sectionAttempts->get($testSet->id) ?? collect();
                                $completedAttempts = $attempts->where('status', 'completed');
                                $inProgressAttempt = $attempts->where('status', 'in_progress')->first();
                                $latestCompleted = $completedAttempts->sortByDesc('created_at')->first();
                                $bestScore = $completedAttempts->max('band_score');
                                $isCompleted = $completedAttempts->count() > 0;
                                $isLocked = $hasPerSectionLimits && $sectionRemaining <= 0 && !$isCompleted && !$inProgressAttempt;
                            @endphp

                            <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-md hover:border-gray-300 transition-all duration-200">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-gray-600">
                                        <i class="far fa-clock text-gray-400 text-[11px]"></i>
                                        {{ $style['duration'] }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#C8102E]">
                                        <i class="fas fa-book text-[11px]"></i>
                                        Academic
                                    </span>
                                </div>

                                <h4 class="text-[15px] font-bold text-gray-900 mb-1.5 truncate">{{ $testSet->title }}</h4>

                                <p class="text-[12px] text-gray-500 mb-4 truncate">
                                    @if($bestScore)
                                        Best Score: <span class="font-semibold text-gray-800">{{ $bestScore }}</span> · {{ $completedAttempts->count() }}x completed
                                    @elseif($inProgressAttempt)
                                        Test in progress — pick up where you left off
                                    @else
                                        Interactive IELTS {{ $style['sectionLabel'] }} test simulation
                                    @endif
                                </p>

                                @if($inProgressAttempt)
                                    <a href="{{ route('student.' . $sectionSlug . '.start', $testSet) }}"
                                       class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-md text-[13px] font-semibold bg-[#C8102E] text-white hover:bg-[#A00E27] transition">
                                        Continue Module
                                    </a>
                                @elseif($isCompleted)
                                    @if($enrollment->branchAllowsRetakes())
                                        <div class="flex gap-2">
                                            <a href="{{ route('student.results.show', $latestCompleted) }}"
                                               class="flex-1 inline-flex items-center justify-center px-3 py-2.5 rounded-md text-[13px] font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                                                Results
                                            </a>
                                            <a href="{{ route('student.' . $sectionSlug . '.start', [$testSet, 'fresh' => 1]) }}"
                                               class="flex-1 inline-flex items-center justify-center px-3 py-2.5 rounded-md text-[13px] font-semibold bg-[#C8102E] text-white hover:bg-[#A00E27] transition">
                                                Retake
                                            </a>
                                        </div>
                                    @else
                                        <a href="{{ route('student.results.show', $latestCompleted) }}"
                                           class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-md text-[13px] font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                                            View Results
                                        </a>
                                    @endif
                                @elseif($isLocked)
                                    <span class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-md text-[13px] font-semibold bg-gray-100 text-gray-400 cursor-not-allowed">
                                        Limit Reached
                                    </span>
                                @else
                                    <a href="{{ route('student.' . $sectionSlug . '.onboarding.confirm-details', $testSet) }}"
                                       class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-md text-[13px] font-semibold bg-[#C8102E] text-white hover:bg-[#A00E27] transition">
                                        Start Module
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- Recent Activity --}}
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-history text-gray-500 text-sm"></i>
            </div>
            <h2 class="text-sm font-semibold text-gray-900">Recent Activity</h2>
        </div>
        <a href="{{ route('student.results') }}" class="text-[11px] text-brand-600 hover:text-brand-700 font-medium">
            View all <i class="fas fa-arrow-right ml-0.5 text-[9px]"></i>
        </a>
    </div>

    @php
        $allFullAttempts = isset($fullTestAttempts) ? $fullTestAttempts->flatten()->sortByDesc('created_at') : collect();
    @endphp

    @if($allFullAttempts->count() > 0 || $recentAttempts->count() > 0)
    <div class="divide-y divide-gray-50">
        {{-- Full test attempts --}}
        @foreach($allFullAttempts->take(3) as $attempt)
        <div class="px-5 py-3 flex items-center gap-3">
            <div class="w-8 h-8 bg-brand-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-layer-group text-brand-500 text-xs"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ $attempt->fullTest->title ?? 'Full Mock Test' }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">{{ $attempt->created_at->format('M d, Y') }} at {{ $attempt->created_at->format('h:i A') }}</p>
            </div>
            <div class="flex-shrink-0">
                @if($attempt->overall_band_score)
                    <span class="text-base font-bold text-brand-600">{{ $attempt->overall_band_score }}</span>
                @elseif($attempt->status === 'completed')
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded-full text-[10px] font-medium">Evaluating</span>
                @else
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full text-[10px] font-medium">In Progress</span>
                @endif
            </div>
        </div>

        {{-- Section scores inline --}}
        @if($attempt->status === 'completed' && $attempt->sectionAttempts->count() > 0)
        <div class="px-5 py-2 bg-gray-50/50">
            <div class="ml-11 flex gap-2">
                @foreach(['listening', 'reading', 'writing', 'speaking'] as $section)
                    @php
                        $sa = $attempt->sectionAttempts->first(fn($s) => $s->section_type === $section);
                        $score = $sa && $sa->studentAttempt ? $sa->studentAttempt->band_score : null;
                    @endphp
                    <div class="flex items-center gap-1 text-[10px] text-gray-500">
                        <span class="capitalize">{{ substr($section, 0, 1) }}:</span>
                        <span class="font-semibold {{ $score ? 'text-gray-700' : 'text-gray-300' }}">{{ $score ?? '-' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach

        {{-- Section test attempts --}}
        @php
            $activityIcons = [
                'listening' => ['icon' => 'fa-headphones', 'bg' => 'bg-blue-50', 'text' => 'text-blue-500'],
                'reading' => ['icon' => 'fa-book-open', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-500'],
                'writing' => ['icon' => 'fa-pen-fancy', 'bg' => 'bg-amber-50', 'text' => 'text-amber-500'],
                'speaking' => ['icon' => 'fa-microphone', 'bg' => 'bg-violet-50', 'text' => 'text-violet-500'],
            ];
        @endphp
        @foreach($recentAttempts->take(5) as $attempt)
            @php
                $slug = $attempt->testSet->section->slug ?? 'general';
                $ai = $activityIcons[$slug] ?? ['icon' => 'fa-file', 'bg' => 'bg-gray-50', 'text' => 'text-gray-500'];
            @endphp
            <div class="px-5 py-3 flex items-center gap-3">
                <div class="w-8 h-8 {{ $ai['bg'] }} rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas {{ $ai['icon'] }} {{ $ai['text'] }} text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $attempt->testSet->title ?? 'Test' }}</p>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 capitalize flex-shrink-0">{{ $slug }}</span>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $attempt->created_at->format('M d, Y') }} at {{ $attempt->created_at->format('h:i A') }}</p>
                </div>
                <div class="flex-shrink-0">
                    @if($attempt->band_score)
                        <span class="text-sm font-bold text-gray-700">{{ $attempt->band_score }}</span>
                    @elseif($attempt->status === 'completed')
                        <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded-full text-[10px] font-medium">Pending</span>
                    @else
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full text-[10px] font-medium">In Progress</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @else
    <div class="px-5 py-10 text-center">
        <i class="fas fa-clipboard-list text-gray-300 text-2xl mb-2"></i>
        <p class="text-sm text-gray-400">No tests taken yet</p>
        <p class="text-[11px] text-gray-400 mt-1">Start a test above to begin your IELTS journey</p>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function scrollToSection(slug) {
        // Open the section's accordion via Alpine event
        window.dispatchEvent(new CustomEvent('open-section', { detail: slug }));

        // Scroll into view after a tick so Alpine has applied the open state
        setTimeout(() => {
            const el = document.getElementById('section-block-' + slug);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 60);
    }
</script>
@endpush
@endsection
