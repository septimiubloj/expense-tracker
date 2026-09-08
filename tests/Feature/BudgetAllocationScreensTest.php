<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Book;
use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BudgetAllocationScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_receives_only_the_selected_books_categories_and_period_allocations(): void
    {
        $book = Book::factory()->create(['currency_code' => 'KWD']);
        $period = BudgetPeriod::factory()->for($book)->create();
        $parent = Category::factory()->for($book)->create(['sort_order' => 0]);
        $category = Category::factory()->for($book)->create(['parent_id' => $parent->id, 'sort_order' => 1]);
        $allocation = BudgetAllocation::factory()->for($period)->for($category)->create();
        Category::factory()->create();
        BudgetAllocation::factory()->create();

        $this->actingAs($book->user)->get(route('books.budget-periods.allocations.index', [$book, $period]))
            ->assertInertia(fn (Assert $page) => $page->component('budget-allocations/index')
                ->where('book.currency_code', 'KWD')->where('period.id', $period->id)
                ->has('categories', 2)->where('categories.0.id', $parent->id)
                ->where('categories.1.parent.id', $parent->id)
                ->has('allocations', 1)->where('allocations.0.id', $allocation->id));
    }

    public function test_new_book_returns_empty_categories_and_allocations(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create();

        $this->actingAs($book->user)->get(route('books.budget-periods.allocations.index', [$book, $period]))
            ->assertInertia(fn (Assert $page) => $page->component('budget-allocations/index')
                ->has('categories', 0)->has('allocations', 0));
    }

    public function test_zero_allocation_can_be_created_and_shows_feedback(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create();

        $this->actingAs($book->user)->post(route('books.budget-periods.allocations.store', [$book, $period]), [
            'category_id' => $category->id, 'planned_amount_minor' => 0,
        ])->assertRedirect(route('books.budget-periods.allocations.index', [$book, $period]));

        $this->assertDatabaseHas('budget_allocations', ['budget_period_id' => $period->id, 'category_id' => $category->id, 'planned_amount_minor' => 0]);
        $this->get(route('books.budget-periods.allocations.index', [$book, $period]))
            ->assertInertia(fn (Assert $page) => $page->where('status', 'Budget allocation created.'));
    }

    public function test_allocation_can_change_category_and_preserve_the_maximum_supported_amount(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create();
        $replacement = Category::factory()->for($book)->create();
        $allocation = BudgetAllocation::factory()->for($period)->for($category)->create();

        $this->actingAs($book->user)->put(route('books.budget-periods.allocations.update', [$book, $period, $allocation]), [
            'category_id' => $replacement->id, 'planned_amount_minor' => 9007199254740991,
        ])->assertRedirect(route('books.budget-periods.allocations.index', [$book, $period]))
            ->assertSessionHas('status', 'Budget allocation updated.');

        $this->assertDatabaseHas('budget_allocations', ['id' => $allocation->id, 'category_id' => $replacement->id, 'planned_amount_minor' => 9007199254740991]);
    }

    public function test_duplicate_categories_are_rejected_on_create_and_update(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create();
        $otherCategory = Category::factory()->for($book)->create();
        $existing = BudgetAllocation::factory()->for($period)->for($category)->create(['planned_amount_minor' => 100]);
        $allocation = BudgetAllocation::factory()->for($period)->for($otherCategory)->create(['planned_amount_minor' => 200]);
        $data = ['category_id' => $category->id, 'planned_amount_minor' => 300];

        $this->actingAs($book->user)->post(route('books.budget-periods.allocations.store', [$book, $period]), $data)
            ->assertSessionHasErrors(['category_id' => 'This category already has an allocation in this period.']);
        $this->put(route('books.budget-periods.allocations.update', [$book, $period, $allocation]), $data)
            ->assertSessionHasErrors(['category_id' => 'This category already has an allocation in this period.']);

        $this->assertDatabaseCount('budget_allocations', 2);
        $this->assertDatabaseHas('budget_allocations', ['id' => $allocation->id, 'category_id' => $otherCategory->id, 'planned_amount_minor' => 200]);
        $this->assertDatabaseHas('budget_allocations', ['id' => $existing->id, 'planned_amount_minor' => 100]);
    }

    public function test_category_can_be_reused_in_another_period_and_kept_on_update(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create(['starts_on' => '2026-09-01', 'ends_on' => '2026-09-30']);
        $otherPeriod = BudgetPeriod::factory()->for($book)->create(['starts_on' => '2026-10-01', 'ends_on' => '2026-10-31']);
        $category = Category::factory()->for($book)->create();
        BudgetAllocation::factory()->for($otherPeriod)->for($category)->create();

        $this->actingAs($book->user)->post(route('books.budget-periods.allocations.store', [$book, $period]), [
            'category_id' => $category->id, 'planned_amount_minor' => 12345,
        ])->assertRedirect(route('books.budget-periods.allocations.index', [$book, $period]))->assertSessionHasNoErrors();
        $allocation = $period->allocations()->sole();
        $this->put(route('books.budget-periods.allocations.update', [$book, $period, $allocation]), [
            'category_id' => $category->id, 'planned_amount_minor' => 0,
        ])->assertRedirect(route('books.budget-periods.allocations.index', [$book, $period]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('budget_allocations', ['id' => $allocation->id, 'category_id' => $category->id, 'planned_amount_minor' => 0]);
    }

    /** @return array<string, array{int|string}> */
    public static function invalidAmounts(): array
    {
        return ['negative' => [-1], 'fractional minor units' => ['1.5'], 'out of range' => ['9007199254740992'], 'invalid decimal input' => ['invalid']];
    }

    #[DataProvider('invalidAmounts')]
    public function test_invalid_amounts_do_not_change_allocations(int|string $amount): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create();
        $unused = Category::factory()->for($book)->create();
        $allocation = BudgetAllocation::factory()->for($period)->for($category)->create(['planned_amount_minor' => 12345]);

        $this->actingAs($book->user)->post(route('books.budget-periods.allocations.store', [$book, $period]), [
            'category_id' => $unused->id, 'planned_amount_minor' => $amount,
        ])->assertSessionHasErrors('planned_amount_minor');
        $this->put(route('books.budget-periods.allocations.update', [$book, $period, $allocation]), [
            'category_id' => $category->id, 'planned_amount_minor' => $amount,
        ])->assertSessionHasErrors('planned_amount_minor');

        $this->assertDatabaseCount('budget_allocations', 1);
        $this->assertDatabaseHas('budget_allocations', ['id' => $allocation->id, 'planned_amount_minor' => 12345]);
    }

    public function test_deleting_an_allocation_preserves_its_category_and_transactions(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create();
        $allocation = BudgetAllocation::factory()->for($period)->for($category)->create();
        $account = Account::factory()->for($book)->create();
        $transaction = Transaction::factory()->for($book)->for($account)->for($category)->create(['amount_minor' => -125]);

        $this->actingAs($book->user)->delete(route('books.budget-periods.allocations.destroy', [$book, $period, $allocation]))
            ->assertRedirect(route('books.budget-periods.allocations.index', [$book, $period]))->assertSessionHas('status', 'Budget allocation deleted.');

        $this->assertModelMissing($allocation);
        $this->assertModelExists($category);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'category_id' => $category->id, 'amount_minor' => -125]);
    }

    public function test_update_rejects_a_category_from_another_book(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create();
        $allocation = BudgetAllocation::factory()->for($period)->for($category)->create();
        $foreignCategory = Category::factory()->create();

        $this->actingAs($book->user)->put(route('books.budget-periods.allocations.update', [$book, $period, $allocation]), [
            'category_id' => $foreignCategory->id, 'planned_amount_minor' => 100,
        ])->assertSessionHasErrors('category_id');

        $this->assertDatabaseHas('budget_allocations', ['id' => $allocation->id, 'category_id' => $category->id]);
    }

    public function test_update_rejects_an_allocation_from_another_period(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create(['starts_on' => '2026-09-01', 'ends_on' => '2026-09-30']);
        $otherPeriod = BudgetPeriod::factory()->for($book)->create(['starts_on' => '2026-10-01', 'ends_on' => '2026-10-31']);
        $category = Category::factory()->for($book)->create();
        $allocation = BudgetAllocation::factory()->for($otherPeriod)->for($category)->create(['planned_amount_minor' => 200]);

        $this->actingAs($book->user)->put(route('books.budget-periods.allocations.update', [$book, $period, $allocation]), [
            'category_id' => $category->id, 'planned_amount_minor' => 100,
        ])->assertNotFound();

        $this->assertDatabaseHas('budget_allocations', ['id' => $allocation->id, 'planned_amount_minor' => 200]);
    }

    public function test_users_cannot_modify_another_users_allocations(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create();
        $allocation = BudgetAllocation::factory()->for($period)->for($category)->create(['planned_amount_minor' => 200]);
        $user = User::factory()->create();
        $data = ['category_id' => $category->id, 'planned_amount_minor' => 100];

        $this->actingAs($user)->post(route('books.budget-periods.allocations.store', [$book, $period]), $data)->assertForbidden();
        $this->put(route('books.budget-periods.allocations.update', [$book, $period, $allocation]), $data)->assertForbidden();
        $this->delete(route('books.budget-periods.allocations.destroy', [$book, $period, $allocation]))->assertForbidden();

        $this->assertDatabaseCount('budget_allocations', 1);
        $this->assertDatabaseHas('budget_allocations', ['id' => $allocation->id, 'planned_amount_minor' => 200]);
    }
}
