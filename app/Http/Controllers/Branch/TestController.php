<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ResultDataTrait;
use App\Models\StudentAttempt;
use App\Models\FullTestAttempt;
use App\Services\AnswerValidator;
use Illuminate\Http\Request;

class TestController extends Controller
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
                // testSets is needed to know which sections the full test actually has, and the
                // evaluation relation supplies the writing/speaking band when the teacher's score
                // was not copied onto the attempt.
                'fullTest.testSets',
                'sectionAttempts.studentAttempt.testSet.section',
                'sectionAttempts.studentAttempt.humanEvaluationRequest.humanEvaluation',
            ]);

        // Build Section-only Attempts query (not part of full tests)
        $sectionQuery = StudentAttempt::whereIn('user_id', $branchStudentIds)
            ->whereDoesntHave('fullTestSectionAttempt')
            // the evaluation relation supplies the writing/speaking band when it was not copied
            // onto the attempt; without it the list would lazy-load one query per row
            ->with('user.offlineEnrollment', 'testSet.section', 'humanEvaluationRequest.humanEvaluation');

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

        // Section filter. A full test covers every section, so choosing one is only meaningful for
        // the standalone list — picking Listening therefore shows section tests only, rather than
        // leaving a Full Mock Tests block above that quietly ignored the filter.
        $sectionFilter = $request->input('section');
        if ($sectionFilter) {
            $sectionQuery->whereHas('testSet.section', fn ($q) => $q->where('name', $sectionFilter));
        }

        // Results are finished sittings, Attempts are still open or abandoned. An explicit Status
        // decides the tab, so the two are never ANDed into something that can never match.
        $statusFilter = $request->input('status');
        $activeTab = $statusFilter
            ? ($statusFilter === 'completed' ? 'results' : 'attempts')
            : ($request->input('view') === 'attempts' ? 'attempts' : 'results');

        // Counted before the tab narrows anything, so each tab shows what it actually holds.
        $resultsCount = (clone $fullTestQuery)->where('status', 'completed')->count()
            + (clone $sectionQuery)->where('status', 'completed')->count();
        $attemptsCount = (clone $fullTestQuery)->where('status', '!=', 'completed')->count()
            + (clone $sectionQuery)->where('status', '!=', 'completed')->count();

        if ($statusFilter) {
            $fullTestQuery->where('status', $statusFilter);
            $sectionQuery->where('status', $statusFilter);
        } elseif ($activeTab === 'attempts') {
            $fullTestQuery->where('status', '!=', 'completed');
            $sectionQuery->where('status', '!=', 'completed');
        } else {
            $fullTestQuery->where('status', 'completed');
            $sectionQuery->where('status', 'completed');
        }

        // Test type filter
        $testType = $request->input('type', 'all');
        if ($sectionFilter) {
            $testType = 'section';
        }

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

        return view('branch.tests.index', compact(
            'fullTestAttempts', 'sectionAttempts', 'stats', 'branch', 'testType',
            'sectionFilter', 'statusFilter', 'activeTab', 'resultsCount', 'attemptsCount'
        ));
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

        $attempt->load(
            'user.offlineEnrollment',
            'testSet.section',
            'answers.question',
            'answers.selectedOption',
            'answers.speakingRecording',
            'humanEvaluationRequest.humanEvaluation'
        );

        // Per-question breakdown for the auto-scored sections, using the same logic as the student,
        // admin and teacher result screens so everyone sees identical marking.
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

        return view('branch.tests.results', compact('attempt', 'branch', 'responses'));
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
