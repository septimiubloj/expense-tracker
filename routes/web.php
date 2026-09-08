<?php

declare(strict_types=1);

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\BooksController;
use App\Http\Controllers\BudgetAllocationsController;
use App\Http\Controllers\BudgetPeriodsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\TransfersController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('books', BooksController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('books.transfers', TransfersController::class)->only(['store', 'update', 'destroy']);

    Route::scopeBindings()->group(function () {
        Route::resource('books.accounts', AccountsController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('books.categories', CategoriesController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('books.budget-periods', BudgetPeriodsController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('books.budget-periods.allocations', BudgetAllocationsController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['allocations' => 'allocation']);
        Route::resource('books.transactions', TransactionsController::class)->only(['index', 'store', 'update', 'destroy']);
    });
});

require __DIR__.'/settings.php';
