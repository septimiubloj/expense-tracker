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

class CategoryScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_are_book_scoped_sorted_and_include_their_parent(): void
    {
        $book = Book::factory()->create();
        $parent = Category::factory()->for($book)->create(['name' => 'Housing', 'sort_order' => 0]);
        $child = Category::factory()->for($book)->create(['name' => 'Rent', 'parent_id' => $parent->id, 'sort_order' => 1]);
        $later = Category::factory()->for($book)->create(['name' => 'Utilities', 'sort_order' => 1]);
        Category::factory()->create();

        $this->actingAs($book->user)->get(route('books.categories.index', $book))
            ->assertInertia(fn (Assert $page) => $page->component('categories/index')
                ->where('book.id', $book->id)->has('categories', 3)
                ->where('categories.0.id', $parent->id)->where('categories.1.id', $child->id)
                ->where('categories.1.parent.name', 'Housing')->where('categories.2.id', $later->id));
    }

    public function test_empty_book_has_no_categories(): void
    {
        $book = Book::factory()->create();

        $this->actingAs($book->user)->get(route('books.categories.index', $book))
            ->assertInertia(fn (Assert $page) => $page->component('categories/index')->has('categories', 0));
    }

    public function test_category_creation_saves_parent_type_and_order_and_shows_feedback(): void
    {
        $book = Book::factory()->create();
        $parent = Category::factory()->for($book)->create();

        $this->actingAs($book->user)->post(route('books.categories.store', $book), [
            'name' => 'Rent', 'type' => 'expense', 'parent_id' => $parent->id, 'sort_order' => 3,
        ])->assertRedirect(route('books.categories.index', $book));

        $this->assertDatabaseHas('categories', ['book_id' => $book->id, 'name' => 'Rent', 'type' => 'expense', 'parent_id' => $parent->id, 'sort_order' => 3]);
        $this->get(route('books.categories.index', $book))
            ->assertInertia(fn (Assert $page) => $page->where('status', 'Category created.'));
    }

    public function test_category_can_be_renamed_retyped_reordered_and_moved_to_top_level(): void
    {
        $book = Book::factory()->create();
        $parent = Category::factory()->for($book)->create();
        $category = Category::factory()->for($book)->create(['parent_id' => $parent->id]);

        $this->actingAs($book->user)->put(route('books.categories.update', [$book, $category]), [
            'name' => 'Salary', 'type' => 'income', 'parent_id' => '', 'sort_order' => 2,
        ])->assertRedirect(route('books.categories.index', $book))->assertSessionHas('status', 'Category updated.');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Salary', 'type' => 'income', 'parent_id' => null, 'sort_order' => 2]);
    }

    public function test_category_can_be_moved_under_an_unrelated_parent(): void
    {
        $book = Book::factory()->create();
        $category = Category::factory()->for($book)->create();
        $parent = Category::factory()->for($book)->create();

        $this->actingAs($book->user)->put(route('books.categories.update', [$book, $category]), [
            'name' => 'Moved', 'type' => 'expense', 'parent_id' => $parent->id, 'sort_order' => 0,
        ])->assertRedirect(route('books.categories.index', $book))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'parent_id' => $parent->id]);
    }

    public function test_invalid_category_fields_are_rejected_without_changing_the_category(): void
    {
        $book = Book::factory()->create();
        $category = Category::factory()->for($book)->create(['name' => 'Original', 'type' => 'expense', 'sort_order' => 0]);
        $data = ['name' => '', 'type' => 'invalid', 'parent_id' => null, 'sort_order' => -1];

        $this->actingAs($book->user)->post(route('books.categories.store', $book), $data)
            ->assertSessionHasErrors(['name', 'type', 'sort_order']);
        $this->put(route('books.categories.update', [$book, $category]), $data)
            ->assertSessionHasErrors(['name', 'type', 'sort_order']);

        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Original', 'type' => 'expense', 'sort_order' => 0]);
    }

    public function test_a_category_cannot_be_its_own_parent_or_a_descendants_child(): void
    {
        $book = Book::factory()->create();
        $root = Category::factory()->for($book)->create();
        $child = Category::factory()->for($book)->create(['parent_id' => $root->id]);
        $grandchild = Category::factory()->for($book)->create(['parent_id' => $child->id]);
        $data = ['name' => $root->name, 'type' => 'expense', 'sort_order' => 0];

        $this->actingAs($book->user)->put(route('books.categories.update', [$book, $root]), [...$data, 'parent_id' => $root->id])
            ->assertSessionHasErrors('parent_id');
        $this->put(route('books.categories.update', [$book, $root]), [...$data, 'parent_id' => $grandchild->id])
            ->assertSessionHasErrors(['parent_id' => 'Choose a parent that does not create a category cycle.']);

        $this->assertDatabaseHas('categories', ['id' => $root->id, 'parent_id' => null]);
        $this->assertDatabaseHas('categories', ['id' => $grandchild->id, 'parent_id' => $child->id]);
    }

    public function test_category_update_rejects_a_parent_from_another_owned_book(): void
    {
        $book = Book::factory()->create();
        $otherBook = Book::factory()->for($book->user)->create();
        $category = Category::factory()->for($book)->create();
        $parent = Category::factory()->for($otherBook)->create();

        $this->actingAs($book->user)->put(route('books.categories.update', [$book, $category]), [
            'name' => 'Invalid parent', 'type' => 'expense', 'parent_id' => $parent->id, 'sort_order' => 0,
        ])->assertSessionHasErrors('parent_id');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'parent_id' => null]);
    }

    public function test_category_with_allocations_cannot_be_deleted(): void
    {
        $book = Book::factory()->create();
        $category = Category::factory()->for($book)->create();
        $period = BudgetPeriod::factory()->for($book)->create();
        $allocation = BudgetAllocation::factory()->for($period)->for($category)->create();

        $this->actingAs($book->user)->from(route('books.categories.index', $book))
            ->delete(route('books.categories.destroy', [$book, $category]))
            ->assertRedirect(route('books.categories.index', $book))
            ->assertSessionHasErrors(['delete' => 'Remove this category’s budget allocations before deleting it.']);

        $this->assertModelExists($category);
        $this->assertModelExists($allocation);
    }

    public function test_deleting_a_category_keeps_children_and_uncategorizes_transactions(): void
    {
        $book = Book::factory()->create();
        $category = Category::factory()->for($book)->create();
        $child = Category::factory()->for($book)->create(['parent_id' => $category->id]);
        $account = Account::factory()->for($book)->create();
        $transaction = Transaction::factory()->for($book)->for($account)->for($category)->create(['amount_minor' => -12345]);

        $this->actingAs($book->user)->delete(route('books.categories.destroy', [$book, $category]))
            ->assertRedirect(route('books.categories.index', $book))->assertSessionHas('status', 'Category deleted.');

        $this->assertModelMissing($category);
        $this->assertDatabaseHas('categories', ['id' => $child->id, 'parent_id' => null]);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'category_id' => null, 'amount_minor' => -12345, 'account_id' => $account->id]);
    }

    public function test_category_mutations_reject_a_category_from_another_owned_book(): void
    {
        $book = Book::factory()->create();
        $otherBook = Book::factory()->for($book->user)->create();
        $category = Category::factory()->for($otherBook)->create(['name' => 'Unchanged']);

        $this->actingAs($book->user)->put(route('books.categories.update', [$book, $category]), [
            'name' => 'Wrong book', 'type' => 'expense', 'parent_id' => null, 'sort_order' => 0,
        ])->assertNotFound();
        $this->delete(route('books.categories.destroy', [$book, $category]))->assertNotFound();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Unchanged']);
    }

    public function test_users_cannot_view_or_modify_another_users_categories(): void
    {
        $book = Book::factory()->create();
        $category = Category::factory()->for($book)->create(['name' => 'Private']);
        $user = User::factory()->create();
        $data = ['name' => 'Unauthorized', 'type' => 'expense', 'parent_id' => null, 'sort_order' => 0];

        $this->actingAs($user)->get(route('books.categories.index', $book))->assertForbidden();
        $this->post(route('books.categories.store', $book), $data)->assertForbidden();
        $this->put(route('books.categories.update', [$book, $category]), $data)->assertForbidden();
        $this->delete(route('books.categories.destroy', [$book, $category]))->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Private']);
        $this->assertDatabaseMissing('categories', ['name' => 'Unauthorized']);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $book = Book::factory()->create();

        $this->get(route('books.categories.index', $book))->assertRedirect(route('login'));
    }
}
