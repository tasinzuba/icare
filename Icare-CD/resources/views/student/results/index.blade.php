{{-- resources/views/student/results/index.blade.php --}}
<x-dashboard-layout>
    <x-slot:title>My Results</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @php
            $allScores = $attempts->where('band_score', '>', 0)->pluck('band_score')
                ->merge($fullTestAttempts->where('overall_band_score', '>', 0)->pluck('overall_band_score'));
            $avgScore = $allScores->count() > 0 ? round($allScores->avg() * 2) / 2 : null;
            $bestScore = $allScores->count() > 0 ? round($allScores->max() * 2) / 2 : null;
            $totalTests = $attempts->total() + $fullTestAttempts->count();
            $completedTests = $attempts->where('status', 'completed')->count() + $fullTestAttempts->where('status', 'completed')->count();

            $currentSection = request('section', 'all');
        @endphp

        <!-- Header with Stats -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900">My Results</h1>
                <p class="text-gray-600 text-base mt-1 font-medium">Track your IELTS progress</p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="inline-flex items-center px-6 py-3 bg-[#C8102E] text-white rounded-xl text-base font-semibold hover:bg-[#A00E27] transition-all hover:scale-105 shadow-lg shadow-[#C8102E]/20">
                <i class="fas fa-plus mr-2"></i>New Test
            </a>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <p class="text-gray-600 text-sm font-semibold mb-2">Average</p>
                <p class="text-3xl font-black text-gray-900">{{ $avgScore ?? '-' }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <p class="text-gray-600 text-sm font-semibold mb-2">Best</p>
                <p class="text-3xl font-black text-gray-900">{{ $bestScore ?? '-' }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <p class="text-gray-600 text-sm font-semibold mb-2">Total</p>
                <p class="text-3xl font-black text-gray-900">{{ $totalTests }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <p class="text-gray-600 text-sm font-semibold mb-2">Completed</p>
                <p class="text-3xl font-black text-gray-900">{{ $completedTests }}</p>
            </div>
        </div>

        <!-- Main Card with Tabs and Table -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200">
                <div class="flex overflow-x-auto scrollbar-hide">
                    @php
                        $tabs = [
                            'all' => ['icon' => 'fa-th-large', 'label' => 'All Tests'],
                            'full-test' => ['icon' => 'fa-layer-group', 'label' => 'Full Test'],
                            'listening' => ['icon' => 'fa-headphones-alt', 'label' => 'Listening'],
                            'reading' => ['icon' => 'fa-book-reader', 'label' => 'Reading'],
                            'writing' => ['icon' => 'fa-pen-nib', 'label' => 'Writing'],
                            'speaking' => ['icon' => 'fa-comment-dots', 'label' => 'Speaking'],
                        ];
                    @endphp

                    @foreach($tabs as $key => $tab)
                        <a href="#" data-section="{{ $key }}"
                           class="tab-link flex items-center gap-2.5 px-6 py-4 text-base font-semibold whitespace-nowrap border-b-3 transition-all {{ $currentSection === $key ? 'text-[#C8102E] border-[#C8102E] bg-red-50/50' : 'text-gray-500 border-transparent hover:text-gray-800 hover:bg-gray-50' }}">
                            <i class="fas {{ $tab['icon'] }} text-lg"></i>
                            <span>{{ $tab['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="p-5 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg"></i>
                        <input type="text" id="searchInput" value="{{ request('search') }}" placeholder="Search Test History and press Enter"
                               class="w-full pl-12 pr-4 py-3 bg-white border border-gray-300 rounded-xl text-base font-medium focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 focus:border-[#C8102E] placeholder:text-gray-400">
                    </div>

                    <!-- Filters -->
                    <div class="flex gap-3">
                        <select id="statusFilter"
                                class="px-4 py-3 bg-white border border-gray-300 rounded-xl text-base font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 cursor-pointer">
                            <option value="">Test Status</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        </select>

                        <select id="scoreStatusFilter"
                                class="px-4 py-3 bg-white border border-gray-300 rounded-xl text-base font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 cursor-pointer">
                            <option value="">Score Status</option>
                            <option value="scored" {{ request('score_status') === 'scored' ? 'selected' : '' }}>Scored</option>
                            <option value="not_scored" {{ request('score_status') === 'not_scored' ? 'selected' : '' }}>Not Scored</option>
                        </select>

                        <select id="sortFilter"
                                class="px-4 py-3 bg-white border border-gray-300 rounded-xl text-base font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 cursor-pointer">
                            <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Sort By</option>
                            <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="score_high" {{ request('sort') === 'score_high' ? 'selected' : '' }}>Highest Score</option>
                            <option value="score_low" {{ request('sort') === 'score_low' ? 'selected' : '' }}>Lowest Score</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden p-10 text-center">
                <i class="fas fa-circle-notch fa-spin text-3xl text-[#C8102E]"></i>
                <p class="text-gray-600 text-base font-medium mt-3">Loading...</p>
            </div>

            <!-- Table Container -->
            <div id="tableContainer">
                @if ($attempts->count() > 0 || $fullTestAttempts->count() > 0)
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="text-left py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wide">Test Title</th>
                                    <th class="text-left py-4 px-5 text-sm font-bold text-gray-700 uppercase tracking-wide">Date</th>
                                    <th class="text-left py-4 px-5 text-sm font-bold text-gray-700 uppercase tracking-wide">Test Status</th>
                                    <th class="text-left py-4 px-5 text-sm font-bold text-gray-700 uppercase tracking-wide">Score</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wide">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="divide-y divide-gray-100">
                                @include('student.results.partials.table-body')
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="paginationContainer" class="p-5 border-t border-gray-200 bg-gray-50">
                        @if($attempts->count() > 0 && request('section') !== 'full-test')
                            {{ $attempts->appends(request()->query())->links() }}
                        @endif
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="p-16 text-center">
                        <div class="w-24 h-24 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-clipboard-list text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">No Test Results Yet</h3>
                        <p class="text-gray-600 text-base mb-8 max-w-md mx-auto font-medium">
                            Start taking tests to track your progress and see your results here.
                        </p>
                        <a href="{{ route('student.dashboard') }}"
                           class="inline-flex items-center px-8 py-4 bg-[#C8102E] text-white rounded-xl text-lg font-semibold hover:bg-[#A00E27] transition-all hover:scale-105 shadow-lg shadow-[#C8102E]/20">
                            <i class="fas fa-rocket mr-3"></i>Start Your First Test
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .border-b-3 {
            border-bottom-width: 3px;
        }
    </style>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const scoreStatusFilter = document.getElementById('scoreStatusFilter');
        const sortFilter = document.getElementById('sortFilter');
        const tableBody = document.getElementById('tableBody');
        const paginationContainer = document.getElementById('paginationContainer');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const tableContainer = document.getElementById('tableContainer');
        const tabLinks = document.querySelectorAll('.tab-link');

        let currentSection = '{{ $currentSection }}';
        let searchTimeout;

        // Build query string
        function buildQueryString() {
            const params = new URLSearchParams();
            if (currentSection && currentSection !== 'all') {
                params.set('section', currentSection);
            }
            if (searchInput.value) {
                params.set('search', searchInput.value);
            }
            if (statusFilter.value) {
                params.set('status', statusFilter.value);
            }
            if (scoreStatusFilter.value) {
                params.set('score_status', scoreStatusFilter.value);
            }
            if (sortFilter.value && sortFilter.value !== 'latest') {
                params.set('sort', sortFilter.value);
            }
            return params.toString();
        }

        // Fetch results via AJAX
        function fetchResults() {
            loadingIndicator.classList.remove('hidden');
            tableContainer.style.opacity = '0.5';

            const queryString = buildQueryString();
            const url = '{{ route("student.results") }}' + (queryString ? '?' + queryString : '');

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = data.html;
                if (paginationContainer) {
                    paginationContainer.innerHTML = data.pagination;
                }

                // Update URL without reload
                window.history.pushState({}, '', url);
            })
            .catch(error => {
                console.error('Error fetching results:', error);
            })
            .finally(() => {
                loadingIndicator.classList.add('hidden');
                tableContainer.style.opacity = '1';
            });
        }

        // Tab click handler
        tabLinks.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();

                // Update active state
                tabLinks.forEach(t => {
                    t.classList.remove('text-[#C8102E]', 'border-[#C8102E]', 'bg-red-50/50');
                    t.classList.add('text-gray-500', 'border-transparent');
                });
                this.classList.remove('text-gray-500', 'border-transparent');
                this.classList.add('text-[#C8102E]', 'border-[#C8102E]', 'bg-red-50/50');

                currentSection = this.dataset.section;
                fetchResults();
            });
        });

        // Search on Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                fetchResults();
            }
        });

        // Filter change handlers
        statusFilter.addEventListener('change', fetchResults);
        scoreStatusFilter.addEventListener('change', fetchResults);
        sortFilter.addEventListener('change', fetchResults);

        // Handle pagination clicks (delegate)
        if (paginationContainer) {
            paginationContainer.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link && link.href) {
                    e.preventDefault();

                    // Fetch with AJAX
                    loadingIndicator.classList.remove('hidden');
                    tableContainer.style.opacity = '0.5';

                    fetch(link.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        tableBody.innerHTML = data.html;
                        paginationContainer.innerHTML = data.pagination;
                        window.history.pushState({}, '', link.href);

                        // Scroll to top of table
                        document.querySelector('.bg-white.rounded-2xl.border').scrollIntoView({ behavior: 'smooth', block: 'start' });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    })
                    .finally(() => {
                        loadingIndicator.classList.add('hidden');
                        tableContainer.style.opacity = '1';
                    });
                }
            });
        }
    });
    </script>
    @endpush
</x-dashboard-layout>
