<?php

namespace App\Services\AI;

use Exception;

class InsufficientCreditsException extends Exception
{
    protected float $availableCredits;
    protected float $requiredCredits;

    public function __construct(string $message, float $availableCredits, float $requiredCredits)
    {
        parent::__construct($message);
        $this->availableCredits = $availableCredits;
        $this->requiredCredits = $requiredCredits;
    }

    public function getAvailableCredits(): float
    {
        return $this->availableCredits;
    }

    public function getRequiredCredits(): float
    {
        return $this->requiredCredits;
    }

    public function getShortfall(): float
    {
        return $this->requiredCredits - $this->availableCredits;
    }
}
