<?php

namespace App\Http\Controllers\OfflineStudent;

use App\Http\Controllers\Controller;
use App\Models\FullTest;
use App\Models\FullTestAttempt;
use App\Models\OfflineEnrollment;
use App\Models\StudentAttempt;
use App\Models\TestSection;
use App\Models\TestSet;
use App\Services\BandScoreRecalculator;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected BandScoreRecalculator $bandScoreRecalculator) {}

    /**
     * Display the offline student dashboard
     */
    public function index()
    {
        $user = auth()->user();

        // Get any enrollment (active, expired, completed, inactive)
        $enrollment = OfflineEnrollment::where('user_id', $user->id)
            ->with('branch')
            ->orderByRaw("CASE WHEN status = 'active' THEN 1 WHEN status = 'completed' THEN 2 WHEN status = 'expired' THEN 3 ELSE 4 END")
            ->first();

        // If no enrollment at all OR expired/inactive - show expired dashboard
        // Note: 'completed' should only show expired if truly ALL tests are exhausted
        if (!$enrollment || in_array($enrollment->status, ['expired', 'inactive']) || $enrollment->isExpired()) {
            return $this->showExpiredDashboard($user, $enrollment);
        }

        // For 'completed' status - verify if truly all tests are done
        // If enrollment was wrongly marked completed, reactivate it
        if ($enrollment->status === 'completed') {
            if (!$enrollment->isAllTestsExhausted()) {
                // Enrollment was prematurely marked completed - reactivate
                $enrollment->update(['status' => OfflineEnrollment::STATUS_ACTIVE]);
            } else {
                // Truly completed - show expired dashboard with results
                return $this->showExpiredDashboard($user, $enrollment);
            }
        }

        // Get test sections
        $sections = TestSection::all();

        // Get recent attempts
        $recentAttempts = StudentAttempt::where('user_id', $user->id)
            ->with('testSet.section')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        $this->bandScoreRecalculator->recalculateMany($recentAttempts);

        // Calculate stats
        $stats = [
            'total_tests' => StudentAttempt::where('user_id', $user->id)->count(),
            'completed_tests' => StudentAttempt::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'average_score' => StudentAttempt::where('user_id', $user->id)
                ->whereNotNull('band_score')
                ->avg('band_score'),
        ];

        // Round average score to IELTS format
        if ($stats['average_score']) {
            $stats['average_score'] = round($stats['average_score'] * 2) / 2;
        }

        // Get Full Tests for offline students
        // If enrollment has specific allowed tests, filter by those
        // null = never configured (legacy) - show all offline tests
        // [] = explicitly emptied (all tests removed) - show nothing
        // [1,2,3] = specific tests allowed
        if ($enrollment->hasTestRestrictions() && empty($enrollment->allowed_full_tests)) {
            // Explicitly set to empty - no tests allowed
            $fullTests = collect();
        } else {
            $fullTestsQuery = FullTest::where('is_for_offline', true);

            if ($enrollment->hasTestRestrictions()) {
                // Specific tests assigned via batch — load exactly those (skip active filter)
                $fullTestsQuery->whereIn('id', $enrollment->allowed_full_tests);
            } else {
                // No restriction — show all active offline tests
                $fullTestsQuery->where('active', true);
            }

            $fullTests = $fullTestsQuery->with('testSets.section')->get();
        }

        // Get user's full test attempts grouped by full_test_id
        $fullTestAttempts = FullTestAttempt::where('user_id', $user->id)
            ->with('sectionAttempts.studentAttempt')
            ->get()
            ->groupBy('full_test_id');

        // Get test assignments with individual validity dates
        $testAssignments = $enrollment->testAssignments()
            ->with('fullTest')
            ->get()
            ->keyBy('full_test_id');

        // Get Section Tests for offline students (grouped by section)
        // Logic:
        //   1. allowed_section_tests = [] (explicitly emptied) → show nothing
        //   2. section_tests_allowed = 0 and no per-section limits → show nothing
        //   3. allowed_section_tests = [1,2,3] → only those specific tests
        //   4. section_test_limits with limits > 0 → filter by those sections only
        //   5. section_tests_allowed > 0 (no other restrictions) → show ALL offline section tests
        $allowedSectionTests = $enrollment->allowed_section_tests;
        $sectionTestLimits = $enrollment->section_test_limits ?? [];
        $hasPerSectionLimits = $enrollment->hasPerSectionLimits();

        // Determine if student has section test access
        $hasSectionAccess = false;
        if (!is_null($allowedSectionTests) && empty($allowedSectionTests)) {
            // Explicitly set to empty [] → no access
            $hasSectionAccess = false;
        } elseif (!empty($allowedSectionTests)) {
            // Specific tests assigned → has access
            $hasSectionAccess = true;
        } elseif ($hasPerSectionLimits) {
            // Per-section limits configured → has access (filtered by limits)
            $hasSectionAccess = true;
        } elseif ($enrollment->section_tests_allowed > 0) {
            // Total section quota > 0 → has access to all
            $hasSectionAccess = true;
        }
        // Otherwise (section_tests_allowed=0, no limits, no specific tests) → no access

        $sectionTests = [];
        if ($hasSectionAccess) {
            $sectionTestsQuery = TestSet::where('is_for_offline', true)
                ->with('section');

            // If specific tests assigned via batch, load exactly those (skip active filter)
            if (!empty($allowedSectionTests)) {
                $sectionTestsQuery->whereIn('id', $allowedSectionTests);
            } else {
                // No specific restriction — show all active offline tests
                $sectionTestsQuery->where('active', true);
            }

            // Filter by section types that have limits > 0
            if (is_null($allowedSectionTests) && $hasPerSectionLimits) {
                $allowedSectionNames = array_keys(array_filter($sectionTestLimits, fn($limit) => $limit > 0));
                if (!empty($allowedSectionNames)) {
                    $allowedSectionIds = TestSection::whereIn('name', $allowedSectionNames)->pluck('id')->toArray();
                    $sectionTestsQuery->whereIn('section_id', $allowedSectionIds);
                } else {
                    $sectionTestsQuery->whereRaw('1 = 0');
                }
            }

            $allOfflineTestSets = $sectionTestsQuery->get()->groupBy('section_id');

            foreach ($sections as $section) {
                $testSets = $allOfflineTestSets->get($section->id);
                if ($testSets && $testSets->count() > 0) {
                    $sectionTests[$section->name] = [
                        'section' => $section,
                        'testSets' => $testSets,
                    ];
                }
            }
        }

        // Get user's section attempts grouped by test_set_id
        // Exclude attempts that are part of a Full Test (linked via FullTestSectionAttempt)
        $sectionAttempts = StudentAttempt::where('user_id', $user->id)
            ->whereDoesntHave('fullTestSectionAttempt')
            ->with('testSet.section')
            ->get();
        $this->bandScoreRecalculator->recalculateMany($sectionAttempts);
        $sectionAttempts = $sectionAttempts->groupBy('test_set_id');

        // Get previously completed tests (from past renewals)
        $previouslyCompletedFullTests = $enrollment->getAllPreviouslyCompletedFullTests();
        $previouslyCompletedSectionTests = $enrollment->getAllPreviouslyCompletedSectionTests();

        return view('offline-student.dashboard', compact(
            'user',
            'enrollment',
            'sections',
            'recentAttempts',
            'stats',
            'fullTests',
            'fullTestAttempts',
            'testAssignments',
            'sectionTests',
            'sectionAttempts',
            'previouslyCompletedFullTests',
            'previouslyCompletedSectionTests'
        ));
    }

    /**
     * Show test results history
     */
    public function results()
    {
        $user = auth()->user();

        // Get full test attempts
        $fullTestAttempts = FullTestAttempt::where('user_id', $user->id)
            ->with(['fullTest', 'sectionAttempts.studentAttempt.testSet.section'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get IDs of student attempts that are part of full tests
        $fullTestStudentAttemptIds = $fullTestAttempts->flatMap(function ($fta) {
            return $fta->sectionAttempts->pluck('student_attempt_id');
        })->filter()->toArray();

        // Get section-only attempts (not part of full tests)
        $sectionAttempts = StudentAttempt::where('user_id', $user->id)
            ->whereNotIn('id', $fullTestStudentAttemptIds)
            ->with('testSet.section')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('offline-student.results', compact('fullTestAttempts', 'sectionAttempts'));
    }

    /**
     * Show dashboard for expired/completed enrollments
     * Student can view results but cannot take new tests
     */
    protected function showExpiredDashboard($user, $enrollment)
    {
        // Get test sections
        $sections = TestSection::all();

        // Get ALL attempts for this user (for viewing results)
        $recentAttempts = StudentAttempt::where('user_id', $user->id)
            ->with('testSet.section')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        $this->bandScoreRecalculator->recalculateMany($recentAttempts);

        // Calculate stats from all attempts
        $stats = [
            'total_tests' => StudentAttempt::where('user_id', $user->id)->count(),
            'completed_tests' => StudentAttempt::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'average_score' => StudentAttempt::where('user_id', $user->id)
                ->whereNotNull('band_score')
                ->avg('band_score'),
        ];

        // Round average score to IELTS format
        if ($stats['average_score']) {
            $stats['average_score'] = round($stats['average_score'] * 2) / 2;
        }

        // Get full test attempts for results viewing
        $fullTestAttempts = FullTestAttempt::where('user_id', $user->id)
            ->with(['fullTest', 'sectionAttempts.studentAttempt.testSet.section'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get IDs of student attempts that are part of full tests
        $fullTestStudentAttemptIds = $fullTestAttempts->flatMap(function ($fta) {
            return $fta->sectionAttempts->pluck('student_attempt_id');
        })->filter()->toArray();

        // Get section-only attempts (not part of full tests)
        $sectionAttempts = StudentAttempt::where('user_id', $user->id)
            ->whereNotIn('id', $fullTestStudentAttemptIds)
            ->with('testSet.section')
            ->orderBy('created_at', 'desc')
            ->get();
        $this->bandScoreRecalculator->recalculateMany($sectionAttempts);

        // Get branch contact info (from enrollment or user's branch)
        $branch = $enrollment ? $enrollment->branch : ($user->branch ?? null);

        return view('offline-student.expired-dashboard', compact(
            'user',
            'enrollment',
            'sections',
            'recentAttempts',
            'stats',
            'fullTestAttempts',
            'sectionAttempts',
            'branch'
        ));
    }
}
