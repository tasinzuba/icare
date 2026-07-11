<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FullTest;
use App\Models\FullTestAttempt;
use App\Models\OfflineEnrollment;
use App\Models\StudentAttempt;
use App\Models\TestSection;
use App\Models\TestSet;
use Illuminate\Http\Request;

class OfflineDashboardController extends Controller
{
    /**
     * Return dashboard data as JSON for desktop app
     * GET /api/offline/dashboard
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get enrollment
        $enrollment = OfflineEnrollment::where('user_id', $user->id)
            ->with('branch')
            ->orderByRaw("CASE WHEN status = 'active' THEN 1 WHEN status = 'completed' THEN 2 WHEN status = 'expired' THEN 3 ELSE 4 END")
            ->first();

        if (!$enrollment || in_array($enrollment->status, ['expired', 'inactive']) || $enrollment->isExpired()) {
            return response()->json([
                'expired' => true,
                'message' => 'Your enrollment has expired. Contact your branch.',
                'stats' => $this->getStats($user),
                'days_left' => 0,
            ]);
        }

        // Reactivate if wrongly marked completed
        if ($enrollment->status === 'completed') {
            if (!$enrollment->isAllTestsExhausted()) {
                $enrollment->update(['status' => OfflineEnrollment::STATUS_ACTIVE]);
            } else {
                return response()->json([
                    'expired' => true,
                    'message' => 'All tests completed.',
                    'stats' => $this->getStats($user),
                    'days_left' => 0,
                ]);
            }
        }

        // Stats
        $stats = $this->getStats($user);
        $stats['testsRemaining'] = max(0, ($enrollment->full_tests_allowed ?? 0) - ($enrollment->full_tests_taken ?? 0));

        // Days left
        $daysLeft = now()->diffInDays($enrollment->valid_until, false);
        $daysLeft = max(0, (int) $daysLeft);

        // ---- Full Tests ----
        $fullTests = $this->getFullTests($user, $enrollment);

        // ---- Section Tests ----
        $sectionData = $this->getSectionTests($user, $enrollment);

        return response()->json([
            'expired' => false,
            'stats' => $stats,
            'days_left' => $daysLeft,
            'full_tests' => $fullTests,
            'section_limits' => $sectionData['limits'],
            'section_taken' => $sectionData['taken'],
            'section_tests' => $sectionData['tests'],
        ]);
    }

    /**
     * Calculate student stats
     */
    private function getStats($user): array
    {
        $avgBand = StudentAttempt::where('user_id', $user->id)
            ->whereNotNull('band_score')
            ->avg('band_score');

        if ($avgBand) {
            $avgBand = round($avgBand * 2) / 2;
        }

        return [
            'testsRemaining' => 0,
            'completed' => StudentAttempt::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'avgBand' => $avgBand,
        ];
    }

    /**
     * Get full tests with attempt status
     */
    private function getFullTests($user, $enrollment): array
    {
        if ($enrollment->hasTestRestrictions() && empty($enrollment->allowed_full_tests)) {
            return [];
        }

        $query = FullTest::where('is_for_offline', true)->where('active', true);

        if ($enrollment->hasTestRestrictions()) {
            $query->whereIn('id', $enrollment->allowed_full_tests);
        }

        $fullTests = $query->with('testSets.section')->get();

        // Get attempts
        $attempts = FullTestAttempt::where('user_id', $user->id)
            ->get()
            ->groupBy('full_test_id');

        return $fullTests->map(function ($test) use ($attempts) {
            $testAttempts = $attempts->get($test->id, collect());
            $latestAttempt = $testAttempts->sortByDesc('created_at')->first();

            $status = 'not_started';
            if ($latestAttempt) {
                $status = $latestAttempt->status === 'completed' ? 'completed' : 'in_progress';
            }

            return [
                'id' => $test->id,
                'title' => $test->title,
                'has_listening' => $test->testSets->contains(fn($ts) => $ts->section && $ts->section->name === 'listening'),
                'has_reading' => $test->testSets->contains(fn($ts) => $ts->section && $ts->section->name === 'reading'),
                'has_writing' => $test->testSets->contains(fn($ts) => $ts->section && $ts->section->name === 'writing'),
                'has_speaking' => $test->testSets->contains(fn($ts) => $ts->section && $ts->section->name === 'speaking'),
                'status' => $status,
                'attempt_id' => $latestAttempt?->id,
                'overall_score' => $latestAttempt?->overall_band_score,
            ];
        })->values()->toArray();
    }

    /**
     * Get section tests grouped by type
     */
    private function getSectionTests($user, $enrollment): array
    {
        $sectionNames = ['listening', 'reading', 'writing', 'speaking'];
        $sectionTestLimits = $enrollment->section_test_limits ?? [];
        $hasPerSectionLimits = $enrollment->hasPerSectionLimits();

        // Calculate limits
        $limits = [];
        $taken = [];

        foreach ($sectionNames as $name) {
            if ($hasPerSectionLimits) {
                $limits[$name] = (int) ($sectionTestLimits[$name] ?? 0);
            } else {
                $limits[$name] = (int) ($enrollment->section_tests_allowed ?? 0);
            }
        }

        // Count taken per section
        $sections = TestSection::all()->keyBy('name');
        $sectionAttempts = StudentAttempt::where('user_id', $user->id)
            ->whereDoesntHave('fullTestSectionAttempt')
            ->with('testSet.section')
            ->get();

        foreach ($sectionNames as $name) {
            $section = $sections->get($name);
            $taken[$name] = $section
                ? $sectionAttempts->filter(fn($a) => $a->testSet && $a->testSet->section_id === $section->id)->count()
                : 0;
        }

        // Get tests
        $tests = [];
        $allowedSectionTests = $enrollment->allowed_section_tests;

        // Determine if has section access
        $hasSectionAccess = false;
        if (!is_null($allowedSectionTests) && empty($allowedSectionTests)) {
            $hasSectionAccess = false;
        } elseif (!empty($allowedSectionTests) || $hasPerSectionLimits || $enrollment->section_tests_allowed > 0) {
            $hasSectionAccess = true;
        }

        if ($hasSectionAccess) {
            $query = TestSet::where('active', true)->where('is_for_offline', true)->with('section');

            if (!empty($allowedSectionTests)) {
                $query->whereIn('id', $allowedSectionTests);
            }

            if (is_null($allowedSectionTests) && $hasPerSectionLimits) {
                $allowedNames = array_keys(array_filter($sectionTestLimits, fn($l) => $l > 0));
                if (!empty($allowedNames)) {
                    $sectionIds = TestSection::whereIn('name', $allowedNames)->pluck('id')->toArray();
                    $query->whereIn('section_id', $sectionIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            $allSets = $query->get()->groupBy(fn($ts) => $ts->section?->name);

            // Map section attempts by test_set_id
            $attemptsByTestSet = $sectionAttempts->groupBy('test_set_id');

            foreach ($sectionNames as $name) {
                $sets = $allSets->get($name, collect());
                $tests[$name] = $sets->map(function ($ts) use ($attemptsByTestSet) {
                    $attempts = $attemptsByTestSet->get($ts->id, collect());
                    $latest = $attempts->sortByDesc('created_at')->first();
                    $status = 'not_started';
                    if ($latest) {
                        $status = $latest->status === 'completed' ? 'completed' : 'in_progress';
                    }

                    return [
                        'id' => $ts->id,
                        'title' => $ts->title ?? $ts->name ?? "Test #{$ts->id}",
                        'section_type' => $ts->section?->name,
                        'question_count' => $ts->questions()->count(),
                        'status' => $status,
                        'attempt_id' => $latest?->id,
                    ];
                })->values()->toArray();
            }
        } else {
            foreach ($sectionNames as $name) {
                $tests[$name] = [];
            }
        }

        return [
            'limits' => $limits,
            'taken' => $taken,
            'tests' => $tests,
        ];
    }
}
