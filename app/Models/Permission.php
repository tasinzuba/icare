<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'module',
        'description'
    ];

    /**
     * Get the roles that have this permission
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission')
                    ->withTimestamps();
    }

    /**
     * Get permissions grouped by module
     */
    public static function getGroupedByModule()
    {
        return self::all()->groupBy('module');
    }

    /**
     * Get all modules
     */
    public static function getModules()
    {
        return self::distinct('module')->pluck('module');
    }
}
