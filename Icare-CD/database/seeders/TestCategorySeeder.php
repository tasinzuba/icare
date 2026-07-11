<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TestCategory;

class TestCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Academic',
                'slug' => 'academic',
                'description' => 'Academic IELTS tests for university admission and professional registration',
                'icon' => 'fas fa-graduation-cap',
                'color' => '#3B82F6',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'General Training',
                'slug' => 'general-training',
                'description' => 'General Training IELTS tests for immigration and work purposes',
                'icon' => 'fas fa-briefcase',
                'color' => '#10B981',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Practice',
                'slug' => 'practice',
                'description' => 'Practice tests to improve your skills',
                'icon' => 'fas fa-dumbbell',
                'color' => '#F59E0B',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Mock Exam',
                'slug' => 'mock-exam',
                'description' => 'Full-length mock exams simulating real test conditions',
                'icon' => 'fas fa-clipboard-check',
                'color' => '#EF4444',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            TestCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
