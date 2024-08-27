<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_user()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'User Test',
            'email' => 'test@email.com',
            'password' => '12345678',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email'
                ]
            ]);

        $this->assertDatabaseHas('users', ['email' => 'test@email.com']);
    }

    public function test_login_user()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in'
            ]);
    }

    public function test_logout_user()
    {
        $password = 'password';
        $user = User::factory()
            ->create([
                'password' => bcrypt($password)
            ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->postJson('/api/logout',
            [],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout successfully!']);
    }

    public function test_refresh_token()
    {
        $password = 'password';
        $user = User::factory()
            ->create([
                'password' => bcrypt($password)
            ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->postJson('/api/refresh',
            [],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in'
            ]);
    }

    public function test_login_returns_token()
    {
        $password = 'secret';

        $user = User::factory()
            ->create(['password' => bcrypt($password)]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);
    }
}
