<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\Book;
use Closure;
use Illuminate\Validation\Validator;

trait ValidatesLedgerTransaction
{
    /** @return array<int, Closure(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $book = $this->route('book');

            if (! $book instanceof Book || $validator->errors()->isNotEmpty()) {
                return;
            }

            $account = $book->accounts()->find($this->integer('account_id'));

            if ($account !== null && $account->opened_on->toDateString() > $this->input('occurred_on')) {
                $validator->errors()->add('occurred_on', 'The transaction cannot predate its account.');
            }
        }];
    }
}
