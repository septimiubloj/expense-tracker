<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Transactions/Index', [
            'transactions' => Transaction::with(['account', 'book', 'category'])->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Transactions/Create', [
            'accounts' => Auth::user()->accounts()
                ->orderBy('title')
                ->get(['id', 'title']),
            'books' => Auth::user()->books()
                ->orderBy('title')
                ->get(['id', 'title']),
            'categories' => Category::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $transaction = new Transaction;
        $transaction->fill($request->all());
        $transaction->save();

        return Redirect::route('transactions.index')->with('success', 'Transaction saved');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Inertia\Response
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Inertia\Response
     */
    public function edit(Transaction $transaction)
    {
        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            'accounts' => Auth::user()->accounts()
                ->orderBy('title')
                ->get(['id', 'title']),
            'books' => Auth::user()->books()
                ->orderBy('title')
                ->get(['id', 'title']),
            'categories' => Category::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Transaction $transaction)
    {
        $transaction->update($request->all());

        return Redirect::route('transactions.index')->with('success', 'Transaction details updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return Redirect::route('transactions.index')->with('success', 'Transaction deleted');
    }
}
