<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HumanEvaluation extends Model
{
    protected $fillable = [
        'evaluation_request_id',
        'evaluator_id',
        'task_scores',
        'overall_band_score',
        'detailed_feedback',
        'strengths',
        'improvements',
        'evaluated_at',
    ];

    protected $casts = [
        'task_scores' => 'array',
        'detailed_feedback' => 'array',
        'strengths' => 'array',
        'improvements' => 'array',
        'evaluated_at' => 'datetime',
        'overall_band_score' => 'float',
    ];

    public function evaluationRequest(): BelongsTo
    {
        return $this->belongsTo(HumanEvaluationRequest::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function errorMarkings(): HasMany
    {
        return $this->hasMany(EvaluationErrorMarking::class);
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(EvaluationAnnotation::class);
    }

    /**
     * Get formatted evaluation data for display
     */
    public function getFormattedEvaluation(): array
    {
        return [
            'overall_band' => $this->overall_band_score,
            'tasks' => $this->formatTaskScores(),
            'strengths' => $this->strengths ?? [],
            'improvements' => $this->improvements ?? [],
            'evaluator' => [
                'name' => $this->evaluator->name,
                'qualifications' => $this->evaluator->teacher->qualifications ?? [],
            ],
            'evaluated_at' => $this->evaluated_at->format('d M Y, h:i A'),
        ];
    }

    /**
     * Format task scores for display
     */
    private function formatTaskScores(): array
    {
        $formatted = [];

        foreach ($this->task_scores as $taskKey => $taskData) {
            $formatted[] = [
                'task_number' => str_replace('task', '', $taskKey),
                'band_score' => $taskData['score'] ?? 0,
                'criteria' => $taskData['criteria'] ?? [],
                'feedback' => $this->detailed_feedback[$taskKey] ?? [],
            ];
        }

        return $formatted;
    }
}
