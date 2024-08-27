<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookAuthor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
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

    public function test_get_all_books()
    {
        $token = $this->authenticate();

        $author = Author::factory()
            ->create();

        $book = Book::factory()
            ->create();

        BookAuthor::create([
            'book_id' => $book->id,
            'author_id' => $author->id
        ]);

        $response = $this->getJson('/api/books', [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_get_a_single_book()
    {
        $token = $this->authenticate();

        $book = Book::factory()
            ->create();

        $response = $this->getJson("/api/books/{$book->id}", [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)
            ->assertJson(['id' => $book->id]);
    }

    public function test_store_book()
    {
        $token = $this->authenticate();

        $author = Author::factory()
            ->create();

        $response = $this->postJson('/api/books', [
            'title' => 'Title Book',
            'publication' => '2012',
            'author_id' => $author->id,
        ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(201)
            ->assertJson(['message' => 'Book created successfully!']);

        $this->assertDatabaseHas('books', ['title' => 'Title Book']);
        $this->assertDatabaseHas('book_authors', ['author_id' => $author->id]);
    }

    public function test_update_book()
    {
        $token = $this->authenticate();

        $book = Book::factory()
            ->create();

        $author = Author::factory()
            ->create();

        BookAuthor::create([
            'book_id' => $book->id,
            'author_id' => $author->id,
        ]);

        $newAuthor = Author::factory()->create();

        $response = $this->putJson("/api/books/{$book->id}", [
            'title' => 'Updated Book',
            'publication' => '2022',
            'author_id' => $newAuthor->id,
        ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200)
            ->assertJson(['message' => 'Book updated successfully!']);

        $this->assertDatabaseHas('books', ['title' => 'Updated Book']);
        $this->assertDatabaseHas('book_authors', ['author_id' => $newAuthor->id]);
    }

    public function test_delete_book()
    {
        $token = $this->authenticate();

        $book = Book::factory()
            ->create();

        $response = $this->deleteJson("/api/books/{$book->id}",
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200)
            ->assertJson(['message' => 'Book deleted successfully!']);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
