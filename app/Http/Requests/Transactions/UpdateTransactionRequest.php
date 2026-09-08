<?php

declare(strict_types=1);

namespace App\Http\Requests\Transactions;

use App\Actions\Money;
use App\Concerns\ValidatesLedgerTransaction;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    use ValidatesLedgerTransaction;

    public function authorize(): bool
    {
        $transaction = $this->route('transaction');

        return $transaction instanceof Transaction && $this->user()?->can('update', $transaction) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $transaction = $this->route('transaction');

        if (! $transaction instanceof Transaction) {
            return [];
        }

        return [
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')->where('book_id', $transaction->book_id)],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('book_id', $transaction->book_id)],
            'occurred_on' => ['required', 'date_format:Y-m-d'],
            'payee' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'memo' => ['nullable', 'string', 'max:65535'],
            'amount_minor' => ['required', 'integer', 'not_in:0', 'between:'.-Money::MaxMinor.','.Money::MaxMinor],
            'transfer_group_id' => ['prohibited'],
            'status' => ['required', Rule::enum(TransactionStatus::class)],
        ];
    }
}
