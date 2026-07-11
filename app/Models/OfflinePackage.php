<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfflinePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'full_tests_allowed',
        'section_tests_allowed',
        'validity_days',
        'price',
        'branch_id',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'full_tests_allowed' => 'integer',
        'section_tests_allowed' => 'integer',
        'validity_days' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Get the branch this package belongs to (if branch-specific)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get branch-specific price overrides
     */
    public function branchPrices(): HasMany
    {
        return $this->hasMany(BranchPackagePrice::class, 'package_id');
    }

    /**
     * Check if this is a global package (available to all branches)
     */
    public function isGlobal(): bool
    {
        return is_null($this->branch_id);
    }

    /**
     * Get the price for a specific branch
     * Returns custom price if set, otherwise base price
     */
    public function getPriceForBranch(?int $branchId): float
    {
        if (!$branchId) {
            return (float) $this->price;
        }

        $branchPrice = $this->branchPrices()
            ->where('branch_id', $branchId)
            ->where('is_available', true)
            ->first();

        return $branchPrice ? (float) $branchPrice->custom_price : (float) $this->price;
    }

    /**
     * Check if package is available for a specific branch
     */
    public function isAvailableForBranch(?int $branchId): bool
    {
        // If it's a branch-specific package, only that branch can use it
        if ($this->branch_id) {
            return $this->branch_id === $branchId;
        }

        // For global packages, check if there's a specific disable for this branch
        if ($branchId) {
            $branchPrice = $this->branchPrices()
                ->where('branch_id', $branchId)
                ->first();

            // If there's a branch price entry and it's disabled, package is not available
            if ($branchPrice && !$branchPrice->is_available) {
                return false;
            }
        }

        return $this->is_active;
    }

    /**
     * Scope for active packages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for global packages (no branch_id)
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('branch_id');
    }

    /**
     * Scope for packages available to a specific branch
     */
    public function scopeAvailableForBranch($query, ?int $branchId)
    {
        return $query->where('is_active', true)
            ->where(function ($q) use ($branchId) {
                // Global packages (branch_id is null)
                $q->whereNull('branch_id')
                    // OR branch-specific packages for this branch
                    ->orWhere('branch_id', $branchId);
            });
    }

    /**
     * Get packages for a branch with proper pricing
     */
    public static function getPackagesForBranch(int $branchId): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
            ->availableForBranch($branchId)
            ->with(['branchPrices' => function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            }])
            ->orderBy('display_order')
            ->orderBy('price')
            ->get()
            ->filter(function ($package) use ($branchId) {
                return $package->isAvailableForBranch($branchId);
            })
            ->values();
    }
}
