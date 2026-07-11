@extends('layouts.branch')

@section('title', 'Tests')

@section('content')
@php
    $sectionTheme = [
        'listening' => ['icon' => 'fa-headphones', 'bg' => 'bg-violet-50', 'border' => 'border-violet-100', 'text' => 'text-violet-700', 'accent' => 'text-violet-500', 'badge' => 'bg-violet-100 text-violet-700'],
        'reading'   => ['icon' => 'fa-book-open',  'bg' => 'bg-sky-50',    'border' => 'border-sky-100',    'text' => 'text-sky-700',    'accent' => 'text-sky-500',    'badge' => 'bg-sky-100 text-sky-700'],
        'writing'   => ['icon' => 'fa-pen-nib',    'bg' => 'bg-emerald-50','border' => 'border-emerald-100','text' => 'text-emerald-700','accent' => 'text-emerald-500','badge' => 'bg-emerald-100 text-emerald-700'],
        'speaking'  => ['icon' => 'fa-microphone', 'bg' => 'bg-amber-50',  'border' => 'border-amber-100',  'text' => 'text-amber-700',  'accent' => 'text-amber-500',  'badge' => 'bg-amber-100 text-amber-700'],
    ];

    $statusLabel = [
        'completed'   => 'Completed',
        'in_progress' => 'In Progress',
        'abandoned'   => 'Abandoned',
        'expired'     => 'Expired',
    ];
    $statusBadge = [
        'completed'   => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
        'in_progress' => 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
        'abandoned'   => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200',
        'expired'     => 'bg-rose-100 text-rose-700 ring-1 ring-rose-200',
    ];
@endphp

{{-- Page Header --}}
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Tests Overview</h1>
    <p class="text-sm text-gray-500 mt-1">All tests taken by offline students at your branch</p>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <a href="{{ route('branch.tests.today') }}"
       class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-[#C8102E]/30 hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-sky-400 to-sky-600 text-white flex items-center justify-center shadow-sm group-hover:scale-105 transition">
                <i class="fas fa-calendar-day text-base"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['today'] ?? 0 }}</p>
                <p class="text-xs font-medium text-gray-500 mt-1.5">Today</p>
            </div>
        </div>
    </a>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 text-white flex items-center justify-center shadow-sm">
                <i class="fas fa-clipboard-list text-base"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['total_full_tests'] ?? 0 }}</p>
                <p class="text-xs font-medium text-gray-500 mt-1.5">Full Tests</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white flex items-center justify-center shadow-sm">
                <i class="fas fa-puzzle-piece text-base"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['total_section_tests'] ?? 0 }}</p>
                <p class="text-xs font-medium text-gray-500 mt-1.5">Section Tests</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-violet-400 to-violet-600 text-white flex items-center justify-center shadow-sm">
                <i class="fas fa-chart-line text-base"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['this_week'] ?? 0 }}</p>
                <p class="text-xs font-medium text-gray-500 mt-1.5">This Week</p>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Student</label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, ID or email"
                       class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 focus:border-[#C8102E]/40 transition">
            </div>
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Date</label>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 focus:border-[#C8102E]/40 transition">
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Type</label>
            <select name="type" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 focus:border-[#C8102E]/40 transition bg-white">
                <option value="all" {{ $testType === 'all' ? 'selected' : '' }}>All Tests</option>
                <option value="full" {{ $testType === 'full' ? 'selected' : '' }}>Full Mock Tests</option>
                <option value="section" {{ $testType === 'section' ? 'selected' : '' }}>Section Tests</option>
            </select>
        </div>
        <button type="submit" class="px-5 py-2 bg-[#C8102E] hover:bg-[#A00E27] text-white text-sm font-semibold rounded-lg shadow-sm transition">
            <i class="fas fa-filter mr-1.5 text-xs"></i> Filter
        </button>
        @if(request()->hasAny(['search', 'date', 'type']))
        <a href="{{ route('branch.tests.index') }}" class="text-sm text-gray-500 hover:text-[#C8102E] font-medium px-2 py-2 transition">
            <i class="fas fa-times mr-1"></i> Clear
        </a>
        @endif
    </form>
</div>

{{-- Full Mock Tests Section --}}
@if($testType !== 'section')
<section class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <header class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/60 to-violet-50/60">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-sm"></i>
                </span>
                Full Mock Tests
                <span class="px-2 py-0.5 bg-white border border-indigo-200 text-indigo-700 rounded-full text-xs font-semibold">
                    {{ $fullTestAttempts instanceof \Illuminate\Pagination\LengthAwarePaginator ? $fullTestAttempts->total() : $fullTestAttempts->count() }}
                </span>
            </h2>
        </div>
    </header>

    @if($fullTestAttempts->count() > 0)
    <div class="divide-y divide-gray-100">
        @foreach($fullTestAttempts as $fullTest)
        <article class="p-5 hover:bg-gray-50/60 transition">
            {{-- Card Header --}}
            <div class="flex items-start justify-between gap-4 mb-4">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 ring-1 ring-indigo-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-clipboard-list text-indigo-500"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900 truncate">{{ $fullTest->fullTest->title ?? 'Full Mock Test' }}</h3>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 mt-1">
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fas fa-user text-gray-400"></i>
                                <span class="text-gray-700 font-medium">{{ $fullTest->user->name ?? 'Unknown' }}</span>
                            </span>
                            <span class="text-gray-300">·</span>
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fas fa-id-card text-gray-400"></i>
                                <span class="font-mono text-[11px]">{{ $fullTest->user->offlineEnrollment->student_id ?? '-' }}</span>
                            </span>
                            <span class="text-gray-300">·</span>
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fas fa-clock text-gray-400"></i>
                                {{ $fullTest->created_at->format('M d, Y · h:i A') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    @if($fullTest->overall_band_score)
                    <div class="text-right pr-3 border-r border-gray-200">
                        <p class="text-2xl font-bold text-[#C8102E] leading-none">{{ $fullTest->overall_band_score }}</p>
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide mt-1">Overall</p>
                    </div>
                    @endif
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $statusBadge[$fullTest->status] ?? $statusBadge['abandoned'] }}">
                        {{ $statusLabel[$fullTest->status] ?? ucfirst($fullTest->status) }}
                    </span>
                </div>
            </div>

            {{-- Sections Grid --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 ml-0 lg:ml-14">
                @foreach(['listening', 'reading', 'writing', 'speaking'] as $sectionSlug)
                    @php
                        $sa = $fullTest->sectionAttempts->first(fn($s) => $s->section_type === $sectionSlug);
                        $t  = $sectionTheme[$sectionSlug];
                        $studAttempt = $sa?->studentAttempt;
                        $isCompleted = $studAttempt?->status === 'completed';
                        $hasScore = $isCompleted && $studAttempt?->band_score;
                    @endphp
                    <div class="{{ $t['bg'] }} {{ $t['border'] }} border rounded-lg px-3 py-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <i class="fas {{ $t['icon'] }} {{ $t['accent'] }} text-sm"></i>
                                <span class="text-xs font-semibold {{ $t['text'] }} capitalize">{{ $sectionSlug }}</span>
                            </div>
                            @if($hasScore)
                                <span class="text-lg font-bold {{ $t['text'] }} leading-none">{{ $studAttempt->band_score }}</span>
                            @elseif($studAttempt && $isCompleted)
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $statusBadge['in_progress'] }}">Pending</span>
                            @elseif($studAttempt)
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $statusBadge['in_progress'] }}">{{ $statusLabel['in_progress'] }}</span>
                            @else
                                <span class="text-xs text-gray-400 font-medium">—</span>
                            @endif
                        </div>
                        @if($studAttempt)
                            <p class="text-[10px] mt-1.5 font-medium {{ $isCompleted ? 'text-emerald-600' : 'text-amber-600' }}">
                                <i class="fas {{ $isCompleted ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                                {{ $isCompleted ? 'Done' : 'In Progress' }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </article>
        @endforeach
    </div>

    @if($fullTestAttempts instanceof \Illuminate\Pagination\LengthAwarePaginator && $fullTestAttempts->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
        {{ $fullTestAttempts->appends(request()->except('full_page'))->links() }}
    </div>
    @endif
    @else
    <div class="px-6 py-16 text-center">
        <div class="w-14 h-14 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-3">
            <i class="fas fa-clipboard-list text-gray-400 text-xl"></i>
        </div>
        <p class="text-sm font-medium text-gray-600">No full mock tests yet</p>
        <p class="text-xs text-gray-400 mt-1">Tests will appear here once students start them</p>
    </div>
    @endif
</section>
@endif

{{-- Section Tests --}}
@if($testType !== 'full')
<section class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <header class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-emerald-50/60 to-teal-50/60">
        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <i class="fas fa-puzzle-piece text-sm"></i>
            </span>
            Individual Section Tests
            <span class="px-2 py-0.5 bg-white border border-emerald-200 text-emerald-700 rounded-full text-xs font-semibold">
                {{ $sectionAttempts instanceof \Illuminate\Pagination\LengthAwarePaginator ? $sectionAttempts->total() : $sectionAttempts->count() }}
            </span>
        </h2>
        <p class="text-xs text-gray-500 mt-1.5 ml-10">Standalone section practice tests (not part of full mock tests)</p>
    </header>

    @if($sectionAttempts->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50/70">
                <tr>
                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Section</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Test</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Taken At</th>
                    <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Score</th>
                    <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($sectionAttempts as $attempt)
                @if($attempt->user)
                @php
                    $slug = $attempt->testSet->section->slug ?? 'unknown';
                    $t = $sectionTheme[$slug] ?? ['icon' => 'fa-file', 'badge' => 'bg-gray-100 text-gray-600', 'text' => 'text-gray-700'];
                @endphp
                <tr class="hover:bg-gray-50/60 transition">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-semibold text-gray-900">{{ $attempt->user->name }}</p>
                        <p class="text-[11px] text-gray-500 font-mono">{{ $attempt->user->offlineEnrollment->student_id ?? '-' }}</p>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold {{ $t['badge'] }}">
                            <i class="fas {{ $t['icon'] }} text-[10px]"></i>
                            {{ ucfirst($slug) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <p class="text-sm text-gray-800 font-medium">{{ $attempt->testSet->title ?? 'Unknown Test' }}</p>
                    </td>
                    <td class="px-5 py-3.5">
                        <p class="text-sm text-gray-800">{{ $attempt->created_at->format('M d, Y') }}</p>
                        <p class="text-[11px] text-gray-500">{{ $attempt->created_at->format('h:i A') }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if($attempt->band_score)
                            <span class="text-lg font-bold {{ $t['text'] }}">{{ $attempt->band_score }}</span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-block px-2.5 py-1 text-[11px] font-semibold rounded-full {{ $statusBadge[$attempt->status] ?? $statusBadge['abandoned'] }}">
                            {{ $statusLabel[$attempt->status] ?? ucfirst($attempt->status) }}
                        </span>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>

    @if($sectionAttempts instanceof \Illuminate\Pagination\LengthAwarePaginator && $sectionAttempts->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
        {{ $sectionAttempts->appends(request()->except('section_page'))->links() }}
    </div>
    @endif
    @else
    <div class="px-6 py-16 text-center">
        <div class="w-14 h-14 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-3">
            <i class="fas fa-puzzle-piece text-gray-400 text-xl"></i>
        </div>
        <p class="text-sm font-medium text-gray-600">No section tests yet</p>
        <p class="text-xs text-gray-400 mt-1">Practice attempts will appear here</p>
    </div>
    @endif
</section>
@endif
@endsection
