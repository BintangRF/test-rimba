<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Str;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $staff = User::where('role', 'staff')->first();

        Task::create([
            'id' => Str::uuid(),
            'title' => 'Sample Task 1',
            'description' => 'This is a sample task.',
            'assigned_to' => $staff->id,
            'status' => 'pending',
            'due_date' => now()->addDays(3),
            'created_by' => $admin->id,
        ]);
    }
}
