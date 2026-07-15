<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ResultDataTrait;
use App\Helpers\ScoreCalculator;
use App\Models\FullTest;
use App\Models\FullTestAttempt;
use App\Models\StudentAttempt;
use App\Models\WebsiteSetting;
use App\Services\AnswerValidator;
use App\Services\TestAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FullTestController extends Controller
{
    use ResultDataTrait;

    protected TestAccessService $testAccess;
    protected AnswerValidator $answerValidator;

    public function __construct(TestAccessService $testAccess, AnswerValidator $answerValidator)
    {
        $this->testAccess = $testAccess;
        $this->answerValidator = $answerValidator;
    }

    protected function getAnswerValidator(): AnswerValidator
    {
        return $this->answerValidator;
    }

    /**
     * Display available full tests.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Filter Full Tests by student type (offline students see offline tests, online students see online tests)
        // Get all active categories
        $categories = \App\Models\TestCategory::active()
            ->ordered()
            ->get();

        // Get full tests filtered by student type
        $query = FullTest::active()->forStudentType($user)->with('testSets');

        // Filter by category if selected
        $selectedCategory = null;
        if ($request->has('category') && $request->category) {
            $selectedCategory = \App\Models\TestCategory::where('slug', $request->category)->first();
            if ($selectedCategory) {
                $query->whereHas('testSets', function ($q) use ($selectedCategory) {
                    $q->whereHas('categories', function ($catQuery) use ($selectedCategory) {
                        $catQuery->where('test_categories.id', $selectedCategory->id);
                    });
                });
            }
        }

        $fullTests = $query->orderBy('order_number')->get();
        
        // Get user's attempts
        $attempts = FullTestAttempt::where('user_id', $user->id)
            ->with('fullTest')
            ->latest()
            ->get()
            ->groupBy('full_test_id');
        
        return view('student.full-test.index', compact('fullTests', 'attempts', 'categories', 'selectedCategory'));
    }

    /**
     * Show onboarding/confirmation page.
     */
    public function onboarding(FullTest $fullTest)
    {
        $user = auth()->user();

        // Centralized access check (student type + premium + enrollment/subscription + quota)
        $access = $this->testAccess->canAccessFullTest($user, $fullTest);
        if (!$access['allowed']) {
            return redirect()->route($access['redirect'])->with('error', $access['reason']);
        }
        
        // Get any in-progress attempt
        $inProgressAttempt = FullTestAttempt::where('user_id', $user->id)
            ->where('full_test_id', $fullTest->id)
            ->where('status', 'in_progress')
            ->first();
        
        return view('student.full-test.onboarding', compact('fullTest', 'inProgressAttempt'));
    }

    /**
     * Start or resume full test.
     */
    public function start(Request $request, FullTest $fullTest)
    {
        $user = auth()->user();
        $isOfflineStudent = $user->isOfflineStudent();

        // Centralized access check (student type + premium + enrollment/subscription + quota)
        $access = $this->testAccess->canAccessFullTest($user, $fullTest);
        if (!$access['allowed']) {
            return redirect()->route($access['redirect'])->with('error', $access['reason']);
        }

        DB::beginTransaction();

        try {
            // Check if fresh start requested (abandon any in_progress attempt)
            if ($request->has('fresh')) {
                FullTestAttempt::where('user_id', $user->id)
                    ->where('full_test_id', $fullTest->id)
                    ->where('status', 'in_progress')
                    ->update(['status' => 'abandoned']);
            }

            // Check for existing in-progress attempt
            $attempt = FullTestAttempt::where('user_id', $user->id)
                ->where('full_test_id', $fullTest->id)
                ->where('status', 'in_progress')
                ->first();

            if (!$attempt) {
                // Re-validate quota before creating new attempt (within transaction)
                $recheck = $this->testAccess->canAccessFullTest($user, $fullTest);
                if (!$recheck['allowed']) {
                    DB::rollback();
                    return redirect()->route($recheck['redirect'])->with('error', $recheck['reason']);
                }

                // Get first available section
                $availableSections = $fullTest->getAvailableSections();
                $firstSection = $availableSections[0] ?? 'listening';

                // Create new attempt
                $attempt = FullTestAttempt::create([
                    'user_id' => $user->id,
                    'full_test_id' => $fullTest->id,
                    'start_time' => now(),
                    'status' => 'in_progress',
                    'current_section' => $firstSection
                ]);

                // Detect if this is a retake (completed attempt exists for same test)
                $isRetake = false;
                if ($isOfflineStudent) {
                    $enrollment = $user->getActiveEnrollment();
                    if ($enrollment && $enrollment->branchAllowsRetakes()) {
                        $isRetake = $enrollment->isFullTestRetake($fullTest->id);
                    }
                }

                // Atomically consume quota (race-condition proof) — skip for retakes
                $this->testAccess->consumeFullTestQuota($user, $isRetake);
            }
            
            DB::commit();
            
            // Redirect to current/next section
            $nextSection = $attempt->getNextSection() ?? $attempt->current_section;
            
            return redirect()->route('student.full-test.section', [
                'fullTestAttempt' => $attempt->id,
                'section' => $nextSection
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->route('student.full-test.index')
                ->with('error', 'Failed to start test. Please try again.');
        }
    }

    /**
     * Continue/Resume an in-progress full test.
     * Redirects to the current or next section.
     */
    public function continueTest(FullTestAttempt $fullTestAttempt)
    {
        // Validate user owns this attempt
        if ($fullTestAttempt->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if attempt is still in progress
        if ($fullTestAttempt->status !== 'in_progress') {
            return redirect()->route('student.full-test.results', $fullTestAttempt);
        }

        // Check if attempt has expired for offline students (24 hour limit)
        if ($fullTestAttempt->isExpiredForOfflineStudent()) {
            $fullTestAttempt->markAsExpired();

            $dashboardRoute = auth()->user()->isOfflineStudent() ? 'offline.dashboard' : 'student.dashboard';

            return redirect()->route($dashboardRoute)
                ->with('error', 'This test attempt has expired. Offline students must complete full tests within 24 hours of starting. This test has been counted as one of your attempts.');
        }

        // Get the next section to complete (or current section)
        $nextSection = $fullTestAttempt->getNextSection() ?? $fullTestAttempt->current_section;

        if (!$nextSection) {
            // All sections completed, show results
            return redirect()->route('student.full-test.results', $fullTestAttempt);
        }

        return redirect()->route('student.full-test.section', [
            'fullTestAttempt' => $fullTestAttempt->id,
            'section' => $nextSection
        ]);
    }

    /**
     * Display section test.
     */
    public function section(FullTestAttempt $fullTestAttempt, string $section)
    {
        // Validate user owns this attempt
        if ($fullTestAttempt->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if attempt has expired for offline students (24 hour limit)
        if ($fullTestAttempt->isExpiredForOfflineStudent()) {
            $fullTestAttempt->markAsExpired();

            $dashboardRoute = auth()->user()->isOfflineStudent() ? 'offline.dashboard' : 'student.dashboard';

            return redirect()->route($dashboardRoute)
                ->with('error', 'This test attempt has expired. Offline students must complete full tests within 24 hours of starting. This test has been counted as one of your attempts.');
        }

        // Premium access check via centralized service
        $user = auth()->user();
        $premiumCheck = $this->testAccess->checkPremiumAccess($user, $fullTestAttempt->fullTest->is_premium);
        if (!$premiumCheck['allowed']) {
            return redirect()->route($premiumCheck['redirect'])->with('error', $premiumCheck['reason']);
        }

        // Validate section
        if (!in_array($section, ['listening', 'reading', 'writing', 'speaking'])) {
            abort(404);
        }

        // Check if this test has this section
        if (!$fullTestAttempt->fullTest->hasSection($section)) {
            // Skip to next available section
            $nextSection = $fullTestAttempt->getNextSection();
            if ($nextSection) {
                return redirect()->route('student.full-test.section', [
                    'fullTestAttempt' => $fullTestAttempt->id,
                    'section' => $nextSection
                ]);
            } else {
                return redirect()->route('student.full-test.results', $fullTestAttempt);
            }
        }
        
        // H17: keep the original per-section start_time across re-entry (default null = fresh clock).
        $preservedStartTime = null;

        // Check if section is TRULY completed (StudentAttempt status = completed)
        $sectionAttempt = $fullTestAttempt->sectionAttempts()
            ->where('section_type', $section)
            ->with('studentAttempt')
            ->first();

        if ($sectionAttempt) {
            $studentAttemptStatus = $sectionAttempt->studentAttempt->status ?? null;

            if ($studentAttemptStatus === 'completed') {
                // Section genuinely completed — skip to next
                $nextSection = $fullTestAttempt->getNextSection();

                if ($nextSection) {
                    return redirect()->route('student.full-test.section', [
                        'fullTestAttempt' => $fullTestAttempt->id,
                        'section' => $nextSection
                    ]);
                } else {
                    return redirect()->route('student.full-test.results', $fullTestAttempt);
                }
            }

            // Section was started but NOT completed (page reload, tab close, network drop).
            // H17: preserve the ORIGINAL start_time so re-entering the section does NOT reset the
            // per-section deadline (otherwise a student could get unlimited time by leaving and
            // returning). cleanupStaleSectionAttempt() abandons the old row, so capture it first.
            $preservedStartTime = $sectionAttempt->studentAttempt->start_time ?? null;

            // Clean up the stale FullTestSectionAttempt so a fresh attempt can be created.
            $fullTestAttempt->cleanupStaleSectionAttempt($section);
        }

        // Update current section
        $fullTestAttempt->update(['current_section' => $section]);

        // Get test set for this section
        $testSet = $fullTestAttempt->fullTest->{$section . 'TestSet'}();

        if (!$testSet) {
            return redirect()->route('student.full-test.index')
                ->with('error', 'Test section not configured properly.');
        }

        // Abandon any existing in-progress attempts for this test_set_id
        // This prevents the section controller from finding an old attempt with stale start_time
        StudentAttempt::where('user_id', auth()->id())
            ->where('test_set_id', $testSet->id)
            ->where('status', 'in_progress')
            ->update(['status' => 'abandoned']);

        // Create student attempt for this section.
        // H17: reuse the original start_time on re-entry so the per-section deadline can't be reset.
        $studentAttempt = StudentAttempt::create([
            'user_id' => auth()->id(),
            'test_set_id' => $testSet->id,
            'start_time' => $preservedStartTime ?? now(),
            'status' => 'in_progress',
            'is_complete_attempt' => true,
            'total_questions' => $testSet->questions()->count()
        ]);

        // Link to full test attempt
        $fullTestAttempt->sectionAttempts()->create([
            'student_attempt_id' => $studentAttempt->id,
            'section_type' => $section
        ]);

        // Redirect to appropriate section controller
        switch ($section) {
            case 'listening':
                return redirect()->route('student.listening.start', $testSet);
            case 'reading':
                return redirect()->route('student.reading.start', $testSet);
            case 'writing':
                return redirect()->route('student.writing.start', $testSet);
            case 'speaking':
                return redirect()->route('student.speaking.start', $testSet);
        }
    }

    /**
     * Complete section and move to next.
     */
    public function completeSection(Request $request, FullTestAttempt $fullTestAttempt)
    {
        // SECURITY FIX: Validate user owns this attempt
        if ($fullTestAttempt->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this test attempt.');
        }

        // Validate section name
        $section = $request->input('section');
        if (!in_array($section, ['listening', 'reading', 'writing', 'speaking'])) {
            return response()->json(['error' => 'Invalid section'], 400);
        }

        // SECURITY FIX: Get score ONLY from server-side calculation
        // NEVER trust client-side score to prevent score manipulation
        $sectionAttempt = $fullTestAttempt->sectionAttempts()
            ->where('section_type', $section)
            ->with('studentAttempt')
            ->first();

        $score = null;
        if ($sectionAttempt && $sectionAttempt->studentAttempt) {
            $studentAttempt = $sectionAttempt->studentAttempt;

            // Refresh to get latest data from database
            $studentAttempt->refresh();

            // Use the band_score calculated during test submission (server-side)
            if (in_array($section, ['listening', 'reading'])) {
                $score = $studentAttempt->band_score;

                \Log::info("Full test section score from StudentAttempt", [
                    'full_test_attempt_id' => $fullTestAttempt->id,
                    'section' => $section,
                    'student_attempt_id' => $studentAttempt->id,
                    'band_score' => $score,
                    'correct_answers' => $studentAttempt->correct_answers,
                    'total_questions' => $studentAttempt->total_questions
                ]);
            }
            // For writing/speaking, score comes from human evaluation later
        }

        // REMOVED: Client-side score fallback - this was causing the bug
        // where correct_answers (e.g., 5) was being used as band_score (5.0)
        // instead of the actual calculated band score (e.g., 2.5)

        // Update section score if we have one
        if ($score !== null) {
            $fullTestAttempt->updateSectionScore($section, $score);
        }
        
        // Get next section
        $nextSection = $fullTestAttempt->getNextSection();
        
        if ($nextSection) {
            return response()->json([
                'success' => true,
                'next_url' => route('student.full-test.section', [
                    'fullTestAttempt' => $fullTestAttempt->id,
                    'section' => $nextSection
                ])
            ]);
        } else {
            // All sections completed
            $fullTestAttempt->markAsCompleted();
            
            return response()->json([
                'success' => true,
                'next_url' => route('student.full-test.results', $fullTestAttempt)
            ]);
        }
    }

    /**
     * Show test results (Vue/Inertia).
     */
    public function results(FullTestAttempt $fullTestAttempt)
    {
        // Validate user owns this attempt
        if ($fullTestAttempt->user_id !== auth()->id()) {
            abort(403);
        }

        // Refresh to get latest scores from database
        $fullTestAttempt->refresh();

        // Load all related data including evaluation requests, answers, and questions
        $fullTestAttempt->load([
            'fullTest',
            'sectionAttempts.studentAttempt' => function ($query) {
                $query->with([
                    'testSet.section',
                    'testSet.questions' => function ($q) {
                        $q->orderBy('part_number')->orderBy('order_number')->with('options');
                    },
                    'answers.question.options',
                    'answers.selectedOption',
                    'humanEvaluationRequest',
                    'humanEvaluationRequest.humanEvaluation',
                    'answers.speakingRecording',
                ]);
            },
        ]);

        // Only mark as completed if ALL sections are actually done
        if (!$fullTestAttempt->isCompleted()) {
            $completedSections = $fullTestAttempt->getActuallyCompletedSections();
            $availableSections = $fullTestAttempt->fullTest->getAvailableSections();

            if (count($completedSections) >= count($availableSections)) {
                $fullTestAttempt->markAsCompleted();
                $fullTestAttempt->refresh();
            }
        }

        $user = auth()->user();
        $availableSections = $fullTestAttempt->fullTest->getAvailableSections();

        // Duration calculation
        $startTime = $fullTestAttempt->start_time;
        $endTime = $fullTestAttempt->end_time ?? $fullTestAttempt->updated_at;
        $totalSeconds = $startTime ? $startTime->diffInSeconds($endTime) : 0;
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $duration = ($hours > 0 ? "{$hours}h " : '') . "{$minutes}m";

        // Build per-section data
        $sectionsData = [];
        $effectiveScores = [];
        $scoreTypes = [];

        foreach ($availableSections as $sectionKey) {
            $scoreField = $sectionKey . '_score';
            $humanScore = $fullTestAttempt->$scoreField;
            $sectionAttempt = $fullTestAttempt->sectionAttempts
                ->firstWhere('section_type', $sectionKey);

            if (!$sectionAttempt || !$sectionAttempt->studentAttempt) {
                // Section not attempted yet
                $sectionsData[$sectionKey] = [
                    'type' => $sectionKey,
                    'attemptId' => null,
                    'bandScore' => null,
                    'aiBandScore' => null,
                    'aiEvaluated' => false,
                    'status' => 'not_started',
                ];
                $effectiveScores[$sectionKey] = null;
                $scoreTypes[$sectionKey] = null;
                continue;
            }

            $studentAttempt = $sectionAttempt->studentAttempt;
            $aiBandScore = $studentAttempt->ai_band_score;
            $aiEvaluated = (bool) $studentAttempt->ai_evaluated_at;

            // Determine effective score (human > AI fallback)
            if ($humanScore !== null && $humanScore !== '') {
                $effectiveScores[$sectionKey] = (float) $humanScore;
                $scoreTypes[$sectionKey] = 'human';
            } elseif ($aiBandScore) {
                $effectiveScores[$sectionKey] = (float) $aiBandScore;
                $scoreTypes[$sectionKey] = 'ai';
            } else {
                $effectiveScores[$sectionKey] = null;
                $scoreTypes[$sectionKey] = null;
            }

            // Human evaluation request info
            $humanEvalRequest = $studentAttempt->humanEvaluationRequest;
            $humanEvalStatus = null;
            $humanEvalRequested = false;
            $humanEvalCompleted = false;
            if ($humanEvalRequest) {
                $humanEvalRequested = true;
                $humanEvalStatus = $humanEvalRequest->status;
                $humanEvalCompleted = $humanEvalRequest->status === 'completed';
            }

            // Build section-specific data
            if (in_array($sectionKey, ['listening', 'reading'])) {
                // H19: only reveal the per-question answer key once THIS section has been submitted.
                // buildQuestionsAnalysis() embeds correct answers + explanations into the Inertia props,
                // so opening the full-test results mid-test would otherwise leak the key for the section
                // the student is still working on. The Vue already hides the detail block unless
                // status === 'completed'; this makes sure the key never reaches the browser at all.
                $sectionCompleted = $studentAttempt->status === 'completed';

                // Get questions and process through trait
                $questions = $studentAttempt->testSet->questions
                    ->where('question_type', '!=', 'passage');

                $formattedQuestions = [];
                if ($sectionCompleted) {
                    $questionsAnalysis = $this->buildQuestionsAnalysis($questions, $studentAttempt);
                    $formattedQuestions = $this->formatQuestionsForVue($questionsAnalysis);
                }

                // Use DB values first, fallback to trait calculation
                $totalQuestions = $studentAttempt->total_questions ?? $this->calculateTotalQuestions($questions);
                $answeredQuestions = $studentAttempt->answered_questions ?? 0;
                $correctAnswers = $studentAttempt->correct_answers ?? 0;

                // Fallback for old records without stored stats (completed sections only)
                if ($sectionCompleted && $answeredQuestions == 0 && $correctAnswers == 0 && count($formattedQuestions) > 0) {
                    $stats = $this->calculateAnswersAndCorrections($questions, $studentAttempt);
                    $answeredQuestions = $stats['attempted'];
                    $correctAnswers = $stats['correct'];
                }

                $sectionsData[$sectionKey] = [
                    'type' => $sectionKey,
                    'attemptId' => $studentAttempt->id,
                    'bandScore' => $studentAttempt->band_score,
                    'aiBandScore' => $aiBandScore,
                    'aiEvaluated' => $aiEvaluated,
                    'totalQuestions' => $totalQuestions,
                    'answeredQuestions' => $answeredQuestions,
                    'correctAnswers' => $correctAnswers,
                    'formattedQuestions' => $formattedQuestions,
                    'status' => $sectionCompleted ? 'completed' : $studentAttempt->status,
                ];
            } elseif ($sectionKey === 'writing') {
                $studentAnswers = $studentAttempt->answers
                    ->sortBy('question.order_number')
                    ->values()
                    ->map(fn ($answer) => [
                        'id' => $answer->id,
                        'question_text' => $answer->question->content ?? '',
                        'question_image' => $answer->question->media_path ? $answer->question->media_url : null,
                        'task_number' => $answer->question->order_number,
                        'answer_text' => $answer->answer ?? '',
                        'word_count' => $answer->answer ? str_word_count($answer->answer) : 0,
                    ])->toArray();

                $sectionsData[$sectionKey] = [
                    'type' => 'writing',
                    'attemptId' => $studentAttempt->id,
                    'bandScore' => $studentAttempt->band_score,
                    'aiBandScore' => $aiBandScore,
                    'aiEvaluated' => $aiEvaluated,
                    'hasAiFeature' => $user->hasFeature('ai_writing_evaluation'),
                    'studentAnswers' => $studentAnswers,
                    'aiEvaluation' => $this->getAiEvaluationData($studentAttempt, 'writing'),
                    'humanEvaluationRequested' => $humanEvalRequested,
                    'humanEvaluationCompleted' => $humanEvalCompleted,
                    'humanEvaluationStatus' => $humanEvalStatus,
                    'status' => 'completed',
                ];
            } elseif ($sectionKey === 'speaking') {
                $studentAnswers = $studentAttempt->answers
                    ->sortBy('question.order_number')
                    ->values()
                    ->map(function ($answer) {
                        $recording = $answer->speakingRecording;
                        return [
                            'id' => $answer->id,
                            'question_text' => $answer->question->content ?? '',
                            'part_number' => $answer->question->part_number ?? $answer->question->order_number,
                            'answer_text' => $answer->answer ?? '',
                            'recording_url' => $recording ? $recording->file_url : null,
                            'recording_mime_type' => $recording ? ($recording->mime_type ?? 'audio/webm') : null,
                        ];
                    })->toArray();

                $sectionsData[$sectionKey] = [
                    'type' => 'speaking',
                    'attemptId' => $studentAttempt->id,
                    'bandScore' => $studentAttempt->band_score,
                    'aiBandScore' => $aiBandScore,
                    'aiEvaluated' => $aiEvaluated,
                    'hasAiFeature' => $user->hasFeature('ai_speaking_evaluation'),
                    'studentAnswers' => $studentAnswers,
                    'aiEvaluation' => $this->getAiEvaluationData($studentAttempt, 'speaking'),
                    'humanEvaluationRequested' => $humanEvalRequested,
                    'humanEvaluationCompleted' => $humanEvalCompleted,
                    'humanEvaluationStatus' => $humanEvalStatus,
                    'status' => 'completed',
                ];
            }
        }

        // Overall band calculation — ALWAYS recalculate from effective scores
        // effectiveScores uses human > AI fallback, so it's always the most up-to-date
        // We can't rely on stored overall_band_score because:
        // 1. markAsCompleted() may have stored a partial average (only L+R)
        // 2. AI evaluation updates ai_band_score but not FullTestAttempt section scores
        $allEffective = array_filter($effectiveScores, fn ($s) => $s !== null);
        $hasAnyAiScore = in_array('ai', $scoreTypes);
        $displayOverallBand = null;

        if (count($allEffective) > 0) {
            $avg = array_sum($allEffective) / count($allEffective);
            // Official IELTS rounding: .25 rounds UP to .5, .75 rounds UP to next whole
            $decimal = $avg - floor($avg);
            if ($decimal < 0.25) {
                $displayOverallBand = floor($avg);
            } elseif ($decimal < 0.75) {
                $displayOverallBand = floor($avg) + 0.5;
            } else {
                $displayOverallBand = ceil($avg);
            }
        }

        // Performance summary: strongest & weakest
        $performanceScores = array_filter($effectiveScores, fn ($s) => $s !== null);
        $strongest = null;
        $weakest = null;
        if (count($performanceScores) >= 2) {
            $strongest = array_search(max($performanceScores), $performanceScores);
            $weakest = array_search(min($performanceScores), $performanceScores);
            if ($strongest === $weakest) {
                $strongest = null;
                $weakest = null;
            }
        }

        // Band description
        $bandDescription = $displayOverallBand ? ScoreCalculator::getBandDescription($displayOverallBand) : null;

        // Evaluation capabilities
        $isOfflineStudent = $user->isOfflineStudent();
        $canUseAI = $user->canUseAIEvaluation();
        $canUseHuman = $user->canUseHumanEvaluation();
        $evaluationType = 'ai';
        if ($isOfflineStudent) {
            $enrollment = $user->offlineEnrollment;
            $evaluationType = $enrollment->evaluation_type ?? 'ai';
        }

        // Human evaluation feature: online → global toggle, offline → enrollment-based
        if ($isOfflineStudent) {
            $hasHumanEvaluationFeature = $user->hasFeature('human_evaluation');
        } else {
            $hasHumanEvaluationFeature = WebsiteSetting::getSettings()->human_evaluation_enabled;
        }

        return Inertia::render('Student/FullTest/Results', [
            'fullTestAttempt' => [
                'id' => $fullTestAttempt->id,
                'status' => $fullTestAttempt->status,
                'created_at' => $fullTestAttempt->created_at?->toISOString(),
                'start_time' => $fullTestAttempt->start_time?->toISOString(),
                'end_time' => $fullTestAttempt->end_time?->toISOString(),
                'overall_band_score' => $fullTestAttempt->overall_band_score,
                'listening_score' => $fullTestAttempt->listening_score,
                'reading_score' => $fullTestAttempt->reading_score,
                'writing_score' => $fullTestAttempt->writing_score,
                'speaking_score' => $fullTestAttempt->speaking_score,
            ],
            'fullTest' => [
                'id' => $fullTestAttempt->fullTest->id,
                'title' => $fullTestAttempt->fullTest->title,
                'is_premium' => (bool) $fullTestAttempt->fullTest->is_premium,
            ],
            'availableSections' => $availableSections,
            'sectionsData' => $sectionsData,
            'duration' => $duration,
            'completedSectionsCount' => $fullTestAttempt->sectionAttempts->count(),
            'effectiveScores' => $effectiveScores,
            'scoreTypes' => $scoreTypes,
            'displayOverallBand' => $displayOverallBand ? (float) $displayOverallBand : null,
            'hasAnyAiScore' => $hasAnyAiScore,
            'bandDescription' => $bandDescription,
            'strongest' => $strongest,
            'weakest' => $weakest,
            'isOfflineStudent' => $isOfflineStudent,
            'canUseAI' => $canUseAI,
            'canUseHuman' => $canUseHuman,
            'evaluationType' => $evaluationType,
            'hasHumanEvaluationFeature' => (bool) $hasHumanEvaluationFeature,
        ]);
    }

    /**
     * Show detailed evaluation results for full test.
     */
    public function evaluationDetails(FullTestAttempt $fullTestAttempt)
    {
        // Validate user owns this attempt
        if ($fullTestAttempt->user_id !== auth()->id()) {
            abort(403);
        }

        // Load all necessary relationships
        $fullTestAttempt->load([
            'fullTest',
            'sectionAttempts.studentAttempt' => function($query) {
                $query->with([
                    'testSet.section',
                    'answers.question',
                    'answers.selectedOption',
                    'answers.speakingRecording',
                    'humanEvaluationRequest.humanEvaluation.errorMarkings'
                ]);
            }
        ]);

        return view('student.full-test.evaluation-details', compact('fullTestAttempt'));
    }

    /**
     * Abandon test.
     */
    public function abandon(FullTestAttempt $fullTestAttempt)
    {
        // Validate user owns this attempt
        if ($fullTestAttempt->user_id !== auth()->id()) {
            abort(403);
        }

        $fullTestAttempt->update([
            'status' => 'abandoned',
            'end_time' => now()
        ]);

        return redirect()->route('student.full-test.index')
            ->with('info', 'Test has been abandoned. You can start a new attempt anytime.');
    }

    /**
     * Show teacher selection page for full test evaluation.
     */
    public function requestEvaluation(FullTestAttempt $fullTestAttempt)
    {
        // Validate user owns this attempt
        if ($fullTestAttempt->user_id !== auth()->id()) {
            abort(403);
        }

        // Load section attempts with student attempts and evaluation requests
        $fullTestAttempt->load(['sectionAttempts.studentAttempt.humanEvaluationRequest', 'fullTest']);

        // Get writing and speaking sections that need evaluation
        $sectionsNeedingEvaluation = [];
        foreach ($fullTestAttempt->sectionAttempts as $sectionAttempt) {
            if (in_array($sectionAttempt->section_type, ['writing', 'speaking'])) {
                // Check if already requested
                if (!$sectionAttempt->studentAttempt->humanEvaluationRequest) {
                    $sectionsNeedingEvaluation[] = [
                        'type' => $sectionAttempt->section_type,
                        'student_attempt' => $sectionAttempt->studentAttempt
                    ];
                }
            }
        }

        if (empty($sectionsNeedingEvaluation)) {
            return redirect()->route('student.full-test.results', $fullTestAttempt)
                ->with('info', 'Evaluation already requested for all sections.');
        }

        // Get available teachers who can evaluate writing or speaking
        $teachers = \App\Models\Teacher::with('user')
            ->where('is_available', true)
            ->get()
            ->filter(function ($teacher) {
                $specializations = $teacher->specialization ?? [];
                return collect($specializations)->contains(function ($spec) {
                    return in_array(strtolower($spec), ['writing', 'speaking']);
                });
            })
            ->values();

        // Token system removed
        $tokenBalance = null;

        return view('student.full-test.request-evaluation', compact(
            'fullTestAttempt',
            'sectionsNeedingEvaluation',
            'teachers',
            'tokenBalance'
        ));
    }

    /**
     * Submit full test evaluation request.
     */
    public function submitEvaluationRequest(Request $request, FullTestAttempt $fullTestAttempt)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'priority' => 'required|in:normal,urgent',
            'sections' => 'required|array|min:1',
            'sections.*' => 'required|exists:student_attempts,id'
        ]);

        // Validate user owns this attempt
        if ($fullTestAttempt->user_id !== auth()->id()) {
            abort(403);
        }

        // Load section attempts
        $fullTestAttempt->load(['sectionAttempts.studentAttempt.humanEvaluationRequest', 'fullTest']);

        // Get selected sections to evaluate
        $selectedStudentAttemptIds = $request->sections;
        $sectionsNeedingEvaluation = [];

        foreach ($fullTestAttempt->sectionAttempts as $sectionAttempt) {
            // Check if this section's student attempt was selected
            if (in_array($sectionAttempt->student_attempt_id, $selectedStudentAttemptIds)) {
                // Verify it's writing or speaking and not already requested
                if (in_array($sectionAttempt->section_type, ['writing', 'speaking'])) {
                    if (!$sectionAttempt->studentAttempt->humanEvaluationRequest) {
                        $sectionsNeedingEvaluation[] = $sectionAttempt;
                    }
                }
            }
        }

        if (empty($sectionsNeedingEvaluation)) {
            return redirect()->route('student.full-test.results', $fullTestAttempt)
                ->with('info', 'Selected sections have already been requested for evaluation.');
        }

        $teacher = \App\Models\Teacher::findOrFail($request->teacher_id);

        // L44: validate the chosen teacher is available and can evaluate every selected section type.
        if (!$teacher->is_available) {
            return redirect()->back()->with('error', 'The selected teacher is not available.');
        }
        foreach ($sectionsNeedingEvaluation as $sectionAttempt) {
            if (!$teacher->canEvaluateSection($sectionAttempt->section_type)) {
                return redirect()->back()->with('error', 'The selected teacher cannot evaluate one of the selected sections.');
            }
        }

        $isPriority = $request->priority === 'urgent';

        DB::beginTransaction();

        try {
            // Create evaluation requests for each section (token system removed)
            foreach ($sectionsNeedingEvaluation as $sectionAttempt) {
                $studentAttempt = $sectionAttempt->studentAttempt;

                \App\Models\HumanEvaluationRequest::create([
                    'student_attempt_id' => $studentAttempt->id,
                    'student_id' => auth()->id(),
                    'teacher_id' => $teacher->id,
                    'tokens_used' => 0,
                    'is_offline_request' => auth()->user()->isOfflineStudent(),
                    'status' => 'assigned',
                    'priority' => $isPriority ? 'urgent' : 'normal',
                    'requested_at' => now(),
                    'assigned_at' => now(),
                    'deadline_at' => now()->addHours($isPriority ? 12 : 48)
                ]);
            }

            DB::commit();

            // Send notification to teacher
            try {
                $teacher->user->notify(new \App\Notifications\NewEvaluationRequest(
                    \App\Models\HumanEvaluationRequest::where('teacher_id', $teacher->id)
                        ->whereIn('student_attempt_id', collect($sectionsNeedingEvaluation)->pluck('student_attempt_id'))
                        ->latest()
                        ->first()
                ));
            } catch (\Exception $e) {
                \Log::error('Failed to send notification to teacher', [
                    'teacher_id' => $teacher->id,
                    'error' => $e->getMessage()
                ]);
            }

            $sectionCount = count($sectionsNeedingEvaluation);
            $sectionNames = collect($sectionsNeedingEvaluation)->pluck('section_type')->map(function($type) {
                return ucfirst($type);
            })->join(' and ');

            return redirect()->route('student.full-test.results', $fullTestAttempt)
                ->with('success', "Evaluation request submitted successfully! Your {$sectionNames} " .
                       ($sectionCount > 1 ? 'sections are' : 'section is') . " now being evaluated.");

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Failed to submit full test evaluation request', [
                'error' => $e->getMessage(),
                'full_test_attempt_id' => $fullTestAttempt->id
            ]);

            return redirect()->back()
                ->with('error', 'Failed to submit evaluation request. Please try again.');
        }
    }
    
    /**
     * Show section completed screen.
     */
    public function sectionCompleted(FullTestAttempt $fullTestAttempt, string $section)
    {
        // Validate user owns this attempt
        if ($fullTestAttempt->user_id !== auth()->id()) {
            abort(403);
        }

        // Validate section name
        if (!in_array($section, ['listening', 'reading', 'writing', 'speaking'])) {
            abort(404);
        }

        // Refresh to get latest scores from database
        $fullTestAttempt->refresh();

        // Store completed section name
        $completedSection = $section;

        // Load full test with sections and student attempts
        $fullTestAttempt->load(['fullTest', 'sectionAttempts.studentAttempt']);

        // Get section score - try FullTestAttempt first, then StudentAttempt as fallback
        $sectionScore = null;
        if (in_array($completedSection, ['listening', 'reading'])) {
            $scoreField = $completedSection . '_score';
            $sectionScore = $fullTestAttempt->$scoreField;

            // If FullTestAttempt doesn't have the score yet, get it from StudentAttempt
            if ($sectionScore === null) {
                $sectionAttempt = $fullTestAttempt->sectionAttempts
                    ->where('section_type', $completedSection)
                    ->first();

                if ($sectionAttempt && $sectionAttempt->studentAttempt) {
                    $sectionScore = $sectionAttempt->studentAttempt->band_score;

                    // Also update the FullTestAttempt with this score
                    if ($sectionScore !== null) {
                        $fullTestAttempt->updateSectionScore($completedSection, $sectionScore);
                        $fullTestAttempt->refresh();
                    }
                }
            }

            \Log::info("Section completed - {$completedSection} score: " . ($sectionScore ?? 'null'), [
                'full_test_attempt_id' => $fullTestAttempt->id,
                'full_test_score' => $fullTestAttempt->$scoreField,
            ]);
        }
        
        // Get available sections and truly completed sections
        $availableSections = $fullTestAttempt->fullTest->getAvailableSections();
        $completedSectionsList = $fullTestAttempt->getActuallyCompletedSections();
        
        // Calculate progress
        $completedSections = count($completedSectionsList);
        $totalSections = count($availableSections);
        $progressPercentage = $totalSections > 0 ? round(($completedSections / $totalSections) * 100) : 0;
        
        // Get next section
        $nextSection = $fullTestAttempt->getNextSection();
        $hasNextSection = $nextSection !== null;
        
        // Full test attempt ID
        $fullTestAttemptId = $fullTestAttempt->id;
        
        return view('student.full-test.section-completed', compact(
            'fullTestAttempt',
            'completedSection',
            'sectionScore',
            'availableSections',
            'completedSectionsList',
            'completedSections',
            'totalSections',
            'progressPercentage',
            'nextSection',
            'hasNextSection',
            'fullTestAttemptId'
        ));
    }
}
