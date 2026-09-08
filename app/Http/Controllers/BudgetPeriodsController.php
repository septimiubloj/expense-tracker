<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BudgetPeriods\StoreBudgetPeriodRequest;
use App\Http\Requests\BudgetPeriods\UpdateBudgetPeriodRequest;
use App\Models\Book;
use App\Models\BudgetPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BudgetPeriodsController extends Controller
{
    public function index(Request $request, Book $book): Response
    {
        Gate::authorize('view', $book);

        return Inertia::render('budget-periods/index', [
            'status' => $request->session()->get('status'),
            'book' => $book,
            'periods' => $book->budgetPeriods()->latest('starts_on')->get(),
        ]);
    }

    public function store(StoreBudgetPeriodRequest $request, Book $book): RedirectResponse
    {
        $book->budgetPeriods()->create($request->validated());

        return to_route('books.budget-periods.index', $book)->with('status', 'Budget period created.');
    }

    public function update(UpdateBudgetPeriodRequest $request, Book $book, BudgetPeriod $budgetPeriod): RedirectResponse
    {
        Gate::authorize('update', $budgetPeriod);
        $budgetPeriod->update($request->validated());

        return to_route('books.budget-periods.index', $book)->with('status', 'Budget period updated.');
    }

    public function destroy(Request $request, Book $book, BudgetPeriod $budgetPeriod): RedirectResponse
    {
        Gate::authorize('delete', $budgetPeriod);
        $budgetPeriod->delete();

        return to_route('books.budget-periods.index', $book)->with('status', 'Budget period deleted.');
    }
}
