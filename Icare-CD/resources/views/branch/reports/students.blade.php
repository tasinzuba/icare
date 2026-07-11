@extends('layouts.branch')

@section('title', 'Student Performance')

@section('content')
<div class="mb-6">
    <a href="{{ route('branch.reports.index') }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Reports
    </a>
</div>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Student Performance</h1>
        <p class="text-gray-600">Track student progress and scores</p>
    </div>
    <a href="{{ route('branch.reports.export', ['type' => 'students']) }}"
       class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
        <i class="fas fa-download mr-2"></i> Export CSV
    </a>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tests Taken</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Avg Score</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Best Score</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Valid Until</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($students as $student)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-indigo-600 font-semibold">{{ substr($student['name'], 0, 1) }}</span>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium text-gray-800">{{ $student['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $student['student_id'] }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="text-lg font-semibold text-gray-800">{{ $student['tests_taken'] }}</span>
                    <span class="text-gray-500">/ {{ $student['tests_allowed'] }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                    @if($student['avg_score'])
                    <span class="text-xl font-bold text-indigo-600">{{ number_format($student['avg_score'], 1) }}</span>
                    @else
                    <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-center">
                    @if($student['best_score'])
                    <span class="text-xl font-bold text-green-600">{{ $student['best_score'] }}</span>
                    @else
                    <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-center">
                    @php
                        $statusColor = match($student['status']) {
                            'active' => 'green',
                            'inactive' => 'gray',
                            'expired' => 'red',
                            'completed' => 'blue',
                            default => 'gray'
                        };
                    @endphp
                    <span class="px-2 py-1 text-xs rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                        {{ ucfirst($student['status']) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-center text-sm text-gray-600">
                    {{ $student['valid_until']->format('M d, Y') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <p>No students found</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
