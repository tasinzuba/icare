<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionBlank;
use App\Models\TestSet;
use App\Models\TestSection;

class BlankQuestionSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Creating sample blank questions...');
        
        // First ensure we have a test set
        $section = TestSection::firstOrCreate(
            ['name' => 'reading'],
            ['name' => 'reading', 'duration' => 60]
        );
        
        $testSet = TestSet::firstOrCreate(
            ['title' => 'Sample Reading Test with Blanks'],
            [
                'title' => 'Sample Reading Test with Blanks',
                'section_id' => $section->id,
                'active' => true
            ]
        );
        
        // Create Sample Questions
        
        // Question 1: Simple single blank
        $q1 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'sentence_completion',
            'content' => 'The capital of Bangladesh is [____1____].',
            'order_number' => 1,
            'part_number' => 1,
            'marks' => 1,
            'instructions' => 'Complete the sentence with the correct answer.'
        ]);
        
        // Add blank answers
        QuestionBlank::create([
            'question_id' => $q1->id,
            'blank_number' => 1,
            'correct_answer' => 'Dhaka',
            'alternate_answers' => ['dhaka', 'DHAKA']
        ]);
        
        // Also save in section_specific_data
        $q1->section_specific_data = [
            'blank_answers' => [
                1 => 'Dhaka/dhaka/DHAKA'
            ]
        ];
        $q1->save();
        
        $this->command->info("Created Question 1: ID = {$q1->id}");
        
        // Question 2: Multiple blanks
        $q2 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'sentence_completion',
            'content' => 'The [____1____] is the largest planet in our solar system. It has [____2____] moons.',
            'order_number' => 2,
            'part_number' => 1,
            'marks' => 2,
            'instructions' => 'Fill in the blanks with appropriate words.'
        ]);
        
        // Add blanks
        QuestionBlank::create([
            'question_id' => $q2->id,
            'blank_number' => 1,
            'correct_answer' => 'Jupiter',
            'alternate_answers' => ['jupiter']
        ]);
        
        QuestionBlank::create([
            'question_id' => $q2->id,
            'blank_number' => 2,
            'correct_answer' => '79',
            'alternate_answers' => ['seventy-nine', 'seventy nine']
        ]);
        
        $q2->section_specific_data = [
            'blank_answers' => [
                1 => 'Jupiter/jupiter',
                2 => '79/seventy-nine/seventy nine'
            ]
        ];
        $q2->save();
        
        $this->command->info("Created Question 2: ID = {$q2->id}");
        
        // Question 3: Form completion
        $q3 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'form_completion',
            'content' => "Patient Registration Form:\nName: [____1____]\nAge: [____2____] years\nBlood Group: [____3____]",
            'order_number' => 3,
            'part_number' => 1,
            'marks' => 3,
            'instructions' => 'Complete the form with the information given in the passage.'
        ]);
        
        QuestionBlank::create([
            'question_id' => $q3->id,
            'blank_number' => 1,
            'correct_answer' => 'John Smith',
            'alternate_answers' => ['John', 'Mr. Smith']
        ]);
        
        QuestionBlank::create([
            'question_id' => $q3->id,
            'blank_number' => 2,
            'correct_answer' => '25',
            'alternate_answers' => ['twenty-five', 'twenty five']
        ]);
        
        QuestionBlank::create([
            'question_id' => $q3->id,
            'blank_number' => 3,
            'correct_answer' => 'O+',
            'alternate_answers' => ['O positive', 'O+ve']
        ]);
        
        $q3->section_specific_data = [
            'blank_answers' => [
                1 => 'John Smith/John/Mr. Smith',
                2 => '25/twenty-five/twenty five',
                3 => 'O+/O positive/O+ve'
            ]
        ];
        $q3->save();
        
        $this->command->info("Created Question 3: ID = {$q3->id}");
        
        // Question 4: Bangla Example
        $q4 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'sentence_completion',
            'content' => 'আজ [____1____] বার। বাংলাদেশের রাজধানী [____2____]।',
            'order_number' => 4,
            'part_number' => 1,
            'marks' => 2,
            'instructions' => 'বাক্যটি সম্পূর্ণ করুন।'
        ]);
        
        QuestionBlank::create([
            'question_id' => $q4->id,
            'blank_number' => 1,
            'correct_answer' => 'শুক্রবার',
            'alternate_answers' => ['Friday', 'friday', 'Shukrobar']
        ]);
        
        QuestionBlank::create([
            'question_id' => $q4->id,
            'blank_number' => 2,
            'correct_answer' => 'ঢাকা',
            'alternate_answers' => ['Dhaka', 'dhaka']
        ]);
        
        $q4->section_specific_data = [
            'blank_answers' => [
                1 => 'শুক্রবার/Friday/friday/Shukrobar',
                2 => 'ঢাকা/Dhaka/dhaka'
            ]
        ];
        $q4->save();
        
        $this->command->info("Created Question 4: ID = {$q4->id}");
        
        // Now run debug
        $this->command->info("\n" . str_repeat('=', 50));
        $this->command->info("DEBUGGING CREATED QUESTIONS");
        $this->command->info(str_repeat('=', 50) . "\n");
        
        $questions = [$q1, $q2, $q3, $q4];
        
        foreach ($questions as $question) {
            $this->debugQuestion($question);
        }
        
        $this->command->info("\nSeeding completed! Test Set ID: {$testSet->id}");
        $this->command->info("You can now test these questions in the student portal.");
    }
    
    private function debugQuestion($question)
    {
        $this->command->info("\n--- Debugging Question {$question->id} ---");
        $this->command->info("Type: {$question->question_type}");
        $this->command->info("Content: " . substr($question->content, 0, 100) . "...");
        
        // Check blanks
        $blanks = $question->blanks;
        if ($blanks->isEmpty()) {
            $this->command->error("❌ No blanks found in QuestionBlank table!");
        } else {
            $this->command->info("✅ Found {$blanks->count()} blanks:");
            foreach ($blanks as $blank) {
                $this->command->info("   Blank {$blank->blank_number}: '{$blank->correct_answer}'");
                if ($blank->alternate_answers) {
                    $this->command->info("   Alternates: " . json_encode($blank->alternate_answers));
                }
            }
        }
        
        // Test some answers
        $this->command->info("\nTesting answer checking:");
        
        // Test for first blank
        if ($question->blanks()->where('blank_number', 1)->exists()) {
            $firstBlank = $question->blanks()->where('blank_number', 1)->first();
            $testAnswers = [
                $firstBlank->correct_answer,
                strtolower($firstBlank->correct_answer),
                strtoupper($firstBlank->correct_answer),
                'wrong_answer'
            ];
            
            foreach ($testAnswers as $test) {
                $result = $question->checkBlankAnswer(1, $test);
                $icon = $result ? '✅' : '❌';
                $this->command->info("   Test '{$test}' for blank 1: {$icon}");
            }
        }
        
        // Check trait method
        $allAnswers = $question->getBlankAnswersArray();
        $this->command->info("\nBlank answers from trait method:");
        foreach ($allAnswers as $num => $answer) {
            $this->command->info("   Blank {$num}: {$answer}");
        }
    }
}