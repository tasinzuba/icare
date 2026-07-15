<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Events\TestCompleted;
use App\Exceptions\InvalidAttemptException;
use App\Exceptions\TestAccessDeniedException;
use App\Models\Question;
use App\Models\SpeakingRecording;
use App\Models\StudentAttempt;
use App\Models\StudentAnswer;
use App\Models\TestSet;
use App\Models\HumanEvaluationRequest;
use App\Services\TestAccessService;
use App\Traits\EnforcesTestTimeLimit;
use App\Traits\HandlesFileUploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SpeakingTestController extends Controller
{
    use EnforcesTestTimeLimit;
    use HandlesFileUploads;

    protected TestAccessService $testAccess;

    public function __construct(TestAccessService $testAccess)
    {
        $this->testAccess = $testAccess;
    }

    /**
     * Display a listing of the available speaking tests.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Get all active categories with counts for speaking section (filtered by student type)
        $categories = \App\Models\TestCategory::active()
            ->ordered()
            ->withCount(['testSets as speaking_count' => function ($query) use ($user) {
                $query->whereHas('section', function ($q) {
                    $q->where('slug', 'speaking')->orWhere('name', 'speaking');
                })->where('active', true)
                  ->forStudentType($user);
            }])
            ->get();

        // Get test sets query - filtered by student type
        $testSetsQuery = TestSet::whereHas('section', function ($query) {
            $query->where('name', 'speaking');
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

        return view('student.test.speaking.index', compact('testSets', 'categories', 'selectedCategory'));
    }
    
    /**
     * Show candidate information confirmation screen.
     */
    public function confirmDetails(TestSet $testSet)
    {
        // Check if the test belongs to speaking section
        if ($testSet->section->name !== 'speaking') {
            throw TestAccessDeniedException::wrongSection('speaking');
        }

        // Centralized access check (student type + premium)
        $this->testAccess->assertCanAccessSectionTest(auth()->user(), $testSet, 'speaking');

        // Get all attempts for this test
        $attempts = StudentAttempt::getAllAttemptsForUserAndTest(auth()->id(), $testSet->id);
        
        // Show previous attempts if any exist
        $latestAttempt = $attempts->first();
        $canRetake = $latestAttempt && $latestAttempt->canRetake();
        
        return view('student.test.speaking.onboarding.confirm-details', compact('testSet', 'attempts', 'canRetake'));
    }

    /**
     * Show the microphone check screen.
     */
    public function microphoneCheck(TestSet $testSet): View
    {
        // Check if the test belongs to speaking section
        if ($testSet->section->name !== 'speaking') {
            throw TestAccessDeniedException::wrongSection('speaking');
        }

        return view('student.test.speaking.onboarding.microphone-check', compact('testSet'));
    }

    /**
     * Show the instructions screen.
     */
    public function instructions(TestSet $testSet): View
    {
        // Check if the test belongs to speaking section
        if ($testSet->section->name !== 'speaking') {
            throw TestAccessDeniedException::wrongSection('speaking');
        }

        return view('student.test.speaking.onboarding.instructions', compact('testSet'));
    }
    
    /**
     * Start a new speaking test.
     */
    public function start(TestSet $testSet)
    {
        // Eager load all required relations to avoid N+1 queries
        $testSet->load([
            'section',
            'questions' => function ($query) {
                $query->orderBy('part_number')->orderBy('order_number');
            },
        ]);

        // Check if the test belongs to speaking section
        if ($testSet->section->name !== 'speaking') {
            throw TestAccessDeniedException::wrongSection('speaking');
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
            $this->testAccess->assertCanAccessSectionTest(auth()->user(), $testSet, 'speaking');
        }

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
                app(TestAccessService::class)->consumeSectionTestQuota(auth()->user(), 'speaking', $skipQuota);
            }

            // Pre-create answer records - using already loaded questions
            foreach ($testSet->questions as $question) {
                StudentAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                ]);
            }
        }

        // Load attempt with answers
        $attempt->load('answers.speakingRecording');

        // Auto-finalize if the existing in-progress attempt's time has already expired
        // (student left without submitting and the time limit has since passed).
        if ($attempt->start_time) {
            $timeLimitMin = $testSet->section->time_limit ?? 15;
            $expiresAt = \Carbon\Carbon::parse($attempt->start_time)->addMinutes($timeLimitMin);

            if (now()->greaterThan($expiresAt)) {
                $autoRequest = Request::create('', 'POST', [
                    'auto_submit' => 1,
                ]);
                $autoRequest->setUserResolver(fn () => auth()->user());
                return $this->submit($autoRequest, $attempt);
            }
        }

        \Log::info('Returning Inertia view for Speaking Test');

        return \Inertia\Inertia::render('Test/Speaking/Show', [
            'testSet' => $testSet,
            'attempt' => $attempt,
            'timeLimitSeconds' => $testSet->section->time_limit * 60,
            'serverTime' => now()->toIso8601String(),
        ]);
    }
    
    /**
     * Save the recorded audio.
     */
    public function record(Request $request, StudentAttempt $attempt, Question $question): JsonResponse
    {
        // Verify the attempt belongs to the current user and is not completed
        if ($attempt->user_id !== auth()->id() || $attempt->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Invalid attempt']);
        }
        
        $request->validate([
            'recording' => 'required|file|mimes:audio/mpeg,mpga,mp3,wav,webm|max:51200', // 50MB max
        ]);
        
        // Find or create answer for this question (updateOrCreate prevents race condition)
        $answer = StudentAnswer::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ],
            [
                'updated_at' => now(),
            ]
        );
        
        try {
            // Check if there's an existing recording
            if ($answer->speakingRecording) {
                // Delete old recording
                $this->deleteFile(
                    $answer->speakingRecording->file_path,
                    $answer->speakingRecording->storage_disk
                );
                $answer->speakingRecording->delete();
            }

            // Upload recording using trait (to R2 if configured)
            $result = $this->uploadFile(
                $request->file('recording'),
                'speaking-recordings/attempt-' . $attempt->id
            );

            if (!$result['success']) {
                \Log::error('Speaking recording upload failed', [
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload recording. Please try again.'
                ]);
            }

            // Create a new recording with CDN URL
            SpeakingRecording::create([
                'answer_id' => $answer->id,
                'file_path' => $result['path'],
                'file_url' => $result['url'],
                'storage_disk' => $result['disk'],
                'file_size' => $result['size'],
                'mime_type' => $result['mime_type']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Recording saved successfully',
                'storage' => strtoupper($result['disk']),
                'url' => $result['url']
            ]);

        } catch (\Exception $e) {
            \Log::error('Speaking recording exception', [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving your recording. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Submit the speaking test.
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
        
        // Calculate completion rate
        $totalQuestions = $attempt->testSet->questions()->count();
        $recordedAnswers = $attempt->answers()
            ->whereHas('speakingRecording')
            ->count();
        
        $completionRate = $totalQuestions > 0 ? round(($recordedAnswers / $totalQuestions) * 100, 2) : 0;
        
        // Mark attempt as completed.
        // H17: speaking is ACCEPT-and-FLAG — record()/autoSave() are never rejected (a legitimately
        // recorded answer can arrive late on a slow upload and must not be discarded), so we only
        // record the overtime flag + non-negative timing here.
        $attempt->update([
            'end_time' => now(),
            'status' => 'completed',
            'completion_rate' => $completionRate,
            'total_questions' => $totalQuestions,
            'answered_questions' => $recordedAnswers,
            'is_complete_attempt' => ($completionRate >= 80),
            'band_score' => null, // Speaking scores are set when teacher evaluates
            'is_overtime' => $this->isTimeExceeded($attempt, 'speaking'),
            'time_taken_minutes' => $this->elapsedMinutes($attempt),
            'allowed_minutes' => $this->resolveAllowedMinutes($attempt),
        ]);

        // Dispatch TestCompleted event - handles all side effects
        // Speaking needs human evaluation, so no score data yet
        $fullTestAttemptId = $isPartOfFullTest && $fullTestSectionAttempt
            ? $fullTestSectionAttempt->full_test_attempt_id
            : null;

        event(new TestCompleted(
            attempt: $attempt,
            user: auth()->user(),
            section: 'speaking',
            scoreData: [], // No score yet - requires human evaluation
            isPartOfFullTest: $isPartOfFullTest,
            fullTestAttemptId: $fullTestAttemptId
        ));

        // Auto-create evaluation request for offline students based on their enrollment's evaluation_type
        // Only creates request if evaluation_type is 'human' or 'both' (not 'ai' only)
        $user = auth()->user();
        if ($user->isOfflineStudent()) {
            HumanEvaluationRequest::createForOfflineStudentIfNeeded($attempt, $user, 'speaking');
        }

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
                'section' => 'speaking'
            ]);

            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::location($url);
            }
            return redirect($url)->with('success', 'Speaking section completed successfully!');
        }

        // Regular test completion
        $url = route('student.results.show', $attempt);
        
        if ($request->header('X-Inertia')) {
            return \Inertia\Inertia::location($url);
        }
        return redirect($url)->with('success', 'Test submitted successfully!');
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