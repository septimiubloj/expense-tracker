<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Books\StoreBookRequest;
use App\Http\Requests\Books\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BooksController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Book::class);

        return Inertia::render('books/index', [
            'status' => $request->session()->get('status'),
            'books' => $request->user()->books()->latest('id')->get(),
        ]);
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $book = $request->user()->books()->create($request->validated());

        return to_route('books.index')->with('status', "Book {$book->name} created.");
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        Gate::authorize('update', $book);
        $book->update($request->validated());

        return to_route('books.index')->with('status', "Book {$book->name} updated.");
    }

    public function destroy(Request $request, Book $book): RedirectResponse
    {
        Gate::authorize('delete', $book);
        if ($book->accounts()->exists()) {
            return back()->withErrors(['delete' => 'Delete the accounts in this book first.']);
        }

        $book->delete();

        return to_route('books.index')->with('status', 'Book deleted.');
    }
}
