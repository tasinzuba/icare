<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Events\TestCompleted;
use App\Exceptions\InvalidAttemptException;
use App\Exceptions\TestAccessDeniedException;
use App\Exceptions\TestTimeExceededException;
use App\Models\StudentAttempt;
use App\Models\StudentAnswer;
use App\Models\TestSet;
use App\Models\QuestionOption;
use App\Helpers\ScoreCalculator;
use App\Services\AnswerValidator;
use App\Services\TestAccessService;
use App\Traits\EnforcesTestTimeLimit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;


class ListeningTestController extends Controller
{
    use EnforcesTestTimeLimit;

    protected AnswerValidator $answerValidator;
    protected TestAccessService $testAccess;

    public function __construct(AnswerValidator $answerValidator, TestAccessService $testAccess)
    {
        $this->answerValidator = $answerValidator;
        $this->testAccess = $testAccess;
    }

    public function index(Request $request): View
    {
        $user = auth()->user();

        // Get all active categories with counts for listening section (filtered by student type)
        $categories = \App\Models\TestCategory::active()
            ->ordered()
            ->withCount(['testSets as listening_count' => function ($query) use ($user) {
                $query->whereHas('section', function ($q) {
                    $q->where('slug', 'listening')->orWhere('name', 'listening');
                })->where('active', true)
                  ->forStudentType($user);
            }])
            ->get();

        // Get test sets query - filtered by student type
        $testSetsQuery = TestSet::whereHas('section', function ($query) {
            $query->where('name', 'listening');
        })->where('active', true)
          ->forStudentType($user);

        // Filter by category if selected
        $selectedCategory = null;
        if ($request->has('category') && $request->category) {
            $selectedCategory = \App\Models\TestCategory::where('slug', $request->category)->first();
            if ($selectedCategory) {
                $testSetsQuery->whereHas('categories', function ($query) use ($selectedCategory) {
                    $query->where('test_categories.id', $selectedCategory->id);
                });
            }
        }

        $testSets = $testSetsQuery->get();

        return view('student.test.listening.index', compact('testSets', 'categories', 'selectedCategory'));
    }
    
    public function confirmDetails(TestSet $testSet)
    {
        if ($testSet->section->name !== 'listening') {
            throw TestAccessDeniedException::wrongSection('listening');
        }

        // Centralized access check (student type + premium)
        $this->testAccess->assertCanAccessSectionTest(auth()->user(), $testSet, 'listening');

        // Get all attempts for this test
        $attempts = StudentAttempt::getAllAttemptsForUserAndTest(auth()->id(), $testSet->id);

        // Show previous attempts if any exist
        $latestAttempt = $attempts->first();
        $canRetake = $latestAttempt && $latestAttempt->canRetake();

        return view('student.test.listening.onboarding.confirm-details', compact('testSet', 'attempts', 'canRetake'));
    }

    public function soundCheck(TestSet $testSet): View
    {
        if ($testSet->section->name !== 'listening') {
            throw TestAccessDeniedException::wrongSection('listening');
        }

        return view('student.test.listening.onboarding.sound-check', compact('testSet'));
    }

    public function instructions(TestSet $testSet): View
    {
        if ($testSet->section->name !== 'listening') {
            throw TestAccessDeniedException::wrongSection('listening');
        }

        return view('student.test.listening.onboarding.instructions', compact('testSet'));
    }
    
    public function start(TestSet $testSet)
    {
        // Eager load all required relations to avoid N+1 queries
        $testSet->load([
            'section',
            'questions' => function ($query) {
                $query->orderBy('part_number')->orderBy('order_number');
            },
            'questions.options' => function ($query) {
                $query->orderBy('order');
            },
            'partAudios',
        ]);

        if ($testSet->section->name !== 'listening') {
            throw TestAccessDeniedException::wrongSection('listening');
        }

        // Detect if this is being called as part of a Full Test flow.
        // FullTestController::section() already: (1) validated access, (2) created StudentAttempt,
        // (3) linked it via FullTestSectionAttempt, and (4) consumed full test quota.
        // In that case we must NOT re-check section test access and NOT consume section quota.
        $isPartOfFullTest = StudentAttempt::where('user_id', auth()->id())
            ->where('test_set_id', $testSet->id)
            ->where('status', 'in_progress')
            ->whereHas('fullTestSectionAttempt')
            ->exists();

        // Only check section test access for standalone section tests (NOT full test sections)
        if (!$isPartOfFullTest) {
            $this->testAccess->assertCanAccessSectionTest(auth()->user(), $testSet, 'listening');
        }

        // Wrap in transaction with locking to prevent race condition
        $attempt = DB::transaction(function () use ($testSet, $isPartOfFullTest) {
            // Check if fresh start requested (abandon any in_progress attempt)
            // But NOT if this is part of a full test (FullTestController already created the attempt)
            if (!$isPartOfFullTest && request()->has('fresh')) {
                StudentAttempt::where('user_id', auth()->id())
                    ->where('test_set_id', $testSet->id)
                    ->where('status', 'in_progress')
                    ->lockForUpdate()
                    ->update(['status' => 'abandoned']);
            }

            // Check if there's an ongoing attempt with lock to prevent race condition
            $attempt = StudentAttempt::where('user_id', auth()->id())
                ->where('test_set_id', $testSet->id)
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->latest('created_at')
                ->first();

            if (!$attempt) {
                // Get the latest completed attempt to determine attempt number
                $latestAttempt = StudentAttempt::getLatestAttempt(auth()->id(), $testSet->id);

                $attemptNumber = 1;
                $isRetake = false;
                $originalAttemptId = null;

                if ($latestAttempt) {
                    // This is a retake
                    $attemptNumber = ($latestAttempt->attempt_number ?? 1) + 1;
                    $isRetake = true;
                    $originalAttemptId = $latestAttempt->original_attempt_id ?? $latestAttempt->id;
                }

                // Create a new attempt
                $attempt = StudentAttempt::create([
                    'user_id' => auth()->id(),
                    'test_set_id' => $testSet->id,
                    'start_time' => now(),
                    'status' => 'in_progress',
                    'attempt_number' => $attemptNumber,
                    'is_retake' => $isRetake,
                    'original_attempt_id' => $originalAttemptId,
                ]);

                // Only consume section test quota for STANDALONE section tests, not full test sections
                if (!$isPartOfFullTest) {
                    // Check if branch allows free retakes for offline students
                    $skipQuota = false;
                    if ($isRetake && auth()->user()->isOfflineStudent()) {
                        $enrollment = auth()->user()->getActiveEnrollment();
                        $skipQuota = $enrollment && $enrollment->branchAllowsRetakes();
                    }
                    app(TestAccessService::class)->consumeSectionTestQuota(auth()->user(), 'listening', $skipQuota);
                }
            }

            return $attempt;
        });

        // If the existing in-progress attempt's time has already expired
        // (student left without submitting and came back after the time limit),
        // auto-finalize it using saved draft answers instead of loading the test
        // UI with a timer stuck at 0:00.
        if ($attempt && $attempt->start_time) {
            $timeLimitMin = $testSet->time_limit_minutes ?? $testSet->section?->time_limit ?? 40;
            $expiresAt = \Carbon\Carbon::parse($attempt->start_time)->addMinutes($timeLimitMin);

            if (now()->greaterThan($expiresAt)) {
                $draftAnswers = $attempt->draft_answers ?: ['__empty' => true];
                $autoRequest = Request::create('', 'POST', [
                    'answers' => $draftAnswers,
                    'auto_submit' => 1,
                ]);
                $autoRequest->setUserResolver(fn () => auth()->user());
                return $this->submit($autoRequest, $attempt);
            }
        }

        // Format questions for Vue and group by part
        $formattedQuestions = $testSet->questions->map(function ($q) {
            return [
                'id' => $q->id,
                'part_number' => $q->part_number,
                'order_number' => $q->order_number,
                'question_type' => $q->question_type,
                'question_group' => $q->question_group,
                'instructions' => $q->instructions,
                'content' => $q->content,
                // C9/H28: NEVER send the answer key (is_correct) to the student. Expose only a
                // neutral max_selections count so the UI can still cap multi-select checkboxes.
                'options' => $q->options->map(function ($opt) {
                    return [
                        'id' => $opt->id,
                        'content' => $opt->content,
                    ];
                }),
                'max_selections' => (int) $q->options->where('is_correct', true)->count(),
                // Strip correct-answer fields (dropdown_correct/blank_answers/mappings.correct/etc.)
                // so the student cannot read the answer key from section_specific_data.
                'section_specific_data' => \App\Models\Question::sanitizeSectionDataForStudent($q->section_specific_data),
                // H19: these sibling columns ARE the answer key (scoring reads matching_pairs.right,
                // form_structure.fields.answer, diagram_hotspots.answer) — strip the answers before
                // sending to the student. Scoring reads them from the DB model, not this payload.
                'matching_pairs' => \App\Models\Question::sanitizeMatchingPairsForStudent($q->matching_pairs),
                'form_structure' => \App\Models\Question::sanitizeFormStructureForStudent($q->form_structure),
                'diagram_hotspots' => \App\Models\Question::sanitizeDiagramHotspotsForStudent($q->diagram_hotspots),
            ];
        });

        // Get single Global Audio (Admin uploads full audio attached to part 0 or first part)
        $globalAudio = $testSet->partAudios()->first();
        $audioUrl = $globalAudio ? $globalAudio->audio_url : null;
        
        // Fallback to older mechanism if partAudios is empty
        if (!$audioUrl) {
            $firstQuestionWithAudio = $testSet->questions()->whereNotNull('media_path')->first();
            if ($firstQuestionWithAudio) {
                // Use the Question model's media_url accessor (handles R2 → local fallback)
                $audioUrl = $firstQuestionWithAudio->media_url;
            }
        }

        // IELTS Computer-Based: 2 minutes extra review time after audio ends
        $reviewTimeSeconds = 2 * 60;

        return \Inertia\Inertia::render('Test/Listening/Show', [
            'testSet' => [
                'id' => $testSet->id,
                'title' => $testSet->title,
                'section' => $testSet->section,
            ],
            'attempt' => [
                'id' => $attempt->id,
                'start_time' => $attempt->start_time,
                'draft_answers' => $attempt->draft_answers,
            ],
            'questions' => $formattedQuestions,
            'serverTime' => now()->toIso8601String(),
            'audioUrl' => $audioUrl,
            'timeLimitSeconds' => ($testSet->time_limit_minutes ?? $testSet->section?->time_limit ?? 40) * 60,
            'reviewTimeSeconds' => $reviewTimeSeconds,
        ]);
    }
    
    public function submit(Request $request, StudentAttempt $attempt): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        // Verify the attempt belongs to the current user
        if ($attempt->user_id !== auth()->id()) {
            throw InvalidAttemptException::notOwner();
        }

        // Check if already completed
        if ($attempt->status === 'completed') {
            throw InvalidAttemptException::alreadyCompleted();
        }

        // H17/M36: resolve the real allowed duration (respects the per-test-set override), compute a
        // NON-NEGATIVE elapsed time, and decide overtime server-side. 'listening' includes the +120s
        // review phase in its allowed window.
        $allowedMinutes = $this->resolveAllowedMinutes($attempt);
        $startTime = $attempt->start_time;
        $actualMinutes = $this->elapsedMinutes($attempt);
        $isOvertime = $this->isTimeExceeded($attempt, 'listening');

        // Check if this is part of a full test
        $fullTestSectionAttempt = \App\Models\FullTestSectionAttempt::where('student_attempt_id', $attempt->id)->first();
        $isPartOfFullTest = $fullTestSectionAttempt !== null;
        
        // H17: once past the deadline, ignore whatever the (possibly timer-bypassed) client posts and
        // score ONLY the work saved on the server as of the deadline (draft_answers frozen by the
        // autosave / emergency-save guards). Never discard already-saved work.
        if ($isOvertime) {
            $request->merge(['answers' => $attempt->draft_answers ?: ['__empty' => true]]);
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'nullable',
            'auto_submit' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $attempt, $isPartOfFullTest, $fullTestSectionAttempt, $actualMinutes, $allowedMinutes, $isOvertime) {
            // Get all questions with options (eager loaded) - excluding passages
            $questions = $attempt->testSet->questions()
                ->with('options')
                ->where('question_type', '!=', 'passage')
                ->get()
                ->keyBy('id');
            
            // Get actual total answerable items (counts blanks, zones, mappings etc.)
            $totalQuestions = \App\Helpers\ScoreCalculator::calculateTotalAnswerableItems($questions);
            
            // Save answers and calculate score
            $correctAnswers = 0;
            $answeredCount = 0;
            
            foreach ($request->answers as $answerKey => $answer) {
                if (empty($answer) && $answer !== '0') {
                    continue;
                }
                
                // Skip if answer is an empty array
                if (is_array($answer) && empty($answer)) {
                    continue;
                }
                
                // Debug log for problematic answers
                if (is_array($answer)) {
                    \Log::info('Processing array answer', [
                        'answer_key' => $answerKey,
                        'answer' => $answer,
                        'answer_type' => gettype($answer)
                    ]);
                }
                
                // Parse answer key for special types
                if (strpos($answerKey, '_') !== false) {
                    // Special type answer: questionId_subIndex
                    list($questionId, $subIndex) = explode('_', $answerKey);
                    $question = $questions->get($questionId);
                    
                    if ($question) {
                        // Check if answer is correct based on type
                        $isCorrect = false;
                        
                        switch ($question->question_type) {
                            case 'matching':
                                if (isset($question->matching_pairs[$subIndex])) {
                                    $correctAnswer = $question->matching_pairs[$subIndex]['right'];
                                    $isCorrect = $this->compareAnswers($answer, $correctAnswer);
                                }
                                break;
                                
                            case 'form_completion':
                                if (isset($question->form_structure['fields'][$subIndex])) {
                                    $correctAnswer = $question->form_structure['fields'][$subIndex]['answer'];
                                    $isCorrect = $this->compareAnswers($answer, $correctAnswer);
                                }
                                break;
                                
                            case 'plan_map_diagram':
                                if (isset($question->diagram_hotspots[$subIndex])) {
                                    $correctAnswer = $question->diagram_hotspots[$subIndex]['answer'];
                                    $isCorrect = $this->compareAnswers($answer, $correctAnswer);
                                }
                                break;
                                
                            case 'drag_drop':
                                // Handle drag & drop answers
                                $sectionData = $question->section_specific_data ?? [];
                                $dragZones = $sectionData['drop_zones'] ?? $sectionData['drag_zones'] ?? [];
                                // Extract zone index from subIndex (e.g., 'zone_1' -> 1)
                                $zoneIdx = str_replace('zone_', '', $subIndex);
                                if (isset($dragZones[$zoneIdx])) {
                                    $correctAnswer = $dragZones[$zoneIdx]['answer'] ?? '';
                                    $isCorrect = $this->compareAnswers($answer, $correctAnswer);
                                }
                                break;
                        }
                        
                        // Save answer - ensure it's properly formatted
                        $answerData = [
                            'sub_index' => $subIndex,
                            'answer' => is_array($answer) ? json_encode($answer) : $answer,
                            'is_correct' => $isCorrect
                        ];
                        
                        StudentAnswer::updateOrCreate(
                            [
                                'attempt_id' => $attempt->id,
                                'question_id' => $questionId,
                            ],
                            [
                                'answer' => json_encode($answerData),
                            ]
                        );
                        
                        if ($isCorrect) {
                            $correctAnswers++;
                        }
                        $answeredCount++;
                    }
                } else {
                    // Regular question answer
                    $questionId = $answerKey;
                    $question = $questions->get($questionId);
                    
                    if (!$question) {
                        continue;
                    }
                    
                    // Handle drag & drop questions with zone-based answers
                    // Note: Use array_key_exists/str_starts_with instead of isset because Laravel's
                    // ConvertEmptyStringsToNull middleware converts "" to null, and isset() returns false for null
                    $answerKeys = is_array($answer) ? array_keys($answer) : [];
                    $hasZoneKeys = collect($answerKeys)->contains(fn($k) => str_starts_with((string)$k, 'zone_'));
                    $hasBlankKeys = collect($answerKeys)->contains(fn($k) => str_starts_with((string)$k, 'blank_'));
                    $hasDropdownKeys = collect($answerKeys)->contains(fn($k) => str_starts_with((string)$k, 'dropdown_'));
                    if (is_array($answer) && $hasZoneKeys) {
                        // This is a drag & drop question with multiple zones
                        $sectionData = $question->section_specific_data ?? [];
                        $dragZones = $sectionData['drop_zones'] ?? $sectionData['drag_zones'] ?? [];

                        foreach ($answer as $zoneKey => $zoneAnswer) {
                            if (strpos($zoneKey, 'zone_') === 0 && !empty($zoneAnswer)) {
                                $zoneIdx = str_replace('zone_', '', $zoneKey);
                                $answeredCount++;

                                // Check if correct
                                if (isset($dragZones[$zoneIdx])) {
                                    $correctAnswer = $dragZones[$zoneIdx]['answer'] ?? '';
                                    if ($this->compareAnswers($zoneAnswer, $correctAnswer)) {
                                        $correctAnswers++;
                                    }
                                }
                            }
                        }
                        
                        // Store the entire answer array as JSON
                        StudentAnswer::updateOrCreate(
                            [
                                'attempt_id' => $attempt->id,
                                'question_id' => $questionId,
                            ],
                            [
                                'selected_option_id' => null,
                                'answer' => json_encode($answer),
                            ]
                        );
                    }
                    // Handle fill-in-the-blank questions with multiple blanks
                    elseif (is_array($answer) && ($hasBlankKeys || $hasDropdownKeys)) {
                        // This is a multi-blank question
                        StudentAnswer::updateOrCreate(
                            [
                                'attempt_id' => $attempt->id,
                                'question_id' => $questionId,
                            ],
                            [
                                'selected_option_id' => null,
                                'answer' => json_encode($answer),
                            ]
                        );
                        
                        // Check each blank separately for IELTS scoring
                        $blankResults = $this->checkMultiBlankAnswer($question, $answer);
                        $answeredCount += $blankResults['answered'];
                        $correctAnswers += $blankResults['correct'];
                    }
                    // Handle multiple choice with multiple selections (checkbox array)
                    elseif (is_array($answer) && $question->question_type === 'multiple_choice') {
                        // Clear any existing answers for this question first
                        StudentAnswer::where('attempt_id', $attempt->id)
                                    ->where('question_id', $questionId)
                                    ->delete();

                        // H18 (audit fix): de-duplicate the selected option ids so a client cannot
                        // POST the same correct id repeatedly to inflate the count / bypass the cap.
                        $uniqueSelections = array_values(array_unique(array_filter($answer, 'is_numeric')));

                        $correctSelections = 0;
                        $savedSelections = 0;
                        foreach ($uniqueSelections as $selectedOptionId) {
                            StudentAnswer::create([
                                'attempt_id' => $attempt->id,
                                'question_id' => $questionId,
                                'selected_option_id' => $selectedOptionId,
                                'answer' => null,
                            ]);

                            $savedSelections++;
                            $option = $question->options->firstWhere('id', $selectedOptionId);
                            if ($option && $option->is_correct) {
                                $correctSelections++;
                            }
                        }

                        // H18 (audit fix): dedup + selection-cap scoring — IELTS partial credit with
                        // anti-gaming. Over-selecting (more distinct options than allowed) scores 0;
                        // otherwise award one mark per correct option actually selected.
                        $totalCorrectOptions = $question->options->where('is_correct', true)->count();
                        $awarded = ($savedSelections > $totalCorrectOptions) ? 0 : $correctSelections;
                        $correctAnswers += $awarded;
                        $answeredCount += min($savedSelections, max($totalCorrectOptions, 1));
                    } else {
                        // Single answer (option or text)
                        StudentAnswer::updateOrCreate(
                            [
                                'attempt_id' => $attempt->id,
                                'question_id' => $questionId,
                            ],
                            [
                                'selected_option_id' => is_numeric($answer) ? $answer : null,
                                'answer' => !is_numeric($answer) ? (is_array($answer) ? json_encode($answer) : $answer) : null,
                            ]
                        );

                        // For multiple choice with single correct answer
                        if ($question->question_type === 'multiple_choice') {
                            $answeredCount++;
                            if (is_numeric($answer)) {
                                $option = $question->options->firstWhere('id', $answer);
                                if ($option && $option->is_correct) {
                                    $correctAnswers++;
                                }
                            }
                        } else {
                            // Other question types
                            $answeredCount++;

                            // Check if correct - use eager loaded options
                            if ($question->requiresOptions() && is_numeric($answer)) {
                                $option = $question->options->firstWhere('id', $answer);
                                if ($option && $option->is_correct) {
                                    $correctAnswers++;
                                }
                            } elseif (!$question->requiresOptions()) {
                                // Check text answer
                                if ($this->checkSingleTextAnswer($question, $answer)) {
                                    $correctAnswers++;
                                }
                            }
                        }
                    }
                }
            }
            
            // Mark attempt as completed with time tracking
            // Also clear draft_answers since they're no longer needed
            $attempt->update([
                'end_time' => now(),
                'status' => 'completed',
                'time_taken_minutes' => $actualMinutes,
                'allowed_minutes' => $allowedMinutes,
                'is_overtime' => $isOvertime,
                'draft_answers' => null,
                'draft_saved_at' => null,
            ]);

            // Use the new partial test score calculation with actual question count
            $scoreData = \App\Helpers\ScoreCalculator::calculatePartialTestScore(
                $correctAnswers,
                $answeredCount,
                $totalQuestions,
                'listening'
            );

            // Log for debugging
            \Log::info('Score calculation result', $scoreData);

            // Store band score and additional data
            $updateData = [
                'band_score' => $scoreData['band_score'] ?? null,
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredCount,
                'correct_answers' => $correctAnswers
            ];

            // Only add optional fields if they exist in the score data
            if (isset($scoreData['completion_percentage'])) {
                $updateData['completion_rate'] = $scoreData['completion_percentage'];
            }
            if (isset($scoreData['confidence'])) {
                $updateData['confidence_level'] = $scoreData['confidence'];
            }
            if (isset($scoreData['is_reliable'])) {
                $updateData['is_complete_attempt'] = $scoreData['is_reliable'];
            }

            try {
                $attempt->update($updateData);
            } catch (\Exception $e) {
                \Log::error('Failed to update attempt', [
                    'error' => $e->getMessage(),
                    'data' => $updateData
                ]);
                // Continue with basic update
                $attempt->update([
                    'band_score' => $scoreData['band_score'] ?? null,
                    'total_questions' => $totalQuestions,
                    'answered_questions' => $answeredCount,
                    'correct_answers' => $correctAnswers
                ]);
            }

            // Store score data in session for display
            session()->flash('score_details', $scoreData);

            // Dispatch TestCompleted event - handles all side effects
            $fullTestAttemptId = $isPartOfFullTest && $fullTestSectionAttempt
                ? $fullTestSectionAttempt->full_test_attempt_id
                : null;

            event(new TestCompleted(
                attempt: $attempt,
                user: auth()->user(),
                section: 'listening',
                scoreData: $scoreData,
                isPartOfFullTest: $isPartOfFullTest,
                fullTestAttemptId: $fullTestAttemptId
            ));
        });
        
        // If part of full test, route based on whether this was an auto-submit (time-up) or manual submit
        if ($isPartOfFullTest && $fullTestSectionAttempt) {
            // Refresh to get updated full test attempt with scores
            $fullTestSectionAttempt->refresh();
            $fullTestAttempt = $fullTestSectionAttempt->fullTestAttempt;
            $fullTestAttempt->refresh();

            // Auto-submit on time-up → skip intermediate "section completed" screen and go straight
            // to the next section (or final results if this was the last section).
            if ($request->boolean('auto_submit')) {
                $nextSection = $fullTestAttempt->getNextSection();
                $url = $nextSection
                    ? route('student.full-test.section', [
                        'fullTestAttempt' => $fullTestAttempt->id,
                        'section' => $nextSection,
                    ])
                    : route('student.full-test.results', $fullTestAttempt);

                if ($request->header('X-Inertia')) {
                    return \Inertia\Inertia::location($url);
                }
                return redirect($url);
            }

            $url = route('student.full-test.section-completed', [
                'fullTestAttempt' => $fullTestAttempt->id,
                'section' => 'listening'
            ]);

            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::location($url);
            }
            return redirect($url)->with('success', 'Listening section completed successfully!');
        }
        
        // Regular test completion
        $url = route('student.results.show', $attempt);
        
        if ($request->header('X-Inertia')) {
            return \Inertia\Inertia::location($url);
        }
        return redirect($url)->with('success', 'Test submitted successfully!');
    }
    
    /**
     * Check multi-blank answer - delegates to AnswerValidator
     */
    protected function checkMultiBlankAnswer($question, $studentAnswers): array
    {
        return $this->answerValidator->checkMultiBlankAnswer($question, $studentAnswers);
    }

    /**
     * Check single text answer - delegates to AnswerValidator
     */
    protected function checkSingleTextAnswer($question, $studentAnswer): bool
    {
        return $this->answerValidator->checkSingleTextAnswer($question, $studentAnswer);
    }

    /**
     * Compare two answers - delegates to AnswerValidator
     */
    protected function compareAnswers($studentAnswer, $correctAnswer): bool
    {
        return $this->answerValidator->compareAnswers($studentAnswer, $correctAnswer);
    }

    /**
     * Auto-save answers to server (called via AJAX every 30 seconds)
     */
    public function autoSave(Request $request, StudentAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($attempt->status !== 'in_progress') {
            return response()->json(['error' => 'Attempt already completed'], 400);
        }

        // H17: reject late draft writes so the graded draft_answers is frozen at the deadline.
        if ($this->isTimeExceeded($attempt, 'listening')) {
            throw TestTimeExceededException::exceeded('listening', $this->resolveAllowedMinutes($attempt) ?? 0, $this->elapsedMinutes($attempt));
        }

        $request->validate([
            'answers' => 'required|array',
        ]);

        try {
            $attempt->update([
                'draft_answers' => $request->answers,
                'draft_saved_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'saved_at' => now()->format('h:i:s A'),
                'answers_count' => count($request->answers),
            ]);
        } catch (\Exception $e) {
            \Log::error('Listening auto-save failed', [
                'attempt_id' => $attempt->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to save',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get saved draft answers
     */
    public function getDraftAnswers(StudentAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'answers' => $attempt->draft_answers ?? [],
            'saved_at' => $attempt->draft_saved_at?->format('h:i:s A'),
        ]);
    }
}