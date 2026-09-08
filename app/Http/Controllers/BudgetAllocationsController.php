<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BudgetAllocations\StoreBudgetAllocationRequest;
use App\Http\Requests\BudgetAllocations\UpdateBudgetAllocationRequest;
use App\Models\Book;
use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BudgetAllocationsController extends Controller
{
    public function index(Request $request, Book $book, BudgetPeriod $budgetPeriod): Response
    {
        Gate::authorize('view', $budgetPeriod);

        return Inertia::render('budget-allocations/index', [
            'status' => $request->session()->get('status'),
            'book' => $book,
            'period' => $budgetPeriod,
            'allocations' => $budgetPeriod->allocations()->with('category')->get(),
            'categories' => $book->categories()->with('parent')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreBudgetAllocationRequest $request, Book $book, BudgetPeriod $budgetPeriod): RedirectResponse
    {
        $budgetPeriod->allocations()->create($request->validated());

        return to_route('books.budget-periods.allocations.index', [$book, $budgetPeriod])
            ->with('status', 'Budget allocation created.');
    }

    public function update(
        UpdateBudgetAllocationRequest $request,
        Book $book,
        BudgetPeriod $budgetPeriod,
        BudgetAllocation $allocation,
    ): RedirectResponse {
        Gate::authorize('update', $allocation);
        $allocation->update($request->validated());

        return to_route('books.budget-periods.allocations.index', [$book, $budgetPeriod])
            ->with('status', 'Budget allocation updated.');
    }

    public function destroy(
        Request $request,
        Book $book,
        BudgetPeriod $budgetPeriod,
        BudgetAllocation $allocation,
    ): RedirectResponse {
        Gate::authorize('delete', $allocation);
        $allocation->delete();

        return to_route('books.budget-periods.allocations.index', [$book, $budgetPeriod])
            ->with('status', 'Budget allocation deleted.');
    }
}
