<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIEvaluationJob extends Model
{
    protected $table = 'ai_evaluation_jobs';
    
    protected $fillable = [
        'attempt_id', 'type', 'status', 'progress', 
        'error_message', 'meta_data', 'started_at', 'completed_at'
    ];
    
    protected $casts = [
        'meta_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
    
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(StudentAttempt::class, 'attempt_id');
    }
    
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
            'progress' => 10
        ]);
    }
    
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress' => 100
        ]);
    }
    
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'completed_at' => now()
        ]);
    }
    
    public function updateProgress(int $progress): void
    {
        $this->update(['progress' => $progress]);
    }
}