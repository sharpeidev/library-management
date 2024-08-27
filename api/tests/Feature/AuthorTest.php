<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorTest extends TestCase
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

    public function test_get_all_authors()
    {
        $token = $this->authenticate();

        Author::factory(3)
            ->create();

        $response = $this->getJson('/api/authors', [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_get_a_single_author()
    {
        $token = $this->authenticate();

        $author = Author::factory()
            ->create();

        $response = $this->getJson("/api/authors/{$author->id}", [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)
            ->assertJson(['id' => $author->id]);
    }

    public function test_store_author()
    {
        $token = $this->authenticate();

        $response = $this->postJson('/api/authors',
            [
                'name' => 'Name Author',
                'birthday' => '1954-07-14',
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(201)
            ->assertJson(['message' => 'Author created successfully!']);

        $this->assertDatabaseHas('authors', [
            'name' => 'Name Author'
        ]);
    }

    public function test_update_author()
    {
        $token = $this->authenticate();

        $author = Author::factory()->create();

        $response = $this->putJson("/api/authors/{$author->id}",
            [
                'name' => 'Author Updated',
                'birthday' => '1966-11-23',
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200)
            ->assertJson(['message' => 'Author updated successfully!']);

        $this->assertDatabaseHas('authors', [
            'name' => 'Author Updated'
        ]);
    }

    public function test_delete_author()
    {
        $token = $this->authenticate();

        $author = Author::factory()->create();

        $response = $this->deleteJson("/api/authors/{$author->id}", [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Author deleted successfully!']);

        $this->assertDatabaseMissing('authors', [
            'id' => $author->id
        ]);
    }
}
