<?php

namespace App\Http\Controllers;

use App\Models\SpeakingRecording;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Response;

class AudioStreamController extends Controller
{
    /**
     * Stream audio file from CDN or local storage
     */
    public function stream($recordingId)
    {
        $recording = SpeakingRecording::findOrFail($recordingId);

        // Check access: the student who owns the attempt, an admin, or a teacher assigned to
        // evaluate THIS attempt.
        $user = auth()->user();
        $attempt = $recording->answer->attempt;

        $hasAccess = $user->id === $attempt->user_id || $user->is_admin;

        // H12: a teacher may stream a recording ONLY for an attempt whose human evaluation is
        // assigned to them — not every teacher for every student's private recording. (The old
        // code used $user->hasRole(...), which is undefined and would 500 for real teachers.)
        if (!$hasAccess && $user->teacher) {
            $evalRequest = $attempt->humanEvaluationRequest;
            $hasAccess = $evalRequest && (int) $evalRequest->teacher_id === (int) $user->teacher->id;
        }

        if (!$hasAccess) {
            abort(403, 'You do not have access to this recording.');
        }

        $disk = $recording->storage_disk ?? 'public';
        $mimeType = $recording->mime_type ?? 'audio/webm';

        // For R2/CDN storage, proxy the audio to avoid CORS issues
        if ($disk === 'r2' && $recording->file_url) {
            return $this->proxyFromCDN($recording->file_url, $mimeType);
        }

        // For local storage, stream the file directly
        return $this->streamFromLocal($recording, $disk, $mimeType);
    }

    /**
     * Proxy audio from CDN to avoid CORS issues
     */
    private function proxyFromCDN(string $cdnUrl, string $mimeType)
    {
        try {
            // Fetch audio from CDN
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => $mimeType,
                ])
                ->get($cdnUrl);

            if (!$response->successful()) {
                abort(404, 'Audio file not found on CDN.');
            }

            $content = $response->body();
            $size = strlen($content);

            return response($content, 200, [
                'Content-Type' => $mimeType,
                'Content-Length' => $size,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'public, max-age=3600',
                'Content-Disposition' => 'inline',
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to proxy audio from CDN', [
                'url' => $cdnUrl,
                'error' => $e->getMessage()
            ]);
            abort(500, 'Failed to load audio file.');
        }
    }

    /**
     * Stream audio from local storage
     */
    private function streamFromLocal(SpeakingRecording $recording, string $disk, string $mimeType)
    {
        $path = $recording->file_path;

        if (!Storage::disk($disk)->exists($path)) {
            abort(404, 'Audio file not found.');
        }

        $size = Storage::disk($disk)->size($path);

        return response()->stream(function () use ($disk, $path) {
            $stream = Storage::disk($disk)->readStream($path);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => $size,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=3600',
            'Content-Disposition' => 'inline',
        ]);
    }
}
