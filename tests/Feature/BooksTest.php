<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BooksTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_only_see_their_own_books(): void
    {
        $user = User::factory()->create();
        Book::factory()->for($user)->create(['name' => 'Household']);
        Book::factory()->create(['name' => 'Other user book']);

        $this->actingAs($user)
            ->get(route('books.index'))
            ->assertOk();
    }

    public function test_a_user_can_create_and_update_a_book(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('books.store'), [
                'name' => 'Personal',
                'currency_code' => 'RON',
                'timezone' => 'Europe/Bucharest',
            ])
            ->assertRedirect(route('books.index'));

        $book = Book::query()->where('name', 'Personal')->firstOrFail();

        $this->actingAs($user)
            ->put(route('books.update', $book), [
                'name' => 'Personal 2026',
                'currency_code' => 'EUR',
                'timezone' => 'Europe/Bucharest',
            ])
            ->assertRedirect(route('books.index'));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'name' => 'Personal 2026',
            'currency_code' => 'EUR',
        ]);
    }

    public function test_book_creation_rejects_duplicate_names_and_invalid_timezone(): void
    {
        $user = User::factory()->create();
        Book::factory()->for($user)->create(['name' => 'Personal']);

        $this->actingAs($user)
            ->post(route('books.store'), [
                'name' => 'Personal',
                'currency_code' => 'RON',
                'timezone' => 'not-a-timezone',
            ])
            ->assertSessionHasErrors(['name', 'timezone']);
    }

    public function test_a_user_cannot_update_another_users_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)
            ->put(route('books.update', $book), [
                'name' => 'Stolen update',
                'currency_code' => 'USD',
                'timezone' => 'UTC',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('books', ['name' => 'Stolen update']);
    }
}
