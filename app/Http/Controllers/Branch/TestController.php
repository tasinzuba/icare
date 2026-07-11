<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\StudentAttempt;
use App\Models\FullTestAttempt;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Display all tests for branch students - organized by Full Tests and Section Tests
     */
    public function index(Request $request)
    {
        $branch = auth()->user()->getPrimaryBranch();

        // Get student IDs belonging to this branch
        $branchStudentIds = \App\Models\User::where('branch_id', $branch->id)
            ->where('student_type', 'offline')
            ->pluck('id');

        // Build Full Test Attempts query
        $fullTestQuery = FullTestAttempt::whereIn('user_id', $branchStudentIds)
            ->with([
                'user.offlineEnrollment',
                'fullTest',
                'sectionAttempts.studentAttempt.testSet.section'
            ]);

        // Build Section-only Attempts query (not part of full tests)
        $sectionQuery = StudentAttempt::whereIn('user_id', $branchStudentIds)
            ->whereDoesntHave('fullTestSectionAttempt')
            ->with('user.offlineEnrollment', 'testSet.section');

        // Date filter
        if ($request->filled('date')) {
            $fullTestQuery->whereDate('created_at', $request->date);
            $sectionQuery->whereDate('created_at', $request->date);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $fullTestQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('offlineEnrollment', function ($sq) use ($search) {
                        $sq->where('student_id', 'like', "%{$search}%");
                    });
            });
            $sectionQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('offlineEnrollment', function ($sq) use ($search) {
                        $sq->where('student_id', 'like', "%{$search}%");
                    });
            });
        }

        // Test type filter
        $testType = $request->input('type', 'all');

        if ($testType === 'full') {
            $fullTestAttempts = $fullTestQuery->orderBy('created_at', 'desc')->paginate(15);
            $sectionAttempts = collect();
        } elseif ($testType === 'section') {
            $fullTestAttempts = collect();
            $sectionAttempts = $sectionQuery->orderBy('created_at', 'desc')->paginate(15);
        } else {
            $fullTestAttempts = $fullTestQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'full_page');
            $sectionAttempts = $sectionQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'section_page');
        }

        // Stats
        $stats = [
            'total_full_tests' => FullTestAttempt::whereIn('user_id', $branchStudentIds)->count(),
            'total_section_tests' => StudentAttempt::whereIn('user_id', $branchStudentIds)
                ->whereDoesntHave('fullTestSectionAttempt')->count(),
            'today_full' => FullTestAttempt::whereIn('user_id', $branchStudentIds)
                ->whereDate('created_at', today())->count(),
            'today_section' => StudentAttempt::whereIn('user_id', $branchStudentIds)
                ->whereDoesntHave('fullTestSectionAttempt')
                ->whereDate('created_at', today())->count(),
            'this_week' => FullTestAttempt::whereIn('user_id', $branchStudentIds)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
                + StudentAttempt::whereIn('user_id', $branchStudentIds)
                    ->whereDoesntHave('fullTestSectionAttempt')
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
        $stats['today'] = $stats['today_full'] + $stats['today_section'];
        $stats['total'] = $stats['total_full_tests'] + $stats['total_section_tests'];

        return view('branch.tests.index', compact('fullTestAttempts', 'sectionAttempts', 'stats', 'branch', 'testType'));
    }

    /**
     * Display today's tests
     */
    public function today(Request $request)
    {
        $branch = auth()->user()->getPrimaryBranch();

        $branchStudentIds = \App\Models\User::where('branch_id', $branch->id)
            ->where('student_type', 'offline')
            ->pluck('id');

        // Full test attempts today
        $fullTestAttempts = FullTestAttempt::whereIn('user_id', $branchStudentIds)
            ->with([
                'user.offlineEnrollment',
                'fullTest',
                'sectionAttempts.studentAttempt.testSet.section'
            ])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();

        // Section-only attempts today
        $sectionAttempts = StudentAttempt::whereIn('user_id', $branchStudentIds)
            ->whereDoesntHave('fullTestSectionAttempt')
            ->with('user.offlineEnrollment', 'testSet.section')
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('branch.tests.today', compact('fullTestAttempts', 'sectionAttempts', 'branch'));
    }

    /**
     * Display test results with scores - redirect to index
     */
    public function results(Request $request)
    {
        return redirect()->route('branch.tests.index');
    }

    /**
     * Show individual attempt details
     */
    public function showAttempt(StudentAttempt $attempt)
    {
        $branch = auth()->user()->getPrimaryBranch();

        if (!$attempt->user || $attempt->user->branch_id !== $branch->id) {
            abort(403);
        }

        $attempt->load('user.offlineEnrollment', 'testSet.section', 'answers.question');

        return view('branch.tests.results', compact('attempt', 'branch'));
    }

    /**
     * Show full test attempt details
     */
    public function showFullTestAttempt(FullTestAttempt $fullTestAttempt)
    {
        $branch = auth()->user()->getPrimaryBranch();

        if (!$fullTestAttempt->user || $fullTestAttempt->user->branch_id !== $branch->id) {
            abort(403);
        }

        $fullTestAttempt->load([
            'user.offlineEnrollment',
            'fullTest',
            'sectionAttempts.studentAttempt.testSet.section',
            'sectionAttempts.studentAttempt.answers.question'
        ]);

        return view('branch.tests.full-test-results', compact('fullTestAttempt', 'branch'));
    }
}
