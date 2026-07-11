<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HumanEvaluationRequest extends Model
{
    protected $fillable = [
        'student_attempt_id',
        'student_id',
        'teacher_id',
        'tokens_used',
        'is_offline_request',
        'status',
        'priority',
        'requested_at',
        'assigned_at',
        'completed_at',
        'deadline_at'
    ];
    
    protected $casts = [
        'requested_at' => 'datetime',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'deadline_at' => 'datetime',
        'is_offline_request' => 'boolean'
    ];
    
    public function studentAttempt(): BelongsTo
    {
        return $this->belongsTo(StudentAttempt::class);
    }
    
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
    
    public function humanEvaluation(): HasOne
    {
        return $this->hasOne(HumanEvaluation::class, 'evaluation_request_id');
    }
    
    /**
     * Assign teacher to evaluation request
     */
    public function assignTeacher(Teacher $teacher): void
    {
        $this->teacher_id = $teacher->id;
        $this->status = 'assigned';
        $this->assigned_at = now();
        
        // Set deadline based on priority
        $hours = $this->priority === 'urgent' ? 12 : 48;
        $this->deadline_at = now()->addHours($hours);
        
        $this->save();
    }
    
    /**
     * Mark as completed
     */
    public function markCompleted(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
        
        // Update teacher statistics
        if ($this->teacher) {
            $this->teacher->updateStatistics();
        }
    }
    
    /**
     * Check if request is overdue
     */
    public function isOverdue(): bool
    {
        return $this->deadline_at && 
               $this->deadline_at->isPast() && 
               $this->status !== 'completed';
    }
    
    /**
     * Get status badge color
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'assigned' => 'blue',
            'in_progress' => 'purple',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    /**
     * Scope for offline student requests
     */
    public function scopeOffline($query)
    {
        return $query->where('is_offline_request', true);
    }

    /**
     * Scope for online student requests
     */
    public function scopeOnline($query)
    {
        return $query->where('is_offline_request', false);
    }

    /**
     * Create evaluation request for offline student (auto-request, no tokens)
     * NOTE: Does NOT auto-assign teacher - request stays pending until claimed
     */
    public static function createForOfflineStudent(StudentAttempt $attempt, User $student, string $section): self
    {
        // Create the evaluation request WITHOUT auto-assignment
        // Teachers will claim from their dashboard or admin will assign
        $request = self::create([
            'student_attempt_id' => $attempt->id,
            'student_id' => $student->id,
            'teacher_id' => null, // No auto-assignment
            'tokens_used' => 0, // Free for offline students
            'is_offline_request' => true,
            'status' => 'pending', // Always pending until teacher claims
            'priority' => 'normal',
            'requested_at' => now(),
            'assigned_at' => null,
            'deadline_at' => null // Set when assigned
        ]);

        return $request;
    }

    /**
     * Create evaluation request for offline student based on their enrollment's evaluation_type
     *
     * @param StudentAttempt $attempt
     * @param User $student
     * @param string $section 'writing' or 'speaking'
     * @return self|null Returns null if evaluation_type is 'ai' only (no human request needed)
     */
    public static function createForOfflineStudentIfNeeded(StudentAttempt $attempt, User $student, string $section): ?self
    {
        // Get student's active enrollment
        $enrollment = $student->getActiveEnrollment();

        if (!$enrollment) {
            // No active enrollment - don't create request
            \Log::warning('No active enrollment found for offline student', [
                'student_id' => $student->id,
                'attempt_id' => $attempt->id,
                'section' => $section
            ]);
            return null;
        }

        // Check if human evaluation is allowed based on enrollment's evaluation_type
        // evaluation_type can be: 'ai', 'human', 'both'
        if (!$enrollment->canUseHumanEvaluation()) {
            // evaluation_type is 'ai' only - no human evaluation request needed
            \Log::info('Skipping human evaluation request - enrollment is AI-only', [
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'evaluation_type' => $enrollment->evaluation_type,
                'section' => $section
            ]);
            return null;
        }

        // Human evaluation is allowed ('human' or 'both') - create the request
        return self::createForOfflineStudent($attempt, $student, $section);
    }
}
