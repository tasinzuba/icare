<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ResultDataTrait;
use App\Models\StudentAttempt;
use App\Models\Teacher;
use App\Services\AnswerValidator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentResultController extends Controller
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

        // Full Test / Section Test tabs.
        // A full test is ONE sitting made of several section attempts, so that tab lists
        // FullTestAttempt records (one card, overall band) rather than each section separately.
        $activeTab = $request->input('test_type') === 'full' ? 'full' : 'single';

        $fullTestQuery = \App\Models\FullTestAttempt::query()
            ->with(['user.branch', 'fullTest', 'sectionAttempts.studentAttempt.testSet.section'])
            ->whereHas('sectionAttempts.studentAttempt', fn ($q) => $q->where('status', 'completed'));

        // Carry the student-level filters onto the full-test list (section / evaluation status are
        // per-section concepts and do not apply to a whole sitting).
        if ($request->filled('branch_id')) {
            $fullTestQuery->whereHas('user', fn ($q) => $q->where('branch_id', $request->branch_id));
        }
        if ($request->filled('search') && $request->search !== '') {
            $search = $request->search;
            $fullTestQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sectionTestCount = (clone $query)->whereDoesntHave('fullTestSectionAttempt')->count();
        $fullTestCount = (clone $fullTestQuery)->count();

        $fullTestAttempts = null;
        if ($activeTab === 'full') {
            $fullTestAttempts = $fullTestQuery->latest()->paginate(12)->withQueryString();
            // Keep an empty paginator so shared view code (counts, pagination) stays valid.
            $attempts = $query->whereRaw('1 = 0')->paginate(20)->withQueryString();
        } else {
            $attempts = $query->whereDoesntHave('fullTestSectionAttempt')
                ->latest()->paginate(20)->withQueryString();
        }

        $branches = \App\Models\Branch::active()->ordered()->get(['id', 'name', 'code']);

        return view('teacher.student-results.index', compact(
            'attempts',
            'totalAttempts',
            'evaluatedCount',
            'pendingCount',
            'branches',
            'activeTab',
            'fullTestCount',
            'sectionTestCount',
            'fullTestAttempts'
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

        // Build the per-question breakdown for the auto-scored sections. The view previously read
        // fields that do not exist on StudentAnswer (option_text, text_answer, is_correct), so every
        // answer rendered blank and the score summary always said 0. This trait is the same one the
        // student result pages use, so each blank/dropdown/drag zone becomes its own row.
        $responses = null;
        if (in_array(optional($studentAttempt->testSet->section)->name, ['reading', 'listening'], true)) {
            $questions = $studentAttempt->testSet->questions()
                ->with('options')
                ->where('question_type', '!=', 'passage')
                ->orderBy('part_number')
                ->orderBy('order_number')
                ->get();

            $responses = $this->formatQuestionsForVue(
                $this->buildQuestionsAnalysis($questions, $studentAttempt)
            );
        }

        return view('teacher.student-results.show', compact('studentAttempt', 'responses'));
    }

    /**
     * One full-test sitting: overall band plus a tab per section.
     *
     * The section tabs are driven by ?section=, so no client-side state is needed and a tab can be
     * linked to directly.
     */
    public function showFullTest(Request $request, \App\Models\FullTestAttempt $fullTestAttempt): View
    {
        $fullTestAttempt->load([
            'user.branch',
            'fullTest',
            'sectionAttempts.studentAttempt.testSet.section',
            'sectionAttempts.studentAttempt.answers.question',
            'sectionAttempts.studentAttempt.answers.selectedOption',
            'sectionAttempts.studentAttempt.answers.speakingRecording',
            'sectionAttempts.studentAttempt.humanEvaluationRequest.humanEvaluation',
        ]);

        // Sections that actually have a completed attempt, in IELTS order.
        $order = ['listening', 'reading', 'writing', 'speaking'];
        $sections = $fullTestAttempt->sectionAttempts
            ->filter(fn ($sa) => $sa->studentAttempt && $sa->studentAttempt->status === 'completed')
            ->keyBy('section_type')
            ->sortBy(fn ($sa, $type) => array_search($type, $order));

        abort_if($sections->isEmpty(), 404);

        $activeSection = $request->input('section');
        if (!$activeSection || !$sections->has($activeSection)) {
            $activeSection = $sections->keys()->first();
        }

        $studentAttempt = $sections[$activeSection]->studentAttempt;

        // Per-question breakdown for the auto-scored sections (same logic as the single-section page).
        $responses = null;
        if (in_array($activeSection, ['reading', 'listening'], true)) {
            $questions = $studentAttempt->testSet->questions()
                ->with('options')
                ->where('question_type', '!=', 'passage')
                ->orderBy('part_number')
                ->orderBy('order_number')
                ->get();

            $responses = $this->formatQuestionsForVue(
                $this->buildQuestionsAnalysis($questions, $studentAttempt)
            );
        }

        return view('teacher.student-results.full-test', compact(
            'fullTestAttempt',
            'sections',
            'activeSection',
            'studentAttempt',
            'responses'
        ));
    }
}
