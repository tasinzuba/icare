<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSection extends Model
{
    protected $fillable = ['name', 'description', 'time_limit'];
    
    protected $casts = [
        'time_limit' => 'integer',
    ];
    
    public function testSets(): HasMany
    {
        return $this->hasMany(TestSet::class, 'section_id');
    }
}