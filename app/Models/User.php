<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\StudentAttempt;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;



class User extends Authenticatable
{
   use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        // SECURITY (C7): 'is_admin' and 'role_id' are intentionally NOT mass-assignable.
        // They are set only via User::assignRole()/removeRole() or the admin user flow using
        // explicit property assignment, so no fill()/create()/update() can escalate privileges.
        'student_type',
        'branch_id',
        'tests_taken_this_month',
        'ai_evaluations_used',
        'phone_number',
        'phone_verified_at',
        'google_id',
        'facebook_id',
        'avatar_url',
        'login_method',
        'country_code',
        'country_name',
        'city',
        'timezone',
        'currency',
        'is_social_signup',
        'avatar_url',
        'banned_at',
        'ban_reason',
        'ban_type',
        'ban_expires_at',
        'banned_by',
        'created_by',
        'last_login_at',
        'onboarding_completed',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'tests_taken_this_month' => 'integer',
        'ai_evaluations_used' => 'integer',
        'phone_verified_at' => 'datetime',
        'is_social_signup' => 'boolean',
        'banned_at' => 'datetime',
        'ban_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
        'onboarding_completed' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * SECURITY (C5/C6): without this, the bcrypt password hash and remember_token
     * were serialized into every Inertia response (page.props.auth.user) and any
     * User->toArray()/toJson() call, leaking to the browser. Social provider ids are
     * hidden too as they are internal-only.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
        'facebook_id',
    ];


public function devices()
{
    return $this->hasMany(UserDevice::class);
}

public function otpVerifications()
{
    return $this->hasMany(OtpVerification::class, 'identifier', 'email');
}

public function hasVerifiedPhone(): bool
{
    return !is_null($this->phone_verified_at);
}

public function markPhoneAsVerified(): void
{
    $this->update(['phone_verified_at' => now()]);
}

public function getCountryFlagAttribute(): string
{
    return $this->country_code 
        ? "https://flagcdn.com/w40/" . strtolower($this->country_code) . ".png"
        : '';
}

public function trustedDevices()
{
    return $this->devices()->where('is_trusted', true);
}

public function hasTrustedDevice(string $fingerprint): bool
{
    return $this->trustedDevices()->where('device_fingerprint', $fingerprint)->exists();
}

     public function attempts(): HasMany
    {
        return $this->hasMany(StudentAttempt::class);
    }

    /**
     * Get user's full test attempts.
     */
    public function fullTestAttempts(): HasMany
    {
        return $this->hasMany(FullTestAttempt::class);
    }


    /**
     * Check if user has access to a feature.
     * Subscription system removed — offline students use enrollment, online users always granted.
     */
    public function hasFeature(string $featureKey): bool
    {
        if ($this->isOfflineStudent()) {
            $enrollment = $this->getActiveEnrollment();

            if (in_array($featureKey, ['ai_writing_evaluation', 'ai_speaking_evaluation'])) {
                return $enrollment ? $enrollment->canUseAIEvaluation() : false;
            }

            if ($featureKey === 'human_evaluation') {
                return $enrollment ? $enrollment->canUseHumanEvaluation() : false;
            }

            return $enrollment !== null;
        }

        return true;
    }

    /**
     * Get feature limit. Always unlimited now (subscription system removed).
     */
    public function getFeatureLimit(string $featureKey)
    {
        return 'unlimited';
    }

    /**
     * Get monthly test limit.
     */
    public function getMonthlyTestLimit()
    {
        return 'unlimited';
    }

    /**
     * Test usage percentage — always 0 (no limit).
     */
    public function getTestUsagePercentage(): float
    {
        return 0;
    }

    /**
     * Check if user can take more tests this month.
     */
    public function canTakeMoreTests(): bool
    {
        if ($this->isOfflineStudent()) {
            $enrollment = $this->getActiveEnrollment();

            if (!$enrollment) {
                $enrollment = OfflineEnrollment::where('user_id', $this->id)
                    ->where('status', 'completed')
                    ->where('valid_until', '>=', now()->toDateString())
                    ->first();

                if ($enrollment && !$enrollment->isAllTestsExhausted()) {
                    $enrollment->update(['status' => OfflineEnrollment::STATUS_ACTIVE]);
                    $this->unsetRelation('activeOfflineEnrollment');
                    return true;
                }
            }

            return $enrollment !== null;
        }

        return true;
    }

    /**
     * Check if user can use AI evaluation.
     */
    public function canUseAIEvaluation(): bool
    {
        // For offline students, check their enrollment's evaluation_type
        if ($this->isOfflineStudent()) {
            $enrollment = $this->getActiveEnrollment();
            return $enrollment ? $enrollment->canUseAIEvaluation() : false;
        }

        return $this->hasFeature('ai_writing_evaluation') ||
               $this->hasFeature('ai_speaking_evaluation');
    }

    /**
     * Check if user can use Human evaluation.
     */
    public function canUseHumanEvaluation(): bool
    {
        // For offline students, check their enrollment's evaluation_type
        if ($this->isOfflineStudent()) {
            $enrollment = $this->getActiveEnrollment();
            return $enrollment ? $enrollment->canUseHumanEvaluation() : false;
        }

        // Online students can always request human evaluation (if they have tokens)
        return true;
    }

    /**
     * Get evaluation type label for offline students
     */
    public function getEvaluationTypeLabel(): ?string
    {
        if (!$this->isOfflineStudent()) {
            return null;
        }

        $enrollment = $this->getActiveEnrollment();
        return $enrollment ? $enrollment->evaluation_type_label : null;
    }

    /**
     * Increment test count.
     */
    public function incrementTestCount(): void
    {
        // For offline students, increment their enrollment test count
        if ($this->isOfflineStudent()) {
            $enrollment = $this->getActiveEnrollment();
            if ($enrollment) {
                $enrollment->incrementFullTestCount();
            }
        }

        // Also increment monthly counter for tracking
        $this->increment('tests_taken_this_month');
    }

    /**
     * Increment AI evaluation count.
     */
    public function incrementAIEvaluationCount(): void
    {
        $this->increment('ai_evaluations_used');
    }

    /**
     * Reset monthly counters.
     */
    public function resetMonthlyCounters(): void
    {
        $this->update([
            'tests_taken_this_month' => 0,
            'ai_evaluations_used' => 0,
        ]);
    }

    /**
     * Get subscription badge/label. Subscription removed — only offline branch label remains.
     */
    public function getSubscriptionBadgeAttribute(): array
    {
        if ($this->isOfflineStudent()) {
            return ['label' => 'Branch Student', 'class' => 'bg-indigo-100 text-indigo-800'];
        }

        return ['label' => 'Student', 'class' => 'bg-gray-100 text-gray-800'];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\CustomResetPassword($token));
    }
    
    /**
     * Teacher relationship
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }
    
    /**
     * Student attempts relationship
     */
    public function studentAttempts()
    {
        return $this->hasMany(StudentAttempt::class);
    }
    
    /**
     * Authentication logs relationship
     */
    public function authenticationLogs()
    {
        if (Schema::hasTable('authentication_log')) {
            return $this->hasMany(\Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog::class, 'authenticatable_id');
        }
        return $this->hasMany(\stdClass::class, 'id'); // Return empty relationship if table doesn't exist
    }
    
    /**
     * Check if user is banned
     */
    public function isBanned(): bool
    {
        if (is_null($this->banned_at)) {
            return false;
        }
        
        // Check if temporary ban has expired
        if ($this->ban_type === 'temporary' && $this->ban_expires_at && $this->ban_expires_at->isPast()) {
            // Auto-unban if temporary ban expired
            $this->unban();
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if ban is permanent
     */
    public function isPermanentlyBanned(): bool
    {
        return $this->isBanned() && $this->ban_type === 'permanent';
    }
    
    /**
     * Check if ban is temporary
     */
    public function isTemporarilyBanned(): bool
    {
        return $this->isBanned() && $this->ban_type === 'temporary';
    }
    
    /**
     * Get ban expiry date for temporary bans
     */
    public function getBanExpiryDate(): ?string
    {
        if ($this->isTemporarilyBanned() && $this->ban_expires_at) {
            return $this->ban_expires_at->format('F j, Y g:i A');
        }
        return null;
    }
    
    /**
     * Ban the user
     */
    public function ban(string $reason, string $type = 'temporary', $expiresAt = null, ?User $bannedBy = null): void
    {
        $banData = [
            'banned_at' => now(),
            'ban_reason' => $reason,
            'ban_type' => $type,
            'banned_by' => $bannedBy?->id
        ];
        
        if ($type === 'temporary') {
            if ($expiresAt instanceof \Carbon\Carbon) {
                $banData['ban_expires_at'] = $expiresAt;
            } elseif (is_string($expiresAt)) {
                $banData['ban_expires_at'] = Carbon::parse($expiresAt);
            } elseif (is_null($expiresAt)) {
                $banData['ban_expires_at'] = now()->addDays(7); // Default 7 days
            } else {
                $banData['ban_expires_at'] = $expiresAt;
            }
        } else {
            $banData['ban_expires_at'] = null;
        }
        
        $this->update($banData);
    }
    
    /**
     * Unban the user
     */
    public function unban(): void
    {
        $this->update([
            'banned_at' => null,
            'ban_reason' => null,
            'ban_type' => 'temporary',
            'ban_expires_at' => null,
            'banned_by' => null
        ]);
    }
    
    /**
     * Get the admin who banned this user
     */
    public function bannedBy()
    {
        return $this->belongsTo(User::class, 'banned_by');
    }
    
    /**
     * Get role display name
     */
    public function getRoleAttribute(): string
    {
        if ($this->is_admin) {
            return 'Admin';
        }
        
        if ($this->teacher) {
            return 'Teacher';
        }
        
        return 'Student';
    }
    
    /**
     * Get role badge color
     */
    public function getRoleBadgeColorAttribute(): string
    {
        if ($this->is_admin) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->teacher) {
            return 'bg-purple-100 text-purple-800';
        }
        
        return 'bg-blue-100 text-blue-800';
    }
    
    /**
     * Get user's role relationship
     */
    public function userRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
    
    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Admin has all permissions
        if ($this->is_admin) {
            return true;
        }
        
        // Check through role
        if ($this->userRole) {
            return $this->userRole->hasPermission($permissionSlug);
        }
        
        return false;
    }
    
    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Get all user permissions
     */
    public function getAllPermissions()
    {
        if ($this->is_admin) {
            return Permission::all();
        }
        
        if ($this->userRole) {
            return $this->userRole->permissions;
        }
        
        return collect([]);
    }
    
    /**
     * Assign role to user
     */
    public function assignRole(Role $role): void
    {
        // C7: role_id is guarded; property assignment bypasses $fillable safely.
        $this->role_id = $role->id;
        $this->save();
    }
    
    /**
     * Remove role from user
     */
    public function removeRole(): void
    {
        // C7: role_id is guarded; property assignment bypasses $fillable safely.
        $this->role_id = null;
        $this->save();
    }

    // =====================
    // Branch & Offline Student Relationships
    // =====================

    /**
     * Get the branch this offline student belongs to
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get branches where this user is staff
     */
    public function staffBranches()
    {
        return $this->belongsToMany(Branch::class, 'branch_staff')
            ->withPivot(['role', 'active'])
            ->withTimestamps();
    }

    /**
     * Get branch staff records for this user
     */
    public function branchStaffRecords()
    {
        return $this->hasMany(BranchStaff::class);
    }

    /**
     * Get offline enrollments for this user
     */
    public function offlineEnrollments()
    {
        return $this->hasMany(OfflineEnrollment::class);
    }

    /**
     * Get the latest offline enrollment (singular relationship for eager loading)
     */
    public function offlineEnrollment()
    {
        return $this->hasOne(OfflineEnrollment::class)->latest();
    }

    /**
     * Get active offline enrollment
     */
    public function activeOfflineEnrollment()
    {
        return $this->hasOne(OfflineEnrollment::class)
            ->where('status', 'active')
            ->where('valid_until', '>=', now()->toDateString())
            ->latest();
    }

    /**
     * Get the active enrollment instance
     */
    public function getActiveEnrollment(): ?OfflineEnrollment
    {
        return $this->activeOfflineEnrollment()->first();
    }

    /**
     * Check if user is an offline student
     */
    public function isOfflineStudent(): bool
    {
        return $this->student_type === 'offline';
    }

    /**
     * Check if user is a public (online) student
     */
    public function isPublicStudent(): bool
    {
        return $this->student_type === 'public';
    }

    /**
     * Check if user is branch staff (any branch)
     */
    public function isBranchStaff(): bool
    {
        return $this->branchStaffRecords()->where('active', true)->exists();
    }

    /**
     * Check if user is branch admin (any branch)
     */
    public function isBranchAdmin(): bool
    {
        return $this->branchStaffRecords()
            ->where('active', true)
            ->where('role', 'admin')
            ->exists();
    }

    /**
     * Get user's primary branch (for staff)
     */
    public function getPrimaryBranch(): ?Branch
    {
        $staffRecord = $this->branchStaffRecords()->where('active', true)->first();
        return $staffRecord?->branch;
    }

    /**
     * Check if user is staff of specific branch
     */
    public function isStaffOf(Branch $branch): bool
    {
        return $this->branchStaffRecords()
            ->where('branch_id', $branch->id)
            ->where('active', true)
            ->exists();
    }

    /**
     * Check if user is admin of specific branch
     */
    public function isAdminOf(Branch $branch): bool
    {
        return $this->branchStaffRecords()
            ->where('branch_id', $branch->id)
            ->where('role', 'admin')
            ->where('active', true)
            ->exists();
    }

    /**
     * Check if offline student can take more full tests
     */
    public function canTakeOfflineFullTest(): bool
    {
        if (!$this->isOfflineStudent()) {
            return false;
        }

        $enrollment = $this->getActiveEnrollment();
        return $enrollment && $enrollment->canTakeFullTest();
    }

    /**
     * Check if offline student can take more section tests
     */
    public function canTakeOfflineSectionTest(): bool
    {
        if (!$this->isOfflineStudent()) {
            return false;
        }

        $enrollment = $this->getActiveEnrollment();
        return $enrollment && $enrollment->canTakeSectionTest();
    }

    /**
     * Get remaining full tests for offline student
     */
    public function getRemainingOfflineFullTests(): int
    {
        $enrollment = $this->getActiveEnrollment();
        return $enrollment ? $enrollment->remaining_full_tests : 0;
    }

    /**
     * Get student ID (for offline students)
     */
    public function getOfflineStudentId(): ?string
    {
        $enrollment = $this->getActiveEnrollment();
        return $enrollment?->student_id;
    }
}