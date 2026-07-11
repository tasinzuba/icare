@extends('layouts.branch')

@section('title', "Today's Tests")

@section('content')
<div class="mb-6">
    <a href="{{ route('branch.tests.index') }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to All Tests
    </a>
</div>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Today's Tests</h1>
        <p class="text-gray-600">{{ now()->format('l, F d, Y') }}</p>
    </div>
    <div class="flex gap-6">
        <div class="text-center bg-white rounded-xl shadow px-6 py-3">
            <p class="text-3xl font-bold text-indigo-600">{{ $fullTestAttempts->count() }}</p>
            <p class="text-sm text-gray-600">Full Tests</p>
        </div>
        <div class="text-center bg-white rounded-xl shadow px-6 py-3">
            <p class="text-3xl font-bold text-green-600">{{ $sectionAttempts->count() }}</p>
            <p class="text-sm text-gray-600">Section Tests</p>
        </div>
    </div>
</div>

{{-- Full Mock Tests Section --}}
<div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
    <div class="p-4 border-b bg-gradient-to-r from-indigo-50 to-purple-50">
        <h2 class="text-lg font-bold text-gray-800 flex items-center">
            <i class="fas fa-clipboard-list text-indigo-600 mr-2"></i>
            Full Mock Tests Today
            <span class="ml-2 px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-normal">
                {{ $fullTestAttempts->count() }}
            </span>
        </h2>
    </div>

    @if($fullTestAttempts->count() > 0)
    <div class="divide-y divide-gray-100">
        @foreach($fullTestAttempts as $fullTest)
        <div class="p-4 hover:bg-gray-50 transition">
            {{-- Full Test Header --}}
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-file-alt text-indigo-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $fullTest->fullTest->title ?? 'Full Mock Test' }}</h3>
                        <div class="flex items-center gap-3 text-sm text-gray-500">
                            <span><i class="fas fa-user mr-1"></i> {{ $fullTest->user->name ?? 'Unknown' }}</span>
                            <span class="text-gray-300">|</span>
                            <span><i class="fas fa-id-card mr-1"></i> {{ $fullTest->user->offlineEnrollment->student_id ?? '-' }}</span>
                            <span class="text-gray-300">|</span>
                            <span><i class="fas fa-clock mr-1"></i> {{ $fullTest->created_at->format('h:i A') }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    @if($fullTest->overall_band_score)
                    <div class="text-center">
                        <p class="text-3xl font-bold text-indigo-600">{{ $fullTest->overall_band_score }}</p>
                        <p class="text-xs text-gray-500">Overall Band</p>
                    </div>
                    @endif
                    <span class="px-3 py-1 rounded-full text-xs font-medium
                        @if($fullTest->status === 'completed') bg-green-100 text-green-700
                        @elseif($fullTest->status === 'in_progress') bg-yellow-100 text-yellow-700
                        @else bg-gray-100 text-gray-600 @endif">
                        {{ ucfirst($fullTest->status) }}
                    </span>
                </div>
            </div>

            {{-- Section Scores Grid --}}
            <div class="grid grid-cols-4 gap-3 ml-16">
                @php
                    $sectionOrder = ['listening', 'reading', 'writing', 'speaking'];
                    $sectionIcons = [
                        'listening' => 'fa-headphones',
                        'reading' => 'fa-book-open',
                        'writing' => 'fa-pen-nib',
                        'speaking' => 'fa-microphone'
                    ];
                    $sectionColors = [
                        'listening' => 'purple',
                        'reading' => 'blue',
                        'writing' => 'green',
                        'speaking' => 'orange'
                    ];
                @endphp

                @foreach($sectionOrder as $sectionSlug)
                    @php
                        // Use section_type directly from FullTestSectionAttempt
                        $sectionAttempt = $fullTest->sectionAttempts->first(function ($sa) use ($sectionSlug) {
                            return $sa->section_type === $sectionSlug;
                        });
                        $color = $sectionColors[$sectionSlug] ?? 'gray';
                    @endphp
                    <div class="bg-{{ $color }}-50 rounded-lg p-3 border border-{{ $color }}-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas {{ $sectionIcons[$sectionSlug] ?? 'fa-file' }} text-{{ $color }}-500"></i>
                                <span class="text-sm font-medium text-gray-700 capitalize">{{ $sectionSlug }}</span>
                            </div>
                            @if($sectionAttempt)
                                @if($sectionAttempt->studentAttempt)
                                    @if($sectionAttempt->studentAttempt->status === 'completed' && $sectionAttempt->studentAttempt->band_score)
                                        <span class="text-lg font-bold text-{{ $color }}-600">
                                            {{ $sectionAttempt->studentAttempt->band_score }}
                                        </span>
                                    @else
                                        <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full">Pending</span>
                                    @endif
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">Not Started</span>
                                @endif
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </div>
                        @if($sectionAttempt && $sectionAttempt->studentAttempt)
                        <p class="text-xs text-gray-500 mt-1">
                            @if($sectionAttempt->studentAttempt->status === 'completed')
                                <i class="fas fa-check-circle text-green-500 mr-1"></i> Done
                            @else
                                <i class="fas fa-clock text-yellow-500 mr-1"></i> In Progress
                            @endif
                        </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="p-8 text-center text-gray-500">
        <i class="fas fa-clipboard-list text-4xl mb-2 text-gray-300"></i>
        <p>No full mock tests today</p>
    </div>
    @endif
</div>

{{-- Section Tests Section --}}
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="p-4 border-b bg-gradient-to-r from-green-50 to-teal-50">
        <h2 class="text-lg font-bold text-gray-800 flex items-center">
            <i class="fas fa-puzzle-piece text-green-600 mr-2"></i>
            Individual Section Tests Today
            <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-sm font-normal">
                {{ $sectionAttempts->count() }}
            </span>
        </h2>
        <p class="text-sm text-gray-500 mt-1">Standalone section practice tests (not part of full mock tests)</p>
    </div>

    @if($sectionAttempts->count() > 0)
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Section</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Score</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($sectionAttempts as $attempt)
            @if($attempt->user)
            @php
                $sectionSlug = $attempt->testSet->section->slug ?? 'unknown';
                $sectionColors = [
                    'listening' => 'purple',
                    'reading' => 'blue',
                    'writing' => 'green',
                    'speaking' => 'orange'
                ];
                $sectionIcons = [
                    'listening' => 'fa-headphones',
                    'reading' => 'fa-book-open',
                    'writing' => 'fa-pen-nib',
                    'speaking' => 'fa-microphone'
                ];
                $color = $sectionColors[$sectionSlug] ?? 'gray';
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $attempt->created_at->format('h:i A') }}</p>
                </td>
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $attempt->user->name }}</p>
                    <p class="text-sm text-gray-500">{{ $attempt->user->offlineEnrollment->student_id ?? '-' }}</p>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center gap-2 px-3 py-1 bg-{{ $color }}-50 text-{{ $color }}-700 rounded-full text-sm">
                        <i class="fas {{ $sectionIcons[$sectionSlug] ?? 'fa-file' }}"></i>
                        {{ ucfirst($sectionSlug) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <p class="text-gray-800">{{ $attempt->testSet->title ?? 'Unknown Test' }}</p>
                </td>
                <td class="px-6 py-4 text-center">
                    @if($attempt->band_score)
                    <span class="text-xl font-bold text-{{ $color }}-600">{{ $attempt->band_score }}</span>
                    @else
                    <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-center">
                    @if($attempt->status === 'completed')
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Completed</span>
                    @else
                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">In Progress</span>
                    @endif
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
    @else
    <div class="p-8 text-center text-gray-500">
        <i class="fas fa-puzzle-piece text-4xl mb-2 text-gray-300"></i>
        <p>No section tests today</p>
    </div>
    @endif
</div>
@endsection
