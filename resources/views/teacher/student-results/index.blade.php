<x-teacher-layout>
    <x-slot:title>Student Results</x-slot>

    <x-slot:header>
        <h1 class="text-xl font-semibold text-gray-900">Student Results</h1>
    </x-slot>

    <div class="container mx-auto px-4 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
            <!-- Total Results -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
                                <i class="fas fa-clipboard-list text-white"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Results</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $totalAttempts }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evaluated -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Evaluated</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $evaluatedCount }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Evaluation -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Pending Evaluation</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $pendingCount }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Filter Results</h3>
                <form action="{{ route('teacher.student-results.index') }}" method="GET" class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-1 sm:gap-4 lg:grid-cols-6">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search Student</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               placeholder="Name or email..."
                               class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md">
                    </div>

                    <!-- Section Filter -->
                    <div>
                        <label for="section" class="block text-sm font-medium text-gray-700">Section</label>
                        <select id="section" name="section" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md">
                            <option value="">All Sections</option>
                            <option value="listening" {{ request('section') == 'listening' ? 'selected' : '' }}>
                                Listening
                            </option>
                            <option value="reading" {{ request('section') == 'reading' ? 'selected' : '' }}>
                                Reading
                            </option>
                            <option value="writing" {{ request('section') == 'writing' ? 'selected' : '' }}>
                                Writing
                            </option>
                            <option value="speaking" {{ request('section') == 'speaking' ? 'selected' : '' }}>
                                Speaking
                            </option>
                        </select>
                    </div>

                    <!-- Test Type Filter -->
                    <div>
                        <label for="test_type" class="block text-sm font-medium text-gray-700">Test Type</label>
                        <select id="test_type" name="test_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md">
                            <option value="">All Types</option>
                            <option value="free" {{ request('test_type') == 'free' ? 'selected' : '' }}>
                                Free Tests
                            </option>
                            <option value="premium" {{ request('test_type') == 'premium' ? 'selected' : '' }}>
                                Premium Tests
                            </option>
                        </select>
                    </div>

                    <!-- Student Type Filter -->
                    <div>
                        <label for="student_type" class="block text-sm font-medium text-gray-700">Student Type</label>
                        <select id="student_type" name="student_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md">
                            <option value="">All Students</option>
                            <option value="online" {{ request('student_type') == 'online' ? 'selected' : '' }}>
                                Online Students
                            </option>
                            <option value="offline" {{ request('student_type') == 'offline' ? 'selected' : '' }}>
                                Branch Students
                            </option>
                        </select>
                    </div>

                    <!-- Branch Filter -->
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                        <select id="branch_id" name="branch_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }} ({{ $branch->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Evaluation Status Filter -->
                    <div>
                        <label for="evaluation_status" class="block text-sm font-medium text-gray-700">Evaluation Status</label>
                        <select id="evaluation_status" name="evaluation_status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md">
                            <option value="">All</option>
                            <option value="evaluated" {{ request('evaluation_status') == 'evaluated' ? 'selected' : '' }}>
                                Evaluated
                            </option>
                            <option value="pending" {{ request('evaluation_status') == 'pending' ? 'selected' : '' }}>
                                Pending Evaluation
                            </option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                            <i class="fas fa-filter mr-2"></i>
                            Apply Filter
                        </button>

                        @if(request()->hasAny(['section', 'student_type', 'evaluation_status', 'search', 'test_type', 'branch_id']))
                            <a href="{{ route('teacher.student-results.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                <i class="fas fa-times mr-2"></i>
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Student Test Results</h3>
                    <span class="text-sm text-gray-500">{{ $attempts->total() }} results found</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Student
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Test Details
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Test Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Score
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Evaluation
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($attempts as $attempt)
                            @php
                                $isOffline = $attempt->user->branch_id !== null;
                                $isFullTest = $attempt->fullTestSectionAttempt !== null;
                                $hasEvaluation = $attempt->humanEvaluationRequest && $attempt->humanEvaluationRequest->status === 'completed';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center">
                                                <span class="text-xs font-medium text-white">
                                                    {{ strtoupper(substr($attempt->user->name, 0, 2)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $attempt->user->name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $attempt->user->email }}
                                            </div>
                                            @if($isOffline && $attempt->user->branch)
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 mt-1 inline-block">
                                                    <i class="fas fa-building mr-1"></i>{{ $attempt->user->branch->name }}
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 mt-1 inline-block">
                                                    <i class="fas fa-globe mr-1"></i>Online
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $attempt->testSet->title }}</div>
                                    <div class="text-sm text-gray-500">
                                        @php
                                            $sectionIcons = [
                                                'listening' => 'fa-headphones',
                                                'reading' => 'fa-book-open',
                                                'writing' => 'fa-pen-fancy',
                                                'speaking' => 'fa-microphone'
                                            ];
                                            $sectionName = $attempt->testSet->section->name;
                                        @endphp
                                        <i class="fas {{ $sectionIcons[$sectionName] ?? 'fa-file' }} mr-1"></i>
                                        {{ ucfirst($sectionName) }}
                                    </div>
                                    @if($attempt->testSet->is_premium)
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 mt-1 inline-block">
                                            <i class="fas fa-crown mr-1"></i>Premium
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600 mt-1 inline-block">
                                            Free
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($isFullTest)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                            <i class="fas fa-clipboard-list mr-1"></i>Full Test
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500">Single Section</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $attempt->created_at->format('M d, Y') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $attempt->created_at->format('g:i A') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($attempt->band_score)
                                        <div class="flex items-center">
                                            <span class="text-lg font-bold text-gray-900">
                                                {{ number_format($attempt->band_score, 1) }}
                                            </span>
                                            @if($attempt->band_score >= 7)
                                                <i class="fas fa-star text-yellow-500 ml-1"></i>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($hasEvaluation)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>Evaluated
                                        </span>
                                    @elseif($attempt->humanEvaluationRequest)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>{{ ucfirst($attempt->humanEvaluationRequest->status) }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600">
                                            <i class="fas fa-minus mr-1"></i>No Request
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('teacher.student-results.show', $attempt) }}"
                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-emerald-700 bg-emerald-100 hover:bg-emerald-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                        <i class="fas fa-eye mr-1"></i>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-sm text-gray-500">No student results found</p>
                                        <p class="text-xs text-gray-400 mt-1">Try adjusting your filters</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($attempts->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $attempts->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-teacher-layout>
