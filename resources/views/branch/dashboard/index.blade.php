@extends('layouts.branch')

@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-semibold text-gray-900">Welcome back</h1>
    <p class="text-sm text-gray-500 mt-0.5">{{ $branch->name }} Branch Overview</p>
</div>

<!-- AI Credits Card -->
@if(isset($creditSummary))
<div class="bg-white border border-gray-200 rounded-xl p-5 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
        {{-- Left: Balance --}}
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-coins text-blue-700 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">AI Evaluation Credits</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($creditSummary['balance'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">~ ৳{{ number_format($creditSummary['balance_in_bdt'], 0) }} BDT</p>
            </div>
        </div>

        {{-- Middle: Usage Stats --}}
        <div class="flex items-center gap-6 lg:gap-8">
            <div class="text-center">
                <p class="text-lg font-semibold text-gray-900">{{ $creditSummary['usage_this_month']['writing_evaluations'] }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Writing (Month)</p>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <div class="text-center">
                <p class="text-lg font-semibold text-gray-900">{{ $creditSummary['usage_this_month']['speaking_evaluations'] }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Speaking (Month)</p>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <div class="text-center">
                <p class="text-lg font-semibold text-gray-900">{{ number_format($creditSummary['usage_this_month']['total_cost'], 2) }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Used (Month)</p>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <div class="text-center">
                <p class="text-lg font-semibold text-gray-900">{{ number_format($creditSummary['total_used'], 2) }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Total Used</p>
            </div>
        </div>

        {{-- Right: Rates --}}
        <div class="bg-gray-50 border border-gray-100 rounded-lg px-4 py-3">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-2">Rates</p>
            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between gap-6">
                    <span class="text-gray-500"><i class="fas fa-pen-fancy mr-1.5 text-xs"></i>Writing</span>
                    <span class="font-medium text-gray-700">{{ $creditSummary['rates']['writing'] }}</span>
                </div>
                <div class="flex justify-between gap-6">
                    <span class="text-gray-500"><i class="fas fa-microphone mr-1.5 text-xs"></i>Speaking</span>
                    <span class="font-medium text-gray-700">{{ $creditSummary['rates']['speaking'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Low Balance Warning --}}
    @if($creditSummary['balance'] < 5)
    <div class="mt-4 flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
        <i class="fas fa-exclamation-triangle text-amber-600"></i>
        <div>
            <p class="text-sm font-medium text-amber-800">Low Credit Balance</p>
            <p class="text-xs text-amber-600">Your AI evaluation credits are running low. Contact admin for top-up.</p>
        </div>
    </div>
    @endif
</div>
@endif

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-medium">Active Students</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['active_students'] }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-sm"></i>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-medium">Tests Today</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['tests_today'] }}</p>
            </div>
            <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-clipboard-check text-emerald-600 text-sm"></i>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-medium">Pending Payments</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">৳{{ number_format($stats['pending_payments']) }}</p>
            </div>
            <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-amber-600 text-sm"></i>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-medium">This Month Revenue</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">৳{{ number_format($stats['revenue_this_month']) }}</p>
            </div>
            <div class="w-10 h-10 bg-violet-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-violet-600 text-sm"></i>
            </div>
        </div>
    </div>
</div>

<!-- Two Column Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Recent Enrollments -->
    <div class="bg-white border border-gray-200 rounded-xl">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-sm font-semibold text-gray-800">Recent Enrollments</h2>
            <a href="{{ route('branch.students.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                View all <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
            </a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentEnrollments as $enrollment)
            @if($enrollment->student)
            <div class="flex items-center justify-between px-5 py-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <span class="text-gray-600 font-semibold text-xs">{{ strtoupper(substr($enrollment->student->name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $enrollment->student->name }}</p>
                        <p class="text-[11px] text-gray-400">{{ $enrollment->student_id }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block px-2 py-0.5 text-[10px] font-medium rounded-full
                        {{ $enrollment->status === 'active' ? 'bg-emerald-50 text-emerald-700' : '' }}
                        {{ $enrollment->status === 'expired' ? 'bg-red-50 text-red-600' : '' }}
                        {{ $enrollment->status === 'completed' ? 'bg-blue-50 text-blue-600' : '' }}
                        {{ !in_array($enrollment->status, ['active', 'expired', 'completed']) ? 'bg-gray-100 text-gray-500' : '' }}">
                        {{ ucfirst($enrollment->status) }}
                    </span>
                    <p class="text-[10px] text-gray-400 mt-1">{{ $enrollment->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endif
            @empty
            <div class="px-5 py-8 text-center">
                <p class="text-sm text-gray-400">No recent enrollments</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Today's Tests -->
    <div class="bg-white border border-gray-200 rounded-xl">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-sm font-semibold text-gray-800">Today's Tests</h2>
            <a href="{{ route('branch.tests.today') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                View all <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
            </a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($todayTests as $test)
            @if($test->user)
            <div class="flex items-center justify-between px-5 py-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-alt text-gray-500 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $test->user->name }}</p>
                        <p class="text-[11px] text-gray-400">{{ $test->testSet->section->name ?? 'Full Test' }}</p>
                    </div>
                </div>
                <div class="text-right">
                    @if($test->band_score)
                    <span class="text-sm font-bold text-blue-700">{{ $test->band_score }}</span>
                    @else
                    <span class="inline-block px-2 py-0.5 text-[10px] font-medium rounded-full bg-amber-50 text-amber-600">In Progress</span>
                    @endif
                    <p class="text-[10px] text-gray-400 mt-1">{{ $test->created_at->format('h:i A') }}</p>
                </div>
            </div>
            @endif
            @empty
            <div class="px-5 py-8 text-center">
                <p class="text-sm text-gray-400">No tests today</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white border border-gray-200 rounded-xl p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="{{ route('branch.students.create') }}" class="flex items-center gap-3 p-3.5 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50/50 transition group">
            <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition">
                <i class="fas fa-user-plus text-blue-600 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">New Student</span>
        </a>
        <a href="{{ route('branch.payments.due') }}" class="flex items-center gap-3 p-3.5 border border-gray-200 rounded-lg hover:border-amber-300 hover:bg-amber-50/50 transition group">
            <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center group-hover:bg-amber-100 transition">
                <i class="fas fa-receipt text-amber-600 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-700 group-hover:text-amber-700">Due Payments</span>
        </a>
        <a href="{{ route('branch.tests.today') }}" class="flex items-center gap-3 p-3.5 border border-gray-200 rounded-lg hover:border-emerald-300 hover:bg-emerald-50/50 transition group">
            <div class="w-9 h-9 bg-emerald-50 rounded-lg flex items-center justify-center group-hover:bg-emerald-100 transition">
                <i class="fas fa-calendar-day text-emerald-600 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-700 group-hover:text-emerald-700">Today's Tests</span>
        </a>
        <a href="{{ route('branch.reports.daily') }}" class="flex items-center gap-3 p-3.5 border border-gray-200 rounded-lg hover:border-violet-300 hover:bg-violet-50/50 transition group">
            <div class="w-9 h-9 bg-violet-50 rounded-lg flex items-center justify-center group-hover:bg-violet-100 transition">
                <i class="fas fa-chart-pie text-violet-600 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-700 group-hover:text-violet-700">Daily Report</span>
        </a>
    </div>
</div>
@endsection
