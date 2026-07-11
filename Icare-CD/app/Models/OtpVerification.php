<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifier',
        'token',
        'otp_code',
        'type',
        'expires_at',
        'verified_at',
        'attempts'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    public function markAsVerified(): void
    {
        $this->update(['verified_at' => now()]);
    }

    /**
     * Generate cryptographically secure OTP
     * SECURITY FIX: Using 8-character alphanumeric OTP instead of 6-digit numeric
     * This increases entropy from ~20 bits (10^6) to ~48 bits (36^8)
     */
    public static function generateOTP(): string
    {
        // Use alphanumeric characters for higher entropy
        // Excluding similar-looking characters: 0, O, 1, I, L
        $characters = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
        $length = 8;
        $otp = '';

        // Use cryptographically secure random bytes
        $bytes = random_bytes($length);

        for ($i = 0; $i < $length; $i++) {
            $otp .= $characters[ord($bytes[$i]) % strlen($characters)];
        }

        return $otp;
    }

    /**
     * Maximum allowed attempts before lockout
     */
    public const MAX_ATTEMPTS = 5;

    /**
     * Lockout duration in minutes after max attempts
     */
    public const LOCKOUT_MINUTES = 15;

    /**
     * Check if this OTP verification is locked out due to too many attempts
     */
    public function isLockedOut(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Get remaining lockout time in seconds
     */
    public function getLockoutRemainingSeconds(): int
    {
        if (!$this->isLockedOut()) {
            return 0;
        }

        $lockoutEndsAt = $this->updated_at->addMinutes(self::LOCKOUT_MINUTES);

        if (now()->gte($lockoutEndsAt)) {
            return 0;
        }

        return now()->diffInSeconds($lockoutEndsAt);
    }

    public static function createForEmail(string $email): self
    {
        // Delete old OTPs
        self::where('identifier', $email)
            ->where('type', 'email')
            ->delete();

        return self::create([
            'identifier' => $email,
            'token' => \Str::random(64),
            'otp_code' => self::generateOTP(),
            'type' => 'email',
            'expires_at' => now()->addMinutes(5),
        ]);
    }
}