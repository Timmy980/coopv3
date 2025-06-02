<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Create admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'phone_number' => '+2340000000000',
                'status' => 'active',
                'email_verified_at' => now()
            ]
        );

        // Assign admin role to user
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
