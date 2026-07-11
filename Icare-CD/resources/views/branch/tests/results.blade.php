@extends('layouts.branch')

@section('title', 'Test Results')

@section('content')
<div class="mb-6">
    <a href="{{ route('branch.tests.index') }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Tests
    </a>
</div>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Test Results</h1>
                <p class="text-gray-600">{{ $attempt->created_at->format('F d, Y - h:i A') }}</p>
            </div>
            @if($attempt->band_score)
            <div class="text-center">
                <p class="text-5xl font-bold text-indigo-600">{{ $attempt->band_score }}</p>
                <p class="text-gray-600">Band Score</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Student Info -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Student Information</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-500">Name</p>
                <p class="font-medium">{{ $attempt->user->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Student ID</p>
                <p class="font-medium">{{ $attempt->user->offlineEnrollment->student_id ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p class="font-medium">{{ $attempt->user->email ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Test Type</p>
                <p class="font-medium">{{ $attempt->testSet->section->name ?? 'Full Test' }}</p>
            </div>
        </div>
    </div>

    <!-- Section Scores (if full test) -->
    @if(isset($sectionScores) && count($sectionScores) > 0)
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Section Breakdown</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($sectionScores as $section => $score)
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-3xl font-bold text-indigo-600">{{ $score ?? '-' }}</p>
                <p class="text-sm text-gray-600">{{ $section }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Status -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Test Status</h2>
        <div class="flex items-center justify-between">
            <div>
                @if($attempt->is_completed)
                <span class="px-4 py-2 text-lg rounded-full bg-green-100 text-green-800">
                    <i class="fas fa-check-circle mr-2"></i> Completed
                </span>
                @else
                <span class="px-4 py-2 text-lg rounded-full bg-yellow-100 text-yellow-800">
                    <i class="fas fa-clock mr-2"></i> In Progress
                </span>
                @endif
            </div>
            <div class="text-right text-sm text-gray-500">
                <p>Started: {{ $attempt->created_at->format('h:i A') }}</p>
                @if($attempt->completed_at)
                <p>Completed: {{ $attempt->completed_at->format('h:i A') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
