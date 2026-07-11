<?php

/**
 * Centralized Onboarding Configuration
 *
 * This config defines the onboarding flow for each test section.
 * Design remains exactly the same, only data is centralized.
 */

return [
    'listening' => [
        'title' => 'IELTS Listening',
        'section_key' => 'listening',
        'equipment_check' => 'sound', // sound, mic, or null
        'equipment_check_route' => 'sound-check', // Keep original route segment
        'steps' => ['confirm-details', 'sound-check', 'instructions'],
        'instructions' => [
            'candidates' => [
                'Answer <strong>all</strong> questions.',
                'You can change your answers at any time during the test.',
            ],
            'information' => [
                'There are 40 questions in this test.',
                'Each question carries one mark.',
                'The test will have four parts.',
                'You will hear each part once.',
                'For each part of the test there will be time for you to look through the questions and time for you to check your answers.',
            ],
        ],
        'routes' => [
            'start' => 'student.listening.start',
            'index' => 'student.listening.index',
        ],
    ],

    'reading' => [
        'title' => 'IELTS Reading',
        'section_key' => 'reading',
        'equipment_check' => null, // No equipment check needed
        'steps' => ['confirm-details', 'instructions'],
        'instructions' => [
            'candidates' => [
                'Answer <strong>all</strong> questions.',
                'You can change your answers at any time during the test.',
                'Use the scroll bar to move up and down the passage.',
                'You can click on the Review button to flag questions and return to them later.',
            ],
            'information' => [
                'There are 40 questions in this test.',
                'Each question carries one mark.',
                'The test will have three passages with questions.',
                'You should spend about 20 minutes on each passage.',
            ],
        ],
        'routes' => [
            'start' => 'student.reading.start',
            'index' => 'student.reading.index',
        ],
    ],

    'writing' => [
        'title' => 'IELTS Writing',
        'section_key' => 'writing',
        'equipment_check' => null, // No equipment check needed
        'steps' => ['confirm-details', 'instructions'],
        'instructions' => [
            'candidates' => [
                'Answer <strong>both</strong> tasks.',
                'You can change your answers at any time during the test.',
                'You should spend about 20 minutes on Task 1 and 40 minutes on Task 2.',
            ],
            'information' => [
                'There are 2 tasks in this test.',
                'Task 1 requires a minimum of 150 words.',
                'Task 2 requires a minimum of 250 words.',
                'Task 2 carries more marks than Task 1.',
            ],
        ],
        'routes' => [
            'start' => 'student.writing.start',
            'index' => 'student.writing.index',
        ],
    ],

    'speaking' => [
        'title' => 'IELTS Speaking',
        'section_key' => 'speaking',
        'equipment_check' => 'mic', // Microphone check required
        'equipment_check_route' => 'microphone-check', // Keep original route segment
        'steps' => ['confirm-details', 'microphone-check', 'instructions'],
        'instructions' => [
            'candidates' => [
                'Speak clearly into the microphone.',
                'You will be recorded during the test.',
                'Listen carefully to each question before answering.',
            ],
            'information' => [
                'There are 3 parts in this test.',
                'Part 1: Introduction and interview (4-5 minutes).',
                'Part 2: Long turn - speak for 1-2 minutes on a topic (3-4 minutes).',
                'Part 3: Discussion (4-5 minutes).',
                'The test takes 11-14 minutes in total.',
            ],
        ],
        'routes' => [
            'start' => 'student.speaking.start',
            'index' => 'student.speaking.index',
        ],
    ],
];
