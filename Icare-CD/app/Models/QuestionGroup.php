<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionGroup extends Model
{
    protected $fillable = [
        'test_set_id',
        'group_type',
        'title',
        'instructions',
        'start_number',
        'end_number',
        'data'
    ];
    
    protected $casts = [
        'data' => 'array'
    ];
    
    /**
     * Get questions in this group
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'question_group_id', 'id');
    }
    
    /**
     * Get test set
     */
    public function testSet()
    {
        return $this->belongsTo(TestSet::class);
    }
    
    /**
     * Get headings for matching headings type
     */
    public function getHeadings()
    {
        if ($this->group_type !== 'matching_headings') {
            return [];
        }
        
        return $this->data['headings'] ?? [];
    }
}
