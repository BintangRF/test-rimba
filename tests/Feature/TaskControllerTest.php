<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_tasks()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Task::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/tasks');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function test_staff_can_view_only_assigned_tasks()
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $task1 = Task::factory()->create(['assigned_to' => $staff->id]);
        $task2 = Task::factory()->create();

        $response = $this->actingAs($staff)->getJson('/api/tasks');

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $task1->id])
                 ->assertJsonMissing(['id' => $task2->id]);
    }

    public function test_manager_cannot_assign_task_to_admin()
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'title' => 'Test',
            'description' => 'Task Desc',
            'assigned_to' => $admin->id,
            'status' => 'pending',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ];

        $response = $this->actingAs($manager)->postJson('/api/tasks', $payload);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Manager hanya bisa assign ke staff']);
    }
}
