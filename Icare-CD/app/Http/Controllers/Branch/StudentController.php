<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchActivityLog;
use App\Models\FullTest;
use App\Models\OfflineEnrollment;
use App\Models\OfflinePackage;
use App\Models\User;
use App\Notifications\OfflineStudentWelcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Imports\OfflineStudentImport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StudentController extends Controller
{
    /**
     * Display list of students for the branch
     */
    public function index(Request $request)
    {
        $branch = auth()->user()->getPrimaryBranch();

        $query = OfflineEnrollment::where('branch_id', $branch->id)
            ->with('student', 'enrolledByUser');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Batch filter
        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        $enrollments = $query->with('batch')->orderBy('created_at', 'desc')->paginate(15);

        // Stats - single aggregated query instead of 4 separate COUNT queries
        $statsRaw = OfflineEnrollment::where('branch_id', $branch->id)
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(status = 'active') as active")
            ->selectRaw("SUM(status = 'expired') as expired")
            ->selectRaw("SUM(payment_status = 'pending') as pending_payment")
            ->first();

        $stats = [
            'total' => (int) $statsRaw->total,
            'active' => (int) $statsRaw->active,
            'expired' => (int) $statsRaw->expired,
            'pending_payment' => (int) $statsRaw->pending_payment,
        ];

        $batches = \App\Models\Batch::forBranch($branch->id)->active()->orderBy('name')->get();

        return view('branch.students.index', compact('enrollments', 'branch', 'stats', 'batches'));
    }

    /**
     * Show form to create new student
     */
    public function create()
    {
        $branch = auth()->user()->getPrimaryBranch();
        $batches = \App\Models\Batch::forBranch($branch->id)->active()->configured()->orderBy('name')->get();

        return view('branch.students.create', compact('branch', 'batches'));
    }

    /**
     * Store new student enrollment
     */
    public function store(Request $request)
    {
        $branch = auth()->user()->getPrimaryBranch();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|max:50',
            'batch_id' => 'required|exists:batches,id',
            'evaluation_type' => 'required|in:ai,human,both',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify batch belongs to this branch and is configured
        $batch = \App\Models\Batch::findOrFail($validated['batch_id']);
        if ($batch->branch_id !== $branch->id || !$batch->isConfigured()) {
            return back()->withInput()->with('error', 'Invalid batch selected.');
        }

        $plainPassword = !empty($validated['password']) ? $validated['password'] : $this->generateSecurePassword();

        // Check if user with this email already exists
        $existingUser = User::where('email', $validated['email'])->first();

        if ($existingUser) {
            // SECURITY (C2): matching an existing account purely by email allowed a branch user to
            // overwrite and password-reset an admin / teacher / branch-staff / other-branch account
            // (is_admin/role_id are left intact) and take it over. Never convert an account we do
            // not legitimately own at this branch.
            $isPrivileged = $existingUser->is_admin
                || $existingUser->role_id
                || $existingUser->isBranchStaff()
                || $existingUser->teacher()->exists();
            $belongsToAnotherBranch = $existingUser->student_type === 'offline'
                && (int) $existingUser->branch_id !== (int) $branch->id;
            if ($isPrivileged || $belongsToAnotherBranch) {
                return back()->withInput()->with('error', 'This email is already registered to another account and cannot be enrolled at this branch.');
            }

            if ($existingUser->student_type === 'offline') {
                $hasActiveEnrollment = OfflineEnrollment::where('user_id', $existingUser->id)
                    ->whereIn('status', ['active', 'completed'])
                    ->exists();
                if ($hasActiveEnrollment) {
                    return back()->withInput()->with('error', 'This student already has an active enrollment.');
                }
            }
        }

        try {
            DB::beginTransaction();

            if ($existingUser) {
                $user = $existingUser;
                $user->update([
                    'name' => $validated['name'],
                    'phone_number' => $validated['phone_number'] ?? $user->phone_number,
                    'password' => Hash::make($plainPassword),
                    'student_type' => 'offline',
                    'branch_id' => $branch->id,
                    'tests_taken_this_month' => 0,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            } else {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone_number'] ?? null,
                    'password' => Hash::make($plainPassword),
                    'student_type' => 'offline',
                    'branch_id' => $branch->id,
                    'email_verified_at' => now(),
                ]);
            }

            $studentId = $branch->generateStudentId();

            // Create enrollment from batch config
            $enrollment = OfflineEnrollment::createFromBatch(
                userId: $user->id,
                branchId: $branch->id,
                studentId: $studentId,
                batch: $batch,
                evaluationType: $validated['evaluation_type'],
                notes: $validated['notes'] ?? null,
            );
            $packageName = $batch->name;

            // Store the plain password (encrypted) for admin viewing
            $enrollment->update(['initial_password' => $plainPassword]);

            // Create test assignments with individual validity dates
            if (!empty($enrollment->allowed_full_tests)) {
                $validUntil = $enrollment->valid_until->toDateString();
                foreach ($enrollment->allowed_full_tests as $testId) {
                    \App\Models\EnrollmentTestAssignment::create([
                        'offline_enrollment_id' => $enrollment->id,
                        'full_test_id' => $testId,
                        'assigned_at' => now()->toDateString(),
                        'valid_until' => $validUntil,
                        'status' => 'available',
                        'renewal_batch' => 1,
                    ]);
                }
            }

            // Log activity
            BranchActivityLog::log(
                $branch->id,
                BranchActivityLog::ACTION_CREATED,
                "Enrolled new student: {$validated['name']} ({$studentId})",
                OfflineEnrollment::class,
                $enrollment->id,
                null,
                [
                    'student_id' => $studentId,
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'package' => $packageName,
                    'full_tests_allowed' => $enrollment->full_tests_allowed,
                    'evaluation_type' => $enrollment->evaluation_type,
                ]
            );

            DB::commit();

            // Send welcome email with credentials (outside transaction to prevent rollback issues)
            try {
                $user->notify(new OfflineStudentWelcome($plainPassword, $enrollment, $branch));
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email to offline student', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the enrollment if email fails
            }

            return redirect()->route('branch.students.show', $enrollment)
                ->with('success', "Student enrolled successfully! Student ID: {$studentId}. Login credentials sent to {$validated['email']}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to enroll student: ' . $e->getMessage());
        }
    }

    /**
     * Generate a secure random password
     */
    protected function generateSecurePassword(int $length = 10): string
    {
        // Mix of uppercase, lowercase, numbers for readability
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // Removed I, O to avoid confusion
        $lowercase = 'abcdefghjkmnpqrstuvwxyz';   // Removed i, l, o
        $numbers = '23456789';                    // Removed 0, 1

        $password = '';

        // Ensure at least one of each type
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];

        // Fill the rest
        $allChars = $uppercase . $lowercase . $numbers;
        for ($i = 3; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle the password
        return str_shuffle($password);
    }

    /**
     * Show student details
     */
    public function show(OfflineEnrollment $enrollment)
    {
        $branch = auth()->user()->getPrimaryBranch();

        // Ensure enrollment belongs to this branch
        if ($enrollment->branch_id !== $branch->id) {
            abort(403);
        }

        $enrollment->load('student', 'enrolledByUser', 'branch');

        // Get test attempts
        $attempts = $enrollment->student->attempts()
            ->with('testSet.section')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('branch.students.show', compact('enrollment', 'attempts', 'branch'));
    }

    /**
     * Show form to edit student enrollment
     */
    public function edit(OfflineEnrollment $enrollment)
    {
        $branch = auth()->user()->getPrimaryBranch();

        if ($enrollment->branch_id !== $branch->id) {
            abort(403);
        }

        $enrollment->load('student', 'batch');
        $batches = \App\Models\Batch::forBranch($branch->id)->active()->configured()->orderBy('name')->get();

        return view('branch.students.edit', compact('enrollment', 'branch', 'batches'));
    }

    /**
     * Update student enrollment
     */
    public function update(Request $request, OfflineEnrollment $enrollment)
    {
        $branch = auth()->user()->getPrimaryBranch();

        if ($enrollment->branch_id !== $branch->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($enrollment->user_id)],
            'phone_number' => 'nullable|string|max:20',
            'new_password' => 'nullable|string|min:6|max:50',
            'batch_id' => 'nullable|exists:batches,id',
            'status' => 'required|in:active,inactive,expired,completed',
            'valid_until' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Store old values for audit
            $oldValues = [
                'name' => $enrollment->student->name,
                'email' => $enrollment->student->email,
                'status' => $enrollment->status,
                'valid_until' => $enrollment->valid_until?->format('Y-m-d'),
            ];

            // Update User
            $userUpdate = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
            ];

            // Reset password if provided
            if (!empty($validated['new_password'])) {
                $userUpdate['password'] = Hash::make($validated['new_password']);
                $enrollment->update(['initial_password' => $validated['new_password']]);
            }

            $enrollment->student->update($userUpdate);

            // Build enrollment update data
            $enrollmentUpdate = [
                'status' => $validated['status'],
                'notes' => $validated['notes'],
            ];

            // Update batch — sync enrollment config from batch
            if (array_key_exists('batch_id', $validated) && $validated['batch_id'] != $enrollment->batch_id) {
                $newBatchId = $validated['batch_id'] ?: null;
                $enrollmentUpdate['batch_id'] = $newBatchId;

                if ($newBatchId) {
                    $newBatch = \App\Models\Batch::find($newBatchId);
                    if ($newBatch && $newBatch->isConfigured()) {
                        $limits = $newBatch->section_test_limits ?? [];
                        $enrollmentUpdate['full_tests_allowed'] = $newBatch->full_tests_allowed;
                        $enrollmentUpdate['section_test_limits'] = $limits;
                        $enrollmentUpdate['section_tests_allowed'] = array_sum($limits);
                        $enrollmentUpdate['allowed_full_tests'] = $newBatch->allowed_full_tests;
                        $enrollmentUpdate['allowed_section_tests'] = $newBatch->allowed_section_tests;
                        $enrollmentUpdate['valid_until'] = now()->addDays($newBatch->validity_days)->toDateString();
                        $enrollmentUpdate['valid_from'] = now()->toDateString();
                    }
                }
            }

            // If reactivating and new validity date provided, extend it
            if ($validated['status'] === 'active' && !empty($validated['valid_until'])) {
                $enrollmentUpdate['valid_until'] = $validated['valid_until'];
                $enrollmentUpdate['valid_from'] = now()->toDateString();
            }

            // Update Enrollment
            $enrollment->update($enrollmentUpdate);

            // Log activity
            BranchActivityLog::log(
                $branch->id,
                BranchActivityLog::ACTION_UPDATED,
                "Updated student: {$validated['name']} ({$enrollment->student_id})",
                OfflineEnrollment::class,
                $enrollment->id,
                $oldValues,
                [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'status' => $validated['status'],
                ]
            );

            DB::commit();

            return redirect()->route('branch.students.show', $enrollment)
                ->with('success', 'Student updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    /**
     * Delete student enrollment
     */
    public function destroy(OfflineEnrollment $enrollment)
    {
        $branch = auth()->user()->getPrimaryBranch();

        if ($enrollment->branch_id !== $branch->id) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            // Store info for audit before deletion (handle null student gracefully)
            $student = $enrollment->student;
            $studentName = $student->name ?? 'Unknown';
            $studentId = $enrollment->student_id;

            // Log activity before deletion
            BranchActivityLog::log(
                $branch->id,
                BranchActivityLog::ACTION_DELETED,
                "Removed student enrollment: {$studentName} ({$studentId})",
                OfflineEnrollment::class,
                $enrollment->id,
                [
                    'student_id' => $studentId,
                    'name' => $studentName,
                    'status' => $enrollment->status,
                    'full_tests_taken' => $enrollment->full_tests_taken,
                ],
                null
            );

            // Delete test assignments
            $enrollment->testAssignments()->delete();

            if ($student) {
                $userId = $student->id;

                // Clean all test data for this student so re-enrollment starts fresh
                // Cascade handles: full_test_section_attempts, student_answers, ai_evaluation_jobs
                DB::table('full_test_attempts')->where('user_id', $userId)->delete();
                DB::table('student_attempts')->where('user_id', $userId)->delete();

                // Revert user back to public student and reset counters
                $student->update([
                    'student_type' => 'public',
                    'branch_id' => null,
                    'tests_taken_this_month' => 0,
                ]);
            }

            // Delete enrollment
            $enrollment->delete();

            DB::commit();

            return redirect()->route('branch.students.index')
                ->with('success', 'Student enrollment removed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete student enrollment', [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to remove: ' . $e->getMessage());
        }
    }

    /**
     * Show renewal form
     */
    public function renewForm(OfflineEnrollment $enrollment)
    {
        $branch = auth()->user()->getPrimaryBranch();

        if ($enrollment->branch_id !== $branch->id) {
            abort(403);
        }

        // Get available packages for this branch
        $packages = OfflinePackage::getPackagesForBranch($branch->id);

        // Get available full tests for offline students (active flag ignored — branch admin can assign any)
        $fullTests = FullTest::where('is_for_offline', true)
            ->orderBy('order_number')
            ->get(['id', 'title', 'is_premium']);

        // Get available section tests for offline students (active flag ignored)
        $sectionTests = \App\Models\TestSet::where('is_for_offline', true)
            ->with('section:id,name')
            ->orderBy('title')
            ->get(['id', 'title', 'section_id']);

        // Get previously completed tests
        $previouslyCompletedIds = $enrollment->getAllPreviouslyCompletedFullTests();
        $currentlyCompletedIds = $enrollment->getCurrentCompletedFullTestIds();
        $allCompletedIds = array_unique(array_merge($previouslyCompletedIds, $currentlyCompletedIds));

        return view('branch.students.renew', compact(
            'enrollment',
            'branch',
            'packages',
            'fullTests',
            'sectionTests',
            'previouslyCompletedIds',
            'currentlyCompletedIds',
            'allCompletedIds'
        ));
    }

    /**
     * Process renewal
     */
    public function renew(Request $request, OfflineEnrollment $enrollment)
    {
        $branch = auth()->user()->getPrimaryBranch();

        if ($enrollment->branch_id !== $branch->id) {
            abort(403);
        }

        $validated = $request->validate([
            'renewal_mode' => 'required|in:add_new,full_reset',
            'package_type' => 'required|in:preset,custom',
            'package_id' => 'required_if:package_type,preset|nullable|exists:offline_packages,id',
            'full_tests_allowed' => 'nullable|integer|min:0|max:100',
            'section_tests_allowed' => 'nullable|integer|min:0|max:500',
            'validity_days' => 'nullable|integer|min:1|max:365',
            'valid_until' => 'nullable|date|after:today',
            'evaluation_type' => 'required|in:ai,human,both',
            'allowed_full_tests' => 'nullable|array',
            'allowed_full_tests.*' => 'exists:full_tests,id',
            'allowed_section_tests' => 'nullable|array',
            'allowed_section_tests.*' => 'exists:test_sets,id',
            'section_limit_listening' => 'nullable|integer|min:0|max:100',
            'section_limit_reading' => 'nullable|integer|min:0|max:100',
            'section_limit_writing' => 'nullable|integer|min:0|max:100',
            'section_limit_speaking' => 'nullable|integer|min:0|max:100',
            'total_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Store old values for audit
            $oldValues = [
                'full_tests_allowed' => $enrollment->full_tests_allowed,
                'full_tests_taken' => $enrollment->full_tests_taken,
                'section_tests_allowed' => $enrollment->section_tests_allowed,
                'section_tests_taken' => $enrollment->section_tests_taken,
                'valid_until' => $enrollment->valid_until?->toDateString(),
                'status' => $enrollment->status,
                'renewal_count' => $enrollment->renewal_count ?? 0,
            ];

            // Get package details
            if ($validated['package_type'] === 'preset') {
                $package = \App\Models\OfflinePackage::findOrFail($validated['package_id']);
                $renewalData = [
                    'full_tests_allowed' => $package->full_tests_allowed,
                    'section_tests_allowed' => $package->section_tests_allowed ?? 0,
                    'validity_days' => $package->validity_days,
                    'evaluation_type' => $validated['evaluation_type'],
                    'allowed_full_tests' => !empty($validated['allowed_full_tests']) ? $validated['allowed_full_tests'] : null,
                    'total_amount' => $validated['total_amount'] ?? $package->price ?? 0,
                    'paid_amount' => $validated['paid_amount'] ?? 0,
                ];
            } else {
                // Build per-section test limits
                $sectionTestLimits = [
                    'listening' => (int) ($validated['section_limit_listening'] ?? 0),
                    'reading' => (int) ($validated['section_limit_reading'] ?? 0),
                    'writing' => (int) ($validated['section_limit_writing'] ?? 0),
                    'speaking' => (int) ($validated['section_limit_speaking'] ?? 0),
                ];
                $totalSectionTests = array_sum($sectionTestLimits);

                $renewalData = [
                    'full_tests_allowed' => (int) ($validated['full_tests_allowed'] ?? 0),
                    'section_tests_allowed' => $totalSectionTests,
                    'validity_days' => (int) ($validated['validity_days'] ?? 30),
                    'evaluation_type' => $validated['evaluation_type'],
                    'allowed_full_tests' => !empty($validated['allowed_full_tests']) ? $validated['allowed_full_tests'] : null,
                    'allowed_section_tests' => !empty($validated['allowed_section_tests']) ? $validated['allowed_section_tests'] : null,
                    'section_test_limits' => $sectionTestLimits,
                    'section_tests_taken_by_type' => [
                        'listening' => 0, 'reading' => 0, 'writing' => 0, 'speaking' => 0,
                    ],
                    'total_amount' => $validated['total_amount'] ?? 0,
                    'paid_amount' => $validated['paid_amount'] ?? 0,
                ];

                // If specific valid_until date is provided, use it instead of validity_days
                if (!empty($validated['valid_until'])) {
                    $renewalData['valid_until'] = $validated['valid_until'];
                }
            }

            // Add renewal mode to data
            $renewalData['renewal_mode'] = $validated['renewal_mode'];

            // Calculate new validity date for the new tests
            $newValidUntil = !empty($renewalData['valid_until'])
                ? $renewalData['valid_until']
                : now()->addDays((int) ($renewalData['validity_days'] ?? 30))->toDateString();

            // Get currently assigned test IDs (before renewal)
            $existingAssignedTestIds = $enrollment->testAssignments()
                ->pluck('full_test_id')
                ->toArray();

            // Perform renewal based on mode
            if ($validated['renewal_mode'] === 'add_new') {
                // Add New Tests mode: Keep old tests, add new ones
                $enrollment->addNewTestsRenewal($renewalData);
                $successMessage = 'New tests added successfully! Previous tests remain unchanged, new tests have new validity period.';

                // Create test assignments for NEW tests only (with new validity)
                $newTestIds = array_diff($renewalData['allowed_full_tests'] ?? [], $existingAssignedTestIds);
                // renewal_count already incremented by addNewTestsRenewal()
                $renewalBatch = $enrollment->renewal_count ?? 1;

                foreach ($newTestIds as $testId) {
                    \App\Models\EnrollmentTestAssignment::create([
                        'offline_enrollment_id' => $enrollment->id,
                        'full_test_id' => $testId,
                        'assigned_at' => now()->toDateString(),
                        'valid_until' => $newValidUntil,
                        'status' => 'available',
                        'renewal_batch' => $renewalBatch,
                    ]);
                }
            } else {
                // Full Reset mode: Reset counters (old behavior)
                $enrollment->renewPackage($renewalData);
                $successMessage = 'Package renewed successfully! Student can now take new tests (previously completed tests are excluded).';

                // For full reset - create new assignments for all selected tests
                // renewal_count already incremented by renewPackage()
                $renewalBatch = $enrollment->renewal_count ?? 1;
                $allCompletedIds = $enrollment->getAllPreviouslyCompletedFullTests();

                foreach ($renewalData['allowed_full_tests'] ?? [] as $testId) {
                    // Skip if already completed
                    if (in_array($testId, $allCompletedIds)) {
                        continue;
                    }

                    // Check if assignment already exists for this batch
                    $exists = \App\Models\EnrollmentTestAssignment::where('offline_enrollment_id', $enrollment->id)
                        ->where('full_test_id', $testId)
                        ->where('renewal_batch', $renewalBatch)
                        ->exists();

                    if (!$exists) {
                        \App\Models\EnrollmentTestAssignment::create([
                            'offline_enrollment_id' => $enrollment->id,
                            'full_test_id' => $testId,
                            'assigned_at' => now()->toDateString(),
                            'valid_until' => $newValidUntil,
                            'status' => 'available',
                            'renewal_batch' => $renewalBatch,
                        ]);
                    }
                }
            }

            // Log activity
            $studentName = $enrollment->student->name ?? 'Unknown';
            BranchActivityLog::log(
                $branch->id,
                'renewed',
                "Renewed enrollment for: {$studentName} ({$enrollment->student_id}) - Mode: {$validated['renewal_mode']}",
                OfflineEnrollment::class,
                $enrollment->id,
                $oldValues,
                [
                    'renewal_mode' => $validated['renewal_mode'],
                    'full_tests_allowed' => $enrollment->full_tests_allowed,
                    'validity_days' => $renewalData['validity_days'],
                    'valid_until' => $enrollment->valid_until->toDateString(),
                    'renewal_count' => $enrollment->renewal_count,
                    'previously_completed_count' => count($enrollment->previously_completed_full_tests ?? []),
                ]
            );

            DB::commit();

            return redirect()->route('branch.students.show', $enrollment)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to renew: ' . $e->getMessage());
        }
    }

    /**
     * Extend student validity
     */
    public function extend(Request $request, OfflineEnrollment $enrollment)
    {
        $branch = auth()->user()->getPrimaryBranch();

        if ($enrollment->branch_id !== $branch->id) {
            abort(403);
        }

        $validated = $request->validate([
            'extend_days' => 'required|integer|min:1|max:365',
            'additional_tests' => 'nullable|integer|min:0|max:50',
            'additional_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Store old values for audit
            $oldValues = [
                'valid_until' => $enrollment->valid_until->toDateString(),
                'full_tests_allowed' => $enrollment->full_tests_allowed,
                'total_amount' => $enrollment->total_amount,
                'status' => $enrollment->status,
            ];

            // Extend validity
            $enrollment->valid_until = $enrollment->valid_until->addDays($validated['extend_days']);

            // Add tests if specified
            if (!empty($validated['additional_tests'])) {
                $enrollment->full_tests_allowed += $validated['additional_tests'];
            }

            // Handle additional payment
            if (!empty($validated['additional_amount'])) {
                $enrollment->total_amount += $validated['additional_amount'];
                $enrollment->due_amount += $validated['additional_amount'];
            }

            if (!empty($validated['paid_amount'])) {
                $enrollment->recordPayment($validated['paid_amount'], 'cash', 'Extension payment');
            }

            // Reactivate if was expired
            if ($enrollment->status === 'expired') {
                $enrollment->status = 'active';
            }

            $enrollment->save();

            // Log activity
            $studentName = $enrollment->student->name ?? 'Unknown';
            BranchActivityLog::log(
                $branch->id,
                BranchActivityLog::ACTION_EXTENDED,
                "Extended enrollment for: {$studentName} ({$enrollment->student_id}) by {$validated['extend_days']} days",
                OfflineEnrollment::class,
                $enrollment->id,
                $oldValues,
                [
                    'valid_until' => $enrollment->valid_until->toDateString(),
                    'full_tests_allowed' => $enrollment->full_tests_allowed,
                    'extend_days' => $validated['extend_days'],
                    'additional_tests' => $validated['additional_tests'] ?? 0,
                ]
            );

            DB::commit();

            return back()->with('success', 'Enrollment extended successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to extend: ' . $e->getMessage());
        }
    }

    /**
     * Update test assignments (remove tests, change validity)
     */
    public function updateTests(Request $request, OfflineEnrollment $enrollment)
    {
        $branch = auth()->user()->getPrimaryBranch();

        if ($enrollment->branch_id !== $branch->id) {
            abort(403);
        }

        $validated = $request->validate([
            'keep_tests' => 'nullable|array',
            'keep_tests.*' => 'exists:enrollment_test_assignments,id',
            'validity' => 'nullable|array',
            'validity.*' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            DB::beginTransaction();

            $keepTestIds = $validated['keep_tests'] ?? [];
            $validityUpdates = $validated['validity'] ?? [];

            // Get all current assignments
            $assignments = $enrollment->testAssignments()->get();
            $completedTestIds = $enrollment->getCurrentCompletedFullTestIds();

            $removedCount = 0;
            $updatedCount = 0;

            foreach ($assignments as $assignment) {
                // Skip completed tests - they cannot be modified
                if (in_array($assignment->full_test_id, $completedTestIds)) {
                    continue;
                }

                // If not in keep list, remove it
                if (!in_array($assignment->id, $keepTestIds)) {
                    // Remove from allowed_full_tests array
                    // Keep as empty array [] (not null) so dashboard knows tests were explicitly assigned but all removed
                    $allowedTests = $enrollment->allowed_full_tests ?? [];
                    $allowedTests = array_values(array_diff($allowedTests, [$assignment->full_test_id]));
                    $enrollment->allowed_full_tests = $allowedTests;

                    // Decrease counter
                    $enrollment->full_tests_allowed = max(0, $enrollment->full_tests_allowed - 1);

                    // Delete assignment
                    $assignment->delete();
                    $removedCount++;
                } else {
                    // Update validity if changed
                    if (isset($validityUpdates[$assignment->id]) && $validityUpdates[$assignment->id]) {
                        $newDate = $validityUpdates[$assignment->id];
                        if ($assignment->valid_until->format('Y-m-d') !== $newDate) {
                            $assignment->valid_until = $newDate;
                            $assignment->save();
                            $updatedCount++;
                        }
                    }
                }
            }

            $enrollment->save();

            // Log activity
            $studentName = $enrollment->student->name ?? 'Unknown';
            BranchActivityLog::log(
                $branch->id,
                'updated',
                "Updated test assignments for: {$studentName} - Removed: {$removedCount}, Updated: {$updatedCount}",
                OfflineEnrollment::class,
                $enrollment->id
            );

            DB::commit();

            $message = '';
            if ($removedCount > 0) {
                $message .= "{$removedCount} test(s) removed. ";
            }
            if ($updatedCount > 0) {
                $message .= "{$updatedCount} validity date(s) updated. ";
            }
            if (empty($message)) {
                $message = 'No changes made.';
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update tests: ' . $e->getMessage());
        }
    }

    /**
     * Reset student password and return the new password
     */
    public function resetPassword(Request $request, OfflineEnrollment $enrollment)
    {
        $branch = auth()->user()->getPrimaryBranch();

        if ($enrollment->branch_id !== $branch->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            // Generate new password
            $newPassword = $this->generateSecurePassword();

            // Update user password
            $enrollment->student->update([
                'password' => Hash::make($newPassword)
            ]);

            // Store the new plain password (encrypted) for admin viewing
            $enrollment->update(['initial_password' => $newPassword]);

            // Log activity
            BranchActivityLog::log(
                $branch->id,
                BranchActivityLog::ACTION_UPDATED,
                "Reset password for: {$enrollment->student->name} ({$enrollment->student_id})",
                OfflineEnrollment::class,
                $enrollment->id
            );

            return response()->json([
                'success' => true,
                'password' => $newPassword,
                'message' => 'Password reset successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record payment for enrollment
     */
    public function recordPayment(Request $request, OfflineEnrollment $enrollment)
    {
        $branch = auth()->user()->getPrimaryBranch();

        if ($enrollment->branch_id !== $branch->id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        // Store old values for audit
        $oldPaidAmount = $enrollment->paid_amount;
        $oldDueAmount = $enrollment->due_amount;
        $oldPaymentStatus = $enrollment->payment_status;

        $enrollment->recordPayment(
            $validated['amount'],
            $validated['method'],
            $validated['notes'] ?? "Payment recorded by " . auth()->user()->name
        );

        // Log activity
        $studentName = $enrollment->student->name ?? 'Unknown';
        BranchActivityLog::log(
            $branch->id,
            BranchActivityLog::ACTION_PAYMENT,
            "Payment of ৳{$validated['amount']} recorded for: {$studentName} ({$enrollment->student_id})",
            OfflineEnrollment::class,
            $enrollment->id,
            [
                'paid_amount' => $oldPaidAmount,
                'due_amount' => $oldDueAmount,
                'payment_status' => $oldPaymentStatus,
            ],
            [
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'paid_amount' => $enrollment->paid_amount,
                'due_amount' => $enrollment->due_amount,
                'payment_status' => $enrollment->payment_status,
            ]
        );

        return back()->with('success', 'Payment recorded successfully!');
    }

    /**
     * Show bulk import form
     */
    public function importForm()
    {
        $branch = auth()->user()->getPrimaryBranch();
        $packages = OfflinePackage::getPackagesForBranch($branch->id);

        return view('branch.students.import', compact('branch', 'packages'));
    }

    /**
     * Preview uploaded file and return row count
     */
    public function importPreview(Request $request)
    {
        // Validate with JSON response
        $validator = \Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $importId = Str::uuid()->toString();

            // Ensure imports directory exists
            $importDir = storage_path('app/imports');
            if (!is_dir($importDir)) {
                mkdir($importDir, 0775, true);
            }

            // Store file directly using move
            $filename = $importId . '.' . $file->getClientOriginalExtension();
            $fullPath = $importDir . '/' . $filename;
            $file->move($importDir, $filename);

            // Verify file exists
            if (!file_exists($fullPath)) {
                throw new \Exception('Failed to save uploaded file');
            }
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = [];

            foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex === 1) continue; // Skip header

                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }

                // Skip empty rows
                if (empty(array_filter($rowData))) continue;

                $rows[] = $rowData;
            }

            $totalRows = count($rows);

            // Store import data in cache
            Cache::put("import_{$importId}", [
                'path' => $fullPath,
                'rows' => $rows,
                'total' => $totalRows,
                'processed' => 0,
                'success' => 0,
                'skipped' => 0,
                'errors' => [],
                'imported' => [],
            ], now()->addHours(1));

            return response()->json([
                'success' => true,
                'import_id' => $importId,
                'total_rows' => $totalRows,
                'preview' => array_slice($rows, 0, 5), // First 5 rows for preview
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to read file: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Process import in batches
     */
    public function importProcess(Request $request)
    {
        // Validate with JSON response
        $validator = \Validator::make($request->all(), [
            'import_id' => 'required|string',
            'plan_type' => 'nullable|in:preset,custom',
            'package_id' => 'required_unless:plan_type,custom|nullable|exists:offline_packages,id',
            'full_tests_allowed' => 'required_if:plan_type,custom|nullable|integer|min:0|max:100',
            'section_tests_allowed' => 'required_if:plan_type,custom|nullable|integer|min:0|max:500',
            'validity_days' => 'required_if:plan_type,custom|nullable|integer|min:1|max:365',
            'password' => 'required|string|min:6',
            'evaluation_type' => 'required|in:ai,human,both',
            'batch_size' => 'integer|min:1|max:50',
            'batch_id' => 'nullable|exists:batches,id',
            'new_batch_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $validated = $validator->validated();
        $branch = auth()->user()->getPrimaryBranch();
        $importId = $validated['import_id'];
        $batchSize = $validated['batch_size'] ?? 10;

        // Resolve batch (create new if needed, only on first batch call)
        static $resolvedBatchId = null;
        if ($resolvedBatchId === null) {
            if (!empty($validated['new_batch_name'])) {
                $existingBatch = \App\Models\Batch::where('branch_id', $branch->id)->where('name', $validated['new_batch_name'])->first();
                $resolvedBatchId = $existingBatch ? $existingBatch->id : \App\Models\Batch::create([
                    'branch_id' => $branch->id,
                    'name' => $validated['new_batch_name'],
                    'created_by' => auth()->id(),
                ])->id;
            } else {
                $resolvedBatchId = $validated['batch_id'] ?? false;
            }
        }

        // Get import data from cache
        $importData = Cache::get("import_{$importId}");
        if (!$importData) {
            return response()->json([
                'success' => false,
                'message' => 'Import session expired. Please upload the file again.',
            ], 400);
        }

        $isCustomPlan = ($validated['plan_type'] ?? 'preset') === 'custom';

        if ($isCustomPlan) {
            // Build an in-memory OfflinePackage with custom values (not persisted).
            // We reuse OfflinePackage so the downstream code (createFromImport, logs)
            // doesn't need to branch on plan type.
            $package = new OfflinePackage([
                'name' => sprintf(
                    'Custom Plan — %d Full / %d Section / %d days',
                    (int) $validated['full_tests_allowed'],
                    (int) $validated['section_tests_allowed'],
                    (int) $validated['validity_days'],
                ),
                'full_tests_allowed' => (int) $validated['full_tests_allowed'],
                'section_tests_allowed' => (int) $validated['section_tests_allowed'],
                'validity_days' => (int) $validated['validity_days'],
                'price' => 0,
                'active' => true,
            ]);
        } else {
            $package = OfflinePackage::find($validated['package_id']);
            if (!$package || !$package->isAvailableForBranch($branch->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid package selected.',
                ], 400);
            }
        }

        $rows = $importData['rows'];
        $processed = $importData['processed'];
        $total = $importData['total'];

        // Get next batch
        $batch = array_slice($rows, $processed, $batchSize);

        if (empty($batch)) {
            // All done - log activity and cleanup
            BranchActivityLog::log(
                $branch->id,
                BranchActivityLog::ACTION_CREATED,
                "Bulk imported {$importData['success']} students (Skipped: {$importData['skipped']}, Errors: " . count($importData['errors']) . ")",
                User::class,
                null,
                null,
                [
                    'success_count' => $importData['success'],
                    'skipped_count' => $importData['skipped'],
                    'error_count' => count($importData['errors']),
                    'package' => $package->name,
                ]
            );

            // Store results in session
            session()->put('import_results', [
                'success' => $importData['success'],
                'skipped' => $importData['skipped'],
                'errors' => $importData['errors'],
                'imported' => $importData['imported'],
            ]);
            session()->put('import_password', $validated['password']);

            // Cleanup
            if (isset($importData['path']) && file_exists($importData['path'])) {
                @unlink($importData['path']);
            }
            Cache::forget("import_{$importId}");

            return response()->json([
                'success' => true,
                'completed' => true,
                'processed' => $total,
                'total' => $total,
                'results' => [
                    'success' => $importData['success'],
                    'skipped' => $importData['skipped'],
                    'errors' => count($importData['errors']),
                ],
            ]);
        }

        // Process batch
        foreach ($batch as $index => $rowData) {
            $currentRow = $processed + $index + 2; // +2 for header and 0-index

            try {
                $result = $this->processImportRow($rowData, $branch, $package, $validated['password'], $validated['evaluation_type'], $currentRow, $resolvedBatchId ?: null);

                if ($result['status'] === 'success') {
                    $importData['success']++;
                    $importData['imported'][] = $result['data'];
                } elseif ($result['status'] === 'skipped') {
                    $importData['skipped']++;
                } else {
                    $importData['errors'][] = [
                        'row' => $currentRow,
                        'message' => $result['message'],
                    ];
                }
            } catch (\Exception $e) {
                $importData['errors'][] = [
                    'row' => $currentRow,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $importData['processed'] = $processed + count($batch);

        // Update cache
        Cache::put("import_{$importId}", $importData, now()->addHours(1));

        return response()->json([
            'success' => true,
            'completed' => false,
            'processed' => $importData['processed'],
            'total' => $total,
            'current_success' => $importData['success'],
            'current_skipped' => $importData['skipped'],
            'current_errors' => count($importData['errors']),
        ]);
    }

    /**
     * Process a single import row
     */
    protected function processImportRow(array $rowData, Branch $branch, OfflinePackage $package, string $password, string $evaluationType, int $rowNumber, ?int $batchId = null): array
    {
        // Map columns from CSV template: Name(0), Email(1), Number(2)
        $name = trim($rowData[0] ?? '');
        $email = strtolower(trim($rowData[1] ?? ''));
        $phone = trim($rowData[2] ?? '');

        // Validate
        if (empty($name)) {
            return ['status' => 'error', 'message' => 'Name is required'];
        }

        if (empty($email)) {
            return ['status' => 'error', 'message' => 'Email is required'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'Invalid email format'];
        }

        // Check existing user — same logic as manual store()
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // SECURITY (C3): refuse to overwrite/convert an account we do not own (admin/teacher/
            // branch-staff/other-branch offline student). Email-only matching otherwise resets the
            // victim's password (exported to the branch) and hands over the account in bulk.
            $isPrivileged = $existingUser->is_admin
                || $existingUser->role_id
                || $existingUser->isBranchStaff()
                || $existingUser->teacher()->exists();
            $belongsToAnotherBranch = $existingUser->student_type === 'offline'
                && (int) $existingUser->branch_id !== (int) $branch->id;
            if ($isPrivileged || $belongsToAnotherBranch) {
                return ['status' => 'error', 'message' => 'Email already registered to another account'];
            }

            // If user is an active offline student with enrollment, skip
            if ($existingUser->student_type === 'offline') {
                $hasActiveEnrollment = OfflineEnrollment::where('user_id', $existingUser->id)
                    ->whereIn('status', ['active', 'completed'])
                    ->exists();
                if ($hasActiveEnrollment) {
                    return ['status' => 'skipped', 'message' => 'Already has active enrollment'];
                }
            }

            // Otherwise: expired offline student or public student → re-enroll
        }

        // Check duplicate phone number if provided (only for new users)
        if (!$existingUser && !empty($phone)) {
            $existingPhone = User::where('phone_number', $phone)->exists();
            if ($existingPhone) {
                return ['status' => 'skipped', 'message' => 'Phone number already in use'];
            }
        }

        // Create/update user and enrollment
        return DB::transaction(function () use ($name, $email, $phone, $branch, $package, $password, $evaluationType, $existingUser, $batchId) {
            if ($existingUser) {
                // Re-enroll existing user (expired offline / public without subscription)
                $user = $existingUser;
                $user->update([
                    'name' => $name,
                    'phone_number' => $phone ?: $user->phone_number,
                    'password' => Hash::make($password),
                    'student_type' => 'offline',
                    'branch_id' => $branch->id,
                    'tests_taken_this_month' => 0,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone_number' => $phone ?: null,
                    'password' => Hash::make($password),
                    'student_type' => 'offline',
                    'branch_id' => $branch->id,
                    'email_verified_at' => now(),
                ]);
            }

            $studentId = $branch->generateStudentId();

            $enrollment = OfflineEnrollment::createFromImport(
                userId: $user->id,
                branchId: $branch->id,
                studentId: $studentId,
                package: $package,
                evaluationType: $evaluationType,
            );

            // Assign batch
            if ($batchId) {
                $enrollment->update(['batch_id' => $batchId]);
            }

            // Store the plain password (encrypted) for admin viewing
            $enrollment->update(['initial_password' => $password]);

            // Send welcome email (queued — won't block import)
            try {
                $user->notify(new OfflineStudentWelcome($password, $enrollment, $branch));
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email during bulk import', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the import row if email fails
            }

            return [
                'status' => 'success',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'student_id' => $studentId,
                ],
            ];
        });
    }

    /**
     * Download import template
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, ['Name', 'Email', 'Number']);

            // Sample data rows
            fputcsv($file, ['John Doe', 'john@example.com', '01700000001']);
            fputcsv($file, ['Jane Smith', 'jane@example.com', '01700000002']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export import results as CSV
     */
    public function exportImportResults(Request $request): StreamedResponse
    {
        $results = session('import_results', []);
        $password = session('import_password', '');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="imported_students_credentials.csv"',
        ];

        $callback = function () use ($results, $password) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, ['Name', 'Email', 'Student ID', 'Password']);

            // Data rows
            foreach ($results['imported'] ?? [] as $student) {
                fputcsv($file, [
                    $student['name'],
                    $student['email'],
                    $student['student_id'],
                    $password,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
