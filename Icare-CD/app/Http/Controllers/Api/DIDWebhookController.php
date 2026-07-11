<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCompletedAvatarJob;
use App\Models\AvatarGenerationTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DIDWebhookController extends Controller
{
    /**
     * D-ID's known IP ranges for webhook requests.
     * Update this list if D-ID provides official IP ranges.
     */
    private const ALLOWED_IPS = [
        '3.251.*.*',      // AWS EU (Ireland) - D-ID infrastructure
        '54.220.*.*',     // AWS EU (Ireland)
        '52.214.*.*',     // AWS EU (Ireland)
        '34.249.*.*',     // AWS EU (Ireland)
        '63.33.*.*',      // AWS EU (Ireland)
        '127.0.0.1',      // Localhost for testing
    ];

    /**
     * Handle D-ID webhook callback.
     *
     * D-ID sends webhook when talk generation is complete:
     * {
     *   "id": "talk_id",
     *   "status": "done|error|rejected",
     *   "result_url": "https://...",
     *   "duration": 5.2,
     *   "error": { "description": "..." }
     * }
     */
    public function handle(Request $request): JsonResponse
    {
        // Verify the request is from D-ID
        if (!$this->verifyRequest($request)) {
            Log::warning('D-ID webhook: unauthorized request', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Log::info('D-ID webhook received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
        ]);

        $talkId = $request->input('id');
        $status = $request->input('status');

        if (!$talkId) {
            Log::warning('D-ID webhook missing talk_id');
            return response()->json(['error' => 'Missing talk_id'], 400);
        }

        // Find the task by talk_id
        $task = AvatarGenerationTask::findByTalkId($talkId);

        if (!$task) {
            Log::warning('D-ID webhook: task not found', ['talk_id' => $talkId]);
            return response()->json(['error' => 'Task not found'], 404);
        }

        // Already processed
        if ($task->isCompleted()) {
            Log::info('D-ID webhook: task already completed', ['talk_id' => $talkId]);
            return response()->json(['status' => 'already_processed']);
        }

        // Handle based on status
        if ($status === 'done') {
            $resultUrl = $request->input('result_url');
            $duration = $request->input('duration');

            if (!$resultUrl) {
                Log::error('D-ID webhook: done but no result_url', ['talk_id' => $talkId]);
                $task->markAsFailed('Webhook received done status but no result_url');
                return response()->json(['error' => 'Missing result_url'], 400);
            }

            // Update task with result URL
            $task->update([
                'status' => 'processing',
                'result_url' => $resultUrl,
                'duration' => $duration,
                'webhook_received_at' => now(),
            ]);

            // Dispatch job to download and upload video
            ProcessCompletedAvatarJob::dispatch($task);

            Log::info('D-ID webhook: dispatched process job', [
                'talk_id' => $talkId,
                'task_id' => $task->id,
            ]);

            return response()->json(['status' => 'processing']);

        } elseif (in_array($status, ['error', 'rejected'])) {
            $errorDescription = $request->input('error.description', 'Unknown D-ID error');

            $task->markAsFailed($errorDescription);

            // Also update the question status
            $task->question->update([
                'avatar_status' => 'failed',
                'avatar_error' => $errorDescription,
            ]);

            Log::error('D-ID webhook: talk failed', [
                'talk_id' => $talkId,
                'error' => $errorDescription,
            ]);

            return response()->json(['status' => 'failed']);

        } else {
            // Unknown status, log it
            Log::warning('D-ID webhook: unknown status', [
                'talk_id' => $talkId,
                'status' => $status,
            ]);

            return response()->json(['status' => 'unknown_status']);
        }
    }

    /**
     * Verify the webhook request is legitimate.
     * Uses IP whitelist + result_url domain validation.
     */
    private function verifyRequest(Request $request): bool
    {
        $ip = $request->ip();

        // Check IP whitelist
        if (!$this->isAllowedIP($ip)) {
            return false;
        }

        // Validate result_url domain if present (must be from D-ID CDN)
        $resultUrl = $request->input('result_url');
        if ($resultUrl && !$this->isValidResultUrl($resultUrl)) {
            Log::warning('D-ID webhook: invalid result_url domain', [
                'result_url' => $resultUrl,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Check if IP is in allowed list (supports wildcards).
     */
    private function isAllowedIP(string $ip): bool
    {
        foreach (self::ALLOWED_IPS as $pattern) {
            $regex = str_replace(['.', '*'], ['\.', '\d+'], $pattern);
            if (preg_match('/^' . $regex . '$/', $ip)) {
                return true;
            }
        }

        // Allow if D-ID webhook verification is disabled (for development)
        if (config('app.env') === 'local' && config('app.debug') === true) {
            return true;
        }

        return false;
    }

    /**
     * Validate result URL is from D-ID's CDN.
     */
    private function isValidResultUrl(string $url): bool
    {
        $allowedDomains = [
            'd-id-talks-prod.s3.us-west-2.amazonaws.com',
            'd-id-talks-prod.s3.amazonaws.com',
            'd-id.com',
            'www.d-id.com',
            'api.d-id.com',
        ];

        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['host'])) {
            return false;
        }

        foreach ($allowedDomains as $domain) {
            if ($parsedUrl['host'] === $domain || str_ends_with($parsedUrl['host'], '.' . $domain)) {
                return true;
            }
        }

        return false;
    }
}
