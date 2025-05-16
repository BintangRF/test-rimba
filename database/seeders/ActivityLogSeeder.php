<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Str;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        ActivityLog::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'action' => 'create_user',
            'description' => 'Seeded a test user',
            'logged_at' => now(),
        ]);
    }
}
