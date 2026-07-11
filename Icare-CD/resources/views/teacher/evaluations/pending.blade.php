<x-teacher-layout>
    <x-slot:title>Pending Evaluations</x-slot>
    
    <x-slot:header>
        <h1 class="text-xl font-semibold text-white">Pending Evaluations</h1>
    </x-slot>
    
    <div class="container mx-auto px-4 py-8 space-y-6">
        <!-- Unassigned Branch Evaluations (Can Claim) -->
        @if(isset($unassignedEvaluations) && $unassignedEvaluations->count() > 0)
        <div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-lg shadow overflow-hidden border border-orange-200">
            <div class="px-6 py-4 border-b border-orange-200">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center">
                            <i class="fas fa-hand-pointer text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Available Branch Evaluations</h2>
                            <p class="text-sm text-gray-600">Claim these evaluations to start grading</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-orange-500 text-white text-sm font-semibold rounded-full">
                        {{ $unassignedEvaluations->count() }} Available
                    </span>
                </div>

                <!-- Filters -->
                <form action="{{ route('teacher.evaluations.pending') }}" method="GET" class="mt-4 flex flex-wrap items-center gap-3">
                    @if(request('student_type'))
                        <input type="hidden" name="student_type" value="{{ request('student_type') }}">
                    @endif
                    <select name="branch_id" onchange="this.form.submit()" class="text-sm border-orange-300 rounded-md shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 bg-white">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="section" onchange="this.form.submit()" class="text-sm border-orange-300 rounded-md shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 bg-white">
                        <option value="">All Sections</option>
                        <option value="writing" {{ request('section') === 'writing' ? 'selected' : '' }}>Writing</option>
                        <option value="speaking" {{ request('section') === 'speaking' ? 'selected' : '' }}>Speaking</option>
                    </select>
                    <select name="sort" onchange="this.form.submit()" class="text-sm border-orange-300 rounded-md shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 bg-white">
                        <option value="requested_at" {{ request('sort', 'requested_at') === 'requested_at' ? 'selected' : '' }}>Sort by Date</option>
                        <option value="branch" {{ request('sort') === 'branch' ? 'selected' : '' }}>Sort by Branch</option>
                    </select>
                    <select name="dir" onchange="this.form.submit()" class="text-sm border-orange-300 rounded-md shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 bg-white">
                        <option value="desc" {{ request('dir', 'desc') === 'desc' ? 'selected' : '' }}>Newest First</option>
                        <option value="asc" {{ request('dir') === 'asc' ? 'selected' : '' }}>Oldest First</option>
                    </select>
                    @if(request('branch_id') || request('section') || request('sort') !== null)
                        <a href="{{ route('teacher.evaluations.pending') }}" class="text-sm text-orange-600 hover:text-orange-800 font-medium">
                            <i class="fas fa-times mr-1"></i>Clear Filters
                        </a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-orange-200">
                    <thead class="bg-orange-100/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Set</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-orange-100">
                        @foreach($unassignedEvaluations as $evaluation)
                            @php
                                $isFullTest = $evaluation->studentAttempt->fullTestSectionAttempt !== null;
                            @endphp
                            <tr class="hover:bg-orange-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $evaluation->student->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $evaluation->student->email }}</div>
                                        @if($evaluation->student->branch)
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 mt-1 inline-block">
                                                <i class="fas fa-building mr-1"></i>{{ $evaluation->student->branch->name }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($evaluation->studentAttempt->testSet->section->name) }}
                                    </span>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $evaluation->studentAttempt->testSet->title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $evaluation->requested_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $evaluation->requested_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="{{ route('teacher.evaluations.claim', $evaluation) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
                                            <i class="fas fa-hand-pointer mr-2"></i>
                                            Claim & Start
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Unassigned Online Evaluations (Can Claim) -->
        @if(isset($unassignedOnlineEvaluations) && $unassignedOnlineEvaluations->count() > 0)
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-lg shadow overflow-hidden border border-emerald-200">
            <div class="px-6 py-4 border-b border-emerald-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center">
                            <i class="fas fa-globe text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Available Online Evaluations</h2>
                            <p class="text-sm text-gray-600">Online student requests waiting to be claimed</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-emerald-500 text-white text-sm font-semibold rounded-full">
                        {{ $unassignedOnlineEvaluations->count() }} Available
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-200">
                    <thead class="bg-emerald-100/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Set</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tokens</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-emerald-100">
                        @foreach($unassignedOnlineEvaluations as $evaluation)
                            @php
                                $isFullTest = $evaluation->studentAttempt->fullTestSectionAttempt !== null;
                            @endphp
                            <tr class="hover:bg-emerald-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $evaluation->student->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $evaluation->student->email }}</div>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 mt-1 inline-block">
                                            <i class="fas fa-globe mr-1"></i>Online
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($evaluation->studentAttempt->testSet->section->name) }}
                                    </span>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $evaluation->studentAttempt->testSet->title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <i class="fas fa-coins text-yellow-500 mr-1"></i>
                                        {{ $evaluation->tokens_used }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $evaluation->requested_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $evaluation->requested_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="{{ route('teacher.evaluations.claim', $evaluation) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-500 hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                                            <i class="fas fa-hand-pointer mr-2"></i>
                                            Claim & Start
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Assigned Evaluations -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Evaluations Awaiting Your Review</h2>
                    <div class="flex items-center space-x-4">
                        <!-- Student Type Filter -->
                        <form action="{{ route('teacher.evaluations.pending') }}" method="GET" class="flex items-center space-x-2">
                            <select name="student_type" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                                <option value="">All Students</option>
                                <option value="online" {{ request('student_type') === 'online' ? 'selected' : '' }}>Online Students</option>
                                <option value="offline" {{ request('student_type') === 'offline' ? 'selected' : '' }}>Branch Students</option>
                            </select>
                        </form>
                        <span class="text-sm text-gray-600">
                            Total: <span class="font-semibold">{{ $evaluations->total() }}</span>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Set</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tokens</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($evaluations as $evaluation)
                            @php
                                $isFullTest = $evaluation->studentAttempt->fullTestSectionAttempt !== null;
                            @endphp
                            <tr class="{{ $evaluation->priority === 'high' ? 'bg-amber-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $evaluation->student->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $evaluation->student->email }}</div>
                                        @if($evaluation->is_offline_request && $evaluation->student->branch)
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 mt-1 inline-block">
                                                <i class="fas fa-building mr-1"></i>{{ $evaluation->student->branch->name }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($evaluation->studentAttempt->testSet->section->name) }}
                                    </span>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $evaluation->studentAttempt->testSet->title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($evaluation->status === 'in_progress')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            In Progress
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Assigned
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($evaluation->priority === 'high')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-fire mr-1"></i>High
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Normal
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $evaluation->deadline_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $evaluation->deadline_at->format('h:i A') }}</div>
                                    @if($evaluation->deadline_at->isPast())
                                        <span class="text-xs text-red-600 font-semibold">Overdue</span>
                                    @elseif($evaluation->deadline_at->diffInHours(now()) < 24)
                                        <span class="text-xs text-amber-600">Due soon</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($evaluation->is_offline_request)
                                        <span class="text-green-600 font-medium">Free</span>
                                    @else
                                        <div class="flex items-center">
                                            <i class="fas fa-coins text-yellow-500 mr-1"></i>
                                            {{ $evaluation->tokens_used }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('teacher.evaluations.show', $evaluation) }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                        <i class="fas fa-edit mr-2"></i>
                                        {{ $evaluation->status === 'in_progress' ? 'Continue' : 'Start' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="text-gray-500">
                                        <i class="fas fa-clipboard-check text-4xl mb-3"></i>
                                        <p class="text-lg font-medium">No pending evaluations</p>
                                        <p class="text-sm mt-1">Great job! You're all caught up.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($evaluations->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $evaluations->links() }}
                </div>
            @endif
        </div>
    </div>
</x-teacher-layout>