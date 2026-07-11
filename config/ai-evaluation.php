<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Evaluation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI evaluation services including CDN support
    |
    */

    // Enable CDN support for audio files
    'use_cdn_for_audio' => env('AI_USE_CDN_FOR_AUDIO', true),
    
    // Maximum file size for audio transcription (in MB)
    'max_audio_size' => env('AI_MAX_AUDIO_SIZE', 25),
    
    // Timeout for downloading files from CDN (in seconds)
    'cdn_download_timeout' => env('AI_CDN_DOWNLOAD_TIMEOUT', 60),
    
    // Temporary directory for CDN downloads
    'temp_directory' => env('AI_TEMP_DIRECTORY', sys_get_temp_dir()),
    
    /*
    |--------------------------------------------------------------------------
    | Feature Availability for Plans
    |--------------------------------------------------------------------------
    |
    | Define which plans have AI features by default
    | This can be overridden in database
    |
    */
    'features_by_plan' => [
        'free' => [
            'ai_writing_evaluation' => env('AI_FREE_PLAN_WRITING', false),
            'ai_speaking_evaluation' => env('AI_FREE_PLAN_SPEAKING', false),
        ],
        'premium' => [
            'ai_writing_evaluation' => env('AI_PREMIUM_PLAN_WRITING', true),
            'ai_speaking_evaluation' => env('AI_PREMIUM_PLAN_SPEAKING', true),
        ],
        'pro' => [
            'ai_writing_evaluation' => env('AI_PRO_PLAN_WRITING', true),
            'ai_speaking_evaluation' => env('AI_PRO_PLAN_SPEAKING', true),
        ],
    ],
];
