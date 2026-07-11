<?php

namespace App\Http\Controllers;

use App\Models\StudentAttempt;
use App\Models\StudentAnswer;
use App\Models\Question;
use App\Models\AnswerExplanation;
use App\Services\AI\AIEvaluationService;
use App\Services\AI\InsufficientCreditsException;
use App\Services\BranchCreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use OpenAI\Laravel\Facades\OpenAI;

class AIEvaluationController extends Controller
{
    protected $aiService;
    protected $creditService;

    public function __construct(AIEvaluationService $aiService)
    {
        $this->aiService = $aiService;
        $this->creditService = new BranchCreditService();
    }

    /**
     * Evaluate writing with AI.
     */
    public function evaluateWriting(Request $request)
    {
        try {
            Log::info('Writing evaluation started', [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            $attemptId = $request->input('attempt_id');
            
            if (!$attemptId) {
                return response()->json([
                    'success' => false,
                    'error' => 'No attempt ID provided.'
                ], 400);
            }
            
            $attempt = StudentAttempt::findOrFail($attemptId);

            // Validation checks
            if ($attempt->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access to this attempt.'
                ], 403);
            }

            if (!auth()->user()->hasFeature('ai_writing_evaluation')) {
                // Provide more specific error for offline students
                $errorMessage = auth()->user()->isOfflineStudent()
                    ? 'AI evaluation is not enabled for your enrollment. Please contact your branch administrator.'
                    : 'AI evaluation is not available in your current plan.';

                return response()->json([
                    'success' => false,
                    'error' => $errorMessage
                ], 403);
            }

            if ($attempt->testSet->section->name !== 'writing') {
                return response()->json([
                    'success' => false,
                    'error' => 'This attempt is not for writing section.'
                ], 400);
            }

            // Check credits for offline students BEFORE evaluation
            $user = auth()->user();
            if ($user->isOfflineStudent()) {
                $creditCheck = $this->creditService->canUseAiEvaluation($user->branch_id, 'writing');
                if (!$creditCheck['allowed']) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Insufficient AI credits. Your branch has ' . number_format($creditCheck['balance'], 2) . ' credits, but ' . $creditCheck['required'] . ' credits are required for writing evaluation. Please contact your branch administrator.',
                        'insufficient_credits' => true,
                        'balance' => $creditCheck['balance'],
                        'required' => $creditCheck['required']
                    ], 402); // Payment Required
                }
            }

            // H10: atomically claim the evaluation so two concurrent requests can't both evaluate
            // and both charge. Flipping ai_evaluated_at only-if-null in a single UPDATE is the
            // guard — only the request whose UPDATE affects a row (returns 1) proceeds. On failure
            // below we roll the claim back so the student can retry.
            $forceReEvaluate = $request->boolean('force_reevaluate', false);
            $claimedThisRequest = false;
            if (!$forceReEvaluate) {
                $claimed = StudentAttempt::whereKey($attempt->id)
                    ->whereNull('ai_evaluated_at')
                    ->update(['ai_evaluated_at' => now()]);
                if (!$claimed) {
                    return response()->json([
                        'success' => true,
                        'redirect_url' => route('ai.evaluation.get', $attempt->id),
                        'already_evaluated' => true
                    ]);
                }
                $claimedThisRequest = true;
            }

            // Get writing answers
            $answers = $attempt->answers()
                ->with('question')
                ->get();

            $evaluations = [];
            $hasEvaluated = false;

            foreach ($answers as $answer) {
                if (empty($answer->answer)) {
                    continue;
                }

                // Skip already evaluated answers unless forcing re-evaluation
                if ($answer->ai_evaluation && !$forceReEvaluate) {
                    $evaluations[] = $answer->ai_evaluation;
                    continue;
                }

                // Evaluate with AI
                $evaluation = $this->aiService->evaluateWriting(
                    $answer->answer,
                    $answer->question->content,
                    $answer->question->order_number
                );

                // Store evaluation
                $answer->update([
                    'ai_evaluation' => $evaluation,
                    'ai_band_score' => $evaluation['band_score'] ?? null,
                    'ai_evaluated_at' => now(),
                ]);

                $evaluations[] = $evaluation;
                $hasEvaluated = true;
            }

            // Calculate overall band score
            $overallBand = $this->calculateOverallBand($evaluations);

            // Update attempt with AI scores
            $attempt->update([
                'ai_band_score' => $overallBand,
                'ai_evaluated_at' => now(),
            ]);

            // Increment AI usage count only if new evaluation happened
            if ($hasEvaluated) {
                auth()->user()->incrementAIEvaluationCount();

                // Deduct credits for offline students
                $user = auth()->user();
                if ($user->isOfflineStudent() && $user->branch_id) {
                    $this->creditService->deductForEvaluation(
                        $user->branch_id,
                        'writing',
                        $user->id,
                        [
                            'attempt_id' => $attempt->id,
                            'test_set' => $attempt->testSet->title ?? 'Writing Test',
                            'student_name' => $user->name,
                        ]
                    );
                    Log::info('Credits deducted for writing evaluation', [
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                        'attempt_id' => $attempt->id
                    ]);
                }
            }

            // Direct redirect to AI result page
            return response()->json([
                'success' => true,
                'redirect_url' => route('ai.evaluation.get', $attempt->id),
                'message' => 'Evaluation completed successfully!'
            ]);

        } catch (InsufficientCreditsException $e) {
            // H10: roll back the evaluation claim so the student can retry.
            if (!empty($claimedThisRequest) && isset($attempt)) {
                StudentAttempt::whereKey($attempt->id)->update(['ai_evaluated_at' => null]);
            }
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'insufficient_credits' => true,
                'balance' => $e->getAvailableCredits(),
                'required' => $e->getRequiredCredits()
            ], 402);

        } catch (\Exception $e) {
            // H10: roll back the evaluation claim so a failed evaluation doesn't permanently block retries.
            if (!empty($claimedThisRequest) && isset($attempt)) {
                StudentAttempt::whereKey($attempt->id)->update(['ai_evaluated_at' => null]);
            }
            Log::error('AI Writing evaluation failed', [
                'attempt_id' => $attemptId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to evaluate writing. Please try again later.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Evaluate speaking with AI.
     */
    public function evaluateSpeaking(Request $request)
    {
        try {
            Log::info('Speaking evaluation started', [
                'user_id' => auth()->id(),
                'attempt_id' => $request->input('attempt_id')
            ]);

            $attemptId = $request->input('attempt_id');
            
            if (!$attemptId) {
                return response()->json([
                    'success' => false,
                    'error' => 'No attempt ID provided.'
                ], 400);
            }
            
            $attempt = StudentAttempt::findOrFail($attemptId);

            // Validation checks
            if ($attempt->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access to this attempt.'
                ], 403);
            }

            if (!auth()->user()->hasFeature('ai_speaking_evaluation')) {
                // Provide more specific error for offline students
                $errorMessage = auth()->user()->isOfflineStudent()
                    ? 'AI evaluation is not enabled for your enrollment. Please contact your branch administrator.'
                    : 'AI speaking evaluation is not available in your current plan.';

                return response()->json([
                    'success' => false,
                    'error' => $errorMessage
                ], 403);
            }

            if ($attempt->testSet->section->name !== 'speaking') {
                return response()->json([
                    'success' => false,
                    'error' => 'This attempt is not for speaking section.'
                ], 400);
            }

            // Check credits for offline students BEFORE evaluation
            $user = auth()->user();
            if ($user->isOfflineStudent()) {
                $creditCheck = $this->creditService->canUseAiEvaluation($user->branch_id, 'speaking');
                if (!$creditCheck['allowed']) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Insufficient AI credits. Your branch has ' . number_format($creditCheck['balance'], 2) . ' credits, but ' . $creditCheck['required'] . ' credits are required for speaking evaluation. Please contact your branch administrator.',
                        'insufficient_credits' => true,
                        'balance' => $creditCheck['balance'],
                        'required' => $creditCheck['required']
                    ], 402); // Payment Required
                }
            }

            // H10: atomically claim the evaluation so two concurrent requests can't both evaluate
            // and both charge (rolled back below on failure so the student can retry).
            $forceReEvaluate = $request->boolean('force_reevaluate', false);
            $claimedThisRequest = false;
            if (!$forceReEvaluate) {
                $claimed = StudentAttempt::whereKey($attempt->id)
                    ->whereNull('ai_evaluated_at')
                    ->update(['ai_evaluated_at' => now()]);
                if (!$claimed) {
                    return response()->json([
                        'success' => true,
                        'redirect_url' => route('ai.evaluation.get', $attempt->id),
                        'already_evaluated' => true
                    ]);
                }
                $claimedThisRequest = true;
            }

            // Get speaking answers with audio
            $answers = $attempt->answers()
                ->with('question', 'speakingRecording')
                ->whereHas('speakingRecording')
                ->get();

            if ($answers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No speaking recordings found for this attempt.'
                ], 404);
            }

            $evaluations = [];
            $hasEvaluated = false;
            $failedAnswers = [];
            $totalAnswers = $answers->count();
            $processedCount = 0;

            // Create AI service instance once
            $aiService = new \App\Services\AI\AIEvaluationServiceWithCDN();

            foreach ($answers as $answer) {
                $processedCount++;

                // Skip already evaluated answers unless forcing re-evaluation
                if ($answer->ai_evaluation && !$forceReEvaluate) {
                    $evaluations[] = $answer->ai_evaluation;
                    continue;
                }

                // Get audio path or URL
                $recording = $answer->speakingRecording;
                $audioPathOrUrl = null;

                if ($recording->storage_disk === 'r2' || $recording->file_url) {
                    // Use CDN URL
                    $audioPathOrUrl = $recording->file_url;
                    Log::info('Using CDN URL for audio', [
                        'recording_id' => $recording->id,
                        'url' => $audioPathOrUrl,
                        'progress' => "{$processedCount}/{$totalAnswers}"
                    ]);
                } else {
                    // Use local file path
                    $audioPath = $recording->file_path;
                    $fullPath = storage_path('app/public/' . $audioPath);

                    if (!file_exists($fullPath)) {
                        Log::error('Audio file not found', [
                            'path' => $audioPath,
                            'full_path' => $fullPath
                        ]);
                        $failedAnswers[] = [
                            'part' => $answer->question->part_number ?? $processedCount,
                            'reason' => 'Audio file not found'
                        ];
                        continue;
                    }

                    $audioPathOrUrl = $this->convertAudioIfNeeded($fullPath);
                }

                try {
                    // Extract cue card points for Part 2
                    $cueCardPoints = null;
                    $partNumber = $answer->question->part_number ?? $answer->question->order_number;

                    if ($partNumber == 2) {
                        $formStructure = $answer->question->form_structure ?? [];
                        if (isset($formStructure['fields']) && is_array($formStructure['fields'])) {
                            $points = array_map(function($field) {
                                return $field['label'] ?? '';
                            }, $formStructure['fields']);
                            $cueCardPoints = implode("\n", array_filter($points));
                        }
                    }

                    $evaluation = $aiService->evaluateSpeaking(
                        $audioPathOrUrl,
                        strip_tags($answer->question->content), // Remove HTML tags from question
                        $partNumber,
                        $cueCardPoints
                    );

                    $answer->update([
                        'ai_evaluation' => $evaluation,
                        'ai_band_score' => $evaluation['band_score'] ?? null,
                        'ai_evaluated_at' => now(),
                        'transcription' => $evaluation['transcription'] ?? null,
                    ]);

                    $evaluations[] = $evaluation;
                    $hasEvaluated = true;

                    Log::info('Answer evaluated successfully', [
                        'answer_id' => $answer->id,
                        'band_score' => $evaluation['band_score'] ?? null,
                        'progress' => "{$processedCount}/{$totalAnswers}"
                    ]);

                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    Log::error('Failed to evaluate answer', [
                        'answer_id' => $answer->id,
                        'error' => $errorMessage,
                        'progress' => "{$processedCount}/{$totalAnswers}"
                    ]);

                    // Track failed answers with user-friendly messages
                    $userFriendlyError = $this->getUserFriendlyError($errorMessage);
                    $failedAnswers[] = [
                        'part' => $answer->question->part_number ?? $processedCount,
                        'reason' => $userFriendlyError
                    ];
                    continue;
                }
            }

            // Handle case where all evaluations failed
            if (empty($evaluations)) {
                $errorDetails = $this->formatFailedAnswersMessage($failedAnswers);
                return response()->json([
                    'success' => false,
                    'error' => 'Could not evaluate any recordings. ' . $errorDetails,
                    'failed_parts' => $failedAnswers
                ], 422); // Use 422 instead of 500 for validation-like errors
            }

            // Handle partial success
            $partialSuccess = count($failedAnswers) > 0;

            // Calculate overall band score
            $overallBand = $this->calculateOverallBand($evaluations);

            // Update attempt with AI scores
            $attempt->update([
                'ai_band_score' => $overallBand,
                'ai_evaluated_at' => now(),
            ]);

            // Increment AI usage count only if new evaluation happened
            if ($hasEvaluated) {
                auth()->user()->incrementAIEvaluationCount();

                // Deduct credits for offline students
                $user = auth()->user();
                if ($user->isOfflineStudent() && $user->branch_id) {
                    $this->creditService->deductForEvaluation(
                        $user->branch_id,
                        'speaking',
                        $user->id,
                        [
                            'attempt_id' => $attempt->id,
                            'test_set' => $attempt->testSet->title ?? 'Speaking Test',
                            'student_name' => $user->name,
                        ]
                    );
                    Log::info('Credits deducted for speaking evaluation', [
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                        'attempt_id' => $attempt->id
                    ]);
                }
            }

            // Build response message
            $message = 'Evaluation completed successfully!';
            if ($partialSuccess) {
                $evaluatedCount = count($evaluations);
                $message = "Evaluation completed! {$evaluatedCount} of {$totalAnswers} recordings were evaluated.";
            }

            // Direct redirect to AI result page
            return response()->json([
                'success' => true,
                'redirect_url' => route('ai.evaluation.get', $attempt->id),
                'message' => $message,
                'partial_success' => $partialSuccess,
                'evaluated_count' => count($evaluations),
                'total_count' => $totalAnswers,
                'failed_parts' => $partialSuccess ? $failedAnswers : []
            ]);

        } catch (InsufficientCreditsException $e) {
            // H10: roll back the evaluation claim so the student can retry.
            if (!empty($claimedThisRequest) && isset($attempt)) {
                StudentAttempt::whereKey($attempt->id)->update(['ai_evaluated_at' => null]);
            }
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'insufficient_credits' => true,
                'balance' => $e->getAvailableCredits(),
                'required' => $e->getRequiredCredits()
            ], 402);

        } catch (\Exception $e) {
            // H10: roll back the evaluation claim so a failed evaluation doesn't permanently block retries.
            if (!empty($claimedThisRequest) && isset($attempt)) {
                StudentAttempt::whereKey($attempt->id)->update(['ai_evaluated_at' => null]);
            }
            Log::error('AI Speaking evaluation failed', [
                'attempt_id' => $attemptId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // User-friendly error message
            $userFriendlyError = $this->getUserFriendlyError($e->getMessage());

            // Use 422 for validation-like errors that user can fix
            $statusCode = 422;
            if (str_contains($e->getMessage(), 'API key') || str_contains($e->getMessage(), 'OpenAI')) {
                $statusCode = 503; // Service unavailable for API issues
            }

            return response()->json([
                'success' => false,
                'error' => $userFriendlyError,
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], $statusCode);
        }
    }

    /**
     * Get user-friendly error message from technical error
     */
    private function getUserFriendlyError(string $technicalError): string
    {
        // If the error already contains specific details (word count, seconds), return as-is
        // These are already user-friendly messages from the service
        if (preg_match('/\d+ words|\d+ seconds|\d+MB/', $technicalError)) {
            return $technicalError;
        }

        $errorMappings = [
            'No speech detected' => 'No speech was detected in the recording. Please ensure you speak clearly and your microphone is working.',
            'too short' => 'The recording is too short. IELTS speaking requires at least 15-30 seconds of clear speech.',
            'too small' => 'The audio file appears to be empty or corrupted. Please try recording again.',
            'too large' => 'The audio file is too large. Please try a shorter recording.',
            'file not found' => 'The audio file could not be found. Please try recording again.',
            'Failed to download' => 'Could not access the audio file. Please try again later.',
            'quota' => 'AI evaluation service is temporarily busy. Please try again in a few minutes.',
            'rate limit' => 'Too many requests. Please wait a moment and try again.',
            'API key' => 'AI evaluation service is temporarily unavailable.',
            'timeout' => 'The evaluation took too long. Please try again.',
        ];

        foreach ($errorMappings as $keyword => $friendlyMessage) {
            if (stripos($technicalError, $keyword) !== false) {
                return $friendlyMessage;
            }
        }

        return 'An error occurred during evaluation. Please try again later.';
    }

    /**
     * Format failed answers into a user-friendly message
     */
    private function formatFailedAnswersMessage(array $failedAnswers): string
    {
        if (empty($failedAnswers)) {
            return '';
        }

        $parts = array_unique(array_column($failedAnswers, 'part'));
        $reasons = array_unique(array_column($failedAnswers, 'reason'));

        if (count($reasons) === 1) {
            return "Reason: " . $reasons[0];
        }

        return "Parts " . implode(', ', $parts) . " could not be evaluated.";
    }

    /**
     * Get evaluation status - returns list of recordings and their evaluation status
     */
    public function getEvaluationStatus(Request $request)
    {
        try {
            $attemptId = $request->input('attempt_id');
            $attempt = StudentAttempt::findOrFail($attemptId);

            if ($attempt->user_id !== auth()->id()) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            // Already fully evaluated
            if ($attempt->ai_evaluated_at) {
                return response()->json([
                    'success' => true,
                    'status' => 'completed',
                    'redirect_url' => route('ai.evaluation.get', $attempt->id)
                ]);
            }

            $answers = $attempt->answers()
                ->with('question', 'speakingRecording')
                ->whereHas('speakingRecording')
                ->get();

            $recordings = $answers->map(function ($answer, $index) {
                return [
                    'answer_id' => $answer->id,
                    'part' => $answer->question->part_number ?? $answer->question->order_number ?? ($index + 1),
                    'question' => \Str::limit(strip_tags($answer->question->content), 50),
                    'evaluated' => !empty($answer->ai_evaluation),
                    'band_score' => $answer->ai_band_score,
                ];
            });

            $evaluated = $recordings->where('evaluated', true)->count();
            $total = $recordings->count();

            return response()->json([
                'success' => true,
                'status' => $evaluated === $total ? 'ready_to_finalize' : 'pending',
                'recordings' => $recordings->values(),
                'progress' => [
                    'evaluated' => $evaluated,
                    'total' => $total,
                    'percentage' => $total > 0 ? round(($evaluated / $total) * 100) : 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get evaluation status', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to get status'], 500);
        }
    }

    /**
     * Evaluate a single recording - prevents timeout by processing one at a time
     */
    public function evaluateSingleRecording(Request $request)
    {
        try {
            $answerId = $request->input('answer_id');
            $answer = StudentAnswer::with(['question', 'speakingRecording', 'attempt.testSet.section'])->findOrFail($answerId);
            $attempt = $answer->attempt;

            // Validation
            if ($attempt->user_id !== auth()->id()) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            if (!auth()->user()->hasFeature('ai_speaking_evaluation')) {
                $errorMessage = auth()->user()->isOfflineStudent()
                    ? 'AI evaluation is not enabled for your enrollment.'
                    : 'Feature not available in your plan.';
                return response()->json(['success' => false, 'error' => $errorMessage], 403);
            }

            // Check credits for offline students (only for first evaluation of this attempt)
            $user = auth()->user();
            if ($user->isOfflineStudent() && !$attempt->ai_evaluated_at) {
                // Check if any answer in this attempt has been evaluated (means credits already deducted)
                $anyEvaluated = $attempt->answers()->whereNotNull('ai_evaluation')->exists();
                if (!$anyEvaluated) {
                    $creditCheck = $this->creditService->canUseAiEvaluation($user->branch_id, 'speaking');
                    if (!$creditCheck['allowed']) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Insufficient AI credits. Required: ' . $creditCheck['required'] . ', Available: ' . number_format($creditCheck['balance'], 2),
                            'insufficient_credits' => true
                        ], 402);
                    }
                }
            }

            // Already evaluated (skip if force_reevaluate is true)
            $forceReEvaluate = $request->boolean('force_reevaluate', false);
            if (!empty($answer->ai_evaluation) && !$forceReEvaluate) {
                return response()->json([
                    'success' => true,
                    'already_evaluated' => true,
                    'band_score' => $answer->ai_band_score
                ]);
            }

            $recording = $answer->speakingRecording;
            if (!$recording) {
                return response()->json(['success' => false, 'error' => 'No recording found'], 404);
            }

            // Get audio path or URL
            $audioPathOrUrl = $recording->file_url;
            if (!$audioPathOrUrl && $recording->file_path) {
                $fullPath = storage_path('app/public/' . $recording->file_path);
                if (!file_exists($fullPath)) {
                    return response()->json(['success' => false, 'error' => 'Audio file not found'], 404);
                }
                $audioPathOrUrl = $fullPath;
            }

            Log::info('Evaluating single recording', [
                'answer_id' => $answerId,
                'part' => $answer->question->part_number ?? $answer->question->order_number
            ]);

            // Extract cue card points for Part 2
            $cueCardPoints = null;
            $partNumber = $answer->question->part_number ?? $answer->question->order_number;

            if ($partNumber == 2) {
                $formStructure = $answer->question->form_structure ?? [];
                if (isset($formStructure['fields']) && is_array($formStructure['fields'])) {
                    $points = array_map(fn($field) => $field['label'] ?? '', $formStructure['fields']);
                    $cueCardPoints = implode("\n", array_filter($points));
                }
            }

            // Evaluate
            $aiService = new \App\Services\AI\AIEvaluationServiceWithCDN();
            $evaluation = $aiService->evaluateSpeaking(
                $audioPathOrUrl,
                strip_tags($answer->question->content),
                $partNumber,
                $cueCardPoints
            );

            // Save evaluation
            $answer->update([
                'ai_evaluation' => $evaluation,
                'ai_band_score' => $evaluation['band_score'] ?? null,
                'ai_evaluated_at' => now(),
                'transcription' => $evaluation['transcription'] ?? null,
            ]);

            // H9: charge the branch ONCE, at the point the paid evaluation actually succeeds —
            // not in the separate, client-triggered finalizeEvaluation() (which a client could skip
            // to get free speaking evaluations). The attempt's ai_evaluated_at is used as an atomic
            // idempotency marker so only the first successful recording triggers exactly one charge;
            // finalizeEvaluation() then sees it already set and does not double-charge.
            if ($user->isOfflineStudent() && $user->branch_id) {
                $claimedCharge = StudentAttempt::whereKey($attempt->id)
                    ->whereNull('ai_evaluated_at')
                    ->update(['ai_evaluated_at' => now()]);
                if ($claimedCharge) {
                    $this->creditService->deductForEvaluation(
                        $user->branch_id,
                        'speaking',
                        $user->id,
                        [
                            'attempt_id' => $attempt->id,
                            'test_set' => $attempt->testSet->title ?? 'Speaking Test',
                            'student_name' => $user->name,
                            'via' => 'single_recording',
                        ]
                    );
                    Log::info('Credits deducted for speaking evaluation (single recording)', [
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                        'attempt_id' => $attempt->id,
                    ]);
                }
            }

            Log::info('Single recording evaluated successfully', [
                'answer_id' => $answerId,
                'band_score' => $evaluation['band_score'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'band_score' => $evaluation['band_score'] ?? null,
                'transcription_preview' => \Str::limit($evaluation['transcription'] ?? '', 100)
            ]);

        } catch (\Exception $e) {
            Log::error('Single recording evaluation failed', [
                'answer_id' => $request->input('answer_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $this->getUserFriendlyError($e->getMessage())
            ], 422);
        }
    }

    /**
     * Finalize evaluation - calculate overall scores after all recordings are done
     */
    public function finalizeEvaluation(Request $request)
    {
        try {
            $attemptId = $request->input('attempt_id');
            $attempt = StudentAttempt::findOrFail($attemptId);

            if ($attempt->user_id !== auth()->id()) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            // Check all recordings are evaluated
            $answers = $attempt->answers()
                ->with('question', 'speakingRecording')
                ->whereHas('speakingRecording')
                ->get();

            $evaluated = $answers->whereNotNull('ai_evaluation')->count();
            $total = $answers->count();

            if ($evaluated === 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'No recordings have been evaluated yet.'
                ], 422);
            }

            // Calculate average band score
            $bandScores = $answers->pluck('ai_band_score')->filter()->values();
            $averageBandScore = $bandScores->isNotEmpty() ? round($bandScores->avg(), 1) : null;

            // Check if this is first time finalizing (credits not yet deducted)
            $firstTimeFinalize = !$attempt->ai_evaluated_at;

            // Update attempt
            $attempt->update([
                'ai_evaluated_at' => now(),
                'ai_band_score' => $averageBandScore,
            ]);

            // Deduct credits for offline students (only on first finalization)
            $user = auth()->user();
            if ($firstTimeFinalize && $user->isOfflineStudent() && $user->branch_id) {
                $section = $attempt->testSet->section->name ?? 'speaking';
                if ($section === 'speaking') {
                    $this->creditService->deductForEvaluation(
                        $user->branch_id,
                        'speaking',
                        $user->id,
                        [
                            'attempt_id' => $attempt->id,
                            'test_set' => $attempt->testSet->title ?? 'Speaking Test',
                            'student_name' => $user->name,
                            'via' => 'finalize_evaluation'
                        ]
                    );
                    Log::info('Credits deducted for speaking evaluation (finalize)', [
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                        'attempt_id' => $attempt->id
                    ]);
                }
            }

            Log::info('Evaluation finalized', [
                'attempt_id' => $attemptId,
                'evaluated' => $evaluated,
                'total' => $total,
                'average_score' => $averageBandScore
            ]);

            return response()->json([
                'success' => true,
                'redirect_url' => route('ai.evaluation.get', $attempt->id),
                'stats' => [
                    'evaluated' => $evaluated,
                    'total' => $total,
                    'average_band_score' => $averageBandScore
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to finalize evaluation', [
                'attempt_id' => $request->input('attempt_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to finalize evaluation. Please try again.'
            ], 500);
        }
    }

    /**
     * Get AI evaluation for an attempt.
     */
    public function getEvaluation($attemptId)
    {
        try {
            $attempt = StudentAttempt::findOrFail($attemptId);

            if ($attempt->user_id !== auth()->id()) {
                abort(403);
            }

            if (!$attempt->ai_evaluated_at) {
                // If not evaluated yet, redirect to result page with message
                return redirect()->route('student.results.show', $attempt)
                    ->with('warning', 'AI evaluation is still processing. Please wait a moment and try again.');
            }

            $evaluations = $attempt->answers()
                ->whereNotNull('ai_evaluation')
                ->with('question')
                ->get()
                ->map(function ($answer) {
                    return [
                        'question_id' => $answer->question_id,
                        'question_title' => $answer->question->content,
                        'part' => $answer->question->order_number,
                        'evaluation' => $answer->ai_evaluation,
                        'band_score' => $answer->ai_band_score,
                        'evaluated_at' => $answer->ai_evaluated_at,
                        'transcription' => $answer->transcription ?? null,
                    ];
                });

            $section = $attempt->testSet->section->name;
            
            if ($section === 'writing') {
                return view('ai-evaluation.writing-result', [
                    'attempt' => $attempt,
                    'evaluation' => [
                        'overall_band' => $attempt->ai_band_score,
                        'tasks' => $evaluations->map(function ($eval) {
                            return [
                                'question_title' => $eval['question_title'],
                                'band_score' => $eval['band_score'],
                                'word_count' => $eval['evaluation']['word_count'] ?? 0,
                                'required_words' => $eval['part'] == 1 ? 150 : 250,
                                'criteria' => $eval['evaluation']['criteria'] ?? [],
                                'feedback' => $eval['evaluation']['feedback'] ?? [],
                                'grammar_errors' => $eval['evaluation']['grammar_errors'] ?? [],
                                'grammar_error_types' => $eval['evaluation']['grammar_error_types'] ?? [],
                                'grammar_corrections' => $eval['evaluation']['grammar_corrections'] ?? [],
                                'vocabulary_suggestions' => $eval['evaluation']['vocabulary_suggestions'] ?? [],
                                'improvement_tips' => $eval['evaluation']['improvement_tips'] ?? [],
                                'essay_text' => $eval['evaluation']['original_text'] ?? '',
                                // New data-driven fields
                                'vocabulary_level' => $eval['evaluation']['vocabulary_level'] ?? 'B1',
                                'academic_words_used' => $eval['evaluation']['academic_words_used'] ?? [],
                                'cohesive_devices' => $eval['evaluation']['cohesive_devices'] ?? [],
                                'sentence_variety_score' => $eval['evaluation']['sentence_variety_score'] ?? 5,
                                'paragraph_structure' => $eval['evaluation']['paragraph_structure'] ?? [],
                                'text_statistics' => $eval['evaluation']['text_statistics'] ?? [],
                            ];
                        })->toArray(),
                        'overall_strengths' => $this->extractOverallStrengths($evaluations),
                        'overall_improvements' => $this->extractOverallImprovements($evaluations),
                    ]
                ]);
            } else {
                return view('ai-evaluation.speaking-result', [
                    'attempt' => $attempt,
                    'evaluation' => [
                        'overall_band' => $attempt->ai_band_score,
                        'overall_scores' => [
                            'Fluency and Coherence' => $this->calculateCriterionAverage($evaluations, 'Fluency and Coherence'),
                            'Lexical Resource' => $this->calculateCriterionAverage($evaluations, 'Lexical Resource'),
                            'Grammar' => $this->calculateCriterionAverage($evaluations, 'Grammar'),
                            'Pronunciation' => $this->calculateCriterionAverage($evaluations, 'Pronunciation'),
                        ],
                        'parts' => $evaluations->map(function ($eval) use ($attempt) {
                            $recording = $attempt->answers()
                                ->where('question_id', $eval['question_id'])
                                ->first()
                                ->speakingRecording ?? null;

                            // Extract new metrics from evaluation
                            $preAnalysis = $eval['evaluation']['pre_analysis'] ?? [];
                            $speechMetrics = $eval['evaluation']['speech_metrics'] ?? [];
                            $fluencyIndicators = $preAnalysis['fluency_indicators'] ?? [];
                            $lexicalAnalysis = $preAnalysis['lexical_analysis'] ?? [];
                            $coherenceMarkers = $preAnalysis['coherence_markers'] ?? [];
                            $partSpecific = $preAnalysis['part_specific'] ?? [];

                            return [
                                'part_number' => $eval['part'],
                                'part_type' => "Part {$eval['part']}",
                                'question' => $eval['question_title'],
                                'band_score' => $eval['band_score'],
                                'duration' => $speechMetrics['duration_formatted'] ?? $this->formatDuration($eval['evaluation']['word_count'] ?? 0),
                                'duration_seconds' => $speechMetrics['duration_seconds'] ?? null,
                                'transcription' => $eval['transcription'] ?? $eval['evaluation']['transcription'] ?? '',
                                'feedback' => $eval['evaluation']['feedback'] ?? [],
                                'vocabulary_range' => $eval['evaluation']['vocabulary_range'] ?? [],
                                'pronunciation_issues' => $eval['evaluation']['pronunciation_issues'] ?? [],
                                'tips' => $eval['evaluation']['tips'] ?? [],
                                'audio_url' => $recording ? route('audio.stream', $recording->id) : null,
                                // New enhanced metrics
                                'metrics' => [
                                    'speech_rate' => $speechMetrics['words_per_minute'] ?? 150,
                                    'speech_rate_assessment' => $speechMetrics['words_per_minute_assessment'] ?? 'Normal',
                                    'word_count' => $preAnalysis['basic_stats']['word_count'] ?? 0,
                                ],
                                'filler_words' => [
                                    'total_count' => $fluencyIndicators['total_filler_count'] ?? 0,
                                    'percentage' => $fluencyIndicators['filler_percentage'] ?? 0,
                                    'details' => $fluencyIndicators['filler_word_details'] ?? [],
                                    'assessment' => $fluencyIndicators['fluency_assessment'] ?? 'Not assessed',
                                ],
                                'lexical_analysis' => [
                                    'unique_words' => $lexicalAnalysis['unique_word_count'] ?? 0,
                                    'diversity_ratio' => $lexicalAnalysis['type_token_ratio'] ?? 0,
                                    'diversity_percentage' => round(($lexicalAnalysis['type_token_ratio'] ?? 0) * 100),
                                    'assessment' => $lexicalAnalysis['lexical_assessment'] ?? 'Not assessed',
                                    'academic_words' => $lexicalAnalysis['academic_words'] ?? [],
                                ],
                                'coherence_markers' => [
                                    'total_count' => $coherenceMarkers['total_marker_count'] ?? 0,
                                    'by_category' => $coherenceMarkers['markers_by_category'] ?? [],
                                    'assessment' => $coherenceMarkers['coherence_assessment'] ?? 'Not assessed',
                                ],
                                'cue_card_coverage' => $eval['part'] == 2 ? [
                                    'coverage_percentage' => $partSpecific['coverage_percentage'] ?? null,
                                    'points_covered' => $partSpecific['points_covered'] ?? [],
                                    'points_missed' => $partSpecific['points_missed'] ?? [],
                                ] : null,
                                'grammar_errors' => $eval['evaluation']['grammar_errors'] ?? [],
                                'task_achievement' => $eval['evaluation']['task_achievement'] ?? null,
                            ];
                        })->toArray(),
                        'strengths' => $this->extractSpeakingStrengths($evaluations),
                        'improvements' => $this->extractSpeakingImprovements($evaluations),
                        'study_plan' => $this->generateStudyPlan($evaluations),
                    ]
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to get AI evaluation', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('student.results.show', $attemptId)
                ->with('error', 'Failed to retrieve evaluation.');
        }
    }

    /**
     * Calculate overall band score from evaluations.
     */
    private function calculateOverallBand(array $evaluations): float
    {
        if (empty($evaluations)) {
            return 0;
        }

        $totalScore = 0;
        $count = 0;

        foreach ($evaluations as $evaluation) {
            if (isset($evaluation['band_score'])) {
                $totalScore += $evaluation['band_score'];
                $count++;
            }
        }

        if ($count === 0) {
            return 0;
        }

        $average = $totalScore / $count;
        return round($average * 2) / 2;
    }
    
    /**
     * Convert audio to MP3 if needed (OpenAI doesn't support webm)
     */
    private function convertAudioIfNeeded($fullPath)
    {
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
        
        if (in_array($extension, ['webm', 'ogg'])) {
            $mp3Path = str_replace('.' . $extension, '.mp3', $fullPath);
            
            if (file_exists($mp3Path)) {
                Log::info('MP3 file already exists', ['path' => $mp3Path]);
                return $mp3Path;
            }
            
            $command = "ffmpeg -i " . escapeshellarg($fullPath) . " -acodec libmp3lame -ab 128k " . escapeshellarg($mp3Path) . " 2>&1";
            
            Log::info('Running FFmpeg command', ['command' => $command]);
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                Log::error('FFmpeg conversion failed', [
                    'command' => $command,
                    'output' => $output,
                    'return_code' => $returnCode
                ]);
                
                return $fullPath;
            }
            
            Log::info('Audio converted successfully', ['mp3_path' => $mp3Path]);
            return $mp3Path;
        }
        
        return $fullPath;
    }
    
    /**
     * Calculate average score for a specific criterion
     */
    private function calculateCriterionAverage($evaluations, $criterion)
    {
        $total = 0;
        $count = 0;
        
        foreach ($evaluations as $eval) {
            if (isset($eval['evaluation']['criteria'][$criterion])) {
                $total += $eval['evaluation']['criteria'][$criterion];
                $count++;
            }
        }
        
        return $count > 0 ? round($total / $count, 1) : 0;
    }
    
    /**
     * Format duration from word count
     */
    private function formatDuration($wordCount)
    {
        $minutes = round($wordCount / 150, 1);
        
        if ($minutes < 1) {
            return round($minutes * 60) . ' seconds';
        }
        
        return $minutes . ' minutes';
    }

    /**
     * Extract overall strengths from evaluations (use AI-generated data)
     */
    private function extractOverallStrengths($evaluations)
    {
        $strengths = [];

        // First try to get AI-generated strengths
        foreach ($evaluations as $eval) {
            if (!empty($eval['evaluation']['overall_strengths'])) {
                $strengths = array_merge($strengths, $eval['evaluation']['overall_strengths']);
            }
        }

        // Return AI-generated strengths if available
        if (!empty($strengths)) {
            return array_unique($strengths);
        }

        // Fallback: analyze criteria scores (only if AI didn't provide)
        return [];
    }

    /**
     * Extract overall improvements from evaluations (use AI-generated data)
     */
    private function extractOverallImprovements($evaluations)
    {
        $improvements = [];

        // First try to get AI-generated improvements
        foreach ($evaluations as $eval) {
            if (!empty($eval['evaluation']['overall_improvements'])) {
                $improvements = array_merge($improvements, $eval['evaluation']['overall_improvements']);
            }
        }

        // Return AI-generated improvements if available
        if (!empty($improvements)) {
            return array_unique($improvements);
        }

        // Fallback: return empty (only if AI didn't provide)
        return [];
    }

    /**
     * Extract speaking strengths (use AI-generated data)
     */
    private function extractSpeakingStrengths($evaluations)
    {
        $strengths = [];

        // First try to get AI-generated strengths
        foreach ($evaluations as $eval) {
            if (!empty($eval['evaluation']['overall_strengths'])) {
                $strengths = array_merge($strengths, $eval['evaluation']['overall_strengths']);
            }
        }

        // Return AI-generated strengths if available
        if (!empty($strengths)) {
            return array_unique($strengths);
        }

        return [];
    }

    /**
     * Extract speaking improvements (use AI-generated data)
     */
    private function extractSpeakingImprovements($evaluations)
    {
        $improvements = [];

        // First try to get AI-generated improvements
        foreach ($evaluations as $eval) {
            if (!empty($eval['evaluation']['overall_improvements'])) {
                $improvements = array_merge($improvements, $eval['evaluation']['overall_improvements']);
            }
        }

        // Return AI-generated improvements if available
        if (!empty($improvements)) {
            return array_unique($improvements);
        }

        return [];
    }

    /**
     * Generate study plan (use AI-generated data)
     */
    private function generateStudyPlan($evaluations)
    {
        $studyPlan = [];

        // First try to get AI-generated study plan
        foreach ($evaluations as $eval) {
            if (!empty($eval['evaluation']['study_plan'])) {
                $studyPlan = array_merge($studyPlan, $eval['evaluation']['study_plan']);
            }
        }

        // Return AI-generated study plan if available
        if (!empty($studyPlan)) {
            return array_unique($studyPlan);
        }

        return [];
    }

    /**
     * Generate AI explanation for a wrong answer
     */
    public function getAnswerExplanation(Request $request)
    {
        try {
            $request->validate([
                'question_id' => 'required|integer',
                'student_answer' => 'required|string',
                'correct_answer' => 'required|string',
                'question_content' => 'required|string',
                'question_type' => 'required|string',
            ]);

            $questionId = $request->input('question_id');

            // H8: this endpoint proxies text to the LLM. Restrict it to students who actually
            // answered THIS question (in one of their own attempts), so it cannot be used as a
            // free, arbitrary LLM proxy, and use the SERVER's question content rather than
            // whatever the client supplies (prompt-injection hardening).
            $question = \App\Models\Question::find($questionId);
            if (!$question) {
                return response()->json(['success' => false, 'error' => 'Question not found'], 404);
            }
            $ownsAnswer = \App\Models\StudentAnswer::where('question_id', $questionId)
                ->whereHas('attempt', fn ($q) => $q->where('user_id', auth()->id()))
                ->exists();
            if (!$ownsAnswer) {
                return response()->json(['success' => false, 'error' => 'You are not authorized to request this explanation.'], 403);
            }

            $studentAnswer = $request->input('student_answer');
            $correctAnswer = $request->input('correct_answer');
            $questionContent = $question->content; // server-side, not client-supplied
            $questionType = $question->question_type ?: $request->input('question_type');
            $context = $request->input('context');
            $options = $request->input('options');

            // Create unique cache key
            $cacheKey = 'explanation_' . md5($questionId . $studentAnswer . $correctAnswer);

            // Check cache first (24 hour cache)
            $cached = Cache::get($cacheKey);
            if ($cached) {
                Log::info('Returning cached explanation', ['question_id' => $questionId]);
                return response()->json([
                    'success' => true,
                    'explanation' => $cached['explanation'],
                    'tip' => $cached['tip'] ?? null,
                    'cached' => true
                ]);
            }

            // Generate new explanation
            $result = $this->aiService->generateAnswerExplanation(
                $questionContent,
                $questionType,
                $studentAnswer,
                $correctAnswer,
                $context,
                $options
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to generate explanation'
                ], 500);
            }

            // Cache the result for 24 hours
            Cache::put($cacheKey, [
                'explanation' => $result['explanation'],
                'tip' => $result['tip']
            ], now()->addHours(24));

            return response()->json([
                'success' => true,
                'explanation' => $result['explanation'],
                'tip' => $result['tip'],
                'cached' => false
            ]);

        } catch (\Exception $e) {
            Log::error('Answer explanation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate explanation. Please try again.'
            ], 500);
        }
    }
}