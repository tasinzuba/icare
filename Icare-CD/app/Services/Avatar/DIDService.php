<?php

namespace App\Services\Avatar;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DIDService
{
    private ?string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.d_id.api_key');
        $this->baseUrl = config('services.d_id.api_url', 'https://api.d-id.com');
    }

    /**
     * Create a talking avatar video.
     *
     * @param string $sourceUrl URL of the source image (teacher photo)
     * @param string $audioUrl URL of the audio file
     * @param array $options Optional settings (driver_expressions, webhook_url, etc.)
     * @return array ['success' => bool, 'talk_id' => string|null, 'error' => string|null]
     */
    public function createTalk(string $sourceUrl, string $audioUrl, array $options = []): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'D-ID API key not configured',
            ];
        }

        try {
            $payload = [
                'source_url' => $sourceUrl,
                'script' => [
                    'type' => 'audio',
                    'audio_url' => $audioUrl,
                ],
                'config' => [
                    'fluent' => $options['fluent'] ?? true,
                    'pad_audio' => $options['pad_audio'] ?? 0.5,
                    'stitch' => $options['stitch'] ?? true,
                ],
            ];

            // Add driver_url only if explicitly provided
            // D-ID recommends auto-match (no driver_url) for best results
            if (!empty($options['driver_url'])) {
                $payload['driver_url'] = $options['driver_url'];
            }

            // Add webhook URL for async notification
            if (!empty($options['webhook_url'])) {
                $payload['webhook'] = $options['webhook_url'];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
                'Content-Type' => 'application/json',
            ])->timeout(60)->post("{$this->baseUrl}/talks", $payload);

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['description'] ?? $errorBody['message'] ?? 'Unknown error';

                // Get more specific error details if available
                $details = $errorBody['details'] ?? $errorBody['error'] ?? null;
                $kind = $errorBody['kind'] ?? null;

                Log::error('D-ID create talk error', [
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'details' => $details,
                    'kind' => $kind,
                    'full_response' => $errorBody,
                    'source_url' => $sourceUrl,
                    'audio_url' => $audioUrl,
                ]);

                // Build more informative error message
                $fullError = $errorMessage;
                if ($details) {
                    $fullError .= ' - ' . (is_array($details) ? json_encode($details) : $details);
                }

                return [
                    'success' => false,
                    'error' => "D-ID API error: {$fullError}",
                ];
            }

            $data = $response->json();

            Log::info('D-ID talk created', [
                'talk_id' => $data['id'],
                'source_url' => $sourceUrl,
                'webhook' => $options['webhook_url'] ?? 'none',
            ]);

            return [
                'success' => true,
                'talk_id' => $data['id'],
                'status' => $data['status'] ?? 'created',
            ];

        } catch (\Exception $e) {
            Log::error('D-ID create talk exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'D-ID request failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create a talk with webhook (non-blocking).
     * Returns immediately after creating the talk. D-ID will call webhook when done.
     *
     * @param string $sourceUrl URL of the source image
     * @param string $audioUrl URL of the audio file
     * @param array $options Additional options
     * @return array
     */
    public function createTalkAsync(string $sourceUrl, string $audioUrl, array $options = []): array
    {
        // Get webhook URL from config or options
        $webhookUrl = $options['webhook_url'] ?? $this->getWebhookUrl();

        if (!$webhookUrl) {
            Log::warning('D-ID: No webhook URL configured, falling back to polling');
        }

        $options['webhook_url'] = $webhookUrl;

        return $this->createTalk($sourceUrl, $audioUrl, $options);
    }

    /**
     * Get the webhook URL for D-ID callbacks.
     */
    public function getWebhookUrl(): ?string
    {
        $baseUrl = config('app.url');
        if (!$baseUrl) {
            return null;
        }

        return rtrim($baseUrl, '/') . '/api/webhooks/d-id';
    }

    /**
     * Get the status of a talk.
     *
     * @param string $talkId The talk ID from createTalk
     * @return array ['success' => bool, 'status' => string, 'result_url' => string|null, 'error' => string|null]
     */
    public function getTalkStatus(string $talkId): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'D-ID API key not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
            ])->timeout(30)->get("{$this->baseUrl}/talks/{$talkId}");

            if ($response->failed()) {
                return [
                    'success' => false,
                    'error' => 'Failed to get talk status',
                ];
            }

            $data = $response->json();

            return [
                'success' => true,
                'status' => $data['status'],
                'result_url' => $data['result_url'] ?? null,
                'duration' => $data['duration'] ?? null,
                'error' => $data['error']['description'] ?? null,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to get talk status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Wait for a talk to complete (with polling).
     *
     * @param string $talkId The talk ID
     * @param int $maxWaitSeconds Maximum seconds to wait
     * @param int $pollIntervalSeconds Seconds between status checks
     * @return array Complete result with video URL
     */
    public function waitForTalkCompletion(string $talkId, int $maxWaitSeconds = 120, int $pollIntervalSeconds = 5): array
    {
        $startTime = time();

        while ((time() - $startTime) < $maxWaitSeconds) {
            $statusResult = $this->getTalkStatus($talkId);

            if (!$statusResult['success']) {
                return $statusResult;
            }

            $status = $statusResult['status'];

            if ($status === 'done') {
                Log::info('D-ID talk completed', [
                    'talk_id' => $talkId,
                    'duration' => $statusResult['duration'],
                    'wait_time' => time() - $startTime,
                ]);

                return [
                    'success' => true,
                    'status' => 'done',
                    'result_url' => $statusResult['result_url'],
                    'duration' => $statusResult['duration'],
                ];
            }

            if ($status === 'error' || $status === 'rejected') {
                Log::error('D-ID talk failed', [
                    'talk_id' => $talkId,
                    'status' => $status,
                    'error' => $statusResult['error'],
                ]);

                return [
                    'success' => false,
                    'status' => $status,
                    'error' => $statusResult['error'] ?? 'Talk generation failed',
                ];
            }

            // Still processing, wait and retry
            sleep($pollIntervalSeconds);
        }

        return [
            'success' => false,
            'error' => 'Talk generation timed out after ' . $maxWaitSeconds . ' seconds',
        ];
    }

    /**
     * Download video from D-ID and upload to R2.
     *
     * @param string $videoUrl The D-ID result URL
     * @param int $questionId The question ID (for filename)
     * @return array ['success' => bool, 'url' => string|null, 'path' => string|null]
     */
    public function downloadAndUploadVideo(string $videoUrl, int $questionId): array
    {
        try {
            // Download video from D-ID
            $response = Http::timeout(120)->get($videoUrl);

            if ($response->failed()) {
                return [
                    'success' => false,
                    'error' => 'Failed to download video from D-ID',
                ];
            }

            $videoContent = $response->body();

            // Upload to R2
            $disk = $this->shouldUseR2() ? 'r2' : 'public';
            $filename = "q-{$questionId}-" . Str::random(8) . '.mp4';
            $path = 'speaking-avatars/video/' . date('Y/m') . '/' . $filename;

            Storage::disk($disk)->put($path, $videoContent, 'public');

            $url = $this->getFileUrl($path, $disk);

            Log::info('Avatar video uploaded', [
                'disk' => $disk,
                'path' => $path,
                'size' => strlen($videoContent),
                'question_id' => $questionId,
            ]);

            return [
                'success' => true,
                'url' => $url,
                'path' => $path,
                'disk' => $disk,
                'size' => strlen($videoContent),
            ];

        } catch (\Exception $e) {
            Log::error('Avatar video upload failed', [
                'error' => $e->getMessage(),
                'question_id' => $questionId,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to upload video: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate video and upload to R2 (full pipeline).
     *
     * @param string $sourceUrl Teacher photo URL
     * @param string $audioUrl Generated audio URL
     * @param int $questionId Question ID
     * @param array $options Additional options
     * @return array Complete result with R2 video URL
     */
    public function generateAndUploadVideo(string $sourceUrl, string $audioUrl, int $questionId, array $options = []): array
    {
        // Step 1: Create talk
        $createResult = $this->createTalk($sourceUrl, $audioUrl, $options);

        if (!$createResult['success']) {
            return $createResult;
        }

        // Step 2: Wait for completion
        $completionResult = $this->waitForTalkCompletion(
            $createResult['talk_id'],
            $options['max_wait'] ?? 120,
            $options['poll_interval'] ?? 5
        );

        if (!$completionResult['success']) {
            return $completionResult;
        }

        // Step 3: Download and upload to R2
        $uploadResult = $this->downloadAndUploadVideo(
            $completionResult['result_url'],
            $questionId
        );

        if (!$uploadResult['success']) {
            return $uploadResult;
        }

        return [
            'success' => true,
            'video_url' => $uploadResult['url'],
            'video_path' => $uploadResult['path'],
            'duration' => $completionResult['duration'],
            'disk' => $uploadResult['disk'],
        ];
    }

    /**
     * Delete a talk from D-ID.
     *
     * @param string $talkId
     * @return array
     */
    public function deleteTalk(string $talkId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
            ])->delete("{$this->baseUrl}/talks/{$talkId}");

            return [
                'success' => $response->successful(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get remaining credits info.
     *
     * @return array
     */
    public function getCreditsInfo(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'D-ID API key not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
            ])->get("{$this->baseUrl}/credits");

            if ($response->failed()) {
                return [
                    'success' => false,
                    'error' => 'Failed to fetch credits info',
                ];
            }

            return [
                'success' => true,
                'credits' => $response->json(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if R2 is configured.
     */
    private function shouldUseR2(): bool
    {
        return !empty(config('filesystems.disks.r2.key')) &&
               !empty(config('filesystems.disks.r2.secret')) &&
               !empty(config('filesystems.disks.r2.bucket'));
    }

    /**
     * Get public URL for a file.
     */
    private function getFileUrl(string $path, string $disk): string
    {
        if ($disk === 'r2') {
            $baseUrl = rtrim(config('filesystems.disks.r2.url'), '/');
            return $baseUrl . '/' . ltrim($path, '/');
        }
        return asset('storage/' . $path);
    }
}
