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

/**
 * Fallback job to check stale avatar generation tasks.
 * This runs periodically to catch any tasks where the webhook failed.
 */
class CheckStaleAvatarTasksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Timeout in seconds.
     */
    public int $timeout = 120;

    /**
     * Maximum number of tasks to check per run.
     */
    private int $maxTasks = 10;

    /**
     * Minutes after which a task is considered stale.
     */
    private int $staleMinutes = 5;

    /**
     * Maximum poll attempts before marking as failed.
     */
    private int $maxPollAttempts = 10;

    public function __construct()
    {
        $this->onQueue('avatar-processing');
    }

    /**
     * Execute the job.
     */
    public function handle(DIDService $didService): void
    {
        // Get stale pending tasks (older than X minutes)
        $staleTasks = AvatarGenerationTask::stale($this->staleMinutes)
            ->where('poll_attempts', '<', $this->maxPollAttempts)
            ->orderBy('created_at')
            ->limit($this->maxTasks)
            ->get();

        if ($staleTasks->isEmpty()) {
            Log::debug('No stale avatar tasks to process');
            return;
        }

        Log::info('Checking stale avatar tasks', [
            'count' => $staleTasks->count(),
        ]);

        foreach ($staleTasks as $task) {
            $this->checkTask($task, $didService);
        }
    }

    /**
     * Check a single task's status.
     */
    private function checkTask(AvatarGenerationTask $task, DIDService $didService): void
    {
        $task->incrementPollAttempts();

        Log::info('Polling D-ID status for stale task', [
            'task_id' => $task->id,
            'talk_id' => $task->talk_id,
            'poll_attempts' => $task->poll_attempts,
        ]);

        $statusResult = $didService->getTalkStatus($task->talk_id);

        if (!$statusResult['success']) {
            Log::warning('Failed to get D-ID status', [
                'task_id' => $task->id,
                'error' => $statusResult['error'],
            ]);

            // If max attempts reached, mark as failed
            if ($task->poll_attempts >= $this->maxPollAttempts) {
                $this->markTaskFailed($task, 'Max poll attempts reached: ' . ($statusResult['error'] ?? 'Unknown error'));
            }
            return;
        }

        $status = $statusResult['status'];

        if ($status === 'done') {
            // Video is ready! Update task and dispatch processing job
            $task->update([
                'status' => 'processing',
                'result_url' => $statusResult['result_url'],
                'duration' => $statusResult['duration'],
            ]);

            // Dispatch job to download and upload video
            ProcessCompletedAvatarJob::dispatch($task);

            Log::info('Stale task completed, dispatched processing', [
                'task_id' => $task->id,
                'talk_id' => $task->talk_id,
            ]);

        } elseif (in_array($status, ['error', 'rejected'])) {
            $this->markTaskFailed($task, $statusResult['error'] ?? 'D-ID returned error status');

        } else {
            // Still processing
            Log::debug('Task still processing', [
                'task_id' => $task->id,
                'status' => $status,
            ]);
        }
    }

    /**
     * Mark task and question as failed.
     */
    private function markTaskFailed(AvatarGenerationTask $task, string $error): void
    {
        $task->markAsFailed($error);

        $task->question->update([
            'avatar_status' => 'failed',
            'avatar_error' => $error,
        ]);

        Log::error('Avatar task marked as failed', [
            'task_id' => $task->id,
            'error' => $error,
        ]);
    }

    /**
     * Get the tags for the job.
     */
    public function tags(): array
    {
        return ['avatar-stale-check'];
    }
}
