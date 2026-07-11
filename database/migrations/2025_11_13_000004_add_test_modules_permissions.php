<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add Test Categories permissions
        $testCategoriesPermissions = [
            ['name' => 'View Test Categories', 'slug' => 'test-categories.view', 'module' => 'test-categories'],
            ['name' => 'Create Test Categories', 'slug' => 'test-categories.create', 'module' => 'test-categories'],
            ['name' => 'Edit Test Categories', 'slug' => 'test-categories.edit', 'module' => 'test-categories'],
            ['name' => 'Delete Test Categories', 'slug' => 'test-categories.delete', 'module' => 'test-categories'],
        ];
        
        // Add Test Sets permissions
        $testSetsPermissions = [
            ['name' => 'View Test Sets', 'slug' => 'test-sets.view', 'module' => 'test-sets'],
            ['name' => 'Create Test Sets', 'slug' => 'test-sets.create', 'module' => 'test-sets'],
            ['name' => 'Edit Test Sets', 'slug' => 'test-sets.edit', 'module' => 'test-sets'],
            ['name' => 'Delete Test Sets', 'slug' => 'test-sets.delete', 'module' => 'test-sets'],
        ];
        
        // Add Full Tests permissions
        $fullTestsPermissions = [
            ['name' => 'View Full Tests', 'slug' => 'full-tests.view', 'module' => 'full-tests'],
            ['name' => 'Create Full Tests', 'slug' => 'full-tests.create', 'module' => 'full-tests'],
            ['name' => 'Edit Full Tests', 'slug' => 'full-tests.edit', 'module' => 'full-tests'],
            ['name' => 'Delete Full Tests', 'slug' => 'full-tests.delete', 'module' => 'full-tests'],
        ];
        
        // Merge all permissions
        $allPermissions = array_merge(
            $testCategoriesPermissions,
            $testSetsPermissions,
            $fullTestsPermissions
        );
        
        // Insert permissions
        foreach ($allPermissions as $permission) {
            // Check if permission already exists
            $exists = DB::table('permissions')->where('slug', $permission['slug'])->exists();
            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $permission['name'],
                    'slug' => $permission['slug'],
                    'module' => $permission['module'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        // Assign all new permissions to Admin role
        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        if ($adminRole) {
            $newPermissionSlugs = array_column($allPermissions, 'slug');
            $permissionIds = DB::table('permissions')
                ->whereIn('slug', $newPermissionSlugs)
                ->pluck('id');
            
            foreach ($permissionIds as $permissionId) {
                $exists = DB::table('role_permission')
                    ->where('role_id', $adminRole->id)
                    ->where('permission_id', $permissionId)
                    ->exists();
                    
                if (!$exists) {
                    DB::table('role_permission')->insert([
                        'role_id' => $adminRole->id,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        // Assign view permissions to Teacher role
        $teacherRole = DB::table('roles')->where('slug', 'teacher')->first();
        if ($teacherRole) {
            $teacherPermissions = DB::table('permissions')
                ->whereIn('slug', [
                    'test-categories.view',
                    'test-sets.view',
                    'full-tests.view'
                ])
                ->pluck('id');
            
            foreach ($teacherPermissions as $permissionId) {
                $exists = DB::table('role_permission')
                    ->where('role_id', $teacherRole->id)
                    ->where('permission_id', $permissionId)
                    ->exists();
                    
                if (!$exists) {
                    DB::table('role_permission')->insert([
                        'role_id' => $teacherRole->id,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Remove test categories, test sets, and full tests permissions
        DB::table('permissions')->whereIn('module', [
            'test-categories',
            'test-sets',
            'full-tests'
        ])->delete();
    }
};
