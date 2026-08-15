<x-admin-layout>
    <x-slot:title>Student Attempts</x-slot>

    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Student Attempts</h1>
                    <p class="mt-2 text-sm text-gray-600">Manage and review all student test attempts</p>
                </div>
                <!-- Bulk Delete Button (hidden by default) -->
                <button type="button" 
                        id="bulkDeleteBtn"
                        class="hidden inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        onclick="confirmBulkDelete()">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete Selected (<span id="selectedCount">0</span>)
                </button>
            </div>

            <!-- Success/Warning/Error Alerts -->
            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 rounded-lg bg-yellow-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <!-- Total Attempts -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Attempts</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $totalAttempts ?? $attempts->total() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $completedCount ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- In Progress -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $inProgressCount ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Needs Evaluation -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Needs Evaluation</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $needsEvaluationCount ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Filter Attempts</h3>
                    <form action="{{ route('admin.attempts.index') }}" method="GET" class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-1 sm:gap-4 lg:grid-cols-5">
                        <!-- Section Filter -->
                        <div>
                            <label for="section" class="block text-sm font-medium text-gray-700">Section</label>
                            <select id="section" name="section" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Sections</option>
                                <option value="listening" {{ request('section') == 'listening' ? 'selected' : '' }}>
                                    🎧 Listening
                                </option>
                                <option value="reading" {{ request('section') == 'reading' ? 'selected' : '' }}>
                                    📖 Reading
                                </option>
                                <option value="writing" {{ request('section') == 'writing' ? 'selected' : '' }}>
                                    ✍️ Writing
                                </option>
                                <option value="speaking" {{ request('section') == 'speaking' ? 'selected' : '' }}>
                                    🎤 Speaking
                                </option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Statuses</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                    ✅ Completed
                                </option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>
                                    ⏳ In Progress
                                </option>
                                <option value="abandoned" {{ request('status') == 'abandoned' ? 'selected' : '' }}>
                                    ❌ Abandoned
                                </option>
                            </select>
                        </div>

                        <!-- Test Type Filter: full test vs standalone section -->
                        <div>
                            <label for="test_type" class="block text-sm font-medium text-gray-700">Test Type</label>
                            <select id="test_type" name="test_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Types</option>
                                <option value="full" {{ request('test_type') == 'full' ? 'selected' : '' }}>
                                    Full Test
                                </option>
                                <option value="single" {{ request('test_type') == 'single' ? 'selected' : '' }}>
                                    Single Section
                                </option>
                            </select>
                        </div>

                        <!-- Student Filter -->
                        <div>
                            <label for="user" class="block text-sm font-medium text-gray-700">Student</label>
                            <select id="user" name="user" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Students</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Apply Filter
                            </button>
                            
                            @if(request()->hasAny(['section', 'status', 'user', 'test_type']))
                                <a href="{{ route('admin.attempts.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Attempts Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex items-center justify-between gap-3">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ $activeTab === 'attempts' ? 'Recent Attempts' : 'Recent Results' }}
                    </h3>
                    {{-- Select-all moved here from the old table header --}}
                    <label class="flex items-center gap-2 text-xs text-gray-500 whitespace-nowrap cursor-pointer">
                        <input type="checkbox"
                               id="selectAll"
                               class="rounded border-gray-300 text-indigo-600 focus:border-indigo-500 focus:ring-indigo-500"
                               onchange="toggleSelectAll()">
                        Select all
                    </label>
                </div>
                {{-- Recent Results = finished sittings; Recent Attempts = still open or abandoned.
                     The other filters ride along in the query string when switching tab. --}}
                @php $tabParams = request()->except(['view', 'page']); @endphp
                <div class="border-b border-gray-200 px-4 sm:px-6">
                    <nav class="-mb-px flex gap-6" aria-label="Attempt view">
                        <a href="{{ route('admin.attempts.index', array_merge($tabParams, ['view' => 'results'])) }}"
                           class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium {{ $activeTab === 'results' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                            <i class="fas fa-award mr-1.5"></i>Recent Results
                            <span class="ml-1.5 rounded-full px-2 py-0.5 text-xs {{ $activeTab === 'results' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $resultsCount }}
                            </span>
                        </a>
                        <a href="{{ route('admin.attempts.index', array_merge($tabParams, ['view' => 'attempts'])) }}"
                           class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium {{ $activeTab === 'attempts' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                            <i class="fas fa-hourglass-half mr-1.5"></i>Recent Attempts
                            <span class="ml-1.5 rounded-full px-2 py-0.5 text-xs {{ $activeTab === 'attempts' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $attemptsCount }}
                            </span>
                        </a>
                    </nav>
                </div>

                <form id="bulkDeleteForm" method="POST" action="{{ route('admin.attempts.bulk-destroy') }}">
                    @csrf
                    @method('DELETE')
                    <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse ($attempts as $attempt)
                            @php
                                $sName = optional(optional($attempt->testSet)->section)->name ?? '';
                                $sMetaMap = [
                                    'reading'   => ['label' => 'Reading',   'icon' => 'fa-book-open',  'color' => '#2563eb', 'bg' => '#eff6ff', 'border' => '#bfdbfe'],
                                    'listening' => ['label' => 'Listening', 'icon' => 'fa-headphones', 'color' => '#7c3aed', 'bg' => '#f5f3ff', 'border' => '#ddd6fe'],
                                    'writing'   => ['label' => 'Writing',   'icon' => 'fa-pen-fancy',  'color' => '#b45309', 'bg' => '#fffbeb', 'border' => '#fde68a'],
                                    'speaking'  => ['label' => 'Speaking',  'icon' => 'fa-microphone', 'color' => '#be123c', 'bg' => '#fff1f2', 'border' => '#fecdd3'],
                                ];
                                $sMeta = $sMetaMap[$sName] ?? ['label' => ucfirst($sName ?: 'Test'), 'icon' => 'fa-file-lines', 'color' => '#475569', 'bg' => '#f8fafc', 'border' => '#e2e8f0'];

                                if ($attempt->status === 'completed') {
                                    $statusLabel = 'Completed'; $statusStyle = 'background:#dcfce7;color:#166534;';
                                } elseif ($attempt->status === 'in_progress') {
                                    $statusLabel = 'In Progress'; $statusStyle = 'background:#fef9c3;color:#854d0e;';
                                } else {
                                    $statusLabel = 'Abandoned'; $statusStyle = 'background:#fee2e2;color:#991b1b;';
                                }

                                // 0.0 is a real band but falsy in PHP, so compare against null explicitly.
                                $hasBand = !is_null($attempt->band_score);
                                $needsEval = $attempt->status === 'completed' && !$hasBand
                                    && in_array($sName, ['writing', 'speaking'], true);

                                $bandTone = !$hasBand ? '#6b7280'
                                    : ($attempt->band_score >= 7 ? '#15803d'
                                    : ($attempt->band_score >= 6 ? '#1d4ed8'
                                    : ($attempt->band_score >= 5 ? '#b45309' : '#b91c1c')));
                            @endphp

                            <div class="rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-sm transition p-4">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox"
                                           name="attempt_ids[]"
                                           value="{{ $attempt->id }}"
                                           class="attempt-checkbox mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                           onchange="updateSelectedCount()">

                                    <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0"
                                         style="background:#f3f4f6;color:#4b5563;font-weight:700;font-size:12px;">
                                        {{ strtoupper(mb_substr(optional($attempt->user)->name ?? '?', 0, 2)) }}
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-gray-900 truncate">
                                            {{ optional($attempt->user)->name ?? 'Unknown student' }}
                                        </p>
                                        <p class="text-[11px] text-gray-400 truncate">{{ optional($attempt->user)->email }}</p>
                                    </div>

                                    <span class="text-[10px] font-bold uppercase tracking-wide rounded-full px-2 py-0.5 shrink-0"
                                          style="{{ $statusStyle }}">{{ $statusLabel }}</span>
                                </div>

                                {{-- The result block links to this attempt's full result page. It is a
                                     link rather than the whole card so the bulk-select checkbox and the
                                     footer actions stay clickable on their own. --}}
                                <a href="{{ route('admin.attempts.show', $attempt) }}"
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
                                                </div>
                                                @if($attempt->total_questions)
                                                    <p class="text-[10px] text-gray-500 mt-0.5">
                                                        {{ $attempt->correct_answers ?? 0 }}/{{ $attempt->total_questions }} correct
                                                    </p>
                                                @endif
                                            @elseif($needsEval)
                                                <p style="font-size:14px;font-weight:700;color:#7e22ce;line-height:1.4;">Needs evaluation</p>
                                            @elseif($attempt->status === 'completed')
                                                <p style="font-size:14px;font-weight:700;color:#b45309;line-height:1.4;">Awaiting result</p>
                                            @else
                                                <p style="font-size:14px;font-weight:700;color:#9ca3af;line-height:1.4;">Not finished</p>
                                            @endif
                                        </div>

                                        <div class="text-right shrink-0">
                                            <p class="text-[11px] text-gray-500">{{ $attempt->created_at->format('M d, Y') }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $attempt->created_at->format('g:i A') }}</p>
                                        </div>
                                    </div>
                                </a>

                                <div class="mt-3 flex items-center justify-end gap-4">
                                    <a href="{{ route('admin.attempts.show', $attempt) }}"
                                       class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                    @if($needsEval)
                                        <a href="{{ route('admin.attempts.evaluate-form', $attempt) }}"
                                           class="text-xs font-medium text-green-600 hover:text-green-800">
                                            <i class="fas fa-pen-to-square mr-1"></i>Evaluate
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="sm:col-span-2 lg:col-span-3 py-12 text-center">
                                <p class="text-sm text-gray-500">No student attempts found</p>
                                <p class="text-xs text-gray-400 mt-1">Try adjusting your filters</p>
                            </div>
                        @endforelse
                    </div>
                </form>
                
                <!-- Pagination -->
                @if($attempts->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $attempts->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Toggle select all checkboxes
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.attempt-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            
            updateSelectedCount();
        }
        
        // Update selected count and show/hide bulk delete button
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.attempt-checkbox:checked');
            const count = checkboxes.length;
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const selectedCount = document.getElementById('selectedCount');
            
            selectedCount.textContent = count;
            
            if (count > 0) {
                bulkDeleteBtn.classList.remove('hidden');
            } else {
                bulkDeleteBtn.classList.add('hidden');
            }
            
            // Update select all checkbox state
            const selectAll = document.getElementById('selectAll');
            const allCheckboxes = document.querySelectorAll('.attempt-checkbox');
            
            if (allCheckboxes.length > 0) {
                selectAll.checked = count === allCheckboxes.length;
                selectAll.indeterminate = count > 0 && count < allCheckboxes.length;
            }
        }
        
        // Confirm bulk delete
        function confirmBulkDelete() {
            const checkboxes = document.querySelectorAll('.attempt-checkbox:checked');
            const count = checkboxes.length;
            
            if (count === 0) {
                alert('Please select at least one attempt to delete.');
                return;
            }
            
            if (confirm(`Are you sure you want to delete ${count} selected attempt(s)? This action cannot be undone.`)) {
                document.getElementById('bulkDeleteForm').submit();
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSelectedCount();
        });
    </script>
    @endpush
</x-admin-layout>
