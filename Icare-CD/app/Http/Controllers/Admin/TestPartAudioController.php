<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestSet;
use App\Models\TestPartAudio;
use App\Traits\HandlesFileUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class TestPartAudioController extends Controller
{
    use HandlesFileUploads;
    
    /**
     * Show part audios management page
     */
    public function index(TestSet $testSet)
    {
        // Only for listening section
        if ($testSet->section->name !== 'listening') {
            return redirect()->route('admin.test-sets.show', $testSet)
                ->with('error', 'Part audios are only for listening section.');
        }
        
        $partAudios = $testSet->partAudios()->get()->keyBy('part_number');
        
        return view('admin.test-sets.part-audios', compact('testSet', 'partAudios'));
    }
    
    /**
     * Upload part audio
     */
    public function upload(Request $request, TestSet $testSet): JsonResponse
    {
        $request->validate([
            'part_number' => 'required|integer|min:0|max:4', // 0 = full audio, 1-4 = individual parts
            'audio' => 'required|file|mimes:mp3,wav,ogg,webm|max:51200', // 50MB max
            'transcript' => 'nullable|string'
        ]);
        
        try {
            $partNumber = $request->part_number;
            
            // Prevent individual part upload if full audio exists (unless replacing full audio)
            if ($partNumber > 0) {
                $fullAudio = $testSet->partAudios()->where('part_number', 0)->first();
                if ($fullAudio) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot upload individual part audio. Full audio is active. Please delete full audio first.'
                    ], 400);
                }
            }
            
            // Prevent full audio upload if any part audio exists
            if ($partNumber === 0) {
                $existingPartAudios = $testSet->partAudios()->where('part_number', '>', 0)->count();
                if ($existingPartAudios > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot upload full audio. Individual part audios exist. Please delete them first.'
                    ], 400);
                }
            }
            
            // Check if audio already exists for this part
            $existingAudio = $testSet->partAudios()
                ->where('part_number', $partNumber)
                ->first();
            
            // Delete old audio if exists
            if ($existingAudio) {
                $this->deleteFile($existingAudio->audio_path, $existingAudio->storage_disk);
            }
            
            // Upload new audio using trait (automatically handles R2 sync)
            $result = $this->uploadFile(
                $request->file('audio'), 
                'test-audios/set-' . $testSet->id
            );
            
            if (!$result['success']) {
                throw new \Exception('Failed to upload audio file');
            }
            
            // Get audio metadata (duration, size)
            $audioInfo = $this->getAudioInfo($request->file('audio'));
            
            // Create or update part audio record
            $partAudio = TestPartAudio::updateOrCreate(
                [
                    'test_set_id' => $testSet->id,
                    'part_number' => $partNumber
                ],
                [
                    'audio_path' => $result['path'],
                    'audio_url' => $result['url'],
                    'storage_disk' => $result['disk'],
                    'audio_duration' => $audioInfo['duration'] ?? null,
                    'audio_size' => $result['size'],
                    'transcript' => $request->transcript
                ]
            );
            
            $audioType = $partNumber === 0 ? 'full audio' : "Part {$partNumber} audio";
            
            return response()->json([
                'success' => true,
                'message' => ucfirst($audioType) . ' uploaded successfully to ' . strtoupper($result['disk']),
                'audio' => [
                    'id' => $partAudio->id,
                    'part_number' => $partAudio->part_number,
                    'url' => $result['url'],
                    'duration' => $partAudio->formatted_duration,
                    'size' => $this->humanFileSize($result['size']),
                    'storage' => strtoupper($result['disk'])
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload audio: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete part audio
     */
    public function destroy(TestSet $testSet, $partNumber): JsonResponse
    {
        $partAudio = $testSet->partAudios()
            ->where('part_number', $partNumber)
            ->first();
        
        if (!$partAudio) {
            return response()->json([
                'success' => false,
                'message' => 'Audio not found'
            ], 404);
        }
        
        // Check if any questions are using this audio (only for individual parts, not full audio)
        if ($partNumber > 0) {
            $questionsCount = $testSet->questions()
                ->where('part_number', $partNumber)
                ->where('use_part_audio', true)
                ->count();
            
            if ($questionsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete: {$questionsCount} questions are using this audio"
                ], 400);
            }
        } else {
            // For full audio (part_number = 0), check all questions
            $questionsCount = $testSet->questions()
                ->where('use_part_audio', true)
                ->count();
            
            if ($questionsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete: {$questionsCount} questions are using this full audio"
                ], 400);
            }
        }
        
        // Delete file from storage (works with both local and R2)
        $this->deleteFile($partAudio->audio_path, $partAudio->storage_disk);
        
        // Delete record
        $partAudio->delete();
        
        $audioType = $partNumber === 0 ? 'Full audio' : "Part {$partNumber} audio";
        
        return response()->json([
            'success' => true,
            'message' => $audioType . ' deleted successfully'
        ]);
    }
    
    /**
     * Get audio information using getID3
     */
    private function getAudioInfo($file): array
    {
        try {
            // Now using getID3 for complete audio analysis
            $getID3 = new \getID3;
            $tempPath = $file->getRealPath();
            $info = $getID3->analyze($tempPath);
            
            // Extract detailed audio information
            $duration = isset($info['playtime_seconds']) ? round($info['playtime_seconds']) : 0;
            $bitrate = isset($info['bitrate']) ? round($info['bitrate'] / 1000) : 0; // Convert to kbps
            $format = $info['fileformat'] ?? 'unknown';
            
            // Additional info for admin
            $sampleRate = 0;
            $channels = '';
            $codec = '';
            
            if (isset($info['audio'])) {
                $sampleRate = $info['audio']['sample_rate'] ?? 0;
                $channels = $info['audio']['channelmode'] ?? 'unknown';
                $codec = $info['audio']['codec'] ?? $format;
            }
            
            // Quality check for IELTS standards
            $qualityIssues = [];
            
            // Check duration (IELTS listening is typically 30-40 minutes total)
            if ($duration > 1200) { // More than 20 minutes per part is unusual
                $qualityIssues[] = 'Duration exceeds 20 minutes';
            }
            
            // Check bitrate (should be at least 128kbps for clarity)
            if ($bitrate > 0 && $bitrate < 128) {
                $qualityIssues[] = 'Low bitrate (< 128kbps) - may affect audio quality';
            }
            
            // Check sample rate (should be at least 44100 Hz)
            if ($sampleRate > 0 && $sampleRate < 44100) {
                $qualityIssues[] = 'Low sample rate - may affect clarity';
            }
            
            // Log the analysis
            \Log::info('Audio Analysis Complete', [
                'file' => $file->getClientOriginalName(),
                'duration' => $this->formatDuration($duration),
                'bitrate' => $bitrate . ' kbps',
                'format' => $format,
                'sample_rate' => $sampleRate . ' Hz',
                'channels' => $channels,
                'quality_issues' => $qualityIssues
            ]);
            
            return [
                'duration' => $duration,
                'bitrate' => $bitrate,
                'format' => $format,
                'sample_rate' => $sampleRate,
                'channels' => $channels,
                'codec' => $codec,
                'quality_issues' => $qualityIssues
            ];
            
        } catch (\Exception $e) {
            \Log::error('getID3 Error: ' . $e->getMessage());
            
            // Fallback to basic info
            return [
                'duration' => 0,
                'bitrate' => 0,
                'format' => $file->getClientOriginalExtension(),
                'quality_issues' => ['Could not analyze audio file']
            ];
        }
    }
    
    /**
     * Format duration in MM:SS format
     */
    private function formatDuration($seconds): string
    {
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
    
    /**
     * Get audio duration using FFmpeg (if available)
     */
    private function getAudioDurationViaFFmpeg($filepath): int
    {
        try {
            // Check if ffmpeg is available
            $output = shell_exec("which ffmpeg 2>&1");
            if (empty($output)) {
                return 0;
            }
            
            // Get duration using ffprobe (comes with ffmpeg)
            $cmd = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($filepath);
            $duration = shell_exec($cmd);
            
            return $duration ? (int)round((float)$duration) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}