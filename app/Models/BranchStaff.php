<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchStaff extends Model
{
    use HasFactory;

    protected $table = 'branch_staff';

    protected $fillable = [
        'branch_id',
        'user_id',
        'role',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Available roles
    public const ROLE_ADMIN = 'admin';
    public const ROLE_STAFF = 'staff';
    public const ROLE_RECEPTIONIST = 'receptionist';

    public const ROLES = [
        self::ROLE_ADMIN => 'Branch Admin',
        self::ROLE_STAFF => 'Staff',
        self::ROLE_RECEPTIONIST => 'Receptionist',
    ];

    // =====================
    // Relationships
    // =====================

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user (staff member)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =====================
    // Scopes
    // =====================

    /**
     * Active staff only
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Filter by role
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Admins only
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    // =====================
    // Helper Methods
    // =====================

    /**
     * Get role display name
     */
    public function getRoleNameAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    /**
     * Check if this is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if can manage students
     */
    public function canManageStudents(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_STAFF]);
    }

    /**
     * Check if can view reports
     */
    public function canViewReports(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
}
