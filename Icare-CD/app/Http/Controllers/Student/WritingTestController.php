<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Events\TestCompleted;
use App\Exceptions\InvalidAttemptException;
use App\Exceptions\TestAccessDeniedException;
use App\Exceptions\InsufficientQuestionsException;
use App\Models\Question;
use App\Models\StudentAttempt;
use App\Models\StudentAnswer;
use App\Models\TestSet;
use App\Models\HumanEvaluationRequest;
use App\Services\TestAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WritingTestController extends Controller
{
    protected TestAccessService $testAccess;

    public function __construct(TestAccessService $testAccess)
    {
        $this->testAccess = $testAccess;
    }

    /**
     * Display a listing of the available writing tests.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Get all active categories with counts for writing section (filtered by student type)
        $categories = \App\Models\TestCategory::active()
            ->ordered()
            ->withCount(['testSets as writing_count' => function ($query) use ($user) {
                $query->whereHas('section', function ($q) {
                    $q->where('slug', 'writing')->orWhere('name', 'writing');
                })->where('active', true)
                  ->forStudentType($user);
            }])
            ->get();

        // Get test sets query - filtered by student type
        $testSetsQuery = TestSet::whereHas('section', function ($query) {
            $query->where('name', 'writing');
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

        return view('student.test.writing.index', compact('testSets', 'categories', 'selectedCategory'));
    }
    
    /**
     * Show candidate information confirmation screen.
     */
    public function confirmDetails(TestSet $testSet)
    {
        // Check if the test belongs to writing section
        if ($testSet->section->name !== 'writing') {
            throw TestAccessDeniedException::wrongSection('writing');
        }

        // Centralized access check (student type + premium)
        $this->testAccess->assertCanAccessSectionTest(auth()->user(), $testSet, 'writing');

        // Get all attempts for this test
        $attempts = StudentAttempt::getAllAttemptsForUserAndTest(auth()->id(), $testSet->id);

        // Show previous attempts if any exist
        $latestAttempt = $attempts->first();
        $canRetake = $latestAttempt && $latestAttempt->canRetake();

        return view('student.test.writing.onboarding.confirm-details', compact('testSet', 'attempts', 'canRetake'));
    }

    /**
     * Show the instructions screen.
     */
    public function instructions(TestSet $testSet): View
    {
        // Check if the test belongs to writing section
        if ($testSet->section->name !== 'writing') {
            throw TestAccessDeniedException::wrongSection('writing');
        }

        return view('student.test.writing.onboarding.instructions', compact('testSet'));
    }
    
    /**
     * Start a new writing test.
     */
    public function start(TestSet $testSet)
    {
        // Eager load section to avoid extra query
        $testSet->load('section');

        // Check if the test belongs to writing section
        if ($testSet->section->name !== 'writing') {
            throw TestAccessDeniedException::wrongSection('writing');
        }

        // Detect if this is being called as part of a Full Test flow.
        // FullTestController::section() already: (1) validated access, (2) created StudentAttempt,
        // (3) linked it via FullTestSectionAttempt, and (4) consumed full test quota.
        // In that case we must NOT re-check section test access (which would wrongly check
        // section quota/premium for standalone section tests) and NOT consume section quota.
        $isPartOfFullTest = StudentAttempt::where('user_id', auth()->id())
            ->where('test_set_id', $testSet->id)
            ->where('status', 'in_progress')
            ->whereHas('fullTestSectionAttempt')
            ->exists();

        // Only check section test access for standalone section tests (NOT full test sections)
        if (!$isPartOfFullTest) {
            $this->testAccess->assertCanAccessSectionTest(auth()->user(), $testSet, 'writing');
        }

        // Determine if this is a single-task writing test set
        $isSingleTask = $testSet->isSingleTaskWriting();

        if ($isSingleTask) {
            // Single-task mode: load 1 question matching the task type
            $taskNumber = $testSet->getWritingTaskNumber();
            $questions = $testSet->questions()
                ->where(function ($query) use ($taskNumber) {
                    $query->where('part_number', $taskNumber)
                          ->orWhere('question_type', 'like', "task{$taskNumber}_%");
                })
                ->orderBy('order_number')
                ->limit(1)
                ->get();

            // Fallback: get any question from the test set
            if ($questions->isEmpty()) {
                $questions = $testSet->questions()->orderBy('order_number')->limit(1)->get();
            }

            if ($questions->isEmpty()) {
                throw InsufficientQuestionsException::writingTest(0, 1);
            }
        } else {
            // Legacy 2-task mode
            $questions = $testSet->questions()
                ->whereIn('part_number', [1, 2])
                ->orderBy('part_number')
                ->orderBy('order_number')
                ->get();

            if ($questions->count() < 2) {
                $questions = $testSet->questions()
                    ->orderBy('order_number')
                    ->limit(2)
                    ->get();
            }

            if ($questions->count() < 2) {
                throw InsufficientQuestionsException::writingTest($questions->count(), 2);
            }
        }

        // Ensure Vue receives a valid media_url from local public storage.
        $questions->each(function ($q) {
            $rawUrl = $q->getAttributes()['media_url'] ?? null;
            if ($rawUrl && preg_match('#^https?://#i', $rawUrl)) {
                $q->media_url = $rawUrl;
                return;
            }
            $q->media_url = $q->media_path
                ? asset('storage/' . ltrim($q->media_path, '/'))
                : null;
        });

        // Ensure the questions relation is set on testSet so it serializes properly to Inertia
        $testSet->setRelation('questions', $questions);

        // Check if fresh start requested (abandon any in_progress attempt)
        // But NOT if this is part of a full test (FullTestController already created the attempt)
        if (!$isPartOfFullTest && request()->has('fresh')) {
            StudentAttempt::where('user_id', auth()->id())
                ->where('test_set_id', $testSet->id)
                ->where('status', 'in_progress')
                ->update(['status' => 'abandoned']);
        }

        // Check if there's an ongoing attempt - get the latest one to avoid stale start_time issues
        $attempt = StudentAttempt::where('user_id', auth()->id())
            ->where('test_set_id', $testSet->id)
            ->where('status', 'in_progress')
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
                app(TestAccessService::class)->consumeSectionTestQuota(auth()->user(), 'writing', $skipQuota);
            }

            // Pre-create answer records for all questions in this test
            foreach ($questions as $question) {
                StudentAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'answer' => '',
                ]);
            }
        }
        
        // Load attempt with answers
        $attempt->load('answers');

        // Auto-finalize if the existing in-progress attempt's time has already expired
        // (student left without submitting and the time limit has since passed).
        if ($attempt->start_time) {
            $timeLimitMin = $testSet->getEffectiveTimeLimitMinutes();
            $expiresAt = \Carbon\Carbon::parse($attempt->start_time)->addMinutes($timeLimitMin);

            if (now()->greaterThan($expiresAt)) {
                $savedAnswers = [];
                foreach ($attempt->answers as $ans) {
                    $savedAnswers[$ans->question_id] = (string) ($ans->answer ?? '');
                }
                if (empty($savedAnswers)) {
                    $savedAnswers = ['__empty' => ''];
                }
                $autoRequest = Request::create('', 'POST', [
                    'answers' => $savedAnswers,
                    'auto_submit' => 1,
                ]);
                $autoRequest->setUserResolver(fn () => auth()->user());
                return $this->submit($autoRequest, $attempt);
            }
        }

        // Format initial answers for Vue: { question_id: 'answer string' }
        $initialAnswers = [];
        foreach ($attempt->answers as $ans) {
            $initialAnswers[$ans->question_id] = [
                'answer' => $ans->answer
            ];
        }

        \Log::info('Returning Inertia view for Writing Test');
        
        return \Inertia\Inertia::render('Test/Writing/Show', [
            'testSet' => $testSet,
            'attempt' => $attempt,
            'initialAnswers' => $initialAnswers,
            'timeLimitSeconds' => $testSet->getEffectiveTimeLimitMinutes() * 60,
            'serverTime' => now()->toIso8601String(),
            'isSingleTask' => $isSingleTask,
            'taskNumber' => $testSet->getWritingTaskNumber(),
        ]);
    }
    
    /**
     * Auto-save the writing test answers (Bulk).
     */
    public function autosave(Request $request, StudentAttempt $attempt): JsonResponse
    {
        // Verify the attempt belongs to the current user and is not completed
        if ($attempt->user_id !== auth()->id() || $attempt->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Invalid attempt'], 403);
        }
        
        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'nullable|string',
        ]);
        
        foreach ($request->answers as $questionId => $content) {
            if ($content !== null) {
                StudentAnswer::updateOrCreate(
                    [
                        'attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                    ],
                    [
                        'answer' => $content,
                    ]
                );
            }
        }
        
        // Update completion rate on autosave (optional)
        $this->updateCompletionRate($attempt);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Submit the writing test answers.
     */
    public function submit(Request $request, StudentAttempt $attempt): \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        // Verify the attempt belongs to the current user
        if ($attempt->user_id !== auth()->id()) {
            throw InvalidAttemptException::notOwner();
        }

        // Check if already completed
        if ($attempt->status === 'completed') {
            throw InvalidAttemptException::alreadyCompleted();
        }
        
        // Check if this is part of a full test
        $fullTestSectionAttempt = \App\Models\FullTestSectionAttempt::where('student_attempt_id', $attempt->id)->first();
        $isPartOfFullTest = $fullTestSectionAttempt !== null;
        
        // Validate the submission
        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'nullable|string',
            'auto_submit' => 'nullable|boolean',
        ]);
        
        DB::transaction(function () use ($request, $attempt, $isPartOfFullTest, $fullTestSectionAttempt) {
            $totalQuestions = 0;
            $answeredQuestions = 0;
            
            // Save answers
            foreach ($request->answers as $questionId => $content) {
                $totalQuestions++;
                
                // Find existing answer
                $answer = StudentAnswer::where('attempt_id', $attempt->id)
                    ->where('question_id', $questionId)
                    ->first();
                
                if ($answer) {
                    $answer->update(['answer' => $content]);
                } else {
                    StudentAnswer::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                        'answer' => $content,
                    ]);
                }
                
                // Count as answered if content is not empty
                if (!empty(trim($content))) {
                    $answeredQuestions++;
                }
            }
            
            // Calculate completion rate
            $completionRate = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0;
            
            // Mark attempt as completed with proper stats
            $attempt->update([
                'end_time' => now(),
                'status' => 'completed',
                'completion_rate' => $completionRate,
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'is_complete_attempt' => ($completionRate >= 80), // Consider 80%+ as complete
            ]);

            // Dispatch TestCompleted event - handles all side effects
            // Writing needs human evaluation, so no score data yet
            $fullTestAttemptId = $isPartOfFullTest && $fullTestSectionAttempt
                ? $fullTestSectionAttempt->full_test_attempt_id
                : null;

            event(new TestCompleted(
                attempt: $attempt,
                user: auth()->user(),
                section: 'writing',
                scoreData: [], // No score yet - requires human evaluation
                isPartOfFullTest: $isPartOfFullTest,
                fullTestAttemptId: $fullTestAttemptId
            ));

            // Auto-create evaluation request for offline students based on their enrollment's evaluation_type
            // Only creates request if evaluation_type is 'human' or 'both' (not 'ai' only)
            $user = auth()->user();
            if ($user->isOfflineStudent()) {
                HumanEvaluationRequest::createForOfflineStudentIfNeeded($attempt, $user, 'writing');
            }
        });
        
        // If part of full test, redirect to section completed screen
        if ($isPartOfFullTest && $fullTestSectionAttempt) {
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
                'section' => 'writing'
            ]);

            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::location($url);
            }
            return redirect($url)->with('success', 'Writing section completed successfully!');
        }
        
        // Regular test completion
        $url = route('student.results.show', $attempt);
        
        if ($request->header('X-Inertia')) {
            return \Inertia\Inertia::location($url);
        }
        return redirect($url)->with('success', 'Test submitted successfully!');
    }
    
    /**
     * Update completion rate for an attempt
     */
    private function updateCompletionRate(StudentAttempt $attempt)
    {
        $totalQuestions = $attempt->testSet->questions()->count();
        $answeredQuestions = 0;
        
        // Get all answers for this attempt
        $answers = $attempt->answers()->with('question')->get();
        
        foreach ($answers as $answer) {
            // Count as answered if has content
            if (!empty(trim($answer->answer))) {
                $answeredQuestions++;
            }
        }
        
        $completionRate = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0;
        
        $attempt->update([
            'completion_rate' => $completionRate,
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
        ]);
    }

    /**
     * Auto-save draft answers to server (called via AJAX every 30 seconds)
     */
    public function saveDraft(Request $request, StudentAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($attempt->status !== 'in_progress') {
            return response()->json(['error' => 'Attempt already completed'], 400);
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