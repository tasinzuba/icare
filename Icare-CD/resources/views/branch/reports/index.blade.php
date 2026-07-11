@extends('layouts.branch')

@section('title', 'Reports')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Reports</h1>
    <p class="text-gray-600">View branch performance reports</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Daily Report -->
    <a href="{{ route('branch.reports.daily') }}" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center mb-4">
            <div class="p-4 bg-blue-100 rounded-full">
                <i class="fas fa-calendar-day text-blue-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-xl font-semibold text-gray-800">Daily Report</h3>
                <p class="text-gray-500">Today's activity summary</p>
            </div>
        </div>
        <p class="text-sm text-gray-600">View enrollments, tests, and revenue for any specific day.</p>
    </a>

    <!-- Monthly Report -->
    <a href="{{ route('branch.reports.monthly') }}" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center mb-4">
            <div class="p-4 bg-green-100 rounded-full">
                <i class="fas fa-calendar-alt text-green-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-xl font-semibold text-gray-800">Monthly Report</h3>
                <p class="text-gray-500">Monthly statistics</p>
            </div>
        </div>
        <p class="text-sm text-gray-600">View monthly trends, daily breakdown, and performance metrics.</p>
    </a>

    <!-- Student Performance -->
    <a href="{{ route('branch.reports.students') }}" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center mb-4">
            <div class="p-4 bg-purple-100 rounded-full">
                <i class="fas fa-user-graduate text-purple-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-xl font-semibold text-gray-800">Student Performance</h3>
                <p class="text-gray-500">Score analysis</p>
            </div>
        </div>
        <p class="text-sm text-gray-600">Track student progress, average scores, and test completion rates.</p>
    </a>

    <!-- Export Data -->
    <a href="{{ route('branch.reports.export', ['type' => 'students']) }}" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center mb-4">
            <div class="p-4 bg-yellow-100 rounded-full">
                <i class="fas fa-file-export text-yellow-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-xl font-semibold text-gray-800">Export Data</h3>
                <p class="text-gray-500">Download CSV</p>
            </div>
        </div>
        <p class="text-sm text-gray-600">Export student data to CSV for external reporting.</p>
    </a>
</div>
@endsection
