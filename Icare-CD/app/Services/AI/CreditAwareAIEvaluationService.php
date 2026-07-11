<?php

namespace App\Services\AI;

use App\Models\User;
use App\Models\OfflineEnrollment;
use App\Services\BranchCreditService;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Wrapper service that handles credit deduction before AI evaluation
 * Only deducts credits for offline students (branch students)
 */
class CreditAwareAIEvaluationService
{
    protected AIEvaluationService $aiService;
    protected BranchCreditService $creditService;

    public function __construct()
    {
        $this->aiService = new AIEvaluationService();
        $this->creditService = new BranchCreditService();
    }

    /**
     * Evaluate writing with credit deduction for offline students
     */
    public function evaluateWriting(string $text, string $question, int $taskNumber, ?int $userId = null, ?array $metadata = null): array
    {
        $user = $userId ? User::find($userId) : auth()->user();

        // Check if offline student and deduct credits
        if ($user && $user->isOfflineStudent()) {
            $this->deductCreditsForOfflineStudent($user, 'writing', $metadata);
        }

        // Perform the actual evaluation
        return $this->aiService->evaluateWriting($text, $question, $taskNumber);
    }

    /**
     * Evaluate speaking with credit deduction for offline students
     */
    public function evaluateSpeaking(
        string $audioPath,
        string $question,
        int $partNumber,
        ?string $cueCardPoints = null,
        ?float $audioDuration = null,
        ?int $userId = null,
        ?array $metadata = null
    ): array {
        $user = $userId ? User::find($userId) : auth()->user();

        // Check if offline student and deduct credits
        if ($user && $user->isOfflineStudent()) {
            $this->deductCreditsForOfflineStudent($user, 'speaking', $metadata);
        }

        // Perform the actual evaluation
        return $this->aiService->evaluateSpeaking($audioPath, $question, $partNumber, $cueCardPoints, $audioDuration);
    }

    /**
     * Check if branch has sufficient credits for evaluation
     */
    public function canEvaluate(?int $userId, string $evaluationType): array
    {
        $user = $userId ? User::find($userId) : auth()->user();

        // Online students don't need credits (or handle differently)
        if (!$user || !$user->isOfflineStudent()) {
            return [
                'allowed' => true,
                'is_offline_student' => false,
                'message' => null,
            ];
        }

        $branchId = $user->branch_id;
        if (!$branchId) {
            return [
                'allowed' => false,
                'is_offline_student' => true,
                'message' => 'Student not associated with any branch',
            ];
        }

        $check = $this->creditService->canUseAiEvaluation($branchId, $evaluationType);
        $check['is_offline_student'] = true;

        return $check;
    }

    /**
     * Deduct credits for offline student's branch
     */
    protected function deductCreditsForOfflineStudent(User $user, string $evaluationType, ?array $metadata = null): void
    {
        $branchId = $user->branch_id;

        if (!$branchId) {
            Log::warning('Offline student without branch_id, skipping credit deduction', [
                'user_id' => $user->id,
                'evaluation_type' => $evaluationType,
            ]);
            return;
        }

        // Check if sufficient credits
        $check = $this->creditService->canUseAiEvaluation($branchId, $evaluationType);

        if (!$check['allowed']) {
            Log::error('Insufficient credits for AI evaluation', [
                'branch_id' => $branchId,
                'user_id' => $user->id,
                'evaluation_type' => $evaluationType,
                'balance' => $check['balance'],
                'required' => $check['required'],
            ]);

            throw new InsufficientCreditsException(
                "Insufficient AI credits. Required: {$check['required']}, Available: {$check['balance']}",
                $check['balance'],
                $check['required']
            );
        }

        // Deduct credits
        $metadata = array_merge($metadata ?? [], [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'student_id' => $user->offlineEnrollment?->student_id,
        ]);

        $transaction = $this->creditService->deductForEvaluation(
            $branchId,
            $evaluationType,
            $user->id,
            $metadata
        );

        if (!$transaction) {
            throw new Exception('Failed to deduct credits for evaluation');
        }

        Log::info('Credits deducted for AI evaluation', [
            'branch_id' => $branchId,
            'user_id' => $user->id,
            'evaluation_type' => $evaluationType,
            'amount' => $transaction->amount,
            'new_balance' => $transaction->balance_after,
        ]);
    }

    /**
     * Get the underlying AI service for direct access
     */
    public function getAIService(): AIEvaluationService
    {
        return $this->aiService;
    }

    /**
     * Generate answer explanation (no credit charge)
     */
    public function generateAnswerExplanation(
        string $questionContent,
        string $questionType,
        string $studentAnswer,
        string $correctAnswer,
        ?string $context = null,
        ?array $options = null
    ): array {
        return $this->aiService->generateAnswerExplanation(
            $questionContent,
            $questionType,
            $studentAnswer,
            $correctAnswer,
            $context,
            $options
        );
    }
}
