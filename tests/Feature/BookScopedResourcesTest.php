<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Book;
use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookScopedResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_book_owner_can_create_an_account_with_validated_fields(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('books.accounts.store', $book), [
                'name' => 'Checking',
                'type' => 'checking',
                'opening_balance_minor' => 12500,
                'opened_on' => '2026-09-01',
            ])
            ->assertRedirect(route('books.accounts.index', $book));

        $this->assertDatabaseHas('accounts', [
            'book_id' => $book->id,
            'name' => 'Checking',
            'opening_balance_minor' => 12500,
        ]);
    }

    public function test_nested_binding_rejects_a_record_from_another_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $otherBook = Book::factory()->create();
        $otherAccount = Account::factory()->for($otherBook)->create();

        $this->actingAs($user)
            ->get(route('books.accounts.index', [$book]))
            ->assertOk();

        $this->actingAs($user)
            ->delete(route('books.accounts.destroy', [$book, $otherAccount]))
            ->assertNotFound();

        $this->assertDatabaseHas('accounts', ['id' => $otherAccount->id]);
    }

    public function test_category_parent_must_belong_to_the_current_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $otherBook = Book::factory()->create();
        $otherCategory = Category::factory()->for($otherBook)->create();

        $this->actingAs($user)
            ->post(route('books.categories.store', $book), [
                'name' => 'Groceries',
                'type' => 'expense',
                'parent_id' => $otherCategory->id,
                'sort_order' => 0,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_budget_allocation_is_scoped_to_the_book_period_and_category(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create();

        $this->actingAs($user)
            ->post(route('books.budget-periods.allocations.store', [$book, $period]), [
                'category_id' => $category->id,
                'planned_amount_minor' => 50000,
            ])
            ->assertRedirect(route('books.budget-periods.allocations.index', [$book, $period]));

        $this->assertDatabaseHas('budget_allocations', [
            'budget_period_id' => $period->id,
            'category_id' => $category->id,
            'planned_amount_minor' => 50000,
        ]);
    }

    public function test_allocation_binding_rejects_an_allocation_from_another_period(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $otherPeriod = BudgetPeriod::factory()->for($book)->create([
            'name' => 'October 2026',
            'starts_on' => '2026-10-01',
            'ends_on' => '2026-10-31',
        ]);
        $category = Category::factory()->for($book)->create();
        $allocation = BudgetAllocation::factory()->for($otherPeriod)->for($category)->create();

        $this->actingAs($user)
            ->delete(route('books.budget-periods.allocations.destroy', [$book, $period, $allocation]))
            ->assertNotFound();

        $this->assertDatabaseHas('budget_allocations', ['id' => $allocation->id]);
    }
}
