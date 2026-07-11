<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BranchCredit extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'balance',
        'total_purchased',
        'total_used',
    ];

    protected $casts = [
        'balance' => 'decimal:4',
        'total_purchased' => 'decimal:4',
        'total_used' => 'decimal:4',
    ];

    /**
     * Get the branch that owns this credit account
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get all transactions for this credit account
     */
    public function transactions()
    {
        return $this->hasMany(BranchCreditTransaction::class, 'branch_id', 'branch_id');
    }

    /**
     * Get or create credit account for a branch
     */
    public static function getOrCreate(int $branchId): self
    {
        return self::firstOrCreate(
            ['branch_id' => $branchId],
            ['balance' => 0, 'total_purchased' => 0, 'total_used' => 0]
        );
    }

    /**
     * Add credits to the account (top-up)
     */
    public function addCredits(float $amount, ?int $userId = null, string $reason = 'admin_topup', ?string $description = null, ?array $metadata = null): BranchCreditTransaction
    {
        return DB::transaction(function () use ($amount, $userId, $reason, $description, $metadata) {
            $this->balance += $amount;
            $this->total_purchased += $amount;
            $this->save();

            return BranchCreditTransaction::create([
                'branch_id' => $this->branch_id,
                'user_id' => $userId,
                'type' => 'credit',
                'amount' => $amount,
                'balance_after' => $this->balance,
                'reason' => $reason,
                'description' => $description ?? "Added {$amount} credits",
                'metadata' => $metadata,
            ]);
        });
    }

    /**
     * Deduct credits from the account
     */
    public function deductCredits(float $amount, ?int $userId = null, string $reason = 'evaluation', ?string $description = null, ?array $metadata = null): ?BranchCreditTransaction
    {
        return DB::transaction(function () use ($amount, $userId, $reason, $description, $metadata) {
            // Check sufficient balance
            if ($this->balance < $amount) {
                return null; // Insufficient balance
            }

            $this->balance -= $amount;
            $this->total_used += $amount;
            $this->save();

            return BranchCreditTransaction::create([
                'branch_id' => $this->branch_id,
                'user_id' => $userId,
                'type' => 'debit',
                'amount' => $amount,
                'balance_after' => $this->balance,
                'reason' => $reason,
                'description' => $description ?? "Deducted {$amount} credits for {$reason}",
                'metadata' => $metadata,
            ]);
        });
    }

    /**
     * Check if branch has sufficient credits
     */
    public function hasSufficientCredits(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Get balance in different formats
     */
    public function getBalanceInCentsAttribute(): int
    {
        return (int) ($this->balance * 100);
    }

    public function getBalanceInBdtAttribute(): float
    {
        return round($this->balance * 120, 2); // 1 USD = 120 BDT approximate
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats(string $period = 'month'): array
    {
        $startDate = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $transactions = $this->transactions()
            ->where('type', 'debit')
            ->where('created_at', '>=', $startDate)
            ->get();

        $writingCount = $transactions->where('reason', 'writing_evaluation')->count();
        $speakingCount = $transactions->where('reason', 'speaking_evaluation')->count();
        $writingCost = $transactions->where('reason', 'writing_evaluation')->sum('amount');
        $speakingCost = $transactions->where('reason', 'speaking_evaluation')->sum('amount');

        return [
            'period' => $period,
            'writing_evaluations' => $writingCount,
            'speaking_evaluations' => $speakingCount,
            'total_evaluations' => $writingCount + $speakingCount,
            'writing_cost' => round($writingCost, 4),
            'speaking_cost' => round($speakingCost, 4),
            'total_cost' => round($writingCost + $speakingCost, 4),
        ];
    }
}
