<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\OfflineEnrollment;
use App\Models\StudentAttempt;
use App\Models\FullTestAttempt;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Reports overview
     */
    public function index()
    {
        $branch = auth()->user()->getPrimaryBranch();

        return view('branch.reports.index', compact('branch'));
    }

    /**
     * Daily report
     */
    public function daily(Request $request)
    {
        $branch = auth()->user()->getPrimaryBranch();
        $date = $request->filled('date') ? Carbon::parse($request->date) : today();

        // Enrollments on this date
        $enrollments = OfflineEnrollment::where('branch_id', $branch->id)
            ->whereDate('created_at', $date)
            ->with('student', 'enrolledByUser')
            ->get();

        // Tests on this date - Single section tests (for display in list)
        $tests = StudentAttempt::whereHas('user', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id)->where('student_type', 'offline');
            })
            ->whereDoesntHave('fullTestSectionAttempt')
            ->whereDate('created_at', $date)
            ->with('user', 'testSet.section')
            ->get();

        // Full tests on this date
        $fullTests = FullTestAttempt::whereHas('user', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id)->where('student_type', 'offline');
            })
            ->whereDate('created_at', $date)
            ->with('user', 'fullTest')
            ->get();

        // Payments on this date
        $payments = OfflineEnrollment::where('branch_id', $branch->id)
            ->whereDate('updated_at', $date)
            ->where('paid_amount', '>', 0)
            ->get();

        $stats = [
            'enrollments' => $enrollments->count(),
            'tests' => $tests->count() + $fullTests->count(),
            'single_section_tests' => $tests->count(),
            'full_tests' => $fullTests->count(),
            'revenue' => $payments->sum('paid_amount'),
        ];

        return view('branch.reports.daily', compact('branch', 'date', 'enrollments', 'tests', 'fullTests', 'stats'));
    }

    /**
     * Monthly report
     */
    public function monthly(Request $request)
    {
        $branch = auth()->user()->getPrimaryBranch();
        $month = $request->filled('month') ? Carbon::parse($request->month) : now();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        // Monthly enrollments
        $enrollments = OfflineEnrollment::where('branch_id', $branch->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Monthly tests - Single section tests (not part of full test)
        $singleSectionTests = StudentAttempt::whereHas('user', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id)->where('student_type', 'offline');
            })
            ->whereDoesntHave('fullTestSectionAttempt')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Monthly full tests
        $fullTests = FullTestAttempt::whereHas('user', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id)->where('student_type', 'offline');
            })
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $tests = $singleSectionTests + $fullTests;

        // Monthly revenue
        $revenue = OfflineEnrollment::where('branch_id', $branch->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('paid_amount');

        // Daily breakdown
        $dailyData = [];
        for ($day = $startOfMonth->copy(); $day <= $endOfMonth; $day->addDay()) {
            // Single section tests for this day
            $daySingleSection = StudentAttempt::whereHas('user', function ($q) use ($branch) {
                    $q->where('branch_id', $branch->id)->where('student_type', 'offline');
                })
                ->whereDoesntHave('fullTestSectionAttempt')
                ->whereDate('created_at', $day)
                ->count();

            // Full tests for this day
            $dayFullTests = FullTestAttempt::whereHas('user', function ($q) use ($branch) {
                    $q->where('branch_id', $branch->id)->where('student_type', 'offline');
                })
                ->whereDate('created_at', $day)
                ->count();

            $dailyData[] = [
                'date' => $day->format('Y-m-d'),
                'enrollments' => OfflineEnrollment::where('branch_id', $branch->id)
                    ->whereDate('created_at', $day)
                    ->count(),
                'tests' => $daySingleSection + $dayFullTests,
            ];
        }

        $stats = [
            'enrollments' => $enrollments,
            'tests' => $tests,
            'single_section_tests' => $singleSectionTests,
            'full_tests' => $fullTests,
            'revenue' => $revenue,
            'avg_tests_per_day' => round($tests / $endOfMonth->day, 1),
        ];

        return view('branch.reports.monthly', compact('branch', 'month', 'stats', 'dailyData'));
    }

    /**
     * Student performance report
     */
    public function students(Request $request)
    {
        $branch = auth()->user()->getPrimaryBranch();

        $students = OfflineEnrollment::where('branch_id', $branch->id)
            ->with(['student.attempts' => function ($q) {
                $q->whereNotNull('band_score');
            }])
            ->get()
            ->map(function ($enrollment) {
                $attempts = $enrollment->student->attempts;
                return [
                    'student_id' => $enrollment->student_id,
                    'name' => $enrollment->student->name,
                    'tests_taken' => $enrollment->full_tests_taken,
                    'tests_allowed' => $enrollment->full_tests_allowed,
                    'avg_score' => $attempts->avg('band_score'),
                    'best_score' => $attempts->max('band_score'),
                    'status' => $enrollment->status,
                    'valid_until' => $enrollment->valid_until,
                ];
            });

        return view('branch.reports.students', compact('branch', 'students'));
    }

    /**
     * Export report to Excel/CSV
     */
    public function export(Request $request)
    {
        $branch = auth()->user()->getPrimaryBranch();
        $type = $request->type ?? 'students';

        // For now, return CSV
        $filename = "{$branch->code}_{$type}_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        if ($type === 'students') {
            $data = OfflineEnrollment::where('branch_id', $branch->id)
                ->with('student')
                ->get();

            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Student ID', 'Name', 'Email', 'Phone', 'Tests Taken', 'Tests Allowed', 'Status', 'Valid Until', 'Payment Status']);

                foreach ($data as $e) {
                    fputcsv($file, [
                        $e->student_id,
                        $e->student->name,
                        $e->student->email,
                        $e->student->phone_number,
                        $e->full_tests_taken,
                        $e->full_tests_allowed,
                        $e->status,
                        $e->valid_until->format('Y-m-d'),
                        $e->payment_status,
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return back()->with('error', 'Invalid export type');
    }
}
