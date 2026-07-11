<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\TestSection;
use App\Models\TestSet;
use Illuminate\Database\Seeder;

class SampleTestSeeder extends Seeder
{
    /**
     * Create sample tests for all sections to test the application.
     */
    public function run(): void
    {
        $this->command->info('Creating sample tests for all sections...');

        // Create sections if they don't exist
        $this->createSections();

        // Create sample tests for each section
        $this->createListeningTest();
        $this->createReadingTest();
        $this->createWritingTest();
        $this->createSpeakingTest();

        $this->command->info('Sample tests created successfully!');
    }

    private function createSections(): void
    {
        $sections = [
            ['name' => 'listening', 'time_limit' => 30],
            ['name' => 'reading', 'time_limit' => 60],
            ['name' => 'writing', 'time_limit' => 60],
            ['name' => 'speaking', 'time_limit' => 15],
        ];

        foreach ($sections as $section) {
            TestSection::firstOrCreate(
                ['name' => $section['name']],
                $section
            );
        }
    }

    private function createListeningTest(): void
    {
        $section = TestSection::where('name', 'listening')->first();

        $testSet = TestSet::firstOrCreate(
            ['title' => 'Sample Listening Test'],
            [
                'title' => 'Sample Listening Test',
                'section_id' => $section->id,
                'active' => true,
                'is_premium' => false,
            ]
        );

        // Only create questions if none exist
        if ($testSet->questions()->count() > 0) {
            $this->command->info('Listening test already has questions, skipping...');
            return;
        }

        // Part 1: Multiple Choice
        $q1 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'multiple_choice',
            'content' => 'What is the main topic of the conversation?',
            'order_number' => 1,
            'part_number' => 1,
            'marks' => 1,
            'instructions' => 'Choose the correct answer.',
        ]);

        $options1 = [
            ['text' => 'Booking a hotel room', 'is_correct' => true, 'order' => 1],
            ['text' => 'Ordering food', 'is_correct' => false, 'order' => 2],
            ['text' => 'Buying clothes', 'is_correct' => false, 'order' => 3],
            ['text' => 'Renting a car', 'is_correct' => false, 'order' => 4],
        ];

        foreach ($options1 as $opt) {
            QuestionOption::create([
                'question_id' => $q1->id,
                'content' => $opt['text'],
                'is_correct' => $opt['is_correct'],
                'order' => $opt['order'],
            ]);
        }

        // Part 1: Fill in the blank
        $q2 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'fill_in_blank',
            'content' => 'The guest wants to check in on [____1____].',
            'order_number' => 2,
            'part_number' => 1,
            'marks' => 1,
            'instructions' => 'Write NO MORE THAN TWO WORDS.',
            'section_specific_data' => [
                'blank_answers' => [
                    1 => 'Monday/monday/MONDAY'
                ]
            ],
        ]);

        // Part 2: True/False/Not Given
        $q3 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'true_false',
            'content' => 'The hotel has a swimming pool.',
            'order_number' => 3,
            'part_number' => 2,
            'marks' => 1,
            'instructions' => 'Choose TRUE, FALSE, or NOT GIVEN.',
        ]);

        $options3 = [
            ['text' => 'True', 'is_correct' => true, 'order' => 1],
            ['text' => 'False', 'is_correct' => false, 'order' => 2],
            ['text' => 'Not Given', 'is_correct' => false, 'order' => 3],
        ];

        foreach ($options3 as $opt) {
            QuestionOption::create([
                'question_id' => $q3->id,
                'content' => $opt['text'],
                'is_correct' => $opt['is_correct'],
                'order' => $opt['order'],
            ]);
        }

        // Part 3: Multiple choice
        $q4 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'multiple_choice',
            'content' => 'What time does breakfast start?',
            'order_number' => 4,
            'part_number' => 3,
            'marks' => 1,
            'instructions' => 'Choose the correct answer.',
        ]);

        $options4 = [
            ['text' => '6:00 AM', 'is_correct' => false, 'order' => 1],
            ['text' => '7:00 AM', 'is_correct' => true, 'order' => 2],
            ['text' => '8:00 AM', 'is_correct' => false, 'order' => 3],
            ['text' => '9:00 AM', 'is_correct' => false, 'order' => 4],
        ];

        foreach ($options4 as $opt) {
            QuestionOption::create([
                'question_id' => $q4->id,
                'content' => $opt['text'],
                'is_correct' => $opt['is_correct'],
                'order' => $opt['order'],
            ]);
        }

        // Part 4: Fill in blanks
        $q5 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'fill_in_blank',
            'content' => 'The room rate is [____1____] dollars per night.',
            'order_number' => 5,
            'part_number' => 4,
            'marks' => 1,
            'instructions' => 'Write the number.',
            'section_specific_data' => [
                'blank_answers' => [
                    1 => '150/one hundred fifty/one hundred and fifty'
                ]
            ],
        ]);

        $this->command->info("Created Listening Test: {$testSet->title} with 5 questions");
    }

    private function createReadingTest(): void
    {
        $section = TestSection::where('name', 'reading')->first();

        $testSet = TestSet::firstOrCreate(
            ['title' => 'Sample Reading Test'],
            [
                'title' => 'Sample Reading Test',
                'section_id' => $section->id,
                'active' => true,
                'is_premium' => false,
            ]
        );

        if ($testSet->questions()->count() > 0) {
            $this->command->info('Reading test already has questions, skipping...');
            return;
        }

        // Passage 1
        $passage1 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'passage',
            'content' => "The Rise of Remote Work\n\nThe COVID-19 pandemic has fundamentally transformed how we work. Before 2020, remote work was a perk offered by progressive companies. Now, it has become the norm for millions of workers worldwide.\n\nStudies show that productivity has actually increased for many remote workers. Without the distractions of an open office and the time spent commuting, employees can focus more on their tasks. Companies have also reported reduced overhead costs.\n\nHowever, challenges remain. Many workers report feelings of isolation and difficulty separating work from personal life. Managers struggle to maintain team cohesion and company culture in a virtual environment.",
            'order_number' => 1,
            'part_number' => 1,
            'marks' => 0,
        ]);

        // Question 1: Multiple Choice
        $q1 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'multiple_choice',
            'content' => 'According to the passage, what has increased for many remote workers?',
            'order_number' => 2,
            'part_number' => 1,
            'marks' => 1,
            'instructions' => 'Choose the correct answer.',
        ]);

        $options1 = [
            ['text' => 'Salary', 'is_correct' => false, 'order' => 1],
            ['text' => 'Productivity', 'is_correct' => true, 'order' => 2],
            ['text' => 'Stress levels', 'is_correct' => false, 'order' => 3],
            ['text' => 'Commuting time', 'is_correct' => false, 'order' => 4],
        ];

        foreach ($options1 as $opt) {
            QuestionOption::create([
                'question_id' => $q1->id,
                'content' => $opt['text'],
                'is_correct' => $opt['is_correct'],
                'order' => $opt['order'],
            ]);
        }

        // Question 2: True/False/Not Given
        $q2 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'true_false',
            'content' => 'Remote work was common before 2020.',
            'order_number' => 3,
            'part_number' => 1,
            'marks' => 1,
            'instructions' => 'Choose TRUE, FALSE, or NOT GIVEN.',
        ]);

        $options2 = [
            ['text' => 'True', 'is_correct' => false, 'order' => 1],
            ['text' => 'False', 'is_correct' => true, 'order' => 2],
            ['text' => 'Not Given', 'is_correct' => false, 'order' => 3],
        ];

        foreach ($options2 as $opt) {
            QuestionOption::create([
                'question_id' => $q2->id,
                'content' => $opt['text'],
                'is_correct' => $opt['is_correct'],
                'order' => $opt['order'],
            ]);
        }

        // Question 3: Fill in blank
        $q3 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'fill_in_blank',
            'content' => 'Companies have reduced [____1____] costs by having employees work remotely.',
            'order_number' => 4,
            'part_number' => 1,
            'marks' => 1,
            'instructions' => 'Write NO MORE THAN TWO WORDS from the passage.',
            'section_specific_data' => [
                'blank_answers' => [
                    1 => 'overhead'
                ]
            ],
        ]);

        // Question 4: Multiple Choice
        $q4 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'multiple_choice',
            'content' => 'What challenge do managers face with remote teams?',
            'order_number' => 5,
            'part_number' => 1,
            'marks' => 1,
            'instructions' => 'Choose the correct answer.',
        ]);

        $options4 = [
            ['text' => 'Higher costs', 'is_correct' => false, 'order' => 1],
            ['text' => 'Technical issues', 'is_correct' => false, 'order' => 2],
            ['text' => 'Maintaining team cohesion', 'is_correct' => true, 'order' => 3],
            ['text' => 'Finding employees', 'is_correct' => false, 'order' => 4],
        ];

        foreach ($options4 as $opt) {
            QuestionOption::create([
                'question_id' => $q4->id,
                'content' => $opt['text'],
                'is_correct' => $opt['is_correct'],
                'order' => $opt['order'],
            ]);
        }

        // Question 5: True/False
        $q5 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'true_false',
            'content' => 'Workers have reported feeling isolated while working from home.',
            'order_number' => 6,
            'part_number' => 1,
            'marks' => 1,
            'instructions' => 'Choose TRUE, FALSE, or NOT GIVEN.',
        ]);

        $options5 = [
            ['text' => 'True', 'is_correct' => true, 'order' => 1],
            ['text' => 'False', 'is_correct' => false, 'order' => 2],
            ['text' => 'Not Given', 'is_correct' => false, 'order' => 3],
        ];

        foreach ($options5 as $opt) {
            QuestionOption::create([
                'question_id' => $q5->id,
                'content' => $opt['text'],
                'is_correct' => $opt['is_correct'],
                'order' => $opt['order'],
            ]);
        }

        $this->command->info("Created Reading Test: {$testSet->title} with 5 questions + 1 passage");
    }

    private function createWritingTest(): void
    {
        $section = TestSection::where('name', 'writing')->first();

        $testSet = TestSet::firstOrCreate(
            ['title' => 'Sample Writing Test'],
            [
                'title' => 'Sample Writing Test',
                'section_id' => $section->id,
                'active' => true,
                'is_premium' => false,
            ]
        );

        if ($testSet->questions()->count() > 0) {
            $this->command->info('Writing test already has questions, skipping...');
            return;
        }

        // Task 1
        Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'writing_task',
            'content' => "The chart below shows the percentage of households with internet access in three different countries from 2000 to 2020.\n\nSummarize the information by selecting and reporting the main features, and make comparisons where relevant.\n\nWrite at least 150 words.",
            'order_number' => 1,
            'part_number' => 1,
            'marks' => 1,
            'instructions' => 'You should spend about 20 minutes on this task.',
            'section_specific_data' => [
                'task_type' => 'task1',
                'min_words' => 150,
                'time_suggestion' => 20,
            ],
        ]);

        // Task 2
        Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'writing_task',
            'content' => "Some people believe that technology has made our lives more complicated, while others think it has made life easier.\n\nDiscuss both views and give your own opinion.\n\nWrite at least 250 words.",
            'order_number' => 2,
            'part_number' => 2,
            'marks' => 1,
            'instructions' => 'You should spend about 40 minutes on this task.',
            'section_specific_data' => [
                'task_type' => 'task2',
                'min_words' => 250,
                'time_suggestion' => 40,
            ],
        ]);

        $this->command->info("Created Writing Test: {$testSet->title} with 2 tasks");
    }

    private function createSpeakingTest(): void
    {
        $section = TestSection::where('name', 'speaking')->first();

        $testSet = TestSet::firstOrCreate(
            ['title' => 'Sample Speaking Test'],
            [
                'title' => 'Sample Speaking Test',
                'section_id' => $section->id,
                'active' => true,
                'is_premium' => false,
            ]
        );

        if ($testSet->questions()->count() > 0) {
            $this->command->info('Speaking test already has questions, skipping...');
            return;
        }

        // Part 1: Introduction and Interview
        Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'speaking',
            'content' => "Let's talk about your hometown.\n\n- Where is your hometown?\n- What do you like about it?\n- Has it changed much since you were a child?",
            'order_number' => 1,
            'part_number' => 1,
            'marks' => 1,
            'instructions' => 'Part 1: Introduction and Interview (4-5 minutes)',
            'section_specific_data' => [
                'part' => 1,
                'topic' => 'Hometown',
                'preparation_time' => 0,
                'speaking_time' => 60,
            ],
        ]);

        // Part 1: Second topic
        Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'speaking',
            'content' => "Now let's talk about music.\n\n- What kind of music do you enjoy?\n- Do you play any musical instruments?\n- How has your taste in music changed over time?",
            'order_number' => 2,
            'part_number' => 1,
            'marks' => 1,
            'instructions' => 'Part 1: Introduction and Interview',
            'section_specific_data' => [
                'part' => 1,
                'topic' => 'Music',
                'preparation_time' => 0,
                'speaking_time' => 60,
            ],
        ]);

        // Part 2: Long Turn
        Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'speaking',
            'content' => "Describe a memorable trip you have taken.\n\nYou should say:\n- where you went\n- who you went with\n- what you did there\n\nand explain why this trip was memorable.",
            'order_number' => 3,
            'part_number' => 2,
            'marks' => 1,
            'instructions' => 'Part 2: Long Turn (3-4 minutes). You have 1 minute to prepare.',
            'section_specific_data' => [
                'part' => 2,
                'topic' => 'Memorable Trip',
                'preparation_time' => 60,
                'speaking_time' => 120,
            ],
        ]);

        // Part 3: Discussion
        Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'speaking',
            'content' => "Let's discuss travel and tourism more broadly.\n\n- Why do you think people enjoy traveling?\n- How has tourism changed in recent years?\n- What are the advantages and disadvantages of mass tourism?",
            'order_number' => 4,
            'part_number' => 3,
            'marks' => 1,
            'instructions' => 'Part 3: Two-way Discussion (4-5 minutes)',
            'section_specific_data' => [
                'part' => 3,
                'topic' => 'Travel and Tourism',
                'preparation_time' => 0,
                'speaking_time' => 120,
            ],
        ]);

        $this->command->info("Created Speaking Test: {$testSet->title} with 4 questions");
    }
}
