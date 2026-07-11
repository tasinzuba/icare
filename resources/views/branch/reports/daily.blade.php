@extends('layouts.branch')

@section('title', 'Daily Report')

@section('content')
<div class="mb-6">
    <a href="{{ route('branch.reports.index') }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Reports
    </a>
</div>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Daily Report</h1>
        <p class="text-gray-600">{{ $date->format('l, F d, Y') }}</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            <i class="fas fa-search"></i>
        </button>
    </form>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center">
            <div class="p-4 bg-blue-100 rounded-full">
                <i class="fas fa-user-plus text-blue-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-3xl font-bold text-gray-800">{{ $stats['enrollments'] }}</p>
                <p class="text-gray-600">New Enrollments</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center">
            <div class="p-4 bg-green-100 rounded-full">
                <i class="fas fa-clipboard-check text-green-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-3xl font-bold text-gray-800">{{ $stats['tests'] }}</p>
                <p class="text-gray-600">Tests Taken</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center">
            <div class="p-4 bg-purple-100 rounded-full">
                <i class="fas fa-money-bill-wave text-purple-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-3xl font-bold text-gray-800">৳{{ number_format($stats['revenue']) }}</p>
                <p class="text-gray-600">Revenue</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Enrollments -->
    <div class="bg-white rounded-xl shadow-md">
        <div class="p-4 border-b bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-800">Enrollments</h2>
        </div>
        <div class="p-4">
            @forelse($enrollments as $enrollment)
            <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b' : '' }}">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <span class="text-indigo-600 font-semibold">{{ substr($enrollment->student->name, 0, 1) }}</span>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-800">{{ $enrollment->student->name }}</p>
                        <p class="text-sm text-gray-500">Enrolled by {{ $enrollment->enrolledByUser->name ?? 'System' }}</p>
                    </div>
                </div>
                <p class="text-sm text-gray-500">{{ $enrollment->created_at->format('h:i A') }}</p>
            </div>
            @empty
            <p class="text-center text-gray-500 py-4">No enrollments on this day</p>
            @endforelse
        </div>
    </div>

    <!-- Tests -->
    <div class="bg-white rounded-xl shadow-md">
        <div class="p-4 border-b bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-800">Tests</h2>
        </div>
        <div class="p-4">
            @forelse($tests as $test)
            <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b' : '' }}">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-alt text-green-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-800">{{ $test->user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $test->testSet->section->name ?? 'Full Test' }}</p>
                    </div>
                </div>
                <div class="text-right">
                    @if($test->band_score)
                    <span class="text-lg font-bold text-indigo-600">{{ $test->band_score }}</span>
                    @else
                    <span class="text-sm text-yellow-600">In Progress</span>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-center text-gray-500 py-4">No tests on this day</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
