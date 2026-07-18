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
use App\Helpers\ScoreCalculator;
use App\Services\AnswerValidator;
use App\Services\TestAccessService;
use App\Traits\EnforcesTestTimeLimit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReadingTestController extends Controller
{
    use EnforcesTestTimeLimit;

    protected AnswerValidator $answerValidator;
    protected TestAccessService $testAccess;

    public function __construct(AnswerValidator $answerValidator, TestAccessService $testAccess)
    {
        $this->answerValidator = $answerValidator;
        $this->testAccess = $testAccess;
    }

    /**
     * Display a listing of the available reading tests.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Get all active categories with counts for reading section (filtered by student type)
        $categories = \App\Models\TestCategory::active()
            ->ordered()
            ->withCount(['testSets as reading_count' => function ($query) use ($user) {
                $query->whereHas('section', function ($q) {
                    $q->where('slug', 'reading')->orWhere('name', 'reading');
                })->where('active', true)
                  ->forStudentType($user);
            }])
            ->get();

        // Get test sets query - filtered by student type
        $testSetsQuery = TestSet::whereHas('section', function ($query) {
            $query->where('name', 'reading');
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

        return view('student.test.reading.index', compact('testSets', 'categories', 'selectedCategory'));
    }
    
    /**
     * Show candidate information confirmation screen.
     */
    public function confirmDetails(TestSet $testSet)
    {
        // Check if the test belongs to reading section
        if ($testSet->section->name !== 'reading') {
            throw TestAccessDeniedException::wrongSection('reading');
        }

        // Centralized access check (student type + premium)
        $this->testAccess->assertCanAccessSectionTest(auth()->user(), $testSet, 'reading');

        // Get all attempts for this test
        $attempts = StudentAttempt::getAllAttemptsForUserAndTest(auth()->id(), $testSet->id);

        // Show previous attempts if any exist
        $latestAttempt = $attempts->first();
        $canRetake = $latestAttempt && $latestAttempt->canRetake();

        return view('student.test.reading.onboarding.confirm-details', compact('testSet', 'attempts', 'canRetake'));
    }

    /**
     * Show the instructions screen.
     */
    public function instructions(TestSet $testSet): View
    {
        // Check if the test belongs to reading section
        if ($testSet->section->name !== 'reading') {
            throw TestAccessDeniedException::wrongSection('reading');
        }

        return view('student.test.reading.onboarding.instructions', compact('testSet'));
    }
    
    /**
     * Start a new reading test.
     */
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
        ]);

        // Check if the test belongs to reading section
        if ($testSet->section->name !== 'reading') {
            throw TestAccessDeniedException::wrongSection('reading');
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
            $this->testAccess->assertCanAccessSectionTest(auth()->user(), $testSet, 'reading');
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
                    app(TestAccessService::class)->consumeSectionTestQuota(auth()->user(), 'reading', $skipQuota);
                }
            }

            return $attempt;
        });

        // Auto-finalize if the existing in-progress attempt's time has already expired
        // (student left without submitting and the time limit has since passed).
        if ($attempt && $attempt->start_time) {
            $timeLimitMin = $testSet->time_limit_minutes ?? $testSet->section?->time_limit ?? 60;
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

        // C9/H28: NEVER expose the answer key to the student. Hide is_correct (+ option metadata)
        // on the serialized options and expose a neutral max_selections per question so the UI can
        // still cap multi-select. Scoring reads is_correct from the DB, not from this payload, and
        // makeHidden only affects THESE instances (post-completion result views are unaffected).
        $testSet->questions->each(function ($q) {
            $q->setAttribute('max_selections', (int) $q->options->where('is_correct', true)->count());
            $q->options->each(fn ($opt) => $opt->makeHidden(['is_correct', 'metadata']));
            // Also strip correct-answer fields from section_specific_data (dropdown_correct,
            // blank_answers, mappings.correct, sentence correctAnswer, drop_zone answers) — the
            // whole section_specific_data was being shipped to the student, leaking the answer key.
            $q->setAttribute('section_specific_data', \App\Models\Question::sanitizeSectionDataForStudent($q->section_specific_data));
        });

        return \Inertia\Inertia::render('Test/Reading/Show', [
            'testSet' => $testSet,
            'attempt' => $attempt,
            'initialAnswers' => $attempt->draft_answers ?? [],
            'timeLimitSeconds' => ($testSet->time_limit_minutes ?? $testSet->section?->time_limit ?? 60) * 60,
            'serverTime' => now()->toIso8601String(),
            'attemptStartTime' => ($attempt->start_time ?? $attempt->created_at)->toIso8601String(),
        ]);
    }

    /**
     * Submit the reading test answers.
     */
    public function submit(Request $request, StudentAttempt $attempt): \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        // Verify the attempt belongs to the current user and is not already completed
        if ($attempt->user_id !== auth()->id()) {
            throw InvalidAttemptException::notOwner();
        }

        if ($attempt->status === 'completed') {
            throw InvalidAttemptException::alreadyCompleted();
        }

        // H17/M36: resolve the real allowed duration (respects the per-test-set override, fixing the
        // old bug where allowed_minutes stored only the section default), compute a NON-NEGATIVE
        // elapsed time, and decide overtime server-side.
        $allowedMinutes = $this->resolveAllowedMinutes($attempt);
        $startTime = $attempt->start_time;
        $actualMinutes = $this->elapsedMinutes($attempt);
        $isOvertime = $this->isTimeExceeded($attempt, 'reading');

        // Log all incoming data
        \Log::info('=== READING TEST SUBMISSION START ===', [
            'attempt_id' => $attempt->id,
            'user_id' => auth()->id(),
            'request_method' => $request->method(),
            'has_answers' => $request->has('answers'),
            'all_input_keys' => array_keys($request->all()),
            'answers_count' => is_array($request->input('answers')) ? count($request->input('answers')) : 0
        ]);
        
        // Log each answer
        if ($request->has('answers')) {
            foreach ($request->input('answers', []) as $key => $value) {
                \Log::info('Answer received', [
                    'key' => $key,
                    'value' => $value,
                    'type' => gettype($value)
                ]);
            }
        }

        // Check if this is part of a full test
        $fullTestSectionAttempt = \App\Models\FullTestSectionAttempt::where('student_attempt_id', $attempt->id)->first();
        $isPartOfFullTest = $fullTestSectionAttempt !== null;
        
        // H17: once past the deadline, ignore whatever the (possibly timer-bypassed) client posts and
        // score ONLY the work saved on the server as of the deadline (draft_answers were frozen by the
        // autosave / emergency-save guards). Never discard already-saved work.
        if ($isOvertime) {
            $request->merge(['answers' => $attempt->draft_answers ?: ['__empty' => true]]);
        }

        // Validate the submission
        $request->validate([
            'answers' => 'required|array',
            'auto_submit' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $attempt, $isPartOfFullTest, $fullTestSectionAttempt, $actualMinutes, $allowedMinutes, $isOvertime) {
            // Get all questions with options (excluding passages) - eager loaded
            $questions = $attempt->testSet->questions()
                ->with('options')
                ->where('question_type', '!=', 'passage')
                ->get();
            
            \Log::info('Questions in test', [
                'total_questions' => $questions->count(),
                'matching_headings_count' => $questions->where('question_type', 'matching_headings')->count()
            ]);
            
            // Get actual total answerable items (counts blanks, zones, mappings etc.)
            $totalQuestions = \App\Helpers\ScoreCalculator::calculateTotalAnswerableItems($questions);
            
            // Track answered questions and correct answers
            $answeredCount = 0;
            $correctAnswers = 0;
            $matchingHeadingSaved = 0;
            
            // Save answers
            foreach ($request->answers as $questionId => $answer) {
                // Check if this is a master matching headings or sentence completion (e.g., 807_q1)
                if (str_contains($questionId, '_q')) {
                    // Extract question ID and sub-question number
                    [$actualQuestionId, $subQuestionNum] = explode('_q', $questionId);
                    
                    $question = $questions->find($actualQuestionId);
                    
                    // PRIORITY 1: Check if it's matching headings first
                    if ($question && $question->question_type === 'matching_headings' && !empty($answer)) {
                        \Log::info('MATCHING HEADING DETECTED', [
                            'original_key' => $questionId,
                            'question_id' => $actualQuestionId,
                            'sub_question_num' => $subQuestionNum,
                            'value' => $answer
                        ]);
                        
                        // Find the option based on letter (A, B, C, etc.)
                        $letter = strtoupper($answer);
                        $optionIndex = ord($letter) - ord('A');
                        $option = $question->options->sortBy('order')->values()->get($optionIndex);
                        
                        if ($option) {
                            $saved = StudentAnswer::create([
                                'attempt_id' => $attempt->id,
                                'question_id' => $actualQuestionId,
                                'selected_option_id' => $option->id,
                                'answer' => json_encode([
                                    'sub_question' => $subQuestionNum,
                                    'selected_letter' => $letter,
                                    'option_id' => $option->id
                                ]),
                            ]);
                            
                            if ($saved && $saved->exists) {
                                $matchingHeadingSaved++;
                                \Log::info('MATCHING HEADING SAVED', [
                                    'student_answer_id' => $saved->id,
                                    'question_id' => $actualQuestionId,
                                    'sub_question' => $subQuestionNum,
                                    'letter' => $letter,
                                    'option_id' => $option->id
                                ]);
                            }
                            
                            $answeredCount++;
                            
                            // Check if correct based on mappings
                            $mappings = $question->section_specific_data['mappings'] ?? [];
                            foreach ($mappings as $mapping) {
                                if ($mapping['question'] == $subQuestionNum && strtoupper($mapping['correct']) == $letter) {
                                    $correctAnswers++;
                                    break;
                                }
                            }
                        } else {
                            \Log::warning('MATCHING HEADING OPTION NOT FOUND', [
                                'letter' => $letter,
                                'option_index' => $optionIndex,
                                'total_options' => $question->options->count()
                            ]);
                        }
                        
                        continue; // Skip to next iteration
                    }
                    
                    // PRIORITY 2: Check if it's sentence completion
                    if ($question && $question->question_type === 'sentence_completion' && !empty($answer)) {
                        \Log::info('SENTENCE COMPLETION ANSWER DETECTED', [
                            'original_key' => $questionId,
                            'question_id' => $actualQuestionId,
                            'sub_question_num' => $subQuestionNum,
                            'value' => $answer
                        ]);
                        
                        // Save the answer
                        $saved = StudentAnswer::create([
                                'attempt_id' => $attempt->id,
                                'question_id' => $actualQuestionId,
                                'selected_option_id' => null,
                                'answer' => json_encode([
                                    'sub_question' => $subQuestionNum,
                                    'selected_answer' => $answer
                                ]),
                            ]);
                            
                            if ($saved && $saved->exists) {
                                \Log::info('SENTENCE COMPLETION SAVED SUCCESSFULLY', [
                                    'student_answer_id' => $saved->id,
                                    'question_id' => $actualQuestionId,
                                    'sub_question' => $subQuestionNum,
                                    'selected_answer' => $answer
                                ]);
                            }
                            
                        $answeredCount++;
                        
                        // Check if correct based on sentence completion data
                        $sectionData = $question->section_specific_data;
                        if (isset($sectionData['sentence_completion']['sentences'])) {
                            foreach ($sectionData['sentence_completion']['sentences'] as $sentence) {
                                if ($sentence['questionNumber'] == $subQuestionNum &&
                                    $this->compareAnswers($answer, $sentence['correctAnswer'])) {
                                    $correctAnswers++;
                                    break;
                                }
                            }
                        }
                    }
                    continue; // Skip to next iteration
                }
                
                // Handle master matching headings with sub-questions (e.g., 123_q14)
                if (str_contains($questionId, '_q')) {
                    // Extract master question ID and sub-question number
                    [$masterQuestionId, $subQuestionNum] = explode('_q', $questionId);
                    
                    \Log::info('MASTER MATCHING HEADING DETECTED', [
                        'original_key' => $questionId,
                        'master_question_id' => $masterQuestionId,
                        'sub_question_num' => $subQuestionNum,
                        'value' => $answer
                    ]);
                    
                    if (!empty($answer)) {
                        $masterQuestion = $questions->find($masterQuestionId);
                        if ($masterQuestion && $masterQuestion->question_type === 'matching_headings' && $masterQuestion->isMasterMatchingHeading()) {
                            // Find the correct option based on letter (A, B, C, etc.)
                            $optionIndex = ord($answer) - ord('A');
                            $option = $masterQuestion->options->sortBy('order')->values()->get($optionIndex);
                            
                            if ($option) {
                                $saved = StudentAnswer::create([
                                    'attempt_id' => $attempt->id,
                                    'question_id' => $masterQuestionId,
                                    'selected_option_id' => $option->id,
                                    'answer' => json_encode([
                                        'sub_question' => $subQuestionNum,
                                        'selected_letter' => $answer,
                                        'option_id' => $option->id
                                    ]),
                                ]);
                                
                                if ($saved && $saved->exists) {
                                    $matchingHeadingSaved++;
                                    \Log::info('MASTER MATCHING HEADING SAVED SUCCESSFULLY', [
                                        'student_answer_id' => $saved->id,
                                        'master_question_id' => $masterQuestionId,
                                        'sub_question' => $subQuestionNum,
                                        'selected_letter' => $answer,
                                        'selected_option_id' => $option->id
                                    ]);
                                }
                                
                                $answeredCount++;
                                
                                // Check if correct based on mappings (case-insensitive)
                                $mappings = $masterQuestion->section_specific_data['mappings'] ?? [];
                                foreach ($mappings as $mapping) {
                                    if ($mapping['question'] == $subQuestionNum &&
                                        strtoupper(trim($mapping['correct'])) === strtoupper(trim($answer))) {
                                        $correctAnswers++;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    continue; // Skip to next iteration
                }
                
                // Check if this is a matching headings paragraph answer (legacy format)
                if (str_contains($questionId, '_para_')) {
                    // Extract actual question ID and paragraph index
                    [$actualQuestionId, $paraIndex] = explode('_para_', $questionId);
                    
                    \Log::info('MATCHING HEADING DETECTED (LEGACY)', [
                        'original_key' => $questionId,
                        'question_id' => $actualQuestionId,
                        'para_index' => $paraIndex,
                        'value' => $answer
                    ]);
                    
                    if (!empty($answer) && is_numeric($answer)) {
                        $question = $questions->find($actualQuestionId);
                        if ($question && $question->question_type === 'matching_headings') {
                            $saved = StudentAnswer::create([
                                'attempt_id' => $attempt->id,
                                'question_id' => $actualQuestionId,
                                'selected_option_id' => $answer,
                                'answer' => json_encode(['paragraph' => $paraIndex, 'option_id' => $answer]),
                            ]);
                            
                            if ($saved && $saved->exists) {
                                $matchingHeadingSaved++;
                                \Log::info('MATCHING HEADING SAVED SUCCESSFULLY (LEGACY)', [
                                    'student_answer_id' => $saved->id,
                                    'question_id' => $actualQuestionId,
                                    'paragraph' => $paraIndex,
                                    'selected_option_id' => $answer
                                ]);
                            }
                            
                            $answeredCount++;
                            
                            // Check if correct
                            $option = $question->options->find($answer);
                            if ($option && $option->is_correct) {
                                $correctAnswers++;
                            }
                        }
                    }
                    continue; // Skip to next iteration
                }
                
                // Skip if no answer provided
                if (empty($answer) && $answer !== '0') {
                    continue;
                }
                
                $question = $questions->find($questionId);
                if (!$question || $question->question_type === 'passage') {
                    continue;
                }
                
                // Log the answer being processed
                \Log::info('Processing regular answer', [
                    'question_id' => $questionId,
                    'question_type' => $question->question_type,
                    'answer' => $answer,
                    'is_array' => is_array($answer)
                ]);
                
                if (is_array($answer)) {
                    // Log array answers for debugging
                    \Log::info('Processing array answer for question', [
                        'question_id' => $questionId,
                        'answer_array' => $answer,
                        'question_type' => $question->question_type ?? 'unknown'
                    ]);
                    
                    // Handle array answers (blanks, dropdowns or multiple selections)
                    // Note: Use array_key_exists instead of isset because Laravel's
                    // ConvertEmptyStringsToNull middleware converts "" to null,
                    // and isset() returns false for null values
                    $hasBlankKeys = collect(array_keys($answer))->contains(fn($k) => str_starts_with((string)$k, 'blank_'));
                    $hasDropdownKeys = collect(array_keys($answer))->contains(fn($k) => str_starts_with((string)$k, 'dropdown_'));
                    $hasHeadingKeys = collect(array_keys($answer))->contains(fn($k) => str_starts_with((string)$k, 'heading_'));
                    if ($hasBlankKeys || $hasDropdownKeys || $hasHeadingKeys) {
                        // Fill-in-the-blanks with multiple blanks OR inline heading/dropdowns
                        $combinedAnswer = json_encode($answer);
                        StudentAnswer::updateOrCreate(
                            [
                                'attempt_id' => $attempt->id,
                                'question_id' => $questionId,
                            ],
                            [
                                'selected_option_id' => null,
                                'answer' => $combinedAnswer,
                            ]
                        );
                        
                        // Check each blank/dropdown separately for IELTS scoring
                        // (matching_grid stores + scores identically to dropdown_selection — #4)
                        if (in_array($question->question_type, ['dropdown_selection', 'matching_grid'])) {
                            $sectionData = $question->section_specific_data;
                            
                            // For dropdown_selection, each dropdown counts as a separate question
                            foreach ($answer as $dropdownKey => $dropdownValue) {
                                if (strpos($dropdownKey, 'dropdown_') === 0 && !empty($dropdownValue)) {
                                    $answeredCount++;
                                    
                                    // Extract dropdown number
                                    $dropdownNum = str_replace('dropdown_', '', $dropdownKey);
                                    
                                    // Check if correct
                                    if (isset($sectionData['dropdown_correct'][$dropdownNum])) {
                                        $correctIndex = $sectionData['dropdown_correct'][$dropdownNum];
                                        $options = explode(',', $sectionData['dropdown_options'][$dropdownNum] ?? '');
                                        $correctAnswer = isset($options[$correctIndex]) ? trim($options[$correctIndex]) : '';
                                        
                                        \Log::info('Dropdown answer check', [
                                            'dropdown_num' => $dropdownNum,
                                            'student_answer' => $dropdownValue,
                                            'correct_answer' => $correctAnswer,
                                            'correct_index' => $correctIndex,
                                            'options' => $options
                                        ]);
                                        
                                        if ($this->compareAnswers($dropdownValue, $correctAnswer)) {
                                            $correctAnswers++;
                                        }
                                    }
                                }
                            }
                        } else {
                            // Regular blank checking
                            $blankResults = $this->checkMultiBlankAnswer($question, $answer);
                            $answeredCount += $blankResults['answered'];
                            $correctAnswers += $blankResults['correct'];
                        }
                    } else {
                        // Multiple selection question (multiple choice with checkboxes)
                        \Log::info('Processing multiple selection answer', [
                            'question_id' => $questionId,
                            'answer_values' => $answer,
                            'question_type' => $question->question_type
                        ]);
                        
                        // First, clear any existing answers for this question
                        StudentAnswer::where('attempt_id', $attempt->id)
                                    ->where('question_id', $questionId)
                                    ->delete();

                        // H18 (audit fix): de-duplicate the selected option ids before saving/scoring.
                        // A client could otherwise POST the same correct option id multiple times to
                        // inflate the correct count and slip past the selection cap.
                        $uniqueSelections = array_values(array_unique(array_filter(
                            is_array($answer) ? $answer : [$answer],
                            'is_numeric'
                        )));

                        $savedCount = 0;
                        $correctSelections = 0;

                        foreach ($uniqueSelections as $value) {
                            $saved = StudentAnswer::create([
                                'attempt_id' => $attempt->id,
                                'question_id' => $questionId,
                                'selected_option_id' => $value,
                                'answer' => null,
                            ]);

                            if ($saved && $saved->id) {
                                $savedCount++;
                                $option = $question->options->find($value);
                                if ($option && $option->is_correct) {
                                    $correctSelections++;
                                }
                            }
                        }

                        \Log::info('Multi-select saved', [
                            'question_id' => $questionId,
                            'saved_count' => $savedCount,
                            'correct_selections' => $correctSelections,
                        ]);

                        // For multiple choice questions with multiple correct answers
                        if ($question->question_type === 'multiple_choice') {
                            $totalCorrectOptions = $question->options->where('is_correct', true)->count();

                            // H18 (audit fix): dedup + selection-cap scoring — proper IELTS partial
                            // credit with anti-gaming. Selecting MORE than the allowed number of
                            // options scores 0 for the group (IELTS over-selection rule); otherwise
                            // award one mark per correct option actually selected. Defeats the
                            // "tick every option" and "duplicate a known correct id" exploits AND no
                            // longer penalizes an honest student who picks the right count with one
                            // wrong choice.
                            $awarded = ($savedCount > $totalCorrectOptions) ? 0 : $correctSelections;

                            if ($totalCorrectOptions > 1) {
                                $correctAnswers += $awarded;
                                $answeredCount += min($savedCount, $totalCorrectOptions);
                            } else {
                                if ($awarded > 0) {
                                    $correctAnswers++;
                                }
                                $answeredCount++;
                            }
                        } else {
                            $answeredCount++;
                        }
                    }
                } else {
                    // Single answer
                    $hasOptions = $question->options->count() > 0;
                    
                    if ($hasOptions && is_numeric($answer)) {
                        // Option ID (single choice, true/false, etc.)
                        StudentAnswer::updateOrCreate(
                            [
                                'attempt_id' => $attempt->id,
                                'question_id' => $questionId,
                            ],
                            [
                                'selected_option_id' => $answer,
                                'answer' => null,
                            ]
                        );
                        
                        // Check if correct
                        $option = $question->options->find($answer);
                        if ($option && $option->is_correct) {
                            $correctAnswers++;
                        }
                    } else {
                        // Text answer
                        StudentAnswer::updateOrCreate(
                            [
                                'attempt_id' => $attempt->id,
                                'question_id' => $questionId,
                            ],
                            [
                                'selected_option_id' => null,
                                'answer' => $answer,
                            ]
                        );
                        
                        // Check text answer
                        if ($this->checkSingleTextAnswer($question, $answer)) {
                            $correctAnswers++;
                        }
                    }
                    
                    $answeredCount++;
                }
            }
            
            \Log::info('SUBMISSION SUMMARY', [
                'total_questions' => $totalQuestions,
                'answered_count' => $answeredCount,
                'correct_answers' => $correctAnswers,
                'matching_headings_saved' => $matchingHeadingSaved
            ]);
            
            // Mark attempt as completed with time tracking
            // Also clear draft_answers since they're no longer needed
            $attempt->update([
                'end_time' => now(),
                'status' => 'completed',
                'time_taken_minutes' => $actualMinutes,
                'allowed_minutes' => $allowedMinutes,
                'is_overtime' => $isOvertime,
                'draft_answers' => null,      // Clear draft after submission
                'draft_saved_at' => null,     // Clear timestamp too
            ]);

            // Use the new partial test score calculation with actual question count
            // Pass the test type for Reading (academic/general)
            $testType = $attempt->testSet->test_type ?? 'academic';
            $scoreData = \App\Helpers\ScoreCalculator::calculatePartialTestScore(
                $correctAnswers,
                $answeredCount,
                $totalQuestions,
                'reading',
                $testType
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
                section: 'reading',
                scoreData: $scoreData,
                isPartOfFullTest: $isPartOfFullTest,
                fullTestAttemptId: $fullTestAttemptId
            ));

            \Log::info('=== READING TEST SUBMISSION COMPLETE ===');
        });
        
        $url = '';
        $successMessage = '';

        // If part of full test, redirect to section completed screen
        if ($isPartOfFullTest && $fullTestSectionAttempt) {
            // Refresh to get updated full test attempt with scores
            $fullTestSectionAttempt->refresh();
            $fullTestAttempt = $fullTestSectionAttempt->fullTestAttempt;
            $fullTestAttempt->refresh();

            // Auto-submit on time-up → skip intermediate screen, go straight to next section / results
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
                'section' => 'reading'
            ]);
            $successMessage = 'Reading section completed successfully!';
        } else {
            // Regular test completion
            $url = route('student.results.show', $attempt);
            $successMessage = 'Test submitted successfully!';
        }

        if ($request->header('X-Inertia')) {
            return \Inertia\Inertia::location($url);
        }
        
        return redirect($url)->with('success', $successMessage);
    }
    
    /**
     * Check multi-blank answer - uses trait + AnswerValidator for dropdowns
     */
    protected function checkMultiBlankAnswer($question, $studentAnswers): array
    {
        // Use the trait method for blanks
        $results = $question->checkMultipleBlanks($studentAnswers);

        // Count actually answered blanks (not total blanks)
        $answeredBlanks = 0;
        foreach ($results['details'] as $blankNum => $detail) {
            $studentAnswer = $detail['student_answer'] ?? '';
            if ($studentAnswer !== null && trim((string)$studentAnswer) !== '') {
                $answeredBlanks++;
            }
        }

        // Also check dropdowns via AnswerValidator
        $dropdownResults = $this->answerValidator->checkMultiBlankAnswer($question, $studentAnswers);

        // Check heading dropdowns using eager-loaded options (fixes N+1)
        $headingResults = $this->answerValidator->checkHeadingDropdownAnswers($studentAnswers, $question->options);

        // Include dropdown results which were historically left out
        $dropdownAnswered = $dropdownResults['answered'] ?? 0;
        $dropdownCorrect = $dropdownResults['correct'] ?? 0;

        return [
            'answered' => $answeredBlanks + $dropdownAnswered + $headingResults['answered'],
            'correct' => $results['correct'] + $dropdownCorrect + $headingResults['correct'],
            'details' => $results['details'] ?? []
        ];
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
        return $this->answerValidator->compareAnswers($studentAnswer, $correctAnswer, true);
    }

    /**
     * Check if a string is valid JSON - delegates to AnswerValidator
     */
    protected function isJson($string): bool
    {
        return $this->answerValidator->isJson($string);
    }

    /**
     * Auto-save answers to server (called via AJAX every 30 seconds)
     *
     * This provides data safety - even if browser crashes, answers are preserved.
     * Answers are stored in the attempt's draft_answers column as JSON.
     */
    public function autoSave(Request $request, StudentAttempt $attempt)
    {
        // Verify the attempt belongs to the current user
        if ($attempt->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Only allow auto-save for in_progress attempts
        if ($attempt->status !== 'in_progress') {
            return response()->json(['error' => 'Attempt already completed'], 400);
        }

        // H17: reject late draft writes so the graded draft_answers is frozen at the deadline.
        // (Renders as JSON 422 for this XHR call; already-saved draft is untouched.)
        if ($this->isTimeExceeded($attempt, 'reading')) {
            throw TestTimeExceededException::exceeded('reading', $this->resolveAllowedMinutes($attempt) ?? 0, $this->elapsedMinutes($attempt));
        }

        $request->validate([
            'answers' => 'nullable|array',
        ]);

        try {
            $attempt->update([
                'draft_answers' => $request->answers,
                'draft_saved_at' => now(),
            ]);

            \Log::info('Auto-save successful', [
                'attempt_id' => $attempt->id,
                'answers_count' => count($request->answers),
                'saved_at' => now()->toDateTimeString(),
            ]);

            return response()->json([
                'success' => true,
                'saved_at' => now()->format('h:i:s A'),
                'answers_count' => count($request->answers),
            ]);
        } catch (\Exception $e) {
            \Log::error('Auto-save failed', [
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
     * Get saved draft answers (called on page load to restore answers)
     */
    public function getDraftAnswers(StudentAttempt $attempt)
    {
        // Verify the attempt belongs to the current user
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
