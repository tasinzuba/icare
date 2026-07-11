<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CloudflareR2Service
{
    protected $disk;
    
    /**
     * Get R2 disk instance
     */
    protected function getDisk()
    {
        if (!$this->disk) {
            $this->disk = Storage::disk('r2');
        }
        return $this->disk;
    }
    
    /**
     * Upload image from file
     */
    public function uploadImage($file, $folder = 'questions')
    {
        try {
            // Generate unique filename
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = $this->generateFilename($extension);
            $path = "{$folder}/{$filename}";
            
            // Direct upload without image processing
            $this->getDisk()->put($path, file_get_contents($file->getRealPath()), [
                'CacheControl' => 'public, max-age=31536000',
                'ContentType' => $file->getMimeType() ?: 'image/jpeg',
            ]);
            
            return $this->getPublicUrl($path);
            
        } catch (\Exception $e) {
            \Log::error('R2 Upload Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate unique filename
     */
    protected function generateFilename($extension)
    {
        return date('Y/m/d/') . Str::random(40) . '.' . $extension;
    }
    
    /**
     * Get public URL
     */
    public function getPublicUrl($path)
    {
        return rtrim(env('R2_URL'), '/') . '/' . $path;
    }
    
    /**
     * Delete image
     */
    public function deleteImage($url)
    {
        $path = str_replace(env('R2_URL') . '/', '', $url);
        return $this->getDisk()->delete($path);
    }
}
