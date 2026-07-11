<x-teacher-layout>
    <x-slot:title>Dashboard</x-slot>
    
    <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Teacher Dashboard</h1>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-right">
                <p class="text-sm text-gray-600">Average Rating</p>
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-primary-600">{{ number_format($teacher->rating, 1) }}</span>
                    <svg class="w-5 h-5 text-yellow-400 ml-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </div>
            @if($teacher->is_available)
                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">Available</span>
            @else
                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">Unavailable</span>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <!-- Available to Claim (Branch Evaluations) -->
        @if(isset($stats['unassigned_available']) && $stats['unassigned_available'] > 0)
        <a href="{{ route('teacher.evaluations.pending') }}" class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-lg shadow p-6 border-2 border-orange-200 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-orange-700">Available to Claim</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">{{ $stats['unassigned_available'] }}</p>
                    <p class="text-xs text-orange-600 mt-1">Branch Students</p>
                </div>
                <div class="p-3 bg-orange-500 rounded-full animate-pulse">
                    <i class="fas fa-building text-white"></i>
                </div>
            </div>
        </a>
        @endif

        <!-- Available to Claim (Online Evaluations) -->
        @if(isset($stats['unassigned_online']) && $stats['unassigned_online'] > 0)
        <a href="{{ route('teacher.evaluations.pending') }}" class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-lg shadow p-6 border-2 border-emerald-200 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-emerald-700">Available to Claim</p>
                    <p class="text-3xl font-bold text-emerald-600 mt-2">{{ $stats['unassigned_online'] }}</p>
                    <p class="text-xs text-emerald-600 mt-1">Online Students</p>
                </div>
                <div class="p-3 bg-emerald-500 rounded-full animate-pulse">
                    <i class="fas fa-globe text-white"></i>
                </div>
            </div>
        </a>
        @endif

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending Evaluations</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['pending'] }}</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">In Progress</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['in_progress'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Completed Today</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['completed_today'] }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Tokens Earned This Month</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['earnings_this_month'] }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="{{ route('teacher.evaluations.pending') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">View Pending Evaluations</h3>
            <p class="text-gray-600">Check and complete pending evaluation requests</p>
        </a>

        <a href="{{ route('teacher.evaluations.completed') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Completed Evaluations</h3>
            <p class="text-gray-600">View your evaluation history and feedback</p>
        </a>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Availability Status</h3>
            <form action="{{ route('teacher.toggle-availability') }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    {{ $teacher->is_available ? 'Set as Unavailable' : 'Set as Available' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Recent Evaluations -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Recent Evaluations</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tokens</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentEvaluations as $evaluation)
                        @php
                            $isFullTest = $evaluation->studentAttempt->fullTestSectionAttempt !== null;
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $evaluation->student->name }}</div>
                                <div class="text-sm text-gray-500">{{ $evaluation->student->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">{{ ucfirst($evaluation->studentAttempt->testSet->section->name) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($isFullTest)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        Full Test
                                    </span>
                                @else
                                    <span class="text-sm text-gray-500">Single Section</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($evaluation->status === 'completed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Completed
                                    </span>
                                @elseif($evaluation->status === 'in_progress')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        In Progress
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $evaluation->deadline_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $evaluation->tokens_used }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($evaluation->status !== 'completed')
                                    <a href="{{ route('teacher.evaluations.show', $evaluation) }}" class="text-primary-600 hover:text-primary-900">
                                        Evaluate
                                    </a>
                                @else
                                    <a href="{{ route('teacher.evaluations.show', $evaluation) }}" class="text-gray-600 hover:text-gray-900">
                                        View
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No evaluations found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
</x-teacher-layout>