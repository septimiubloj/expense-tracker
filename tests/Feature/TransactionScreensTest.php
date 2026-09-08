<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Book;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TransactionScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_options_and_entries_are_limited_to_the_selected_book(): void
    {
        $book = Book::factory()->create(['currency_code' => 'KWD']);
        $account = Account::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create();
        $entry = Transaction::factory()->for($book)->for($account)->for($category)->create(['amount_minor' => -1234]);
        $otherBook = Book::factory()->for($book->user)->create();
        Account::factory()->for($otherBook)->create();
        Category::factory()->for($otherBook)->create();
        Transaction::factory()->create();

        $this->actingAs($book->user)->get(route('books.transactions.index', $book))
            ->assertInertia(fn (Assert $page) => $page->component('transactions/index')
                ->where('book.currency_code', 'KWD')->has('accounts', 1)->where('accounts.0.id', $account->id)
                ->has('categories', 1)->where('categories.0.id', $category->id)
                ->has('transactions', 1)->where('transactions.0.id', $entry->id)
                ->where('transactions.0.amount_minor', -1234));
    }

    public function test_new_book_has_empty_editor_options_and_ledger(): void
    {
        $book = Book::factory()->create();
        $this->actingAs($book->user)->get(route('books.transactions.index', $book))
            ->assertInertia(fn (Assert $page) => $page->component('transactions/index')
                ->has('accounts', 0)->has('categories', 0)->has('transactions', 0));
    }

    public function test_transfer_lifecycle_displays_feedback_and_keeps_both_entries_together(): void
    {
        $book = Book::factory()->create();
        $source = Account::factory()->for($book)->create(['opened_on' => '2026-01-01']);
        $destination = Account::factory()->for($book)->create(['opened_on' => '2026-01-01']);
        $data = ['source_account_id' => $source->id, 'destination_account_id' => $destination->id,
            'amount_minor' => 1234, 'occurred_on' => '2026-09-06', 'status' => 'cleared', 'memo' => 'Savings'];
        $url = route('books.transactions.index', $book);

        $this->actingAs($book->user)->post(route('books.transfers.store', $book), $data)
            ->assertRedirect($url)->assertSessionHas('status', 'Transfer created.');
        $group = $book->transactions()->firstOrFail()->transfer_group_id;
        $this->get($url)->assertInertia(fn (Assert $page) => $page->where('status', 'Transfer created.')
            ->has('transactions', 2)->where('transactions.0.transfer_group_id', $group)
            ->where('transactions.1.transfer_group_id', $group));

        $this->put(route('books.transfers.update', [$book, $group]), [...$data, 'amount_minor' => 2345])
            ->assertRedirect($url)->assertSessionHas('status', 'Transfer updated.');
        $this->assertDatabaseHas('transactions', ['account_id' => $source->id, 'amount_minor' => -2345, 'category_id' => null]);
        $this->assertDatabaseHas('transactions', ['account_id' => $destination->id, 'amount_minor' => 2345, 'category_id' => null]);

        $this->delete(route('books.transfers.destroy', [$book, $group]))
            ->assertRedirect($url)->assertSessionHas('status', 'Transfer deleted.');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_transaction_feedback_is_rendered_after_creation(): void
    {
        $book = Book::factory()->create();
        $account = Account::factory()->for($book)->create(['opened_on' => '2026-01-01']);
        $this->actingAs($book->user)->post(route('books.transactions.store', $book), [
            'account_id' => $account->id, 'category_id' => null, 'amount_minor' => -1234,
            'occurred_on' => '2026-09-06', 'status' => 'pending', 'payee' => 'Grocer',
        ])->assertRedirect(route('books.transactions.index', $book));
        $this->assertDatabaseHas('transactions', ['book_id' => $book->id, 'amount_minor' => -1234, 'payee' => 'Grocer']);
        $this->get(route('books.transactions.index', $book))
            ->assertInertia(fn (Assert $page) => $page->where('status', 'Transaction created.')
                ->where('transactions.0.status', 'pending'));
    }
}
