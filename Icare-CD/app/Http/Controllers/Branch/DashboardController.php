<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchActivityLog;
use App\Models\OfflineEnrollment;
use App\Models\StudentAttempt;
use App\Models\FullTestAttempt;
use App\Services\BranchCreditService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the branch admin dashboard
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $branch = $user->getPrimaryBranch();

        // Get stats
        $stats = [
            'total_students' => OfflineEnrollment::where('branch_id', $branch->id)->count(),
            'active_students' => OfflineEnrollment::where('branch_id', $branch->id)
                ->where('status', 'active')
                ->where('valid_until', '>=', now())
                ->count(),
            'tests_today' => $this->getTodayTestsCount($branch),
            'tests_this_month' => $this->getMonthlyTestsCount($branch),
            'revenue_today' => $this->getTodayRevenue($branch),
            'revenue_this_month' => $this->getMonthlyRevenue($branch),
            'pending_payments' => OfflineEnrollment::where('branch_id', $branch->id)
                ->whereIn('payment_status', ['pending', 'partial'])
                ->sum('due_amount'),
            'expiring_soon' => OfflineEnrollment::where('branch_id', $branch->id)
                ->where('status', 'active')
                ->whereBetween('valid_until', [now(), now()->addDays(7)])
                ->count(),
        ];

        // Recent enrollments
        $recentEnrollments = OfflineEnrollment::where('branch_id', $branch->id)
            ->with('student', 'enrolledByUser')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Today's tests
        $todayTests = $this->getTodayTests($branch);

        // Get activity logs from database
        $activityLogs = BranchActivityLog::forBranch($branch->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        // Recent activities - combine logs with today's tests
        $recentActivities = collect();

        // Add activity logs
        foreach ($activityLogs as $log) {
            $recentActivities->push([
                'type' => $log->action,
                'message' => $log->description,
                'time' => $log->created_at,
                'icon' => $log->action_icon,
                'color' => $log->action_color,
                'user' => $log->user?->name ?? 'System',
            ]);
        }

        // Add today's tests as activities (not logged in activity_logs)
        foreach ($todayTests->take(5) as $test) {
            // Skip if user was deleted
            if (!$test->user) {
                continue;
            }
            $recentActivities->push([
                'type' => 'test',
                'message' => "{$test->user->name} completed a test",
                'time' => $test->created_at,
                'icon' => 'clipboard-check',
                'color' => 'blue',
                'user' => $test->user->name,
            ]);
        }

        // Sort by time
        $recentActivities = $recentActivities->sortByDesc('time')->take(10);

        // Get AI Credit Summary
        $creditService = new BranchCreditService();
        $creditSummary = $creditService->getCreditSummary($branch->id);

        return view('branch.dashboard.index', compact(
            'branch',
            'stats',
            'recentEnrollments',
            'todayTests',
            'recentActivities',
            'creditSummary'
        ));
    }

    /**
     * Get today's test count for the branch
     * Counts single section tests + full tests (not individual sections within full tests)
     */
    private function getTodayTestsCount(Branch $branch): int
    {
        // Single section tests (not part of full test)
        $singleSectionTests = StudentAttempt::whereHas('user', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                    ->where('student_type', 'offline');
            })
            ->whereDoesntHave('fullTestSectionAttempt')
            ->whereDate('created_at', today())
            ->count();

        // Full tests
        $fullTests = FullTestAttempt::whereHas('user', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                    ->where('student_type', 'offline');
            })
            ->whereDate('created_at', today())
            ->count();

        return $singleSectionTests + $fullTests;
    }

    /**
     * Get monthly test count for the branch
     * Counts single section tests + full tests (not individual sections within full tests)
     */
    private function getMonthlyTestsCount(Branch $branch): int
    {
        // Single section tests (not part of full test)
        $singleSectionTests = StudentAttempt::whereHas('user', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                    ->where('student_type', 'offline');
            })
            ->whereDoesntHave('fullTestSectionAttempt')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Full tests
        $fullTests = FullTestAttempt::whereHas('user', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                    ->where('student_type', 'offline');
            })
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return $singleSectionTests + $fullTests;
    }

    /**
     * Get today's tests for the branch
     */
    private function getTodayTests(Branch $branch)
    {
        return StudentAttempt::whereHas('user', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                    ->where('student_type', 'offline');
            })
            ->with('user', 'testSet.section')
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get today's revenue for the branch
     */
    private function getTodayRevenue(Branch $branch): float
    {
        return OfflineEnrollment::where('branch_id', $branch->id)
            ->whereDate('updated_at', today())
            ->where('payment_status', '!=', 'pending')
            ->sum('paid_amount');
    }

    /**
     * Get monthly revenue for the branch
     */
    private function getMonthlyRevenue(Branch $branch): float
    {
        return OfflineEnrollment::where('branch_id', $branch->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('paid_amount');
    }
}
