{{-- resources/views/student/results/show.blade.php --}}
<x-dashboard-layout>
    <x-slot:title>Test Result Details</x-slot>

    @php
        $sectionIcons = [
            'listening' => ['icon' => 'fa-headphones', 'color' => 'blue', 'gradient' => 'from-blue-500 to-blue-600'],
            'reading' => ['icon' => 'fa-book-open', 'color' => 'emerald', 'gradient' => 'from-emerald-500 to-emerald-600'],
            'writing' => ['icon' => 'fa-pen-fancy', 'color' => 'violet', 'gradient' => 'from-violet-500 to-violet-600'],
            'speaking' => ['icon' => 'fa-microphone', 'color' => 'orange', 'gradient' => 'from-orange-500 to-orange-600']
        ];
        $sectionData = $sectionIcons[$attempt->testSet->section->name] ?? ['icon' => 'fa-question', 'color' => 'gray', 'gradient' => 'from-gray-500 to-gray-600'];

        $startTime = $attempt->start_time;
        $endTime = $attempt->end_time ?? $attempt->updated_at;
        $totalSeconds = $startTime->diffInSeconds($endTime);
        $minutes = floor($totalSeconds / 60);
        $seconds = $totalSeconds % 60;

        $canRetake = $attempt->status === 'completed';
        $latestAttempt = \App\Models\StudentAttempt::getLatestAttempt($attempt->user_id, $attempt->test_set_id);
        $isLatestAttempt = $latestAttempt && $attempt->id === $latestAttempt->id;
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Hero Result Card -->
        <div class="relative bg-white rounded-3xl border border-gray-200 overflow-hidden mb-6 shadow-xl">
            <!-- Decorative Background -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-gradient-to-br {{ $sectionData['gradient'] }} opacity-5 rounded-full"></div>
                <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-gradient-to-br from-[#C8102E] to-[#A00E27] opacity-5 rounded-full"></div>
            </div>

            <div class="relative p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <!-- Test Info -->
                    <div class="flex items-start gap-4">
                        <div class="relative">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br {{ $sectionData['gradient'] }} flex items-center justify-center shadow-lg">
                                <i class="fas {{ $sectionData['icon'] }} text-white text-2xl"></i>
                            </div>
                            @if($attempt->testSet->is_premium)
                                <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-amber-400 to-yellow-500 rounded-full flex items-center justify-center shadow-md border-2 border-white">
                                    <i class="fas fa-crown text-white text-[10px]"></i>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs text-{{ $sectionData['color'] }}-600 font-semibold uppercase tracking-wider mb-1">{{ ucfirst($attempt->testSet->section->name) }} Test</p>
                            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">{{ $attempt->testSet->title }}</h1>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm mt-3">
                                <span class="inline-flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                    <span class="font-medium">{{ $attempt->created_at->format('M d, Y \a\t g:i A') }}</span>
                                </span>
                                <span class="text-gray-300">•</span>
                                <span class="inline-flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-stopwatch text-gray-400"></i>
                                    <span class="font-medium">{{ $minutes }}m {{ $seconds }}s</span>
                                </span>
                                <span class="text-gray-300">•</span>
                                <span class="inline-flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-check-circle text-gray-400"></i>
                                    @if(isset($answeredQuestions) && isset($totalQuestions) && $totalQuestions > 0)
                                        <span class="font-medium">{{ round(($answeredQuestions / $totalQuestions) * 100) }}%</span>
                                    @else
                                        <span class="font-medium">{{ $attempt->completion_rate }}%</span>
                                    @endif
                                </span>
                                <span class="text-gray-300">•</span>
                                <span class="inline-flex items-center gap-2">
                                    <i class="fas fa-trophy text-amber-500"></i>
                                    <span class="font-semibold text-amber-600">{{ \App\Helpers\ScoreCalculator::getBandDescription($attempt->band_score ?? 0) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Band Score Display -->
                    <div class="flex items-center gap-4">
                        @if($attempt->band_score)
                            <div class="relative">
                                <div class="w-32 h-32 lg:w-36 lg:h-36 rounded-full bg-gradient-to-br from-[#C8102E] to-[#A00E27] p-1 shadow-xl">
                                    <div class="w-full h-full bg-white rounded-full flex flex-col items-center justify-center px-2">
                                        <p class="text-[10px] text-gray-500 font-medium">Band Score</p>
                                        <p class="text-2xl lg:text-3xl font-black text-[#C8102E] whitespace-nowrap">{{ bandScoreRange($attempt->band_score) }}</p>
                                        <p class="text-[10px] text-gray-400">out of 9.0</p>
                                    </div>
                                </div>
                            </div>
                        @elseif(in_array($attempt->testSet->section->name, ['writing', 'speaking']))
                            <div class="text-center px-6 py-4 bg-gradient-to-br from-amber-50 to-yellow-50 rounded-2xl border border-amber-200">
                                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-hourglass-half text-amber-600"></i>
                                </div>
                                <p class="text-amber-700 font-semibold text-sm">Pending</p>
                                <p class="text-[10px] text-amber-600">Awaiting evaluation</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- No Answers Alert --}}
        @if(isset($answeredQuestions) && $answeredQuestions === 0)
            <div class="bg-gradient-to-r from-red-50 to-rose-50 rounded-2xl p-6 mb-6 border border-red-200">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-red-800 mb-1">No Questions Answered</h4>
                        <p class="text-red-700 text-sm mb-4">You did not answer any questions in this test. Your band score is 0.0 as no answers were submitted.</p>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('student.' . $attempt->testSet->section->name . '.onboarding.confirm-details', $attempt->testSet->id) }}"
                               class="inline-flex items-center px-5 py-2.5 bg-[#C8102E] text-white rounded-xl text-sm font-semibold hover:bg-[#A00E27] transition-all">
                                <i class="fas fa-redo mr-2"></i>Retake Test
                            </a>
                            <a href="{{ route('student.' . $attempt->testSet->section->name . '.index') }}"
                               class="inline-flex items-center px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition-all">
                                <i class="fas fa-list mr-2"></i>View All Tests
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                {{-- Score Breakdown for Listening/Reading --}}
                @if(in_array($attempt->testSet->section->name, ['listening', 'reading']))
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                        <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-chart-pie text-[#C8102E]"></i>
                                Score Breakdown
                            </h3>
                        </div>
                        <div class="p-5">
                            <!-- Score Cards -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                                <div class="text-center p-4 bg-gray-50 rounded-xl">
                                    <p class="text-2xl sm:text-3xl font-black text-gray-900">{{ $answeredQuestions }}</p>
                                    <p class="text-[10px] sm:text-xs text-gray-500 mt-1">of {{ $totalQuestions }} attempted</p>
                                </div>
                                <div class="text-center p-4 bg-emerald-50 rounded-xl">
                                    <p class="text-2xl sm:text-3xl font-black text-emerald-600">{{ $correctAnswers }}</p>
                                    <p class="text-[10px] sm:text-xs text-emerald-600 mt-1">Correct</p>
                                </div>
                                <div class="text-center p-4 bg-red-50 rounded-xl">
                                    <p class="text-2xl sm:text-3xl font-black text-red-600">{{ $answeredQuestions - $correctAnswers }}</p>
                                    <p class="text-[10px] sm:text-xs text-red-600 mt-1">Wrong</p>
                                </div>
                                <div class="text-center p-4 bg-[#C8102E]/5 rounded-xl">
                                    <p class="text-2xl sm:text-3xl font-black text-[#C8102E]">{{ bandScoreRange($attempt->band_score) }}</p>
                                    <p class="text-[10px] sm:text-xs text-[#C8102E] mt-1">Band Score</p>
                                </div>
                            </div>

                            <!-- Band Progress -->
                            <div class="relative pt-4">
                                <div class="flex justify-between mb-2">
                                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9] as $band)
                                        <span class="text-xs font-medium {{ $attempt->band_score >= $band ? 'text-[#C8102E]' : 'text-gray-300' }}">{{ $band }}</span>
                                    @endforeach
                                </div>
                                <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-[#C8102E] to-[#A00E27] rounded-full transition-all duration-1000" style="width: {{ ($attempt->band_score / 9) * 100 }}%"></div>
                                </div>
                            </div>

                            @if($answeredQuestions < $totalQuestions)
                                <div class="mt-4 p-3 bg-amber-50 rounded-xl border border-amber-200">
                                    <p class="text-xs text-amber-700">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Band score calculated based on {{ $correctAnswers }}/{{ $totalQuestions }} correct answers.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Evaluation Section for Writing/Speaking --}}
                @if(in_array($attempt->testSet->section->name, ['writing', 'speaking']))
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden" x-data="{ activeTab: 'ai' }">
                        <div class="border-b border-gray-200">
                            <div class="flex">
                                <button @click="activeTab = 'ai'"
                                        :class="activeTab === 'ai' ? 'text-[#C8102E] border-[#C8102E] bg-red-50/50' : 'text-gray-500 border-transparent hover:text-gray-700'"
                                        class="flex-1 px-6 py-4 text-sm font-semibold border-b-2 transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-robot"></i> AI Evaluation
                                </button>
                                <button @click="activeTab = 'human'"
                                        :class="activeTab === 'human' ? 'text-[#C8102E] border-[#C8102E] bg-red-50/50' : 'text-gray-500 border-transparent hover:text-gray-700'"
                                        class="flex-1 px-6 py-4 text-sm font-semibold border-b-2 transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-user-tie"></i> Human Evaluation
                                </button>
                            </div>
                        </div>

                        <!-- AI Tab -->
                        <div x-show="activeTab === 'ai'" class="p-6">
                            @if($attempt->completion_rate == 0)
                                <div class="text-center py-8">
                                    <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-exclamation-circle text-amber-600 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-700 font-medium mb-2">Test Not Completed</p>
                                    <p class="text-gray-500 text-sm">Complete the test first to get AI evaluation.</p>
                                </div>
                            @elseif(!$attempt->ai_evaluated_at)
                                <div class="text-center py-8">
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">Get Instant AI Feedback</h4>
                                    <p class="text-gray-500 text-sm mb-6 max-w-sm mx-auto">Our AI will analyze your response and provide detailed feedback with band score prediction.</p>
                                    <button onclick="startAIEvaluation({{ $attempt->id }}, '{{ $attempt->testSet->section->name }}')"
                                            id="ai-eval-btn"
                                            class="px-8 py-3 bg-gradient-to-r from-[#C8102E] to-[#A00E27] text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                        <i class="fas fa-magic mr-2"></i>Get Instant Evaluation
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-emerald-50 to-green-50 rounded-xl border border-emerald-200">
                                    <div>
                                        <p class="text-xs text-emerald-600 font-medium">AI Band Score</p>
                                        <p class="text-3xl font-black text-emerald-700">{{ $attempt->ai_band_score ? bandScoreRange($attempt->ai_band_score) : 'N/A' }}</p>
                                    </div>
                                    <a href="{{ route('ai.evaluation.get', $attempt->id) }}"
                                       class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-all">
                                        <i class="fas fa-chart-line mr-2"></i>View Analysis
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Human Tab -->
                        <div x-show="activeTab === 'human'" x-cloak class="p-6">
                            @if(isset($humanEvaluationRequest) && $humanEvaluationRequest)
                                @if($humanEvaluationRequest->status === 'completed')
                                    <div class="text-center py-4">
                                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-violet-50 to-purple-50 rounded-xl border border-violet-200 mb-4">
                                            <div class="text-left">
                                                <p class="text-xs text-violet-600 font-medium">Evaluated by {{ $humanEvaluationRequest->teacher?->user?->name ?? 'Teacher' }}</p>
                                                <p class="text-3xl font-black text-violet-700">{{ $humanEvaluationRequest->humanEvaluation->overall_band_score ? bandScoreRange($humanEvaluationRequest->humanEvaluation->overall_band_score) : 'N/A' }}</p>
                                            </div>
                                            <div class="w-12 h-12 bg-violet-100 rounded-xl flex items-center justify-center">
                                                <i class="fas fa-check-circle text-violet-600 text-xl"></i>
                                            </div>
                                        </div>
                                        <a href="{{ route('student.evaluation.result', $attempt->id) }}"
                                           class="inline-flex items-center px-6 py-3 bg-violet-600 text-white rounded-xl font-semibold hover:bg-violet-700 transition-all">
                                            <i class="fas fa-eye mr-2"></i>View Detailed Evaluation
                                        </a>
                                    </div>
                                @else
                                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-clock text-amber-600"></i>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-amber-800">Evaluation In Progress</p>
                                                @if($humanEvaluationRequest->teacher && $humanEvaluationRequest->teacher->user)
                                                    <p class="text-sm text-amber-700 mt-1">Assigned to <strong>{{ $humanEvaluationRequest->teacher->user->name }}</strong></p>
                                                @else
                                                    <p class="text-sm text-amber-700 mt-1">Waiting to be assigned to a teacher</p>
                                                @endif
                                                @if($humanEvaluationRequest->deadline_at)
                                                    <p class="text-xs text-amber-600 mt-1">Deadline: {{ $humanEvaluationRequest->deadline_at->format('M d, Y h:i A') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                @if(auth()->user()->isOfflineStudent())
                                    <div class="text-center py-8">
                                        <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <i class="fas fa-clock text-amber-600 text-2xl"></i>
                                        </div>
                                        <h4 class="text-lg font-bold text-gray-900 mb-2">Awaiting Teacher Evaluation</h4>
                                        <p class="text-gray-500 text-sm">Your submission has been sent for teacher evaluation.</p>
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <h4 class="text-lg font-bold text-gray-900 mb-2">Get Expert Feedback</h4>
                                        <p class="text-gray-500 text-sm mb-6">Get your work evaluated by certified IELTS teachers.</p>
                                        <a href="{{ route('student.evaluation.teachers', $attempt->id) }}"
                                           class="inline-flex items-center px-6 py-3 bg-violet-600 text-white rounded-xl font-semibold hover:bg-violet-700 transition-all">
                                            <i class="fas fa-search mr-2"></i>Choose Teacher
                                        </a>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Question Analysis for Listening/Reading --}}
                @if(in_array($attempt->testSet->section->name, ['listening', 'reading']))
                    <div id="question-analysis" class="bg-white rounded-2xl border border-gray-200 overflow-hidden"
                         x-data="{ activePart: '{{ $attempt->testSet->questions()->where('question_type', '!=', 'passage')->whereNotNull('part_number')->reorder()->orderBy('part_number')->value('part_number') ?? 1 }}' }">
                        <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-microscope text-[#C8102E]"></i>
                                    Question Analysis
                                </h3>
                                <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ $totalQuestions }} Questions</span>
                            </div>
                            @php
                                // Get unique part numbers for tabs
                                $partNumbers = $attempt->testSet->questions()
                                    ->where('question_type', '!=', 'passage')
                                    ->whereNotNull('part_number')
                                    ->distinct()
                                    ->reorder()
                                    ->orderBy('part_number')
                                    ->pluck('part_number')
                                    ->unique()
                                    ->values()
                                    ->toArray();
                            @endphp
                            @if(count($partNumbers) >= 1)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($partNumbers as $partNum)
                                        <button @click="activePart = '{{ $partNum }}'"
                                                :class="activePart === '{{ $partNum }}' ? 'bg-[#C8102E] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                                class="px-4 py-2 rounded-lg text-sm font-medium transition-all">
                                            Part {{ $partNum }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="p-5">
                            @php
                                $isPremium = true;
                                // ... (keeping the same question processing logic)
                                $allQuestions = $attempt->testSet->questions()
                                    ->where('question_type', '!=', 'passage')
                                    ->orderBy('part_number')
                                    ->orderBy('order_number')
                                    ->get();

                                $displayQuestions = [];
                                $currentNumber = 1;
                                $masterQuestionIds = [];

                                foreach ($allQuestions as $question) {
                                    if ($question->isMasterMatchingHeading()) {
                                        if (!in_array($question->id, $masterQuestionIds)) {
                                            $masterQuestionIds[] = $question->id;
                                            $mappings = $question->section_specific_data['mappings'] ?? [];
                                            $headings = $question->section_specific_data['headings'] ?? [];
                                            $masterAnswers = $attempt->answers->filter(function($answer) use ($question) {
                                                return $answer->question_id == $question->id;
                                            });

                                            foreach ($mappings as $mapping) {
                                                $subQuestionNum = $mapping['question'] ?? $mapping['number'] ?? $currentNumber;
                                                $paragraphLabel = $mapping['paragraph'] ?? chr(65 + array_search($mapping, $mappings));
                                                $correctLetter = $mapping['correct'] ?? null;
                                                $correctHeadingText = null;
                                                if ($correctLetter) {
                                                    foreach ($headings as $heading) {
                                                        if ($heading['id'] === $correctLetter) {
                                                            $correctHeadingText = $heading['text'] ?? null;
                                                            break;
                                                        }
                                                    }
                                                }
                                                $specificAnswer = $masterAnswers->first(function($answer) use ($subQuestionNum) {
                                                    if ($answer->answer) {
                                                        $decoded = json_decode($answer->answer, true);
                                                        return isset($decoded['sub_question']) && $decoded['sub_question'] == $subQuestionNum;
                                                    }
                                                    return false;
                                                });

                                                $displayQuestions[] = [
                                                    'number' => $currentNumber,
                                                    'question' => $question,
                                                    'content' => "Choose the correct heading for Paragraph {$paragraphLabel}",
                                                    'answer' => $specificAnswer,
                                                    'is_master_sub' => true,
                                                    'sub_question' => $subQuestionNum,
                                                    'correct_letter' => $correctLetter,
                                                    'correct_heading_text' => $correctHeadingText,
                                                    'all_headings' => $headings,
                                                    'part_number' => $question->part_number
                                                ];
                                                $currentNumber++;
                                            }
                                        }
                                    } elseif ($question->question_type === 'sentence_completion' && isset($question->section_specific_data['sentence_completion'])) {
                                        $scData = $question->section_specific_data['sentence_completion'];
                                        $sentences = $scData['sentences'] ?? [];
                                        foreach ($sentences as $sentenceIndex => $sentence) {
                                            $questionNumber = $sentence['questionNumber'] ?? ($sentenceIndex + 1);
                                            $specificAnswer = $attempt->answers->first(function($ans) use ($question, $questionNumber) {
                                                if ($ans->question_id != $question->id) return false;
                                                $answerData = json_decode($ans->answer, true);
                                                if (is_array($answerData) && isset($answerData['sub_question'])) {
                                                    return (int)$answerData['sub_question'] === $questionNumber;
                                                }
                                                return false;
                                            });
                                            $displayQuestions[] = [
                                                'number' => $currentNumber,
                                                'question' => $question,
                                                'content' => $sentence['text'] ?? "Sentence " . ($sentenceIndex + 1),
                                                'answer' => $specificAnswer,
                                                'is_sentence_completion' => true,
                                                'sentence_index' => $sentenceIndex,
                                                'question_number' => $questionNumber,
                                                'correct_answer' => $sentence['correctAnswer'] ?? $sentence['correct_answer'] ?? null,
                                                'part_number' => $question->part_number
                                            ];
                                            $currentNumber++;
                                        }
                                    } elseif ($question->question_type === 'drag_drop') {
                                        $dragDropData = $question->section_specific_data ?? [];
                                        $dropZones = $dragDropData['drop_zones'] ?? [];
                                        $answer = $attempt->answers->where('question_id', $question->id)->first();
                                        foreach ($dropZones as $zoneIndex => $zone) {
                                            $displayQuestions[] = [
                                                'number' => $currentNumber,
                                                'question' => $question,
                                                'content' => $zone['text'] ?? "Drop Zone " . ($zoneIndex + 1),
                                                'answer' => $answer,
                                                'is_drag_drop' => true,
                                                'zone_index' => $zoneIndex,
                                                'correct_answer' => $zone['correct_answer'] ?? $zone['answer'] ?? null,
                                                'part_number' => $question->part_number
                                            ];
                                            $currentNumber++;
                                        }
                                    } elseif ($question->question_type === 'fill_blanks') {
                                        $answer = $attempt->answers->where('question_id', $question->id)->first();
                                        preg_match_all('/\[____(\d+)____\]/', $question->content, $matches, PREG_SET_ORDER);
                                        $blankCount = count($matches);
                                        if ($blankCount > 0) {
                                            foreach ($matches as $index => $match) {
                                                $blankNum = $match[1] ?? ($index + 1);
                                                $cleanContent = preg_replace('/\[____\d+____\]/', '___', $question->content);
                                                $cleanContent = strip_tags($cleanContent);
                                                $displayQuestions[] = [
                                                    'number' => $currentNumber,
                                                    'question' => $question,
                                                    'content' => $cleanContent,
                                                    'answer' => $answer,
                                                    'is_fill_blank' => true,
                                                    'blank_number' => $blankNum,
                                                    'part_number' => $question->part_number
                                                ];
                                                $currentNumber++;
                                            }
                                        } else {
                                            $displayQuestions[] = [
                                                'number' => $currentNumber,
                                                'question' => $question,
                                                'content' => strip_tags($question->content),
                                                'answer' => $answer,
                                                'is_fill_blank' => true,
                                                'blank_number' => 1,
                                                'part_number' => $question->part_number
                                            ];
                                            $currentNumber++;
                                        }
                                    } elseif ($question->question_type === 'dropdown_selection') {
                                        $answer = $attempt->answers->where('question_id', $question->id)->first();
                                        preg_match_all('/\[DROPDOWN_(\d+)\]/', $question->content, $matches, PREG_SET_ORDER);
                                        $dropdownCount = count($matches);
                                        if ($dropdownCount > 0) {
                                            foreach ($matches as $index => $match) {
                                                $dropdownNum = $match[1] ?? ($index + 1);
                                                $cleanContent = preg_replace('/\[DROPDOWN_\d+\]/', '___', $question->content);
                                                $cleanContent = strip_tags($cleanContent);
                                                $displayQuestions[] = [
                                                    'number' => $currentNumber,
                                                    'question' => $question,
                                                    'content' => $cleanContent,
                                                    'answer' => $answer,
                                                    'is_dropdown' => true,
                                                    'dropdown_index' => $dropdownNum,
                                                    'part_number' => $question->part_number
                                                ];
                                                $currentNumber++;
                                            }
                                        } else {
                                            $displayQuestions[] = [
                                                'number' => $currentNumber,
                                                'question' => $question,
                                                'content' => strip_tags($question->content),
                                                'answer' => $answer,
                                                'is_dropdown' => true,
                                                'dropdown_index' => 1,
                                                'part_number' => $question->part_number
                                            ];
                                            $currentNumber++;
                                        }
                                    } elseif ($question->question_type === 'multiple_choice') {
                                        $correctCount = $question->options->where('is_correct', true)->count();
                                        $correctOptions = $question->options->where('is_correct', true)->values();
                                        $questionAnswers = $attempt->answers->where('question_id', $question->id);
                                        $answer = $questionAnswers->first();
                                        $selectedOptionIds = [];
                                        foreach ($questionAnswers as $ans) {
                                            if ($ans->selected_option_id) {
                                                $selectedOptionIds[] = $ans->selected_option_id;
                                            } elseif ($ans->answer) {
                                                $decoded = json_decode($ans->answer, true);
                                                if (is_array($decoded)) {
                                                    $selectedOptionIds = array_merge($selectedOptionIds, $decoded);
                                                }
                                            }
                                        }
                                        $selectedOptionIds = array_values(array_unique($selectedOptionIds));

                                        if ($correctCount > 1) {
                                            for ($i = 0; $i < $correctCount; $i++) {
                                                $selectedOptionId = $selectedOptionIds[$i] ?? null;
                                                $selectedOption = $selectedOptionId ? $question->options->firstWhere('id', $selectedOptionId) : null;
                                                $displayQuestions[] = [
                                                    'number' => $currentNumber,
                                                    'question' => $question,
                                                    'content' => strip_tags($question->content),
                                                    'answer' => $answer,
                                                    'is_multiple_choice' => true,
                                                    'choice_index' => $i,
                                                    'all_selected_ids' => $selectedOptionIds,
                                                    'selected_option' => $selectedOption,
                                                    'correct_option' => $correctOptions[$i] ?? null,
                                                    'part_number' => $question->part_number
                                                ];
                                                $currentNumber++;
                                            }
                                        } else {
                                            $selectedOption = !empty($selectedOptionIds) ? $question->options->firstWhere('id', $selectedOptionIds[0]) : null;
                                            $displayQuestions[] = [
                                                'number' => $currentNumber,
                                                'question' => $question,
                                                'content' => strip_tags($question->content),
                                                'answer' => $answer,
                                                'is_multiple_choice' => true,
                                                'choice_index' => 0,
                                                'all_selected_ids' => $selectedOptionIds,
                                                'selected_option' => $selectedOption,
                                                'correct_option' => $correctOptions[0] ?? null,
                                                'part_number' => $question->part_number
                                            ];
                                            $currentNumber++;
                                        }
                                    } else {
                                        $answer = $attempt->answers->where('question_id', $question->id)->first();
                                        $displayQuestions[] = [
                                            'number' => $currentNumber,
                                            'question' => $question,
                                            'content' => strip_tags($question->content),
                                            'answer' => $answer,
                                            'is_regular' => true,
                                            'part_number' => $question->part_number
                                        ];
                                        $currentNumber++;
                                    }
                                }

                                $startIndex = ($currentPage - 1) * $perPage;
                                $endIndex = $startIndex + $perPage;
                            @endphp

                            <div class="space-y-3">
                                @foreach($displayQuestions as $qIndex => $item)
                                    @php
                                        $question = $item['question'];
                                        $answer = $item['answer'];
                                        $isAnswered = !empty($answer);
                                        $isCorrect = false;
                                        $displayAnswer = 'No answer';

                                        // Answer processing logic (same as before but condensed)
                                        if (isset($item['is_drag_drop']) && $item['is_drag_drop'] && $isAnswered && $answer) {
                                            $answerData = @json_decode($answer->answer, true);
                                            if (is_array($answerData)) {
                                                $zoneIndex = $item['zone_index'];
                                                $zoneKey = 'zone_' . $zoneIndex;
                                                $oldZoneKey = 'zone_' . ($zoneIndex + 1);
                                                $studentAnswer = $answerData[$zoneKey] ?? $answerData[$oldZoneKey] ?? null;
                                                if ($studentAnswer !== null) {
                                                    $displayAnswer = $studentAnswer;
                                                    $correctAnswer = $item['correct_answer'];
                                                    $isCorrect = ($correctAnswer && $studentAnswer === $correctAnswer);
                                                }
                                            }
                                        } elseif (isset($item['is_fill_blank']) && $item['is_fill_blank'] && $isAnswered && $answer) {
                                            $answerData = @json_decode($answer->answer, true);
                                            if (is_array($answerData)) {
                                                $blankNum = $item['blank_number'];
                                                $studentAnswer = $answerData['blank_' . $blankNum] ?? null;
                                                if ($studentAnswer !== null) {
                                                    $displayAnswer = $studentAnswer;
                                                    $isCorrect = $question->checkBlankAnswer($blankNum, $studentAnswer);
                                                }
                                            }
                                        } elseif (isset($item['dropdown_index']) && isset($item['is_dropdown']) && $item['is_dropdown'] && $isAnswered && $answer) {
                                            $answerData = @json_decode($answer->answer, true);
                                            if (is_array($answerData)) {
                                                $dropdownNum = $item['dropdown_index'];
                                                $studentDropdownAnswer = $answerData['dropdown_' . $dropdownNum] ?? null;
                                                if ($studentDropdownAnswer !== null) {
                                                    $displayAnswer = $studentDropdownAnswer;
                                                    if ($question->section_specific_data && isset($question->section_specific_data['dropdown_correct'][$dropdownNum])) {
                                                        $correctIndex = $question->section_specific_data['dropdown_correct'][$dropdownNum];
                                                        $dropdownOptions = $question->section_specific_data['dropdown_options'][$dropdownNum] ?? '';
                                                        if ($dropdownOptions) {
                                                            $options = array_map('trim', explode(',', $dropdownOptions));
                                                            $correctOption = $options[$correctIndex] ?? '';
                                                            $isCorrect = (strtolower(trim($studentDropdownAnswer)) === strtolower(trim($correctOption)));
                                                        }
                                                    }
                                                }
                                            }
                                        } elseif (isset($item['is_multiple_choice']) && $item['is_multiple_choice']) {
                                            $selectedOption = $item['selected_option'] ?? null;
                                            if ($selectedOption) {
                                                $displayAnswer = $selectedOption->content;
                                                $isCorrect = $selectedOption->is_correct;
                                                $isAnswered = true;
                                            } elseif ($answer) {
                                                // Fallback: use choice_index to pick correct selection from stored answer
                                                $choiceIdx = $item['choice_index'] ?? 0;
                                                $allSelectedIds = $item['all_selected_ids'] ?? [];

                                                if (!empty($allSelectedIds) && isset($allSelectedIds[$choiceIdx])) {
                                                    $opt = $question->options->firstWhere('id', $allSelectedIds[$choiceIdx]);
                                                    if ($opt) {
                                                        $displayAnswer = $opt->content;
                                                        $isCorrect = $opt->is_correct;
                                                        $isAnswered = true;
                                                    }
                                                } elseif ($answer->selected_option_id) {
                                                    $opt = $question->options->firstWhere('id', $answer->selected_option_id);
                                                    if ($opt) {
                                                        $displayAnswer = $opt->content;
                                                        $isCorrect = $opt->is_correct;
                                                        $isAnswered = true;
                                                    }
                                                } elseif ($answer->answer) {
                                                    $decoded = @json_decode($answer->answer, true);
                                                    if (is_array($decoded) && isset($decoded[$choiceIdx])) {
                                                        $opt = $question->options->firstWhere('id', $decoded[$choiceIdx]);
                                                        if ($opt) {
                                                            $displayAnswer = $opt->content;
                                                            $isCorrect = $opt->is_correct;
                                                            $isAnswered = true;
                                                        }
                                                    }
                                                }
                                                if (!$isAnswered) {
                                                    $displayAnswer = 'Not attempted';
                                                }
                                            } else {
                                                $displayAnswer = 'Not attempted';
                                                $isAnswered = false;
                                            }
                                        } elseif ($isAnswered && $answer) {
                                            if (isset($item['is_master_sub']) && $item['is_master_sub']) {
                                                $decoded = json_decode($answer->answer, true);
                                                $selectedLetter = $decoded['selected_letter'] ?? null;
                                                $selectedHeadingText = null;
                                                if ($selectedLetter && isset($item['all_headings'])) {
                                                    foreach ($item['all_headings'] as $heading) {
                                                        if ($heading['id'] === $selectedLetter) {
                                                            $selectedHeadingText = $heading['text'] ?? null;
                                                            break;
                                                        }
                                                    }
                                                }
                                                $displayAnswer = $selectedHeadingText ? $selectedHeadingText : ($selectedLetter ? "Option {$selectedLetter}" : 'No answer');
                                                $isCorrect = $selectedLetter && $selectedLetter === $item['correct_letter'];
                                            } elseif ($answer->selectedOption) {
                                                $displayAnswer = $answer->selectedOption->content;
                                                $isCorrect = $answer->selectedOption->is_correct;
                                            } elseif (isset($item['is_sentence_completion']) && $item['is_sentence_completion']) {
                                                if ($answer && $answer->answer) {
                                                    $answerData = json_decode($answer->answer, true);
                                                    if (is_array($answerData) && isset($answerData['sub_question']) && isset($answerData['selected_answer'])) {
                                                        $questionNum = (int)$answerData['sub_question'];
                                                        $questionNumber = $item['question_number'] ?? $item['number'];
                                                        if ($questionNum == $questionNumber) {
                                                            $displayAnswer = $answerData['selected_answer'] ? "Option {$answerData['selected_answer']}" : 'No answer';
                                                            $isCorrect = $answerData['selected_answer'] && $answerData['selected_answer'] === $item['correct_answer'];
                                                        }
                                                    }
                                                }
                                            } elseif ($answer->answer) {
                                                $answerData = @json_decode($answer->answer, true);
                                                if (is_array($answerData)) {
                                                    $displayParts = [];
                                                    foreach ($answerData as $key => $value) {
                                                        if (!empty($value)) $displayParts[] = $value;
                                                    }
                                                    $displayAnswer = implode(', ', $displayParts);
                                                    $allCorrect = true;
                                                    if ($question->section_specific_data && isset($question->section_specific_data['blank_answers'])) {
                                                        foreach ($question->section_specific_data['blank_answers'] as $num => $correctAnswer) {
                                                            $studentAnswer = $answerData['blank_' . $num] ?? '';
                                                            if (!$question->checkBlankAnswer($num, $studentAnswer)) {
                                                                $allCorrect = false;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    $isCorrect = $allCorrect;
                                                } else {
                                                    $displayAnswer = $answer->answer;
                                                }
                                            }
                                        }
                                    @endphp

                                    @php
                                        // Get correct answer for explanation
                                        $correctAnswerForExplain = '';
                                        if (isset($item['is_drag_drop']) && $item['is_drag_drop']) {
                                            $correctAnswerForExplain = $item['correct_answer'] ?? '';
                                        } elseif (isset($item['is_fill_blank']) && $item['is_fill_blank']) {
                                            $blankNum = $item['blank_number'];
                                            $blankAnswers = $question->getBlankAnswersArray();
                                            $correctAnswerForExplain = $blankAnswers[$blankNum] ?? '';
                                        } elseif (isset($item['dropdown_index']) && isset($item['is_dropdown']) && $item['is_dropdown']) {
                                            $dropdownNum = $item['dropdown_index'];
                                            $correctIndex = $question->section_specific_data['dropdown_correct'][$dropdownNum] ?? null;
                                            $dropdownOptions = $question->section_specific_data['dropdown_options'][$dropdownNum] ?? '';
                                            if ($dropdownOptions && $correctIndex !== null) {
                                                $options = array_map('trim', explode(',', $dropdownOptions));
                                                $correctAnswerForExplain = $options[$correctIndex] ?? '';
                                            }
                                        } elseif (isset($item['is_master_sub']) && $item['is_master_sub']) {
                                            $correctAnswerForExplain = $item['correct_heading_text'] ?? 'Option ' . $item['correct_letter'];
                                        } elseif (isset($item['is_sentence_completion']) && $item['is_sentence_completion']) {
                                            $correctAnswerForExplain = 'Option ' . $item['correct_answer'];
                                        } elseif (isset($item['is_multiple_choice']) && $item['is_multiple_choice']) {
                                            $correctAnswerForExplain = $item['correct_option']->content ?? '';
                                        } else {
                                            $correctAnswerForExplain = $question->getCorrectAnswerForDisplay();
                                        }
                                    @endphp
                                    <div class="rounded-xl border {{ $isCorrect ? 'bg-emerald-50 border-emerald-200' : (!$isAnswered ? 'bg-gray-50 border-gray-200 opacity-60' : 'bg-red-50 border-red-200') }}"
                                         x-show="activePart === '{{ $item['part_number'] ?? '' }}'"
                                         x-data="{ showExplanation: false, explanation: '', tip: '', loading: false, error: '' }">
                                        <div class="flex items-start gap-3 p-4">
                                            <!-- Question Number -->
                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-sm font-bold {{ $isCorrect ? 'bg-emerald-100 text-emerald-700' : (!$isAnswered ? 'bg-gray-200 text-gray-500' : 'bg-red-100 text-red-700') }}">
                                                {{ $item['number'] }}
                                            </div>

                                            <!-- Question Content -->
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-gray-700 mb-2 line-clamp-2">{!! Str::limit($item['content'], 120) !!}</p>
                                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                                    <span class="text-gray-500">Your answer:</span>
                                                    <span class="{{ $isCorrect ? 'text-emerald-700 font-medium' : (!$isAnswered ? 'text-amber-600' : 'text-red-700') }}">
                                                        {{ Str::limit($displayAnswer, 40) }}
                                                    </span>
                                                    @if($isPremium && $isAnswered && !$isCorrect)
                                                        <span class="text-gray-400">|</span>
                                                        <span class="text-gray-500">Correct:</span>
                                                        <span class="text-emerald-600 font-medium">
                                                            {{ Str::limit($correctAnswerForExplain, 30) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($isPremium && $isAnswered && !$isCorrect)
                                                    <div class="mt-3">
                                                        <button @click="if(!explanation && !loading) {
                                                                    loading = true;
                                                                    error = '';
                                                                    fetch('{{ route('ai.explain.answer') }}', {
                                                                        method: 'POST',
                                                                        headers: {
                                                                            'Content-Type': 'application/json',
                                                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                                                        },
                                                                        body: JSON.stringify({
                                                                            question_id: {{ $question->id }},
                                                                            student_answer: '{{ addslashes($displayAnswer) }}',
                                                                            correct_answer: '{{ addslashes($correctAnswerForExplain) }}',
                                                                            question_content: '{{ addslashes(Str::limit($item['content'], 200)) }}',
                                                                            question_type: '{{ $question->question_type }}'
                                                                        })
                                                                    })
                                                                    .then(r => r.json())
                                                                    .then(data => {
                                                                        loading = false;
                                                                        if(data.success) {
                                                                            explanation = data.explanation;
                                                                            tip = data.tip || '';
                                                                            showExplanation = true;
                                                                        } else {
                                                                            error = data.error || 'Failed to load explanation';
                                                                        }
                                                                    })
                                                                    .catch(e => {
                                                                        loading = false;
                                                                        error = 'Network error. Please try again.';
                                                                    });
                                                                } else if(explanation) {
                                                                    showExplanation = !showExplanation;
                                                                }"
                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700 text-white text-xs font-medium rounded-lg shadow-sm hover:shadow transition-all cursor-pointer">
                                                            <i class="fas" :class="loading ? 'fa-spinner fa-spin' : (showExplanation ? 'fa-chevron-up' : 'fa-robot')"></i>
                                                            <span x-text="loading ? 'Loading...' : (showExplanation ? 'Hide' : 'Review Explanation')"></span>
                                                        </button>
                                                    </div>
                                                    <div x-show="error" x-cloak class="mt-2 px-3 py-2 bg-amber-50 border border-amber-200 rounded-lg">
                                                        <p class="text-xs text-amber-700 flex items-center gap-1">
                                                            <i class="fas fa-info-circle"></i>
                                                            <span x-text="error"></span>
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Status Icon -->
                                            <div class="flex-shrink-0">
                                                @if(!$isAnswered)
                                                    <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                                                        <i class="fas fa-minus text-amber-500 text-xs"></i>
                                                    </div>
                                                @elseif($isCorrect)
                                                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                                                        <i class="fas fa-check text-emerald-600 text-xs"></i>
                                                    </div>
                                                @else
                                                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                                        <i class="fas fa-times text-red-600 text-xs"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Explanation Panel (only for wrong answers) -->
                                        @if($isPremium && $isAnswered && !$isCorrect)
                                            <div x-show="showExplanation" x-cloak x-collapse>
                                                <div class="px-4 pb-4">
                                                    <div class="p-4 bg-white/80 rounded-xl border border-violet-200">
                                                        <div class="flex items-start gap-3">
                                                            <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center flex-shrink-0">
                                                                <i class="fas fa-robot text-violet-600 text-sm"></i>
                                                            </div>
                                                            <div class="flex-1">
                                                                <p class="text-xs font-semibold text-violet-600 mb-1">AI Explanation</p>
                                                                <p class="text-sm text-gray-700" x-text="explanation"></p>
                                                                <p x-show="tip" x-cloak class="mt-2 text-xs text-amber-700 bg-amber-50 px-3 py-2 rounded-lg">
                                                                    <i class="fas fa-lightbulb mr-1"></i><span x-text="tip"></span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                @endif

                {{-- Writing/Speaking Submission --}}
                @if(in_array($attempt->testSet->section->name, ['writing', 'speaking']))
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                        <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-file-alt text-[#C8102E]"></i>
                                Your Submission
                            </h3>
                        </div>
                        <div class="p-5 space-y-4">
                            @if($attempt->testSet->section->name === 'writing')
                                @foreach($attempt->answers->sortBy('question.order_number') as $answer)
                                    <div class="p-5 bg-gray-50 rounded-xl border border-gray-200">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="font-semibold text-gray-900">
                                                <i class="fas fa-tasks text-[#C8102E] mr-2"></i>Task {{ $answer->question->order_number }}
                                            </h4>
                                            @if(!empty($answer->answer))
                                                <span class="text-xs bg-white px-3 py-1 rounded-full text-gray-500 border border-gray-200">
                                                    <i class="fas fa-file-word mr-1"></i>{{ str_word_count($answer->answer) }} words
                                                </span>
                                            @endif
                                        </div>
                                        @if(!empty($answer->answer))
                                            <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap bg-white p-4 rounded-lg border border-gray-100">{{ $answer->answer }}</div>
                                        @else
                                            <p class="text-gray-400 italic">No answer provided for this task.</p>
                                        @endif
                                    </div>
                                @endforeach
                            @elseif($attempt->testSet->section->name === 'speaking')
                                @foreach($attempt->answers->sortBy('question.order_number') as $answer)
                                    <div class="p-5 bg-gray-50 rounded-xl border border-gray-200">
                                        <h4 class="font-semibold text-gray-900 mb-3">
                                            <i class="fas fa-microphone text-[#C8102E] mr-2"></i>Part {{ $answer->question->order_number }}
                                        </h4>
                                        @if($answer->speakingRecording)
                                            @php $audioUrl = route('audio.stream', $answer->speakingRecording->id); @endphp
                                            <audio controls class="w-full rounded-lg" preload="metadata">
                                                <source src="{{ $audioUrl }}" type="{{ $answer->speakingRecording->mime_type ?? 'audio/webm' }}">
                                                Your browser does not support the audio element.
                                            </audio>
                                        @else
                                            <p class="text-gray-400 italic">No recording available.</p>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column (1/3) -->
            <div class="space-y-6">
                <!-- Actions Card -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h3 class="font-bold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-3">
                        @if($canRetake && $isLatestAttempt)
                            <form action="{{ route('student.results.retake', $attempt) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-[#C8102E] to-[#A00E27] text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                    <i class="fas fa-redo"></i>Retake Test
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('student.' . $attempt->testSet->section->name . '.index') }}"
                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-all">
                            <i class="fas fa-list"></i>All {{ ucfirst($attempt->testSet->section->name) }} Tests
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

    {{-- AI Evaluation Modal --}}
    <div id="aiEvalModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl max-w-md w-full p-8 shadow-2xl border border-gray-100">
            <div class="text-center">
                <!-- Animated Brain/AI Icon -->
                <div class="relative w-24 h-24 mx-auto mb-6">
                    <div class="absolute inset-0 bg-gradient-to-br from-[#C8102E] to-[#A00E27] rounded-full animate-ai-pulse"></div>
                    <div class="absolute inset-2 bg-white rounded-full flex items-center justify-center">
                        <i class="fas fa-brain text-[#C8102E] text-3xl animate-ai-think"></i>
                    </div>
                    <!-- Orbiting dots -->
                    <div class="absolute inset-0 animate-spin-slow">
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-3 h-3 bg-amber-400 rounded-full shadow-lg"></div>
                    </div>
                    <div class="absolute inset-0 animate-spin-slower">
                        <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-2 h-2 bg-emerald-400 rounded-full shadow-lg"></div>
                    </div>
                </div>

                <h3 class="text-2xl font-bold text-gray-900 mb-3">AI Analysis in Progress</h3>

                <!-- Animated Status Messages -->
                <div class="h-6 mb-4">
                    <p class="text-gray-600 font-medium animate-fade-cycle" id="eval-status">Reading your response...</p>
                </div>

                <!-- Progress Bar -->
                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden mb-4">
                    <div class="h-full bg-gradient-to-r from-[#C8102E] via-amber-500 to-[#C8102E] rounded-full animate-progress-flow"></div>
                </div>

                <!-- Motivational Tips -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-4">
                    <p class="text-sm text-amber-700 flex items-center justify-center gap-2">
                        <i class="fas fa-lightbulb text-amber-500"></i>
                        <span id="eval-tip">Great job completing your test!</span>
                    </p>
                </div>

                <p class="text-xs text-gray-400">Usually takes 15-30 seconds</p>
            </div>
        </div>
    </div>

    {{-- AI Evaluation Error Modal --}}
    <div id="aiErrorModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-gray-100 transform transition-all">
            <!-- Header with gradient -->
            <div class="bg-gradient-to-r from-rose-500 to-red-600 px-6 py-5">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Evaluation Failed</h3>
                        <p class="text-rose-100 text-sm">We couldn't process your recording</p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 mb-5">
                    <p id="aiErrorMessage" class="text-rose-700 text-sm leading-relaxed"></p>
                </div>

                <!-- Tips section -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5">
                    <h4 class="font-semibold text-amber-800 text-sm mb-2 flex items-center gap-2">
                        <i class="fas fa-lightbulb text-amber-500"></i>
                        Tips for a successful recording:
                    </h4>
                    <ul class="text-xs text-amber-700 space-y-1.5">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-amber-500 mt-0.5"></i>
                            Speak clearly for at least 15-30 seconds
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-amber-500 mt-0.5"></i>
                            Make sure your microphone is working properly
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-amber-500 mt-0.5"></i>
                            Give complete answers with 2-3 sentences minimum
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-amber-500 mt-0.5"></i>
                            Avoid background noise during recording
                        </li>
                    </ul>
                </div>

                <!-- Action buttons -->
                <div class="flex gap-3">
                    <button onclick="closeErrorModal()" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-all">
                        <i class="fas fa-times mr-2"></i>Close
                    </button>
                    <a href="{{ route('student.speaking.index') }}" class="flex-1 px-4 py-3 bg-gradient-to-r from-[#C8102E] to-[#A00E27] text-white rounded-xl font-semibold hover:shadow-lg text-center transition-all">
                        <i class="fas fa-redo mr-2"></i>Try Again
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 5px rgba(245, 158, 11, 0.5), 0 0 10px rgba(245, 158, 11, 0.3);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 15px rgba(245, 158, 11, 0.8), 0 0 25px rgba(245, 158, 11, 0.5);
                transform: scale(1.02);
            }
        }
        .animate-pulse-glow {
            animation: pulse-glow 1.5s ease-in-out infinite;
        }

        /* AI Modal Animations */
        @keyframes ai-pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        .animate-ai-pulse {
            animation: ai-pulse 2s ease-in-out infinite;
        }

        @keyframes ai-think {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.1) rotate(-5deg); }
            75% { transform: scale(1.1) rotate(5deg); }
        }
        .animate-ai-think {
            animation: ai-think 1.5s ease-in-out infinite;
        }

        .animate-spin-slow {
            animation: spin 3s linear infinite;
        }
        .animate-spin-slower {
            animation: spin 4s linear infinite reverse;
        }

        @keyframes progress-flow {
            0% { transform: translateX(-100%); width: 100%; }
            50% { transform: translateX(0%); width: 100%; }
            100% { transform: translateX(100%); width: 100%; }
        }
        .animate-progress-flow {
            animation: progress-flow 2s ease-in-out infinite;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page');
        if (page && page !== '1') {
            const analysisSection = document.getElementById('question-analysis');
            if (analysisSection) {
                const offsetTop = analysisSection.offsetTop - 100;
                window.scrollTo({ top: offsetTop, behavior: 'smooth' });
            }
        }
    });

    // Convert decimal band score to IELTS range format (e.g., 6.1 -> "6.0-6.5")
    function formatBandRange(score) {
        if (score === null || score === undefined || score === '-') return '-';
        score = parseFloat(score);
        if (isNaN(score)) return '-';

        const lowerBand = Math.floor(score * 2) / 2;
        const upperBand = Math.ceil(score * 2) / 2;

        // If exactly on a boundary, show single value
        if (lowerBand === upperBand) {
            return lowerBand.toFixed(1);
        }

        // Otherwise show range
        return lowerBand.toFixed(1) + '-' + upperBand.toFixed(1);
    }

    function startAIEvaluation(attemptId, type) {
        document.getElementById('aiEvalModal').classList.remove('hidden');
        const button = document.getElementById('ai-eval-btn');
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';

        const statusEl = document.getElementById('eval-status');
        const tipEl = document.getElementById('eval-tip');

        // Use progressive evaluation for speaking to prevent timeout
        if (type === 'speaking') {
            startProgressiveEvaluation(attemptId, statusEl, tipEl, button);
            return;
        }

        // Original flow for writing
        startSimpleEvaluation(attemptId, type, statusEl, tipEl, button);
    }

    // Progressive evaluation - one recording at a time
    async function startProgressiveEvaluation(attemptId, statusEl, tipEl, button) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        try {
            // Step 1: Get list of recordings
            statusEl.textContent = 'Checking recordings...';
            tipEl.textContent = 'Preparing your evaluation';

            const statusRes = await fetch('/ai/evaluate/speaking/status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ attempt_id: attemptId })
            });
            const statusData = await statusRes.json();

            if (statusData.status === 'completed') {
                statusEl.innerHTML = '<i class="fas fa-check-circle text-emerald-500 mr-2"></i>Already evaluated!';
                tipEl.textContent = 'Redirecting to your results...';
                setTimeout(() => { window.location.href = statusData.redirect_url; }, 1000);
                return;
            }

            const recordings = statusData.recordings || [];
            const pendingRecordings = recordings.filter(r => !r.evaluated);

            if (pendingRecordings.length === 0 && recordings.length > 0) {
                // All evaluated, just finalize
                await finalizeEvaluation(attemptId, csrfToken, statusEl, tipEl);
                return;
            }

            // Step 2: Evaluate each recording one by one
            let completed = recordings.filter(r => r.evaluated).length;
            const total = recordings.length;
            const failedParts = [];

            for (const recording of pendingRecordings) {
                statusEl.innerHTML = `<i class="fas fa-microphone text-amber-500 mr-2"></i>Evaluating Part ${recording.part}...`;
                tipEl.textContent = `Progress: ${completed + 1} of ${total} recordings`;

                try {
                    const evalRes = await fetch('/ai/evaluate/speaking/single', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ answer_id: recording.answer_id })
                    });
                    const evalData = await evalRes.json();

                    if (evalData.success) {
                        completed++;
                        statusEl.innerHTML = `<i class="fas fa-check text-emerald-500 mr-2"></i>Part ${recording.part}: Band ${formatBandRange(evalData.band_score)}`;
                        await new Promise(r => setTimeout(r, 500)); // Brief pause to show result
                    } else {
                        failedParts.push({ part: recording.part, error: evalData.error });
                    }
                } catch (e) {
                    failedParts.push({ part: recording.part, error: e.message });
                }
            }

            // Step 3: Finalize
            if (completed > 0) {
                await finalizeEvaluation(attemptId, csrfToken, statusEl, tipEl, failedParts);
            } else {
                throw new Error('Could not evaluate any recordings. ' + (failedParts[0]?.error || 'Please try again.'));
            }

        } catch (error) {
            document.getElementById('aiEvalModal').classList.add('hidden');
            showErrorModal(error.message || 'An error occurred. Please try again.');
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-magic mr-2"></i> Get Instant Evaluation';
        }
    }

    async function finalizeEvaluation(attemptId, csrfToken, statusEl, tipEl, failedParts = []) {
        statusEl.innerHTML = '<i class="fas fa-calculator text-blue-500 mr-2"></i>Calculating final score...';
        tipEl.textContent = 'Almost done!';

        const finalRes = await fetch('/ai/evaluate/speaking/finalize', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ attempt_id: attemptId })
        });
        const finalData = await finalRes.json();

        if (finalData.success) {
            let message = 'Evaluation completed!';
            if (failedParts.length > 0) {
                message = `Completed ${finalData.stats.evaluated}/${finalData.stats.total} parts`;
            }
            statusEl.innerHTML = `<i class="fas fa-check-circle text-emerald-500 mr-2"></i>${message}`;
            tipEl.textContent = 'Redirecting to your results...';
            setTimeout(() => { window.location.href = finalData.redirect_url; }, 1500);
        } else {
            throw new Error(finalData.error || 'Failed to finalize evaluation');
        }
    }

    // Simple evaluation for writing (original flow)
    function startSimpleEvaluation(attemptId, type, statusEl, tipEl, button) {
        const statusMessages = [
            'Reading your response...',
            'Analyzing vocabulary usage...',
            'Checking grammar patterns...',
            'Evaluating coherence...',
            'Assessing task achievement...',
            'Calculating band score...',
            'Preparing detailed feedback...'
        ];

        const tips = [
            'Great job completing your test!',
            'Every attempt makes you stronger!',
            'Practice makes perfect!',
            'You\'re one step closer to your goal!',
            'Keep up the great work!'
        ];

        let msgIndex = 0;
        let tipIndex = 0;

        const messageInterval = setInterval(() => {
            msgIndex = (msgIndex + 1) % statusMessages.length;
            statusEl.style.opacity = '0';
            setTimeout(() => {
                statusEl.textContent = statusMessages[msgIndex];
                statusEl.style.opacity = '1';
            }, 200);
        }, 2500);

        const tipInterval = setInterval(() => {
            tipIndex = (tipIndex + 1) % tips.length;
            tipEl.textContent = tips[tipIndex];
        }, 4000);

        fetch(`/ai/evaluate/${type}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ attempt_id: attemptId })
        })
        .then(async response => {
            let data = {};
            try {
                data = await response.json();
            } catch (e) {}
            if (!response.ok) {
                throw new Error(data.error || `Server error (${response.status}). Please try again later.`);
            }
            return data;
        })
        .then(data => {
            clearInterval(messageInterval);
            clearInterval(tipInterval);
            if (data.success) {
                statusEl.innerHTML = '<i class="fas fa-check-circle text-emerald-500 mr-2"></i>Evaluation completed!';
                tipEl.textContent = 'Redirecting to your results...';
                setTimeout(() => { window.location.href = data.redirect_url || window.location.href; }, 1500);
            } else {
                throw new Error(data.error || 'Failed to evaluate');
            }
        })
        .catch(error => {
            clearInterval(messageInterval);
            clearInterval(tipInterval);
            document.getElementById('aiEvalModal').classList.add('hidden');
            showErrorModal(error.message || 'An error occurred. Please try again.');
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-magic mr-2"></i> Get Instant Evaluation';
        });
    }

    function showErrorModal(message) {
        document.getElementById('aiErrorMessage').textContent = message;
        document.getElementById('aiErrorModal').classList.remove('hidden');
    }

    function closeErrorModal() {
        document.getElementById('aiErrorModal').classList.add('hidden');
    }

    // Close modal on backdrop click
    document.getElementById('aiErrorModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeErrorModal();
        }
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !document.getElementById('aiErrorModal').classList.contains('hidden')) {
            closeErrorModal();
        }
    });
    </script>
    @endpush
</x-dashboard-layout>
