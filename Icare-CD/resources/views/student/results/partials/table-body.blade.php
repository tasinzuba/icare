@php
    $sectionIcons = [
        'listening' => 'fa-headphones-alt',
        'reading' => 'fa-book-reader',
        'writing' => 'fa-pen-nib',
        'speaking' => 'fa-comment-dots'
    ];

    $sectionColors = [
        'listening' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-500'],
        'reading' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-500'],
        'writing' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-500'],
        'speaking' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-500'],
    ];

    $allAttempts = collect();

    if (request('section') !== 'full-test') {
        foreach ($attempts as $attempt) {
            // Skip attempts with missing testSet or section
            if (!$attempt->testSet || !$attempt->testSet->section) {
                continue;
            }
            $allAttempts->push([
                'type' => 'section',
                'data' => $attempt,
                'created_at' => $attempt->created_at
            ]);
        }
    }

    if (in_array(request('section'), ['all', 'full-test', null, ''])) {
        foreach ($fullTestAttempts as $fullTest) {
            // Skip full tests with missing fullTest relation
            if (!$fullTest->fullTest) {
                continue;
            }
            $allAttempts->push([
                'type' => 'full',
                'data' => $fullTest,
                'created_at' => $fullTest->created_at
            ]);
        }
    }

    // Sort based on current sort parameter
    $sort = request('sort', 'latest');
    if ($sort === 'oldest') {
        $allAttempts = $allAttempts->sortBy('created_at');
    } elseif ($sort === 'score_high') {
        $allAttempts = $allAttempts->sortByDesc(function($item) {
            return $item['type'] === 'full'
                ? $item['data']->overall_band_score ?? 0
                : $item['data']->band_score ?? 0;
        });
    } elseif ($sort === 'score_low') {
        $allAttempts = $allAttempts->sortBy(function($item) {
            return $item['type'] === 'full'
                ? $item['data']->overall_band_score ?? 0
                : $item['data']->band_score ?? 0;
        });
    } else {
        $allAttempts = $allAttempts->sortByDesc('created_at');
    }
@endphp

@forelse ($allAttempts as $attemptItem)
    @if($attemptItem['type'] === 'full')
        @php $fullTest = $attemptItem['data']; @endphp
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="py-5 px-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-layer-group text-slate-600 text-xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-900 text-base truncate">{{ $fullTest->fullTest->title }}</p>
                        <p class="text-sm text-gray-500 font-medium">Full Test</p>
                    </div>
                </div>
            </td>
            <td class="py-5 px-5">
                <span class="text-base font-semibold text-gray-800">{{ $fullTest->created_at->format('d M Y') }}</span>
                <span class="block text-sm text-gray-500 font-medium">{{ $fullTest->created_at->format('h:i a') }}</span>
            </td>
            <td class="py-5 px-5">
                @if ($fullTest->status === 'completed')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold bg-green-100 text-green-700">
                        <i class="fas fa-check-circle text-xs"></i>
                        Complete
                    </span>
                @elseif ($fullTest->status === 'in_progress')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold bg-amber-100 text-amber-700">
                        <i class="fas fa-clock text-xs"></i>
                        Incomplete
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold bg-gray-100 text-gray-600">
                        <i class="fas fa-times-circle text-xs"></i>
                        Abandoned
                    </span>
                @endif
            </td>
            <td class="py-5 px-5">
                @if ($fullTest->overall_band_score !== null)
                    <span class="text-2xl font-black text-gray-900">{{ bandScoreRange($fullTest->overall_band_score) }}</span>
                @else
                    <span class="text-base font-semibold text-gray-400">Not Scored</span>
                @endif
            </td>
            <td class="py-5 px-6 text-center">
                <div class="flex items-center justify-center gap-2">
                    @if ($fullTest->status === 'in_progress')
                        <a href="{{ route('student.full-test.continue', $fullTest) }}"
                           class="inline-flex items-center px-5 py-2.5 bg-white border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all">
                            <i class="fas fa-play mr-2"></i>
                            Resume Test
                        </a>
                    @else
                        <a href="{{ route('student.full-test.results', $fullTest) }}"
                           class="inline-flex items-center px-5 py-2.5 bg-white border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all">
                            <i class="fas fa-eye mr-2"></i>
                            View Results
                        </a>
                    @endif
                </div>
            </td>
        </tr>
    @else
        @php
            $attempt = $attemptItem['data'];
            $sectionName = $attempt->testSet->section->name;
            $icon = $sectionIcons[$sectionName] ?? 'fa-question';
            $colors = $sectionColors[$sectionName] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-500'];
        @endphp
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="py-5 px-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 {{ $colors['bg'] }} rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas {{ $icon }} {{ $colors['text'] }} text-xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-900 text-base truncate">{{ $attempt->testSet->title }}</p>
                        <p class="text-sm text-gray-500 font-medium capitalize">{{ $sectionName }}</p>
                    </div>
                </div>
            </td>
            <td class="py-5 px-5">
                <span class="text-base font-semibold text-gray-800">{{ $attempt->created_at->format('d M Y') }}</span>
                <span class="block text-sm text-gray-500 font-medium">{{ $attempt->created_at->format('h:i a') }}</span>
            </td>
            <td class="py-5 px-5">
                @if ($attempt->status === 'completed')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold bg-green-100 text-green-700">
                        <i class="fas fa-check-circle text-xs"></i>
                        Complete
                    </span>
                @elseif ($attempt->status === 'in_progress')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold bg-amber-100 text-amber-700">
                        <i class="fas fa-clock text-xs"></i>
                        Incomplete
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold bg-gray-100 text-gray-600">
                        <i class="fas fa-times-circle text-xs"></i>
                        Abandoned
                    </span>
                @endif
            </td>
            <td class="py-5 px-5">
                @if ($attempt->band_score !== null)
                    <span class="text-2xl font-black text-gray-900">{{ bandScoreRange($attempt->band_score) }}</span>
                @else
                    <span class="text-base font-semibold text-gray-400">Not Scored</span>
                @endif
            </td>
            <td class="py-5 px-6 text-center">
                <div class="flex items-center justify-center gap-2">
                    @if ($attempt->status === 'in_progress')
                        @php
                            $sectionName = $attempt->testSet->section->name ?? 'listening';
                            $resumeRoute = match($sectionName) {
                                'listening' => route('student.listening.start', $attempt->testSet),
                                'reading' => route('student.reading.start', $attempt->testSet),
                                'writing' => route('student.writing.start', $attempt->testSet),
                                'speaking' => route('student.speaking.start', $attempt->testSet),
                                default => route('student.dashboard'),
                            };
                        @endphp
                        <a href="{{ $resumeRoute }}"
                           class="inline-flex items-center px-5 py-2.5 bg-white border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all">
                            <i class="fas fa-play mr-2"></i>
                            Resume Test
                        </a>
                    @else
                        <a href="{{ route('student.results.show', $attempt) }}"
                           class="inline-flex items-center px-5 py-2.5 bg-white border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all">
                            <i class="fas fa-eye mr-2"></i>
                            View Results
                        </a>
                    @endif
                </div>
            </td>
        </tr>
    @endif
@empty
    @php
        $currentSection = request('section', 'all');
        $emptyStateConfig = [
            'all' => [
                'title' => 'No Test Results Yet',
                'description' => 'Start taking tests to track your progress and see your results here.',
                'button' => 'Start Your First Test',
                'url' => route('student.dashboard')
            ],
            'full-test' => [
                'title' => 'No Full Test Results Yet',
                'description' => 'Take a complete IELTS mock test to see your overall band score.',
                'button' => 'Start Your First Full Test',
                'url' => url('/student/test/full-test')
            ],
            'listening' => [
                'title' => 'No Listening Results Yet',
                'description' => 'Practice your listening skills and track your progress here.',
                'button' => 'Start Your First Listening Test',
                'url' => url('/student/test/listening')
            ],
            'reading' => [
                'title' => 'No Reading Results Yet',
                'description' => 'Improve your reading comprehension and see your scores here.',
                'button' => 'Start Your First Reading Test',
                'url' => url('/student/test/reading')
            ],
            'writing' => [
                'title' => 'No Writing Results Yet',
                'description' => 'Practice your writing tasks and get feedback on your essays.',
                'button' => 'Start Your First Writing Test',
                'url' => url('/student/test/writing')
            ],
            'speaking' => [
                'title' => 'No Speaking Results Yet',
                'description' => 'Record your speaking responses and track your improvement.',
                'button' => 'Start Your First Speaking Test',
                'url' => url('/student/test/speaking')
            ],
        ];
        $config = $emptyStateConfig[$currentSection] ?? $emptyStateConfig['all'];
    @endphp
    <tr>
        <td colspan="5" class="py-16 text-center">
            <h3 class="font-bold text-gray-900 text-lg mb-2">{{ $config['title'] }}</h3>
            <p class="text-gray-600 text-base font-medium mb-5">{{ $config['description'] }}</p>
            <a href="{{ $config['url'] }}" class="inline-flex items-center px-6 py-3 bg-[#C8102E] text-white rounded-xl text-base font-semibold hover:bg-[#A00E27] transition-all hover:scale-105 shadow-lg shadow-[#C8102E]/20">
                <i class="fas fa-rocket mr-2"></i>{{ $config['button'] }}
            </a>
        </td>
    </tr>
@endforelse
