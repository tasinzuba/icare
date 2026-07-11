<?php

namespace Database\Seeders;

use App\Models\TestSection;
use Illuminate\Database\Seeder;

class TestSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'name' => 'listening',
                'description' => 'The Listening section consists of 40 questions. You will listen to 4 recordings and answer questions related to them.',
                'time_limit' => 30, // 30 minutes
            ],
            [
                'name' => 'reading',
                'description' => 'The Reading section consists of 40 questions. You will read 3 passages and answer questions related to them.',
                'time_limit' => 60, // 60 minutes
            ],
            [
                'name' => 'writing',
                'description' => 'The Writing section consists of 2 tasks. Task 1 requires you to describe visual information, and Task 2 requires you to write an essay.',
                'time_limit' => 60, // 60 minutes
            ],
            [
                'name' => 'speaking',
                'description' => 'The Speaking section consists of an interview with 3 parts. You will be asked questions about yourself, a topic, and a more abstract discussion.',
                'time_limit' => 15, // 15 minutes (though actual test is 11-14 minutes)
            ],
        ];
        
        foreach ($sections as $section) {
            TestSection::create($section);
        }
    }
}