@extends('layouts.offline-student')

@section('title', $enrollment ? 'Enrollment Expired' : 'No Active Enrollment')

@section('content')

{{-- Expiry Notice --}}
<div class="bg-white border border-amber-200 rounded-xl p-5 mb-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-amber-50 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-clock text-amber-600 text-lg"></i>
            </div>
            <div>
                @if($enrollment)
                    <h1 class="text-lg font-semibold text-gray-900">
                        @if($enrollment->status === 'completed')
                            All Tests Completed
                        @else
                            Enrollment Expired
                        @endif
                    </h1>
                    <p class="text-[11px] text-gray-400 mt-0.5">
                        @if($enrollment->status === 'completed')
                            You have completed all your allocated tests.
                        @else
                            Expired on {{ $enrollment->valid_until->format('M d, Y') }}
                        @endif
                        <span class="mx-1">&middot;</span>
                        {{ $enrollment->student_id }}
                    </p>
                @else
                    <h1 class="text-lg font-semibold text-gray-900">No Active Enrollment</h1>
                    <p class="text-[11px] text-gray-400 mt-0.5">Please contact your branch to get enrolled.</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-4 sm:gap-5 text-center">
            <div>
                <p class="text-xl font-bold text-gray-900">{{ $stats['total_tests'] }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Attempts</p>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <div>
                <p class="text-xl font-bold text-emerald-600">{{ $stats['completed_tests'] }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Completed</p>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <div>
                <p class="text-xl font-bold text-gray-900">{{ $stats['average_score'] ?? '-' }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Avg. Band</p>
            </div>
        </div>
    </div>
</div>

{{-- Branch Contact --}}
@if($branch)
<div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
    <div class="flex items-start gap-3">
        <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-building text-blue-600 text-sm"></i>
        </div>
        <div class="flex-1">
            <h2 class="text-sm font-semibold text-gray-900">Renew Your Enrollment</h2>
            <p class="text-[11px] text-gray-400 mt-0.5 mb-3">Contact your branch to continue taking tests.</p>
            <div class="bg-gray-50 border border-gray-100 rounded-lg p-3.5">
                <p class="text-sm font-medium text-gray-800 mb-2">{{ $branch->name }}</p>
                <div class="space-y-1.5 text-[12px] text-gray-500">
                    @if($branch->address)
                    <p><i class="fas fa-location-dot w-4 text-gray-400 text-[10px]"></i> {{ $branch->address }}{{ $branch->city ? ', ' . $branch->city : '' }}</p>
                    @endif
                    @if($branch->phone)
                    <p><i class="fas fa-phone w-4 text-gray-400 text-[10px]"></i> <a href="tel:{{ $branch->phone }}" class="text-blue-600 hover:underline">{{ $branch->phone }}</a></p>
                    @endif
                    @if($branch->email)
                    <p><i class="fas fa-envelope w-4 text-gray-400 text-[10px]"></i> <a href="mailto:{{ $branch->email }}" class="text-blue-600 hover:underline">{{ $branch->email }}</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Test Results --}}
<div class="bg-white border border-gray-200 rounded-xl mb-5 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-history text-gray-500 text-sm"></i>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Your Test Results</h2>
                <p class="text-[11px] text-gray-400">View all your previous test results</p>
            </div>
        </div>
        <a href="{{ route('student.results') }}" class="text-[11px] text-brand-600 hover:text-brand-700 font-medium">
            View all <i class="fas fa-arrow-right ml-0.5 text-[9px]"></i>
        </a>
    </div>

    @if($fullTestAttempts->count() > 0 || $sectionAttempts->count() > 0)
    <div class="divide-y divide-gray-50">
        {{-- Full Mock Test Results --}}
        @foreach($fullTestAttempts as $attempt)
        <div class="px-5 py-3 flex items-center gap-3">
            <div class="w-8 h-8 bg-brand-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-layer-group text-brand-500 text-xs"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ $attempt->fullTest->title ?? 'Full Mock Test' }}</p>
                <div class="flex items-center gap-2 mt-0.5">
                    <p class="text-[10px] text-gray-400">{{ $attempt->created_at->format('M d, Y') }}</p>
                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-medium
                        @if($attempt->status === 'completed') bg-emerald-50 text-emerald-600
                        @elseif($attempt->status === 'in_progress') bg-blue-50 text-blue-600
                        @else bg-gray-100 text-gray-500 @endif">
                        {{ ucfirst($attempt->status) }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                @if($attempt->overall_band_score)
                    <span class="text-base font-bold text-brand-600">{{ $attempt->overall_band_score }}</span>
                @endif
                <a href="{{ route('student.full-test.results', $attempt) }}"
                   class="px-3 py-1.5 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition text-[11px] font-medium">
                    <i class="fas fa-eye mr-1 text-[9px]"></i> View
                </a>
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

        {{-- Section Test Results --}}
        @php
            $activityIcons = [
                'listening' => ['icon' => 'fa-headphones', 'bg' => 'bg-blue-50', 'text' => 'text-blue-500'],
                'reading' => ['icon' => 'fa-book-open', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-500'],
                'writing' => ['icon' => 'fa-pen-fancy', 'bg' => 'bg-amber-50', 'text' => 'text-amber-500'],
                'speaking' => ['icon' => 'fa-microphone', 'bg' => 'bg-violet-50', 'text' => 'text-violet-500'],
            ];
        @endphp
        @foreach($sectionAttempts->take(10) as $attempt)
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
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $attempt->created_at->format('M d, Y') }}</p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    @if($attempt->band_score)
                        <span class="text-sm font-bold text-gray-700">{{ $attempt->band_score }}</span>
                    @endif
                    @if($attempt->status === 'completed')
                    <a href="{{ route('student.results.show', $attempt) }}"
                       class="px-3 py-1.5 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition text-[11px] font-medium">
                        View
                    </a>
                    @endif
                </div>
            </div>
        @endforeach

        @if($sectionAttempts->count() > 10)
        <div class="px-5 py-3 text-center">
            <a href="{{ route('student.results') }}" class="text-[11px] text-brand-600 hover:text-brand-700 font-medium">
                View All Results <i class="fas fa-arrow-right ml-0.5 text-[9px]"></i>
            </a>
        </div>
        @endif
    </div>
    @else
    <div class="px-5 py-10 text-center">
        <i class="fas fa-clipboard-list text-gray-300 text-2xl mb-2"></i>
        <p class="text-sm text-gray-400">No test results found</p>
    </div>
    @endif
</div>
@endsection
