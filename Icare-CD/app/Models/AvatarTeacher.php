<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AvatarTeacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'photo_url',
        'photo_path',
        'elevenlabs_voice_id',
        'voice_name',
        'd_id_source_url',
        'gender',
        'accent',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get all questions using this avatar teacher.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'avatar_teacher_id');
    }

    /**
     * Scope for active teachers only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the default teacher.
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->where('is_active', true)->first()
            ?? static::where('is_active', true)->first();
    }

    /**
     * Set this teacher as the default (unsets others).
     */
    public function setAsDefault(): void
    {
        static::where('is_default', true)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }

    /**
     * Get the photo URL for D-ID (uses d_id_source_url if set, otherwise photo_url).
     */
    public function getSourceUrlForDID(): string
    {
        return $this->d_id_source_url ?: $this->photo_url;
    }

    /**
     * Get display name with accent info.
     */
    public function getDisplayNameAttribute(): string
    {
        $accent = ucfirst($this->accent);
        $gender = $this->gender === 'female' ? 'Female' : 'Male';
        return "{$this->name} ({$accent} {$gender})";
    }

    /**
     * Get count of questions with ready avatars.
     */
    public function getReadyAvatarsCountAttribute(): int
    {
        return $this->questions()->where('avatar_status', 'ready')->count();
    }

    /**
     * Get count of questions with pending/generating avatars.
     */
    public function getPendingAvatarsCountAttribute(): int
    {
        return $this->questions()
            ->whereIn('avatar_status', ['pending', 'generating_audio', 'generating_video'])
            ->count();
    }
}
