<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\BudgetPeriodStatus;
use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Models\Account;
use App\Models\Book;
use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_owned_ledger_records_are_connected_and_cast(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $account = Account::factory()->for($book)->create([
            'type' => AccountType::Checking,
            'opening_balance_minor' => 125_00,
        ]);
        $category = Category::factory()->for($book)->create([
            'type' => CategoryType::Expense,
        ]);
        $period = BudgetPeriod::factory()->for($book)->create([
            'status' => BudgetPeriodStatus::Open,
        ]);
        $allocation = BudgetAllocation::factory()
            ->for($period)
            ->for($category)
            ->create(['planned_amount_minor' => 50_00]);
        $transaction = Transaction::factory()
            ->for($book)
            ->for($account)
            ->for($category)
            ->create([
                'amount_minor' => -12_50,
                'status' => TransactionStatus::Cleared,
            ]);

        $this->assertTrue($user->books->contains($book));
        $this->assertTrue($book->accounts->contains($account));
        $this->assertTrue($book->categories->contains($category));
        $this->assertTrue($book->budgetPeriods->contains($period));
        $this->assertTrue($period->allocations->contains($allocation));
        $this->assertTrue($account->transactions->contains($transaction));
        $this->assertSame(AccountType::Checking, $account->type);
        $this->assertSame(CategoryType::Expense, $category->type);
        $this->assertSame(BudgetPeriodStatus::Open, $period->status);
        $this->assertSame(TransactionStatus::Cleared, $transaction->status);
        $this->assertSame(-1_250, $transaction->amount_minor);
        $this->assertSame(5_000, $allocation->planned_amount_minor);
    }

    public function test_book_queries_can_be_scoped_to_the_authenticated_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownedBook = Book::factory()->for($owner)->create();
        Book::factory()->for($otherUser)->create();

        $books = Book::query()->whereBelongsTo($owner)->get();

        $this->assertCount(1, $books);
        $this->assertTrue($books->first()->is($ownedBook));
    }
}
