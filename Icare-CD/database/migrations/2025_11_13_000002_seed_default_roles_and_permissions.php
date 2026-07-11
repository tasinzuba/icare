<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insert default permissions
        $permissions = [
            // User Management
            ['name' => 'View Users', 'slug' => 'users.view', 'module' => 'users'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'module' => 'users'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'module' => 'users'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'module' => 'users'],
            
            // Test Management
            ['name' => 'View Tests', 'slug' => 'tests.view', 'module' => 'tests'],
            ['name' => 'Create Tests', 'slug' => 'tests.create', 'module' => 'tests'],
            ['name' => 'Edit Tests', 'slug' => 'tests.edit', 'module' => 'tests'],
            ['name' => 'Delete Tests', 'slug' => 'tests.delete', 'module' => 'tests'],
            
            // Question Management
            ['name' => 'View Questions', 'slug' => 'questions.view', 'module' => 'questions'],
            ['name' => 'Create Questions', 'slug' => 'questions.create', 'module' => 'questions'],
            ['name' => 'Edit Questions', 'slug' => 'questions.edit', 'module' => 'questions'],
            ['name' => 'Delete Questions', 'slug' => 'questions.delete', 'module' => 'questions'],
            
            // Student Attempts Management
            ['name' => 'View Attempts', 'slug' => 'attempts.view', 'module' => 'attempts'],
            ['name' => 'Evaluate Attempts', 'slug' => 'attempts.evaluate', 'module' => 'attempts'],
            ['name' => 'Delete Attempts', 'slug' => 'attempts.delete', 'module' => 'attempts'],
            ['name' => 'Export Attempts', 'slug' => 'attempts.export', 'module' => 'attempts'],
            
            // Subscription Management
            ['name' => 'View Subscriptions', 'slug' => 'subscriptions.view', 'module' => 'subscriptions'],
            ['name' => 'Create Subscriptions', 'slug' => 'subscriptions.create', 'module' => 'subscriptions'],
            ['name' => 'Edit Subscriptions', 'slug' => 'subscriptions.edit', 'module' => 'subscriptions'],
            ['name' => 'Delete Subscriptions', 'slug' => 'subscriptions.delete', 'module' => 'subscriptions'],
            
            // Teacher Management
            ['name' => 'View Teachers', 'slug' => 'teachers.view', 'module' => 'teachers'],
            ['name' => 'Create Teachers', 'slug' => 'teachers.create', 'module' => 'teachers'],
            ['name' => 'Edit Teachers', 'slug' => 'teachers.edit', 'module' => 'teachers'],
            ['name' => 'Delete Teachers', 'slug' => 'teachers.delete', 'module' => 'teachers'],
            
            // Coupon Management
            ['name' => 'View Coupons', 'slug' => 'coupons.view', 'module' => 'coupons'],
            ['name' => 'Create Coupons', 'slug' => 'coupons.create', 'module' => 'coupons'],
            ['name' => 'Edit Coupons', 'slug' => 'coupons.edit', 'module' => 'coupons'],
            ['name' => 'Delete Coupons', 'slug' => 'coupons.delete', 'module' => 'coupons'],
            
            // Announcement Management
            ['name' => 'View Announcements', 'slug' => 'announcements.view', 'module' => 'announcements'],
            ['name' => 'Create Announcements', 'slug' => 'announcements.create', 'module' => 'announcements'],
            ['name' => 'Edit Announcements', 'slug' => 'announcements.edit', 'module' => 'announcements'],
            ['name' => 'Delete Announcements', 'slug' => 'announcements.delete', 'module' => 'announcements'],
            
            // Settings Management
            ['name' => 'View Settings', 'slug' => 'settings.view', 'module' => 'settings'],
            ['name' => 'Edit Settings', 'slug' => 'settings.edit', 'module' => 'settings'],
            
            // Reports
            ['name' => 'View Reports', 'slug' => 'reports.view', 'module' => 'reports'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'module' => 'reports'],
        ];
        
        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'slug' => $permission['slug'],
                'module' => $permission['module'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Insert default roles
        $adminRoleId = DB::table('roles')->insertGetId([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Full system access',
            'is_system_role' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $teacherRoleId = DB::table('roles')->insertGetId([
            'name' => 'Teacher',
            'slug' => 'teacher',
            'description' => 'Can evaluate students and manage tests',
            'is_system_role' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $studentRoleId = DB::table('roles')->insertGetId([
            'name' => 'Student',
            'slug' => 'student',
            'description' => 'Can take tests and view results',
            'is_system_role' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Assign all permissions to Admin
        $allPermissionIds = DB::table('permissions')->pluck('id');
        foreach ($allPermissionIds as $permissionId) {
            DB::table('role_permission')->insert([
                'role_id' => $adminRoleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Assign specific permissions to Teacher
        $teacherPermissions = [
            'tests.view', 'questions.view', 'attempts.view',
            'attempts.evaluate', 'teachers.view', 'reports.view'
        ];
        foreach ($teacherPermissions as $slug) {
            $permissionId = DB::table('permissions')->where('slug', $slug)->value('id');
            if ($permissionId) {
                DB::table('role_permission')->insert([
                    'role_id' => $teacherRoleId,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('role_permission')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
    }
};
