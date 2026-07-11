<x-dashboard-layout>
    <x-slot:title>AI Speaking Evaluation</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Hero Result Card -->
        <div class="relative bg-white rounded-3xl border border-gray-200 overflow-hidden mb-6 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-gradient-to-br from-rose-500 to-rose-600 opacity-5 rounded-full"></div>
                <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-gradient-to-br from-[#C8102E] to-[#A00E27] opacity-5 rounded-full"></div>
            </div>

            <div class="relative p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <!-- Test Info -->
                    <div class="flex items-start gap-4">
                        <div class="relative">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 flex items-center justify-center shadow-lg">
                                <i class="fas fa-robot text-white text-2xl"></i>
                            </div>
                            <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center shadow-md border-2 border-white">
                                <i class="fas fa-check text-white text-[10px]"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-rose-600 font-semibold uppercase tracking-wider mb-1">AI Speaking Evaluation</p>
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
                            <div class="w-32 h-32 lg:w-36 lg:h-36 rounded-full bg-gradient-to-br from-rose-500 to-rose-600 p-1 shadow-xl">
                                <div class="w-full h-full bg-white rounded-full flex flex-col items-center justify-center px-2">
                                    <p class="text-[10px] text-gray-500 font-medium">AI Band Score</p>
                                    <p class="text-2xl lg:text-3xl font-black text-rose-600 whitespace-nowrap">{{ bandScoreRange($evaluation['overall_band']) }}</p>
                                    <p class="text-[10px] text-gray-400">out of 9.0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expert Feedback Banner (Disabled) -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 opacity-60">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-tie text-gray-400 text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-500 text-lg">Want Expert Feedback?</h4>
                        <p class="text-gray-400 text-sm">Coming soon - Get detailed evaluation from certified IELTS teachers</p>
                    </div>
                </div>
                <button disabled
                   class="flex items-center gap-2 px-6 py-3 bg-gray-400 text-white rounded-xl font-semibold cursor-not-allowed whitespace-nowrap">
                    <i class="fas fa-search"></i>
                    Choose Teacher
                </button>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column (2/3) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Part Results -->
                @foreach($evaluation['parts'] as $index => $part)
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <!-- Part Header -->
                    <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-gray-900 flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#C8102E] to-[#A00E27] flex items-center justify-center text-white font-bold">
                                    {{ $part['part_number'] }}
                                </span>
                                {{ $part['part_type'] }}
                            </h3>
                            <div class="flex items-center gap-3">
                                <!-- Duration -->
                                <span class="px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                    <i class="fas fa-clock mr-1"></i>{{ $part['duration'] }}
                                </span>
                                <!-- Part Score -->
                                <div class="px-4 py-2 bg-gray-100 rounded-xl border border-gray-200">
                                    <span class="text-2xl font-black text-gray-800">{{ bandScoreRange($part['band_score']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 space-y-4" x-data="{ showFeedback: false }">
                        <!-- Question -->
                        <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                            <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Question</p>
                            <div class="text-gray-900 font-medium prose prose-sm max-w-none">{!! $part['question'] !!}</div>
                        </div>

                        <!-- Transcription -->
                        @if(!empty($part['transcription']))
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="px-5 py-3 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="fas fa-microphone text-gray-400"></i>
                                    Your Response
                                </h4>
                                @if(!empty($part['audio_url']))
                                <audio controls class="w-full sm:w-64 h-10" preload="metadata">
                                    <source src="{{ $part['audio_url'] }}" type="audio/webm">
                                    <source src="{{ $part['audio_url'] }}" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                                @endif
                            </div>
                            <div class="p-5">
                                <p class="text-gray-700 leading-7 whitespace-pre-wrap">{{ $part['transcription'] }}</p>
                            </div>
                            <div class="px-5 py-2.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                                <span>{{ $part['metrics']['word_count'] ?? str_word_count($part['transcription']) }} words</span>
                                <span>{{ $part['duration'] }}</span>
                            </div>
                        </div>
                        @endif

                        <!-- Speech Analysis Dashboard -->
                        @if(!empty($part['metrics']) || !empty($part['filler_words']) || !empty($part['lexical_analysis']))
                        <div class="bg-gradient-to-r from-slate-50 to-gray-50 rounded-xl border border-gray-200 p-4">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2 text-sm">
                                <i class="fas fa-chart-bar text-indigo-500"></i>
                                Speech Analysis
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <!-- Speech Rate -->
                                <div class="bg-white rounded-lg p-3 border border-gray-100 text-center">
                                    <p class="text-2xl font-bold text-indigo-600">{{ $part['metrics']['speech_rate'] ?? '-' }}</p>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">WPM</p>
                                    <p class="text-[9px] mt-1 px-2 py-0.5 rounded-full inline-block
                                        {{ ($part['metrics']['speech_rate_assessment'] ?? '') === 'Normal' ? 'bg-green-100 text-green-700' :
                                           (($part['metrics']['speech_rate_assessment'] ?? '') === 'Fast' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                        {{ $part['metrics']['speech_rate_assessment'] ?? 'N/A' }}
                                    </p>
                                </div>

                                <!-- Duration -->
                                <div class="bg-white rounded-lg p-3 border border-gray-100 text-center">
                                    <p class="text-2xl font-bold text-slate-600">{{ $part['duration_seconds'] ? round($part['duration_seconds']) . 's' : $part['duration'] }}</p>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Duration</p>
                                </div>

                                <!-- Filler Words -->
                                <div class="bg-white rounded-lg p-3 border border-gray-100 text-center">
                                    <p class="text-2xl font-bold {{ ($part['filler_words']['percentage'] ?? 0) > 5 ? 'text-red-500' : (($part['filler_words']['percentage'] ?? 0) > 2 ? 'text-amber-500' : 'text-green-500') }}">
                                        {{ number_format($part['filler_words']['percentage'] ?? 0, 1) }}%
                                    </p>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Filler Words</p>
                                    <p class="text-[9px] text-gray-400">({{ $part['filler_words']['total_count'] ?? 0 }} total)</p>
                                </div>

                                <!-- Vocabulary Variety -->
                                <div class="bg-white rounded-lg p-3 border border-gray-100 text-center">
                                    <p class="text-2xl font-bold {{ ($part['lexical_analysis']['diversity_percentage'] ?? 0) >= 50 ? 'text-green-500' : (($part['lexical_analysis']['diversity_percentage'] ?? 0) >= 35 ? 'text-amber-500' : 'text-red-500') }}">
                                        {{ $part['lexical_analysis']['diversity_percentage'] ?? 0 }}%
                                    </p>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Vocab Variety</p>
                                    <p class="text-[9px] text-gray-400">({{ $part['lexical_analysis']['unique_words'] ?? 0 }} unique)</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Filler Words Alert -->
                        @if(!empty($part['filler_words']['details']) && ($part['filler_words']['percentage'] ?? 0) > 2)
                        <div class="bg-amber-50 rounded-xl border border-amber-200 p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-amber-500 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-amber-800 text-sm mb-2">Filler Words Detected</h4>
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        @foreach($part['filler_words']['details'] as $word => $count)
                                        <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded text-xs font-medium">
                                            "{{ $word }}" ({{ $count }}x)
                                        </span>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-amber-600">
                                        <i class="fas fa-lightbulb mr-1"></i>
                                        Tip: Practice pausing silently instead of using filler words. This improves your fluency score.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Coherence Markers -->
                        @if(!empty($part['coherence_markers']['by_category']))
                        <div class="bg-blue-50 rounded-xl border border-blue-200 p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-link text-blue-500 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-blue-800 text-sm">Discourse Markers Used</h4>
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                                            {{ $part['coherence_markers']['total_count'] ?? 0 }} total
                                        </span>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($part['coherence_markers']['by_category'] as $category => $markers)
                                        @if(!empty($markers))
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-xs text-blue-600 font-medium capitalize">{{ str_replace('_', ' ', $category) }}:</span>
                                            @foreach(array_slice($markers, 0, 5) as $marker)
                                            <span class="px-1.5 py-0.5 bg-white text-blue-700 rounded text-[10px] border border-blue-100">{{ $marker }}</span>
                                            @endforeach
                                        </div>
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Part 2 Cue Card Coverage -->
                        @if($part['part_number'] == 2 && !empty($part['cue_card_coverage']) && $part['cue_card_coverage']['coverage_percentage'] !== null)
                        <div class="bg-purple-50 rounded-xl border border-purple-200 p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-clipboard-check text-purple-500 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-purple-800 text-sm">Cue Card Coverage</h4>
                                        <span class="px-2 py-0.5 rounded text-xs font-bold
                                            {{ ($part['cue_card_coverage']['coverage_percentage'] ?? 0) >= 75 ? 'bg-green-100 text-green-700' :
                                               (($part['cue_card_coverage']['coverage_percentage'] ?? 0) >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $part['cue_card_coverage']['coverage_percentage'] ?? 0 }}%
                                        </span>
                                    </div>
                                    <div class="space-y-1.5">
                                        @if(!empty($part['cue_card_coverage']['points_covered']))
                                        @foreach($part['cue_card_coverage']['points_covered'] as $point)
                                        <div class="flex items-center gap-2 text-xs text-green-700">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span>{{ $point }}</span>
                                        </div>
                                        @endforeach
                                        @endif
                                        @if(!empty($part['cue_card_coverage']['points_missed']))
                                        @foreach($part['cue_card_coverage']['points_missed'] as $point)
                                        <div class="flex items-center gap-2 text-xs text-red-600">
                                            <i class="fas fa-times-circle text-red-500"></i>
                                            <span>{{ $point }} (NOT COVERED)</span>
                                        </div>
                                        @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Grammar Errors -->
                        @if(!empty($part['grammar_errors']))
                        <div class="bg-rose-50 rounded-xl border border-rose-200 p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-spell-check text-rose-500 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-rose-800 text-sm mb-2">Grammar Corrections</h4>
                                    <div class="space-y-2">
                                        @foreach(array_slice($part['grammar_errors'], 0, 5) as $error)
                                        <div class="bg-white rounded-lg p-2 border border-rose-100">
                                            <div class="flex items-start gap-2">
                                                <span class="text-xs text-rose-500 line-through">{{ $error['error'] ?? $error['original'] ?? 'N/A' }}</span>
                                                <i class="fas fa-arrow-right text-gray-400 text-[10px] mt-1"></i>
                                                <span class="text-xs text-green-600 font-medium">{{ $error['correction'] ?? $error['corrected'] ?? 'N/A' }}</span>
                                            </div>
                                            @if(!empty($error['type']))
                                            <span class="text-[9px] text-gray-400 mt-1 inline-block">{{ $error['type'] }}</span>
                                            @endif
                                        </div>
                                        @endforeach
                                        @if(count($part['grammar_errors']) > 5)
                                        <p class="text-xs text-rose-500">+ {{ count($part['grammar_errors']) - 5 }} more errors</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Criteria Scores -->
                        <div class="grid grid-cols-4 gap-2">
                            <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                                <p class="text-[10px] text-gray-500 truncate mb-1">Fluency</p>
                                <p class="text-xl font-bold text-gray-800">{{ bandScoreRange($evaluation['overall_scores']['Fluency and Coherence'] ?? 0) }}</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                                <p class="text-[10px] text-gray-500 truncate mb-1">Vocabulary</p>
                                <p class="text-xl font-bold text-gray-800">{{ bandScoreRange($evaluation['overall_scores']['Lexical Resource'] ?? 0) }}</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                                <p class="text-[10px] text-gray-500 truncate mb-1">Grammar</p>
                                <p class="text-xl font-bold text-gray-800">{{ bandScoreRange($evaluation['overall_scores']['Grammar'] ?? 0) }}</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                                <p class="text-[10px] text-gray-500 truncate mb-1">Pronunciation</p>
                                <p class="text-xl font-bold text-gray-800">{{ bandScoreRange($evaluation['overall_scores']['Pronunciation'] ?? 0) }}</p>
                            </div>
                        </div>

                        <!-- Detailed Feedback Toggle -->
                        <div>
                            <button @click="showFeedback = !showFeedback"
                                    class="w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition-all">
                                <span class="font-medium text-gray-700 flex items-center gap-2 text-sm">
                                    <i class="fas fa-chevron-right text-gray-400 text-xs transition-transform" :class="showFeedback && 'rotate-90'"></i>
                                    Detailed Feedback
                                </span>
                            </button>

                            <div x-show="showFeedback" x-collapse x-cloak class="mt-3 space-y-3">
                                <!-- Feedback Cards -->
                                <div class="grid md:grid-cols-2 gap-3">
                                    <!-- Fluency and Coherence -->
                                    @if(!empty($part['feedback']['fluency_coherence']))
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-medium text-gray-800 text-sm">Fluency & Coherence</h4>
                                            <span class="text-lg font-bold text-gray-700">{{ bandScoreRange($evaluation['overall_scores']['Fluency and Coherence'] ?? 0) }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 leading-relaxed">{{ $part['feedback']['fluency_coherence'] }}</p>
                                    </div>
                                    @endif

                                    <!-- Lexical Resource -->
                                    @if(!empty($part['feedback']['lexical_resource']))
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-medium text-gray-800 text-sm">Lexical Resource</h4>
                                            <span class="text-lg font-bold text-gray-700">{{ bandScoreRange($evaluation['overall_scores']['Lexical Resource'] ?? 0) }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 leading-relaxed">{{ $part['feedback']['lexical_resource'] }}</p>
                                        @if(!empty($part['vocabulary_range']))
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            @foreach(array_slice($part['vocabulary_range'], 0, 5) as $word)
                                            <span class="px-2 py-0.5 text-xs bg-gray-200 text-gray-700 rounded">{{ $word }}</span>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    @endif

                                    <!-- Grammar -->
                                    @if(!empty($part['feedback']['grammar']))
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-medium text-gray-800 text-sm">Grammar & Accuracy</h4>
                                            <span class="text-lg font-bold text-gray-700">{{ bandScoreRange($evaluation['overall_scores']['Grammar'] ?? 0) }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 leading-relaxed">{{ $part['feedback']['grammar'] }}</p>
                                    </div>
                                    @endif

                                    <!-- Pronunciation -->
                                    @if(!empty($part['feedback']['pronunciation']))
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-medium text-gray-800 text-sm">Pronunciation</h4>
                                            <span class="text-lg font-bold text-gray-700">{{ bandScoreRange($evaluation['overall_scores']['Pronunciation'] ?? 0) }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 leading-relaxed">{{ $part['feedback']['pronunciation'] }}</p>
                                        @if(!empty($part['pronunciation_issues']))
                                        <ul class="mt-2 space-y-1">
                                            @foreach($part['pronunciation_issues'] as $issue)
                                            <li class="text-xs text-red-600 flex items-start gap-1">
                                                <i class="fas fa-exclamation-circle mt-0.5"></i>
                                                {{ $issue }}
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                                <!-- Tips for This Part -->
                                @if(!empty($part['tips']))
                                <div class="p-4 bg-gray-800 rounded-lg text-white">
                                    <h4 class="font-medium mb-2 text-sm">Tips for Improvement</h4>
                                    <ul class="space-y-1.5">
                                        @foreach($part['tips'] as $tip)
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
                @if(!empty($evaluation['strengths']) || !empty($evaluation['improvements']))
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-chart-line text-gray-600"></i>
                            Overall Performance Summary
                        </h3>
                    </div>
                    <div class="p-5">
                        <div class="grid md:grid-cols-2 gap-4">
                            @if(!empty($evaluation['strengths']))
                            <!-- Strengths -->
                            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                                <h4 class="font-semibold text-emerald-800 mb-3 flex items-center gap-2">
                                    <i class="fas fa-star text-emerald-500"></i>
                                    Your Strengths
                                </h4>
                                <ul class="space-y-2">
                                    @foreach($evaluation['strengths'] as $strength)
                                    <li class="flex items-start gap-2 text-sm text-emerald-700">
                                        <i class="fas fa-check-circle mt-0.5"></i>
                                        {{ $strength }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            @if(!empty($evaluation['improvements']))
                            <!-- Improvements -->
                            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200">
                                <h4 class="font-semibold text-amber-800 mb-3 flex items-center gap-2">
                                    <i class="fas fa-arrow-up text-amber-500"></i>
                                    Areas to Improve
                                </h4>
                                <ul class="space-y-2">
                                    @foreach($evaluation['improvements'] as $improvement)
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
                <!-- Overall Criteria Scores -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h4 class="font-bold text-gray-900 mb-4">Criteria Analysis</h4>
                    <div class="space-y-4">
                        @foreach($evaluation['overall_scores'] as $criterion => $score)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ $criterion }}</span>
                                <span class="font-bold text-gray-900">{{ bandScoreRange($score) }}</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 {{ $score >= 7 ? 'bg-emerald-500' : ($score >= 5 ? 'bg-amber-500' : 'bg-red-500') }}"
                                     style="width: {{ ($score/9)*100 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h3 class="font-bold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('student.results.show', $attempt) }}"
                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-all">
                            <i class="fas fa-arrow-left"></i>Back to Results
                        </a>
                        <a href="{{ route('student.speaking.index') }}"
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
    </style>
    @endpush
</x-dashboard-layout>
