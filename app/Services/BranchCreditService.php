<?php

namespace App\Services;

use App\Models\AiEvaluationRate;
use App\Models\Branch;
use App\Models\BranchCredit;
use App\Models\BranchCreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class BranchCreditService
{
    /**
     * Check if branch has sufficient credits for an evaluation
     */
    public function hasSufficientCredits(int $branchId, string $evaluationType): bool
    {
        $credit = BranchCredit::getOrCreate($branchId);
        $rate = $this->getRate($evaluationType);

        return $credit->hasSufficientCredits($rate);
    }

    /**
     * Deduct credits for AI evaluation
     */
    public function deductForEvaluation(
        int $branchId,
        string $evaluationType,
        ?int $userId = null,
        ?array $metadata = null
    ): ?BranchCreditTransaction {
        try {
            $credit = BranchCredit::getOrCreate($branchId);
            $rate = $this->getRate($evaluationType);

            if (!$credit->hasSufficientCredits($rate)) {
                Log::warning('Insufficient credits for evaluation', [
                    'branch_id' => $branchId,
                    'evaluation_type' => $evaluationType,
                    'required' => $rate,
                    'balance' => $credit->balance,
                ]);
                return null;
            }

            $reason = $evaluationType . '_evaluation';
            $description = $this->getEvaluationDescription($evaluationType);

            $transaction = $credit->deductCredits(
                $rate,
                $userId,
                $reason,
                $description,
                $metadata
            );

            Log::info('Credits deducted for AI evaluation', [
                'branch_id' => $branchId,
                'evaluation_type' => $evaluationType,
                'amount' => $rate,
                'new_balance' => $credit->balance,
            ]);

            return $transaction;

        } catch (Exception $e) {
            Log::error('Failed to deduct credits', [
                'branch_id' => $branchId,
                'evaluation_type' => $evaluationType,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Add credits to branch (admin top-up)
     */
    public function addCredits(
        int $branchId,
        float $amount,
        ?int $adminUserId = null,
        string $reason = 'admin_topup',
        ?string $description = null
    ): BranchCreditTransaction {
        $credit = BranchCredit::getOrCreate($branchId);

        return $credit->addCredits(
            $amount,
            $adminUserId,
            $reason,
            $description ?? "Added {$amount} credits by admin"
        );
    }

    /**
     * Get the rate for an evaluation type
     */
    public function getRate(string $evaluationType): float
    {
        return match($evaluationType) {
            'writing' => AiEvaluationRate::writingRate(),
            'speaking' => AiEvaluationRate::speakingRate(),
            default => 0,
        };
    }

    /**
     * Get description for evaluation type
     */
    protected function getEvaluationDescription(string $evaluationType): string
    {
        return match($evaluationType) {
            'writing' => 'AI Writing Evaluation',
            'speaking' => 'AI Speaking Evaluation (with transcription)',
            default => "AI {$evaluationType} Evaluation",
        };
    }

    /**
     * Get branch credit balance
     */
    public function getBalance(int $branchId): float
    {
        return BranchCredit::getOrCreate($branchId)->balance;
    }

    /**
     * Get credit summary for branch
     */
    public function getCreditSummary(int $branchId): array
    {
        $credit = BranchCredit::getOrCreate($branchId);

        return [
            'balance' => round($credit->balance, 4),
            'balance_in_bdt' => round($credit->balance * 120, 2),
            'total_purchased' => round($credit->total_purchased, 4),
            'total_used' => round($credit->total_used, 4),
            'usage_this_month' => $credit->getUsageStats('month'),
            'usage_today' => $credit->getUsageStats('today'),
            'rates' => [
                'writing' => AiEvaluationRate::writingRate(),
                'speaking' => AiEvaluationRate::speakingRate(),
            ],
        ];
    }

    /**
     * Get recent transactions for branch
     */
    public function getRecentTransactions(int $branchId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return BranchCreditTransaction::where('branch_id', $branchId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if branch can use AI evaluation (has credits)
     */
    public function canUseAiEvaluation(int $branchId, string $evaluationType): array
    {
        $credit = BranchCredit::getOrCreate($branchId);
        $rate = $this->getRate($evaluationType);
        $hasSufficient = $credit->hasSufficientCredits($rate);

        return [
            'allowed' => $hasSufficient,
            'balance' => round($credit->balance, 4),
            'required' => $rate,
            'shortfall' => $hasSufficient ? 0 : round($rate - $credit->balance, 4),
            'message' => $hasSufficient
                ? null
                : "Insufficient credits. Required: {$rate}, Available: {$credit->balance}",
        ];
    }
}
