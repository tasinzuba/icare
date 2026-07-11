<?php

namespace App\Helpers;

class DiagramHelper
{
    /**
     * Safely format diagram answer for display
     */
    public static function formatAnswer($answerData)
    {
        if (!$answerData) {
            return 'Not answered';
        }
        
        // If it's already a string and not JSON, return as is
        if (is_string($answerData) && !self::isJson($answerData)) {
            return $answerData;
        }
        
        // Try to decode if it's a JSON string
        if (is_string($answerData)) {
            $decoded = @json_decode($answerData, true);
            if ($decoded !== null) {
                $answerData = $decoded;
            }
        }
        
        // If it's not an array at this point, convert to string
        if (!is_array($answerData)) {
            return (string) $answerData;
        }
        
        // Handle single answer with 'answer' key
        if (isset($answerData['answer'])) {
            return $answerData['answer'];
        }
        
        // Handle array of answers
        if (!empty($answerData) && isset($answerData[0])) {
            if (is_array($answerData[0]) && isset($answerData[0]['answer'])) {
                return $answerData[0]['answer'];
            }
        }
        
        // Extract all answers from array
        $answers = [];
        foreach ($answerData as $item) {
            if (is_array($item) && isset($item['answer'])) {
                $answers[] = $item['answer'];
            }
        }
        
        return !empty($answers) ? implode(', ', $answers) : 'Multiple answers';
    }
    
    /**
     * Check if string is JSON
     */
    private static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
