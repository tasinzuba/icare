<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'phone',
        'email',
        'description',
        'active',
        'allow_test_retakes',
        'order_number',
    ];

    protected $casts = [
        'active' => 'boolean',
        'allow_test_retakes' => 'boolean',
    ];

    // =====================
    // Relationships
    // =====================

    /**
     * Get staff members of this branch
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_staff')
            ->withPivot(['role', 'active'])
            ->withTimestamps();
    }

    /**
     * Get branch staff records
     */
    public function branchStaff(): HasMany
    {
        return $this->hasMany(BranchStaff::class);
    }

    /**
     * Get active staff members
     */
    public function activeStaff(): BelongsToMany
    {
        return $this->staff()->wherePivot('active', true);
    }

    /**
     * Get admins of this branch
     */
    public function admins(): BelongsToMany
    {
        return $this->staff()->wherePivot('role', 'admin');
    }

    /**
     * Get all students enrolled in this branch
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class)->where('student_type', 'offline');
    }

    /**
     * Get enrollments for this branch
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(OfflineEnrollment::class);
    }

    /**
     * Get activity logs for this branch
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(BranchActivityLog::class);
    }

    /**
     * Get credit account for this branch
     */
    public function creditAccount()
    {
        return $this->hasOne(BranchCredit::class);
    }

    /**
     * Get credit transactions for this branch
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(BranchCreditTransaction::class);
    }

    /**
     * Get or create credit account
     */
    public function getOrCreateCreditAccount(): BranchCredit
    {
        return BranchCredit::getOrCreate($this->id);
    }

    /**
     * Get active enrollments
     */
    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', 'active');
    }

    // =====================
    // Scopes
    // =====================

    /**
     * Active branches only
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Order by order_number
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_number');
    }

    // =====================
    // Helper Methods
    // =====================

    /**
     * Generate next student ID for this branch
     * Format: CODE-YEAR-SERIAL (e.g., DHK-2025-0001)
     * Uses database locking to prevent race conditions
     */
    public function generateStudentId(): string
    {
        return \DB::transaction(function () {
            $year = date('Y');
            $prefix = $this->code . '-' . $year . '-';

            // Lock the row to prevent race conditions
            $lastEnrollment = OfflineEnrollment::where('branch_id', $this->id)
                ->where('student_id', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByRaw("CAST(SUBSTRING(student_id, -4) AS UNSIGNED) DESC")
                ->first();

            if ($lastEnrollment) {
                // Extract the serial number and increment
                $lastSerial = (int) substr($lastEnrollment->student_id, -4);
                $newSerial = $lastSerial + 1;
            } else {
                $newSerial = 1;
            }

            $studentId = $prefix . str_pad($newSerial, 4, '0', STR_PAD_LEFT);

            // Double-check uniqueness
            $maxRetries = 5;
            $retry = 0;
            while (OfflineEnrollment::where('student_id', $studentId)->exists() && $retry < $maxRetries) {
                $newSerial++;
                $studentId = $prefix . str_pad($newSerial, 4, '0', STR_PAD_LEFT);
                $retry++;
            }

            return $studentId;
        });
    }

    /**
     * Get total students count (active enrollments)
     */
    public function getActiveStudentsCountAttribute(): int
    {
        return $this->activeEnrollments()->count();
    }

    /**
     * Get total tests taken today
     */
    public function getTodayTestsCountAttribute(): int
    {
        return $this->enrollments()
            ->join('users', 'offline_enrollments.user_id', '=', 'users.id')
            ->join('student_attempts', 'users.id', '=', 'student_attempts.user_id')
            ->whereDate('student_attempts.created_at', today())
            ->count();
    }

    /**
     * Check if user is staff of this branch
     */
    public function hasStaff(User $user): bool
    {
        return $this->staff()->where('users.id', $user->id)->exists();
    }

    /**
     * Check if user is admin of this branch
     */
    public function hasAdmin(User $user): bool
    {
        return $this->admins()->where('users.id', $user->id)->exists();
    }
}
