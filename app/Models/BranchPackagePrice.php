<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchPackagePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'branch_id',
        'custom_price',
        'is_available',
    ];

    protected $casts = [
        'custom_price' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    /**
     * Get the package this price belongs to
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(OfflinePackage::class, 'package_id');
    }

    /**
     * Get the branch this price is for
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
