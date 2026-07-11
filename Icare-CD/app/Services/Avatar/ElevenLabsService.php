<?php

namespace App\Services\Avatar;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ElevenLabsService
{
    private ?string $apiKey;
    private string $baseUrl = 'https://api.elevenlabs.io/v1';

    public function __construct()
    {
        $this->apiKey = config('services.elevenlabs.api_key');
    }

    /**
     * Generate speech from text using ElevenLabs TTS.
     *
     * @param string $text The text to convert to speech
     * @param string $voiceId The ElevenLabs voice ID
     * @param array $options Optional settings (stability, similarity_boost, etc.)
     * @return array ['success' => bool, 'audio_content' => string|null, 'duration' => float|null, 'error' => string|null]
     */
    public function generateSpeech(string $text, string $voiceId, array $options = []): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'ElevenLabs API key not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'xi-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'audio/mpeg',
            ])->timeout(60)->post("{$this->baseUrl}/text-to-speech/{$voiceId}", [
                'text' => $text,
                'model_id' => $options['model_id'] ?? 'eleven_turbo_v2_5',
                'voice_settings' => [
                    'stability' => $options['stability'] ?? 0.5,
                    'similarity_boost' => $options['similarity_boost'] ?? 0.75,
                    'style' => $options['style'] ?? 0.0,
                    'use_speaker_boost' => $options['use_speaker_boost'] ?? true,
                ],
            ]);

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['detail']['message'] ?? $errorBody['detail'] ?? 'Unknown error';

                Log::error('ElevenLabs API error', [
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'text_length' => strlen($text),
                ]);

                return [
                    'success' => false,
                    'error' => "ElevenLabs API error: {$errorMessage}",
                ];
            }

            $audioContent = $response->body();

            // Estimate duration (rough estimate: ~150 words per minute for English)
            $wordCount = str_word_count($text);
            $estimatedDuration = ($wordCount / 150) * 60; // in seconds

            Log::info('ElevenLabs speech generated', [
                'voice_id' => $voiceId,
                'text_length' => strlen($text),
                'audio_size' => strlen($audioContent),
                'estimated_duration' => $estimatedDuration,
            ]);

            return [
                'success' => true,
                'audio_content' => $audioContent,
                'duration' => round($estimatedDuration, 2),
                'content_type' => 'audio/mpeg',
            ];

        } catch (\Exception $e) {
            Log::error('ElevenLabs exception', [
                'error' => $e->getMessage(),
                'voice_id' => $voiceId,
            ]);

            return [
                'success' => false,
                'error' => 'ElevenLabs request failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Upload audio to R2 storage.
     *
     * @param string $audioContent The raw audio content
     * @param string $filename The filename to use
     * @return array ['success' => bool, 'url' => string|null, 'path' => string|null, 'error' => string|null]
     */
    public function uploadAudioToR2(string $audioContent, string $filename): array
    {
        try {
            $disk = $this->shouldUseR2() ? 'r2' : 'public';
            $path = 'speaking-avatars/audio/' . date('Y/m') . '/' . $filename;

            Storage::disk($disk)->put($path, $audioContent, 'public');

            $url = $this->getFileUrl($path, $disk);

            Log::info('Avatar audio uploaded', [
                'disk' => $disk,
                'path' => $path,
                'size' => strlen($audioContent),
            ]);

            return [
                'success' => true,
                'url' => $url,
                'path' => $path,
                'disk' => $disk,
            ];

        } catch (\Exception $e) {
            Log::error('Avatar audio upload failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to upload audio: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate speech and upload to R2 in one step.
     *
     * @param string $text The text to convert
     * @param string $voiceId The voice ID
     * @param int $questionId The question ID (for filename)
     * @return array Complete result with URL
     */
    public function generateAndUpload(string $text, string $voiceId, int $questionId): array
    {
        // Generate speech
        $speechResult = $this->generateSpeech($text, $voiceId);

        if (!$speechResult['success']) {
            return $speechResult;
        }

        // Upload to R2
        $filename = "q-{$questionId}-" . Str::random(8) . '.mp3';
        $uploadResult = $this->uploadAudioToR2($speechResult['audio_content'], $filename);

        if (!$uploadResult['success']) {
            return $uploadResult;
        }

        return [
            'success' => true,
            'audio_url' => $uploadResult['url'],
            'audio_path' => $uploadResult['path'],
            'duration' => $speechResult['duration'],
            'disk' => $uploadResult['disk'],
        ];
    }

    /**
     * Get available voices from ElevenLabs.
     * Returns all voices grouped by category (custom first, then recommended, then others).
     *
     * @param bool $includeAll If true, returns all voices; if false, only IELTS-suitable voices
     * @return array ['success' => bool, 'voices' => array|null, 'grouped' => array|null, 'error' => string|null]
     */
    public function getVoices(bool $includeAll = true): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'ElevenLabs API key not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'xi-api-key' => $this->apiKey,
            ])->timeout(30)->get("{$this->baseUrl}/voices");

            if ($response->failed()) {
                return [
                    'success' => false,
                    'error' => 'Failed to fetch voices',
                ];
            }

            $voices = $response->json()['voices'] ?? [];

            // Format all voices
            $formattedVoices = collect($voices)->map(function ($voice) {
                $labels = $voice['labels'] ?? [];
                $category = $voice['category'] ?? 'premade';

                // Determine voice type for grouping
                $isCustom = in_array($category, ['cloned', 'generated', 'professional']);
                $isRecommended = $this->isRecommendedVoice($labels);

                return [
                    'voice_id' => $voice['voice_id'],
                    'name' => $voice['name'],
                    'category' => $category,
                    'is_custom' => $isCustom,
                    'is_recommended' => $isRecommended,
                    'labels' => $labels,
                    'preview_url' => $voice['preview_url'] ?? null,
                    'accent' => ucfirst($labels['accent'] ?? 'Unknown'),
                    'gender' => ucfirst($labels['gender'] ?? 'Unknown'),
                    'age' => ucfirst(str_replace('_', ' ', $labels['age'] ?? 'Unknown')),
                    'language' => $labels['language'] ?? 'en',
                    'description' => $voice['description'] ?? null,
                ];
            });

            // Filter if not including all
            if (!$includeAll) {
                $formattedVoices = $formattedVoices->filter(function ($voice) {
                    return $voice['is_custom'] || $voice['is_recommended'];
                });
            }

            // Sort: custom first, then recommended, then others
            $sortedVoices = $formattedVoices->sortBy(function ($voice) {
                if ($voice['is_custom']) return 0;
                if ($voice['is_recommended']) return 1;
                return 2;
            })->values();

            // Group voices for easier display
            $grouped = [
                'custom' => $sortedVoices->filter(fn($v) => $v['is_custom'])->values()->toArray(),
                'recommended' => $sortedVoices->filter(fn($v) => !$v['is_custom'] && $v['is_recommended'])->values()->toArray(),
                'other' => $sortedVoices->filter(fn($v) => !$v['is_custom'] && !$v['is_recommended'])->values()->toArray(),
            ];

            return [
                'success' => true,
                'voices' => $sortedVoices->toArray(),
                'grouped' => $grouped,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch voices: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if a voice is recommended for IELTS examiner use.
     */
    private function isRecommendedVoice(array $labels): bool
    {
        $language = $labels['language'] ?? '';
        $age = $labels['age'] ?? '';
        $accent = $labels['accent'] ?? '';

        // Must be English
        if (!empty($language) && $language !== 'en') {
            return false;
        }

        // Prefer mature voices
        $preferredAges = ['middle_aged', 'old', 'young'];
        if (!empty($age) && !in_array($age, $preferredAges)) {
            return false;
        }

        // Prefer common IELTS accents
        $preferredAccents = ['british', 'american', 'australian', 'irish'];
        if (!empty($accent) && !in_array($accent, $preferredAccents)) {
            return false;
        }

        return true;
    }

    /**
     * Get user subscription info (remaining characters, etc.).
     *
     * @return array
     */
    public function getSubscriptionInfo(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'ElevenLabs API key not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'xi-api-key' => $this->apiKey,
            ])->timeout(30)->get("{$this->baseUrl}/user/subscription");

            if ($response->failed()) {
                return [
                    'success' => false,
                    'error' => 'Failed to fetch subscription info',
                ];
            }

            return [
                'success' => true,
                'subscription' => $response->json(),
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
