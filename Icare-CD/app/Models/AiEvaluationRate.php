<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiEvaluationRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_type',
        'credit_cost',
        'bdt_equivalent',
        'description',
        'is_active',
    ];

    protected $casts = [
        'credit_cost' => 'decimal:4',
        'bdt_equivalent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get rate for a specific evaluation type
     */
    public static function getRate(string $type): ?float
    {
        $rate = self::where('evaluation_type', $type)
            ->where('is_active', true)
            ->first();

        return $rate ? (float) $rate->credit_cost : null;
    }

    /**
     * Get writing evaluation rate
     */
    public static function writingRate(): float
    {
        return self::getRate('writing') ?? 0.40; // Default 40 cents
    }

    /**
     * Get speaking evaluation rate
     */
    public static function speakingRate(): float
    {
        return self::getRate('speaking') ?? 0.80; // Default 80 cents
    }

    /**
     * Get all active rates
     */
    public static function activeRates(): array
    {
        return self::where('is_active', true)
            ->pluck('credit_cost', 'evaluation_type')
            ->toArray();
    }
}
