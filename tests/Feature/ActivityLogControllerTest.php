<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityLogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_logs()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ActivityLog::factory()->count(2)->create();

        $response = $this->actingAs($admin)->getJson('/api/logs');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    public function test_non_admin_cannot_see_logs()
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $response = $this->actingAs($staff)->getJson('/api/logs');

        $response->assertStatus(403)
                 ->assertJson(['message' => 'This action is unauthorized.']);
    }
}
