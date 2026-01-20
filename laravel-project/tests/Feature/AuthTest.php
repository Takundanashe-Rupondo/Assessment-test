<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'access_token',
                    'token_type',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
    }

    public function test_user_cannot_register_with_duplicate_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_register_with_invalid_email()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_register_with_short_password()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '12345',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    public function test_user_cannot_register_without_required_fields()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'access_token',
                    'token_type',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role'
                    ]
                ]);
    }

    public function test_user_cannot_login_with_invalid_email()
    {
        $user = User::factory()->create();

        $loginData = [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
                ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_user_cannot_login_with_invalid_password()
    {
        $user = User::factory()->create();

        $loginData = [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
                ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_user_cannot_login_with_nonexistent_email()
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
                ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_user_cannot_login_without_credentials()
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_user_can_logout_with_valid_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                ->assertJson(['message' => 'Logged out successfully']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id
        ]);
    }

    public function test_user_cannot_logout_without_token()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    public function test_user_cannot_logout_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token'
        ])->postJson('/api/logout');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/user');

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ]);
    }

    public function test_unauthenticated_user_cannot_get_profile()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
    public function test_user_cannot_login_with_mismatched_password()
{
    // Create a user with a known password hash
    $user = User::factory()->create([
        'password' => Hash::make('correct-password')
    ]);

    // Attempt login with the right email but wrong password
    $loginData = [
        'email' => $user->email,
        'password' => 'wrong-password',
    ];

    $response = $this->postJson('/api/login', $loginData);

    // Assert that login fails with 401 Unauthorized
    $response->assertStatus(401)
             ->assertJson([
                 'message' => 'Invalid credentials'
             ]);

    // Assert that no token was issued
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id
    ]);
}

}
