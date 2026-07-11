@extends('layouts.offline-student')

@section('title', 'My Results')

@section('content')
<div class="mb-6">
    <a href="{{ route('offline.dashboard') }}" class="text-indigo-600 hover:text-indigo-700">
        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
    </a>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-6">My Test Results</h1>

<!-- Full Tests Section -->
@if($fullTestAttempts->count() > 0)
<div class="bg-white rounded-xl shadow-sm mb-6">
    <div class="p-4 border-b bg-gradient-to-r from-indigo-500 to-purple-500 rounded-t-xl">
        <h2 class="text-lg font-bold text-white flex items-center">
            <i class="fas fa-layer-group mr-2"></i> Full Mock Tests
        </h2>
        <p class="text-indigo-100 text-sm">Complete IELTS tests with all sections</p>
    </div>

    <div class="divide-y divide-gray-100">
        @foreach($fullTestAttempts as $fullAttempt)
            <div class="p-4 hover:bg-gray-50 transition">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-award text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-semibold text-gray-800">{{ $fullAttempt->fullTest->title ?? 'Full Mock Test' }}</p>
                            <p class="text-xs text-gray-400">{{ $fullAttempt->created_at->format('M d, Y - h:i A') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        @if($fullAttempt->overall_band_score)
                            <p class="text-3xl font-bold text-indigo-600">{{ $fullAttempt->overall_band_score }}</p>
                            <p class="text-xs text-gray-500">Overall Band</p>
                        @elseif($fullAttempt->status === 'completed')
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">Evaluating</span>
                        @else
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">{{ ucfirst($fullAttempt->status) }}</span>
                        @endif
                    </div>
                </div>

                <!-- Section Scores -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-3">
                    @php
                        $sectionStyles = [
                            'listening' => ['icon' => 'fa-headphones', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'],
                            'reading' => ['icon' => 'fa-book-open', 'color' => 'text-green-600', 'bg' => 'bg-green-50'],
                            'writing' => ['icon' => 'fa-pen', 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-50'],
                            'speaking' => ['icon' => 'fa-microphone', 'color' => 'text-purple-600', 'bg' => 'bg-purple-50'],
                        ];
                    @endphp
                    @foreach(['listening', 'reading', 'writing', 'speaking'] as $section)
                        @php
                            $score = $fullAttempt->{$section . '_score'};
                            $style = $sectionStyles[$section];
                            $sectionAttempt = $fullAttempt->sectionAttempts->where('section_type', $section)->first();
                            $testName = $sectionAttempt?->studentAttempt?->testSet?->name;
                        @endphp
                        <div class="{{ $style['bg'] }} rounded-lg p-2 text-center">
                            <i class="fas {{ $style['icon'] }} {{ $style['color'] }} text-sm"></i>
                            <p class="text-xs text-gray-600 capitalize font-medium">{{ $section }}</p>
                            @if($testName)
                                <p class="text-xs text-gray-500 truncate" title="{{ $testName }}">{{ Str::limit($testName, 15) }}</p>
                            @endif
                            <p class="font-bold {{ $style['color'] }} text-lg">{{ $score ?? '-' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Section Tests -->
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-4 border-b">
        <h2 class="text-lg font-bold text-gray-800 flex items-center">
            <i class="fas fa-file-alt mr-2 text-gray-500"></i> Section Tests
        </h2>
        <p class="text-gray-500 text-sm">Individual section practice tests</p>
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($sectionAttempts as $attempt)
            <div class="p-4 hover:bg-gray-50 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        @php
                            $sectionSlug = $attempt->testSet->section->slug ?? 'general';
                            $icons = [
                                'listening' => ['icon' => 'fa-headphones', 'color' => 'text-blue-600', 'bg' => 'bg-blue-100'],
                                'reading' => ['icon' => 'fa-book-open', 'color' => 'text-green-600', 'bg' => 'bg-green-100'],
                                'writing' => ['icon' => 'fa-pen', 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-100'],
                                'speaking' => ['icon' => 'fa-microphone', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100'],
                            ];
                            $style = $icons[$sectionSlug] ?? ['icon' => 'fa-file', 'color' => 'text-gray-600', 'bg' => 'bg-gray-100'];
                        @endphp
                        <div class="w-12 h-12 {{ $style['bg'] }} rounded-full flex items-center justify-center">
                            <i class="fas {{ $style['icon'] }} {{ $style['color'] }} text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-gray-800">{{ $attempt->testSet->name ?? 'Test' }}</p>
                                <span class="px-2 py-0.5 text-xs {{ $style['bg'] }} {{ $style['color'] }} rounded">{{ $attempt->testSet->section->name ?? 'Section' }}</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ $attempt->created_at->format('M d, Y - h:i A') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        @if($attempt->band_score)
                            <p class="text-3xl font-bold text-indigo-600">{{ $attempt->band_score }}</p>
                            <p class="text-xs text-gray-500">Band Score</p>
                        @elseif($attempt->status === 'completed')
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">Pending</span>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">In Progress</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            @if($fullTestAttempts->count() === 0)
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-clipboard-list text-5xl mb-3"></i>
                <p class="text-lg">No test results yet</p>
                <a href="{{ route('offline.dashboard') }}" class="text-indigo-600 hover:text-indigo-700 mt-2 inline-block">
                    Take your first test
                </a>
            </div>
            @else
            <div class="text-center py-8 text-gray-500">
                <p>No individual section tests taken</p>
            </div>
            @endif
        @endforelse
    </div>

    @if($sectionAttempts->hasPages())
        <div class="p-4 border-t">
            {{ $sectionAttempts->links() }}
        </div>
    @endif
</div>
@endsection
