<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
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
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'assigned_to' => User::factory(),
            'status' => 'pending',
            'due_date' => now()->addDays(2),
            'created_by' => User::factory(),
        ];
    }

}
