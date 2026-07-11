<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuestionTemplate;

class QuestionTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Listening Templates
            [
                'name' => 'Form Completion - Personal Details',
                'section' => 'listening',
                'question_type' => 'form_completion',
                'template_content' => 'Student Registration Form\n\nName: [____1____]\nStudent ID: [____2____]\nDate of Birth: [____3____]\nNationality: [____4____]\nCourse: [____5____]',
                'instructions' => 'Complete the form below. Write NO MORE THAN TWO WORDS AND/OR A NUMBER for each answer.',
                'default_marks' => 1,
                'template_data' => [
                    'form_fields' => ['Name', 'Student ID', 'Date of Birth', 'Nationality', 'Course']
                ]
            ],
            [
                'name' => 'Note Completion - Lecture Notes',
                'section' => 'listening',
                'question_type' => 'note_completion',
                'template_content' => 'LECTURE NOTES: Climate Change\n\n• Main cause: [____1____]\n• Temperature increase by 2050: [____2____] degrees\n• Most affected regions: [____3____] and [____4____]\n• Solution proposed: [____5____] energy',
                'instructions' => 'Complete the notes below. Write NO MORE THAN TWO WORDS for each answer.',
                'default_marks' => 1,
                'template_data' => []
            ],
            
            // Reading Templates
            [
                'name' => 'True/False/Not Given - Academic',
                'section' => 'reading',
                'question_type' => 'true_false',
                'template_content' => 'The research was conducted over a period of five years.',
                'instructions' => 'Do the following statements agree with the information given in the reading passage? Write TRUE if the statement agrees with the information, FALSE if the statement contradicts the information, or NOT GIVEN if there is no information on this.',
                'default_marks' => 1,
                'default_options' => ['TRUE', 'FALSE', 'NOT GIVEN'],
                'template_data' => []
            ],
            [
                'name' => 'Sentence Completion - Academic',
                'section' => 'reading',
                'question_type' => 'sentence_completion',
                'template_content' => 'Scientists discovered that [____1____] was the primary factor in the experiment\'s success.',
                'instructions' => 'Complete the sentences below. Choose NO MORE THAN TWO WORDS from the passage for each answer.',
                'default_marks' => 1,
                'template_data' => []
            ],
            
            // Writing Templates
            [
                'name' => 'Task 1 - Line Graph Description',
                'section' => 'writing',
                'question_type' => 'task1_line_graph',
                'template_content' => 'The graph below shows [topic] between [start year] and [end year].\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.\n\nWrite at least 150 words.',
                'instructions' => 'You should spend about 20 minutes on this task.',
                'default_marks' => 1,
                'template_data' => [
                    'word_limit' => 150,
                    'time_limit' => 20
                ]
            ],
            [
                'name' => 'Task 2 - Opinion Essay',
                'section' => 'writing',
                'question_type' => 'task2_opinion',
                'template_content' => 'Some people believe that [viewpoint A], while others think that [viewpoint B].\n\nDiscuss both views and give your own opinion.\n\nWrite at least 250 words.',
                'instructions' => 'You should spend about 40 minutes on this task.',
                'default_marks' => 1,
                'template_data' => [
                    'word_limit' => 250,
                    'time_limit' => 40
                ]
            ],
            
            // Speaking Templates
            [
                'name' => 'Part 1 - Personal Questions',
                'section' => 'speaking',
                'question_type' => 'part1_personal',
                'template_content' => 'Let\'s talk about your hometown.\n- Where is your hometown?\n- What do you like about it?\n- How has it changed in recent years?',
                'instructions' => 'Answer the following questions. You should speak for 30-45 seconds for each question.',
                'default_marks' => 1,
                'template_data' => [
                    'time_limit' => 5
                ]
            ],
            [
                'name' => 'Part 2 - Cue Card Topic',
                'section' => 'speaking',
                'question_type' => 'part2_cue_card',
                'template_content' => 'Describe a place you would like to visit.\n\nYou should say:\n- where it is\n- why you want to go there\n- what you would do there\n- and explain why this place is special to you',
                'instructions' => 'You will have 1 minute to prepare and 1-2 minutes to speak.',
                'default_marks' => 1,
                'template_data' => [
                    'time_limit' => 2,
                    'preparation_time' => 1
                ]
            ],
        ];
        
        foreach ($templates as $template) {
            QuestionTemplate::create($template);
        }
    }
}
