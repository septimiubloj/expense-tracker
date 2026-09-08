<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Accounts\StoreAccountRequest;
use App\Http\Requests\Accounts\UpdateAccountRequest;
use App\Models\Account;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AccountsController extends Controller
{
    public function index(Request $request, Book $book): Response
    {
        Gate::authorize('view', $book);

        return Inertia::render('accounts/index', [
            'status' => $request->session()->get('status'),
            'book' => $book,
            'accounts' => $book->accounts()->latest('id')->get(),
        ]);
    }

    public function store(StoreAccountRequest $request, Book $book): RedirectResponse
    {
        $book->accounts()->create($request->validated());

        return to_route('books.accounts.index', $book)->with('status', 'Account created.');
    }

    public function update(UpdateAccountRequest $request, Book $book, Account $account): RedirectResponse
    {
        Gate::authorize('update', $account);
        $account->update($request->validated());

        return to_route('books.accounts.index', $book)->with('status', 'Account updated.');
    }

    public function destroy(Request $request, Book $book, Account $account): RedirectResponse
    {
        Gate::authorize('delete', $account);
        if ($account->transactions()->exists()) {
            return back()->withErrors(['delete' => 'Accounts with transactions cannot be deleted.']);
        }

        $account->delete();

        return to_route('books.accounts.index', $book)->with('status', 'Account deleted.');
    }
}
