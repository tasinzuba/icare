<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\HumanEvaluationRequest;
use App\Models\HumanEvaluation;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    /**
     * Teacher dashboard
     */
    public function dashboard()
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        // Count unassigned offline evaluations this teacher can claim
        $unassignedOfflineCount = HumanEvaluationRequest::whereNull('teacher_id')
            ->where('status', 'pending')
            ->where('is_offline_request', true)
            ->whereHas('studentAttempt.testSet.section', function ($q) use ($teacher) {
                $specializations = $teacher->specialization ?? [];
                $q->whereIn('name', $specializations);
            })
            ->count();

        // Count unassigned online evaluations this teacher can claim
        $unassignedOnlineCount = HumanEvaluationRequest::whereNull('teacher_id')
            ->where('status', 'pending')
            ->where('is_offline_request', false)
            ->whereHas('studentAttempt.testSet.section', function ($q) use ($teacher) {
                $specializations = $teacher->specialization ?? [];
                $q->whereIn('name', $specializations);
            })
            ->count();

        // Get statistics
        $stats = [
            'pending' => $teacher->evaluationRequests()->where('status', 'assigned')->count(),
            'in_progress' => $teacher->evaluationRequests()->where('status', 'in_progress')->count(),
            'completed_today' => $teacher->evaluationRequests()
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
            'total_completed' => $teacher->evaluationRequests()->where('status', 'completed')->count(),
            'earnings_this_month' => $teacher->evaluationRequests()
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->sum('tokens_used'),
            'unassigned_available' => $unassignedOfflineCount,
            'unassigned_online' => $unassignedOnlineCount
        ];

        // Get recent evaluations
        $recentEvaluations = $teacher->evaluationRequests()
            ->with(['studentAttempt.testSet.section', 'studentAttempt.fullTestSectionAttempt', 'student'])
            ->latest()
            ->take(10)
            ->get();

        return view('teacher.dashboard', compact('teacher', 'stats', 'recentEvaluations'));
    }
    
    /**
     * List of pending evaluations
     */
    public function pending(Request $request)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        // Get teacher's assigned evaluations
        $query = $teacher->evaluationRequests()
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with(['studentAttempt.testSet.section', 'studentAttempt.fullTestSectionAttempt', 'student.branch']);

        // Filter by student type
        if ($request->has('student_type') && $request->student_type !== '') {
            if ($request->student_type === 'offline') {
                $query->offline();
            } elseif ($request->student_type === 'online') {
                $query->online();
            }
        }

        $evaluations = $query->orderBy('priority', 'desc')
            ->orderBy('deadline_at', 'asc')
            ->paginate(20);

        // Get unassigned offline evaluations that this teacher can claim
        $unassignedOfflineQuery = HumanEvaluationRequest::whereNull('teacher_id')
            ->where('status', 'pending')
            ->where('is_offline_request', true)
            ->with(['studentAttempt.testSet.section', 'studentAttempt.fullTestSectionAttempt', 'student.branch'])
            ->whereHas('studentAttempt.testSet.section', function ($q) use ($teacher) {
                $specializations = $teacher->specialization ?? [];
                $q->whereIn('name', $specializations);
            });

        // Filter by branch
        if ($request->filled('branch_id')) {
            $unassignedOfflineQuery->whereHas('student', fn($q) => $q->where('branch_id', $request->branch_id));
        }

        // Filter by section
        if ($request->filled('section')) {
            $unassignedOfflineQuery->whereHas('studentAttempt.testSet.section', fn($q) => $q->where('name', $request->section));
        }

        // #3: search the offline queue by student name/email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $unassignedOfflineQuery->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort — default newest first
        $sortField = $request->input('sort', 'requested_at');
        $sortDir = $request->input('dir', 'desc');
        $allowedSorts = ['requested_at'];
        if (in_array($sortField, $allowedSorts)) {
            $unassignedOfflineQuery->orderBy($sortField, $sortDir);
        } else {
            $unassignedOfflineQuery->orderBy('requested_at', 'desc');
        }

        $unassignedEvaluations = $unassignedOfflineQuery->get();

        // Sort by branch name if requested (post-query since it's a relation)
        if ($sortField === 'branch') {
            $unassignedEvaluations = $unassignedEvaluations->sortBy(
                fn($e) => $e->student->branch->name ?? 'zzz',
                SORT_NATURAL | SORT_FLAG_CASE,
                $sortDir === 'desc'
            )->values();
        }

        // Get unassigned online evaluations that this teacher can claim
        $unassignedOnlineQuery = HumanEvaluationRequest::whereNull('teacher_id')
            ->where('status', 'pending')
            ->where('is_offline_request', false)
            ->with(['studentAttempt.testSet.section', 'studentAttempt.fullTestSectionAttempt', 'student'])
            ->whereHas('studentAttempt.testSet.section', function ($q) use ($teacher) {
                $specializations = $teacher->specialization ?? [];
                $q->whereIn('name', $specializations);
            });

        // #3: apply the same student search to the online queue.
        if ($request->filled('search')) {
            $search = $request->input('search');
            $unassignedOnlineQuery->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        }

        $unassignedOnlineEvaluations = $unassignedOnlineQuery->orderBy('requested_at', 'desc')->get();

        // Get unique branches for filter dropdown
        $branches = \App\Models\Branch::active()->ordered()->get(['id', 'name', 'code']);

        return view('teacher.evaluations.pending', compact('evaluations', 'unassignedEvaluations', 'unassignedOnlineEvaluations', 'branches'));
    }

    /**
     * Claim an unassigned evaluation (offline or online)
     */
    public function claim(HumanEvaluationRequest $evaluationRequest)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        // Verify teacher can evaluate this section (safe to read before the lock)
        $sectionName = $evaluationRequest->studentAttempt->testSet->section->name;
        if (!$teacher->canEvaluateSection($sectionName)) {
            return redirect()->back()->with('error', 'You cannot evaluate this section type.');
        }

        // M48: claim atomically — lock the row and re-check inside the transaction so two teachers
        // cannot claim the same pending request concurrently (was a check-then-act race).
        $claimed = DB::transaction(function () use ($evaluationRequest, $teacher) {
            $fresh = HumanEvaluationRequest::whereKey($evaluationRequest->getKey())->lockForUpdate()->first();
            if (!$fresh || $fresh->teacher_id !== null || $fresh->status !== 'pending') {
                return false;
            }
            $fresh->assignTeacher($teacher);
            return true;
        });

        if (!$claimed) {
            return redirect()->back()->with('error', 'This evaluation is no longer available.');
        }

        return redirect()->route('teacher.evaluations.show', $evaluationRequest)
            ->with('success', 'Evaluation claimed successfully! You can now start grading.');
    }
    
    /**
     * Show evaluation details for grading
     */
    public function show(HumanEvaluationRequest $evaluationRequest)
    {
        // Ensure this evaluation belongs to the teacher (L57: guard null teacher on unassigned/pending requests)
        if (!$evaluationRequest->teacher || $evaluationRequest->teacher->user_id !== auth()->id()) {
            abort(403);
        }
        
        // Load necessary data
        $evaluationRequest->load([
            'studentAttempt.testSet.section',
            'studentAttempt.testSet.questions',
            'studentAttempt.answers.question',
            'studentAttempt.answers.speakingRecording'
        ]);
        
        // Mark as in progress if still assigned
        if ($evaluationRequest->status === 'assigned') {
            $evaluationRequest->update(['status' => 'in_progress']);
        }
        
        return view('teacher.evaluations.show', compact('evaluationRequest'));
    }
    
    /**
     * Submit evaluation
     */
    public function submit(Request $request, HumanEvaluationRequest $evaluationRequest)
    {
        // Ensure this evaluation belongs to the teacher (L57: guard null teacher on unassigned/pending requests)
        if (!$evaluationRequest->teacher || $evaluationRequest->teacher->user_id !== auth()->id()) {
            abort(403);
        }
        
        $sectionName = $evaluationRequest->studentAttempt->testSet->section->name;
        
        // Validate based on section type
        if ($sectionName === 'writing') {
            $request->validate([
                'task_scores' => 'required|array',
                'task_scores.*.score' => 'required|numeric|min:0|max:9',
                'task_scores.*.task_achievement' => 'required|numeric|min:0|max:9',
                'task_scores.*.coherence_cohesion' => 'required|numeric|min:0|max:9',
                'task_scores.*.lexical_resource' => 'required|numeric|min:0|max:9',
                'task_scores.*.grammar' => 'required|numeric|min:0|max:9',
                'task_scores.*.feedback' => 'nullable|string',
                'overall_band_score' => 'required|numeric|min:0|max:9',
                'strengths' => 'nullable|array',
                'improvements' => 'nullable|array',
                'error_markings' => 'nullable|json'
            ]);
        } else { // speaking
            $request->validate([
                'task_scores' => 'required|array',
                'task_scores.*.score' => 'required|numeric|min:0|max:9',
                'task_scores.*.fluency_coherence' => 'required|numeric|min:0|max:9',
                'task_scores.*.lexical_resource' => 'required|numeric|min:0|max:9',
                'task_scores.*.grammar' => 'required|numeric|min:0|max:9',
                'task_scores.*.pronunciation' => 'required|numeric|min:0|max:9',
                'task_scores.*.feedback' => 'nullable|string',
                'overall_band_score' => 'required|numeric|min:0|max:9',
                'strengths' => 'nullable|array',
                'improvements' => 'nullable|array'
            ]);
        }
        
        DB::transaction(function () use ($request, $evaluationRequest) {
            // Create human evaluation
            $humanEvaluation = HumanEvaluation::create([
                'evaluation_request_id' => $evaluationRequest->id,
                'evaluator_id' => auth()->id(),
                'task_scores' => $request->task_scores,
                'overall_band_score' => $request->overall_band_score,
                'detailed_feedback' => $request->task_scores, // Store detailed feedback with scores
                'strengths' => $request->strengths,
                'improvements' => $request->improvements,
                'evaluated_at' => now()
            ]);
            
            // Save error markings if provided
            if ($request->has('error_markings') && $request->error_markings) {
                $errorMarkings = json_decode($request->error_markings, true);
                
                foreach ($errorMarkings as $marking) {
                    \App\Models\EvaluationErrorMarking::create([
                        'human_evaluation_id' => $humanEvaluation->id,
                        'student_answer_id' => $marking['answerId'],
                        'task_number' => $marking['taskNumber'],
                        'marked_text' => $marking['text'],
                        'start_position' => $marking['startOffset'],
                        'end_position' => $marking['endOffset'],
                        'error_type' => $marking['errorType'],
                        'comment' => $marking['comment'] ?? $marking['note'] ?? null // Save teacher's note/comment
                    ]);
                }
            }
            
            // Mark request as completed
            $evaluationRequest->markCompleted();

            // Update student attempt with human evaluation band score
            $evaluationRequest->studentAttempt->update([
                'band_score' => $request->overall_band_score
            ]);

            // If this is part of a full test, update the full test section score
            $fullTestSectionAttempt = \App\Models\FullTestSectionAttempt::where('student_attempt_id', $evaluationRequest->student_attempt_id)->first();

            if ($fullTestSectionAttempt) {
                $fullTestAttempt = $fullTestSectionAttempt->fullTestAttempt;
                $sectionType = $fullTestSectionAttempt->section_type;

                // Update the full test attempt with the evaluated score
                $fullTestAttempt->updateSectionScore($sectionType, (float) $request->overall_band_score);
            }
        });
        
        return redirect()->route('teacher.evaluations.pending')
            ->with('success', 'Evaluation submitted successfully!');
    }
    
    /**
     * View completed evaluations
     */
    public function completed(Request $request)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $query = $teacher->evaluationRequests()
            ->where('status', 'completed')
            ->with(['studentAttempt.testSet.section', 'studentAttempt.fullTestSectionAttempt', 'student.branch', 'humanEvaluation']);

        // Filter by student type
        if ($request->has('student_type') && $request->student_type !== '') {
            if ($request->student_type === 'offline') {
                $query->offline();
            } elseif ($request->student_type === 'online') {
                $query->online();
            }
        }

        $evaluations = $query->latest('completed_at')
            ->paginate(20);

        return view('teacher.evaluations.completed', compact('evaluations'));
    }
    
    /**
     * Toggle teacher availability
     */
    public function toggleAvailability()
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        
        $teacher->update([
            'is_available' => !$teacher->is_available
        ]);
        
        return redirect()->back()->with('success', 
            $teacher->is_available ? 'You are now available for evaluations.' : 'You are now unavailable for evaluations.'
        );
    }
}
