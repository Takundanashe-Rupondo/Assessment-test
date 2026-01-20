<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user'
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('user', $user->role);
    }

    public function test_user_password_is_hashed()
    {
        $password = 'plain-password';
        $user = User::factory()->create([
            'password' => Hash::make($password)
        ]);

        $this->assertTrue(Hash::check($password, $user->password));
    }

    public function test_user_can_create_sanctum_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');

        $this->assertNotNull($token);
        $this->assertNotNull($token->accessToken);
        $this->assertEquals('auth_token', $token->accessToken->name);
    }

    public function test_user_email_is_unique()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(QueryException::class);
        User::factory()->create(['email' => 'test@example.com']);
    }

    public function test_user_default_role_is_user()
    {
        $user = User::factory()->create();

        $this->assertEquals('user', $user->role);
    }

    public function test_user_can_be_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertEquals('admin', $admin->role);
    }

    public function test_user_fillable_attributes()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => 'user'
        ];

        $user = User::create($userData);

        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertEquals($userData['role'], $user->role);
    }

    public function test_user_hidden_attributes()
    {
        $user = User::factory()->create([
            'password' => 'hashed-password',
            'remember_token' => 'remember-token'
        ]);

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    public function test_user_casts()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->updated_at);
    }

    public function test_user_factory_creates_valid_user()
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertNotEmpty($user->name);
        $this->assertNotEmpty($user->email);
        $this->assertTrue(str_contains($user->email, '@'));
    }

    public function test_user_can_check_password()
    {
        $password = 'secret-password';
        $user = User::factory()->create([
            'password' => Hash::make($password)
        ]);

        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertFalse(Hash::check('wrong-password', $user->password));
    }
}
