<x-dashboard-layout>
    <x-slot:title>Human Evaluation Result</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header Card -->
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 shadow-sm">
            <div class="relative p-6 lg:p-8">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-[#C8102E]/5 to-transparent rounded-full -translate-y-1/2 translate-x-1/2"></div>

                <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <!-- Left: Title & Info -->
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-[#C8102E] flex items-center justify-center shadow-lg flex-shrink-0">
                            <i class="fas fa-user-tie text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-[#C8102E] font-semibold uppercase tracking-wider mb-1">Expert Assessment</p>
                            <h1 class="text-2xl font-bold text-gray-900">Human Evaluation Result</h1>
                            <div class="flex flex-wrap items-center gap-3 mt-3 text-sm text-gray-500">
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 px-3 py-1 rounded-lg">
                                    <i class="fas fa-book-open text-gray-400"></i>
                                    {{ $attempt->testSet->title }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 px-3 py-1 rounded-lg">
                                    <i class="fas fa-layer-group text-gray-400"></i>
                                    {{ ucfirst($attempt->testSet->section->name) }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 px-3 py-1 rounded-lg">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                    {{ $evaluation->updated_at->format('M d, Y') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Score & Teacher -->
                    <div class="flex items-center gap-4">
                        <!-- Overall Band Score -->
                        <div class="bg-gray-50 border border-gray-200 rounded-2xl px-6 py-4 text-center">
                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Overall Band</p>
                            <p class="text-4xl font-black text-[#C8102E]">
                                {{ bandScoreRange($evaluation->overall_band_score) }}
                            </p>
                            <div class="mt-2 flex items-center justify-center gap-0.5">
                                @for($i = 1; $i <= 9; $i++)
                                    <div class="w-1.5 h-3 rounded-full {{ $i <= floor($evaluation->overall_band_score) ? 'bg-[#C8102E]' : 'bg-gray-200' }}"></div>
                                @endfor
                            </div>
                        </div>

                        <!-- Teacher Info -->
                        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4" style="min-width: 160px;">
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Evaluated by</p>
                            <div class="flex items-center gap-3">
                                @if($evaluation->evaluator->avatar_url)
                                    <img src="{{ $evaluation->evaluator->avatar_url }}"
                                         alt="{{ $evaluation->evaluator->name }}"
                                         class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gray-900 flex items-center justify-center text-white font-bold">
                                        {{ substr($evaluation->evaluator->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $evaluation->evaluator->name }}</p>
                                    <p class="text-xs text-gray-500 flex items-center gap-1">
                                        <i class="fas fa-certificate text-[#C8102E]"></i>
                                        IELTS Expert
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Evaluations -->
        @foreach($evaluation->task_scores as $index => $taskScore)
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 shadow-sm">
                <!-- Task Header -->
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#C8102E] flex items-center justify-center text-white font-bold text-lg shadow">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Task {{ $index + 1 }} Evaluation</h3>
                                <p class="text-xs text-gray-500">Detailed Assessment & Feedback</p>
                            </div>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-xl px-4 py-2 text-center shadow-sm">
                            <p class="text-xs text-gray-500">Band Score</p>
                            <p class="text-2xl font-bold text-[#C8102E]">{{ number_format($taskScore['score'], 1) }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Criteria Scores -->
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-chart-bar text-[#C8102E]"></i>
                            Assessment Criteria
                        </h4>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-bullseye text-blue-600"></i>
                                    <span class="text-2xl font-bold text-blue-600">{{ $taskScore['task_achievement'] }}</span>
                                </div>
                                <p class="text-sm font-medium text-gray-700">Task Response</p>
                            </div>

                            <div class="bg-violet-50 border border-violet-200 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-link text-violet-600"></i>
                                    <span class="text-2xl font-bold text-violet-600">{{ $taskScore['coherence_cohesion'] }}</span>
                                </div>
                                <p class="text-sm font-medium text-gray-700">Coherence & Cohesion</p>
                            </div>

                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-book text-amber-600"></i>
                                    <span class="text-2xl font-bold text-amber-600">{{ $taskScore['lexical_resource'] }}</span>
                                </div>
                                <p class="text-sm font-medium text-gray-700">Lexical Resource</p>
                            </div>

                            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-spell-check text-red-600"></i>
                                    <span class="text-2xl font-bold text-red-600">{{ $taskScore['grammar'] }}</span>
                                </div>
                                <p class="text-sm font-medium text-gray-700">Grammar</p>
                            </div>
                        </div>
                    </div>

                    <!-- Teacher's Feedback -->
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                        <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-comment-dots text-[#C8102E]"></i>
                            Expert Feedback
                        </h4>
                        <p class="text-gray-700 leading-relaxed">{{ $taskScore['feedback'] }}</p>
                    </div>

                    <!-- Student's Response with Errors -->
                    @php
                        $answer = $attempt->answers->where('question.part_number', $index + 1)->first();
                        if (!$answer) {
                            $answer = $attempt->answers->get($index);
                        }
                        $errorMarkings = $evaluation->errorMarkings->where('task_number', $index + 1);
                    @endphp

                    @if($answer && $errorMarkings->count() > 0)
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <i class="fas fa-edit text-[#C8102E]"></i>
                                Your Response with Marked Errors
                            </h4>

                            <!-- Error Legend -->
                            <div class="flex flex-wrap gap-2 mb-4">
                                <div class="flex items-center gap-2 bg-blue-50 px-3 py-1.5 rounded-lg text-sm font-medium border border-blue-200">
                                    <div class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                                    <span class="text-gray-700">Task Response</span>
                                </div>
                                <div class="flex items-center gap-2 bg-violet-50 px-3 py-1.5 rounded-lg text-sm font-medium border border-violet-200">
                                    <div class="w-2.5 h-2.5 bg-violet-500 rounded-full"></div>
                                    <span class="text-gray-700">Coherence & Cohesion</span>
                                </div>
                                <div class="flex items-center gap-2 bg-amber-50 px-3 py-1.5 rounded-lg text-sm font-medium border border-amber-200">
                                    <div class="w-2.5 h-2.5 bg-amber-500 rounded-full"></div>
                                    <span class="text-gray-700">Lexical Resource</span>
                                </div>
                                <div class="flex items-center gap-2 bg-red-50 px-3 py-1.5 rounded-lg text-sm font-medium border border-red-200">
                                    <div class="w-2.5 h-2.5 bg-red-500 rounded-full"></div>
                                    <span class="text-gray-700">Grammar</span>
                                </div>
                            </div>

                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                                <div id="markedResponse_{{ $index }}" class="text-gray-700 leading-relaxed whitespace-pre-wrap break-words">{{ $answer->answer }}</div>
                            </div>
                        </div>

                        <!-- Error Details -->
                        @if($errorMarkings->count() > 0)
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                                <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-exclamation-circle text-amber-500"></i>
                                        Marked Errors & Teacher Notes
                                    </span>
                                    <span class="text-xs bg-white px-3 py-1.5 rounded-lg border border-amber-200 font-semibold text-gray-700">
                                        {{ $errorMarkings->count() }} errors found
                                    </span>
                                </h4>

                                <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                                    @foreach($errorMarkings->sortBy('start_position') as $marking)
                                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-shrink-0 mt-1">
                                                    @php
                                                        $iconColor = match($marking->error_type) {
                                                            'task_achievement' => 'text-blue-500',
                                                            'coherence_cohesion' => 'text-violet-500',
                                                            'lexical_resource' => 'text-amber-500',
                                                            'grammar' => 'text-red-500',
                                                            default => 'text-gray-500'
                                                        };
                                                    @endphp
                                                    <i class="fas fa-exclamation-triangle {{ $iconColor }}"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-lg text-white
                                                            {{ match($marking->error_type) {
                                                                'task_achievement' => 'bg-blue-600',
                                                                'coherence_cohesion' => 'bg-violet-600',
                                                                'lexical_resource' => 'bg-amber-600',
                                                                'grammar' => 'bg-red-600',
                                                                default => 'bg-gray-600'
                                                            } }}
                                                        ">
                                                            {{ $marking->getErrorTypeLabel() }}
                                                        </span>
                                                    </div>
                                                    <div class="mb-2">
                                                        <span class="text-xs text-gray-500">Marked text:</span>
                                                        <span class="text-sm font-semibold bg-gray-100 px-2 py-1 rounded ml-2 text-gray-900">
                                                            "{{ Str::limit($marking->marked_text, 60) }}"
                                                        </span>
                                                    </div>
                                                    @if($marking->comment && trim($marking->comment) !== '')
                                                        <div class="bg-blue-50 border-l-4 border-blue-500 pl-3 py-2 rounded-r">
                                                            <p class="text-sm text-gray-700">
                                                                <i class="fas fa-sticky-note text-blue-500 mr-2"></i>
                                                                {{ $marking->comment }}
                                                            </p>
                                                        </div>
                                                    @elseif($marking->note && trim($marking->note) !== '')
                                                        <div class="bg-blue-50 border-l-4 border-blue-500 pl-3 py-2 rounded-r">
                                                            <p class="text-sm text-gray-700">
                                                                <i class="fas fa-sticky-note text-blue-500 mr-2"></i>
                                                                {{ $marking->note }}
                                                            </p>
                                                        </div>
                                                    @else
                                                        <div class="bg-gray-100 border-l-4 border-gray-300 pl-3 py-2 rounded-r">
                                                            <p class="text-xs italic text-gray-500">
                                                                No teacher note provided
                                                            </p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Error Summary -->
                            <div class="bg-white border border-gray-200 rounded-xl p-5">
                                <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="fas fa-chart-pie text-[#C8102E]"></i>
                                    Error Summary Statistics
                                </h4>
                                @php
                                    $errorsByType = $errorMarkings->groupBy('error_type');
                                @endphp
                                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                    @foreach(['task_achievement', 'coherence_cohesion', 'lexical_resource', 'grammar'] as $errorType)
                                        @php
                                            $typeErrors = $errorsByType->get($errorType, collect());
                                            $count = $typeErrors->count();
                                            $withComments = $typeErrors->filter(function($e) {
                                                return ($e->comment && trim($e->comment) !== '') || ($e->note && trim($e->note) !== '');
                                            })->count();
                                            $model = new \App\Models\EvaluationErrorMarking(['error_type' => $errorType]);
                                        @endphp
                                        <div class="bg-gray-50 border {{ match($errorType) {
                                            'task_achievement' => 'border-blue-200',
                                            'coherence_cohesion' => 'border-violet-200',
                                            'lexical_resource' => 'border-amber-200',
                                            'grammar' => 'border-red-200',
                                        } }} rounded-xl p-4 text-center">
                                            <p class="text-xs font-semibold text-gray-600 mb-1">{{ $model->getErrorTypeLabel() }}</p>
                                            <p class="text-3xl font-bold {{ $count > 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                                {{ $count }}
                                            </p>
                                            @if($count > 0)
                                                <p class="text-xs mt-1 text-gray-500">
                                                    {{ $withComments }} with notes
                                                </p>
                                            @else
                                                <p class="text-xs mt-1 text-emerald-500 font-medium">
                                                    <i class="fas fa-check-circle"></i> Perfect
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            @push('scripts')
            <script>
                (function() {
                    const container = document.getElementById('markedResponse_{{ $index }}');
                    if (!container) return;

                    const originalText = container.textContent;
                    const markings = @json($errorMarkings->values());

                    markings.sort((a, b) => b.start_position - a.start_position);

                    let markedText = originalText;
                    markings.forEach((marking) => {
                        const before = markedText.substring(0, marking.start_position);
                        const marked = markedText.substring(marking.start_position, marking.end_position);
                        const after = markedText.substring(marking.end_position);

                        const colorClass = {
                            'task_achievement': 'bg-blue-100 text-blue-800 border-blue-300',
                            'coherence_cohesion': 'bg-violet-100 text-violet-800 border-violet-300',
                            'lexical_resource': 'bg-amber-100 text-amber-800 border-amber-300',
                            'grammar': 'bg-red-100 text-red-800 border-red-300'
                        }[marking.error_type] || 'bg-gray-100 text-gray-800 border-gray-300';

                        const hasNote = (marking.comment || marking.note) && (marking.comment || marking.note).trim() !== '';
                        const noteIcon = hasNote ? '<i class="fas fa-comment-dots text-xs ml-1"></i>' : '';

                        markedText = before +
                            `<span class="marked-error border ${colorClass} cursor-pointer hover:opacity-80 transition-opacity rounded px-1"
                                   data-error-id="${marking.id}"
                                   style="font-weight: 500;">${marked}${noteIcon}</span>` +
                            after;
                    });

                    container.innerHTML = markedText;

                    container.addEventListener('click', function(e) {
                        const markedError = e.target.closest('.marked-error');
                        if (markedError) {
                            e.preventDefault();
                            const errorId = markedError.dataset.errorId;
                            const marking = markings.find(m => m.id == errorId);
                            if (marking) {
                                showErrorPopup(marking, markedError);
                            }
                        }
                    });
                })();

                function showErrorPopup(marking, element) {
                    document.querySelectorAll('.error-popup').forEach(popup => popup.remove());

                    const errorLabels = {
                        'task_achievement': 'Task Response',
                        'coherence_cohesion': 'Coherence & Cohesion',
                        'lexical_resource': 'Lexical Resource',
                        'grammar': 'Grammatical Range & Accuracy'
                    };

                    const popup = document.createElement('div');
                    popup.className = 'error-popup fixed z-50 bg-white rounded-xl shadow-2xl border border-gray-200 p-4 max-w-md';

                    const noteContent = marking.comment || marking.note || '';

                    popup.innerHTML = `
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-${
                                    marking.error_type === 'task_achievement' ? 'blue' :
                                    marking.error_type === 'coherence_cohesion' ? 'violet' :
                                    marking.error_type === 'lexical_resource' ? 'amber' :
                                    marking.error_type === 'grammar' ? 'red' : 'gray'
                                }-500"></div>
                                <span class="text-sm font-bold text-gray-900">${errorLabels[marking.error_type]}</span>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="text-xs text-gray-500 mb-3 bg-gray-50 px-3 py-2 rounded-lg border border-gray-100">
                            <strong>Marked:</strong> "${marking.marked_text.substring(0, 50)}${marking.marked_text.length > 50 ? '...' : ''}"
                        </div>
                        <div class="text-sm ${noteContent ? 'bg-blue-50 border-l-4 border-blue-500 pl-3 py-2 rounded-r' : 'bg-gray-50 px-3 py-2 rounded-lg'} text-gray-700">
                            ${noteContent ?
                                `<i class="fas fa-sticky-note text-blue-500 mr-2"></i>${noteContent}` :
                                `<i class="fas fa-info-circle text-gray-400 mr-2"></i><span class="text-gray-500 italic">No teacher note provided</span>`
                            }
                        </div>
                    `;

                    document.body.appendChild(popup);

                    const rect = element.getBoundingClientRect();
                    popup.style.left = Math.max(20, Math.min(rect.left, window.innerWidth - popup.offsetWidth - 20)) + 'px';
                    popup.style.top = (rect.bottom + 10) + 'px';

                    setTimeout(() => popup.remove(), 8000);
                }
            </script>
            @endpush
        @endforeach

        <!-- Overall Assessment -->
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i class="fas fa-lightbulb text-[#C8102E]"></i>
                Overall Assessment
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @if(!empty($evaluation->strengths))
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5">
                        <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-star text-amber-500"></i>
                            Your Strengths
                        </h3>
                        <ul class="space-y-3">
                            @foreach($evaluation->strengths as $strength)
                                <li class="flex items-start text-gray-700">
                                    <i class="fas fa-check-circle text-emerald-500 mr-3 mt-1 flex-shrink-0"></i>
                                    <span class="text-sm">{{ $strength }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($evaluation->improvements))
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                        <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-chart-line text-blue-500"></i>
                            Areas for Improvement
                        </h3>
                        <ul class="space-y-3">
                            @foreach($evaluation->improvements as $improvement)
                                <li class="flex items-start text-gray-700">
                                    <i class="fas fa-arrow-up text-blue-500 mr-3 mt-1 flex-shrink-0"></i>
                                    <span class="text-sm">{{ $improvement }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('student.results.show', $attempt) }}"
               class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-gray-100 border border-gray-200 text-gray-700 font-semibold hover:bg-gray-200 transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Back to Results
            </a>
            <button onclick="window.print()"
                    class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-gray-900 text-white font-semibold hover:bg-gray-800 transition-all shadow-lg">
                <i class="fas fa-download mr-2"></i> Download PDF
            </button>
        </div>
    </div>

    @push('styles')
    <style>
        .marked-error {
            transition: all 0.2s ease;
            display: inline;
        }

        .marked-error:hover {
            transform: translateY(-1px);
        }

        .error-popup {
            animation: slideUp 0.2s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media print {
            .error-popup, button, nav { display: none !important; }
        }
    </style>
    @endpush
</x-dashboard-layout>
