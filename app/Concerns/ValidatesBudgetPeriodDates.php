<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\Book;
use App\Models\BudgetPeriod;
use Closure;
use Illuminate\Validation\Validator;

trait ValidatesBudgetPeriodDates
{
    /** @return array<int, Closure(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $book = $this->route('book');

            if (! $book instanceof Book || $validator->errors()->isNotEmpty()) {
                return;
            }

            $period = $this->route('budget_period');
            $duplicates = $book
                ->budgetPeriods()
                ->whereDate('starts_on', $this->input('starts_on'))
                ->whereDate('ends_on', $this->input('ends_on'));

            if ($period instanceof BudgetPeriod) {
                $duplicates->whereKeyNot($period->id);
            }

            if ($duplicates->exists()) {
                $validator->errors()->add('ends_on', 'A budget period with these dates already exists in this book.');
            }
        }];
    }
}
