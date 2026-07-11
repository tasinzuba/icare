<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SpeakingRecording extends Model
{
    protected $fillable = [
        'answer_id', 
        'file_path',
        'file_url',
        'storage_disk',
        'file_size',
        'mime_type'
    ];
    
    public function answer(): BelongsTo
    {
        return $this->belongsTo(StudentAnswer::class, 'answer_id');
    }
    
    /**
     * Get the recording URL (CDN or local)
     */
    public function getFileUrlAttribute(): string
    {
        // If CDN URL is stored, use it
        if ($this->attributes['file_url'] ?? null) {
            return $this->attributes['file_url'];
        }
        
        // Otherwise generate from path
        $disk = $this->storage_disk ?? 'public';
        
        if ($disk === 'r2') {
            // Generate R2 URL
            $baseUrl = rtrim(config('filesystems.disks.r2.url'), '/');
            return $baseUrl . '/' . ltrim($this->file_path, '/');
        }
        
        // Local storage URL
        return Storage::disk($disk)->url($this->file_path);
    }
    
    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }
        
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}