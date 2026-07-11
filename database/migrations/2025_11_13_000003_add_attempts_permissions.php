<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add Student Attempts permissions
        $attemptsPermissions = [
            ['name' => 'View Attempts', 'slug' => 'attempts.view', 'module' => 'attempts'],
            ['name' => 'Evaluate Attempts', 'slug' => 'attempts.evaluate', 'module' => 'attempts'],
            ['name' => 'Delete Attempts', 'slug' => 'attempts.delete', 'module' => 'attempts'],
            ['name' => 'Export Attempts', 'slug' => 'attempts.export', 'module' => 'attempts'],
        ];
        
        foreach ($attemptsPermissions as $permission) {
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
        
        // Assign attempts permissions to Admin role
        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        if ($adminRole) {
            $attemptsPermissionIds = DB::table('permissions')
                ->whereIn('slug', ['attempts.view', 'attempts.evaluate', 'attempts.delete', 'attempts.export'])
                ->pluck('id');
            
            foreach ($attemptsPermissionIds as $permissionId) {
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
        
        // Assign attempts.view and attempts.evaluate to Teacher role
        $teacherRole = DB::table('roles')->where('slug', 'teacher')->first();
        if ($teacherRole) {
            $teacherPermissions = DB::table('permissions')
                ->whereIn('slug', ['attempts.view', 'attempts.evaluate'])
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
        // Remove attempts permissions
        DB::table('permissions')->where('module', 'attempts')->delete();
    }
};
