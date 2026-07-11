<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchCreditTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'type',
        'amount',
        'balance_after',
        'reason',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'metadata' => 'array',
    ];

    /**
     * Get the branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who triggered this transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is a credit (add) transaction
     */
    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }

    /**
     * Check if this is a debit (subtract) transaction
     */
    public function isDebit(): bool
    {
        return $this->type === 'debit';
    }

    /**
     * Get human-readable reason
     */
    public function getReasonLabelAttribute(): string
    {
        return match($this->reason) {
            'writing_evaluation' => 'Writing AI Evaluation',
            'speaking_evaluation' => 'Speaking AI Evaluation',
            'admin_topup' => 'Admin Top-up',
            'refund' => 'Refund',
            'adjustment' => 'Balance Adjustment',
            'bonus' => 'Bonus Credits',
            default => ucfirst(str_replace('_', ' ', $this->reason)),
        };
    }

    /**
     * Get amount in BDT
     */
    public function getAmountInBdtAttribute(): float
    {
        return round($this->amount * 120, 2);
    }

    /**
     * Scope for credits only
     */
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    /**
     * Scope for debits only
     */
    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    /**
     * Scope by reason
     */
    public function scopeByReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }
}
