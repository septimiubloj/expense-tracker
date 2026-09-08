<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Book;
use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_access_only_records_from_their_own_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->for($owner)->create();
        $account = Account::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $allocation = BudgetAllocation::factory()
            ->for($period)
            ->for($category)
            ->create();
        $transaction = Transaction::factory()
            ->for($book)
            ->for($account)
            ->create();

        $this->assertTrue(Gate::forUser($owner)->allows('view', $book));
        $this->assertFalse(Gate::forUser($otherUser)->allows('view', $book));
        $this->assertTrue(Gate::forUser($owner)->allows('view', $account));
        $this->assertFalse(Gate::forUser($otherUser)->allows('view', $account));
        $this->assertTrue(Gate::forUser($owner)->allows('view', $category));
        $this->assertFalse(Gate::forUser($otherUser)->allows('view', $category));
        $this->assertTrue(Gate::forUser($owner)->allows('view', $period));
        $this->assertFalse(Gate::forUser($otherUser)->allows('view', $period));
        $this->assertTrue(Gate::forUser($owner)->allows('view', $allocation));
        $this->assertFalse(Gate::forUser($otherUser)->allows('view', $allocation));
        $this->assertTrue(Gate::forUser($owner)->allows('view', $transaction));
        $this->assertFalse(Gate::forUser($otherUser)->allows('view', $transaction));
    }
}
