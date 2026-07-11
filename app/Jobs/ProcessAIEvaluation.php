<?php


namespace App\Jobs;

use App\Models\StudentAttempt;
use App\Models\AIEvaluationJob;
use App\Services\AI\AIEvaluationService;
use App\Services\AI\AIEvaluationServiceWithCDN;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessAIEvaluation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes
    public $backoff = [30, 60, 120]; // Retry after 30s, 1m, 2m

    protected $evaluationJob;
    protected $attempt;

    public function __construct(AIEvaluationJob $evaluationJob)
    {
        $this->evaluationJob = $evaluationJob;
        $this->attempt = $evaluationJob->attempt;
    }

    public function handle()
    {
        // Use CDN-compatible service
        $aiService = new AIEvaluationServiceWithCDN();
        
        try {
            Log::info('Starting AI evaluation job', [
                'job_id' => $this->evaluationJob->id,
                'attempt_id' => $this->attempt->id,
                'type' => $this->evaluationJob->type
            ]);

            $this->evaluationJob->markAsProcessing();

            if ($this->evaluationJob->type === 'speaking') {
                $this->processSpeakingEvaluation($aiService);
            } else {
                $this->processWritingEvaluation($aiService);
            }

            $this->evaluationJob->markAsCompleted();

            Log::info('AI evaluation completed successfully', [
                'job_id' => $this->evaluationJob->id
            ]);

        } catch (Exception $e) {
            $this->handleFailure($e);
            throw $e; // Re-throw for retry mechanism
        }
    }

    protected function processSpeakingEvaluation($aiService)
    {
        $answers = $this->attempt->answers()
            ->with('question', 'speakingRecording')
            ->whereHas('speakingRecording')
            ->get();

        if ($answers->isEmpty()) {
            throw new Exception('No speaking recordings found for evaluation');
        }

        $evaluations = [];
        $totalAnswers = $answers->count();
        $processed = 0;

        foreach ($answers as $answer) {
            // Update progress
            $processed++;
            $progress = 10 + (int)(($processed / $totalAnswers) * 80);
            $this->evaluationJob->updateProgress($progress);

            // Skip if already evaluated
            if ($answer->ai_evaluation) {
                $evaluations[] = $answer->ai_evaluation;
                continue;
            }

            // Get audio file path or URL
            $recording = $answer->speakingRecording;
            $audioPathOrUrl = null;
            
            // Check if recording has CDN URL
            if ($recording->file_url) {
                $audioPathOrUrl = $recording->file_url;
            } elseif ($recording->storage_disk === 'r2') {
                // Generate CDN URL
                $audioPathOrUrl = $recording->getFileUrlAttribute();
            } else {
                // Use local path
                $audioPathOrUrl = storage_path('app/public/' . $recording->file_path);
                
                if (!file_exists($audioPathOrUrl)) {
                    Log::warning('Audio file not found', ['path' => $audioPathOrUrl]);
                    continue;
                }
            }

            try {
                // Call AI service (now handles both local files and CDN URLs)
                $evaluation = $aiService->evaluateSpeaking(
                    $audioPathOrUrl,
                    $answer->question->content,
                    $answer->question->order_number
                );

                // Save evaluation
                $answer->update([
                    'ai_evaluation' => $evaluation,
                    'ai_band_score' => $evaluation['band_score'] ?? null,
                    'ai_evaluated_at' => now(),
                    'transcription' => $evaluation['transcription'] ?? null,
                ]);

                $evaluations[] = $evaluation;

                // Log progress
                Log::info('Answer evaluated', [
                    'answer_id' => $answer->id,
                    'band_score' => $evaluation['band_score'] ?? null
                ]);

            } catch (Exception $e) {
                Log::error('Failed to evaluate answer', [
                    'answer_id' => $answer->id,
                    'error' => $e->getMessage()
                ]);
                
                // Continue with other answers
                continue;
            }
        }

        if (empty($evaluations)) {
            throw new Exception('No answers could be evaluated');
        }

        // Calculate overall band score
        $overallBand = $this->calculateOverallBand($evaluations);

        // Update attempt
        $this->attempt->update([
            'ai_band_score' => $overallBand,
            'ai_evaluated_at' => now(),
        ]);

        // Update progress to 90%
        $this->evaluationJob->updateProgress(90);

        // Increment user's AI usage
        $this->attempt->user->incrementAIEvaluationCount();
    }

    protected function processWritingEvaluation($aiService)
    {
        $answers = $this->attempt->answers()
            ->with('question')
            ->get();

        $evaluations = [];
        $totalAnswers = $answers->count();
        $processed = 0;
        $hasNewEvaluation = false;

        foreach ($answers as $answer) {
            $processed++;
            $progress = 10 + (int)(($processed / $totalAnswers) * 80);
            $this->evaluationJob->updateProgress($progress);

            if (empty($answer->answer)) {
                continue;
            }

            // H22: include already-evaluated answers in the aggregate so a retry does not recompute
            // the overall band from an empty set (which previously overwrote the real score with 0).
            if ($answer->ai_evaluation) {
                $evaluations[] = $answer->ai_evaluation;
                continue;
            }

            try {
                $evaluation = $aiService->evaluateWriting(
                    $answer->answer,
                    $answer->question->content,
                    $answer->question->order_number
                );

                $answer->update([
                    'ai_evaluation' => $evaluation,
                    'ai_band_score' => $evaluation['band_score'] ?? null,
                    'ai_evaluated_at' => now(),
                ]);

                $evaluations[] = $evaluation;
                $hasNewEvaluation = true;

            } catch (Exception $e) {
                Log::error('Failed to evaluate writing answer', [
                    'answer_id' => $answer->id,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        $overallBand = $this->calculateOverallBand($evaluations);

        // H22: never overwrite a real band with 0 when there was nothing to aggregate.
        $updateData = ['ai_evaluated_at' => now()];
        if (!empty($evaluations)) {
            $updateData['ai_band_score'] = $overallBand;
        }
        $this->attempt->update($updateData);

        $this->evaluationJob->updateProgress(90);

        // M57: only count usage when new work was actually done, so a job retry / re-dispatch
        // does not double-charge the user's AI usage quota.
        if ($hasNewEvaluation) {
            $this->attempt->user->incrementAIEvaluationCount();
        }
    }

    protected function calculateOverallBand(array $evaluations): float
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

        return round(($totalScore / $count) * 2) / 2;
    }

    protected function convertAudioIfNeeded($fullPath): string
    {
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
        
        if (!in_array($extension, ['webm', 'ogg'])) {
            return $fullPath;
        }

        $mp3Path = str_replace('.' . $extension, '.mp3', $fullPath);
        
        if (file_exists($mp3Path)) {
            return $mp3Path;
        }
        
        $command = sprintf(
            'ffmpeg -i %s -acodec libmp3lame -ab 128k %s 2>&1',
            escapeshellarg($fullPath),
            escapeshellarg($mp3Path)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            Log::error('FFmpeg conversion failed', [
                'command' => $command,
                'output' => $output,
                'return_code' => $returnCode
            ]);
            return $fullPath;
        }
        
        return $mp3Path;
    }

    protected function handleFailure(Exception $e)
    {
        Log::error('AI Evaluation Job Failed', [
            'job_id' => $this->evaluationJob->id,
            'attempt_id' => $this->attempt->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        $this->evaluationJob->markAsFailed($e->getMessage());
    }

    public function failed(Exception $exception)
    {
        $this->handleFailure($exception);
    }
}