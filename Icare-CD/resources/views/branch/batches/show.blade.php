@extends('layouts.branch')

@section('title', 'Batch: ' . $batch->name)

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('branch.batches.index') }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Batches
    </a>
    <div class="flex gap-2">
        <a href="{{ route('branch.batches.edit', $batch) }}" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
            <i class="fas fa-edit mr-1"></i> Edit
        </a>
        <a href="{{ route('branch.students.create', ['batch_id' => $batch->id]) }}" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
            <i class="fas fa-user-plus mr-1"></i> Add Student
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

<!-- Batch Info Card -->
<div class="bg-white rounded-xl shadow-md p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-gray-800">{{ $batch->name }}</h1>
        @if($batch->status === 'active')
            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-700">Active</span>
        @else
            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-500">Archived</span>
        @endif
    </div>
    @if($batch->description)
        <p class="text-gray-500 mb-4">{{ $batch->description }}</p>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $batch->full_tests_allowed }}</p>
            <p class="text-xs text-blue-700">Full Tests</p>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $batch->section_tests_allowed }}</p>
            <p class="text-xs text-green-700">Section Tests</p>
        </div>
        <div class="bg-orange-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-orange-600">{{ $batch->validity_days }}</p>
            <p class="text-xs text-orange-700">Days Validity</p>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-purple-600">{{ $enrollments->total() }}</p>
            <p class="text-xs text-purple-700">Total Students</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-gray-600">{{ $batch->created_at->format('M d, Y') }}</p>
            <p class="text-xs text-gray-500">Created</p>
        </div>
    </div>

    <!-- Section Limits Breakdown -->
    @if($batch->section_test_limits)
    <div class="mt-4 grid grid-cols-4 gap-2">
        @foreach($batch->section_test_limits as $section => $limit)
        <div class="text-center p-2 bg-gray-50 rounded">
            <span class="text-xs text-gray-500 uppercase">{{ $section }}</span>
            <span class="block text-sm font-semibold text-gray-700">{{ $limit }}</span>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Allowed Tests -->
    @if(count($fullTestNames))
    <div class="mt-4">
        <h3 class="text-sm font-semibold text-gray-600 mb-2">Allowed Full Tests:</h3>
        <div class="flex flex-wrap gap-1.5">
            @foreach($fullTestNames as $name)
                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">{{ $name }}</span>
            @endforeach
        </div>
    </div>
    @endif

    @if(count($sectionTestNames))
    <div class="mt-4">
        <h3 class="text-sm font-semibold text-gray-600 mb-2">Allowed Section Tests:</h3>
        <div class="flex flex-wrap gap-1.5">
            @foreach($sectionTestNames as $name)
                <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">{{ $name }}</span>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Students Table -->
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Students in this Batch ({{ $enrollments->total() }})</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Student ID</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Valid Until</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($enrollments as $enrollment)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900">{{ $enrollment->student->name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-400">{{ $enrollment->student->email ?? '' }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $enrollment->student_id }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($enrollment->status === 'active')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Active</span>
                        @elseif($enrollment->status === 'expired')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Expired</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">{{ ucfirst($enrollment->status) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-600">
                        {{ $enrollment->valid_until?->format('M d, Y') ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('branch.students.show', $enrollment) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        <p>No students in this batch yet</p>
                        <a href="{{ route('branch.students.create', ['batch_id' => $batch->id]) }}" class="text-indigo-600 hover:underline text-sm mt-2 inline-block">
                            <i class="fas fa-plus mr-1"></i> Add first student
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($enrollments->hasPages())
    <div class="px-6 py-4 border-t">{{ $enrollments->links() }}</div>
    @endif
</div>
@endsection
