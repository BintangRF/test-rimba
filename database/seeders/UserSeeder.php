<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'id' => Str::uuid(),
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => true,
            ]
        );

        User::updateOrInsert(
            ['email' => 'manager@example.com'],
            [
                'id' => Str::uuid(),
                'name' => 'Manager User',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'status' => true,
            ]
        );

        User::updateOrInsert(
            ['email' => 'staff1@example.com'],
            [
                'id' => Str::uuid(),
                'name' => 'Staff1 User',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'status' => true,
            ]
        );

        User::updateOrInsert(
            ['email' => 'staff2@example.com'],
            [
                'id' => Str::uuid(),
                'name' => 'Staff2 User',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'status' => false,
            ]
        );

    }
}
