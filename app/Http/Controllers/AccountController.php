<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Accounts/Index', [
            'accounts' => Account::all()->map(function ($account) {
                return [
                    'id' => $account->id,
                    'title' => $account->title,
                    'amount' => $account->amount,
                    'currentAmount' => $account->currentAmount,
                    'currency' => $account->currency,
                    'edit_url' => URL::route('accounts.edit', $account),
                ];
            }),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Accounts/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $account = new Account;
        $account->fill($request->all());
        $account->user()->associate(Auth::user());
        $account->save();

        return Redirect::route('accounts.index')->with('success', 'Account created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Inertia\Response
     */
    public function edit(Account $account)
    {
        return Inertia::render('Accounts/Edit', [
            'account' => $account
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Account $account)
    {
        $account->update($request->all());

        return Redirect::route('accounts.index')->with('success', 'Account updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        //
    }
}
