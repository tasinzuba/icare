<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Evaluation Configuration
    |--------------------------------------------------------------------------
    */

    'provider' => env('AI_PROVIDER', 'openai'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'model' => env('OPENAI_MODEL', 'gpt-4'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 2000),
        'temperature' => env('OPENAI_TEMPERATURE', 0.3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Evaluation Settings
    |--------------------------------------------------------------------------
    */

     'evaluation' => [
        'cache_duration' => env('AI_CACHE_DURATION', 86400), // 24 hours
        'max_retries' => env('AI_MAX_RETRIES', 3),
        'timeout' => env('AI_TIMEOUT', 120), // increased to 2 minutes
        'queue' => env('AI_QUEUE', 'ai-evaluation'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Management
    |--------------------------------------------------------------------------
    */

    'cost_management' => [
        'max_cost_per_user_per_month' => env('AI_MAX_COST_PER_USER', 10), // USD
        'alert_threshold' => env('AI_COST_ALERT_THRESHOLD', 100), // USD
        'rate_limit_per_minute' => env('AI_RATE_LIMIT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | IELTS Specific Settings
    |--------------------------------------------------------------------------
    */

    'ielts' => [
        'writing' => [
            'task1' => [
                'min_words' => 150,
                'time_limit' => 20, // minutes
                'max_score' => 9,
            ],
            'task2' => [
                'min_words' => 250,
                'time_limit' => 40, // minutes
                'max_score' => 9,
            ],
        ],
        'speaking' => [
            'part1' => [
                'duration' => 5, // minutes
                'questions' => 3,
            ],
            'part2' => [
                'preparation_time' => 1, // minute
                'speaking_time' => 2, // minutes
            ],
            'part3' => [
                'duration' => 5, // minutes
                'questions' => 5,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Templates
    |--------------------------------------------------------------------------
    */

    'templates' => [
        'writing_evaluation' => resource_path('ai-templates/writing-evaluation.json'),
        'speaking_evaluation' => resource_path('ai-templates/speaking-evaluation.json'),
    ],
];