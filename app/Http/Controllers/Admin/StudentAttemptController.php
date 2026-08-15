<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ResultDataTrait;
use App\Models\StudentAttempt;
use App\Models\User;
use App\Services\AnswerValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentAttemptController extends Controller
{
    use ResultDataTrait;

    /**
     * Required by ResultDataTrait, which reuses the same answer-checking rules the
     * student result pages use.
     */
    protected function getAnswerValidator(): AnswerValidator
    {
        return app(AnswerValidator::class);
    }

    /**
     * Display a listing of the student attempts.
     */
    public function index(Request $request): View
    {
        // SECURITY (H5): attempts routes share an OR permission group; enforce the specific
        // capability per action so attempts.view alone cannot delete/evaluate attempts.
        abort_unless(auth()->user()->hasPermission('attempts.view'), 403);

        $query = StudentAttempt::with(['user', 'testSet', 'testSet.section']);
        
        // Filter by section - Fixed to properly handle the filter
        if ($request->filled('section') && $request->section !== '') {
            $query->whereHas('testSet.section', function ($q) use ($request) {
                $q->where('name', $request->section);
            });
        }
        
        // Filter by status - Fixed to properly handle the filter
        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        // Filter by user - Fixed to properly handle the filter
        if ($request->filled('user') && $request->user !== '') {
            $query->where('user_id', $request->user);
        }

        // Filter by test type: part of a full test vs a standalone single-section attempt.
        // (Replaces the old premium/free filter, which no longer means anything now that
        // every test is free.) A full-test section attempt is linked via fullTestSectionAttempt.
        if ($request->filled('test_type') && $request->test_type !== '') {
            if ($request->test_type === 'full') {
                $query->whereHas('fullTestSectionAttempt');
            } elseif ($request->test_type === 'single') {
                $query->whereDoesntHave('fullTestSectionAttempt');
            }
        }

        // Calculate statistics
        $totalAttempts = StudentAttempt::count();
        $completedCount = StudentAttempt::where('status', 'completed')->count();
        $inProgressCount = StudentAttempt::where('status', 'in_progress')->count();
        $needsEvaluationCount = StudentAttempt::where('status', 'completed')
            ->whereNull('band_score')
            ->whereHas('testSet.section', function ($q) {
                $q->whereIn('name', ['writing', 'speaking']);
            })
            ->count();
        
        $attempts = $query->latest()->paginate(15)->withQueryString();

        // Get users for filtering
        $users = User::where('is_admin', false)
            ->orderBy('name')
            ->get();

        return view('admin.attempts.index', compact(
            'attempts',
            'users',
            'totalAttempts',
            'completedCount',
            'inProgressCount',
            'needsEvaluationCount'
        ));
    }

    /**
     * Display the specified student attempt.
     */
    public function show(StudentAttempt $attempt): View
    {
        abort_unless(auth()->user()->hasPermission('attempts.view'), 403);

        $attempt->load(['user', 'testSet', 'testSet.section', 'answers', 'answers.question', 'answers.selectedOption', 'answers.speakingRecording']);

        // Build a readable per-question breakdown for the auto-scored sections. Without this the
        // view printed the raw stored JSON (e.g. {"blank_1":"people","blank_2":"marine"}) for every
        // multi-blank/dropdown question. The trait is the same one the student result pages use, so
        // each blank, dropdown and drag zone becomes its own row with the correct answer and a
        // right/wrong verdict.
        $responses = null;
        if (in_array(optional($attempt->testSet->section)->name, ['reading', 'listening'], true)) {
            $questions = $attempt->testSet->questions()
                ->with('options')
                ->where('question_type', '!=', 'passage')
                ->orderBy('part_number')
                ->orderBy('order_number')
                ->get();

            $responses = $this->formatQuestionsForVue(
                $this->buildQuestionsAnalysis($questions, $attempt)
            );
        }

        return view('admin.attempts.show', compact('attempt', 'responses'));
    }

    /**
     * Show the form for evaluating a student attempt.
     */
    public function evaluateForm(StudentAttempt $attempt): View
    {
        abort_unless(auth()->user()->hasPermission('attempts.evaluate'), 403);

        $attempt->load(['user', 'testSet', 'testSet.section', 'answers', 'answers.question', 'answers.selectedOption', 'answers.speakingRecording']);

        return view('admin.attempts.evaluate', compact('attempt'));
    }

    /**
     * Process the evaluation of a student attempt.
     */
    public function evaluate(Request $request, StudentAttempt $attempt): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('attempts.evaluate'), 403);

        $request->validate([
            'band_score' => 'required|numeric|min:0|max:9',
            'feedback' => 'nullable|string',
        ]);
        
        $attempt->update([
            'band_score' => $request->band_score,
            'feedback' => $request->feedback,
        ]);
        
        return redirect()->route('admin.attempts.show', $attempt)
            ->with('success', 'Attempt evaluated successfully.');
    }

    /**
     * Bulk delete student attempts.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('attempts.delete'), 403);

        $request->validate([
            'attempt_ids' => 'required|array',
            'attempt_ids.*' => 'exists:student_attempts,id'
        ]);
        
        $deletedCount = 0;
        $failedCount = 0;
        
        foreach ($request->attempt_ids as $attemptId) {
            try {
                $attempt = StudentAttempt::find($attemptId);
                if ($attempt) {
                    // Delete related answers
                    $attempt->answers()->delete();
                    // Delete the attempt
                    $attempt->delete();
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                $failedCount++;
                \Log::error('Failed to delete attempt: ' . $attemptId, ['error' => $e->getMessage()]);
            }
        }
        
        $message = "Successfully deleted {$deletedCount} attempt(s).";
        if ($failedCount > 0) {
            $message .= " Failed to delete {$failedCount} attempt(s).";
            return back()->with('warning', $message);
        }
        
        return redirect()->route('admin.attempts.index')
            ->with('success', $message);
    }
    
    /**
     * Remove the specified student attempt from storage.
     */
    public function destroy(StudentAttempt $attempt): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('attempts.delete'), 403);

        $attempt->delete();
        
        return redirect()->route('admin.attempts.index')
            ->with('success', 'Attempt deleted successfully.');
    }
}