<?php

namespace App\Services\AI;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Exception;

class AIEvaluationServiceWithCDN extends AIEvaluationService
{
    private int $maxRetries = 3;
    private array $retryDelays = [1, 2, 3]; // seconds
    private ?float $lastAudioDuration = null;

    /**
     * Override evaluateSpeaking to capture and pass audio duration
     */
    public function evaluateSpeaking(string $audioPath, string $question, int $partNumber, ?string $cueCardPoints = null, ?float $audioDuration = null): array
    {
        // Reset duration for new evaluation
        $this->lastAudioDuration = null;

        // Call parent which will call our overridden transcribeAudio
        // But we need to intercept to get duration, so we do transcription here
        try {
            Log::info('Starting CDN speaking evaluation', [
                'audio_path' => $audioPath,
                'part_number' => $partNumber,
                'has_cue_card' => !empty($cueCardPoints)
            ]);

            // Transcribe with duration capture
            $transcriptionResult = $this->transcribeAudioWithDuration($audioPath);
            $transcription = $transcriptionResult['text'];
            $capturedDuration = $transcriptionResult['duration'] ?? 0;

            if (empty($transcription)) {
                throw new Exception('Failed to transcribe audio - no speech detected');
            }

            Log::info('Transcription complete', [
                'length' => strlen($transcription),
                'duration' => $capturedDuration
            ]);

            // Pre-evaluation text analysis
            $textAnalyzer = new SpeakingTextAnalyzer();
            $preAnalysis = $textAnalyzer->analyze($transcription, $partNumber, $cueCardPoints);

            Log::info('Pre-analysis complete', [
                'filler_count' => $preAnalysis['fluency_indicators']['filler_count_total'] ?? 0,
                'lexical_diversity' => $preAnalysis['lexical_analysis']['lexical_diversity_ttr'] ?? 0,
                'coherence_markers' => $preAnalysis['coherence_markers']['total_markers'] ?? 0
            ]);

            // Calculate speech rate
            $speechMetrics = $this->calculateSpeechMetrics(str_word_count($transcription), $capturedDuration);

            // Build enhanced prompt
            $prompt = $this->buildEnhancedSpeakingPrompt($transcription, $question, $partNumber, $preAnalysis, $speechMetrics, $cueCardPoints);

            // Call GPT-4
            $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => $this->getSpeakingSystemPrompt()],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.3,
                'max_tokens' => 3000,
            ]);

            $content = $response->choices[0]->message->content;
            $evaluation = json_decode($content, true);

            if (!$evaluation) {
                Log::error('Failed to parse AI response for speaking');
                throw new Exception('Failed to parse AI response');
            }

            return $this->formatEnhancedSpeakingEvaluation($evaluation, $transcription, $preAnalysis, $speechMetrics, $capturedDuration);

        } catch (\Exception $e) {
            Log::error('CDN Speaking evaluation failed', [
                'error' => $e->getMessage(),
                'audio_path' => $audioPath,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Transcribe audio and return both text and duration
     */
    protected function transcribeAudioWithDuration(string $audioPath): array
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                Log::info("Transcription attempt {$attempt}/{$this->maxRetries}", ['path' => $audioPath]);

                // Check if it's a URL (CDN) or local path
                if (filter_var($audioPath, FILTER_VALIDATE_URL)) {
                    return $this->transcribeFromCDNWithDuration($audioPath);
                } else {
                    return $this->transcribeFromLocalWithDuration($audioPath);
                }
            } catch (\Exception $e) {
                $lastException = $e;
                $errorMessage = $e->getMessage();

                Log::warning("Transcription attempt {$attempt} failed", [
                    'error' => $errorMessage,
                    'path' => $audioPath
                ]);

                // Don't retry for certain errors
                if (str_contains($errorMessage, 'file too large') ||
                    str_contains($errorMessage, 'file too small') ||
                    str_contains($errorMessage, 'file not found')) {
                    break;
                }

                // Wait before retrying (except on last attempt)
                if ($attempt < $this->maxRetries) {
                    sleep($this->retryDelays[$attempt - 1] ?? 2);
                }
            }
        }

        Log::error('Audio transcription failed after all retries', [
            'error' => $lastException->getMessage(),
            'path' => $audioPath
        ]);

        throw $lastException;
    }

    /**
     * Legacy transcribe method for compatibility
     */
    protected function transcribeAudio(string $audioPath): string
    {
        $result = $this->transcribeAudioWithDuration($audioPath);
        return $result['text'];
    }

    /**
     * Transcribe audio from CDN URL
     */
    private function transcribeFromCDN(string $audioUrl): string
    {
        $tempPath = null;
        $mp3Path = null;

        try {
            Log::info('Starting CDN audio transcription', [
                'url' => $audioUrl
            ]);

            // Get original extension from URL
            $urlPath = parse_url($audioUrl, PHP_URL_PATH);
            $originalExt = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION) ?: 'webm');

            // Download audio file temporarily with correct extension
            $tempPath = sys_get_temp_dir() . '/' . uniqid('audio_') . '.' . $originalExt;

            // Download file from CDN with longer timeout and retry
            $response = Http::timeout(120)
                ->retry(3, 1000)
                ->get($audioUrl);

            if (!$response->successful()) {
                throw new Exception("Failed to download audio from CDN: HTTP " . $response->status());
            }

            // Save to temp file
            file_put_contents($tempPath, $response->body());

            // Check file size
            $fileSize = filesize($tempPath);
            Log::info('Audio file downloaded', ['size' => $fileSize, 'ext' => $originalExt]);

            if ($fileSize > 25 * 1024 * 1024) {
                throw new Exception("Audio file too large: " . round($fileSize / 1024 / 1024, 2) . "MB (max 25MB)");
            }

            if ($fileSize < 1000) {
                throw new Exception("Audio file too small or empty: {$fileSize} bytes. Please record again.");
            }

            // Convert webm/ogg to mp3 if needed (Whisper works better with mp3)
            $fileToTranscribe = $tempPath;
            if (in_array($originalExt, ['webm', 'ogg', 'opus'])) {
                $mp3Path = $this->convertToMp3($tempPath, $originalExt);
                if ($mp3Path) {
                    $fileToTranscribe = $mp3Path;
                }
            }

            // Transcribe using OpenAI Whisper
            $transcriptionResponse = OpenAI::audio()->transcribe([
                'model' => 'whisper-1',
                'file' => fopen($fileToTranscribe, 'r'),
                'response_format' => 'verbose_json', // Get more details
                'language' => 'en',
                'prompt' => 'This is an IELTS speaking test response in English.' // Help Whisper understand context
            ]);

            // Clean up temp files
            $this->cleanupTempFiles($tempPath, $mp3Path);

            $transcribedText = $transcriptionResponse->text ?? '';
            $audioDuration = $transcriptionResponse->duration ?? 0;

            Log::info('Transcription result', [
                'text' => $transcribedText,
                'duration' => $audioDuration,
                'word_count' => str_word_count($transcribedText)
            ]);

            // Check audio duration - minimum 10 seconds for valid response
            if ($audioDuration > 0 && $audioDuration < 10) {
                Log::warning('Audio too short', ['duration' => $audioDuration]);
                throw new Exception("Recording is too short ({$audioDuration} seconds). IELTS speaking responses should be at least 15-30 seconds. Please record a longer response.");
            }

            if (empty(trim($transcribedText))) {
                throw new Exception('No speech detected in audio recording. Please ensure you speak clearly and your microphone is working.');
            }

            // Check if transcription is too short (likely noise or incomplete response)
            // IELTS Part 1 needs at least 2-3 sentences (~20 words), Part 2 needs 150+ words
            $wordCount = str_word_count($transcribedText);
            $minWords = 15; // Minimum for any valid IELTS response

            if ($wordCount < $minWords) {
                Log::warning('Very short transcription detected', [
                    'text' => $transcribedText,
                    'word_count' => $wordCount,
                    'duration' => $audioDuration
                ]);
                throw new Exception("Your response is too short ({$wordCount} words). IELTS speaking requires at least 2-3 complete sentences. Please provide a more detailed response.");
            }

            Log::info('CDN transcription successful', [
                'text_length' => strlen($transcribedText),
                'word_count' => $wordCount,
                'duration' => $audioDuration
            ]);

            return $transcribedText;

        } catch (\Exception $e) {
            // Clean up temp files if exist
            $this->cleanupTempFiles($tempPath, $mp3Path);
            throw $e;
        }
    }

    /**
     * Transcribe audio from CDN URL and return with duration
     */
    private function transcribeFromCDNWithDuration(string $audioUrl): array
    {
        $tempPath = null;
        $mp3Path = null;

        try {
            Log::info('Starting CDN audio transcription with duration', ['url' => $audioUrl]);

            $urlPath = parse_url($audioUrl, PHP_URL_PATH);
            $originalExt = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION) ?: 'webm');
            $tempPath = sys_get_temp_dir() . '/' . uniqid('audio_') . '.' . $originalExt;

            $response = Http::timeout(120)->retry(3, 1000)->get($audioUrl);

            if (!$response->successful()) {
                throw new Exception("Failed to download audio from CDN: HTTP " . $response->status());
            }

            file_put_contents($tempPath, $response->body());
            $fileSize = filesize($tempPath);

            if ($fileSize > 25 * 1024 * 1024) {
                throw new Exception("Audio file too large: " . round($fileSize / 1024 / 1024, 2) . "MB (max 25MB)");
            }

            if ($fileSize < 1000) {
                throw new Exception("Audio file too small or empty: {$fileSize} bytes. Please record again.");
            }

            $fileToTranscribe = $tempPath;
            if (in_array($originalExt, ['webm', 'ogg', 'opus'])) {
                $mp3Path = $this->convertToMp3($tempPath, $originalExt);
                if ($mp3Path) {
                    $fileToTranscribe = $mp3Path;
                }
            }

            $transcriptionResponse = OpenAI::audio()->transcribe([
                'model' => 'whisper-1',
                'file' => fopen($fileToTranscribe, 'r'),
                'response_format' => 'verbose_json',
                'language' => 'en',
                'prompt' => 'This is an IELTS speaking test response in English.'
            ]);

            $this->cleanupTempFiles($tempPath, $mp3Path);

            $transcribedText = $transcriptionResponse->text ?? '';
            $audioDuration = $transcriptionResponse->duration ?? 0;

            // Validation
            if ($audioDuration > 0 && $audioDuration < 10) {
                throw new Exception("Recording is too short ({$audioDuration} seconds). IELTS speaking responses should be at least 15-30 seconds.");
            }

            if (empty(trim($transcribedText))) {
                throw new Exception('No speech detected in audio recording.');
            }

            $wordCount = str_word_count($transcribedText);
            if ($wordCount < 15) {
                throw new Exception("Your response is too short ({$wordCount} words). Please provide a more detailed response.");
            }

            return ['text' => $transcribedText, 'duration' => $audioDuration];

        } catch (\Exception $e) {
            $this->cleanupTempFiles($tempPath, $mp3Path);
            throw $e;
        }
    }

    /**
     * Transcribe audio from local path and return with duration
     */
    private function transcribeFromLocalWithDuration(string $audioPath): array
    {
        $fullPath = $audioPath;

        if (!file_exists($fullPath)) {
            throw new Exception("Audio file not found: {$fullPath}");
        }

        $fileSize = filesize($fullPath);
        if ($fileSize > 25 * 1024 * 1024) {
            throw new Exception("Audio file too large: " . round($fileSize / 1024 / 1024, 2) . "MB (max 25MB)");
        }

        if ($fileSize < 1000) {
            throw new Exception("Audio file too small or empty. Please record again.");
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $fileToTranscribe = $fullPath;
        $mp3Path = null;

        if (in_array($ext, ['webm', 'ogg', 'opus'])) {
            $mp3Path = $this->convertToMp3($fullPath, $ext);
            if ($mp3Path) {
                $fileToTranscribe = $mp3Path;
            }
        }

        try {
            $response = OpenAI::audio()->transcribe([
                'model' => 'whisper-1',
                'file' => fopen($fileToTranscribe, 'r'),
                'response_format' => 'verbose_json',
                'language' => 'en',
                'prompt' => 'This is an IELTS speaking test response in English.'
            ]);

            if ($mp3Path && file_exists($mp3Path)) {
                @unlink($mp3Path);
            }

            $transcribedText = $response->text ?? '';
            $audioDuration = $response->duration ?? 0;

            if ($audioDuration > 0 && $audioDuration < 10) {
                throw new Exception("Recording is too short ({$audioDuration} seconds).");
            }

            if (empty(trim($transcribedText))) {
                throw new Exception('No speech detected in audio recording.');
            }

            $wordCount = str_word_count($transcribedText);
            if ($wordCount < 15) {
                throw new Exception("Your response is too short ({$wordCount} words).");
            }

            return ['text' => $transcribedText, 'duration' => $audioDuration];

        } catch (\Exception $e) {
            if ($mp3Path && file_exists($mp3Path)) {
                @unlink($mp3Path);
            }
            throw $e;
        }
    }

    /**
     * Convert audio to MP3 format using FFmpeg
     */
    private function convertToMp3(string $inputPath, string $originalExt): ?string
    {
        // Check if shell functions are available (may be disabled on some servers)
        if (!function_exists('exec')) {
            Log::warning('exec() is disabled, skipping MP3 conversion');
            return null;
        }

        // Try common ffmpeg paths directly (shell_exec may be disabled)
        $ffmpegPath = null;
        $commonPaths = ['/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/opt/homebrew/bin/ffmpeg', '/www/server/bin/ffmpeg'];
        foreach ($commonPaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                $ffmpegPath = $path;
                break;
            }
        }

        if (empty($ffmpegPath)) {
            Log::warning('FFmpeg not found, trying to transcribe original file directly');
            return null;
        }

        $mp3Path = \str_replace('.' . $originalExt, '.mp3', $inputPath);

        // FFmpeg command with better audio processing
        $command = \sprintf(
            '%s -i %s -vn -acodec libmp3lame -ab 128k -ar 16000 -ac 1 -y %s 2>&1',
            \escapeshellarg($ffmpegPath),
            \escapeshellarg($inputPath),
            \escapeshellarg($mp3Path)
        );

        Log::info('Converting audio to MP3', ['command' => $command]);
        \exec($command, $output, $returnCode);

        if ($returnCode === 0 && \file_exists($mp3Path) && \filesize($mp3Path) > 0) {
            Log::info('Audio converted to MP3 successfully', ['size' => \filesize($mp3Path)]);
            return $mp3Path;
        }

        Log::warning('FFmpeg conversion failed', ['output' => \implode("\n", $output), 'return_code' => $returnCode]);
        return null;
    }

    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles(?string $tempPath, ?string $mp3Path): void
    {
        if ($tempPath && file_exists($tempPath)) {
            @unlink($tempPath);
        }
        if ($mp3Path && file_exists($mp3Path)) {
            @unlink($mp3Path);
        }
    }

    /**
     * Transcribe audio from local path
     */
    private function transcribeFromLocal(string $audioPath): string
    {
        $fullPath = $audioPath;

        if (!file_exists($fullPath)) {
            throw new Exception("Audio file not found: {$fullPath}");
        }

        $fileSize = filesize($fullPath);
        if ($fileSize > 25 * 1024 * 1024) {
            throw new Exception("Audio file too large: " . round($fileSize / 1024 / 1024, 2) . "MB (max 25MB)");
        }

        if ($fileSize < 1000) {
            throw new Exception("Audio file too small or empty. Please record again.");
        }

        Log::info('Starting local audio transcription', [
            'path' => $audioPath,
            'size' => $fileSize
        ]);

        // Check if conversion is needed
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $fileToTranscribe = $fullPath;
        $mp3Path = null;

        if (in_array($ext, ['webm', 'ogg', 'opus'])) {
            $mp3Path = $this->convertToMp3($fullPath, $ext);
            if ($mp3Path) {
                $fileToTranscribe = $mp3Path;
            }
        }

        try {
            $response = OpenAI::audio()->transcribe([
                'model' => 'whisper-1',
                'file' => fopen($fileToTranscribe, 'r'),
                'response_format' => 'verbose_json',
                'language' => 'en',
                'prompt' => 'This is an IELTS speaking test response in English.'
            ]);

            // Clean up converted file
            if ($mp3Path && file_exists($mp3Path)) {
                @unlink($mp3Path);
            }

            $transcribedText = $response->text ?? '';
            $audioDuration = $response->duration ?? 0;

            Log::info('Local transcription result', [
                'text' => $transcribedText,
                'duration' => $audioDuration,
                'word_count' => str_word_count($transcribedText)
            ]);

            // Check audio duration - minimum 10 seconds for valid response
            if ($audioDuration > 0 && $audioDuration < 10) {
                Log::warning('Audio too short', ['duration' => $audioDuration]);
                throw new Exception("Recording is too short ({$audioDuration} seconds). IELTS speaking responses should be at least 15-30 seconds. Please record a longer response.");
            }

            if (empty(trim($transcribedText))) {
                throw new Exception('No speech detected in audio recording. Please ensure you speak clearly and your microphone is working.');
            }

            // Check if transcription is too short
            $wordCount = str_word_count($transcribedText);
            $minWords = 15;

            if ($wordCount < $minWords) {
                Log::warning('Very short transcription detected', [
                    'text' => $transcribedText,
                    'word_count' => $wordCount,
                    'duration' => $audioDuration
                ]);
                throw new Exception("Your response is too short ({$wordCount} words). IELTS speaking requires at least 2-3 complete sentences. Please provide a more detailed response.");
            }

            Log::info('Local transcription successful', [
                'text_length' => strlen($transcribedText),
                'word_count' => $wordCount,
                'duration' => $audioDuration
            ]);

            return $transcribedText;

        } catch (\Exception $e) {
            if ($mp3Path && file_exists($mp3Path)) {
                @unlink($mp3Path);
            }
            throw $e;
        }
    }
}
