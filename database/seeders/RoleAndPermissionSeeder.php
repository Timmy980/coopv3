<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $memberRole = Role::create(['name' => 'member']);

        // Create permissions
        $permissions = [
            'manage users',
            'manage roles',
            'manage permissions',
            'view dashboard',
            'manage loans',
            'manage savings',
            'manage transactions',
            'approve withdrawals',
            'manage settings'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Give all permissions to admin role
        $adminRole->givePermissionTo(Permission::all());

        // Give basic permissions to member role
        $memberRole->givePermissionTo([
            'view dashboard'
        ]);
    }
}
