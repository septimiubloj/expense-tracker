<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounts;

use App\Actions\Money;
use App\Enums\AccountType;
use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('book') instanceof Book && $this->user()?->can('view', $this->route('book')) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $book = $this->route('book');

        if (! $book instanceof Book) {
            return [];
        }

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('accounts', 'name')->where('book_id', $book->id)],
            'type' => ['required', Rule::enum(AccountType::class)],
            'opening_balance_minor' => ['required', 'integer', 'between:'.-Money::MaxMinor.','.Money::MaxMinor],
            'opened_on' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
