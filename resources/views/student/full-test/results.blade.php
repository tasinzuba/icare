<x-dashboard-layout>
    <x-slot:title>Full Test Results</x-slot>

    @php
        $availableSections = $fullTestAttempt->fullTest->getAvailableSections();
        $sectionStyles = [
            'listening' => ['icon' => 'fa-headphones', 'color' => 'blue', 'gradient' => 'from-blue-500 to-blue-600'],
            'reading' => ['icon' => 'fa-book-open', 'color' => 'emerald', 'gradient' => 'from-emerald-500 to-emerald-600'],
            'writing' => ['icon' => 'fa-pen-fancy', 'color' => 'violet', 'gradient' => 'from-violet-500 to-violet-600'],
            'speaking' => ['icon' => 'fa-microphone', 'color' => 'orange', 'gradient' => 'from-orange-500 to-orange-600']
        ];

        $startTime = $fullTestAttempt->start_time;
        $endTime = $fullTestAttempt->end_time ?? $fullTestAttempt->updated_at;
        $totalSeconds = $startTime ? $startTime->diffInSeconds($endTime) : 0;
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Hero Result Card -->
        <div class="relative bg-white rounded-3xl border border-gray-200 overflow-hidden mb-6 shadow-xl">
            <!-- Decorative Background -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-gradient-to-br from-[#C8102E] to-[#A00E27] opacity-5 rounded-full"></div>
                <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-gradient-to-br from-blue-500 to-blue-600 opacity-5 rounded-full"></div>
            </div>

            <div class="relative p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <!-- Test Info -->
                    <div class="flex items-start gap-4">
                        <div class="relative">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#C8102E] to-[#A00E27] flex items-center justify-center shadow-lg">
                                <i class="fas fa-file-alt text-white text-2xl"></i>
                            </div>
                            @if($fullTestAttempt->fullTest->is_premium)
                                <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-amber-400 to-yellow-500 rounded-full flex items-center justify-center shadow-md border-2 border-white">
                                    <i class="fas fa-crown text-white text-[10px]"></i>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs text-[#C8102E] font-semibold uppercase tracking-wider mb-1">Full Test Results</p>
                            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">{{ $fullTestAttempt->fullTest->title }}</h1>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm mt-3">
                                <span class="inline-flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                    <span class="font-medium">{{ $fullTestAttempt->end_time ? $fullTestAttempt->end_time->format('M d, Y \a\t g:i A') : 'In Progress' }}</span>
                                </span>
                                <span class="text-gray-300">•</span>
                                <span class="inline-flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-stopwatch text-gray-400"></i>
                                    <span class="font-medium">{{ $hours }}h {{ $minutes }}m</span>
                                </span>
                                <span class="text-gray-300">•</span>
                                <span class="inline-flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-tasks text-gray-400"></i>
                                    <span class="font-medium">{{ $fullTestAttempt->sectionAttempts->count() }}/{{ count($availableSections) }} Sections</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Band Score Display -->
                    @php
                        // Pre-calculate AI scores for overall band in hero section
                        $heroAiScores = [];
                        foreach ($fullTestAttempt->sectionAttempts as $sa) {
                            if (in_array($sa->section_type, ['writing', 'speaking']) && $sa->studentAttempt->ai_band_score) {
                                $heroAiScores[$sa->section_type] = $sa->studentAttempt->ai_band_score;
                            }
                        }

                        // Calculate effective overall band score (including AI)
                        $heroEffectiveScores = [];
                        $hasAnyAiScore = false;
                        foreach ($availableSections as $sectionKey) {
                            $scoreField = $sectionKey . '_score';
                            $humanScore = $fullTestAttempt->$scoreField;

                            if ($humanScore !== null && $humanScore !== '') {
                                $heroEffectiveScores[$sectionKey] = $humanScore;
                            } elseif (isset($heroAiScores[$sectionKey])) {
                                $heroEffectiveScores[$sectionKey] = $heroAiScores[$sectionKey];
                                $hasAnyAiScore = true;
                            }
                        }

                        $displayOverallBand = $fullTestAttempt->overall_band_score;
                        // If no official overall band but we have effective scores, calculate it
                        if (($displayOverallBand === null || $displayOverallBand === '') && count($heroEffectiveScores) > 0) {
                            $displayOverallBand = array_sum($heroEffectiveScores) / count($heroEffectiveScores);
                        }
                    @endphp
                    <div class="flex items-center gap-4">
                        @if($displayOverallBand !== null && $displayOverallBand !== '')
                            <div class="relative">
                                <div class="w-32 h-32 lg:w-36 lg:h-36 rounded-full bg-gradient-to-br from-[#C8102E] to-[#A00E27] p-1 shadow-xl">
                                    <div class="w-full h-full bg-white rounded-full flex flex-col items-center justify-center px-2">
                                        <p class="text-[10px] text-gray-500 font-medium">Overall Band</p>
                                        <p class="text-2xl lg:text-3xl font-black text-[#C8102E] whitespace-nowrap">{{ bandScoreRange($displayOverallBand) }}</p>
                                        <p class="text-[10px] text-gray-400">out of 9.0</p>
                                        @if($hasAnyAiScore && ($fullTestAttempt->overall_band_score === null || $fullTestAttempt->overall_band_score === ''))
                                            <span class="inline-flex items-center px-2 py-0.5 bg-blue-100 text-blue-700 text-[9px] font-semibold rounded-full mt-1">
                                                <i class="fas fa-robot mr-1 text-[7px]"></i>Includes AI
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
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

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column (2/3) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Section Scores -->
                @php
                    // Collect AI scores for writing/speaking sections
                    $aiScores = [];
                    foreach ($fullTestAttempt->sectionAttempts as $sa) {
                        if (in_array($sa->section_type, ['writing', 'speaking']) && $sa->studentAttempt->ai_band_score) {
                            $aiScores[$sa->section_type] = $sa->studentAttempt->ai_band_score;
                        }
                    }

                    // Calculate effective scores (human score OR AI score)
                    $effectiveScores = [];
                    $scoreTypes = []; // Track whether it's AI or human score
                    foreach ($availableSections as $sectionKey) {
                        $scoreField = $sectionKey . '_score';
                        $humanScore = $fullTestAttempt->$scoreField;

                        if ($humanScore !== null && $humanScore !== '') {
                            $effectiveScores[$sectionKey] = $humanScore;
                            $scoreTypes[$sectionKey] = 'human';
                        } elseif (isset($aiScores[$sectionKey])) {
                            $effectiveScores[$sectionKey] = $aiScores[$sectionKey];
                            $scoreTypes[$sectionKey] = 'ai';
                        } else {
                            $effectiveScores[$sectionKey] = null;
                            $scoreTypes[$sectionKey] = null;
                        }
                    }

                    // Calculate overall band including AI scores
                    $allEffectiveScores = array_filter($effectiveScores, function($s) { return $s !== null; });
                    $calculatedOverallBand = count($allEffectiveScores) > 0 ? array_sum($allEffectiveScores) / count($allEffectiveScores) : null;
                @endphp

                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-chart-pie text-[#C8102E]"></i>
                            Section Scores
                        </h3>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($availableSections as $sectionKey)
                                @php
                                    $sectionData = $sectionStyles[$sectionKey] ?? ['icon' => 'fa-question', 'color' => 'gray', 'gradient' => 'from-gray-500 to-gray-600'];
                                    $score = $effectiveScores[$sectionKey] ?? null;
                                    $scoreType = $scoreTypes[$sectionKey] ?? null;
                                @endphp
                                <div class="text-center p-4 bg-{{ $sectionData['color'] }}-50 rounded-xl border border-{{ $sectionData['color'] }}-100">
                                    <div class="w-10 h-10 bg-gradient-to-br {{ $sectionData['gradient'] }} rounded-lg flex items-center justify-center mx-auto mb-2 shadow-sm">
                                        <i class="fas {{ $sectionData['icon'] }} text-white text-sm"></i>
                                    </div>
                                    <p class="text-xs text-{{ $sectionData['color'] }}-600 font-medium mb-1">{{ ucfirst($sectionKey) }}</p>
                                    @if($score !== null && $score !== '')
                                        <p class="text-2xl font-black text-gray-900">{{ number_format($score, 1) }}</p>
                                        @if($scoreType === 'ai')
                                            <span class="inline-flex items-center px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-semibold rounded-full mt-1">
                                                <i class="fas fa-robot mr-1 text-[8px]"></i>AI
                                            </span>
                                        @endif
                                    @else
                                        <p class="text-sm font-semibold text-amber-500">Pending</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Band Progress -->
                        @php
                            // Use calculated overall band (which includes AI scores)
                            $progressOverallBand = $calculatedOverallBand ?? $fullTestAttempt->overall_band_score;
                        @endphp
                        @if($progressOverallBand)
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            <div class="flex justify-between mb-2">
                                @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9] as $band)
                                    <span class="text-xs font-medium {{ $progressOverallBand >= $band ? 'text-[#C8102E]' : 'text-gray-300' }}">{{ $band }}</span>
                                @endforeach
                            </div>
                            <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-[#C8102E] to-[#A00E27] rounded-full transition-all duration-1000" style="width: {{ ($progressOverallBand / 9) * 100 }}%"></div>
                            </div>
                            @php
                                $scoreLabel = match(true) {
                                    $progressOverallBand >= 8.0 => 'Expert User',
                                    $progressOverallBand >= 7.0 => 'Good User',
                                    $progressOverallBand >= 6.0 => 'Competent User',
                                    $progressOverallBand >= 5.0 => 'Modest User',
                                    default => 'Limited User'
                                };
                            @endphp
                            <p class="text-center text-sm text-gray-500 mt-3">
                                <i class="fas fa-award text-[#C8102E] mr-1"></i>{{ $scoreLabel }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Performance Summary -->
                @php
                    // Use effective scores (including AI scores) for performance summary
                    $performanceScores = array_filter($effectiveScores, function($s) { return $s !== null && $s !== ''; });
                    if(!empty($performanceScores)) {
                        $strongestSection = array_search(max($performanceScores), $performanceScores);
                        $weakestSection = array_search(min($performanceScores), $performanceScores);
                    }
                @endphp

                @if(!empty($performanceScores) && $strongestSection !== $weakestSection)
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-lightbulb text-[#C8102E]"></i>
                            Performance Summary
                        </h3>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-arrow-up text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-emerald-600 font-medium">Strongest</p>
                                        <p class="font-bold text-gray-900 capitalize">{{ $strongestSection }}</p>
                                        <div class="flex items-center gap-1">
                                            <p class="text-lg font-black text-emerald-600">{{ number_format($performanceScores[$strongestSection], 1) }}</p>
                                            @if(($scoreTypes[$strongestSection] ?? null) === 'ai')
                                                <span class="text-[9px] text-blue-600"><i class="fas fa-robot"></i></span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-bullseye text-amber-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-amber-600 font-medium">Focus Area</p>
                                        <p class="font-bold text-gray-900 capitalize">{{ $weakestSection }}</p>
                                        <div class="flex items-center gap-1">
                                            <p class="text-lg font-black text-amber-600">{{ number_format($performanceScores[$weakestSection], 1) }}</p>
                                            @if(($scoreTypes[$weakestSection] ?? null) === 'ai')
                                                <span class="text-[9px] text-blue-600"><i class="fas fa-robot"></i></span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Section-by-Section Answer Analysis --}}
                @foreach($fullTestAttempt->sectionAttempts as $sectionAttempt)
                    @php
                        $studentAttempt = $sectionAttempt->studentAttempt;
                        $sectionType = $sectionAttempt->section_type;
                        $sectionData = $sectionStyles[$sectionType] ?? ['icon' => 'fa-question', 'color' => 'gray', 'gradient' => 'from-gray-500 to-gray-600'];
                    @endphp

                    {{-- Listening/Reading: Show Question Analysis --}}
                    @if(in_array($sectionType, ['listening', 'reading']))
                        @php
                            // Get all questions
                            $allQuestions = $studentAttempt->testSet->questions()
                                ->where('question_type', '!=', 'passage')
                                ->orderBy('part_number')
                                ->orderBy('order_number')
                                ->get();

                            // Build display questions array - EXPAND sub-questions
                            $displayQuestions = [];
                            $currentNumber = 1;
                            $masterQuestionIds = [];

                            foreach ($allQuestions as $question) {
                                // Master matching headings - expand each mapping
                                if (method_exists($question, 'isMasterMatchingHeading') && $question->isMasterMatchingHeading()) {
                                    if (!in_array($question->id, $masterQuestionIds)) {
                                        $masterQuestionIds[] = $question->id;
                                        $mappings = $question->section_specific_data['mappings'] ?? [];
                                        $headings = $question->section_specific_data['headings'] ?? [];
                                        $masterAnswers = $studentAttempt->answers->filter(function($answer) use ($question) {
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
                                                'correct_letter' => $correctLetter,
                                                'correct_heading_text' => $correctHeadingText,
                                                'all_headings' => $headings
                                            ];
                                            $currentNumber++;
                                        }
                                    }
                                }
                                // Sentence completion - expand each sentence
                                elseif ($question->question_type === 'sentence_completion' && isset($question->section_specific_data['sentence_completion'])) {
                                    $scData = $question->section_specific_data['sentence_completion'];
                                    $sentences = $scData['sentences'] ?? [];
                                    foreach ($sentences as $sentenceIndex => $sentence) {
                                        $questionNumber = $sentence['questionNumber'] ?? ($sentenceIndex + 1);
                                        $specificAnswer = $studentAttempt->answers->first(function($ans) use ($question, $questionNumber) {
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
                                            'correct_answer' => $sentence['correctAnswer'] ?? $sentence['correct_answer'] ?? null
                                        ];
                                        $currentNumber++;
                                    }
                                }
                                // Drag & drop - expand each zone
                                elseif ($question->question_type === 'drag_drop') {
                                    $dragDropData = $question->section_specific_data ?? [];
                                    $dropZones = $dragDropData['drop_zones'] ?? [];
                                    $answer = $studentAttempt->answers->where('question_id', $question->id)->first();
                                    foreach ($dropZones as $zoneIndex => $zone) {
                                        $displayQuestions[] = [
                                            'number' => $currentNumber,
                                            'question' => $question,
                                            'content' => $zone['text'] ?? "Drop Zone " . ($zoneIndex + 1),
                                            'answer' => $answer,
                                            'is_drag_drop' => true,
                                            'zone_index' => $zoneIndex,
                                            'correct_answer' => $zone['correct_answer'] ?? $zone['answer'] ?? null
                                        ];
                                        $currentNumber++;
                                    }
                                }
                                // Fill-in-blanks - expand each blank
                                elseif ($question->question_type === 'fill_blanks') {
                                    $answer = $studentAttempt->answers->where('question_id', $question->id)->first();
                                    preg_match_all('/\[____(\d+)____\]/', $question->content, $matches, PREG_SET_ORDER);
                                    $blankCount = count($matches);
                                    if ($blankCount > 0) {
                                        foreach ($matches as $index => $match) {
                                            $blankNum = $match[1] ?? ($index + 1);
                                            $cleanContent = preg_replace('/\[____\d+____\]/', '___', $question->content, 1);
                                            $cleanContent = strip_tags($cleanContent);
                                            $displayQuestions[] = [
                                                'number' => $currentNumber,
                                                'question' => $question,
                                                'content' => $cleanContent,
                                                'answer' => $answer,
                                                'is_fill_blank' => true,
                                                'blank_number' => $blankNum
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
                                            'blank_number' => 1
                                        ];
                                        $currentNumber++;
                                    }
                                }
                                // Dropdown - expand each dropdown
                                elseif ($question->question_type === 'dropdown_selection') {
                                    $answer = $studentAttempt->answers->where('question_id', $question->id)->first();
                                    preg_match_all('/\[DROPDOWN_(\d+)\]/', $question->content, $matches, PREG_SET_ORDER);
                                    $dropdownCount = count($matches);
                                    if ($dropdownCount > 0) {
                                        foreach ($matches as $index => $match) {
                                            $dropdownNum = $match[1] ?? ($index + 1);
                                            $cleanContent = preg_replace('/\[DROPDOWN_\d+\]/', '___', $question->content, 1);
                                            $cleanContent = strip_tags($cleanContent);
                                            $displayQuestions[] = [
                                                'number' => $currentNumber,
                                                'question' => $question,
                                                'content' => $cleanContent,
                                                'answer' => $answer,
                                                'is_dropdown' => true,
                                                'dropdown_index' => $dropdownNum
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
                                            'dropdown_index' => 1
                                        ];
                                        $currentNumber++;
                                    }
                                }
                                // Multiple choice with multiple answers - expand each answer
                                elseif ($question->question_type === 'multiple_choice') {
                                    $correctCount = $question->options->where('is_correct', true)->count();
                                    $correctOptions = $question->options->where('is_correct', true)->values();
                                    $questionAnswers = $studentAttempt->answers->where('question_id', $question->id);
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
                                    $selectedOptionIds = array_unique($selectedOptionIds);

                                    // Re-index selectedOptionIds to ensure sequential 0-based keys
                                    $selectedOptionIds = array_values($selectedOptionIds);

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
                                                'correct_option' => $correctOptions[$i] ?? null
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
                                            'selected_option' => $selectedOption,
                                            'correct_option' => $correctOptions[0] ?? null
                                        ];
                                        $currentNumber++;
                                    }
                                }
                                // Regular questions
                                else {
                                    $answer = $studentAttempt->answers->where('question_id', $question->id)->first();
                                    $displayQuestions[] = [
                                        'number' => $currentNumber,
                                        'question' => $question,
                                        'content' => strip_tags($question->content),
                                        'answer' => $answer,
                                        'is_regular' => true
                                    ];
                                    $currentNumber++;
                                }
                            }

                            // FIX: Use database values instead of recalculating in template
                            // This prevents score mismatch between template and actual submission
                            $totalQuestions = $studentAttempt->total_questions ?? count($displayQuestions);
                            $answeredQuestions = $studentAttempt->answered_questions ?? 0;
                            $correctAnswers = $studentAttempt->correct_answers ?? 0;

                            // Fallback: If database values are not set (old records), calculate from display
                            if ($answeredQuestions == 0 && $correctAnswers == 0 && !empty($displayQuestions)) {
                                $answeredCount = 0;
                                $correctCount = 0;

                                foreach ($displayQuestions as $item) {
                                    $answer = $item['answer'];
                                    if (!empty($answer)) {
                                        if (isset($item['is_fill_blank']) && $item['is_fill_blank']) {
                                            $answerData = @json_decode($answer->answer, true);
                                            $blankNum = $item['blank_number'];
                                            $studentAnswer = null;
                                            if (is_array($answerData)) {
                                                $studentAnswer = $answerData["blank_{$blankNum}"] ?? $answerData[$blankNum] ?? null;
                                            } elseif (is_string($answer->answer)) {
                                                $studentAnswer = $answer->answer;
                                            }
                                            if ($studentAnswer !== null && trim((string)$studentAnswer) !== '') {
                                                $answeredCount++;
                                                if (method_exists($item['question'], 'checkBlankAnswer') && $item['question']->checkBlankAnswer($blankNum, $studentAnswer)) {
                                                    $correctCount++;
                                                }
                                            }
                                        } elseif (isset($item['is_drag_drop']) && $item['is_drag_drop']) {
                                            $answerData = @json_decode($answer->answer, true);
                                            if (is_array($answerData)) {
                                                $zoneIndex = $item['zone_index'];
                                                $studentAnswer = $answerData["zone_{$zoneIndex}"] ?? $answerData[$zoneIndex] ?? null;
                                                if ($studentAnswer !== null && trim((string)$studentAnswer) !== '') {
                                                    $answeredCount++;
                                                    if (strtolower(trim((string)$studentAnswer)) === strtolower(trim((string)($item['correct_answer'] ?? '')))) {
                                                        $correctCount++;
                                                    }
                                                }
                                            }
                                        } elseif (isset($item['is_dropdown']) && $item['is_dropdown']) {
                                            $answerData = @json_decode($answer->answer, true);
                                            if (is_array($answerData)) {
                                                $dropdownNum = $item['dropdown_index'];
                                                $studentAnswer = $answerData["dropdown_{$dropdownNum}"] ?? null;
                                                if ($studentAnswer !== null && trim((string)$studentAnswer) !== '') {
                                                    $answeredCount++;
                                                    $q = $item['question'];
                                                    if ($q->section_specific_data && isset($q->section_specific_data['dropdown_correct'][$dropdownNum])) {
                                                        $correctIndex = $q->section_specific_data['dropdown_correct'][$dropdownNum];
                                                        $dropdownOptions = $q->section_specific_data['dropdown_options'][$dropdownNum] ?? '';
                                                        if ($dropdownOptions) {
                                                            $options = array_map('trim', explode(',', $dropdownOptions));
                                                            $correctOption = $options[$correctIndex] ?? '';
                                                            if (strtolower(trim((string)$studentAnswer)) === strtolower(trim($correctOption))) {
                                                                $correctCount++;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } elseif (isset($item['is_multiple_choice']) && $item['is_multiple_choice']) {
                                            if ($item['selected_option']) {
                                                $answeredCount++;
                                                if ($item['selected_option']->is_correct) {
                                                    $correctCount++;
                                                }
                                            }
                                        } elseif (isset($item['is_master_sub']) && $item['is_master_sub']) {
                                            $decoded = @json_decode($answer->answer, true);
                                            $selectedLetter = $decoded['selected_letter'] ?? $decoded['answer'] ?? null;
                                            if ($selectedLetter) {
                                                $answeredCount++;
                                                if ($selectedLetter === $item['correct_letter']) {
                                                    $correctCount++;
                                                }
                                            }
                                        } elseif (isset($item['is_sentence_completion']) && $item['is_sentence_completion']) {
                                            $answerData = @json_decode($answer->answer, true);
                                            if (is_array($answerData) && isset($answerData['selected_answer'])) {
                                                $answeredCount++;
                                                if ($answerData['selected_answer'] === $item['correct_answer']) {
                                                    $correctCount++;
                                                }
                                            }
                                        } else {
                                            if (!empty($answer->answer) || !empty($answer->selected_option_id)) {
                                                $answeredCount++;
                                                if ($answer->is_correct ?? false) {
                                                    $correctCount++;
                                                }
                                            }
                                        }
                                    }
                                }

                                $answeredQuestions = $answeredCount;
                                $correctAnswers = $correctCount;
                            }
                        @endphp

                        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                            <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-{{ $sectionData['color'] }}-50 to-white">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                        <i class="fas {{ $sectionData['icon'] }} text-{{ $sectionData['color'] }}-600"></i>
                                        {{ ucfirst($sectionType) }} - Question Analysis
                                    </h3>
                                    <div class="flex items-center gap-4 text-sm">
                                        <span class="text-{{ $sectionData['color'] }}-600 font-semibold">{{ $totalQuestions }} Questions</span>
                                        @if($studentAttempt->band_score)
                                            <span class="px-3 py-1 bg-{{ $sectionData['color'] }}-100 text-{{ $sectionData['color'] }}-700 rounded-full font-bold">
                                                Band: {{ bandScoreRange($studentAttempt->band_score) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="p-5">
                                <!-- Score Cards -->
                                <div class="grid grid-cols-4 gap-3 mb-5">
                                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                                        <p class="text-2xl font-black text-gray-900">{{ $totalQuestions }}</p>
                                        <p class="text-xs text-gray-500">Total</p>
                                    </div>
                                    <div class="text-center p-3 bg-blue-50 rounded-xl">
                                        <p class="text-2xl font-black text-blue-600">{{ $answeredQuestions }}</p>
                                        <p class="text-xs text-blue-600">Attempted</p>
                                    </div>
                                    <div class="text-center p-3 bg-emerald-50 rounded-xl">
                                        <p class="text-2xl font-black text-emerald-600">{{ $correctAnswers }}</p>
                                        <p class="text-xs text-emerald-600">Correct</p>
                                    </div>
                                    <div class="text-center p-3 bg-red-50 rounded-xl">
                                        <p class="text-2xl font-black text-red-600">{{ $answeredQuestions - $correctAnswers }}</p>
                                        <p class="text-xs text-red-600">Wrong</p>
                                    </div>
                                </div>

                                <!-- Answers List -->
                                <div class="space-y-2 max-h-96 overflow-y-auto">
                                    @foreach($displayQuestions as $item)
                                        @php
                                            $question = $item['question'];
                                            $answer = $item['answer'];
                                            $isAnswered = false;
                                            $displayAnswer = 'Not attempted';
                                            $correctAnswerText = '';
                                            $isCorrect = false;

                                            // Process each question type - IMPROVED answer detection
                                            if (isset($item['is_drag_drop']) && $item['is_drag_drop'] && $answer) {
                                                $answerData = @json_decode($answer->answer, true);
                                                $zoneIndex = $item['zone_index'];
                                                $zoneKey = 'zone_' . $zoneIndex;

                                                // Try multiple key formats for drag_drop
                                                $studentAnswer = null;
                                                if (is_array($answerData)) {
                                                    $studentAnswer = $answerData[$zoneKey]
                                                        ?? $answerData["zone_{$zoneIndex}"]
                                                        ?? $answerData[$zoneIndex]
                                                        ?? null;
                                                }

                                                if ($studentAnswer !== null && trim((string)$studentAnswer) !== '') {
                                                    $displayAnswer = $studentAnswer;
                                                    $isAnswered = true;
                                                    $correctAns = $item['correct_answer'] ?? '';
                                                    $isCorrect = (strtolower(trim((string)$studentAnswer)) === strtolower(trim((string)$correctAns)));
                                                }
                                                $correctAnswerText = $item['correct_answer'] ?? '';
                                            } elseif (isset($item['is_fill_blank']) && $item['is_fill_blank'] && $answer) {
                                                $answerData = @json_decode($answer->answer, true);
                                                $blankNum = $item['blank_number'];

                                                // Try multiple key formats for fill_blank
                                                $studentAnswer = null;
                                                if (is_array($answerData)) {
                                                    $studentAnswer = $answerData["blank_{$blankNum}"]
                                                        ?? $answerData["blank{$blankNum}"]
                                                        ?? $answerData[$blankNum]
                                                        ?? $answerData[$blankNum - 1]
                                                        ?? null;
                                                } elseif (is_string($answer->answer) && !empty($answer->answer)) {
                                                    // Single blank - answer is direct string
                                                    $studentAnswer = $answer->answer;
                                                }

                                                if ($studentAnswer !== null && trim((string)$studentAnswer) !== '') {
                                                    $displayAnswer = $studentAnswer;
                                                    $isAnswered = true;
                                                    if (method_exists($question, 'checkBlankAnswer')) {
                                                        $isCorrect = $question->checkBlankAnswer($blankNum, $studentAnswer);
                                                    }
                                                }

                                                if (method_exists($question, 'getBlankAnswersArray')) {
                                                    $blankAnswers = $question->getBlankAnswersArray();
                                                    $correctAnswerText = $blankAnswers[$item['blank_number']] ?? '';
                                                }
                                            } elseif (isset($item['is_dropdown']) && $item['is_dropdown'] && $answer) {
                                                $answerData = @json_decode($answer->answer, true);
                                                $dropdownNum = $item['dropdown_index'];

                                                // Try multiple key formats for dropdown
                                                $studentAnswer = null;
                                                if (is_array($answerData)) {
                                                    $studentAnswer = $answerData["dropdown_{$dropdownNum}"]
                                                        ?? $answerData["dropdown{$dropdownNum}"]
                                                        ?? $answerData[$dropdownNum]
                                                        ?? null;
                                                }

                                                if ($studentAnswer !== null && trim((string)$studentAnswer) !== '') {
                                                    $displayAnswer = $studentAnswer;
                                                    $isAnswered = true;
                                                    if ($question->section_specific_data && isset($question->section_specific_data['dropdown_correct'][$dropdownNum])) {
                                                        $correctIndex = $question->section_specific_data['dropdown_correct'][$dropdownNum];
                                                        $dropdownOptions = $question->section_specific_data['dropdown_options'][$dropdownNum] ?? '';
                                                        if ($dropdownOptions) {
                                                            $options = array_map('trim', explode(',', $dropdownOptions));
                                                            $correctOption = $options[$correctIndex] ?? '';
                                                            $correctAnswerText = $correctOption;
                                                            $isCorrect = (strtolower(trim((string)$studentAnswer)) === strtolower(trim($correctOption)));
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
                                                    // Fallback: check answer record using choice_index
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
                                                }
                                                $correctAnswerText = $item['correct_option']->content ?? '';
                                            } elseif (isset($item['is_master_sub']) && $item['is_master_sub'] && $answer) {
                                                $decoded = @json_decode($answer->answer, true);
                                                $selectedLetter = $decoded['selected_letter'] ?? $decoded['answer'] ?? null;
                                                $selectedHeadingText = null;
                                                if ($selectedLetter && isset($item['all_headings'])) {
                                                    foreach ($item['all_headings'] as $heading) {
                                                        if ($heading['id'] === $selectedLetter) {
                                                            $selectedHeadingText = $heading['text'] ?? null;
                                                            break;
                                                        }
                                                    }
                                                }
                                                if ($selectedLetter) {
                                                    $displayAnswer = $selectedHeadingText ? $selectedHeadingText : "Option {$selectedLetter}";
                                                    $isAnswered = true;
                                                    $isCorrect = ($selectedLetter === $item['correct_letter']);
                                                }
                                                $correctAnswerText = $item['correct_heading_text'] ?? 'Option ' . ($item['correct_letter'] ?? '');
                                            } elseif (isset($item['is_sentence_completion']) && $item['is_sentence_completion'] && $answer) {
                                                $answerData = @json_decode($answer->answer, true);
                                                $studentAnswer = null;
                                                if (is_array($answerData)) {
                                                    $studentAnswer = $answerData['selected_answer'] ?? $answerData['answer'] ?? null;
                                                }
                                                if ($studentAnswer !== null) {
                                                    $displayAnswer = "Option {$studentAnswer}";
                                                    $isAnswered = true;
                                                    $isCorrect = ($studentAnswer === $item['correct_answer']);
                                                }
                                                $correctAnswerText = 'Option ' . ($item['correct_answer'] ?? '');
                                            } else {
                                                // Regular question - IMPROVED detection
                                                if ($answer) {
                                                    if ($answer->selectedOption) {
                                                        $displayAnswer = $answer->selectedOption->content;
                                                        $isCorrect = $answer->selectedOption->is_correct;
                                                        $isAnswered = true;
                                                    } elseif ($answer->selected_option_id) {
                                                        // selectedOption relation not loaded, try manual lookup
                                                        $opt = $question->options->firstWhere('id', $answer->selected_option_id);
                                                        if ($opt) {
                                                            $displayAnswer = $opt->content;
                                                            $isCorrect = $opt->is_correct;
                                                            $isAnswered = true;
                                                        }
                                                    } elseif ($answer->answer !== null && $answer->answer !== '') {
                                                        $answerData = @json_decode($answer->answer, true);
                                                        if (is_array($answerData) && !empty($answerData)) {
                                                            // Filter out empty values and format
                                                            $filtered = array_filter($answerData, function($v) {
                                                                return $v !== null && $v !== '';
                                                            });
                                                            if (!empty($filtered)) {
                                                                $displayAnswer = implode(', ', $filtered);
                                                                $isAnswered = true;
                                                            }
                                                        } else {
                                                            $displayAnswer = $answer->answer;
                                                            $isAnswered = true;
                                                        }
                                                        $isCorrect = $answer->is_correct ?? false;
                                                    }
                                                }
                                                if (method_exists($question, 'getCorrectAnswerForDisplay')) {
                                                    $correctAnswerText = $question->getCorrectAnswerForDisplay();
                                                }
                                            }
                                        @endphp

                                        <div class="flex items-center gap-3 p-3 rounded-xl {{ $isCorrect ? 'bg-emerald-50 border border-emerald-200' : ($isAnswered ? 'bg-red-50 border border-red-200' : 'bg-gray-50 border border-gray-200') }}">
                                            <!-- Question Number -->
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 text-sm font-bold {{ $isCorrect ? 'bg-emerald-100 text-emerald-700' : ($isAnswered ? 'bg-red-100 text-red-700' : 'bg-gray-200 text-gray-500') }}">
                                                {{ $item['number'] }}
                                            </div>

                                            <!-- Answer Info -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 text-xs">
                                                    <span class="text-gray-600">Your answer:</span>
                                                    <span class="font-medium {{ $isCorrect ? 'text-emerald-700' : ($isAnswered ? 'text-red-700' : 'text-amber-600') }}">
                                                        {{ Str::limit($displayAnswer, 50) }}
                                                    </span>
                                                    @if($isAnswered && !$isCorrect && $correctAnswerText)
                                                        <span class="text-gray-400">|</span>
                                                        <span class="text-gray-500">Correct:</span>
                                                        <span class="text-emerald-600 font-medium">{{ Str::limit($correctAnswerText, 40) }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Status Icon -->
                                            <div class="flex-shrink-0">
                                                @if(!$isAnswered)
                                                    <i class="fas fa-minus text-amber-500"></i>
                                                @elseif($isCorrect)
                                                    <i class="fas fa-check text-emerald-600"></i>
                                                @else
                                                    <i class="fas fa-times text-red-600"></i>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Writing/Speaking: Show Submission Preview --}}
                    @if(in_array($sectionType, ['writing', 'speaking']))
                        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                            <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-{{ $sectionData['color'] }}-50 to-white">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                        <i class="fas {{ $sectionData['icon'] }} text-{{ $sectionData['color'] }}-600"></i>
                                        {{ ucfirst($sectionType) }} - Submission
                                    </h3>
                                    @if($studentAttempt->band_score)
                                        <span class="px-3 py-1 bg-{{ $sectionData['color'] }}-100 text-{{ $sectionData['color'] }}-700 rounded-full font-bold text-sm">
                                            Band: {{ bandScoreRange($studentAttempt->band_score) }}
                                        </span>
                                    @elseif($studentAttempt->ai_band_score)
                                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-bold text-sm flex items-center gap-1">
                                            <i class="fas fa-robot text-[10px]"></i>
                                            Band: {{ bandScoreRange($studentAttempt->ai_band_score) }}
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full font-semibold text-xs">
                                            Pending Evaluation
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="p-5 space-y-4">
                                @if($sectionType === 'writing')
                                    @foreach($studentAttempt->answers->sortBy('question.order_number') as $answer)
                                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                                            <div class="flex items-center justify-between mb-2">
                                                <h4 class="font-semibold text-gray-900 text-sm">
                                                    <i class="fas fa-tasks text-{{ $sectionData['color'] }}-600 mr-2"></i>Task {{ $answer->question->order_number }}
                                                </h4>
                                                @if(!empty($answer->answer))
                                                    <span class="text-xs bg-white px-2 py-1 rounded-full text-gray-500">
                                                        {{ str_word_count($answer->answer) }} words
                                                    </span>
                                                @endif
                                            </div>
                                            @if(!empty($answer->answer))
                                                <div class="text-sm text-gray-700 bg-white p-3 rounded-lg border border-gray-100 max-h-64 overflow-y-auto whitespace-pre-wrap">
                                                    {{ $answer->answer }}
                                                </div>
                                            @else
                                                <p class="text-gray-400 italic text-xs">No answer provided</p>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-4 text-gray-500 text-sm">
                                        <i class="fas fa-microphone text-{{ $sectionData['color'] }}-400 text-2xl mb-2"></i>
                                        <p>{{ $studentAttempt->answers->count() }} audio recording(s) submitted</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach

                <!-- Evaluation Status -->
                @php
                    $hasWriting = in_array('writing', $availableSections);
                    $hasSpeaking = in_array('speaking', $availableSections);
                    $needsEvaluation = $hasWriting || $hasSpeaking;
                    $isOfflineStudent = auth()->user()->isOfflineStudent();
                    $user = auth()->user();

                    // For offline students, check evaluation type from enrollment
                    $canUseAI = $user->canUseAIEvaluation();
                    $canUseHuman = $user->canUseHumanEvaluation();

                    $writingRequested = false;
                    $speakingRequested = false;
                    $writingCompleted = false;
                    $speakingCompleted = false;
                    $writingAIEvaluated = false;
                    $speakingAIEvaluated = false;
                    $sectionsNotRequested = [];

                    // Check for AI evaluations
                    $writingAttempt = null;
                    $speakingAttempt = null;

                    if ($needsEvaluation) {
                        foreach ($fullTestAttempt->sectionAttempts as $sectionAttempt) {
                            if ($sectionAttempt->section_type === 'writing' && $hasWriting) {
                                $writingAttempt = $sectionAttempt->studentAttempt;
                                $request = $writingAttempt->humanEvaluationRequest;
                                if ($request) {
                                    $writingRequested = true;
                                    $writingCompleted = $request->status === 'completed';
                                } else {
                                    $sectionsNotRequested[] = 'Writing';
                                }
                                // Check if AI evaluated
                                $writingAIEvaluated = $writingAttempt->ai_evaluated_at !== null;
                            }

                            if ($sectionAttempt->section_type === 'speaking' && $hasSpeaking) {
                                $speakingAttempt = $sectionAttempt->studentAttempt;
                                $request = $speakingAttempt->humanEvaluationRequest;
                                if ($request) {
                                    $speakingRequested = true;
                                    $speakingCompleted = $request->status === 'completed';
                                } else {
                                    $sectionsNotRequested[] = 'Speaking';
                                }
                                // Check if AI evaluated
                                $speakingAIEvaluated = $speakingAttempt->ai_evaluated_at !== null;
                            }
                        }
                    }

                    $someRequested = $writingRequested || $speakingRequested;
                    $allCompleted = ($hasWriting ? $writingCompleted : true) && ($hasSpeaking ? $speakingCompleted : true);
                    $hasUnrequestedSections = !empty($sectionsNotRequested) && !$isOfflineStudent;

                    // For offline students - check what evaluations are pending
                    $needsAIEvaluation = $isOfflineStudent && $canUseAI && (($hasWriting && !$writingAIEvaluated) || ($hasSpeaking && !$speakingAIEvaluated));
                    $needsHumanEvaluation = $isOfflineStudent && $canUseHuman && !$someRequested;
                @endphp

                {{-- Show evaluation in progress ONLY if human evaluation is requested and pending --}}
                {{-- For offline students with AI-only evaluation, don't show this message --}}
                @if($needsEvaluation && $someRequested && !$allCompleted && !($isOfflineStudent && !$canUseHuman))
                <div class="bg-gradient-to-r from-amber-50 to-yellow-50 rounded-2xl p-5 border border-amber-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-amber-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-amber-800 mb-1">Human Evaluation In Progress</h4>
                            <p class="text-sm text-amber-700">
                                @if($writingRequested && !$writingCompleted && $speakingRequested && !$speakingCompleted)
                                    Your Writing and Speaking sections are being evaluated by a teacher.
                                @elseif($writingRequested && !$writingCompleted)
                                    Your Writing section is being evaluated by a teacher.
                                @elseif($speakingRequested && !$speakingCompleted)
                                    Your Speaking section is being evaluated by a teacher.
                                @endif
                            </p>
                        </div>
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
                        @if($needsEvaluation)
                            {{-- Offline Student Evaluation Options --}}
                            @if($isOfflineStudent)
                                {{-- Show evaluation type badge --}}
                                @php
                                    $enrollment = $user->offlineEnrollment;
                                    $evalType = $enrollment->evaluation_type ?? 'ai';
                                @endphp
                                <div class="p-3 bg-gray-50 rounded-xl mb-2">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Evaluation Type:</span>
                                        <span class="font-semibold {{ $evalType === 'ai' ? 'text-blue-600' : ($evalType === 'human' ? 'text-purple-600' : 'text-emerald-600') }}">
                                            @if($evalType === 'ai')
                                                <i class="fas fa-robot mr-1"></i>AI Only
                                            @elseif($evalType === 'human')
                                                <i class="fas fa-user-tie mr-1"></i>Human Only
                                            @else
                                                <i class="fas fa-balance-scale mr-1"></i>AI + Human
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                {{-- AI Evaluation Buttons (separate for Writing & Speaking) --}}
                                @if($canUseAI)
                                    @if($hasWriting && $writingAttempt)
                                        @if($writingAIEvaluated)
                                            <a href="{{ route('student.full-test.evaluation-details', $fullTestAttempt) }}"
                                               class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-violet-500 to-violet-600 text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                                <i class="fas fa-pen-fancy"></i>View Writing AI Evaluation
                                            </a>
                                        @else
                                            <button onclick="startSectionAIEvaluation('writing')"
                                                    id="ai-eval-btn-writing"
                                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-violet-500 to-violet-600 text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                                <i class="fas fa-pen-fancy"></i>Get Writing AI Evaluation
                                            </button>
                                        @endif
                                    @endif

                                    @if($hasSpeaking && $speakingAttempt)
                                        @if($speakingAIEvaluated)
                                            <a href="{{ route('student.full-test.evaluation-details', $fullTestAttempt) }}"
                                               class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                                <i class="fas fa-microphone"></i>View Speaking AI Evaluation
                                            </a>
                                        @else
                                            <button onclick="startSectionAIEvaluation('speaking')"
                                                    id="ai-eval-btn-speaking"
                                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                                <i class="fas fa-microphone"></i>Get Speaking AI Evaluation
                                            </button>
                                        @endif
                                    @endif
                                @endif

                                {{-- Human Evaluation Button --}}
                                @if($canUseHuman)
                                    @if($writingCompleted || $speakingCompleted)
                                        <a href="{{ route('student.full-test.evaluation-details', $fullTestAttempt) }}"
                                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                            <i class="fas fa-user-tie"></i>View Human Evaluation
                                        </a>
                                    @elseif($writingRequested || $speakingRequested)
                                        <div class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-purple-100 text-purple-700 rounded-xl font-semibold">
                                            <i class="fas fa-spinner fa-spin"></i>Human Evaluation Pending
                                        </div>
                                    @else
                                        <a href="{{ route('student.full-test.request-evaluation', $fullTestAttempt) }}"
                                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                            <i class="fas fa-user-tie"></i>Request Human Evaluation
                                        </a>
                                    @endif
                                @endif

                            {{-- Regular Student Evaluation Options --}}
                            @else
                                @if(!$someRequested)
                                    <a href="{{ route('student.full-test.request-evaluation', $fullTestAttempt) }}"
                                       class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-[#C8102E] to-[#A00E27] text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                        <i class="fas fa-check-circle"></i>Request Evaluation
                                    </a>
                                @elseif($writingCompleted || $speakingCompleted)
                                    <a href="{{ route('student.full-test.evaluation-details', $fullTestAttempt) }}"
                                       class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                        <i class="fas fa-eye"></i>View Evaluation
                                    </a>
                                    @if($hasUnrequestedSections)
                                        <a href="{{ route('student.full-test.request-evaluation', $fullTestAttempt) }}"
                                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-[#C8102E] to-[#A00E27] text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                            <i class="fas fa-plus"></i>Request More
                                        </a>
                                    @endif
                                @elseif($hasUnrequestedSections)
                                    <a href="{{ route('student.full-test.request-evaluation', $fullTestAttempt) }}"
                                       class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-[#C8102E] to-[#A00E27] text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                        <i class="fas fa-plus"></i>Request More
                                    </a>
                                @else
                                    <div class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-amber-100 text-amber-700 rounded-xl font-semibold">
                                        <i class="fas fa-spinner fa-spin"></i>In Progress
                                    </div>
                                @endif
                            @endif
                        @endif

                        <a href="{{ route('student.full-test.index') }}"
                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-all">
                            <i class="fas fa-list"></i>All Full Tests
                        </a>
                        <a href="{{ route('student.dashboard') }}"
                           class="w-full flex items-center justify-center gap-2 px-4 py-3 border border-gray-200 text-gray-600 rounded-xl font-medium hover:bg-gray-50 transition-all">
                            <i class="fas fa-home"></i>Dashboard
                        </a>
                    </div>
                </div>

                <!-- Test Info Card -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h3 class="font-bold text-gray-900 mb-4">Test Information</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Test Type</span>
                            <span class="text-sm font-semibold {{ $fullTestAttempt->fullTest->is_premium ? 'text-amber-600' : 'text-gray-900' }}">
                                {{ $fullTestAttempt->fullTest->is_premium ? 'Premium' : 'Free' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Duration</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $hours }}h {{ $minutes }}m</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Sections</span>
                            <span class="text-sm font-semibold text-gray-900">{{ count($availableSections) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-gray-500">Status</span>
                            <span class="text-sm font-semibold {{ $fullTestAttempt->status === 'completed' ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ ucfirst($fullTestAttempt->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- AI Evaluation Modal --}}
    @if($isOfflineStudent && $canUseAI)
    <div id="aiEvalModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
            <div class="text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-robot text-white text-3xl animate-pulse"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">AI Evaluation in Progress</h3>
                <p class="text-gray-500 text-sm mb-4" id="eval-status">Initializing AI evaluation...</p>
                <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                    <div id="eval-progress" class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
                <p class="text-xs text-gray-400" id="eval-tip">Please wait while we analyze your responses</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Store section attempts data for AI evaluation
        const sectionAttempts = {
            @if($hasWriting && $writingAttempt && !$writingAIEvaluated)
                writing: {{ $writingAttempt->id }},
            @endif
            @if($hasSpeaking && $speakingAttempt && !$speakingAIEvaluated)
                speaking: {{ $speakingAttempt->id }},
            @endif
        };

        // Store ALL section attempts for re-evaluation
        const allSectionAttempts = {
            @if($hasWriting && $writingAttempt)
                writing: {{ $writingAttempt->id }},
            @endif
            @if($hasSpeaking && $speakingAttempt)
                speaking: {{ $speakingAttempt->id }},
            @endif
        };

        // Evaluate a single section (writing or speaking)
        async function startSectionAIEvaluation(section, forceReEvaluate = false) {
            const modal = document.getElementById('aiEvalModal');
            const button = document.getElementById('ai-eval-btn-' + section);
            const statusEl = document.getElementById('eval-status');
            const progressEl = document.getElementById('eval-progress');
            const tipEl = document.getElementById('eval-tip');

            const attemptsToUse = forceReEvaluate ? allSectionAttempts : sectionAttempts;
            const attemptId = attemptsToUse[section] || allSectionAttempts[section];

            if (!attemptId) {
                alert('No ' + section + ' attempt found to evaluate.');
                return;
            }

            // Show modal and disable button
            modal.classList.remove('hidden');
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const sectionLabel = section.charAt(0).toUpperCase() + section.slice(1);

            statusEl.textContent = `${forceReEvaluate ? 'Re-evaluating' : 'Evaluating'} ${sectionLabel} section...`;
            tipEl.textContent = `Please wait while we analyze your ${section} responses`;
            progressEl.style.width = '30%';

            try {
                if (section === 'speaking') {
                    await evaluateSpeakingProgressive(attemptId, csrfToken, statusEl, tipEl, forceReEvaluate);
                } else {
                    // Writing evaluation
                    const response = await fetch('/ai/evaluate/writing', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ attempt_id: attemptId, force_reevaluate: forceReEvaluate })
                    });

                    const data = await response.json();

                    if (!data.success && !data.already_evaluated) {
                        throw new Error(data.error || 'Evaluation failed');
                    }
                }

                // Done!
                progressEl.style.width = '100%';
                statusEl.innerHTML = `<i class="fas fa-check-circle text-emerald-500 mr-2"></i>${sectionLabel} Evaluation Complete!`;
                tipEl.textContent = 'Redirecting to results...';

                setTimeout(() => {
                    window.location.reload();
                }, 1500);

            } catch (error) {
                console.error(`Error evaluating ${section}:`, error);
                statusEl.innerHTML = `<span class="text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>${error.message || 'Evaluation failed'}</span>`;
                tipEl.textContent = 'Please try again later';
                progressEl.style.width = '0%';

                setTimeout(() => {
                    modal.classList.add('hidden');
                    if (button) {
                        button.disabled = false;
                        const icon = section === 'speaking' ? 'fa-microphone' : 'fa-pen-fancy';
                        button.innerHTML = `<i class="fas ${icon} mr-2"></i>Get ${sectionLabel} AI Evaluation`;
                    }
                }, 3000);
            }
        }

        async function evaluateSpeakingProgressive(attemptId, csrfToken, statusEl, tipEl, forceReEvaluate = false) {
            // Step 1: Get status
            statusEl.textContent = 'Checking speaking recordings...';

            const statusRes = await fetch('/ai/evaluate/speaking/status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ attempt_id: attemptId, force_reevaluate: forceReEvaluate })
            });
            const statusData = await statusRes.json();

            if (statusData.status === 'completed' && !forceReEvaluate) {
                return; // Already evaluated (skip if not forcing re-evaluation)
            }

            const recordings = statusData.recordings || [];
            // If forcing re-evaluation, evaluate ALL recordings; otherwise only pending ones
            const recordingsToEvaluate = forceReEvaluate ? recordings : recordings.filter(r => !r.evaluated);

            // Step 2: Evaluate each recording
            for (let i = 0; i < recordingsToEvaluate.length; i++) {
                const recording = recordingsToEvaluate[i];
                statusEl.textContent = `${forceReEvaluate ? 'Re-evaluating' : 'Evaluating'} recording ${i + 1} of ${recordingsToEvaluate.length}...`;
                tipEl.textContent = `Part ${recording.part_number || recording.part}, Question ${recording.question_order || i + 1}`;

                const evalRes = await fetch('/ai/evaluate/speaking/single', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        attempt_id: attemptId,
                        answer_id: recording.answer_id,
                        force_reevaluate: forceReEvaluate
                    })
                });

                const evalData = await evalRes.json();
                if (!evalData.success) {
                    throw new Error(evalData.error || 'Failed to evaluate recording');
                }
            }

            // Step 3: Finalize
            statusEl.textContent = 'Calculating final score...';
            const finalRes = await fetch('/ai/evaluate/speaking/finalize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ attempt_id: attemptId })
            });

            const finalData = await finalRes.json();
            if (!finalData.success) {
                throw new Error(finalData.error || 'Failed to finalize evaluation');
            }
        }
    </script>
    @endpush
    @endif
</x-dashboard-layout>
