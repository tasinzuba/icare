<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TestPartAudio extends Model
{

    protected $table = 'test_part_audios';

    protected $fillable = [
        'test_set_id',
        'part_number',
        'audio_path',
        'audio_url',
        'storage_disk',
        'audio_duration',
        'audio_size',
        'transcript'
    ];

    protected $casts = [
        'part_number' => 'integer',
    ];

    public function testSet(): BelongsTo
    {
        return $this->belongsTo(TestSet::class);
    }

    /**
     * Get the audio URL (CDN or local)
     * Priority: R2 CDN (if configured + stored on R2) → local public storage
     */
    public function getAudioUrlAttribute(): string
    {
        if (empty($this->audio_path)) {
            return '';
        }

        $disk = $this->storage_disk ?? null;
        $r2BaseUrl = config('filesystems.disks.r2.url');
        if ($disk === 'r2' && !empty($r2BaseUrl)) {
            return rtrim($r2BaseUrl, '/') . '/' . ltrim($this->audio_path, '/');
        }

        return asset('storage/' . ltrim($this->audio_path, '/'));
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->audio_duration) {
            return '00:00';
        }
        
        $seconds = $this->audio_duration;
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->audio_size) {
            return 'Unknown';
        }
        
        $bytes = $this->audio_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}