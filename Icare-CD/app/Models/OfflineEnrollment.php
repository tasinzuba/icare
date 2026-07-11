<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class OfflineEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'batch_id',
        'student_id',
        'full_tests_allowed',
        'full_tests_taken',
        'section_tests_allowed',
        'section_tests_taken',
        'section_test_limits',
        'section_tests_taken_by_type',
        'evaluation_type',
        'allowed_full_tests',
        'allowed_section_tests',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'payment_method',
        'payment_notes',
        'valid_from',
        'valid_until',
        'enrolled_by',
        'status',
        'notes',
        'previously_completed_full_tests',
        'previously_completed_section_tests',
        'renewal_count',
        'last_renewed_at',
        'initial_password',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'allowed_full_tests' => 'array',
        'allowed_section_tests' => 'array',
        'section_test_limits' => 'array',
        'section_tests_taken_by_type' => 'array',
        'previously_completed_full_tests' => 'array',
        'previously_completed_section_tests' => 'array',
        'last_renewed_at' => 'datetime',
    ];

    /**
     * Graceful encrypted accessor for initial_password.
     * Returns null instead of crashing when value was encrypted with a different APP_KEY.
     */
    public function getInitialPasswordAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Encrypt on set.
     */
    public function setInitialPasswordAttribute($value): void
    {
        $this->attributes['initial_password'] = empty($value)
            ? null
            : \Illuminate\Support\Facades\Crypt::encryptString($value);
    }

    // Evaluation type constants
    public const EVALUATION_AI = 'ai';
    public const EVALUATION_HUMAN = 'human';
    public const EVALUATION_BOTH = 'both';

    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_COMPLETED = 'completed';

    // Payment status constants
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_PARTIAL = 'partial';
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_REFUNDED = 'refunded';

    // =====================
    // Factory Methods
    // =====================

    /**
     * Create enrollment from a preset package.
     * Ensures all required fields are populated with proper defaults.
     */
    public static function createFromPackage(
        int $userId,
        int $branchId,
        string $studentId,
        OfflinePackage $package,
        string $evaluationType = self::EVALUATION_AI,
        ?string $notes = null,
        ?float $totalAmount = null,
        ?float $paidAmount = null,
        ?int $batchId = null,
    ): self {
        $total = $totalAmount ?? 0;
        $paid = $paidAmount ?? 0;

        return self::create([
            'user_id' => $userId,
            'branch_id' => $branchId,
            'batch_id' => $batchId,
            'student_id' => $studentId,
            'full_tests_allowed' => $package->full_tests_allowed,
            'full_tests_taken' => 0,
            'section_tests_allowed' => $package->section_tests_allowed,
            'section_tests_taken' => 0,
            'section_test_limits' => null,  // Preset packages: no per-section limits
            'section_tests_taken_by_type' => [
                'listening' => 0, 'reading' => 0, 'writing' => 0, 'speaking' => 0,
            ],
            'evaluation_type' => $evaluationType,
            'allowed_full_tests' => null,       // All tests allowed
            'allowed_section_tests' => null,     // All section tests allowed
            'total_amount' => $total,
            'paid_amount' => $paid,
            'due_amount' => max(0, $total - $paid),
            'payment_status' => $paid >= $total ? self::PAYMENT_PAID
                : ($paid > 0 ? self::PAYMENT_PARTIAL : self::PAYMENT_PENDING),
            'payment_method' => null,
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addDays($package->validity_days)->toDateString(),
            'enrolled_by' => auth()->id(),
            'status' => self::STATUS_ACTIVE,
            'notes' => $notes,
        ]);
    }

    /**
     * Create enrollment with custom package settings.
     * Used when branch admin configures a custom package manually.
     */
    public static function createCustom(
        int $userId,
        int $branchId,
        string $studentId,
        array $data,
        ?int $batchId = null,
    ): self {
        // Build per-section test limits
        $sectionTestLimits = [
            'listening' => (int) ($data['section_limit_listening'] ?? 0),
            'reading' => (int) ($data['section_limit_reading'] ?? 0),
            'writing' => (int) ($data['section_limit_writing'] ?? 0),
            'speaking' => (int) ($data['section_limit_speaking'] ?? 0),
        ];
        $totalSectionTests = array_sum($sectionTestLimits);

        $total = (float) ($data['total_amount'] ?? 0);
        $paid = (float) ($data['paid_amount'] ?? 0);

        return self::create([
            'user_id' => $userId,
            'branch_id' => $branchId,
            'batch_id' => $batchId,
            'student_id' => $studentId,
            'full_tests_allowed' => (int) $data['full_tests_allowed'],
            'full_tests_taken' => 0,
            'section_tests_allowed' => $totalSectionTests,
            'section_tests_taken' => 0,
            'section_test_limits' => $sectionTestLimits,
            'section_tests_taken_by_type' => [
                'listening' => 0, 'reading' => 0, 'writing' => 0, 'speaking' => 0,
            ],
            'evaluation_type' => $data['evaluation_type'] ?? self::EVALUATION_AI,
            'allowed_full_tests' => !empty($data['allowed_full_tests']) ? $data['allowed_full_tests'] : null,
            'allowed_section_tests' => !empty($data['allowed_section_tests']) ? $data['allowed_section_tests'] : null,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'due_amount' => max(0, $total - $paid),
            'payment_status' => $paid >= $total ? self::PAYMENT_PAID
                : ($paid > 0 ? self::PAYMENT_PARTIAL : self::PAYMENT_PENDING),
            'payment_method' => $data['payment_method'] ?? null,
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addDays((int) $data['validity_days'])->toDateString(),
            'enrolled_by' => auth()->id(),
            'status' => self::STATUS_ACTIVE,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Create enrollment from a configured Batch.
     * Batch holds all test config; student just picks batch + evaluation type.
     */
    public static function createFromBatch(
        int $userId,
        int $branchId,
        string $studentId,
        Batch $batch,
        string $evaluationType = self::EVALUATION_AI,
        ?string $notes = null,
    ): self {
        $sectionTestLimits = $batch->section_test_limits ?? [
            'listening' => 0, 'reading' => 0, 'writing' => 0, 'speaking' => 0,
        ];
        $totalSectionTests = array_sum($sectionTestLimits);

        return self::create([
            'user_id' => $userId,
            'branch_id' => $branchId,
            'batch_id' => $batch->id,
            'student_id' => $studentId,
            'full_tests_allowed' => $batch->full_tests_allowed,
            'full_tests_taken' => 0,
            'section_tests_allowed' => $totalSectionTests,
            'section_tests_taken' => 0,
            'section_test_limits' => $sectionTestLimits,
            'section_tests_taken_by_type' => [
                'listening' => 0, 'reading' => 0, 'writing' => 0, 'speaking' => 0,
            ],
            'evaluation_type' => $evaluationType,
            'allowed_full_tests' => $batch->allowed_full_tests,
            'allowed_section_tests' => $batch->allowed_section_tests,
            'total_amount' => 0,
            'paid_amount' => 0,
            'due_amount' => 0,
            'payment_status' => self::PAYMENT_PAID,
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addDays($batch->validity_days)->toDateString(),
            'enrolled_by' => auth()->id(),
            'status' => self::STATUS_ACTIVE,
            'notes' => $notes,
        ]);
    }

    /**
     * Create enrollment from bulk import (uses preset package).
     * Similar to createFromPackage but with different payment defaults.
     */
    public static function createFromImport(
        int $userId,
        int $branchId,
        string $studentId,
        OfflinePackage $package,
        string $evaluationType = self::EVALUATION_AI,
    ): self {
        $price = $package->getPriceForBranch($branchId);

        return self::create([
            'user_id' => $userId,
            'branch_id' => $branchId,
            'student_id' => $studentId,
            'full_tests_allowed' => $package->full_tests_allowed,
            'full_tests_taken' => 0,
            'section_tests_allowed' => $package->section_tests_allowed,
            'section_tests_taken' => 0,
            'section_test_limits' => null,
            'section_tests_taken_by_type' => [
                'listening' => 0, 'reading' => 0, 'writing' => 0, 'speaking' => 0,
            ],
            'evaluation_type' => $evaluationType,
            'allowed_full_tests' => null,
            'allowed_section_tests' => null,
            'total_amount' => $price,
            'paid_amount' => $price,
            'due_amount' => 0,
            'payment_status' => self::PAYMENT_PAID,
            'payment_method' => 'bulk_import',
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addDays($package->validity_days)->toDateString(),
            'enrolled_by' => auth()->id(),
            'status' => self::STATUS_ACTIVE,
            'notes' => 'Imported via bulk import',
        ]);
    }

    // =====================
    // Relationships
    // =====================

    /**
     * Get the student (user)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for user - more readable
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the batch
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Batch::class);
    }

    /**
     * Get the staff who enrolled this student
     */
    public function enrolledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    /**
     * Get all test assignments for this enrollment
     */
    public function testAssignments()
    {
        return $this->hasMany(EnrollmentTestAssignment::class);
    }

    /**
     * Get available test assignments (not expired, not completed)
     */
    public function availableTestAssignments()
    {
        return $this->hasMany(EnrollmentTestAssignment::class)
            ->where('status', EnrollmentTestAssignment::STATUS_AVAILABLE)
            ->where('valid_until', '>=', now()->toDateString());
    }

    // =====================
    // Scopes
    // =====================

    /**
     * Active enrollments only
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Valid (not expired) enrollments
     */
    public function scopeValid($query)
    {
        return $query->where('valid_until', '>=', now()->toDateString());
    }

    /**
     * Expired enrollments
     */
    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now()->toDateString());
    }

    /**
     * With remaining tests
     */
    public function scopeWithRemainingTests($query)
    {
        return $query->whereColumn('full_tests_taken', '<', 'full_tests_allowed');
    }

    /**
     * Payment pending
     */
    public function scopePaymentPending($query)
    {
        return $query->whereIn('payment_status', [self::PAYMENT_PENDING, self::PAYMENT_PARTIAL]);
    }

    // =====================
    // Helper Methods
    // =====================

    /**
     * Check if enrollment is currently valid
     */
    public function isValid(): bool
    {
        $today = now()->startOfDay();

        return $this->status === self::STATUS_ACTIVE
            && $this->valid_until->startOfDay()->gte($today)
            && $this->valid_from->startOfDay()->lte($today);
    }

    /**
     * Check if can take more full tests
     */
    public function canTakeFullTest(): bool
    {
        return $this->isValid() && $this->full_tests_taken < $this->full_tests_allowed;
    }

    /**
     * Check if can take more section tests (legacy total check)
     */
    public function canTakeSectionTest(): bool
    {
        return $this->isValid() && $this->section_tests_taken < $this->section_tests_allowed;
    }

    /**
     * Check if can take a section test of a specific type (listening, reading, writing, speaking)
     */
    public function canTakeSectionTestOfType(string $sectionType): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $limits = $this->section_test_limits;
        $taken = $this->section_tests_taken_by_type;

        // If no per-section limits set, fall back to total limit
        if (empty($limits)) {
            return $this->canTakeSectionTest();
        }

        $sectionType = strtolower($sectionType);
        $limit = $limits[$sectionType] ?? 0;
        $used = $taken[$sectionType] ?? 0;

        return $used < $limit;
    }

    /**
     * Get remaining section tests for a specific type
     */
    public function getRemainingSectionTestsOfType(string $sectionType): int
    {
        $limits = $this->section_test_limits;
        $taken = $this->section_tests_taken_by_type;

        if (empty($limits)) {
            return $this->remaining_section_tests;
        }

        $sectionType = strtolower($sectionType);
        $limit = $limits[$sectionType] ?? 0;
        $used = $taken[$sectionType] ?? 0;

        return max(0, $limit - $used);
    }

    /**
     * Get the section test limit for a specific type
     */
    public function getSectionTestLimit(string $sectionType): int
    {
        $limits = $this->section_test_limits;
        if (empty($limits)) {
            return 0;
        }
        return $limits[strtolower($sectionType)] ?? 0;
    }

    /**
     * Get the section tests taken count for a specific type
     */
    public function getSectionTestsTaken(string $sectionType): int
    {
        $taken = $this->section_tests_taken_by_type;
        if (empty($taken)) {
            return 0;
        }
        return $taken[strtolower($sectionType)] ?? 0;
    }

    /**
     * Increment section test count for a specific type (with atomic locking).
     * Prefer TestAccessService::consumeSectionTestQuota() for race-condition safety.
     * NOTE: Does NOT auto-mark as completed — that's checked on next access.
     */
    public function incrementSectionTestCountForType(string $sectionType): void
    {
        $sectionType = strtolower($sectionType);

        \Illuminate\Support\Facades\DB::transaction(function () use ($sectionType) {
            $locked = self::where('id', $this->id)->lockForUpdate()->first();
            if (!$locked) return;

            $taken = $locked->section_tests_taken_by_type ?? [
                'listening' => 0, 'reading' => 0, 'writing' => 0, 'speaking' => 0,
            ];
            $taken[$sectionType] = ($taken[$sectionType] ?? 0) + 1;

            $locked->update([
                'section_tests_taken_by_type' => $taken,
                'section_tests_taken' => $locked->section_tests_taken + 1,
            ]);

            $this->refresh();
        });
    }

    /**
     * Check if enrollment has per-section limits configured
     */
    public function hasPerSectionLimits(): bool
    {
        $limits = $this->section_test_limits;
        if (empty($limits)) {
            return false;
        }
        return array_sum($limits) > 0;
    }

    /**
     * Get total section tests allowed (sum of per-section limits)
     */
    public function getTotalSectionTestsFromLimits(): int
    {
        $limits = $this->section_test_limits;
        if (empty($limits)) {
            return $this->section_tests_allowed ?? 0;
        }
        return array_sum($limits);
    }

    /**
     * Get remaining full tests
     */
    public function getRemainingFullTestsAttribute(): int
    {
        return max(0, $this->full_tests_allowed - $this->full_tests_taken);
    }

    /**
     * Get remaining section tests
     */
    public function getRemainingSectionTestsAttribute(): int
    {
        return max(0, $this->section_tests_allowed - $this->section_tests_taken);
    }

    /**
     * Get days remaining
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->valid_until < now()) {
            return 0;
        }
        return now()->diffInDays($this->valid_until);
    }

    /**
     * Check if expired
     */
    public function isExpired(): bool
    {
        return $this->valid_until < now()->toDateString();
    }

    /**
     * Increment full test count (with atomic locking).
     * Prefer TestAccessService::consumeFullTestQuota() for race-condition safety.
     * NOTE: Does NOT auto-mark as completed — that's checked on next access via canTakeFullTest().
     */
    public function incrementFullTestCount(): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () {
            $locked = self::where('id', $this->id)->lockForUpdate()->first();
            if (!$locked) return;

            $locked->increment('full_tests_taken');
            $this->refresh();
        });
    }

    /**
     * Check if ALL test quotas (full + section) are exhausted
     */
    public function isAllTestsExhausted(): bool
    {
        // Check full tests
        $fullTestsDone = $this->full_tests_taken >= $this->full_tests_allowed;

        // Check section tests
        if ($this->hasPerSectionLimits()) {
            $limits = $this->section_test_limits ?? [];
            $taken = $this->section_tests_taken_by_type ?? [];
            $sectionTestsDone = true;
            foreach ($limits as $type => $limit) {
                if ($limit > 0 && ($taken[$type] ?? 0) < $limit) {
                    $sectionTestsDone = false;
                    break;
                }
            }
        } else {
            // Legacy: use total section_tests_allowed
            $sectionTestsDone = $this->section_tests_allowed <= 0 || $this->section_tests_taken >= $this->section_tests_allowed;
        }

        return $fullTestsDone && $sectionTestsDone;
    }

    /**
     * Increment section test count (with atomic locking).
     * Prefer TestAccessService::consumeSectionTestQuota() for race-condition safety.
     * NOTE: Does NOT auto-mark as completed — that's checked on next access.
     */
    public function incrementSectionTestCount(): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () {
            $locked = self::where('id', $this->id)->lockForUpdate()->first();
            if (!$locked) return;

            $locked->increment('section_tests_taken');
            $this->refresh();
        });
    }

    /**
     * Record payment
     */
    public function recordPayment(float $amount, string $method = 'cash', ?string $notes = null): void
    {
        $this->paid_amount += $amount;
        $this->due_amount = max(0, $this->total_amount - $this->paid_amount);

        if ($this->due_amount <= 0) {
            $this->payment_status = self::PAYMENT_PAID;
        } else {
            $this->payment_status = self::PAYMENT_PARTIAL;
        }

        $this->payment_method = $method;

        if ($notes) {
            $this->payment_notes = ($this->payment_notes ? $this->payment_notes . "\n" : '')
                . "[" . now()->format('Y-m-d H:i') . "] {$notes}";
        }

        $this->save();
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'green',
            self::STATUS_INACTIVE => 'gray',
            self::STATUS_EXPIRED => 'red',
            self::STATUS_COMPLETED => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get payment status badge color
     */
    public function getPaymentColorAttribute(): string
    {
        return match($this->payment_status) {
            self::PAYMENT_PAID => 'green',
            self::PAYMENT_PARTIAL => 'yellow',
            self::PAYMENT_PENDING => 'red',
            self::PAYMENT_REFUNDED => 'gray',
            default => 'gray',
        };
    }

    /**
     * Auto-update status based on validity
     */
    public function updateStatusIfNeeded(): void
    {
        if ($this->status === self::STATUS_ACTIVE && $this->isExpired()) {
            $this->update(['status' => self::STATUS_EXPIRED]);
        }
    }

    // =====================
    // Evaluation Type Methods
    // =====================

    /**
     * Check if student can use AI evaluation
     */
    public function canUseAIEvaluation(): bool
    {
        return in_array($this->evaluation_type, [self::EVALUATION_AI, self::EVALUATION_BOTH]);
    }

    /**
     * Check if student can use Human evaluation
     */
    public function canUseHumanEvaluation(): bool
    {
        return in_array($this->evaluation_type, [self::EVALUATION_HUMAN, self::EVALUATION_BOTH]);
    }

    /**
     * Get evaluation type label
     */
    public function getEvaluationTypeLabelAttribute(): string
    {
        return match($this->evaluation_type) {
            self::EVALUATION_AI => 'AI Only',
            self::EVALUATION_HUMAN => 'Human Only',
            self::EVALUATION_BOTH => 'AI & Human',
            default => 'AI Only',
        };
    }

    /**
     * Get evaluation type badge color
     */
    public function getEvaluationTypeColorAttribute(): string
    {
        return match($this->evaluation_type) {
            self::EVALUATION_AI => 'blue',
            self::EVALUATION_HUMAN => 'purple',
            self::EVALUATION_BOTH => 'green',
            default => 'gray',
        };
    }

    // =====================
    // Allowed Tests Methods
    // =====================

    /**
     * Check if a specific full test is allowed for this enrollment
     * Uses EnrollmentTestAssignment as primary source when assignments exist,
     * falls back to allowed_full_tests JSON array for backwards compatibility.
     */
    public function canAccessFullTest(int $fullTestId): bool
    {
        // If test assignments exist, use them as the source of truth
        if ($this->testAssignments()->exists()) {
            return $this->testAssignments()
                ->where('full_test_id', $fullTestId)
                ->where('status', EnrollmentTestAssignment::STATUS_AVAILABLE)
                ->where('valid_until', '>=', now()->toDateString())
                ->exists();
        }

        // null = never configured (legacy) - allow all offline tests
        if (is_null($this->allowed_full_tests)) {
            return true;
        }

        // [] = explicitly emptied (all tests removed) - allow nothing
        if (empty($this->allowed_full_tests)) {
            return false;
        }

        return in_array($fullTestId, $this->allowed_full_tests);
    }

    /**
     * Get the allowed full tests as a collection
     */
    public function getAllowedFullTestsCollection()
    {
        // null = never configured (legacy) - return all offline tests
        if (is_null($this->allowed_full_tests)) {
            return FullTest::where('is_for_offline', true)->where('active', true)->get();
        }

        // [] = explicitly emptied - return empty collection
        if (empty($this->allowed_full_tests)) {
            return collect();
        }

        return FullTest::whereIn('id', $this->allowed_full_tests)
            ->where('is_for_offline', true)
            ->where('active', true)
            ->get();
    }

    /**
     * Check if enrollment has specific test restrictions
     * Returns true if allowed_full_tests was explicitly set (even to empty array [])
     * null = never configured (legacy - no restrictions)
     * [] = explicitly emptied (all tests removed - restrict to nothing)
     * [1,2,3] = specific tests allowed
     */
    public function hasTestRestrictions(): bool
    {
        return !is_null($this->allowed_full_tests);
    }

    /**
     * Check if this enrollment allows access to a specific section test (TestSet).
     * null = all allowed (legacy/no restriction), [] = none, [ids] = only those.
     */
    public function canAccessSectionTest(int $testSetId): bool
    {
        if (is_null($this->allowed_section_tests)) {
            return true;
        }
        if (empty($this->allowed_section_tests)) {
            return false;
        }
        return in_array($testSetId, $this->allowed_section_tests);
    }

    public function hasSectionTestRestrictions(): bool
    {
        return !is_null($this->allowed_section_tests);
    }

    /**
     * Get count of allowed tests (null means unlimited/all)
     */
    public function getAllowedTestsCountAttribute(): ?int
    {
        // null = never configured (legacy) - unlimited
        if (is_null($this->allowed_full_tests)) {
            return null;
        }

        // [] or [1,2,3] - return actual count (0 or more)
        return count($this->allowed_full_tests);
    }

    // =====================
    // Renewal Methods
    // =====================

    /**
     * Get all previously completed full test IDs (combines current + historical)
     */
    public function getAllPreviouslyCompletedFullTests(): array
    {
        return $this->previously_completed_full_tests ?? [];
    }

    /**
     * Get all previously completed section test IDs
     */
    public function getAllPreviouslyCompletedSectionTests(): array
    {
        return $this->previously_completed_section_tests ?? [];
    }

    /**
     * Check if a full test was previously completed (before renewal)
     */
    public function wasFullTestPreviouslyCompleted(int $fullTestId): bool
    {
        $previouslyCompleted = $this->previously_completed_full_tests ?? [];
        return in_array($fullTestId, $previouslyCompleted);
    }

    /**
     * Check if a section test was previously completed (before renewal)
     */
    public function wasSectionTestPreviouslyCompleted(int $testSetId): bool
    {
        $previouslyCompleted = $this->previously_completed_section_tests ?? [];
        return in_array($testSetId, $previouslyCompleted);
    }

    /**
     * Get currently completed full test IDs (from current enrollment period)
     */
    public function getCurrentCompletedFullTestIds(): array
    {
        return FullTestAttempt::where('user_id', $this->user_id)
            ->where('status', 'completed')
            ->pluck('full_test_id')
            ->unique()
            ->toArray();
    }

    /**
     * Get currently completed section test IDs
     */
    public function getCurrentCompletedSectionTestIds(): array
    {
        return StudentAttempt::where('user_id', $this->user_id)
            ->where('status', 'completed')
            ->doesntHave('fullTestSectionAttempt') // Only standalone section tests
            ->pluck('test_set_id')
            ->unique()
            ->toArray();
    }

    /**
     * Renew the enrollment package
     * - Captures all currently completed tests
     * - Merges with previously completed tests
     * - Resets counters
     * - Updates validity
     */
    public function renewPackage(array $renewalData): self
    {
        // Capture currently completed tests before reset
        $currentFullTests = $this->getCurrentCompletedFullTestIds();
        $currentSectionTests = $this->getCurrentCompletedSectionTestIds();

        // Merge with previously completed tests (remove duplicates)
        $allPreviousFullTests = array_unique(array_merge(
            $this->previously_completed_full_tests ?? [],
            $currentFullTests
        ));

        $allPreviousSectionTests = array_unique(array_merge(
            $this->previously_completed_section_tests ?? [],
            $currentSectionTests
        ));

        // Update enrollment
        $this->update([
            // Store completed tests history
            'previously_completed_full_tests' => array_values($allPreviousFullTests),
            'previously_completed_section_tests' => array_values($allPreviousSectionTests),

            // Reset counters (including per-section tracking)
            'full_tests_taken' => 0,
            'section_tests_taken' => 0,
            'section_tests_taken_by_type' => [
                'listening' => 0, 'reading' => 0, 'writing' => 0, 'speaking' => 0,
            ],

            // New package details
            'full_tests_allowed' => $renewalData['full_tests_allowed'] ?? $this->full_tests_allowed,
            'section_tests_allowed' => $renewalData['section_tests_allowed'] ?? $this->section_tests_allowed,
            'section_test_limits' => $renewalData['section_test_limits'] ?? $this->section_test_limits,
            'evaluation_type' => $renewalData['evaluation_type'] ?? $this->evaluation_type,
            'allowed_full_tests' => $renewalData['allowed_full_tests'] ?? $this->allowed_full_tests,
            'allowed_section_tests' => $renewalData['allowed_section_tests'] ?? $this->allowed_section_tests,

            // Validity - use specific date if provided, otherwise calculate from days
            'valid_from' => now()->toDateString(),
            'valid_until' => !empty($renewalData['valid_until'])
                ? $renewalData['valid_until']
                : now()->addDays((int) ($renewalData['validity_days'] ?? 30))->toDateString(),

            // Status
            'status' => self::STATUS_ACTIVE,

            // Renewal tracking
            'renewal_count' => ($this->renewal_count ?? 0) + 1,
            'last_renewed_at' => now(),

            // Payment (optional)
            'total_amount' => $renewalData['total_amount'] ?? 0,
            'paid_amount' => $renewalData['paid_amount'] ?? 0,
            'due_amount' => ($renewalData['total_amount'] ?? 0) - ($renewalData['paid_amount'] ?? 0),
            'payment_status' => ($renewalData['paid_amount'] ?? 0) >= ($renewalData['total_amount'] ?? 0)
                ? self::PAYMENT_PAID
                : (($renewalData['paid_amount'] ?? 0) > 0 ? self::PAYMENT_PARTIAL : self::PAYMENT_PENDING),
        ]);

        return $this->fresh();
    }

    /**
     * Check if student has ever renewed
     */
    public function hasBeenRenewed(): bool
    {
        return ($this->renewal_count ?? 0) > 0;
    }

    /**
     * Get total tests ever completed (current + historical)
     */
    public function getTotalFullTestsEverCompletedAttribute(): int
    {
        $previousCount = count($this->previously_completed_full_tests ?? []);
        return $previousCount + $this->full_tests_taken;
    }

    /**
     * Check if a test can be started (valid enrollment, quota remaining, not completed)
     */
    public function canStartFullTest(int $fullTestId): bool
    {
        // Check enrollment is valid and has remaining quota
        if (!$this->canTakeFullTest()) {
            return false;
        }

        // Check if allowed by package
        if (!$this->canAccessFullTest($fullTestId)) {
            return false;
        }

        // Check if previously completed
        if ($this->wasFullTestPreviouslyCompleted($fullTestId)) {
            return false;
        }

        // Check if currently completed (in this enrollment period)
        $currentlyCompleted = $this->getCurrentCompletedFullTestIds();
        if (in_array($fullTestId, $currentlyCompleted)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a section test can be started
     */
    public function canStartSectionTest(int $testSetId): bool
    {
        // Check enrollment is valid and has remaining quota
        if (!$this->canTakeSectionTest()) {
            return false;
        }

        // Check if previously completed
        if ($this->wasSectionTestPreviouslyCompleted($testSetId)) {
            return false;
        }

        // Check if currently completed
        $currentlyCompleted = $this->getCurrentCompletedSectionTestIds();
        if (in_array($testSetId, $currentlyCompleted)) {
            return false;
        }

        return true;
    }

    /**
     * Add new tests renewal - keeps old tests validity, adds new tests with new validity
     *
     * This is different from renewPackage() because:
     * - Old tests remain with their original validity
     * - New tests are added with new validity period
     * - Counters are NOT reset - they increase
     */
    public function addNewTestsRenewal(array $renewalData): self
    {
        // Calculate new validity date - use specific date if provided
        if (!empty($renewalData['valid_until'])) {
            $newValidUntil = \Carbon\Carbon::parse($renewalData['valid_until']);
        } else {
            $newValidUntil = now()->addDays((int) ($renewalData['validity_days'] ?? 30));
        }

        // If current validity is still valid, extend from that date
        // Otherwise extend from today
        if ($this->valid_until && $this->valid_until->isFuture()) {
            // Current enrollment still valid - new tests get new validity from today
            // But we extend the overall validity to the later date
            $newValidUntil = max($this->valid_until, $newValidUntil);
        }

        // Get current and new allowed tests
        $currentAllowedTests = $this->allowed_full_tests ?? [];
        $newAllowedTests = $renewalData['allowed_full_tests'] ?? [];

        // Merge allowed tests arrays - only add truly NEW tests
        $mergedAllowedTests = !empty($newAllowedTests)
            ? array_values(array_unique(array_merge($currentAllowedTests, $newAllowedTests)))
            : $currentAllowedTests;

        // Calculate how many NEW tests are being added (not already in the list)
        $trulyNewTests = array_diff($newAllowedTests, $currentAllowedTests);
        $newTestsCount = count($trulyNewTests);

        // Add only NEW tests count to existing (not the input value which might include existing)
        $newFullTestsAllowed = $this->full_tests_allowed + $newTestsCount;
        $newSectionTestsAllowed = $this->section_tests_allowed + ($renewalData['section_tests_allowed'] ?? 0);

        // Update enrollment
        $this->update([
            // Add only new tests count to existing allowances
            'full_tests_allowed' => $newFullTestsAllowed,
            'section_tests_allowed' => $newSectionTestsAllowed,

            // NOTE: We do NOT reset taken counts - they remain
            // This way student keeps their progress but gets more tests

            // Update evaluation type if changed
            'evaluation_type' => $renewalData['evaluation_type'] ?? $this->evaluation_type,

            // Merge allowed tests - keep array even if empty (don't set to null)
            'allowed_full_tests' => $mergedAllowedTests,
            'allowed_section_tests' => $renewalData['allowed_section_tests'] ?? $this->allowed_section_tests,

            // Extend validity to the later date
            'valid_until' => $newValidUntil->toDateString(),

            // Reactivate if expired
            'status' => self::STATUS_ACTIVE,

            // Track renewal
            'renewal_count' => ($this->renewal_count ?? 0) + 1,
            'last_renewed_at' => now(),

            // Payment handling
            'total_amount' => $this->total_amount + ($renewalData['total_amount'] ?? 0),
            'paid_amount' => $this->paid_amount + ($renewalData['paid_amount'] ?? 0),
            'due_amount' => ($this->total_amount + ($renewalData['total_amount'] ?? 0))
                          - ($this->paid_amount + ($renewalData['paid_amount'] ?? 0)),
            'payment_status' => $this->calculatePaymentStatus(
                $this->total_amount + ($renewalData['total_amount'] ?? 0),
                $this->paid_amount + ($renewalData['paid_amount'] ?? 0)
            ),
        ]);

        return $this->fresh();
    }

    /**
     * Calculate payment status based on amounts
     */
    protected function calculatePaymentStatus(float $totalAmount, float $paidAmount): string
    {
        if ($totalAmount <= 0 || $paidAmount >= $totalAmount) {
            return self::PAYMENT_PAID;
        }
        if ($paidAmount > 0) {
            return self::PAYMENT_PARTIAL;
        }
        return self::PAYMENT_PENDING;
    }

    // =====================
    // Retake Methods
    // =====================

    /**
     * Check if the branch allows test retakes for its students
     */
    public function branchAllowsRetakes(): bool
    {
        return $this->branch && $this->branch->allow_test_retakes;
    }

    /**
     * Check if starting this full test would be a retake (already completed once in current period)
     */
    public function isFullTestRetake(int $fullTestId): bool
    {
        return in_array($fullTestId, $this->getCurrentCompletedFullTestIds());
    }

    /**
     * Check if starting this section test would be a retake (already completed once in current period)
     */
    public function isSectionTestRetake(int $testSetId): bool
    {
        return in_array($testSetId, $this->getCurrentCompletedSectionTestIds());
    }
}
