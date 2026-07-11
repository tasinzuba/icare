<?php

namespace App\Services\Avatar;

use App\Models\AvatarGenerationTask;
use App\Models\AvatarTeacher;
use App\Models\Question;
use App\Jobs\GenerateAvatarVideoJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AvatarGeneratorService
{
    private ElevenLabsService $elevenLabsService;
    private DIDService $didService;

    public function __construct()
    {
        $this->elevenLabsService = new ElevenLabsService();
        $this->didService = new DIDService();
    }

    /**
     * Generate avatar video for a single question (async with webhook).
     * This method returns quickly after starting the D-ID generation.
     * The webhook will handle the completion.
     *
     * @param Question $question
     * @param AvatarTeacher $teacher
     * @param bool $useWebhook Whether to use async webhook (true) or blocking polling (false)
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function generateForQuestion(Question $question, AvatarTeacher $teacher, bool $useWebhook = true): array
    {
        try {
            // Update status to generating_audio
            $question->update([
                'avatar_status' => 'generating_audio',
                'avatar_teacher_id' => $teacher->id,
                'avatar_error' => null,
            ]);

            // Get question text for TTS
            $text = $this->getQuestionTextForTTS($question);

            if (empty($text)) {
                return $this->markFailed($question, 'Question has no text content');
            }

            // Step 1: Generate audio with ElevenLabs (synchronous, fast)
            Log::info('Generating audio for question', [
                'question_id' => $question->id,
                'teacher_id' => $teacher->id,
            ]);

            $audioResult = $this->elevenLabsService->generateAndUpload(
                $text,
                $teacher->elevenlabs_voice_id,
                $question->id
            );

            if (!$audioResult['success']) {
                return $this->markFailed($question, $audioResult['error']);
            }

            // Update with audio URL and change status
            $question->update([
                'avatar_audio_url' => $audioResult['audio_url'],
                'avatar_duration' => $audioResult['duration'],
                'avatar_status' => 'generating_video',
            ]);

            // Wait for audio URL to be accessible (CDN propagation delay)
            if (!$this->waitForUrlAccessible($audioResult['audio_url'])) {
                return $this->markFailed($question, 'Audio URL not accessible after upload. CDN propagation issue.');
            }

            // Step 2: Generate video with D-ID
            Log::info('Creating D-ID talk for question', [
                'question_id' => $question->id,
                'audio_url' => $audioResult['audio_url'],
                'use_webhook' => $useWebhook,
            ]);

            if ($useWebhook) {
                // Async approach with webhook - returns immediately
                return $this->createDIDTalkAsync($question, $teacher, $audioResult['audio_url']);
            } else {
                // Legacy blocking approach with polling
                return $this->createDIDTalkBlocking($question, $teacher, $audioResult);
            }

        } catch (\Exception $e) {
            Log::error('Avatar generation exception', [
                'question_id' => $question->id,
                'error' => $e->getMessage(),
            ]);

            return $this->markFailed($question, $e->getMessage());
        }
    }

    /**
     * Create D-ID talk asynchronously with webhook.
     * Returns immediately after creating the talk.
     */
    private function createDIDTalkAsync(Question $question, AvatarTeacher $teacher, string $audioUrl): array
    {
        // Create D-ID talk with webhook URL
        $talkResult = $this->didService->createTalkAsync(
            $teacher->getSourceUrlForDID(),
            $audioUrl
        );

        if (!$talkResult['success']) {
            return $this->markFailed($question, $talkResult['error']);
        }

        // Store task in database for webhook processing
        AvatarGenerationTask::create([
            'question_id' => $question->id,
            'avatar_teacher_id' => $teacher->id,
            'talk_id' => $talkResult['talk_id'],
            'audio_url' => $audioUrl,
            'status' => 'pending',
        ]);

        Log::info('D-ID talk created with webhook', [
            'question_id' => $question->id,
            'talk_id' => $talkResult['talk_id'],
        ]);

        // Return success - the webhook will complete the process
        return [
            'success' => true,
            'talk_id' => $talkResult['talk_id'],
            'audio_url' => $audioUrl,
            'message' => 'Video generation started. Webhook will complete the process.',
        ];
    }

    /**
     * Create D-ID talk with blocking polling (legacy).
     * Waits for D-ID to complete before returning.
     */
    private function createDIDTalkBlocking(Question $question, AvatarTeacher $teacher, array $audioResult): array
    {
        $videoResult = $this->didService->generateAndUploadVideo(
            $teacher->getSourceUrlForDID(),
            $audioResult['audio_url'],
            $question->id
        );

        if (!$videoResult['success']) {
            return $this->markFailed($question, $videoResult['error']);
        }

        // Update with video URL and mark as ready
        $question->update([
            'avatar_video_url' => $videoResult['video_url'],
            'avatar_duration' => $videoResult['duration'] ?? $audioResult['duration'],
            'avatar_status' => 'ready',
        ]);

        Log::info('Avatar generation completed (blocking)', [
            'question_id' => $question->id,
            'video_url' => $videoResult['video_url'],
        ]);

        return [
            'success' => true,
            'audio_url' => $audioResult['audio_url'],
            'video_url' => $videoResult['video_url'],
            'duration' => $videoResult['duration'] ?? $audioResult['duration'],
        ];
    }

    /**
     * Queue avatar generation for multiple questions.
     *
     * @param Collection $questions
     * @param AvatarTeacher $teacher
     * @return int Number of jobs queued
     */
    public function generateBulk(Collection $questions, AvatarTeacher $teacher): int
    {
        $queued = 0;

        foreach ($questions as $question) {
            // Skip if already has avatar
            if ($question->avatar_status === 'ready') {
                continue;
            }

            // Mark as pending
            $question->update([
                'avatar_status' => 'pending',
                'avatar_teacher_id' => $teacher->id,
                'avatar_error' => null,
            ]);

            // Dispatch job
            GenerateAvatarVideoJob::dispatch($question, $teacher)
                ->onQueue('avatar-generation');

            $queued++;
        }

        Log::info('Bulk avatar generation queued', [
            'total_questions' => $questions->count(),
            'queued' => $queued,
            'teacher_id' => $teacher->id,
        ]);

        return $queued;
    }

    /**
     * Retry failed avatar generations.
     *
     * @param AvatarTeacher|null $teacher Use specific teacher, or keep original
     * @return int Number of jobs queued
     */
    public function retryFailed(?AvatarTeacher $teacher = null): int
    {
        $failedQuestions = Question::where('avatar_status', 'failed')
            ->whereNotNull('avatar_teacher_id')
            ->get();

        $queued = 0;

        foreach ($failedQuestions as $question) {
            $useTeacher = $teacher ?? $question->avatarTeacher;

            if (!$useTeacher) {
                continue;
            }

            $question->update([
                'avatar_status' => 'pending',
                'avatar_error' => null,
            ]);

            GenerateAvatarVideoJob::dispatch($question, $useTeacher)
                ->onQueue('avatar-generation');

            $queued++;
        }

        Log::info('Retry failed avatars queued', [
            'queued' => $queued,
        ]);

        return $queued;
    }

    /**
     * Get generation progress stats.
     *
     * @param AvatarTeacher|null $teacher Filter by teacher
     * @return array
     */
    public function getProgress(?AvatarTeacher $teacher = null): array
    {
        $query = Question::whereNotNull('avatar_teacher_id');

        if ($teacher) {
            $query->where('avatar_teacher_id', $teacher->id);
        }

        $total = (clone $query)->count();
        $ready = (clone $query)->where('avatar_status', 'ready')->count();
        $pending = (clone $query)->where('avatar_status', 'pending')->count();
        $generatingAudio = (clone $query)->where('avatar_status', 'generating_audio')->count();
        $generatingVideo = (clone $query)->where('avatar_status', 'generating_video')->count();
        $failed = (clone $query)->where('avatar_status', 'failed')->count();

        return [
            'total' => $total,
            'ready' => $ready,
            'pending' => $pending,
            'generating_audio' => $generatingAudio,
            'generating_video' => $generatingVideo,
            'failed' => $failed,
            'in_progress' => $pending + $generatingAudio + $generatingVideo,
            'percentage' => $total > 0 ? round(($ready / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Get speaking questions that need avatars.
     *
     * @return Collection
     */
    public function getQuestionsNeedingAvatars(): Collection
    {
        return Question::whereHas('testSet', function ($query) {
                $query->whereHas('section', function ($q) {
                    $q->where('slug', 'speaking')->orWhere('name', 'speaking');
                });
            })
            ->whereIn('avatar_status', ['none', 'failed'])
            ->orderBy('test_set_id')
            ->orderBy('order_number')
            ->get();
    }

    /**
     * Extract text from question for TTS.
     *
     * @param Question $question
     * @return string
     */
    private function getQuestionTextForTTS(Question $question): string
    {
        $text = strip_tags($question->content ?? '');

        // For Part 2 cue cards, add the "You should say:" section
        if ($question->question_type === 'part2_cue_card' && !empty($question->form_structure['fields'])) {
            $points = collect($question->form_structure['fields'])
                ->pluck('label')
                ->filter()
                ->implode('. ');

            if ($points) {
                $text .= '. You should say: ' . $points;
            }
        }

        // Clean up extra whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));

        return $text;
    }

    /**
     * Wait for a URL to become accessible (handles CDN propagation delay).
     *
     * @param string $url
     * @param int $maxAttempts
     * @param int $delaySeconds
     * @return bool
     */
    private function waitForUrlAccessible(string $url, int $maxAttempts = 5, int $delaySeconds = 2): bool
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(10)->head($url);

                if ($response->successful()) {
                    Log::debug('URL accessible', [
                        'url' => $url,
                        'attempt' => $attempt,
                    ]);
                    return true;
                }
            } catch (\Exception $e) {
                // Ignore and retry
            }

            if ($attempt < $maxAttempts) {
                Log::debug('Waiting for URL to be accessible', [
                    'url' => $url,
                    'attempt' => $attempt,
                    'delay' => $delaySeconds,
                ]);
                sleep($delaySeconds);
            }
        }

        Log::warning('URL not accessible after max attempts', [
            'url' => $url,
            'attempts' => $maxAttempts,
        ]);

        return false;
    }

    /**
     * Mark question as failed.
     *
     * @param Question $question
     * @param string $error
     * @return array
     */
    private function markFailed(Question $question, string $error): array
    {
        $question->update([
            'avatar_status' => 'failed',
            'avatar_error' => $error,
        ]);

        Log::error('Avatar generation failed', [
            'question_id' => $question->id,
            'error' => $error,
        ]);

        return [
            'success' => false,
            'error' => $error,
        ];
    }

    /**
     * Delete avatar files for a question.
     *
     * @param Question $question
     * @return bool
     */
    public function deleteAvatarFiles(Question $question): bool
    {
        // This could be extended to delete actual files from R2
        // For now, just reset the fields
        $question->update([
            'avatar_audio_url' => null,
            'avatar_video_url' => null,
            'avatar_duration' => null,
            'avatar_status' => 'none',
            'avatar_error' => null,
        ]);

        return true;
    }
}
