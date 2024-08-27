<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $password = 'password';

        $user = User::factory()->create([
            'password' => bcrypt($password)
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        return $loginResponse['access_token'];
    }

    public function test_get_all_users()
    {
        $token = $this->authenticate();

        User::factory(3)
            ->create();

        $response = $this->getJson('/api/users', [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(4);
    }

    public function test_get_a_single_user()
    {
        $token = $this->authenticate();

        $user = User::factory()
            ->create();

        $response = $this->getJson("/api/users/{$user->id}", [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)
            ->assertJson(['id' => $user->id]);
    }

    public function test_update_user()
    {
        $token = $this->authenticate();

        $user = User::factory()
            ->create();

        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Name Updated',
            'email' => 'updated@email.com',
            'password' => 'newpassword',
        ],
        ['Authorization' => "Bearer $token"]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'User updated successfully!']);

        $this->assertDatabaseHas('users', [
            'email' => 'updated@email.com'
        ]);
    }

    public function test_delete_user()
    {
        $token = $this->authenticate();

        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}",
            [],
            ['Authorization' => "Bearer $token"]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully!']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
