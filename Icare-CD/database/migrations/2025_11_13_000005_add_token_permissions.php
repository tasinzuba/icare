<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add Token Packages permissions
        $tokenPackagesPermissions = [
            ['name' => 'View Token Packages', 'slug' => 'token-packages.view', 'module' => 'token-packages'],
            ['name' => 'Create Token Packages', 'slug' => 'token-packages.create', 'module' => 'token-packages'],
            ['name' => 'Edit Token Packages', 'slug' => 'token-packages.edit', 'module' => 'token-packages'],
            ['name' => 'Delete Token Packages', 'slug' => 'token-packages.delete', 'module' => 'token-packages'],
        ];
        
        // Add User Tokens permissions
        $userTokensPermissions = [
            ['name' => 'View User Tokens', 'slug' => 'user-tokens.view', 'module' => 'user-tokens'],
            ['name' => 'Add User Tokens', 'slug' => 'user-tokens.add', 'module' => 'user-tokens'],
            ['name' => 'Deduct User Tokens', 'slug' => 'user-tokens.deduct', 'module' => 'user-tokens'],
            ['name' => 'Set User Tokens', 'slug' => 'user-tokens.set', 'module' => 'user-tokens'],
        ];
        
        // Add Token Transactions permissions
        $tokenTransactionsPermissions = [
            ['name' => 'View Token Transactions', 'slug' => 'token-transactions.view', 'module' => 'token-transactions'],
            ['name' => 'Export Token Transactions', 'slug' => 'token-transactions.export', 'module' => 'token-transactions'],
        ];
        
        // Merge all permissions
        $allPermissions = array_merge(
            $tokenPackagesPermissions,
            $userTokensPermissions,
            $tokenTransactionsPermissions
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
    }

    public function down(): void
    {
        // Remove token-related permissions
        DB::table('permissions')->whereIn('module', [
            'token-packages',
            'user-tokens',
            'token-transactions'
        ])->delete();
    }
};
