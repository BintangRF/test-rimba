<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => \Illuminate\Support\Str::uuid(),
            'user_id' => User::factory(),
            'action' => 'task_overdue',
            'description' => 'Task overdue test',
            'logged_at' => now(),
        ];
    }

}
