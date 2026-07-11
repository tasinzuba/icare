<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentAttempt;
use App\Models\FullTestAttempt;
use App\Models\TestSection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the student dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();

        // Check if offline student - show different dashboard
        if ($user->isOfflineStudent()) {
            return $this->offlineDashboard($user);
        }

        // Get recent attempts for this student
        $recentAttempts = $user->attempts()
            ->with(['testSet.section'])
            ->latest()
            ->take(5)
            ->get();

        // Get student statistics
        $averageBandScore = $user->attempts()
            ->whereNotNull('band_score')
            ->avg('band_score');

        // Round to nearest 0.5 (IELTS official format)
        $averageBandScore = $averageBandScore ? round($averageBandScore * 2) / 2 : null;

        // Count single section tests (not part of full test)
        $singleSectionTotal = $user->attempts()->whereDoesntHave('fullTestSectionAttempt')->count();
        $singleSectionCompleted = $user->attempts()->whereDoesntHave('fullTestSectionAttempt')
            ->where('status', 'completed')->count();
        $singleSectionInProgress = $user->attempts()->whereDoesntHave('fullTestSectionAttempt')
            ->where('status', 'in_progress')->count();

        // Count full tests
        $fullTestTotal = $user->fullTestAttempts()->count();
        $fullTestCompleted = $user->fullTestAttempts()->where('status', 'completed')->count();
        $fullTestInProgress = $user->fullTestAttempts()->where('status', 'in_progress')->count();

        $stats = [
            'total_attempts' => $singleSectionTotal + $fullTestTotal,
            'completed_attempts' => $singleSectionCompleted + $fullTestCompleted,
            'in_progress_attempts' => $singleSectionInProgress + $fullTestInProgress,
            'single_section_tests' => $singleSectionTotal,
            'full_tests' => $fullTestTotal,
            'average_band_score' => $averageBandScore,
        ];

        // Section durations for practice time calculation (in minutes)
        $sectionDurations = [
            'listening' => 30,
            'reading' => 60,
            'writing' => 60,
            'speaking' => 15,
        ];

        // Get section-wise performance with trends (exclude full test attempts)
        $sectionPerformance = TestSection::with(['testSets.attempts' => function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('status', 'completed')
                  ->whereNotNull('band_score')
                  ->whereDoesntHave('fullTestSectionAttempt')
                  ->orderBy('created_at', 'desc');
        }])->get()->map(function($section) use ($sectionDurations) {
            $attempts = $section->testSets->flatMap->attempts;
            $sortedAttempts = $attempts->sortByDesc('created_at');

            $averageScore = $attempts->avg('band_score');
            $bestScore = $attempts->max('band_score');

            $trend = 'same';
            $trendValue = 0;
            if ($sortedAttempts->count() >= 2) {
                $lastScore = $sortedAttempts->first()->band_score;
                $previousScore = $sortedAttempts->skip(1)->first()->band_score;
                $trendValue = $lastScore - $previousScore;
                if ($trendValue > 0) $trend = 'up';
                elseif ($trendValue < 0) $trend = 'down';
            }

            $averageScore = $averageScore ? round($averageScore * 2) / 2 : null;
            $bestScore = $bestScore ? round($bestScore * 2) / 2 : null;

            return [
                'name' => $section->name,
                'attempts_count' => $attempts->count(),
                'average_score' => $averageScore,
                'best_score' => $bestScore,
                'trend' => $trend,
                'trend_value' => round($trendValue, 1),
                'last_score' => $sortedAttempts->first()?->band_score ? round($sortedAttempts->first()->band_score * 2) / 2 : null,
                'duration' => $sectionDurations[$section->name] ?? 30,
            ];
        });

        // Calculate total practice time (in hours)
        $totalPracticeMinutes = 0;
        foreach ($sectionPerformance as $section) {
            $totalPracticeMinutes += $section['attempts_count'] * $section['duration'];
        }
        $totalPracticeHours = round($totalPracticeMinutes / 60, 1);

        // Find weakest section (for Today's Focus)
        $weakestSection = $sectionPerformance
            ->where('attempts_count', '>', 0)
            ->sortBy('average_score')
            ->first();

        // Get available test sections with active test sets
        $testSections = TestSection::with(['testSets' => function($query) {
            $query->where('active', 1);
        }])->get();

        // Goals feature removed
        $userGoal = null;
        $daysToExam = null;
        $progressToTarget = 0;
        $scoreNeeded = null;

        // Achievements/leaderboard removed - empty collections
        $recentAchievements = collect();
        $allBadges = collect();
        $userAchievements = collect();
        $progressToNext = collect();
        $leaderboard = collect();
        $userInLeaderboard = false;
        $userRank = null;

        // Section icons for display
        $icons = [
            'listening' => 'fa-headphones',
            'reading' => 'fa-book-open',
            'writing' => 'fa-pen-fancy',
            'speaking' => 'fa-microphone',
        ];

        // Check if onboarding modal should show
        $showOnboarding = !$user->is_admin &&
                          !$user->isOfflineStudent() &&
                          !$user->teacher &&
                          !$user->onboarding_completed;

        // Get progress timeline (last 5 completed attempts with scores)
        $progressTimeline = $user->attempts()
            ->with('testSet.section')
            ->where('status', 'completed')
            ->whereNotNull('band_score')
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get()
            ->map(function($attempt) {
                return [
                    'date' => $attempt->created_at->format('M d'),
                    'score' => round($attempt->band_score * 2) / 2,
                    'section' => $attempt->testSet->section->name,
                ];
            });

        // Vocabulary bank removed
        $vocabCount = 0;
        $vocabMasteredCount = 0;

        return view('student.dashboard', compact(
            'recentAttempts',
            'stats',
            'sectionPerformance',
            'testSections',
            'userGoal',
            'recentAchievements',
            'allBadges',
            'userAchievements',
            'progressToNext',
            'leaderboard',
            'userInLeaderboard',
            'userRank',
            'icons',
            'showOnboarding',
            'daysToExam',
            'progressToTarget',
            'scoreNeeded',
            'weakestSection',
            'totalPracticeHours',
            'progressTimeline',
            'vocabCount',
            'vocabMasteredCount'
        ));
    }

    /**
     * Get leaderboard data for AJAX requests (feature removed).
     */
    public function getLeaderboard(Request $request, $period = 'weekly')
    {
        $leaderboard = collect();
        $userInLeaderboard = false;
        return view('partials.leaderboard-content', compact('leaderboard', 'userInLeaderboard'));
    }

    /**
     * Get top 100 leaderboard data (feature removed).
     */
    public function getTop100Leaderboard($period = 'weekly')
    {
        return response()->json([
            'leaderboard' => [],
            'currentUser' => auth()->id(),
            'period' => $period,
            'total' => 0,
        ]);
    }

    /**
     * Store user's goal (feature removed).
     */
    public function storeGoal(Request $request)
    {
        return redirect()->route('student.dashboard')
            ->with('info', 'Goal tracking is no longer available.');
    }

    /**
     * Mark achievements as seen (feature removed).
     */
    public function markAchievementsSeen(Request $request)
    {
        return response()->json(['success' => true]);
    }

    /**
     * Get achievement details (feature removed).
     */
    public function getAchievementDetails($badgeId)
    {
        return response()->json([
            'badge' => null,
            'earned' => false,
            'earned_at' => null,
        ]);
    }

    /**
     * Display offline student dashboard
     */
    protected function offlineDashboard($user): View
    {
        $enrollment = $user->offlineEnrollment;

        // Get assigned tests for this enrollment
        $assignedTests = $enrollment->testAssignments()
            ->with(['fullTest' => function($q) {
                $q->with(['testSets']);
            }])
            ->orderBy('valid_until', 'asc')
            ->get();

        // Get completed attempts
        $completedAttempts = FullTestAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('fullTest')
            ->latest('end_time')
            ->get();

        return view('student.offline-dashboard', compact(
            'assignedTests',
            'completedAttempts'
        ));
    }

    /**
     * Update exam type (Academic/General) - feature removed.
     */
    public function updateExamType(Request $request)
    {
        return response()->json(['success' => true]);
    }
}
