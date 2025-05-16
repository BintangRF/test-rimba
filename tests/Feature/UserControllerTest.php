<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user()
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'secret123',
            'role' => 'staff',
            'status' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJson(['email' => 'new@example.com']);
    }

    public function test_non_admin_cannot_create_user()
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'staff']));

        $response = $this->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'secret123',
            'role' => 'staff',
            'status' => true,
        ]);

        $response->assertStatus(403); // karena ada policy atau middleware
    }

    public function test_user_listing_requires_authorization()
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'manager']));

        $response = $this->getJson('/api/users');

        $response->assertStatus(200); // jika policy mengizinkan
    }
}
