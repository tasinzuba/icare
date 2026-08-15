<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\StudentAttempt;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentResultController extends Controller
{
    /**
     * Display a listing of student results.
     */
    public function index(Request $request): View
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $query = StudentAttempt::with(['user', 'user.branch', 'testSet', 'testSet.section', 'fullTestSectionAttempt', 'humanEvaluationRequest'])
            ->where('status', 'completed');

        // Filter by section
        if ($request->filled('section') && $request->section !== '') {
            $query->whereHas('testSet.section', function ($q) use ($request) {
                $q->where('name', $request->section);
            });
        }

        // Filter by test type (premium/free)
        if ($request->filled('test_type') && $request->test_type !== '') {
            $isPremium = $request->test_type === 'premium';
            $query->whereHas('testSet', function ($q) use ($isPremium) {
                $q->where('is_premium', $isPremium);
            });
        }

        // Filter by student type (offline/online)
        if ($request->filled('student_type') && $request->student_type !== '') {
            if ($request->student_type === 'offline') {
                $query->whereHas('user', function ($q) {
                    $q->whereNotNull('branch_id');
                });
            } elseif ($request->student_type === 'online') {
                $query->whereHas('user', function ($q) {
                    $q->whereNull('branch_id');
                });
            }
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->whereHas('user', fn($q) => $q->where('branch_id', $request->branch_id));
        }

        // Filter by evaluation status
        if ($request->filled('evaluation_status') && $request->evaluation_status !== '') {
            if ($request->evaluation_status === 'evaluated') {
                $query->whereNotNull('band_score');
            } elseif ($request->evaluation_status === 'pending') {
                $query->whereNull('band_score');
            }
        }

        // Search by student name or email
        if ($request->filled('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Calculate statistics (using base query without filters for totals)
        $baseQuery = StudentAttempt::where('status', 'completed');
        $totalAttempts = $baseQuery->count();
        $evaluatedCount = (clone $baseQuery)->whereNotNull('band_score')->count();
        $pendingCount = (clone $baseQuery)->whereNull('band_score')
            ->whereHas('testSet.section', function ($q) {
                $q->whereIn('name', ['writing', 'speaking']);
            })->count();

        $attempts = $query->latest()->paginate(20)->withQueryString();

        $branches = \App\Models\Branch::active()->ordered()->get(['id', 'name', 'code']);

        return view('teacher.student-results.index', compact(
            'attempts',
            'totalAttempts',
            'evaluatedCount',
            'pendingCount',
            'branches'
        ));
    }

    /**
     * Display the specified student attempt.
     */
    public function show(StudentAttempt $studentAttempt): View
    {
        // L58: teachers review COMPLETED results only (mirrors index()); prevents viewing
        // in-progress / arbitrary-status attempts by id.
        abort_unless($studentAttempt->status === 'completed', 404);

        $studentAttempt->load([
            'user',
            'user.branch',
            'testSet',
            'testSet.section',
            'testSet.questions',
            'answers',
            'answers.question',
            'answers.selectedOption',
            'answers.speakingRecording',
            'humanEvaluationRequest',
            'humanEvaluationRequest.humanEvaluation'
        ]);

        return view('teacher.student-results.show', compact('studentAttempt'));
    }
}
