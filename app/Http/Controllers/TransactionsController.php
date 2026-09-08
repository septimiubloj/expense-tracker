<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Models\Book;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TransactionsController extends Controller
{
    public function index(Request $request, Book $book): Response
    {
        Gate::authorize('view', $book);

        return Inertia::render('transactions/index', [
            'status' => $request->session()->get('status'),
            'book' => $book,
            'accounts' => $book->accounts()->orderBy('name')->get(),
            'categories' => $book->categories()->with('parent')->orderBy('sort_order')->orderBy('name')->get(),
            'transactions' => $book->transactions()->with(['account', 'category'])->latest('occurred_on')->latest('id')->get(),
        ]);
    }

    public function store(StoreTransactionRequest $request, Book $book): RedirectResponse
    {
        $book->transactions()->create($request->validated());

        return to_route('books.transactions.index', $book)->with('status', 'Transaction created.');
    }

    public function update(UpdateTransactionRequest $request, Book $book, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('update', $transaction);
        if ($transaction->transfer_group_id !== null) {
            throw ValidationException::withMessages(['transfer' => 'Edit the complete transfer using its transfer group.']);
        }

        $transaction->update($request->validated());

        return to_route('books.transactions.index', $book)->with('status', 'Transaction updated.');
    }

    public function destroy(Request $request, Book $book, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('delete', $transaction);
        if ($transaction->transfer_group_id !== null) {
            throw ValidationException::withMessages(['transfer' => 'Delete the complete transfer using its transfer group.']);
        }

        $transaction->delete();

        return to_route('books.transactions.index', $book)->with('status', 'Transaction deleted.');
    }
}
