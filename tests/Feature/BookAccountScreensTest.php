<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Book;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BookAccountScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_selection_contains_only_owned_books(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        Book::factory()->create();

        $this->actingAs($user)->get(route('books.index'))
            ->assertInertia(fn (Assert $page) => $page->component('books/index')
                ->has('books', 1)->where('books.0.id', $book->id));
    }

    public function test_accounts_screen_contains_scoped_accounts_and_currency_settings(): void
    {
        $book = Book::factory()->create(['currency_code' => 'JPY']);
        $account = Account::factory()->for($book)->create(['opening_balance_minor' => -125]);
        Account::factory()->create();

        $this->actingAs($book->user)->get(route('books.accounts.index', $book))
            ->assertInertia(fn (Assert $page) => $page->component('accounts/index')
                ->where('book.currency_code', 'JPY')->has('accounts', 1)
                ->where('accounts.0.id', $account->id)->where('accounts.0.opening_balance_minor', -125));
    }

    public function test_account_updates_show_feedback_and_persist_changes(): void
    {
        $book = Book::factory()->create();
        $account = Account::factory()->for($book)->create();

        $this->actingAs($book->user)->put(route('books.accounts.update', [$book, $account]), [
            'name' => 'Credit card', 'type' => 'credit', 'opening_balance_minor' => -12345, 'opened_on' => '2026-09-01',
        ])->assertRedirect(route('books.accounts.index', $book))->assertSessionHas('status', 'Account updated.');

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'name' => 'Credit card', 'type' => 'credit', 'opening_balance_minor' => -12345]);
        $this->get(route('books.accounts.index', $book))
            ->assertInertia(fn (Assert $page) => $page->where('status', 'Account updated.'));
    }

    public function test_account_with_transactions_cannot_be_deleted(): void
    {
        $book = Book::factory()->create();
        $account = Account::factory()->for($book)->create();
        $transaction = Transaction::factory()->for($book)->for($account)->create();

        $this->actingAs($book->user)->from(route('books.accounts.index', $book))
            ->delete(route('books.accounts.destroy', [$book, $account]))
            ->assertRedirect(route('books.accounts.index', $book))
            ->assertSessionHasErrors(['delete' => 'Accounts with transactions cannot be deleted.']);

        $this->assertModelExists($account);
        $this->assertModelExists($transaction);
    }

    public function test_empty_account_can_be_deleted(): void
    {
        $book = Book::factory()->create();
        $account = Account::factory()->for($book)->create();

        $this->actingAs($book->user)->delete(route('books.accounts.destroy', [$book, $account]))
            ->assertRedirect(route('books.accounts.index', $book))->assertSessionHas('status', 'Account deleted.');

        $this->assertModelMissing($account);
    }

    public function test_book_with_accounts_cannot_be_deleted(): void
    {
        $book = Book::factory()->create();
        $account = Account::factory()->for($book)->create();

        $this->actingAs($book->user)->from(route('books.index'))->delete(route('books.destroy', $book))
            ->assertRedirect(route('books.index'))->assertSessionHasErrors(['delete' => 'Delete the accounts in this book first.']);

        $this->assertModelExists($book);
        $this->assertModelExists($account);
    }

    public function test_empty_book_can_be_deleted(): void
    {
        $book = Book::factory()->create();

        $this->actingAs($book->user)->delete(route('books.destroy', $book))
            ->assertRedirect(route('books.index'))->assertSessionHas('status', 'Book deleted.');

        $this->assertModelMissing($book);
    }
}
