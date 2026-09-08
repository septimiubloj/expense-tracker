<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CategoriesController extends Controller
{
    public function index(Request $request, Book $book): Response
    {
        Gate::authorize('view', $book);

        return Inertia::render('categories/index', [
            'status' => $request->session()->get('status'),
            'book' => $book,
            'categories' => $book->categories()->with('parent')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreCategoryRequest $request, Book $book): RedirectResponse
    {
        $book->categories()->create($request->validated());

        return to_route('books.categories.index', $book)->with('status', 'Category created.');
    }

    public function update(UpdateCategoryRequest $request, Book $book, Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);
        $category->update($request->validated());

        return to_route('books.categories.index', $book)->with('status', 'Category updated.');
    }

    public function destroy(Request $request, Book $book, Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        if ($category->budgetAllocations()->exists()) {
            return back()->withErrors(['delete' => 'Remove this category’s budget allocations before deleting it.']);
        }

        $category->delete();

        return to_route('books.categories.index', $book)->with('status', 'Category deleted.');
    }
}
