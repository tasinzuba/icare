<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\HandlesFileUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImageUploadController extends Controller
{
    use HandlesFileUploads;

    /**
     * Allowed MIME types for image uploads (verified by file content)
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    public function upload(Request $request)
    {
        Log::info('Image upload request received', [
            'has_file' => $request->hasFile('image'),
            'files' => $request->allFiles()
        ]);

        try {
            // Step 1: Basic Laravel validation
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120' // 5MB max
            ]);

            $file = $request->file('image');

            // Step 2: Deep MIME type verification using file content (not extension)
            if (!$this->verifyImageContent($file)) {
                Log::warning('Image upload rejected: MIME type mismatch', [
                    'claimed_mime' => $file->getMimeType(),
                    'extension' => $file->getClientOriginalExtension(),
                    'filename' => $file->getClientOriginalName(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image file. The file content does not match an allowed image type.'
                ], 422);
            }

            // Step 3: Check for PHP code in image (防止 image with embedded PHP)
            if ($this->containsPhpCode($file)) {
                Log::warning('Image upload rejected: contains PHP code', [
                    'filename' => $file->getClientOriginalName(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image file.'
                ], 422);
            }

            // Upload using trait method
            $result = $this->uploadFile($file, 'questions');

            if (!$result['success']) {
                throw new \Exception('Failed to upload file');
            }

            Log::info('Image uploaded successfully', $result);

            return response()->json([
                'success' => true,
                'url' => $result['url'],
                'location' => $result['url'], // TinyMCE sometimes expects 'location'
                'filename' => basename($result['path']),
                'size' => $this->humanFileSize($result['size'])
            ]);

        } catch (\Exception $e) {
            Log::error('Image upload error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify image content using multiple methods
     */
    private function verifyImageContent($file): bool
    {
        $path = $file->getRealPath();

        // Method 1: finfo (most reliable)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $path);
        finfo_close($finfo);

        if (!in_array($detectedMime, self::ALLOWED_MIME_TYPES)) {
            return false;
        }

        // Method 2: getimagesize (confirms it's actually an image)
        $imageInfo = @getimagesize($path);
        if ($imageInfo === false) {
            return false;
        }

        // Verify dimensions are reasonable (not 0x0 or suspiciously large)
        if ($imageInfo[0] < 1 || $imageInfo[1] < 1 || $imageInfo[0] > 10000 || $imageInfo[1] > 10000) {
            return false;
        }

        return true;
    }

    /**
     * Check if file contains PHP code (polyglot attack prevention)
     */
    private function containsPhpCode($file): bool
    {
        $content = file_get_contents($file->getRealPath());

        // Check for common PHP patterns
        $phpPatterns = [
            '<?php',
            '<?=',
            '<? ',
            '<%',           // ASP-style
            '<script language="php"',
            'eval(',
            'base64_decode(',
            'exec(',
            'system(',
            'passthru(',
            'shell_exec(',
        ];

        $contentLower = strtolower($content);

        foreach ($phpPatterns as $pattern) {
            if (stripos($contentLower, strtolower($pattern)) !== false) {
                return true;
            }
        }

        return false;
    }
}
