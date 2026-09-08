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
use Tests\TestCase;

class BudgetPeriodScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_period_selection_lists_only_the_selected_books_periods_newest_first(): void
    {
        $book = Book::factory()->create();
        $older = BudgetPeriod::factory()->for($book)->create(['starts_on' => '2026-08-01', 'ends_on' => '2026-08-31']);
        $newer = BudgetPeriod::factory()->for($book)->create(['starts_on' => '2026-09-01', 'ends_on' => '2026-09-30']);
        BudgetPeriod::factory()->create();

        $this->actingAs($book->user)->get(route('books.budget-periods.index', $book))
            ->assertInertia(fn (Assert $page) => $page->component('budget-periods/index')
                ->where('book.id', $book->id)->has('periods', 2)
                ->where('periods.0.id', $newer->id)->where('periods.1.id', $older->id));
    }

    public function test_a_book_without_periods_returns_an_empty_selection(): void
    {
        $book = Book::factory()->create();

        $this->actingAs($book->user)->get(route('books.budget-periods.index', $book))
            ->assertInertia(fn (Assert $page) => $page->component('budget-periods/index')->has('periods', 0));
    }

    public function test_a_period_can_be_created_with_inclusive_same_day_dates(): void
    {
        $book = Book::factory()->create();

        $this->actingAs($book->user)->post(route('books.budget-periods.store', $book), [
            'name' => 'One day', 'starts_on' => '2026-09-06', 'ends_on' => '2026-09-06', 'status' => 'open',
        ])->assertRedirect(route('books.budget-periods.index', $book));

        $this->assertDatabaseHas('budget_periods', ['book_id' => $book->id, 'name' => 'One day', 'status' => 'open']);
        $period = $book->budgetPeriods()->sole();
        $this->assertSame('2026-09-06', $period->starts_on->toDateString());
        $this->assertSame('2026-09-06', $period->ends_on->toDateString());
        $this->get(route('books.budget-periods.index', $book))
            ->assertInertia(fn (Assert $page) => $page->where('status', 'Budget period created.'));
    }

    public function test_period_creation_rejects_reversed_dates_and_invalid_status(): void
    {
        $book = Book::factory()->create();

        $this->actingAs($book->user)->post(route('books.budget-periods.store', $book), [
            'name' => 'Invalid period', 'starts_on' => '2026-09-30', 'ends_on' => '2026-09-01', 'status' => 'invalid',
        ])->assertSessionHasErrors(['ends_on', 'status']);

        $this->assertDatabaseMissing('budget_periods', ['book_id' => $book->id, 'name' => 'Invalid period']);
    }

    public function test_a_period_can_be_renamed_rescheduled_and_archived(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create();

        $this->actingAs($book->user)->put(route('books.budget-periods.update', [$book, $period]), [
            'name' => 'Completed week', 'starts_on' => '2026-09-01', 'ends_on' => '2026-09-07', 'status' => 'archived',
        ])->assertRedirect(route('books.budget-periods.index', $book))->assertSessionHas('status', 'Budget period updated.');

        $this->assertDatabaseHas('budget_periods', ['id' => $period->id, 'name' => 'Completed week', 'status' => 'archived']);
        $period->refresh();
        $this->assertSame('2026-09-01', $period->starts_on->toDateString());
        $this->assertSame('2026-09-07', $period->ends_on->toDateString());
    }

    public function test_invalid_period_updates_leave_the_saved_period_unchanged(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create(['name' => 'Original', 'starts_on' => '2026-09-01', 'ends_on' => '2026-09-07', 'status' => 'open']);

        $this->actingAs($book->user)->put(route('books.budget-periods.update', [$book, $period]), [
            'name' => '', 'starts_on' => '2026-09-30', 'ends_on' => '2026-09-01', 'status' => 'invalid',
        ])->assertSessionHasErrors(['name', 'ends_on', 'status']);

        $this->assertDatabaseHas('budget_periods', ['id' => $period->id, 'name' => 'Original', 'status' => 'open']);
        $period->refresh();
        $this->assertSame('2026-09-01', $period->starts_on->toDateString());
        $this->assertSame('2026-09-07', $period->ends_on->toDateString());
    }

    public function test_selecting_a_period_shows_only_its_allocations(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create();
        $allocation = BudgetAllocation::factory()->for($period)->for($category)->create(['planned_amount_minor' => 12345]);
        BudgetAllocation::factory()->create();

        $this->actingAs($book->user)->get(route('books.budget-periods.allocations.index', [$book, $period]))
            ->assertInertia(fn (Assert $page) => $page->component('budget-allocations/index')
                ->where('period.id', $period->id)->has('allocations', 1)
                ->where('allocations.0.id', $allocation->id)->where('allocations.0.planned_amount_minor', 12345));
    }

    public function test_deleting_a_period_removes_its_allocations_and_keeps_transactions(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create();
        $allocation = BudgetAllocation::factory()->for($period)->for($category)->create();
        $account = Account::factory()->for($book)->create();
        $transaction = Transaction::factory()->for($book)->for($account)->create();
        $otherPeriod = BudgetPeriod::factory()->for($book)->create(['starts_on' => '2026-10-01', 'ends_on' => '2026-10-31']);

        $this->actingAs($book->user)->delete(route('books.budget-periods.destroy', [$book, $period]))
            ->assertRedirect(route('books.budget-periods.index', $book))->assertSessionHas('status', 'Budget period deleted.');

        $this->assertModelMissing($period);
        $this->assertModelMissing($allocation);
        $this->assertModelExists($transaction);
        $this->assertModelExists($otherPeriod);
    }

    public function test_period_selection_and_mutations_reject_a_period_from_another_owned_book(): void
    {
        $book = Book::factory()->create();
        $otherBook = Book::factory()->for($book->user)->create();
        $period = BudgetPeriod::factory()->for($otherBook)->create(['name' => 'Unchanged']);

        $this->actingAs($book->user)->get(route('books.budget-periods.allocations.index', [$book, $period]))->assertNotFound();
        $this->put(route('books.budget-periods.update', [$book, $period]), [
            'name' => 'Wrong book', 'starts_on' => '2026-09-01', 'ends_on' => '2026-09-30', 'status' => 'open',
        ])->assertNotFound();
        $this->delete(route('books.budget-periods.destroy', [$book, $period]))->assertNotFound();

        $this->assertDatabaseHas('budget_periods', ['id' => $period->id, 'name' => 'Unchanged', 'book_id' => $otherBook->id]);
    }

    public function test_users_cannot_view_or_change_another_users_periods(): void
    {
        $book = Book::factory()->create();
        $period = BudgetPeriod::factory()->for($book)->create(['name' => 'Private']);
        $user = User::factory()->create();
        $data = ['name' => 'Unauthorized', 'starts_on' => '2026-09-01', 'ends_on' => '2026-09-30', 'status' => 'closed'];

        $this->actingAs($user)->get(route('books.budget-periods.index', $book))->assertForbidden();
        $this->get(route('books.budget-periods.allocations.index', [$book, $period]))->assertForbidden();
        $this->post(route('books.budget-periods.store', $book), $data)->assertForbidden();
        $this->put(route('books.budget-periods.update', [$book, $period]), $data)->assertForbidden();
        $this->delete(route('books.budget-periods.destroy', [$book, $period]))->assertForbidden();

        $this->assertDatabaseHas('budget_periods', ['id' => $period->id, 'name' => 'Private']);
        $this->assertDatabaseMissing('budget_periods', ['name' => 'Unauthorized']);
    }

    public function test_guests_are_redirected_to_login_from_period_selection(): void
    {
        $book = Book::factory()->create();

        $this->get(route('books.budget-periods.index', $book))->assertRedirect(route('login'));
    }

    public function test_duplicate_dates_show_validation_feedback_on_create_and_update(): void
    {
        $book = Book::factory()->create();
        BudgetPeriod::factory()->for($book)->create(['starts_on' => '2026-09-01', 'ends_on' => '2026-09-30']);
        $period = BudgetPeriod::factory()->for($book)->create(['starts_on' => '2026-10-01', 'ends_on' => '2026-10-31']);
        $data = ['name' => 'Duplicate', 'starts_on' => '2026-09-01', 'ends_on' => '2026-09-30', 'status' => 'open'];

        $this->actingAs($book->user)->post(route('books.budget-periods.store', $book), $data)
            ->assertSessionHasErrors(['ends_on' => 'A budget period with these dates already exists in this book.']);
        $this->put(route('books.budget-periods.update', [$book, $period]), $data)
            ->assertSessionHasErrors(['ends_on' => 'A budget period with these dates already exists in this book.']);

        $this->assertDatabaseMissing('budget_periods', ['book_id' => $book->id, 'name' => 'Duplicate']);
        $this->assertSame('2026-10-01', $period->fresh()->starts_on->toDateString());
    }

    public function test_unchanged_dates_and_dates_used_in_another_book_are_allowed(): void
    {
        $book = Book::factory()->create();
        BudgetPeriod::factory()->create(['starts_on' => '2026-09-01', 'ends_on' => '2026-09-30']);
        $data = ['name' => 'September', 'starts_on' => '2026-09-01', 'ends_on' => '2026-09-30', 'status' => 'open'];

        $this->actingAs($book->user)->post(route('books.budget-periods.store', $book), $data)
            ->assertRedirect(route('books.budget-periods.index', $book))->assertSessionHasNoErrors();
        $period = $book->budgetPeriods()->sole();
        $this->put(route('books.budget-periods.update', [$book, $period]), [...$data, 'status' => 'closed'])
            ->assertRedirect(route('books.budget-periods.index', $book))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('budget_periods', ['id' => $period->id, 'status' => 'closed']);
    }

    public function test_period_forms_reject_timestamps_and_invalid_calendar_dates(): void
    {
        $book = Book::factory()->create();
        $data = ['name' => 'Invalid dates', 'starts_on' => '2026-09-01T00:00:00Z', 'ends_on' => '2026-02-30', 'status' => 'open'];

        $this->actingAs($book->user)->post(route('books.budget-periods.store', $book), $data)
            ->assertSessionHasErrors(['starts_on', 'ends_on']);

        $this->assertDatabaseMissing('budget_periods', ['book_id' => $book->id, 'name' => 'Invalid dates']);
    }
}
