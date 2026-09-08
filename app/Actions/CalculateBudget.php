<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Models\Book;
use App\Models\BudgetPeriod;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class CalculateBudget
{
    /**
     * @return array{income_minor: int, expenses_minor: int, net_minor: int, uncategorized_minor: int,
     *     accounts: array<int, array{previous_minor: int, current_minor: int}>,
     *     categories: array<int, array{planned_minor: int, actual_minor: int, remaining_minor: int, variance_minor: int}>}
     */
    public function handle(User $user, Book $book, BudgetPeriod $period): array
    {
        Gate::forUser($user)->authorize('view', $book);
        $period = $book->budgetPeriods()->findOrFail($period->id);
        $start = $period->starts_on->toDateString();
        $end = $period->ends_on->toDateString();
        $accounts = [];

        foreach ($book->accounts()->get() as $account) {
            $opened = $account->opened_on->toDateString();
            $accounts[$account->id] = [
                'previous_minor' => $opened < $start ? $account->opening_balance_minor : 0,
                'current_minor' => $opened <= $end ? $account->opening_balance_minor : 0,
            ];
        }

        $categories = [];
        $types = [];
        $allocations = $period->allocations()->pluck('planned_amount_minor', 'category_id');

        foreach ($book->categories()->get() as $category) {
            $planned = (int) ($allocations[$category->id] ?? 0);
            $types[$category->id] = $category->type;
            $categories[$category->id] = ['planned_minor' => $planned, 'actual_minor' => 0,
                'remaining_minor' => $planned, 'variance_minor' => 0];
        }

        $income = 0;
        $expenses = 0;
        $uncategorized = 0;

        foreach ($book->transactions()->where('status', '!=', TransactionStatus::Void)
            ->whereDate('occurred_on', '<=', $end)->orderBy('id')->cursor() as $transaction) {
            $amount = $transaction->amount_minor;
            $accountId = $transaction->account_id;
            if (! isset($accounts[$accountId])) {
                throw new \LogicException('A transaction references an account outside its book.');
            }

            $accounts[$accountId]['current_minor'] = Money::add($accounts[$accountId]['current_minor'], $amount);

            if ($transaction->occurred_on->toDateString() < $start) {
                $accounts[$accountId]['previous_minor'] = Money::add($accounts[$accountId]['previous_minor'], $amount);

                continue;
            }

            if ($transaction->transfer_group_id !== null) {
                continue;
            }

            $categoryId = $transaction->category_id;

            if ($categoryId === null) {
                $uncategorized = Money::add($uncategorized, $amount);

                continue;
            }

            if (! isset($categories[$categoryId])) {
                throw new \LogicException('A transaction references a category outside its book.');
            }

            $actual = $types[$categoryId] === CategoryType::Expense ? -$amount : $amount;
            $categories[$categoryId]['actual_minor'] = Money::add($categories[$categoryId]['actual_minor'], $actual);

            if ($types[$categoryId] === CategoryType::Income) {
                $income = Money::add($income, $actual);

                continue;
            }

            $expenses = Money::add($expenses, $actual);
        }

        foreach ($categories as $id => &$category) {
            $category['remaining_minor'] = Money::add($category['planned_minor'], -$category['actual_minor']);
            $category['variance_minor'] = $types[$id] === CategoryType::Expense
                ? $category['remaining_minor'] : -$category['remaining_minor'];
        }

        return ['income_minor' => $income, 'expenses_minor' => $expenses,
            'net_minor' => Money::add($income, -$expenses), 'uncategorized_minor' => $uncategorized,
            'accounts' => $accounts, 'categories' => $categories];
    }
}
