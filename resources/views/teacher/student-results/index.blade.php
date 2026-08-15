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
                    <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2">
    <span class="flex h-6 min-w-6 items-center justify-center rounded-full bg-blue-600 px-2 text-xs font-semibold text-white">
        {{ $attempts->total() }}
    </span>

    <span class="text-sm font-medium text-blue-700">
        Results Found
    </span>
</div>
                </div>
            </div>

            <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($attempts as $attempt)
                    @php
                        $isOffline = $attempt->user->branch_id !== null;
                        $isFullTest = $attempt->fullTestSectionAttempt !== null;
                        $hasEvaluation = $attempt->humanEvaluationRequest && $attempt->humanEvaluationRequest->status === 'completed';

                        $sName = optional(optional($attempt->testSet)->section)->name ?? '';
                        $sMetaMap = [
                            'reading'   => ['label' => 'Reading',   'icon' => 'fa-book-open',  'color' => '#2563eb', 'bg' => '#eff6ff', 'border' => '#bfdbfe'],
                            'listening' => ['label' => 'Listening', 'icon' => 'fa-headphones', 'color' => '#7c3aed', 'bg' => '#f5f3ff', 'border' => '#ddd6fe'],
                            'writing'   => ['label' => 'Writing',   'icon' => 'fa-pen-fancy',  'color' => '#b45309', 'bg' => '#fffbeb', 'border' => '#fde68a'],
                            'speaking'  => ['label' => 'Speaking',  'icon' => 'fa-microphone', 'color' => '#be123c', 'bg' => '#fff1f2', 'border' => '#fecdd3'],
                        ];
                        $sMeta = $sMetaMap[$sName] ?? ['label' => ucfirst($sName ?: 'Test'), 'icon' => 'fa-file-lines', 'color' => '#475569', 'bg' => '#f8fafc', 'border' => '#e2e8f0'];

                        // 0.0 is a real band but falsy in PHP, so compare against null explicitly.
                        $hasBand = !is_null($attempt->band_score);
                        $bandTone = !$hasBand ? '#6b7280'
                            : ($attempt->band_score >= 7 ? '#15803d'
                            : ($attempt->band_score >= 6 ? '#1d4ed8'
                            : ($attempt->band_score >= 5 ? '#b45309' : '#b91c1c')));
                    @endphp

                    <div class="rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-sm transition p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shrink-0">
                                <span class="text-xs font-medium text-white">
                                    {{ strtoupper(mb_substr($attempt->user->name ?? '?', 0, 2)) }}
                                </span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $attempt->user->name }}</p>
                                <p class="text-[11px] text-gray-400 truncate">{{ $attempt->user->email }}</p>
                            </div>

                            <span class="text-[10px] font-bold uppercase tracking-wide rounded px-1.5 py-0.5 shrink-0"
                                  style="{{ $isFullTest ? 'background:#f3e8ff;color:#6b21a8;' : 'background:#f1f5f9;color:#475569;' }}">
                                {{ $isFullTest ? 'Full Test' : 'Single Section' }}
                            </span>
                        </div>

                        <div class="mt-2 flex items-center gap-2 flex-wrap">
                            @if($isOffline)
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-orange-100 text-orange-800">
                                    <i class="fas fa-building mr-1"></i>{{ optional($attempt->user->branch)->name ?? 'Branch' }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                    <i class="fas fa-globe mr-1"></i>Online
                                </span>
                            @endif

                            @if($hasEvaluation)
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Evaluated
                                </span>
                            @elseif($attempt->humanEvaluationRequest)
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>{{ ucfirst($attempt->humanEvaluationRequest->status) }}
                                </span>
                            @endif
                        </div>

                        <a href="{{ route('teacher.student-results.show', $attempt) }}"
                           class="mt-3 rounded-lg p-3 block hover:brightness-95 transition"
                           style="background:{{ $sMeta['bg'] }};border:1px solid {{ $sMeta['border'] }};">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold uppercase tracking-wide" style="color:{{ $sMeta['color'] }};">
                                    <i class="fas {{ $sMeta['icon'] }} mr-1"></i>{{ $sMeta['label'] }}
                                </span>
                                <span class="text-[11px] text-gray-500 truncate" style="max-width:55%;">
                                    {{ optional($attempt->testSet)->title }}
                                </span>
                            </div>

                            <div class="mt-2 flex items-end justify-between gap-2">
                                <div class="min-w-0">
                                    @if($hasBand)
                                        <div class="flex items-baseline gap-1.5">
                                            <span style="font-size:22px;font-weight:800;line-height:1.1;color:{{ $bandTone }};">
                                                {{ number_format($attempt->band_score, 1) }}
                                            </span>
                                            <span class="text-[11px] text-gray-500">band</span>
                                            @if($attempt->band_score >= 7)
                                                <i class="fas fa-star text-yellow-500 text-[11px]"></i>
                                            @endif
                                        </div>
                                        @if($attempt->total_questions)
                                            <p class="text-[10px] text-gray-500 mt-0.5">
                                                {{ $attempt->correct_answers ?? 0 }}/{{ $attempt->total_questions }} correct
                                            </p>
                                        @endif
                                    @elseif(in_array($sName, ['writing', 'speaking'], true))
                                        <p style="font-size:14px;font-weight:700;color:#7e22ce;line-height:1.4;">Needs evaluation</p>
                                    @else
                                        <p style="font-size:14px;font-weight:700;color:#9ca3af;line-height:1.4;">Not scored</p>
                                    @endif
                                </div>

                                <div class="text-right shrink-0">
                                    <p class="text-[11px] text-gray-500">{{ $attempt->created_at->format('M d, Y') }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $attempt->created_at->format('g:i A') }}</p>
                                </div>
                            </div>
                        </a>

                        <div class="mt-3 flex items-center justify-end">
                            <a href="{{ route('teacher.student-results.show', $attempt) }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-emerald-700 bg-emerald-100 hover:bg-emerald-200">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3 py-12 text-center">
                        <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-3"></i>
                        <p class="text-sm text-gray-500">No student results found</p>
                        <p class="text-xs text-gray-400 mt-1">Try adjusting your filters</p>
                    </div>
                @endforelse
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
