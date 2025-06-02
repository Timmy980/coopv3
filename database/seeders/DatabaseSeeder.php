<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions
        $this->call(RoleAndPermissionSeeder::class);
        $this->call(AdminUserSeeder::class);

        // Create admin user
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'unique_id' => 'ADMIN001',
            'status' => 'active'
        ]);

        // Assign admin role to admin user
        $admin->assignRole('admin');

        // Create a regular member user for testing
        $member = User::create([
            'first_name' => 'Member',
            'last_name' => 'User',
            'email' => 'member@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'unique_id' => 'MEM001',
            'status' => 'active'
        ]);

        // Assign member role
        $member->assignRole('member');
    }
}
