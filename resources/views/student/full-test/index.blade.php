<x-dashboard-layout>
    <x-slot:title>Full Tests</x-slot>

    <div x-data="{ showAll: false, testsPerPage: 9, loading: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Hero Header Card -->
            <div class="relative bg-white rounded-3xl border border-gray-200 overflow-hidden mb-6 shadow-xl">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-gradient-to-br from-[#C8102E] to-[#A00E27] opacity-5 rounded-full"></div>
                    <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-gradient-to-br from-gray-400 to-gray-500 opacity-5 rounded-full"></div>
                </div>

                <div class="relative p-6 lg:p-8">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <!-- Title & Info -->
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#C8102E] to-[#A00E27] flex items-center justify-center shadow-lg">
                                <i class="fas fa-file-alt text-white text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-xs text-[#C8102E] font-semibold uppercase tracking-wider mb-1">Complete Experience</p>
                                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">
                                    @if(isset($selectedCategory) && $selectedCategory)
                                        {{ $selectedCategory->name }}
                                    @else
                                        Full IELTS Tests
                                    @endif
                                </h1>
                                <p class="text-gray-500">Experience the complete IELTS exam with all four modules</p>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="flex items-center gap-6 bg-gray-50 rounded-2xl p-5 border border-gray-200">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ \App\Models\FullTestAttempt::where('user_id', auth()->id())->where('status', 'completed')->count() }}
                                </p>
                                <p class="text-xs text-gray-500">Tests Taken</p>
                            </div>
                            <div class="w-px h-12 bg-gray-200"></div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-[#C8102E]">
                                    {{ number_format(\App\Models\FullTestAttempt::where('user_id', auth()->id())->where('status', 'completed')->avg('overall_band_score') ?? 0, 1) }}
                                </p>
                                <p class="text-xs text-gray-500">Avg. Score</p>
                            </div>
                            <div class="w-px h-12 bg-gray-200"></div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-900">~3h</p>
                                <p class="text-xs text-gray-500">Duration</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Filters -->
            @if(isset($categories) && $categories->count() > 0)
            <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-gray-500 mr-2">Filter:</span>

                    <a href="{{ route('student.full-test.index') }}"
                       class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all {{ !$selectedCategory ? 'bg-[#C8102E] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        All Tests
                        <span class="ml-1.5 text-xs opacity-75">({{ $fullTests->count() }})</span>
                    </a>

                    @foreach($categories as $category)
                        <a href="{{ route('student.full-test.index', ['category' => $category->slug]) }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $selectedCategory && $selectedCategory->id == $category->id ? 'bg-[#C8102E] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            @if($category->icon)
                                <i class="{{ $category->icon }} mr-1.5 text-xs"></i>
                            @endif
                            {{ $category->name }}
                            <span class="ml-1.5 text-xs opacity-75">({{ $category->full_tests_count ?? 0 }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Tests Grid -->
            @if ($fullTests->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($fullTests as $index => $fullTest)
                        @php
                            $userAttempts = $attempts->get($fullTest->id) ?? collect();
                            $completedAttempts = $userAttempts->where('status', 'completed');
                            $inProgressAttempt = $userAttempts->where('status', 'in_progress')->first();

                            $testCategories = collect();
                            foreach ($fullTest->testSets as $testSet) {
                                $testCategories = $testCategories->merge($testSet->categories);
                            }
                            $testCategories = $testCategories->unique('id');

                            $totalFullTestAttempts = \App\Models\FullTestAttempt::where('full_test_id', $fullTest->id)
                                ->where('status', 'completed')
                                ->count();
                        @endphp

                        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-gray-300 transition-all relative"
                             x-show="showAll || {{ $index }} < testsPerPage"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">

                            <!-- Premium Badge -->
                            @if($fullTest->is_premium)
                                <div class="absolute top-3 right-3 z-20">
                                    <span class="px-2.5 py-1 bg-gradient-to-r from-amber-400 to-yellow-500 text-white text-xs font-bold rounded-full shadow-lg flex items-center gap-1">
                                        <i class="fas fa-crown"></i>
                                        Premium
                                    </span>
                                </div>
                            @endif

                            <!-- Card Content -->
                            <div class="p-5">
                                <!-- Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#C8102E] to-[#A00E27] flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-bold text-gray-900 truncate">{{ $fullTest->title }}</h3>
                                            @if($testCategories->count() > 0)
                                                <div class="flex gap-1 mt-1">
                                                    @foreach($testCategories->take(2) as $cat)
                                                        <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">
                                                            {{ $cat->name }}
                                                        </span>
                                                    @endforeach
                                                    @if($testCategories->count() > 2)
                                                        <span class="text-xs text-gray-400">+{{ $testCategories->count() - 2 }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    @if($completedAttempts->count() > 0)
                                        <span class="flex items-center px-2 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-medium">
                                            <i class="fas fa-check mr-1"></i>
                                            @if($completedAttempts->count() > 1) {{ $completedAttempts->count() }}x @else Done @endif
                                        </span>
                                    @elseif($inProgressAttempt)
                                        <span class="flex items-center px-2 py-1 rounded-lg bg-amber-100 text-amber-700 text-xs font-medium">
                                            <i class="fas fa-clock mr-1"></i>
                                            In Progress
                                        </span>
                                    @endif
                                </div>

                                <!-- Stats -->
                                <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-clock text-gray-400"></i>
                                        ~3 hours
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-layer-group text-gray-400"></i>
                                        {{ $fullTest->getAvailableSections() ? count($fullTest->getAvailableSections()) : 0 }} sections
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-users text-gray-400"></i>
                                        {{ $totalFullTestAttempts }}
                                    </span>
                                </div>

                                <!-- Best Score -->
                                @if($completedAttempts->count() > 0 && $completedAttempts->first()->overall_band_score)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl mb-4">
                                        <span class="text-sm text-gray-500">Your Best Score</span>
                                        <span class="text-xl font-bold text-[#C8102E]">{{ $completedAttempts->first()->overall_band_score }}</span>
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                @if($inProgressAttempt)
                                    <div class="flex gap-2">
                                        <a href="{{ route('student.full-test.section', ['fullTestAttempt' => $inProgressAttempt, 'section' => $inProgressAttempt->current_section]) }}"
                                           class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-[#C8102E] text-white text-sm font-semibold hover:bg-[#A00E27] transition-all">
                                            <i class="fas fa-play mr-2"></i>
                                            Continue
                                        </a>
                                        <form action="{{ route('student.full-test.start', $fullTest) }}" method="POST" class="flex-1"
                                              onsubmit="return confirm('This will abandon your current progress. Continue?')">
                                            @csrf
                                            <input type="hidden" name="fresh" value="1">
                                            <button type="submit"
                                                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition-all">
                                                <i class="fas fa-redo mr-2"></i>
                                                Restart
                                            </button>
                                        </form>
                                    </div>
                                @elseif($completedAttempts->count() > 0)
                                    <div class="flex gap-2">
                                        <a href="{{ route('student.full-test.results', $completedAttempts->first()) }}"
                                           class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition-all">
                                            <i class="fas fa-chart-bar mr-2"></i>
                                            Results
                                        </a>
                                        <a href="{{ route('student.full-test.onboarding', $fullTest) }}"
                                           class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-[#C8102E] text-white text-sm font-semibold hover:bg-[#A00E27] transition-all">
                                            <i class="fas fa-redo mr-2"></i>
                                            Retake
                                        </a>
                                    </div>
                                @else
                                    <button onclick="startTest(this, '{{ route('student.full-test.onboarding', $fullTest) }}')"
                                            class="w-full inline-flex items-center justify-center px-4 py-3 rounded-xl bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 transition-all">
                                        <i class="fas fa-play mr-2"></i>
                                        Start Full Test
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Load More Button -->
                @if($fullTests->count() > 9)
                <div class="text-center mt-8" x-show="!showAll && {{ $fullTests->count() }} > testsPerPage">
                    <button @click="showAll = true; loading = true; setTimeout(() => loading = false, 500)"
                            class="inline-flex items-center px-6 py-3 rounded-xl font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 transition-all shadow-sm">
                        <span x-show="!loading">
                            <i class="fas fa-plus mr-2"></i>
                            Show All Tests ({{ $fullTests->count() - 9 }} more)
                        </span>
                        <span x-show="loading" x-cloak>
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Loading...
                        </span>
                    </button>
                </div>

                <div class="text-center mt-4" x-show="showAll" x-cloak>
                    <button @click="showAll = false; window.scrollTo({ top: 0, behavior: 'smooth' })"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 transition-all">
                        <i class="fas fa-chevron-up mr-2"></i>
                        Show Less
                    </button>
                </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                    <div class="max-w-md mx-auto">
                        <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-file-alt text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">
                            @if($selectedCategory)
                                No Full Tests in {{ $selectedCategory->name }}
                            @else
                                No Full Tests Available
                            @endif
                        </h3>
                        <p class="text-gray-500 mb-6">
                            @if($selectedCategory)
                                Try selecting a different category or check back later.
                            @else
                                Full tests are being added regularly. Check back soon!
                            @endif
                        </p>
                        @if($selectedCategory)
                            <a href="{{ route('student.full-test.index') }}"
                               class="inline-flex items-center text-[#C8102E] hover:text-[#A00E27] font-medium">
                                <i class="fas fa-arrow-left mr-2"></i>
                                View All Tests
                            </a>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
    <script>
        function startTest(button, url) {
            button.disabled = true;
            button.innerHTML = `
                <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Starting...
            `;
            button.classList.add('opacity-75', 'cursor-not-allowed');
            setTimeout(() => window.location.href = url, 300);
        }
    </script>
    @endpush
</x-dashboard-layout>
