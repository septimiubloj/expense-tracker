<?php

declare(strict_types=1);

namespace App\Http\Requests\Transfers;

use App\Actions\Money;
use App\Enums\TransactionStatus;
use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $book = $this->route('book');

        return $book instanceof Book && $this->user()?->can('update', $book) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'source_account_id' => ['required', 'integer'],
            'destination_account_id' => ['required', 'integer', 'different:source_account_id'],
            'amount_minor' => ['required', 'integer', 'min:1', 'max:'.Money::MaxMinor],
            'occurred_on' => ['required', 'date_format:Y-m-d'],
            'status' => ['required', Rule::enum(TransactionStatus::class)],
            'memo' => ['nullable', 'string', 'max:65535'],
        ];
    }
}
