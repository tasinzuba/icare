<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if roles exist, if not create them
        $adminExists = DB::table('roles')->where('slug', 'admin')->exists();
        $teacherExists = DB::table('roles')->where('slug', 'teacher')->exists();
        $studentExists = DB::table('roles')->where('slug', 'student')->exists();
        
        if (!$adminExists) {
            DB::table('roles')->insert([
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full system access',
                'is_system_role' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        if (!$teacherExists) {
            DB::table('roles')->insert([
                'name' => 'Teacher',
                'slug' => 'teacher',
                'description' => 'Can evaluate students and manage tests',
                'is_system_role' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        if (!$studentExists) {
            DB::table('roles')->insert([
                'name' => 'Student',
                'slug' => 'student',
                'description' => 'Can take tests and view results',
                'is_system_role' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Don't delete system roles
    }
};
