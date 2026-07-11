{{-- AI Writing Evaluation Partial for Full Test --}}
@php
    $answers = $studentAttempt->answers()
        ->whereNotNull('ai_evaluation')
        ->with('question')
        ->get();

    $aiEvaluations = $answers->map(function ($answer) {
        $evaluation = $answer->ai_evaluation;
        return [
            'question_id' => $answer->question_id,
            'question_title' => $answer->question->content,
            'part' => $answer->question->order_number ?? $answer->question->part_number ?? 1,
            'band_score' => $answer->ai_band_score,
            'evaluation' => $evaluation,
            'essay_text' => $evaluation['original_text'] ?? $answer->answer ?? '',
        ];
    });
@endphp

<div class="space-y-6" x-data="{ activeTab: 'overview' }">
    <!-- Tab Navigation -->
    <div class="flex gap-2 border-b border-gray-200">
        <button @click="activeTab = 'overview'"
                :class="activeTab === 'overview' ? 'border-violet-500 text-violet-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium border-b-2 transition-colors">
            Overview
        </button>
        @foreach($aiEvaluations as $index => $eval)
        <button @click="activeTab = 'task{{ $index + 1 }}'"
                :class="activeTab === 'task{{ $index + 1 }}' ? 'border-violet-500 text-violet-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium border-b-2 transition-colors">
            Task {{ $index + 1 }}
        </button>
        @endforeach
    </div>

    <!-- Overview Tab -->
    <div x-show="activeTab === 'overview'" x-cloak>
        <!-- Criteria Scores Grid -->
        @if($aiEvaluations->isNotEmpty())
        @php $firstEval = $aiEvaluations->first(); @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            @foreach($firstEval['evaluation']['criteria'] ?? [] as $criterion => $score)
            <div class="text-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-[10px] text-gray-500 truncate mb-1">{{ Str::limit($criterion, 12) }}</p>
                <p class="text-xl font-bold text-gray-800">{{ bandScoreRange($score) }}</p>
            </div>
            @endforeach
        </div>

        <!-- Text Statistics -->
        @if(!empty($firstEval['evaluation']['text_statistics']))
        @php $stats = $firstEval['evaluation']['text_statistics']; @endphp
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 mb-4">
            <h4 class="font-semibold text-gray-800 mb-3 text-sm">Text Analysis</h4>
            <div class="grid grid-cols-4 gap-3">
                <div class="text-center">
                    <p class="text-lg font-bold text-gray-900">{{ $stats['word_count'] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500">Words</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-violet-600">{{ $stats['unique_words'] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500">Unique</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-blue-600">{{ $stats['sentence_count'] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500">Sentences</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold {{ ($stats['vocabulary_richness'] ?? 0) >= 50 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $stats['vocabulary_richness'] ?? 0 }}%</p>
                    <p class="text-[10px] text-gray-500">Richness</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Strengths & Improvements -->
        <div class="grid md:grid-cols-2 gap-4">
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
            @if(!empty($strengths))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                <h4 class="font-semibold text-emerald-800 mb-2 flex items-center gap-2 text-sm">
                    <i class="fas fa-star text-emerald-500"></i>
                    Strengths
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

    <!-- Task Tabs -->
    @foreach($aiEvaluations as $index => $eval)
    <div x-show="activeTab === 'task{{ $index + 1 }}'" x-cloak class="space-y-4">
        <!-- Task Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#C8102E] to-[#A00E27] flex items-center justify-center text-white font-bold text-sm">
                    {{ $index + 1 }}
                </span>
                <span class="font-semibold text-gray-900">Task {{ $index + 1 }}</span>
            </div>
            <div class="flex items-center gap-3">
                <!-- Word Count -->
                @php
                    $wordCount = $eval['evaluation']['word_count'] ?? 0;
                    $requiredWords = ($index + 1) == 1 ? 150 : 250;
                @endphp
                <span class="px-3 py-1 rounded-lg text-xs font-medium {{ $wordCount >= $requiredWords ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                    <i class="fas fa-file-word mr-1"></i>
                    {{ $wordCount }} / {{ $requiredWords }} words
                </span>
                <div class="px-3 py-1 bg-violet-50 rounded-lg border border-violet-200">
                    <span class="text-lg font-black text-violet-600">{{ bandScoreRange($eval['band_score']) }}</span>
                </div>
            </div>
        </div>

        <!-- Essay Text with Highlights -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{ showHighlights: true }">
            <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                <h4 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                    <i class="fas fa-file-alt text-gray-400"></i>
                    Your Response
                </h4>
                @if(!empty($eval['evaluation']['grammar_corrections']) || !empty($eval['evaluation']['vocabulary_suggestions']))
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 text-[10px] text-gray-500">
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded bg-red-100 border-b border-red-400"></span>Error
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded bg-amber-100 border-b border-dashed border-amber-400"></span>Improve
                        </span>
                    </div>
                    <label class="flex items-center gap-1 text-[10px] text-gray-500 cursor-pointer">
                        <input type="checkbox" x-model="showHighlights" class="rounded text-gray-600 focus:ring-gray-500 w-3 h-3">
                        Show
                    </label>
                </div>
                @endif
            </div>

            <div class="p-4">
                @php
                    $essayText = $eval['essay_text'];
                    $grammarCorrections = $eval['evaluation']['grammar_corrections'] ?? [];
                    $vocabSuggestions = $eval['evaluation']['vocabulary_suggestions'] ?? [];

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
                <div class="text-gray-700 leading-7 text-sm essay-highlighted"
                     :class="{ 'highlights-active': showHighlights }">
                    {!! $highlightedText !!}
                </div>
            </div>
        </div>

        <!-- Criteria Scores for this Task -->
        <div class="grid grid-cols-4 gap-2">
            @foreach($eval['evaluation']['criteria'] ?? [] as $criterion => $score)
            <div class="text-center p-2 bg-gray-50 rounded-lg border border-gray-100">
                <p class="text-[9px] text-gray-500 truncate mb-0.5">{{ Str::limit($criterion, 10) }}</p>
                <p class="text-lg font-bold text-gray-800">{{ bandScoreRange($score) }}</p>
            </div>
            @endforeach
        </div>

        <!-- Corrections List -->
        @if(!empty($eval['evaluation']['grammar_corrections']) || !empty($eval['evaluation']['vocabulary_suggestions']))
        <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-4 py-2 border-b border-gray-200 bg-gray-100">
                <h5 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Corrections</h5>
            </div>
            <div class="divide-y divide-gray-100 max-h-48 overflow-y-auto">
                @foreach($eval['evaluation']['grammar_corrections'] ?? [] as $correction)
                <div class="px-4 py-2 flex items-center gap-3 text-xs">
                    <span class="text-[9px] text-gray-400 uppercase w-14 flex-shrink-0">{{ $correction['type'] ?? 'Grammar' }}</span>
                    <span class="text-red-600 line-through">{{ $correction['original'] ?? '' }}</span>
                    <i class="fas fa-arrow-right text-gray-300 text-[10px]"></i>
                    <span class="text-gray-800 font-medium">{{ $correction['corrected'] ?? '' }}</span>
                </div>
                @endforeach

                @foreach($eval['evaluation']['vocabulary_suggestions'] ?? [] as $suggestion)
                <div class="px-4 py-2 flex items-center gap-3 text-xs">
                    <span class="text-[9px] text-gray-400 uppercase w-14 flex-shrink-0">Vocab</span>
                    <span class="text-amber-600">{{ $suggestion['original'] ?? '' }}</span>
                    <i class="fas fa-arrow-right text-gray-300 text-[10px]"></i>
                    <span class="text-gray-800 font-medium">{{ $suggestion['suggested'] ?? '' }}</span>
                    <span class="text-[9px] text-gray-400 ml-auto">{{ $suggestion['reason'] ?? '' }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Feedback Sections -->
        @if(!empty($eval['evaluation']['feedback']))
        <div class="grid md:grid-cols-2 gap-3">
            @foreach($eval['evaluation']['feedback'] as $key => $feedback)
            @if(!empty($feedback))
            <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                <h4 class="font-medium text-gray-800 text-xs mb-1 capitalize">{{ str_replace('_', ' ', $key) }}</h4>
                <p class="text-xs text-gray-600 leading-relaxed">{{ $feedback }}</p>
            </div>
            @endif
            @endforeach
        </div>
        @endif

        <!-- Improvement Tips -->
        @if(!empty($eval['evaluation']['improvement_tips']))
        <div class="p-3 bg-gray-800 rounded-lg text-white">
            <h4 class="font-medium mb-2 text-xs">Tips for Improvement</h4>
            <ul class="space-y-1">
                @foreach($eval['evaluation']['improvement_tips'] as $tip)
                <li class="flex items-start gap-2 text-xs text-gray-300">
                    <span class="text-gray-500">•</span>
                    {{ $tip }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    @endforeach
</div>

<style>
    .essay-highlighted { font-size: 14px; line-height: 1.7; }
    .essay-highlighted .error-highlight {
        position: relative;
        cursor: pointer;
        border-radius: 2px;
        padding: 1px 2px;
        transition: all 0.15s ease;
    }
    .highlights-active .grammar-error {
        background-color: #fef2f2;
        border-bottom: 2px solid #ef4444;
    }
    .highlights-active .vocab-suggestion {
        background-color: #fffbeb;
        border-bottom: 2px dashed #f59e0b;
    }
    .essay-highlighted:not(.highlights-active) .error-highlight {
        background: transparent;
        border: none;
        padding: 0;
    }
    .error-highlight::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: calc(100% + 6px);
        left: 50%;
        transform: translateX(-50%);
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 11px;
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
    .error-highlight::before {
        content: '';
        position: absolute;
        bottom: calc(100% + 1px);
        left: 50%;
        transform: translateX(-50%);
        border: 4px solid transparent;
        border-top-color: #1f2937;
        opacity: 0;
        visibility: hidden;
        transition: all 0.15s ease;
        z-index: 101;
    }
    .highlights-active .error-highlight:hover::after,
    .highlights-active .error-highlight:hover::before {
        opacity: 1;
        visibility: visible;
    }
</style>
