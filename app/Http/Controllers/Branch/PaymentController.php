<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\OfflineEnrollment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Payments overview
     */
    public function index(Request $request)
    {
        $branch = auth()->user()->getPrimaryBranch();

        $query = OfflineEnrollment::where('branch_id', $branch->id)
            ->with('student');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $enrollments = $query->latest()->paginate(20);

        // Summary stats
        $stats = [
            'total_revenue' => OfflineEnrollment::where('branch_id', $branch->id)->sum('paid_amount'),
            'pending_amount' => OfflineEnrollment::where('branch_id', $branch->id)->sum('due_amount'),
            'paid_count' => OfflineEnrollment::where('branch_id', $branch->id)->where('payment_status', 'paid')->count(),
            'partial_count' => OfflineEnrollment::where('branch_id', $branch->id)->where('payment_status', 'partial')->count(),
            'pending_count' => OfflineEnrollment::where('branch_id', $branch->id)->where('payment_status', 'pending')->count(),
        ];

        return view('branch.payments.index', compact('branch', 'enrollments', 'stats'));
    }

    /**
     * Record payment
     */
    public function store(Request $request, OfflineEnrollment $enrollment)
    {
        $branch = auth()->user()->getPrimaryBranch();

        // Verify enrollment belongs to this branch
        if ($enrollment->branch_id !== $branch->id) {
            abort(403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $enrollment->due_amount,
            'method' => 'required|in:cash,card,bank_transfer,online',
            'notes' => 'nullable|string|max:500',
        ]);

        $enrollment->recordPayment(
            $request->amount,
            $request->method,
            $request->notes
        );

        return back()->with('success', 'Payment of ৳' . number_format($request->amount) . ' recorded successfully.');
    }

    /**
     * Payment history for an enrollment
     */
    public function history(OfflineEnrollment $enrollment)
    {
        $branch = auth()->user()->getPrimaryBranch();

        if ($enrollment->branch_id !== $branch->id) {
            abort(403);
        }

        return view('branch.payments.history', compact('branch', 'enrollment'));
    }

    /**
     * Due payments list
     */
    public function due()
    {
        $branch = auth()->user()->getPrimaryBranch();

        $enrollments = OfflineEnrollment::where('branch_id', $branch->id)
            ->where('due_amount', '>', 0)
            ->with('student')
            ->orderBy('due_amount', 'desc')
            ->paginate(20);

        return view('branch.payments.due', compact('branch', 'enrollments'));
    }
}
