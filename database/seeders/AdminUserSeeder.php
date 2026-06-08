<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo admin user cho Backpack
        User::create([
            'name' => 'Admin',
            'user_name' => 'Admin',
            'TokenID' => '999999999',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => Hash::make('123'),
            'role' => 'admin',
            'address' => '123 Admin Street, City',
            'phone' => '+1234567890',
            'avatar' => 'default-avatar.jpg',
            'status' => 'active',
            'gender' => 'male',
            'birthday' => '1990-01-01',
            'is_active' => '1',
            'is_admin' => '1',
        ]);

        // Tạo test user
        User::create([
            'name' => 'Test User',
            'user_name' => 'TestUser',
            'TokenID' => '888888888',
            'email' => '1@1.1',
            'email_verified_at' => now(),
            'password' => Hash::make('1'),
            'role' => 'customer',
            'address' => '456 Test Street, City',
            'phone' => '+0987654321',
            'avatar' => 'default-avatar.jpg',
            'status' => 'active',
            'gender' => 'female',
            'birthday' => '1995-05-15',
            'is_active' => '1',
            'is_admin' => '0',
        ]);
    }
}
