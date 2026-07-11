<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\StudentAttempt;
use App\Models\HumanEvaluationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HumanEvaluationController extends Controller
{
    /**
     * Show available teachers for evaluation
     */
    public function showTeachers(StudentAttempt $attempt)
    {
        try {
            // Check if attempt belongs to user
            if ($attempt->user_id !== auth()->id()) {
                if (request()->ajax()) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                abort(403);
            }

            // Check if offline student can use human evaluation
            $user = auth()->user();
            if ($user->isOfflineStudent() && !$user->canUseHumanEvaluation()) {
                if (request()->ajax()) {
                    return response()->json(['error' => 'Human evaluation is not enabled for your enrollment.'], 403);
                }
                return redirect()->back()->with('error', 'Human evaluation is not enabled for your enrollment. Please contact your branch administrator.');
            }

            // Check if human evaluation already requested
            $existingRequest = HumanEvaluationRequest::where('student_attempt_id', $attempt->id)->first();
            if ($existingRequest) {
                if (request()->ajax()) {
                    return response()->json(['error' => 'Evaluation already requested', 'redirect' => route('student.evaluation.status', $attempt->id)], 400);
                }
                return redirect()->route('student.evaluation.status', $attempt->id);
            }

            // Get section name
            $section = $attempt->testSet->section->name;

            // Get available teachers for this section
            $teachers = Teacher::with('user')
                ->where('is_available', true)
                ->get()
                ->filter(function ($teacher) use ($section) {
                    $specializations = $teacher->specialization ?? [];
                    return collect($specializations)->contains(function ($spec) use ($section) {
                        return strcasecmp($spec, $section) === 0;
                    });
                })
                ->values();

            // Token system removed
            $tokenBalance = null;

            // If AJAX request, return only the teacher cards
            if (request()->ajax()) {
                return view('student.evaluation.partials.teacher-cards', compact(
                    'teachers',
                    'tokenBalance',
                    'section'
                ));
            }

            return view('student.evaluation.select-teacher', compact(
                'attempt',
                'teachers',
                'tokenBalance',
                'section'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading teachers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt_id' => $attempt->id ?? null
            ]);

            if (request()->ajax()) {
                return response()->json(['error' => 'Failed to load teachers'], 500);
            }

            return back()->with('error', 'Failed to load teachers. Please try again.');
        }
    }

    /**
     * Request human evaluation
     */
    public function requestEvaluation(Request $request, StudentAttempt $attempt)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'priority' => 'required|in:normal,urgent'
        ]);

        // Check ownership
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if offline student can use human evaluation
        $user = auth()->user();
        if ($user->isOfflineStudent() && !$user->canUseHumanEvaluation()) {
            return redirect()->back()->with('error', 'Human evaluation is not enabled for your enrollment. Please contact your branch administrator.');
        }

        // Check if already requested
        if (HumanEvaluationRequest::where('student_attempt_id', $attempt->id)->exists()) {
            return redirect()->back()->with('error', 'Evaluation already requested for this attempt.');
        }

        $teacher = Teacher::findOrFail($request->teacher_id);

        // M35: the student can POST any teacher_id directly, bypassing the filtered list in
        // showTeachers(). Re-validate the chosen teacher is available and specializes in this section.
        $section = $attempt->testSet->section->name;
        if (!$teacher->is_available || !$teacher->canEvaluateSection($section)) {
            return redirect()->back()->with('error', 'The selected teacher is not available for this section. Please choose another teacher.');
        }

        $isPriority = $request->priority === 'urgent';

        DB::transaction(function () use ($attempt, $teacher, $isPriority) {
            $evaluationRequest = HumanEvaluationRequest::create([
                'student_attempt_id' => $attempt->id,
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

            // Send notification to teacher
            try {
                $teacher->user->notify(new \App\Notifications\NewEvaluationRequest($evaluationRequest));
            } catch (\Exception $e) {
                Log::error('Failed to send notification to teacher', [
                    'teacher_id' => $teacher->id,
                    'error' => $e->getMessage()
                ]);
            }
        });

        return redirect()->route('student.evaluation.status', $attempt->id)
            ->with('success', 'Human evaluation requested successfully!');
    }

    /**
     * Show evaluation status
     */
    public function status(StudentAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        $evaluationRequest = HumanEvaluationRequest::with(['teacher.user', 'humanEvaluation'])
            ->where('student_attempt_id', $attempt->id)
            ->firstOrFail();

        return view('student.evaluation.status', compact('attempt', 'evaluationRequest'));
    }

    /**
     * View human evaluation result
     */
    public function viewResult(StudentAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        $evaluationRequest = HumanEvaluationRequest::with(['humanEvaluation.evaluator', 'humanEvaluation.errorMarkings'])
            ->where('student_attempt_id', $attempt->id)
            ->where('status', 'completed')
            ->firstOrFail();

        if (!$evaluationRequest->humanEvaluation) {
            return redirect()->route('student.evaluation.status', $attempt->id)
                ->with('error', 'Evaluation not yet completed.');
        }

        $evaluation = $evaluationRequest->humanEvaluation;
        $attempt->load(['testSet.section', 'answers.question']);

        return view('student.evaluation.human-result', compact('attempt', 'evaluation', 'evaluationRequest'));
    }
}
