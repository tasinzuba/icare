<?php

namespace App\Jobs;

use App\Models\AvatarTeacher;
use App\Models\Question;
use App\Services\Avatar\AvatarGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAvatarVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300; // 5 minutes

    /**
     * The number of seconds to wait before retrying.
     */
    public array $backoff = [30, 60, 120];

    /**
     * The question to generate avatar for.
     */
    public Question $question;

    /**
     * The teacher to use for avatar.
     */
    public AvatarTeacher $teacher;

    /**
     * Create a new job instance.
     */
    public function __construct(Question $question, AvatarTeacher $teacher)
    {
        $this->question = $question;
        $this->teacher = $teacher;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting avatar generation job', [
            'question_id' => $this->question->id,
            'teacher_id' => $this->teacher->id,
            'attempt' => $this->attempts(),
        ]);

        $service = new AvatarGeneratorService();

        $result = $service->generateForQuestion($this->question, $this->teacher);

        if (!$result['success']) {
            Log::error('Avatar generation job failed', [
                'question_id' => $this->question->id,
                'error' => $result['error'],
                'attempt' => $this->attempts(),
            ]);

            // If we still have retries, throw exception to trigger retry
            if ($this->attempts() < $this->tries) {
                throw new \Exception($result['error']);
            }
        } else {
            // With webhook, we don't have video_url yet - D-ID will call webhook when done
            Log::info('Avatar generation job completed', [
                'question_id' => $this->question->id,
                'talk_id' => $result['talk_id'] ?? null,
                'message' => $result['message'] ?? 'Video generation initiated',
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Avatar generation job permanently failed', [
            'question_id' => $this->question->id,
            'teacher_id' => $this->teacher->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Mark question as failed
        $this->question->update([
            'avatar_status' => 'failed',
            'avatar_error' => 'Job failed after ' . $this->attempts() . ' attempts: ' . $exception->getMessage(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'avatar-generation',
            'question:' . $this->question->id,
            'teacher:' . $this->teacher->id,
        ];
    }
}
