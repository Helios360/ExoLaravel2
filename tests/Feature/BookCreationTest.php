<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class BookCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_is_valid():void {
        $user=User::factory()->create();
        $payload=[
            "title" => "Clean Code",
            "author" => "Robert C. Martin",
            "summary" => "A handbook of agile software craftsmanship.",
            "isbn" => "9780132350884",
        ];
        $response = $this->actingAs($user)->postJson('/api/books', $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('books', [
            "title" => "Clean Code",
            "author" => "Robert C. Martin",
            "summary" => "A handbook of agile software craftsmanship.",
            "isbn" => "9780132350884",
        ]);
    }

    public function test_book_is_not_valid():void {
        $user=User::factory()->create();
        $payload=[
            "title" => "No",
            "author" => "Robert C. Martin",
            "summary" => "A handbook of agile software craftsmanship.",
            "isbn" => "9780132350884",
        ];
        $response = $this->actingAs($user)->postJson('/api/books', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
        $this->assertDatabaseMissing('books', [
            'title' => 'No',
        ]);
    }

    public function test_user_auth_when_book_creation():void {
        $payload=[
            'title' => 'Clean Code',
        ];
        $response = $this->postJson('/api/books', $payload);
        $response->assertStatus(401);
        $this->assertDatabaseMissing('books', [
            'title' => 'Clean Code',
        ]);
    }
}
