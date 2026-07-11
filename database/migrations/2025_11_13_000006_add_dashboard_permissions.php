<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add Dashboard permissions
        $dashboardPermissions = [
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'module' => 'dashboard'],
            ['name' => 'View Dashboard Stats', 'slug' => 'dashboard.stats', 'module' => 'dashboard'],
        ];
        
        // Insert permissions
        foreach ($dashboardPermissions as $permission) {
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
        
        // Assign dashboard permissions to Admin role
        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        if ($adminRole) {
            $dashboardPermissionIds = DB::table('permissions')
                ->whereIn('slug', ['dashboard.view', 'dashboard.stats'])
                ->pluck('id');
            
            foreach ($dashboardPermissionIds as $permissionId) {
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
        
        // Assign dashboard.view to Teacher role
        $teacherRole = DB::table('roles')->where('slug', 'teacher')->first();
        if ($teacherRole) {
            $viewPermissionId = DB::table('permissions')
                ->where('slug', 'dashboard.view')
                ->value('id');
            
            if ($viewPermissionId) {
                $exists = DB::table('role_permission')
                    ->where('role_id', $teacherRole->id)
                    ->where('permission_id', $viewPermissionId)
                    ->exists();
                    
                if (!$exists) {
                    DB::table('role_permission')->insert([
                        'role_id' => $teacherRole->id,
                        'permission_id' => $viewPermissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('permissions')->where('module', 'dashboard')->delete();
    }
};
