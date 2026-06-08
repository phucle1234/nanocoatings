<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo các user customer mẫu cho testing
        \App\Models\User::create([
            'name' => 'Customer 1',
            'user_name' => 'Customer1',
            'TokenID' => '777777777',
            'email' => 'customer1@example.com',
            'email_verified_at' => now(),
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'customer',
            'address' => '123 Customer Street, City',
            'phone' => '+1111111111',
            'avatar' => 'default-avatar.jpg',
            'status' => 'active',
            'gender' => 'male',
            'birthday' => '1990-01-01',
            'is_active' => '1',
            'is_admin' => '0',
        ]);

        \App\Models\User::create([
            'name' => 'Customer 2',
            'user_name' => 'Customer2',
            'TokenID' => '666666666',
            'email' => 'customer2@example.com',
            'email_verified_at' => now(),
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'customer',
            'address' => '456 Customer Avenue, City',
            'phone' => '+2222222222',
            'avatar' => 'default-avatar.jpg',
            'status' => 'active',
            'gender' => 'female',
            'birthday' => '1992-03-15',
            'is_active' => '1',
            'is_admin' => '0',
        ]);
    }
}
