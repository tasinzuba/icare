<x-dashboard-layout>
    <x-slot:title>AI Writing Evaluation</x-slot>

    @php
        $sectionData = ['icon' => 'fa-pen-fancy', 'color' => 'violet', 'gradient' => 'from-violet-500 to-violet-600'];
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Hero Result Card -->
        <div class="relative bg-white rounded-3xl border border-gray-200 overflow-hidden mb-6 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-gradient-to-br from-violet-500 to-violet-600 opacity-5 rounded-full"></div>
                <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-gradient-to-br from-[#C8102E] to-[#A00E27] opacity-5 rounded-full"></div>
            </div>

            <div class="relative p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <!-- Test Info -->
                    <div class="flex items-start gap-4">
                        <div class="relative">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center shadow-lg">
                                <i class="fas fa-robot text-white text-2xl"></i>
                            </div>
                            <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center shadow-md border-2 border-white">
                                <i class="fas fa-check text-white text-[10px]"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-violet-600 font-semibold uppercase tracking-wider mb-1">AI Writing Evaluation</p>
                            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">{{ $attempt->testSet->title }}</h1>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm mt-3">
                                <span class="inline-flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                    <span class="font-medium">{{ $attempt->created_at->format('M d, Y \a\t g:i A') }}</span>
                                </span>
                                <span class="text-gray-300">|</span>
                                <span class="inline-flex items-center gap-2 text-emerald-600">
                                    <i class="fas fa-check-circle"></i>
                                    <span class="font-medium">AI Evaluated</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Band Score Display -->
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="w-32 h-32 lg:w-36 lg:h-36 rounded-full bg-gradient-to-br from-violet-500 to-violet-600 p-1 shadow-xl">
                                <div class="w-full h-full bg-white rounded-full flex flex-col items-center justify-center px-2">
                                    <p class="text-[10px] text-gray-500 font-medium">AI Band Score</p>
                                    <p class="text-2xl lg:text-3xl font-black text-violet-600 whitespace-nowrap">{{ bandScoreRange($evaluation['overall_band']) }}</p>
                                    <p class="text-[10px] text-gray-400">out of 9.0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expert Feedback Banner -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-tie text-gray-600 text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 text-lg">Want Expert Feedback?</h4>
                        <p class="text-gray-500 text-sm">Get detailed evaluation from certified IELTS teachers</p>
                    </div>
                </div>
                <a href="{{ route('student.evaluation.teachers', $attempt->id) }}"
                   class="flex items-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-xl font-semibold hover:bg-gray-800 transition-all whitespace-nowrap">
                    <i class="fas fa-search"></i>
                    Choose Teacher
                </a>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column (2/3) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Task Results -->
                @foreach($evaluation['tasks'] as $index => $task)
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <!-- Task Header -->
                    <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-gray-900 flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#C8102E] to-[#A00E27] flex items-center justify-center text-white font-bold">
                                    {{ $index + 1 }}
                                </span>
                                Task {{ $index + 1 }}
                            </h3>
                            <div class="flex items-center gap-3">
                                <!-- Word Count -->
                                <span class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $task['word_count'] >= $task['required_words'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                    <i class="fas fa-file-word mr-1"></i>
                                    {{ $task['word_count'] }} / {{ $task['required_words'] }} words
                                </span>
                                <!-- Task Score -->
                                <div class="px-4 py-2 bg-violet-50 rounded-xl border border-violet-200">
                                    <span class="text-2xl font-black text-violet-600">{{ bandScoreRange($task['band_score']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 space-y-4" x-data="{ showHighlights: true, showFeedback: false }">
                        <!-- Your Essay with Highlights -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <!-- Header -->
                            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                                <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="fas fa-file-alt text-gray-400"></i>
                                    Your Response
                                </h4>
                                <div class="flex items-center gap-3">
                                    @if(!empty($task['grammar_corrections']) || !empty($task['vocabulary_suggestions']))
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <span class="w-3 h-3 rounded bg-red-100 border-b-2 border-red-400"></span>
                                            Error
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <span class="w-3 h-3 rounded bg-amber-100 border-b-2 border-dashed border-amber-400"></span>
                                            Improve
                                        </span>
                                    </div>
                                    <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer">
                                        <input type="checkbox" x-model="showHighlights" class="rounded text-gray-600 focus:ring-gray-500 w-3.5 h-3.5">
                                        <span>Show</span>
                                    </label>
                                    @endif
                                </div>
                            </div>

                            <!-- Essay Text -->
                            <div class="p-5">
                                @php
                                    $essayText = $task['essay_text'];
                                    $grammarCorrections = $task['grammar_corrections'] ?? [];
                                    $vocabSuggestions = $task['vocabulary_suggestions'] ?? [];
                                @endphp

                                <div class="text-gray-700 leading-7 essay-highlighted"
                                     :class="{ 'highlights-active': showHighlights }">
                                    @php
                                        $highlightedText = e($essayText);

                                        foreach ($grammarCorrections as $correction) {
                                            $original = preg_quote($correction['original'] ?? '', '/');
                                            if (!empty($original)) {
                                                $tooltip = htmlspecialchars($correction['corrected'] ?? '', ENT_QUOTES) . ' (' . htmlspecialchars($correction['type'] ?? 'Grammar', ENT_QUOTES) . ')';
                                                $highlightedText = preg_replace(
                                                    '/\b' . $original . '\b/i',
                                                    '<span class="error-highlight grammar-error" data-tooltip="' . $tooltip . '" tabindex="0">' . ($correction['original'] ?? '') . '</span>',
                                                    $highlightedText,
                                                    1
                                                );
                                            }
                                        }

                                        foreach ($vocabSuggestions as $suggestion) {
                                            $original = preg_quote($suggestion['original'] ?? '', '/');
                                            if (!empty($original)) {
                                                $tooltip = htmlspecialchars($suggestion['suggested'] ?? '', ENT_QUOTES) . ' (' . htmlspecialchars($suggestion['reason'] ?? 'Better choice', ENT_QUOTES) . ')';
                                                $highlightedText = preg_replace(
                                                    '/\b' . $original . '\b/i',
                                                    '<span class="error-highlight vocab-suggestion" data-tooltip="' . $tooltip . '" tabindex="0">' . ($suggestion['original'] ?? '') . '</span>',
                                                    $highlightedText,
                                                    1
                                                );
                                            }
                                        }

                                        $highlightedText = nl2br($highlightedText);
                                    @endphp
                                    {!! $highlightedText !!}
                                </div>
                            </div>

                            <!-- Stats Bar -->
                            <div class="px-5 py-2.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                                <div class="flex items-center gap-4">
                                    <span>{{ $task['word_count'] }} words</span>
                                    @if(!empty($task['grammar_corrections']))
                                    <span>{{ count($task['grammar_corrections']) }} errors</span>
                                    @endif
                                    @if(!empty($task['vocabulary_suggestions']))
                                    <span>{{ count($task['vocabulary_suggestions']) }} suggestions</span>
                                    @endif
                                </div>
                                <span>Min: {{ $task['required_words'] }}</span>
                            </div>
                        </div>

                        <!-- Criteria Scores -->
                        <div class="grid grid-cols-4 gap-2">
                            @foreach($task['criteria'] as $criterion => $score)
                            <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                                <p class="text-[10px] text-gray-500 truncate mb-1">{{ Str::limit($criterion, 10) }}</p>
                                <p class="text-xl font-bold text-gray-800">{{ bandScoreRange($score) }}</p>
                            </div>
                            @endforeach
                        </div>

                        <!-- Detailed Feedback Toggle -->
                        <div>
                            <button @click="showFeedback = !showFeedback"
                                    class="w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition-all">
                                <span class="font-medium text-gray-700 flex items-center gap-2 text-sm">
                                    <i class="fas fa-chevron-right text-gray-400 text-xs transition-transform" :class="showFeedback && 'rotate-90'"></i>
                                    Detailed Feedback
                                    @if(!empty($task['grammar_corrections']) || !empty($task['vocabulary_suggestions']))
                                    <span class="text-xs text-gray-400">({{ count($task['grammar_corrections'] ?? []) + count($task['vocabulary_suggestions'] ?? []) }} corrections)</span>
                                    @endif
                                </span>
                            </button>

                            <div x-show="showFeedback" x-collapse x-cloak class="mt-3 space-y-3">

                                <!-- Corrections List -->
                                @if(!empty($task['grammar_corrections']) || !empty($task['vocabulary_suggestions']))
                                <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                                    <div class="px-4 py-2 border-b border-gray-200 bg-gray-100">
                                        <h5 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Corrections</h5>
                                    </div>
                                    <div class="divide-y divide-gray-100 max-h-64 overflow-y-auto">
                                        @foreach($task['grammar_corrections'] ?? [] as $correction)
                                        <div class="px-4 py-3 flex items-center gap-3 text-sm">
                                            <span class="text-[10px] text-gray-400 uppercase w-16 flex-shrink-0">{{ $correction['type'] ?? 'Grammar' }}</span>
                                            <span class="text-red-600 line-through">{{ $correction['original'] ?? '' }}</span>
                                            <i class="fas fa-arrow-right text-gray-300 text-xs"></i>
                                            <span class="text-gray-800 font-medium">{{ $correction['corrected'] ?? '' }}</span>
                                        </div>
                                        @endforeach

                                        @foreach($task['vocabulary_suggestions'] ?? [] as $suggestion)
                                        <div class="px-4 py-3 flex items-center gap-3 text-sm">
                                            <span class="text-[10px] text-gray-400 uppercase w-16 flex-shrink-0">Vocab</span>
                                            <span class="text-amber-600">{{ $suggestion['original'] ?? '' }}</span>
                                            <i class="fas fa-arrow-right text-gray-300 text-xs"></i>
                                            <span class="text-gray-800 font-medium">{{ $suggestion['suggested'] ?? '' }}</span>
                                            <span class="text-[10px] text-gray-400 ml-auto">{{ $suggestion['reason'] ?? '' }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Criteria Feedback -->
                                <div class="space-y-3">
                                    @php
                                        $feedbackItems = [
                                            ['key' => 'task_achievement', 'label' => 'Task Achievement', 'score' => $task['criteria']['Task Achievement'] ?? 0],
                                            ['key' => 'coherence_cohesion', 'label' => 'Coherence & Cohesion', 'score' => $task['criteria']['Coherence and Cohesion'] ?? 0],
                                            ['key' => 'lexical_resource', 'label' => 'Lexical Resource', 'score' => $task['criteria']['Lexical Resource'] ?? 0],
                                            ['key' => 'grammar', 'label' => 'Grammar & Accuracy', 'score' => $task['criteria']['Grammar'] ?? 0],
                                        ];
                                    @endphp
                                    @foreach($feedbackItems as $item)
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-medium text-gray-800 text-sm">{{ $item['label'] }}</h4>
                                            <span class="text-lg font-bold text-gray-700">{{ bandScoreRange($item['score']) }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 leading-relaxed">{{ $task['feedback'][$item['key']] ?? 'No feedback available.' }}</p>
                                    </div>
                                    @endforeach
                                </div>

                                <!-- Improvement Tips -->
                                @if(!empty($task['improvement_tips']))
                                <div class="p-4 bg-gray-800 rounded-lg text-white">
                                    <h4 class="font-medium mb-2 text-sm">Tips for Improvement</h4>
                                    <ul class="space-y-1.5">
                                        @foreach($task['improvement_tips'] as $tip)
                                        <li class="flex items-start gap-2 text-sm text-gray-300">
                                            <span class="text-gray-500">•</span>
                                            {{ $tip }}
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Overall Summary (only show if AI provided data) -->
                @if(!empty($evaluation['overall_strengths']) || !empty($evaluation['overall_improvements']))
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-chart-line text-gray-600"></i>
                            Overall Performance Summary
                        </h3>
                    </div>
                    <div class="p-5">
                        <div class="grid md:grid-cols-2 gap-4">
                            @if(!empty($evaluation['overall_strengths']))
                            <!-- Strengths -->
                            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                                <h4 class="font-semibold text-emerald-800 mb-3 flex items-center gap-2">
                                    <i class="fas fa-star text-emerald-500"></i>
                                    Your Strengths
                                </h4>
                                <ul class="space-y-2">
                                    @foreach($evaluation['overall_strengths'] as $strength)
                                    <li class="flex items-start gap-2 text-sm text-emerald-700">
                                        <i class="fas fa-check-circle mt-0.5"></i>
                                        {{ $strength }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            @if(!empty($evaluation['overall_improvements']))
                            <!-- Improvements -->
                            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200">
                                <h4 class="font-semibold text-amber-800 mb-3 flex items-center gap-2">
                                    <i class="fas fa-arrow-up text-amber-500"></i>
                                    Areas to Improve
                                </h4>
                                <ul class="space-y-2">
                                    @foreach($evaluation['overall_improvements'] as $improvement)
                                    <li class="flex items-start gap-2 text-sm text-amber-700">
                                        <i class="fas fa-arrow-circle-up mt-0.5"></i>
                                        {{ $improvement }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column (1/3) -->
            <div class="space-y-6">
                <!-- Criteria Radar Chart -->
                @php
                    $firstTask = $evaluation['tasks'][0] ?? null;
                    $radarData = $firstTask ? [
                        $firstTask['criteria']['Task Achievement'] ?? 0,
                        $firstTask['criteria']['Coherence and Cohesion'] ?? 0,
                        $firstTask['criteria']['Lexical Resource'] ?? 0,
                        $firstTask['criteria']['Grammar'] ?? 0,
                    ] : [0, 0, 0, 0];
                @endphp
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h4 class="font-bold text-gray-900 mb-4">Criteria Analysis</h4>
                    <div class="relative">
                        <canvas id="criteriaRadarChart" width="250" height="250"></canvas>
                    </div>
                </div>

                <!-- Text Statistics -->
                @if($firstTask && isset($firstTask['text_statistics']))
                @php $stats = $firstTask['text_statistics']; @endphp
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h4 class="font-bold text-gray-900 mb-4">Text Analysis</h4>
                    <div class="space-y-4">
                        <!-- Word Stats -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="text-center p-3 bg-gray-50 rounded-xl">
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['word_count'] ?? 0 }}</p>
                                <p class="text-xs text-gray-500">Total Words</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-xl">
                                <p class="text-2xl font-bold text-violet-600">{{ $stats['unique_words'] ?? 0 }}</p>
                                <p class="text-xs text-gray-500">Unique Words</p>
                            </div>
                        </div>

                        <!-- Vocabulary Richness -->
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-600">Vocabulary Richness</span>
                                <span class="font-semibold text-gray-900">{{ $stats['vocabulary_richness'] ?? 0 }}%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000 {{ ($stats['vocabulary_richness'] ?? 0) >= 60 ? 'bg-emerald-500' : (($stats['vocabulary_richness'] ?? 0) >= 40 ? 'bg-amber-500' : 'bg-red-500') }}"
                                     style="width: {{ min($stats['vocabulary_richness'] ?? 0, 100) }}%"></div>
                            </div>
                        </div>

                        <!-- Sentence Stats -->
                        <div class="grid grid-cols-2 gap-3 text-center">
                            <div class="p-2 bg-blue-50 rounded-lg">
                                <p class="text-lg font-bold text-blue-700">{{ $stats['sentence_count'] ?? 0 }}</p>
                                <p class="text-[10px] text-blue-600">Sentences</p>
                            </div>
                            <div class="p-2 bg-purple-50 rounded-lg">
                                <p class="text-lg font-bold text-purple-700">{{ $stats['avg_sentence_length'] ?? 0 }}</p>
                                <p class="text-[10px] text-purple-600">Avg Length</p>
                            </div>
                        </div>

                        <!-- Long Words -->
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-600">Complex Words (6+ chars)</span>
                                <span class="font-semibold text-gray-900">{{ $stats['long_word_percentage'] ?? 0 }}%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-violet-500 rounded-full transition-all duration-1000"
                                     style="width: {{ min($stats['long_word_percentage'] ?? 0, 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Vocabulary Level -->
                @if($firstTask && isset($firstTask['vocabulary_level']))
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h4 class="font-bold text-gray-900 mb-4">Vocabulary Level</h4>
                    @php
                        $levels = ['A1' => 1, 'A2' => 2, 'B1' => 3, 'B2' => 4, 'C1' => 5, 'C2' => 6];
                        $currentLevel = $firstTask['vocabulary_level'] ?? 'B1';
                        $levelNum = $levels[$currentLevel] ?? 3;
                        $levelPercent = ($levelNum / 6) * 100;
                    @endphp
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex-1 h-3 bg-gray-200 rounded-full overflow-hidden relative">
                            @foreach(['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $i => $lvl)
                            <div class="absolute top-0 h-full transition-all duration-500 {{ $levels[$lvl] <= $levelNum ? 'bg-gradient-to-r from-violet-400 to-violet-600' : 'bg-gray-200' }}"
                                 style="left: {{ ($i / 6) * 100 }}%; width: {{ 100/6 }}%"></div>
                            @endforeach
                        </div>
                        <span class="text-lg font-bold text-violet-600">{{ $currentLevel }}</span>
                    </div>
                    <div class="flex justify-between text-[10px] text-gray-400">
                        <span>A1</span><span>A2</span><span>B1</span><span>B2</span><span>C1</span><span>C2</span>
                    </div>

                    <!-- Academic Words Used -->
                    @if(!empty($firstTask['academic_words_used']))
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-500 mb-2">Academic Words Used</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_slice($firstTask['academic_words_used'], 0, 8) as $word)
                            <span class="px-2 py-1 bg-violet-50 text-violet-700 text-xs rounded-md">{{ $word }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Cohesive Devices -->
                @if($firstTask && !empty($firstTask['cohesive_devices']))
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h4 class="font-bold text-gray-900 mb-3">Cohesive Devices</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($firstTask['cohesive_devices'] as $device)
                        <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-sm rounded-lg border border-emerald-200">{{ $device }}</span>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-3">{{ count($firstTask['cohesive_devices']) }} linking words detected</p>
                </div>
                @endif

                <!-- Grammar Error Types -->
                @if($firstTask && !empty($firstTask['grammar_error_types']))
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h4 class="font-bold text-gray-900 mb-4">Grammar Issues</h4>
                    <div class="space-y-3">
                        @php $totalErrors = array_sum(array_column($firstTask['grammar_error_types'], 'count')); @endphp
                        @foreach($firstTask['grammar_error_types'] as $error)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700">{{ $error['type'] }}</span>
                                <span class="font-medium text-red-600">{{ $error['count'] }}</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-red-400 rounded-full transition-all duration-500"
                                     style="width: {{ $totalErrors > 0 ? ($error['count'] / $totalErrors) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Actions Card -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h3 class="font-bold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('student.results.show', $attempt) }}"
                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-all">
                            <i class="fas fa-arrow-left"></i>Back to Results
                        </a>
                        <a href="{{ route('student.writing.index') }}"
                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-[#C8102E] to-[#A00E27] text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                            <i class="fas fa-redo"></i>Practice Again
                        </a>
                        <a href="{{ route('student.results') }}"
                           class="w-full flex items-center justify-center gap-2 px-4 py-3 border border-gray-200 text-gray-600 rounded-xl font-medium hover:bg-gray-50 transition-all">
                            <i class="fas fa-history"></i>View All Results
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .rotate-90 { transform: rotate(90deg); }

        /* Essay Text */
        .essay-highlighted {
            font-size: 15px;
            line-height: 1.8;
        }

        /* Highlight Base */
        .essay-highlighted .error-highlight {
            position: relative;
            cursor: pointer;
            border-radius: 2px;
            padding: 1px 3px;
            transition: all 0.15s ease;
        }

        /* Grammar Error - subtle red underline */
        .highlights-active .grammar-error {
            background-color: #fef2f2;
            border-bottom: 2px solid #ef4444;
        }

        /* Vocabulary - subtle amber underline */
        .highlights-active .vocab-suggestion {
            background-color: #fffbeb;
            border-bottom: 2px dashed #f59e0b;
        }

        /* Highlights off */
        .essay-highlighted:not(.highlights-active) .error-highlight {
            background: transparent;
            border: none;
            padding: 0;
        }

        /* Hover */
        .highlights-active .error-highlight:hover {
            filter: brightness(0.95);
        }

        /* Tooltip */
        .error-highlight::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            background: #1f2937;
            color: white;
            opacity: 0;
            visibility: hidden;
            transition: all 0.15s ease;
            z-index: 100;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Tooltip Arrow */
        .error-highlight::before {
            content: '';
            position: absolute;
            bottom: calc(100% + 2px);
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: #1f2937;
            opacity: 0;
            visibility: hidden;
            transition: all 0.15s ease;
            z-index: 101;
        }

        /* Show tooltip */
        .highlights-active .error-highlight:hover::after,
        .highlights-active .error-highlight:hover::before {
            opacity: 1;
            visibility: visible;
        }

        /* Mobile */
        @media (max-width: 640px) {
            .error-highlight::after {
                left: 0;
                transform: translateX(0);
                max-width: 200px;
                white-space: normal;
            }
            .error-highlight::before {
                left: 12px;
                transform: translateX(0);
            }
        }

        /* Scrollbar */
        .max-h-64::-webkit-scrollbar { width: 4px; }
        .max-h-64::-webkit-scrollbar-track { background: #f3f4f6; }
        .max-h-64::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 2px; }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('criteriaRadarChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels: ['Task Achievement', 'Coherence', 'Vocabulary', 'Grammar'],
                        datasets: [{
                            label: 'Your Score',
                            data: @json($radarData),
                            fill: true,
                            backgroundColor: 'rgba(139, 92, 246, 0.2)',
                            borderColor: 'rgb(139, 92, 246)',
                            pointBackgroundColor: 'rgb(139, 92, 246)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgb(139, 92, 246)',
                            borderWidth: 2
                        }, {
                            label: 'Target (7.0)',
                            data: [7, 7, 7, 7],
                            fill: true,
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderColor: 'rgba(16, 185, 129, 0.5)',
                            pointBackgroundColor: 'rgba(16, 185, 129, 0.5)',
                            borderWidth: 1,
                            borderDash: [5, 5]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 10,
                                    font: { size: 10 }
                                }
                            }
                        },
                        scales: {
                            r: {
                                angleLines: { color: 'rgba(0,0,0,0.1)' },
                                grid: { color: 'rgba(0,0,0,0.1)' },
                                pointLabels: {
                                    font: { size: 10 },
                                    color: '#374151'
                                },
                                suggestedMin: 0,
                                suggestedMax: 9,
                                ticks: {
                                    stepSize: 1,
                                    font: { size: 8 },
                                    backdropColor: 'transparent'
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-dashboard-layout>
