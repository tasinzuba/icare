<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchStaff;
use App\Models\BranchCredit;
use App\Models\BranchCreditTransaction;
use App\Models\User;
use App\Services\BranchCreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    /**
     * Display a listing of branches.
     */
    public function index(Request $request)
    {
        $query = Branch::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        $branches = $query->withCount(['branchStaff as staff_count', 'students', 'enrollments'])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new branch.
     */
    public function create()
    {
        return view('admin.branches.create');
    }

    /**
     * Store a newly created branch.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches,code|alpha',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'active' => 'boolean',
            'allow_test_retakes' => 'boolean',
        ]);

        $branch = Branch::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'address' => $request->address,
            'city' => $request->city,
            'phone' => $request->phone,
            'email' => $request->email,
            'active' => $request->boolean('active', true),
            'allow_test_retakes' => $request->boolean('allow_test_retakes', false),
        ]);

        return redirect()->route('admin.branches.show', $branch)
            ->with('success', 'Branch created successfully.');
    }

    /**
     * Display the specified branch.
     */
    public function show(Branch $branch)
    {
        $branch->load(['branchStaff.user', 'enrollments' => function ($q) {
            $q->with('student')->latest()->limit(10);
        }]);

        $stats = [
            'total_students' => $branch->students()->count(),
            'active_enrollments' => $branch->enrollments()->active()->count(),
            'total_revenue' => $branch->enrollments()->sum('paid_amount'),
            'pending_dues' => $branch->enrollments()->sum('due_amount'),
            'tests_this_month' => $branch->students()
                ->withCount(['attempts' => function ($q) {
                    $q->whereMonth('created_at', now()->month);
                }])->get()->sum('attempts_count'),
        ];

        return view('admin.branches.show', compact('branch', 'stats'));
    }

    /**
     * Show the form for editing the specified branch.
     */
    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    /**
     * Update the specified branch.
     */
    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|alpha|unique:branches,code,' . $branch->id,
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'active' => 'boolean',
            'allow_test_retakes' => 'boolean',
        ]);

        $branch->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'address' => $request->address,
            'city' => $request->city,
            'phone' => $request->phone,
            'email' => $request->email,
            'active' => $request->boolean('active', true),
            'allow_test_retakes' => $request->boolean('allow_test_retakes', false),
        ]);

        return redirect()->route('admin.branches.show', $branch)
            ->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified branch.
     */
    public function destroy(Branch $branch)
    {
        // Check if branch has students
        if ($branch->students()->exists()) {
            return back()->with('error', 'Cannot delete branch with enrolled students.');
        }

        $branch->delete();

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch deleted successfully.');
    }

    // =====================
    // Staff Management
    // =====================

    /**
     * Show form to add staff to branch.
     */
    public function addStaffForm(Branch $branch)
    {
        // Get users who are not already staff of this branch
        $availableUsers = User::whereDoesntHave('branchStaffRecords', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })
            ->where('is_admin', false)
            ->orderBy('name')
            ->get();

        return view('admin.branches.add-staff', compact('branch', 'availableUsers'));
    }

    /**
     * Add existing user as staff.
     */
    public function addExistingStaff(Request $request, Branch $branch)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,staff,receptionist',
        ]);

        // Check if user is already staff
        if (BranchStaff::where('branch_id', $branch->id)->where('user_id', $request->user_id)->exists()) {
            return back()->with('error', 'This user is already staff of this branch.');
        }

        BranchStaff::create([
            'branch_id' => $branch->id,
            'user_id' => $request->user_id,
            'role' => $request->role,
            'active' => true,
        ]);

        return redirect()->route('admin.branches.show', $branch)
            ->with('success', 'Staff member added successfully.');
    }

    /**
     * Create new user and add as staff.
     */
    public function createStaff(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,staff,receptionist',
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            // Add as branch staff
            BranchStaff::create([
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'role' => $request->role,
                'active' => true,
            ]);

            DB::commit();

            return redirect()->route('admin.branches.show', $branch)
                ->with('success', 'New staff member created and added successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to create staff member. ' . $e->getMessage());
        }
    }

    /**
     * Update staff role.
     */
    public function updateStaff(Request $request, Branch $branch, BranchStaff $staff)
    {
        $request->validate([
            'role' => 'required|in:admin,staff,receptionist',
            'active' => 'boolean',
        ]);

        $staff->update([
            'role' => $request->role,
            'active' => $request->boolean('active', true),
        ]);

        return back()->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove staff from branch.
     */
    public function removeStaff(Branch $branch, BranchStaff $staff)
    {
        $staff->delete();

        return back()->with('success', 'Staff member removed from branch.');
    }

    /**
     * Toggle branch status.
     */
    public function toggleStatus(Branch $branch)
    {
        $branch->update(['active' => !$branch->active]);

        $status = $branch->active ? 'activated' : 'deactivated';
        return back()->with('success', "Branch {$status} successfully.");
    }

    // =====================
    // AI Credit Management
    // =====================

    /**
     * Show credit management page for a branch.
     */
    public function credits(Branch $branch)
    {
        $creditService = new BranchCreditService();
        $creditSummary = $creditService->getCreditSummary($branch->id);
        $recentTransactions = $creditService->getRecentTransactions($branch->id, 50);

        return view('admin.branches.credits', compact('branch', 'creditSummary', 'recentTransactions'));
    }

    /**
     * Add credits to a branch.
     */
    public function addCredits(Request $request, Branch $branch)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:10000',
            'description' => 'nullable|string|max:255',
        ]);

        $creditService = new BranchCreditService();
        $transaction = $creditService->addCredits(
            $branch->id,
            $request->amount,
            auth()->id(),
            'admin_topup',
            $request->description
        );

        return back()->with('success', "Successfully added {$request->amount} credits to {$branch->name}.");
    }

    /**
     * Show all branches with credit info (for admin overview).
     */
    public function allCredits(Request $request)
    {
        $branches = Branch::with('creditAccount')
            ->withCount('enrollments')
            ->orderBy('name')
            ->get()
            ->map(function ($branch) {
                $credit = $branch->creditAccount ?? BranchCredit::getOrCreate($branch->id);
                return [
                    'branch' => $branch,
                    'balance' => $credit->balance,
                    'total_purchased' => $credit->total_purchased,
                    'total_used' => $credit->total_used,
                ];
            });

        $totalBalance = $branches->sum('balance');
        $totalPurchased = $branches->sum('total_purchased');
        $totalUsed = $branches->sum('total_used');

        return view('admin.branches.all-credits', compact('branches', 'totalBalance', 'totalPurchased', 'totalUsed'));
    }
}
