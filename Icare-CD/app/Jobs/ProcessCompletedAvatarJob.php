<?php

namespace App\Jobs;

use App\Models\AvatarGenerationTask;
use App\Services\Avatar\DIDService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCompletedAvatarJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Timeout in seconds.
     */
    public int $timeout = 180; // 3 minutes for download/upload

    /**
     * Backoff times for retries.
     */
    public array $backoff = [10, 30, 60];

    /**
     * The task to process.
     */
    public AvatarGenerationTask $task;

    /**
     * Create a new job instance.
     */
    public function __construct(AvatarGenerationTask $task)
    {
        $this->task = $task;
        $this->onQueue('avatar-processing');
    }

    /**
     * Execute the job.
     */
    public function handle(DIDService $didService): void
    {
        Log::info('Processing completed avatar', [
            'task_id' => $this->task->id,
            'question_id' => $this->task->question_id,
            'result_url' => $this->task->result_url,
        ]);

        // Refresh the task
        $this->task->refresh();

        // Already completed or failed
        if ($this->task->isCompleted() || $this->task->hasFailed()) {
            Log::info('Task already processed, skipping', [
                'task_id' => $this->task->id,
                'status' => $this->task->status,
            ]);
            return;
        }

        if (!$this->task->result_url) {
            Log::error('No result_url in task', ['task_id' => $this->task->id]);
            $this->task->markAsFailed('No result URL available');
            return;
        }

        // Download video from D-ID and upload to R2
        $uploadResult = $didService->downloadAndUploadVideo(
            $this->task->result_url,
            $this->task->question_id
        );

        if (!$uploadResult['success']) {
            Log::error('Failed to download/upload video', [
                'task_id' => $this->task->id,
                'error' => $uploadResult['error'],
            ]);

            // Retry if we have attempts left
            if ($this->attempts() < $this->tries) {
                throw new \Exception($uploadResult['error']);
            }

            $this->task->markAsFailed($uploadResult['error']);
            $this->updateQuestionStatus('failed', $uploadResult['error']);
            return;
        }

        // Success! Update task and question
        $this->task->markAsCompleted(
            $this->task->result_url,
            $uploadResult['url'],
            $uploadResult['path'],
            $this->task->duration
        );

        // Update the question with the video URL
        $this->task->question->update([
            'avatar_video_url' => $uploadResult['url'],
            'avatar_duration' => $this->task->duration,
            'avatar_status' => 'ready',
            'avatar_error' => null,
        ]);

        Log::info('Avatar video processed successfully', [
            'task_id' => $this->task->id,
            'question_id' => $this->task->question_id,
            'video_url' => $uploadResult['url'],
        ]);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessCompletedAvatarJob permanently failed', [
            'task_id' => $this->task->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        $this->task->markAsFailed('Processing failed: ' . $exception->getMessage());
        $this->updateQuestionStatus('failed', $exception->getMessage());
    }

    /**
     * Update the question's avatar status.
     */
    private function updateQuestionStatus(string $status, ?string $error = null): void
    {
        $this->task->question->update([
            'avatar_status' => $status,
            'avatar_error' => $error,
        ]);
    }

    /**
     * Get the tags for the job.
     */
    public function tags(): array
    {
        return [
            'avatar-processing',
            'task:' . $this->task->id,
            'question:' . $this->task->question_id,
        ];
    }
}
