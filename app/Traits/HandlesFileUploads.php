<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

trait HandlesFileUploads
{
    /**
     * Upload a file to R2 or local storage
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $disk
     * @return array
     */
    protected function uploadFile(UploadedFile $file, string $directory = 'uploads', ?string $disk = null): array
    {
        // Determine disk to use
        if ($disk === null) {
            $disk = $this->shouldUseR2() ? 'r2' : 'public';
        }

        // Generate unique filename with directory structure
        $filename = $this->generateFilename($file, $directory);

        // Upload file
        $stored = Storage::disk($disk)->put($filename, file_get_contents($file), 'public');

        // Get URL
        $url = $this->getFileUrl($filename, $disk);

        return [
            'success' => $stored !== false,
            'disk' => $disk,
            'path' => $filename,
            'url' => $url,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'original_name' => $file->getClientOriginalName()
        ];
    }

    /**
     * Delete a file from storage
     *
     * @param string $path
     * @param string|null $disk
     * @return bool
     */
    protected function deleteFile(string $path, ?string $disk = null): bool
    {
        if ($disk === null) {
            $disk = $this->shouldUseR2() ? 'r2' : 'public';
        }

        // Old records may point to R2 even though R2 isn't currently configured
        // (credentials removed from .env). Skip the delete instead of crashing the
        // caller — the record itself will be replaced/removed in the DB regardless.
        if ($disk === 'r2' && !$this->shouldUseR2()) {
            return true;
        }

        try {
            return Storage::disk($disk)->delete($path);
        } catch (\Throwable $e) {
            \Log::warning('deleteFile failed; continuing', [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate unique filename with directory structure
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string
     */
    protected function generateFilename(UploadedFile $file, string $directory): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        
        // Create directory structure: directory/year/month/filename
        return sprintf('%s/%s/%s', 
            trim($directory, '/'),
            date('Y/m'),
            $filename
        );
    }

    /**
     * Get the public URL for a file
     *
     * @param string $path
     * @param string $disk
     * @return string
     */
    protected function getFileUrl(string $path, string $disk): string
    {
        if ($disk === 'r2') {
            // Use R2 CDN URL
            $baseUrl = rtrim(config('filesystems.disks.r2.url'), '/');
            
            // If custom domain is not set, use the R2 endpoint
            if ($baseUrl === 'https://your-custom-domain.com') {
                // Use bucket subdomain format
                $bucket = config('filesystems.disks.r2.bucket');
                $baseUrl = "https://{$bucket}.r2.cloudflarestorage.com";
            }
            
            return $baseUrl . '/' . ltrim($path, '/');
        }

        // Local storage
        return asset('storage/' . $path);
    }

    /**
     * Check if R2 should be used
     *
     * @return bool
     */
    protected function shouldUseR2(): bool
    {
        // Check if R2 credentials are configured
        return !empty(config('filesystems.disks.r2.key')) &&
               !empty(config('filesystems.disks.r2.secret')) &&
               !empty(config('filesystems.disks.r2.bucket'));
    }

    /**
     * Get file size in human readable format
     *
     * @param int $bytes
     * @return string
     */
    protected function humanFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
