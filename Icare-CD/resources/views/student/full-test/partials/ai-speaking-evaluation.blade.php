{{-- AI Speaking Evaluation Partial for Full Test --}}
@php
    $answers = $studentAttempt->answers()
        ->whereNotNull('ai_evaluation')
        ->with(['question', 'speakingRecording'])
        ->get();

    $aiEvaluations = $answers->map(function ($answer) {
        $evaluation = $answer->ai_evaluation;
        $preAnalysis = $evaluation['pre_analysis'] ?? [];
        $speechMetrics = $evaluation['speech_metrics'] ?? [];

        return [
            'answer_id' => $answer->id,
            'question_id' => $answer->question_id,
            'question_title' => $answer->question->content,
            'part_number' => $answer->question->part_number ?? $answer->question->order_number ?? 1,
            'band_score' => $answer->ai_band_score,
            'evaluation' => $evaluation,
            'transcription' => $answer->transcription ?? $evaluation['transcription'] ?? '',
            'audio_url' => $answer->speakingRecording ? route('audio.stream', $answer->speakingRecording->id) : null,
            'speech_metrics' => $speechMetrics,
            'pre_analysis' => $preAnalysis,
        ];
    })->sortBy('part_number')->values();

    // Calculate overall criteria scores
    $criteriaScores = [
        'Fluency and Coherence' => 0,
        'Lexical Resource' => 0,
        'Grammar' => 0,
        'Pronunciation' => 0,
    ];
    $count = $aiEvaluations->count();

    foreach ($aiEvaluations as $eval) {
        foreach ($criteriaScores as $criterion => &$score) {
            $score += $eval['evaluation']['criteria'][$criterion] ?? 0;
        }
    }
    if ($count > 0) {
        foreach ($criteriaScores as &$score) {
            $score = round($score / $count, 1);
        }
    }
@endphp

<div class="space-y-6">
    <!-- Overall Criteria Scores -->
    <div class="grid grid-cols-4 gap-2">
        @foreach($criteriaScores as $criterion => $score)
        <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-100">
            <p class="text-[9px] text-gray-500 mb-1">{{ Str::limit($criterion, 10) }}</p>
            <p class="text-xl font-bold text-gray-800">{{ bandScoreRange($score) }}</p>
        </div>
        @endforeach
    </div>

    <!-- Parts -->
    @foreach($aiEvaluations as $index => $eval)
    @php
        $fluencyIndicators = $eval['pre_analysis']['fluency_indicators'] ?? [];
        $lexicalAnalysis = $eval['pre_analysis']['lexical_analysis'] ?? [];
        $coherenceMarkers = $eval['pre_analysis']['coherence_markers'] ?? [];
        $partSpecific = $eval['pre_analysis']['part_specific'] ?? [];
    @endphp
    <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden" x-data="{ expanded: {{ $index === 0 ? 'true' : 'false' }} }">
        <!-- Part Header -->
        <button @click="expanded = !expanded"
                class="w-full p-4 flex items-center justify-between hover:bg-gray-100 transition-colors">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#C8102E] to-[#A00E27] flex items-center justify-center text-white font-bold text-sm">
                    {{ $eval['part_number'] }}
                </span>
                <div class="text-left">
                    <span class="font-semibold text-gray-900 text-sm">Part {{ $eval['part_number'] }}</span>
                    <p class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit(strip_tags($eval['question_title']), 40) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <!-- Duration -->
                <span class="px-2 py-1 rounded text-xs font-medium bg-white text-gray-600 border border-gray-200">
                    <i class="fas fa-clock mr-1"></i>{{ $eval['speech_metrics']['duration_formatted'] ?? '0:00' }}
                </span>
                <!-- Score -->
                <div class="px-3 py-1 bg-white rounded-lg border border-gray-200">
                    <span class="text-lg font-black text-gray-800">{{ bandScoreRange($eval['band_score']) }}</span>
                </div>
                <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="expanded && 'rotate-180'"></i>
            </div>
        </button>

        <!-- Part Content -->
        <div x-show="expanded" x-collapse class="border-t border-gray-200">
            <div class="p-4 space-y-4">
                <!-- Question -->
                <div class="p-3 rounded-lg bg-white border border-gray-200">
                    <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Question</p>
                    <div class="text-gray-900 text-sm prose prose-sm max-w-none">{!! $eval['question_title'] !!}</div>
                </div>

                <!-- Audio & Transcription -->
                @if(!empty($eval['transcription']))
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-4 py-2 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <h4 class="font-semibold text-gray-800 text-xs flex items-center gap-2">
                            <i class="fas fa-microphone text-gray-400"></i>
                            Your Response
                        </h4>
                        @if(!empty($eval['audio_url']))
                        <audio controls class="w-full sm:w-48 h-8" preload="metadata">
                            <source src="{{ $eval['audio_url'] }}" type="audio/webm">
                            <source src="{{ $eval['audio_url'] }}" type="audio/mpeg">
                        </audio>
                        @endif
                    </div>
                    <div class="p-4">
                        <p class="text-gray-700 leading-6 text-sm whitespace-pre-wrap">{{ $eval['transcription'] }}</p>
                    </div>
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-400">
                        <span>{{ $eval['pre_analysis']['basic_stats']['word_count'] ?? str_word_count($eval['transcription']) }} words</span>
                        <span>{{ $eval['speech_metrics']['duration_formatted'] ?? '' }}</span>
                    </div>
                </div>
                @endif

                <!-- Speech Analysis Dashboard -->
                <div class="bg-gradient-to-r from-slate-50 to-gray-50 rounded-lg border border-gray-200 p-3">
                    <h4 class="font-semibold text-gray-800 mb-2 flex items-center gap-2 text-xs">
                        <i class="fas fa-chart-bar text-indigo-500"></i>
                        Speech Analysis
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <!-- Speech Rate -->
                        <div class="bg-white rounded-lg p-2 border border-gray-100 text-center">
                            <p class="text-lg font-bold text-indigo-600">{{ $eval['speech_metrics']['words_per_minute'] ?? '-' }}</p>
                            <p class="text-[9px] text-gray-500 uppercase">WPM</p>
                            <p class="text-[8px] mt-0.5 px-1.5 py-0.5 rounded-full inline-block
                                {{ ($eval['speech_metrics']['words_per_minute_assessment'] ?? '') === 'Normal' ? 'bg-green-100 text-green-700' :
                                   (($eval['speech_metrics']['words_per_minute_assessment'] ?? '') === 'Fast' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ $eval['speech_metrics']['words_per_minute_assessment'] ?? 'N/A' }}
                            </p>
                        </div>

                        <!-- Duration -->
                        <div class="bg-white rounded-lg p-2 border border-gray-100 text-center">
                            <p class="text-lg font-bold text-slate-600">{{ round($eval['speech_metrics']['duration_seconds'] ?? 0) }}s</p>
                            <p class="text-[9px] text-gray-500 uppercase">Duration</p>
                        </div>

                        <!-- Filler Words -->
                        <div class="bg-white rounded-lg p-2 border border-gray-100 text-center">
                            @php $fillerPercent = $fluencyIndicators['filler_percentage'] ?? 0; @endphp
                            <p class="text-lg font-bold {{ $fillerPercent > 5 ? 'text-red-500' : ($fillerPercent > 2 ? 'text-amber-500' : 'text-green-500') }}">
                                {{ number_format($fillerPercent, 1) }}%
                            </p>
                            <p class="text-[9px] text-gray-500 uppercase">Filler Words</p>
                            <p class="text-[8px] text-gray-400">({{ $fluencyIndicators['total_filler_count'] ?? 0 }} total)</p>
                        </div>

                        <!-- Vocabulary Variety -->
                        <div class="bg-white rounded-lg p-2 border border-gray-100 text-center">
                            @php $diversity = round(($lexicalAnalysis['type_token_ratio'] ?? 0) * 100); @endphp
                            <p class="text-lg font-bold {{ $diversity >= 50 ? 'text-green-500' : ($diversity >= 35 ? 'text-amber-500' : 'text-red-500') }}">
                                {{ $diversity }}%
                            </p>
                            <p class="text-[9px] text-gray-500 uppercase">Vocab Variety</p>
                            <p class="text-[8px] text-gray-400">({{ $lexicalAnalysis['unique_word_count'] ?? 0 }} unique)</p>
                        </div>
                    </div>
                </div>

                <!-- Filler Words Alert -->
                @if(!empty($fluencyIndicators['filler_word_details']) && ($fluencyIndicators['filler_percentage'] ?? 0) > 2)
                <div class="bg-amber-50 rounded-lg border border-amber-200 p-3">
                    <div class="flex items-start gap-2">
                        <div class="w-6 h-6 bg-amber-100 rounded flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-amber-500 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-amber-800 text-xs mb-1">Filler Words Detected</h4>
                            <div class="flex flex-wrap gap-1 mb-1">
                                @foreach($fluencyIndicators['filler_word_details'] as $word => $count)
                                <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-[10px] font-medium">
                                    "{{ $word }}" ({{ $count }}x)
                                </span>
                                @endforeach
                            </div>
                            <p class="text-[10px] text-amber-600">
                                <i class="fas fa-lightbulb mr-1"></i>
                                Tip: Practice pausing silently instead of using filler words.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Coherence Markers -->
                @if(!empty($coherenceMarkers['markers_by_category']))
                <div class="bg-blue-50 rounded-lg border border-blue-200 p-3">
                    <div class="flex items-start gap-2">
                        <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-link text-blue-500 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-semibold text-blue-800 text-xs">Discourse Markers</h4>
                                <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] font-medium">
                                    {{ $coherenceMarkers['total_marker_count'] ?? 0 }} total
                                </span>
                            </div>
                            <div class="space-y-1">
                                @foreach($coherenceMarkers['markers_by_category'] as $category => $markers)
                                @if(!empty($markers))
                                <div class="flex flex-wrap items-center gap-1">
                                    <span class="text-[10px] text-blue-600 font-medium capitalize">{{ str_replace('_', ' ', $category) }}:</span>
                                    @foreach(array_slice($markers, 0, 4) as $marker)
                                    <span class="px-1 py-0.5 bg-white text-blue-700 rounded text-[9px] border border-blue-100">{{ $marker }}</span>
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
                @if($eval['part_number'] == 2 && !empty($partSpecific['coverage_percentage']))
                <div class="bg-purple-50 rounded-lg border border-purple-200 p-3">
                    <div class="flex items-start gap-2">
                        <div class="w-6 h-6 bg-purple-100 rounded flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clipboard-check text-purple-500 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-semibold text-purple-800 text-xs">Cue Card Coverage</h4>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold
                                    {{ ($partSpecific['coverage_percentage'] ?? 0) >= 75 ? 'bg-green-100 text-green-700' :
                                       (($partSpecific['coverage_percentage'] ?? 0) >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $partSpecific['coverage_percentage'] ?? 0 }}%
                                </span>
                            </div>
                            <div class="space-y-1">
                                @if(!empty($partSpecific['points_covered']))
                                @foreach($partSpecific['points_covered'] as $point)
                                <div class="flex items-center gap-1 text-[10px] text-green-700">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                    <span>{{ $point }}</span>
                                </div>
                                @endforeach
                                @endif
                                @if(!empty($partSpecific['points_missed']))
                                @foreach($partSpecific['points_missed'] as $point)
                                <div class="flex items-center gap-1 text-[10px] text-red-600">
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
                @if(!empty($eval['evaluation']['grammar_errors']))
                <div class="bg-rose-50 rounded-lg border border-rose-200 p-3">
                    <div class="flex items-start gap-2">
                        <div class="w-6 h-6 bg-rose-100 rounded flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-spell-check text-rose-500 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-rose-800 text-xs mb-1">Grammar Corrections</h4>
                            <div class="space-y-1">
                                @foreach(array_slice($eval['evaluation']['grammar_errors'], 0, 4) as $error)
                                <div class="bg-white rounded p-1.5 border border-rose-100">
                                    <div class="flex items-start gap-2">
                                        <span class="text-[10px] text-rose-500 line-through">{{ $error['error'] ?? $error['original'] ?? '' }}</span>
                                        <i class="fas fa-arrow-right text-gray-300 text-[9px] mt-0.5"></i>
                                        <span class="text-[10px] text-green-600 font-medium">{{ $error['correction'] ?? $error['corrected'] ?? '' }}</span>
                                    </div>
                                </div>
                                @endforeach
                                @if(count($eval['evaluation']['grammar_errors']) > 4)
                                <p class="text-[10px] text-rose-500">+ {{ count($eval['evaluation']['grammar_errors']) - 4 }} more errors</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Criteria for this Part -->
                <div class="grid grid-cols-4 gap-2">
                    @foreach($eval['evaluation']['criteria'] ?? [] as $criterion => $score)
                    <div class="text-center p-2 bg-white rounded-lg border border-gray-100">
                        <p class="text-[8px] text-gray-500 truncate mb-0.5">{{ Str::limit($criterion, 8) }}</p>
                        <p class="text-lg font-bold text-gray-800">{{ bandScoreRange($score) }}</p>
                    </div>
                    @endforeach
                </div>

                <!-- Feedback -->
                @if(!empty($eval['evaluation']['feedback']))
                <div class="grid md:grid-cols-2 gap-2">
                    @foreach($eval['evaluation']['feedback'] as $key => $feedback)
                    @if(!empty($feedback))
                    <div class="p-2 bg-white rounded-lg border border-gray-100">
                        <h4 class="font-medium text-gray-800 text-[10px] mb-0.5 capitalize">{{ str_replace('_', ' ', $key) }}</h4>
                        <p class="text-[10px] text-gray-600 leading-relaxed">{{ Str::limit($feedback, 150) }}</p>
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif

                <!-- Tips -->
                @if(!empty($eval['evaluation']['tips']))
                <div class="p-2 bg-gray-800 rounded-lg text-white">
                    <h4 class="font-medium mb-1 text-[10px]">Tips for Improvement</h4>
                    <ul class="space-y-0.5">
                        @foreach(array_slice($eval['evaluation']['tips'], 0, 3) as $tip)
                        <li class="flex items-start gap-1 text-[10px] text-gray-300">
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
    @endforeach

    <!-- Overall Strengths & Improvements -->
    @php
        $strengths = [];
        $improvements = [];
        foreach($aiEvaluations as $eval) {
            if (!empty($eval['evaluation']['strengths'])) {
                $strengths = array_merge($strengths, (array)$eval['evaluation']['strengths']);
            }
            if (!empty($eval['evaluation']['improvements'])) {
                $improvements = array_merge($improvements, (array)$eval['evaluation']['improvements']);
            }
        }
        $strengths = array_unique($strengths);
        $improvements = array_unique($improvements);
    @endphp

    @if(!empty($strengths) || !empty($improvements))
    <div class="grid md:grid-cols-2 gap-4">
        @if(!empty($strengths))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
            <h4 class="font-semibold text-emerald-800 mb-2 flex items-center gap-2 text-sm">
                <i class="fas fa-star text-emerald-500"></i>
                Overall Strengths
            </h4>
            <ul class="space-y-1.5">
                @foreach(array_slice($strengths, 0, 4) as $strength)
                <li class="flex items-start gap-2 text-xs text-emerald-700">
                    <i class="fas fa-check-circle mt-0.5"></i>
                    {{ $strength }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(!empty($improvements))
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200">
            <h4 class="font-semibold text-amber-800 mb-2 flex items-center gap-2 text-sm">
                <i class="fas fa-arrow-up text-amber-500"></i>
                Areas to Improve
            </h4>
            <ul class="space-y-1.5">
                @foreach(array_slice($improvements, 0, 4) as $improvement)
                <li class="flex items-start gap-2 text-xs text-amber-700">
                    <i class="fas fa-arrow-circle-up mt-0.5"></i>
                    {{ $improvement }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    @endif
</div>
