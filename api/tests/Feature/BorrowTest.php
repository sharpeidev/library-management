<?php

namespace Tests\Feature;

use App\Models\Borrow;
use App\Models\User;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowTest extends TestCase
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

    public function test_get_all_borrows()
    {
        $token = $this->authenticate();

        Borrow::factory(3)
            ->create();

        $response = $this->getJson('/api/borrows', [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)->assertJsonCount(3);
    }

    public function test_store_borrow()
    {
        $token = $this->authenticate();

        $user = User::factory()
            ->create();

        $book = Book::factory()
            ->create();

        $response = $this->postJson('/api/borrows', [
                'user_id' => $user->id,
                'book_id' => $book->id,
                'borrowed_at' => now()->toDateString(),
                'return_date' => now()->addDays(7)->toDateString(),
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(201)
            ->assertJson(['message' => 'Book borrowed with success!']);

        $this->assertDatabaseHas('borrows', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_get_a_single_borrow()
    {
        $token = $this->authenticate();

        $borrow = Borrow::factory()
            ->create();

        $response = $this->getJson("/api/borrows/{$borrow->id}", [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)
            ->assertJson([
                [
                'id' => $borrow->id,
                'book' => Book::find($borrow->book_id)->title,
                'user' => User::find($borrow->user_id)->name,
                'borrowed_at' => $borrow->borrowed_at,
                'returned_at' => null,
                ]
            ]);
    }

    public function test_update_borrow()
    {
        $token = $this->authenticate();

        $user = User::factory()->create();
        $book = Book::factory()->create();

        $borrow = Borrow::factory()
            ->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'borrowed_at' => '2024-04-18',
                'returned_at' => null,
            ]);

        $response = $this->putJson("/api/borrows/{$borrow->id}", [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'borrowed_at' => '2024-04-18',
            'returned_at' => '2024-05-18',
        ], [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Book borrow updated successfully!']);

        $this->assertDatabaseHas('borrows', [
            'id' => $borrow->id,
            'user_id' => $user->id,
            'book_id' => $book->id,
            'borrowed_at' => '2024-04-18',
            'returned_at' => '2024-05-18',
        ]);
    }

    public function test_delete_borrow()
    {
        $token = $this->authenticate();

        $borrow = Borrow::factory()
            ->create();

        $response = $this->deleteJson("/api/borrows/{$borrow->id}", [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Book borrow deleted successfully!']);

        $this->assertDatabaseMissing('borrows', ['id' => $borrow->id]);
    }
}
