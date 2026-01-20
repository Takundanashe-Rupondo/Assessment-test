<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_all_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $users = User::factory()->count(3)->create();
        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/users');

        $response->assertStatus(200)
                ->assertJsonCount(4); // admin + 3 users
    }

    public function test_regular_user_cannot_get_all_users()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_user_can_get_own_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]);
    }

    public function test_admin_can_get_any_user_profile()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]);
    }

    public function test_user_cannot_get_nonexistent_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/users/99999');

        $response->assertStatus(404);
    }

    public function test_user_can_update_own_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $user->id,
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com'
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_admin_can_update_any_user_profile()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $token = $admin->createToken('auth_token')->plainTextToken;

        $updateData = [
            'name' => 'Admin Updated Name',
            'email' => 'adminupdated@example.com',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $user->id,
                    'name' => 'Admin Updated Name',
                    'email' => 'adminupdated@example.com'
                ]);
    }

    public function test_user_cannot_update_without_authentication()
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(401);
    }

    public function test_admin_can_delete_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'User deleted successfully']);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    public function test_regular_user_cannot_delete_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_with_duplicate_email()
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);
        $token = $user1->createToken('auth_token')->plainTextToken;

        $updateData = [
            'email' => 'user2@example.com',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/users/{$user1->id}", $updateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }
}
