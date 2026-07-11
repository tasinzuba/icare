@extends('layouts.branch')

@section('title', 'Monthly Report')

@section('content')
<div class="mb-6">
    <a href="{{ route('branch.reports.index') }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Reports
    </a>
</div>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Monthly Report</h1>
        <p class="text-gray-600">{{ $month->format('F Y') }}</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <input type="month" name="month" value="{{ $month->format('Y-m') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            <i class="fas fa-search"></i>
        </button>
    </form>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="text-center">
            <p class="text-4xl font-bold text-blue-600">{{ $stats['enrollments'] }}</p>
            <p class="text-gray-600 mt-1">Total Enrollments</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="text-center">
            <p class="text-4xl font-bold text-green-600">{{ $stats['tests'] }}</p>
            <p class="text-gray-600 mt-1">Tests Taken</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="text-center">
            <p class="text-4xl font-bold text-purple-600">৳{{ number_format($stats['revenue']) }}</p>
            <p class="text-gray-600 mt-1">Revenue</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="text-center">
            <p class="text-4xl font-bold text-yellow-600">{{ $stats['avg_tests_per_day'] }}</p>
            <p class="text-gray-600 mt-1">Avg Tests/Day</p>
        </div>
    </div>
</div>

<!-- Daily Breakdown Chart -->
<div class="bg-white rounded-xl shadow-md p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Daily Activity</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Enrollments</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Tests</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Activity</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($dailyData as $day)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-gray-800">
                        {{ \Carbon\Carbon::parse($day['date'])->format('M d, D') }}
                    </td>
                    <td class="px-4 py-2 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                            {{ $day['enrollments'] }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                            {{ $day['tests'] }}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        <div class="flex items-center">
                            <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[200px]">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(($day['enrollments'] + $day['tests']) * 10, 100) }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
