<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounts;

use App\Actions\Money;
use App\Enums\AccountType;
use App\Models\Account;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAccountRequest extends FormRequest
{
    /** @return array<int, Closure(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $account = $this->route('account');

            if (! $account instanceof Account || $validator->errors()->isNotEmpty()) {
                return;
            }

            if ($account->transactions()->whereDate('occurred_on', '<', $this->input('opened_on'))->exists()) {
                $validator->errors()->add('opened_on', 'The opening date cannot follow an existing transaction.');
            }
        }];
    }

    public function authorize(): bool
    {
        $account = $this->route('account');

        return $account instanceof Account && $this->user()?->can('update', $account) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $account = $this->route('account');

        if (! $account instanceof Account) {
            return [];
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('accounts', 'name')->where('book_id', $account->book_id)->ignore($account),
            ],
            'type' => ['required', Rule::enum(AccountType::class)],
            'opening_balance_minor' => ['required', 'integer', 'between:'.-Money::MaxMinor.','.Money::MaxMinor],
            'opened_on' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
