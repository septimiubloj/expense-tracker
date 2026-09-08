<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\CalculateBudget;
use App\Actions\ManageTransfer;
use App\Models\Account;
use App\Models\Book;
use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class FinancialBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_period_totals_include_boundaries_refunds_and_pending_but_exclude_voids_and_transfers(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $account = Account::factory()->for($book)->create(['opened_on' => '2026-01-01', 'opening_balance_minor' => 10000]);
        $savings = Account::factory()->for($book)->create(['opened_on' => '2026-09-15', 'opening_balance_minor' => 2000]);
        $period = BudgetPeriod::factory()->for($book)->create(['starts_on' => '2026-09-01', 'ends_on' => '2026-09-30']);
        $income = Category::factory()->for($book)->create(['type' => 'income']);
        $expense = Category::factory()->for($book)->create(['type' => 'expense']);
        BudgetAllocation::factory()->for($period)->for($expense)->create(['planned_amount_minor' => 5000]);
        BudgetAllocation::factory()->for($period)->for($income)->create(['planned_amount_minor' => 15000]);

        foreach ([['2026-08-31', -1000, $expense, 'cleared'], ['2026-09-01', 20000, $income, 'pending'],
            ['2026-09-30', -4000, $expense, 'cleared'], ['2026-09-30', 500, $expense, 'cleared'],
            ['2026-09-30', -9000, $expense, 'void'], ['2026-10-01', -6000, $expense, 'cleared']] as [$date, $amount, $category, $status]) {
            Transaction::factory()->for($book)->for($account)->for($category)->create([
                'occurred_on' => $date, 'amount_minor' => $amount, 'status' => $status,
            ]);
        }

        (new ManageTransfer)->save($user, $book, ['source_account_id' => $account->id,
            'destination_account_id' => $savings->id, 'amount_minor' => 1000, 'occurred_on' => '2026-09-30', 'status' => 'cleared']);

        $result = (new CalculateBudget)->handle($user, $book, $period);

        $this->assertSame(20000, $result['income_minor']);
        $this->assertSame(3500, $result['expenses_minor']);
        $this->assertSame(16500, $result['net_minor']);
        $this->assertSame(['previous_minor' => 9000, 'current_minor' => 24500], $result['accounts'][$account->id]);
        $this->assertSame(['previous_minor' => 0, 'current_minor' => 3000], $result['accounts'][$savings->id]);
        $this->assertSame(['planned_minor' => 5000, 'actual_minor' => 3500, 'remaining_minor' => 1500, 'variance_minor' => 1500], $result['categories'][$expense->id]);
        $this->assertSame(5000, $result['categories'][$income->id]['variance_minor']);
    }

    public function test_transfer_lifecycle_updates_both_legs_and_rejects_individual_edits(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $source = Account::factory()->for($book)->create(['opened_on' => '2026-01-01']);
        $destination = Account::factory()->for($book)->create(['opened_on' => '2026-01-01']);
        $data = ['source_account_id' => $source->id, 'destination_account_id' => $destination->id,
            'amount_minor' => 1234, 'occurred_on' => '2026-09-01', 'status' => 'pending'];

        $this->actingAs($user)->post(route('books.transfers.store', $book), $data)->assertRedirect();
        $entries = $book->transactions()->orderBy('amount_minor')->get();
        $this->assertSame([-1234, 1234], $entries->pluck('amount_minor')->all());
        $this->assertSame([null, null], $entries->pluck('category_id')->all());
        $group = $entries->first()->transfer_group_id;
        $this->assertSame($group, $entries->last()->transfer_group_id);

        $this->put(route('books.transactions.update', [$book, $entries->first()]), [
            'account_id' => $source->id, 'amount_minor' => -999, 'occurred_on' => '2026-09-01', 'status' => 'pending',
        ])->assertSessionHasErrors('transfer');
        $this->delete(route('books.transactions.destroy', [$book, $entries->first()]))->assertSessionHasErrors('transfer');
        $this->assertSame([-1234, 1234], $book->transactions()->orderBy('amount_minor')->pluck('amount_minor')->all());

        $this->put(route('books.transfers.update', [$book, $group]), [...$data, 'amount_minor' => 2500, 'status' => 'cleared'])->assertRedirect();
        $this->assertSame([-2500, 2500], $book->transactions()->orderBy('amount_minor')->pluck('amount_minor')->all());
        $this->delete(route('books.transfers.destroy', [$book, $group]))->assertRedirect();
        $this->assertSame(0, $book->transactions()->count());
    }

    public function test_transfer_cannot_use_foreign_accounts_or_be_deleted_through_another_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $source = Account::factory()->for($book)->create(['opened_on' => '2026-01-01']);
        $foreign = Account::factory()->create(['opened_on' => '2026-01-01']);

        $this->actingAs($user)->post(route('books.transfers.store', $book), [
            'source_account_id' => $source->id, 'destination_account_id' => $foreign->id,
            'amount_minor' => 100, 'occurred_on' => '2026-09-01', 'status' => 'cleared',
        ])->assertSessionHasErrors('destination_account_id');
        $this->assertDatabaseCount('transactions', 0);
        $this->delete(route('books.transfers.destroy', [$foreign->book_id, 'unknown']))->assertForbidden();
    }

    #[TestWith([0, '2026-09-01', 'amount_minor'])]
    #[TestWith([100, '2025-12-31', 'occurred_on'])]
    #[TestWith([100, '2026-09-01 12:00:00', 'occurred_on'])]
    #[TestWith(['9007199254740992', '2026-09-01', 'amount_minor'])]
    public function test_invalid_transaction_money_and_dates_leave_the_ledger_unchanged(int|string $amount, string $date, string $field): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $account = Account::factory()->for($book)->create(['opened_on' => '2026-01-01']);

        $this->actingAs($user)->post(route('books.transactions.store', $book), [
            'account_id' => $account->id, 'amount_minor' => $amount, 'occurred_on' => $date, 'status' => 'pending',
        ])->assertSessionHasErrors($field);

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_transaction_changes_recalculate_balances_without_cached_totals(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $account = Account::factory()->for($book)->create(['opened_on' => '2026-01-01', 'opening_balance_minor' => 1000]);
        $period = BudgetPeriod::factory()->for($book)->create(['starts_on' => '2026-09-01', 'ends_on' => '2026-09-30']);
        $data = ['account_id' => $account->id, 'amount_minor' => -200, 'occurred_on' => '2026-09-01', 'status' => 'pending'];
        $calculator = new CalculateBudget;

        $this->actingAs($user)->post(route('books.transactions.store', $book), $data)->assertRedirect();
        $entry = $book->transactions()->firstOrFail();
        $this->assertSame(800, $calculator->handle($user, $book, $period)['accounts'][$account->id]['current_minor']);
        $this->put(route('books.transactions.update', [$book, $entry]), [...$data, 'amount_minor' => -300])->assertRedirect();
        $this->assertSame(700, $calculator->handle($user, $book, $period)['accounts'][$account->id]['current_minor']);
        $this->delete(route('books.transactions.destroy', [$book, $entry]))->assertRedirect();
        $this->assertSame(1000, $calculator->handle($user, $book, $period)['accounts'][$account->id]['current_minor']);
    }

    public function test_transfer_rolls_back_when_second_leg_fails(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $source = Account::factory()->for($book)->create(['opened_on' => '2026-01-01']);
        $destination = Account::factory()->for($book)->create(['opened_on' => '2026-01-01']);
        Transaction::creating(function (Transaction $transaction): void {
            if ($transaction->amount_minor > 0) {
                throw new \RuntimeException('Simulated second-leg failure');
            }
        });

        try {
            (new ManageTransfer)->save($user, $book, ['source_account_id' => $source->id,
                'destination_account_id' => $destination->id, 'amount_minor' => 100,
                'occurred_on' => '2026-09-01', 'status' => 'pending']);
            $this->fail('Expected failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated second-leg failure', $exception->getMessage());
        } finally {
            Transaction::flushEventListeners();
        }

        $this->assertDatabaseCount('transactions', 0);
    }

    #[TestWith([0, '2026-09-01', false, 'amount_minor'])]
    #[TestWith([100, '2025-12-31', false, 'occurred_on'])]
    #[TestWith([100, '2026-09-01', true, 'destination_account_id'])]
    public function test_invalid_transfers_do_not_create_either_leg(int $amount, string $date, bool $sameAccount, string $field): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $source = Account::factory()->for($book)->create(['opened_on' => '2026-01-01']);
        $destination = Account::factory()->for($book)->create(['opened_on' => '2026-01-01']);

        $this->actingAs($user)->post(route('books.transfers.store', $book), [
            'source_account_id' => $source->id, 'destination_account_id' => $sameAccount ? $source->id : $destination->id,
            'amount_minor' => $amount, 'occurred_on' => $date, 'status' => 'pending',
        ])->assertSessionHasErrors($field);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_opening_date_cannot_move_after_existing_entries(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $account = Account::factory()->for($book)->create(['opened_on' => '2026-01-01']);
        Transaction::factory()->for($book)->for($account)->create(['occurred_on' => '2026-09-01']);

        $this->actingAs($user)->put(route('books.accounts.update', [$book, $account]), [
            'name' => $account->name, 'type' => 'checking', 'opening_balance_minor' => 0, 'opened_on' => '2026-09-02',
        ])->assertSessionHasErrors('opened_on');
        $this->assertSame('2026-01-01', $account->fresh()->opened_on->toDateString());
    }

    public function test_negative_allocations_are_rejected(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create();

        $this->actingAs($user)->post(route('books.budget-periods.allocations.store', [$book, $period]), [
            'category_id' => $category->id, 'planned_amount_minor' => -1,
        ])->assertSessionHasErrors('planned_amount_minor');
        $this->assertDatabaseCount('budget_allocations', 0);
    }
}
