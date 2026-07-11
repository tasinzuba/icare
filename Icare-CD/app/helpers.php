<?php

/**
 * Helper Functions for IELTS Mock Platform
 */

if (!function_exists('formatBandScore')) {
    /**
     * Format band score to IELTS official format (0.5 increments)
     * Examples: 6.0, 6.5, 7.0, 7.5, 8.0, 8.5, 9.0
     * 
     * @param float|null $score
     * @return float|null
     */
    function formatBandScore($score)
    {
        if ($score === null) {
            return null;
        }
        
        // Round to nearest 0.5
        return round($score * 2) / 2;
    }
}

if (!function_exists('displayBandScore')) {
    /**
     * Display band score in IELTS format with one decimal place
     *
     * @param float|null $score
     * @param string $default
     * @return string
     */
    function displayBandScore($score, $default = 'N/A')
    {
        if ($score === null) {
            return $default;
        }

        return number_format(formatBandScore($score), 1);
    }
}

if (!function_exists('bandScoreRange')) {
    /**
     * Display band score as IELTS range (e.g., "6.0-6.5" instead of "6.1")
     * IELTS uses 0.5 increments, so scores between boundaries are shown as ranges
     *
     * @param float|null $score
     * @param string $default
     * @return string
     */
    function bandScoreRange($score, $default = 'N/A')
    {
        if ($score === null) {
            return $default;
        }

        // Get floor and ceiling at 0.5 increments
        $lowerBand = floor($score * 2) / 2;
        $upperBand = ceil($score * 2) / 2;

        // If exactly on a boundary (e.g., 6.0 or 6.5), show single value
        if ($lowerBand == $upperBand) {
            return number_format($lowerBand, 1);
        }

        // Otherwise show range (e.g., "6.0-6.5")
        return number_format($lowerBand, 1) . '-' . number_format($upperBand, 1);
    }
}
