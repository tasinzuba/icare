{{-- resources/views/student/dashboard.blade.php --}}
{{-- Personalized Dashboard - Light mode only, Clean design --}}
<x-dashboard-layout>
    <x-slot:title>Dashboard</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Hero Section - Goal Progress -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8 mb-8">
            <div class="flex flex-col lg:flex-row items-center gap-8">
                <!-- Left: Welcome & Stats -->
                <div class="flex-1 w-full">
                    <div class="flex items-center gap-2 mb-2">
                        @if($userGoal && $userGoal->exam_type)
                            <button onclick="document.getElementById('updateExamTypeModal').classList.remove('hidden')"
                                    class="px-3 py-1 bg-[#C8102E]/10 text-[#C8102E] rounded-full text-xs font-medium uppercase hover:bg-[#C8102E]/20 transition-smooth cursor-pointer">
                                {{ $userGoal->exam_type === 'academic' ? 'Academic' : 'General Training' }}
                                <i class="fas fa-pencil-alt ml-1 text-[10px] opacity-60"></i>
                            </button>
                        @else
                            <button onclick="document.getElementById('updateExamTypeModal').classList.remove('hidden')"
                                    class="px-3 py-1 bg-[#C8102E]/10 text-[#C8102E] rounded-full text-xs font-medium hover:bg-[#C8102E]/20 transition-smooth cursor-pointer">
                                <i class="fas fa-graduation-cap mr-1"></i>Set exam type
                            </button>
                        @endif
                        @if($daysToExam !== null)
                            <button onclick="document.getElementById('updateExamDateModal').classList.remove('hidden')"
                                    class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium hover:bg-gray-200 transition-smooth cursor-pointer">
                                <i class="far fa-calendar mr-1"></i>{{ $daysToExam }} days to exam
                                <i class="fas fa-pencil-alt ml-1 text-[10px] opacity-60"></i>
                            </button>
                        @else
                            <button onclick="document.getElementById('updateExamDateModal').classList.remove('hidden')"
                                    class="px-3 py-1 bg-[#C8102E]/10 text-[#C8102E] rounded-full text-xs font-medium hover:bg-[#C8102E]/20 transition-smooth cursor-pointer">
                                <i class="far fa-calendar mr-1"></i>Set exam date
                            </button>
                        @endif
                    </div>

                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-2">
                        Good {{ now()->format('A') === 'AM' ? 'Morning' : (now()->format('H') < 17 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', auth()->user()->name)[0] }}!
                    </h1>

                    <p class="text-gray-500 mb-6">
                        @if($stats['completed_attempts'] > 0)
                            You've completed {{ $stats['completed_attempts'] }} tests. Keep up the momentum!
                        @else
                            Start your IELTS journey today. Every practice counts!
                        @endif
                    </p>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-xl p-3 sm:p-4 text-center">
                            <div class="text-2xl sm:text-3xl font-extrabold text-gray-800">{{ $stats['completed_attempts'] }}</div>
                            <div class="text-xs sm:text-sm font-medium text-gray-600 mt-1">Tests Done</div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3 sm:p-4 text-center">
                            <div class="text-2xl sm:text-3xl font-extrabold text-gray-800">{{ $totalPracticeHours }}h</div>
                            <div class="text-xs sm:text-sm font-medium text-gray-600 mt-1">Practice Time</div>
                        </div>
                    </div>
                </div>

                <!-- Right: Progress Ring -->
                <div class="flex-shrink-0 flex flex-col items-center">
                    <div class="relative w-36 h-36 sm:w-44 sm:h-44 lg:w-48 lg:h-48">
                        <!-- SVG Progress Ring -->
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 120 120">
                            <!-- Background circle -->
                            <circle cx="60" cy="60" r="54" stroke="#f3f4f6" stroke-width="8" fill="none"/>
                            <!-- Progress circle -->
                            <circle cx="60" cy="60" r="54"
                                    stroke="#C8102E"
                                    stroke-width="8"
                                    fill="none"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ 2 * 3.14159 * 54 }}"
                                    stroke-dashoffset="{{ 2 * 3.14159 * 54 * (1 - ($progressToTarget / 100)) }}"
                                    class="progress-ring"/>
                        </svg>
                        <!-- Center Content -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            @if($userGoal)
                                <div class="text-[10px] sm:text-xs font-medium text-gray-500 mb-0.5">Current</div>
                                <div class="text-2xl sm:text-4xl font-extrabold text-gray-800">
                                    {{ $stats['average_band_score'] ? number_format($stats['average_band_score'], 1) : '-' }}
                                </div>
                                <div class="w-8 sm:w-12 h-px bg-gray-300 my-1 sm:my-2"></div>
                                <div class="text-[10px] sm:text-xs font-medium text-gray-500">Target</div>
                                <div class="text-lg sm:text-2xl font-extrabold text-[#C8102E]">{{ number_format($userGoal->target_band_score, 1) }}</div>
                            @else
                                <button onclick="document.getElementById('setGoalModal').classList.remove('hidden')"
                                        class="px-3 py-1.5 sm:px-4 sm:py-2 bg-[#C8102E] text-white rounded-lg text-xs sm:text-sm font-semibold hover:bg-[#A00E27] transition-smooth">
                                    Set Goal
                                </button>
                            @endif
                        </div>
                    </div>
                    @if($userGoal)
                        <button onclick="document.getElementById('setGoalModal').classList.remove('hidden')"
                                class="mt-2 text-xs sm:text-sm font-medium text-gray-500 hover:text-[#C8102E] transition-smooth text-center">
                            <i class="fas fa-pencil-alt mr-1"></i>Change Target
                        </button>
                    @endif
                    @if($scoreNeeded && $scoreNeeded > 0)
                        <p class="text-center text-xs sm:text-sm font-medium text-gray-600 mt-1">
                            Need <span class="font-bold text-[#C8102E]">+{{ number_format($scoreNeeded, 1) }}</span> to reach goal
                        </p>
                    @elseif($userGoal && $progressToTarget >= 100)
                        <p class="text-center text-xs sm:text-sm text-green-600 font-bold mt-1">
                            <i class="fas fa-check-circle mr-1"></i>Goal Achieved!
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Today's Focus (AI Recommendation) -->
        @if($weakestSection)
        <div class="bg-gradient-to-r from-[#C8102E] to-[#8B0000] rounded-2xl p-6 mb-8 text-white">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-bullseye"></i>
                        <span class="text-sm font-medium text-white/80">Today's Focus</span>
                    </div>
                    <h3 class="text-xl font-bold mb-1 capitalize">{{ $weakestSection['name'] }} needs attention</h3>
                    <p class="text-white/80 text-sm">
                        Your {{ $weakestSection['name'] }} score ({{ $weakestSection['average_score'] ?? 'N/A' }}) is lower than other sections. Focus here for maximum improvement.
                    </p>
                </div>
                <a href="{{ route('student.' . $weakestSection['name'] . '.index') }}"
                   class="flex-shrink-0 px-6 py-3 bg-white text-[#C8102E] rounded-xl font-medium hover:bg-gray-100 transition-smooth">
                    Practice Now
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
        @endif

        <!-- Full Test CTA -->
        <div class="mb-8">
            <!-- Full Test -->
            <div class="bg-gradient-to-br from-[#C8102E] to-[#A00E27] rounded-2xl shadow-lg p-6 text-white">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-clipboard-list text-white text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold mb-1">Full IELTS Test</h3>
                        <p class="text-white/80 text-sm">Complete mock exam • 4 sections</p>
                    </div>
                </div>
                <a href="{{ route('student.full-test.index') }}"
                   class="block w-full text-center px-6 py-3 bg-white text-[#C8102E] rounded-xl font-semibold hover:bg-gray-50 transition-smooth shadow-md">
                    Start Full Test
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Section Performance Grid -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Section Performance</h2>
                <a href="{{ route('student.results') }}" class="text-sm text-[#C8102E] hover:text-[#A00E27] font-medium">
                    View Details <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $sectionColors = [
                        'listening' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'icon' => 'fa-headphones'],
                        'reading' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'icon' => 'fa-book-open'],
                        'writing' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'icon' => 'fa-pen-fancy'],
                        'speaking' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'icon' => 'fa-microphone'],
                    ];
                @endphp

                @foreach($sectionPerformance as $section)
                    @php
                        $colors = $sectionColors[$section['name']] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'icon' => 'fa-question'];
                        $progressPercent = $section['average_score'] ? ($section['average_score'] / 9) * 100 : 0;
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 card-hover transition-smooth">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 {{ $colors['bg'] }} rounded-xl flex items-center justify-center">
                                <i class="fas {{ $colors['icon'] }} {{ $colors['text'] }}"></i>
                            </div>
                            @if($section['trend'] !== 'same' && $section['attempts_count'] >= 2)
                                <span class="text-xs font-medium px-2 py-1 rounded-full {{ $section['trend'] === 'up' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                    <i class="fas fa-arrow-{{ $section['trend'] }} mr-1"></i>{{ abs($section['trend_value']) }}
                                </span>
                            @endif
                        </div>

                        <h3 class="font-semibold text-gray-800 capitalize mb-1">{{ $section['name'] }}</h3>

                        <div class="flex items-end gap-2 mb-3">
                            <span class="text-3xl font-bold text-gray-800">
                                {{ $section['average_score'] ? number_format($section['average_score'], 1) : '-' }}
                            </span>
                            <span class="text-sm text-gray-400 mb-1">/ 9.0</span>
                        </div>

                        <!-- Progress bar -->
                        <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden mb-3">
                            <div class="h-full bg-[#C8102E] rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $section['attempts_count'] }} tests</span>
                            <span>Best: {{ $section['best_score'] ? number_format($section['best_score'], 1) : '-' }}</span>
                        </div>

                        <a href="{{ route('student.' . $section['name'] . '.index') }}"
                           class="mt-4 block w-full py-2 text-center text-sm font-medium text-[#C8102E] bg-[#C8102E]/5 rounded-lg hover:bg-[#C8102E]/10 transition-smooth">
                            Practice
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Progress Timeline -->
        @if($userGoal)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-chart-line text-[#C8102E] mr-2"></i>Your Progress Timeline
                </h2>
            </div>

            @if($progressTimeline->isNotEmpty() && $stats['average_band_score'])
            <div class="relative overflow-x-auto pb-2">
                <!-- Timeline Track - Responsive -->
                <div class="relative flex items-center justify-between min-w-[400px] sm:min-w-0">
                    <!-- Background Line -->
                    <div class="absolute top-2 left-4 right-4 h-1 bg-gray-200 rounded-full"></div>
                    <!-- Progress Line -->
                    @php
                        $progressWidth = $progressToTarget > 0 ? min(85, max(15, $progressToTarget * 0.85)) : 15;
                    @endphp
                    <div class="absolute top-2 left-4 h-1 bg-[#C8102E] rounded-full transition-all duration-500"
                         style="width: {{ $progressWidth }}%"></div>

                    <!-- Start Point -->
                    <div class="relative z-10 flex flex-col items-center flex-shrink-0 w-16 sm:w-20">
                        <div class="w-4 h-4 rounded-full bg-[#C8102E] border-4 border-white shadow"></div>
                        <div class="mt-3 text-center">
                            <p class="text-[10px] sm:text-xs text-gray-500">Start</p>
                            <p class="text-xs sm:text-sm font-bold text-gray-800">{{ number_format($progressTimeline->first()['score'], 1) }}</p>
                            <p class="text-[10px] sm:text-xs text-gray-400">{{ $progressTimeline->first()['date'] }}</p>
                        </div>
                    </div>

                    <!-- Progress Points (only show on larger screens) -->
                    @if($progressTimeline->count() > 1)
                        @foreach($progressTimeline->skip(1)->take(2) as $point)
                        <div class="relative z-10 flex-col items-center flex-shrink-0 w-16 sm:w-20 hidden sm:flex">
                            <div class="w-3 h-3 rounded-full bg-[#C8102E] border-3 border-white shadow"></div>
                            <div class="mt-3 text-center">
                                <p class="text-[10px] text-gray-500 capitalize truncate max-w-[60px]">{{ $point['section'] }}</p>
                                <p class="text-xs font-bold text-gray-800">{{ number_format($point['score'], 1) }}</p>
                                <p class="text-[10px] text-gray-400">{{ $point['date'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    @endif

                    <!-- Current Point -->
                    <div class="relative z-10 flex flex-col items-center flex-shrink-0 w-16 sm:w-20">
                        <div class="w-5 h-5 rounded-full bg-white border-4 border-[#C8102E] shadow-lg animate-pulse"></div>
                        <div class="mt-3 text-center">
                            <p class="text-[10px] sm:text-xs text-gray-500">Current</p>
                            <p class="text-xs sm:text-sm font-bold text-[#C8102E]">{{ number_format($stats['average_band_score'], 1) }}</p>
                            <p class="text-[10px] sm:text-xs text-gray-400">Today</p>
                        </div>
                    </div>

                    <!-- Goal Point -->
                    <div class="relative z-10 flex flex-col items-center flex-shrink-0 w-16 sm:w-20">
                        <div class="w-4 h-4 rounded-full bg-gray-300 border-4 border-white shadow"></div>
                        <div class="mt-3 text-center">
                            <p class="text-[10px] sm:text-xs text-gray-500">Goal</p>
                            <p class="text-xs sm:text-sm font-bold text-gray-800">{{ number_format($userGoal->target_band_score, 1) }}</p>
                            <p class="text-[10px] sm:text-xs text-gray-400">{{ $userGoal->target_date->format('M d') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prediction Message -->
            @if($daysToExam && $progressTimeline->count() >= 2)
            @php
                $firstScore = $progressTimeline->first()['score'] ?? 0;
                $currentScore = $stats['average_band_score'] ?? 0;
                $testCount = $progressTimeline->count();
                $improvement = $currentScore - $firstScore;
                $remainingImprovement = $userGoal->target_band_score - $currentScore;

                if ($improvement > 0 && $testCount > 1) {
                    $improvementPerTest = $improvement / ($testCount - 1);
                    $testsNeeded = $improvementPerTest > 0 ? ceil($remainingImprovement / $improvementPerTest) : 0;
                    $avgDaysPerTest = 7;
                    $estimatedDays = $testsNeeded * $avgDaysPerTest;
                    $estimatedDate = now()->addDays($estimatedDays)->format('M d');
                    $onTrack = $estimatedDays <= $daysToExam;
                } else {
                    $estimatedDate = null;
                    $onTrack = null;
                }
            @endphp
            <div class="mt-4 p-3 sm:p-4 rounded-xl {{ $onTrack === true ? 'bg-green-50' : ($onTrack === false ? 'bg-yellow-50' : 'bg-gray-50') }}">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex-shrink-0 {{ $onTrack === true ? 'bg-green-100' : ($onTrack === false ? 'bg-yellow-100' : 'bg-gray-100') }} flex items-center justify-center">
                        <i class="fas {{ $onTrack === true ? 'fa-rocket text-green-600' : ($onTrack === false ? 'fa-clock text-yellow-600' : 'fa-lightbulb text-gray-600') }}"></i>
                    </div>
                    <p class="text-xs sm:text-sm {{ $onTrack === true ? 'text-green-700' : ($onTrack === false ? 'text-yellow-700' : 'text-gray-600') }}">
                        @if($onTrack === true && $estimatedDate)
                            At your current pace, you'll reach Band {{ number_format($userGoal->target_band_score, 1) }} by <span class="font-semibold">{{ $estimatedDate }}</span>!
                        @elseif($onTrack === false && $estimatedDate)
                            Practice more frequently to reach your goal by {{ $userGoal->target_date->format('M d') }}.
                        @elseif($progressToTarget >= 100)
                            You've achieved your target band score!
                        @else
                            Keep practicing to get predictions.
                        @endif
                    </p>
                </div>
            </div>
            @endif

            @else
            <!-- No test data yet -->
            <div class="text-center py-8">
                <div class="relative flex items-center justify-center mb-6">
                    <div class="absolute left-8 right-8 h-1 bg-gray-200 rounded-full"></div>
                    <div class="relative z-10 flex items-center justify-between w-full px-4">
                        <div class="w-4 h-4 rounded-full bg-gray-300 border-4 border-white shadow"></div>
                        <div class="w-4 h-4 rounded-full bg-gray-300 border-4 border-white shadow"></div>
                        <div class="w-4 h-4 rounded-full bg-gray-300 border-4 border-white shadow"></div>
                    </div>
                </div>
                <p class="text-gray-500 text-sm mb-4">Complete tests to see your progress timeline</p>
                <a href="{{ route('student.listening.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-[#C8102E] text-white rounded-lg text-sm font-medium hover:bg-[#A00E27] transition-smooth">
                    Take Your First Test
                </a>
            </div>
            @endif
        </div>
        @endif

        <!-- Tips -->
        <div class="grid grid-cols-1 gap-6">
                <!-- Study Tips -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4">
                        <i class="fas fa-lightbulb text-[#C8102E] mr-2"></i>Smart Tip
                    </h3>
                    @php
                        $tips = [
                            'listening' => 'For Listening, practice with different accents - British, American, Australian are common in IELTS.',
                            'reading' => 'In Reading, scan for keywords first before reading the full passage. It saves time!',
                            'writing' => 'For Writing Task 2, spend 5 minutes planning before writing. Structure is key!',
                            'speaking' => 'In Speaking, use the 1 minute prep time wisely. Make quick notes about what to say.',
                        ];
                        $tipKey = $weakestSection ? $weakestSection['name'] : array_rand($tips);
                        $currentTip = $tips[$tipKey] ?? $tips['reading'];
                    @endphp
                    <p class="text-gray-600 text-sm leading-relaxed">{{ $currentTip }}</p>
                </div>
        </div>
    </div>

    <!-- Set Target Score Modal -->
    <div id="setGoalModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('setGoalModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-800">{{ $userGoal ? 'Update Target Score' : 'Set Your Target Score' }}</h3>
                    <button onclick="document.getElementById('setGoalModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('student.goals.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Target Band Score</label>
                            <select name="target_band_score" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#C8102E] focus:border-transparent" required>
                                <option value="">Select target</option>
                                @for($i = 5.0; $i <= 9.0; $i += 0.5)
                                    <option value="{{ $i }}" {{ $userGoal && $userGoal->target_band_score == $i ? 'selected' : '' }}>Band {{ number_format($i, 1) }}</option>
                                @endfor
                            </select>
                        </div>
                        <!-- Keep existing exam date hidden -->
                        <input type="hidden" name="target_date" value="{{ $userGoal && $userGoal->target_date ? $userGoal->target_date->format('Y-m-d') : now()->addMonths(3)->format('Y-m-d') }}">
                        <button type="submit" class="w-full py-3 bg-[#C8102E] text-white rounded-xl font-medium hover:bg-[#A00E27] transition-smooth">
                            {{ $userGoal ? 'Update Target' : 'Set Target' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Exam Date Modal -->
    <div id="updateExamDateModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('updateExamDateModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-800">{{ $userGoal && $userGoal->target_date ? 'Update Exam Date' : 'Set Exam Date' }}</h3>
                    <button onclick="document.getElementById('updateExamDateModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('student.goals.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Exam Date</label>
                            <input type="date" name="target_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   value="{{ $userGoal && $userGoal->target_date ? $userGoal->target_date->format('Y-m-d') : '' }}"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#C8102E] focus:border-transparent" required>
                        </div>
                        <!-- Keep existing target score hidden -->
                        <input type="hidden" name="target_band_score" value="{{ $userGoal ? $userGoal->target_band_score : 7.0 }}">
                        <button type="submit" class="w-full py-3 bg-[#C8102E] text-white rounded-xl font-medium hover:bg-[#A00E27] transition-smooth">
                            {{ $userGoal && $userGoal->target_date ? 'Update Date' : 'Set Date' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Exam Type Modal -->
    <div id="updateExamTypeModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('updateExamTypeModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <!-- Loading Overlay -->
                <div id="examTypeLoading" class="hidden absolute inset-0 bg-white/90 rounded-2xl flex items-center justify-center z-10">
                    <div class="text-center">
                        <div class="w-12 h-12 border-4 border-[#C8102E]/20 border-t-[#C8102E] rounded-full animate-spin mx-auto mb-3"></div>
                        <p class="text-sm text-gray-600 font-medium">Updating...</p>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Change Exam Type</h3>
                    <button onclick="document.getElementById('updateExamTypeModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <p class="text-gray-600 text-sm mb-6">Select the IELTS exam type you're preparing for:</p>

                <div class="space-y-3">
                    <button onclick="updateExamType('academic')"
                            class="exam-type-option w-full p-4 border-2 rounded-xl text-left transition-all duration-300 hover:border-[#C8102E] {{ ($userGoal->exam_type ?? '') === 'academic' ? 'border-[#C8102E] bg-[#C8102E]/5' : 'border-gray-200' }}"
                            data-type="academic">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center mr-4 {{ ($userGoal->exam_type ?? '') === 'academic' ? 'bg-[#C8102E]/10' : 'bg-gray-100' }}">
                                <i class="fas fa-university text-lg {{ ($userGoal->exam_type ?? '') === 'academic' ? 'text-[#C8102E]' : 'text-gray-600' }}"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">IELTS Academic</p>
                                <p class="text-sm text-gray-500">For higher education & professional registration</p>
                            </div>
                            @if(($userGoal->exam_type ?? '') === 'academic')
                                <i class="fas fa-check-circle text-[#C8102E] text-xl"></i>
                            @endif
                        </div>
                    </button>

                    <button onclick="updateExamType('general')"
                            class="exam-type-option w-full p-4 border-2 rounded-xl text-left transition-all duration-300 hover:border-[#C8102E] {{ ($userGoal->exam_type ?? '') === 'general' ? 'border-[#C8102E] bg-[#C8102E]/5' : 'border-gray-200' }}"
                            data-type="general">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center mr-4 {{ ($userGoal->exam_type ?? '') === 'general' ? 'bg-[#C8102E]/10' : 'bg-gray-100' }}">
                                <i class="fas fa-globe text-lg {{ ($userGoal->exam_type ?? '') === 'general' ? 'text-[#C8102E]' : 'text-gray-600' }}"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">IELTS General Training</p>
                                <p class="text-sm text-gray-500">For migration, work experience & training</p>
                            </div>
                            @if(($userGoal->exam_type ?? '') === 'general')
                                <i class="fas fa-check-circle text-[#C8102E] text-xl"></i>
                            @endif
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateExamType(type) {
            // Show loading
            document.getElementById('examTypeLoading').classList.remove('hidden');

            fetch('{{ route("student.goals.update-exam-type") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ exam_type: type })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    document.getElementById('examTypeLoading').classList.add('hidden');
                    alert('Failed to update. Please try again.');
                }
            })
            .catch(() => {
                document.getElementById('examTypeLoading').classList.add('hidden');
                alert('Failed to update. Please try again.');
            });
        }
    </script>

    <!-- Onboarding Modal -->
    @if($showOnboarding ?? false)
    <div id="onboardingModal" class="fixed inset-0 z-[100] overflow-hidden">
        <div class="onboarding-backdrop fixed inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="onboarding-modal bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="h-1 bg-gray-100">
                    <div id="onboardingProgress" class="h-full bg-[#C8102E] transition-all duration-500 ease-out" style="width: 25%"></div>
                </div>
                <div class="p-8">
                    <div class="flex items-center justify-center mb-8">
                        <div class="flex items-center space-x-3">
                            @for($i = 1; $i <= 4; $i++)
                            <div class="step-indicator {{ $i === 1 ? 'active' : '' }}" data-step="{{ $i }}">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-all duration-300">{{ $i }}</div>
                            </div>
                            @if($i < 4)
                            <div class="w-8 h-px bg-gray-200 step-line" data-step="{{ $i }}"></div>
                            @endif
                            @endfor
                        </div>
                    </div>
                    <div class="relative min-h-[280px]">
                        <div class="onboarding-step active" data-step="1">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-[#C8102E]/10 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <i class="fas fa-graduation-cap text-[#C8102E] text-2xl"></i>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-3">Welcome, {{ auth()->user()->name }}</h2>
                                <p class="text-gray-600 mb-8">Let's personalize your IELTS preparation journey.</p>
                                <button onclick="nextOnboardingStep()" class="w-full py-3 bg-[#C8102E] text-white rounded-xl font-medium hover:bg-[#A00E27] transition-all duration-300">
                                    Get Started
                                </button>
                            </div>
                        </div>
                        <div class="onboarding-step" data-step="2">
                            <div class="text-center">
                                <h2 class="text-xl font-bold text-gray-800 mb-2">Which IELTS are you preparing for?</h2>
                                <p class="text-gray-600 mb-6">This helps us recommend the right practice tests.</p>
                                <div class="space-y-3">
                                    <button onclick="selectExamType('academic')" class="exam-type-btn w-full p-4 border-2 border-gray-200 rounded-xl text-left hover:border-[#C8102E] transition-all duration-300" data-type="academic">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                                                <i class="fas fa-university text-gray-600"></i>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800">IELTS Academic</p>
                                                <p class="text-sm text-gray-500">For higher education</p>
                                            </div>
                                        </div>
                                    </button>
                                    <button onclick="selectExamType('general')" class="exam-type-btn w-full p-4 border-2 border-gray-200 rounded-xl text-left hover:border-[#C8102E] transition-all duration-300" data-type="general">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                                                <i class="fas fa-globe text-gray-600"></i>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800">IELTS General Training</p>
                                                <p class="text-sm text-gray-500">For migration or work</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="onboarding-step" data-step="3">
                            <div class="text-center">
                                <h2 class="text-xl font-bold text-gray-800 mb-2">What's your target band score?</h2>
                                <p class="text-gray-600 mb-6">We'll track your progress towards this goal.</p>
                                <div class="grid grid-cols-4 gap-3 mb-6">
                                    @foreach([5.5, 6.0, 6.5, 7.0, 7.5, 8.0, 8.5, 9.0] as $band)
                                    <button onclick="selectTargetBand({{ $band }})" class="band-btn p-3 border-2 border-gray-200 rounded-xl font-semibold text-gray-700 hover:border-[#C8102E] hover:text-[#C8102E] transition-all duration-300" data-band="{{ $band }}">
                                        {{ number_format($band, 1) }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="onboarding-step" data-step="4">
                            <div class="text-center">
                                <h2 class="text-xl font-bold text-gray-800 mb-2">When is your exam?</h2>
                                <p class="text-gray-600 mb-6">We'll help you plan your preparation.</p>
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <button onclick="selectTimeline('1_month')" class="timeline-btn p-4 border-2 border-gray-200 rounded-xl hover:border-[#C8102E] transition-all duration-300" data-timeline="1_month">
                                        <p class="font-semibold text-gray-800">1 Month</p>
                                        <p class="text-xs text-gray-500">Intensive</p>
                                    </button>
                                    <button onclick="selectTimeline('2_months')" class="timeline-btn p-4 border-2 border-gray-200 rounded-xl hover:border-[#C8102E] transition-all duration-300" data-timeline="2_months">
                                        <p class="font-semibold text-gray-800">2 Months</p>
                                        <p class="text-xs text-gray-500">Balanced</p>
                                    </button>
                                    <button onclick="selectTimeline('3_months')" class="timeline-btn p-4 border-2 border-gray-200 rounded-xl hover:border-[#C8102E] transition-all duration-300" data-timeline="3_months">
                                        <p class="font-semibold text-gray-800">3 Months</p>
                                        <p class="text-xs text-gray-500">Recommended</p>
                                    </button>
                                    <button onclick="selectTimeline('not_sure')" class="timeline-btn p-4 border-2 border-gray-200 rounded-xl hover:border-[#C8102E] transition-all duration-300" data-timeline="not_sure">
                                        <p class="font-semibold text-gray-800">Not Sure</p>
                                        <p class="text-xs text-gray-500">Flexible</p>
                                    </button>
                                </div>
                                <button onclick="submitOnboarding()" id="onboardingSubmitBtn" class="w-full py-3 bg-[#C8102E] text-white rounded-xl font-medium hover:bg-[#A00E27] transition-all duration-300 disabled:opacity-50" disabled>
                                    Complete Setup
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-6">
                        <button onclick="skipOnboarding()" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">Skip for now</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .onboarding-backdrop { animation: fadeIn 0.4s ease-out forwards; }
        .onboarding-modal { animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .onboarding-step { position: absolute; inset: 0; opacity: 0; transform: translateX(30px); pointer-events: none; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .onboarding-step.active { opacity: 1; transform: translateX(0); pointer-events: auto; }
        .onboarding-step.exit { opacity: 0; transform: translateX(-30px); }
        .step-indicator > div { background: #f3f4f6; color: #9ca3af; }
        .step-indicator.active > div, .step-indicator.completed > div { background: #C8102E; color: white; }
        .step-line { transition: background-color 0.3s ease; }
        .step-line.active { background-color: #C8102E; }
        .exam-type-btn.selected, .timeline-btn.selected { border-color: #C8102E; background: rgba(200,16,46,0.05); }
        .band-btn.selected { background: #C8102E; color: white; border-color: #C8102E; }
        .onboarding-closing .onboarding-backdrop { animation: fadeOut 0.3s ease-out forwards; }
        .onboarding-closing .onboarding-modal { animation: slideDown 0.3s ease-out forwards; }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
        @keyframes slideDown { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(30px); } }
    </style>

    <script>
        let onboardingData = { exam_type: null, target_band: null, timeline: null };
        let currentOnboardingStep = 1;

        function nextOnboardingStep() {
            if (currentOnboardingStep >= 4) return;
            const currentStep = document.querySelector(`.onboarding-step[data-step="${currentOnboardingStep}"]`);
            const nextStep = document.querySelector(`.onboarding-step[data-step="${currentOnboardingStep + 1}"]`);
            currentStep.classList.remove('active');
            currentStep.classList.add('exit');
            document.querySelector(`.step-indicator[data-step="${currentOnboardingStep}"]`).classList.add('completed');
            const stepLine = document.querySelector(`.step-line[data-step="${currentOnboardingStep}"]`);
            if (stepLine) stepLine.classList.add('active');
            setTimeout(() => {
                currentStep.classList.remove('exit');
                currentOnboardingStep++;
                nextStep.classList.add('active');
                document.querySelector(`.step-indicator[data-step="${currentOnboardingStep}"]`).classList.add('active');
                document.getElementById('onboardingProgress').style.width = `${currentOnboardingStep * 25}%`;
            }, 150);
        }

        function selectExamType(type) {
            onboardingData.exam_type = type;
            document.querySelectorAll('.exam-type-btn').forEach(btn => {
                btn.classList.toggle('selected', btn.dataset.type === type);
            });
            setTimeout(() => nextOnboardingStep(), 300);
        }

        function selectTargetBand(band) {
            onboardingData.target_band = band;
            document.querySelectorAll('.band-btn').forEach(btn => {
                btn.classList.toggle('selected', parseFloat(btn.dataset.band) === band);
            });
            setTimeout(() => nextOnboardingStep(), 300);
        }

        function selectTimeline(timeline) {
            onboardingData.timeline = timeline;
            document.querySelectorAll('.timeline-btn').forEach(btn => {
                btn.classList.toggle('selected', btn.dataset.timeline === timeline);
            });
            document.getElementById('onboardingSubmitBtn').disabled = false;
        }

        function submitOnboarding() {
            const btn = document.getElementById('onboardingSubmitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
            fetch('{{ route("student.onboarding.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify(onboardingData)
            }).then(async r => {
                const data = await r.json();
                if (!r.ok) {
                    throw new Error(data.message || 'Server error');
                }
                return data;
            }).then(data => {
                if (data.success) { closeOnboardingModal(); setTimeout(() => window.location.reload(), 400); }
                else { btn.disabled = false; btn.innerHTML = 'Complete Setup'; alert(data.message || 'Failed to save'); }
            }).catch(err => {
                console.error('Onboarding error:', err);
                btn.disabled = false;
                btn.innerHTML = 'Complete Setup';
                alert(err.message || 'Something went wrong. Please try again.');
            });
        }

        function skipOnboarding() {
            fetch('{{ route("student.onboarding.skip") }}').then(() => closeOnboardingModal());
        }

        function closeOnboardingModal() {
            const modal = document.getElementById('onboardingModal');
            modal.classList.add('onboarding-closing');
            setTimeout(() => modal.remove(), 350);
        }
    </script>
    @endif

</x-dashboard-layout>
