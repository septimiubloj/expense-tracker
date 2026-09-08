<?php

declare(strict_types=1);

namespace App\Http\Requests\BudgetAllocations;

use App\Actions\Money;
use App\Models\Book;
use App\Models\BudgetPeriod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $period = $this->route('budget_period');

        return $period instanceof BudgetPeriod && $this->user()?->can('view', $period) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $book = $this->route('book');
        $period = $this->route('budget_period');

        if (! $book instanceof Book || ! $period instanceof BudgetPeriod) {
            return [];
        }

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('book_id', $book->id),
                Rule::unique('budget_allocations', 'category_id')->where('budget_period_id', $period->id),
            ],
            'planned_amount_minor' => ['required', 'integer', 'min:0', 'max:'.Money::MaxMinor],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['category_id.unique' => 'This category already has an allocation in this period.'];
    }
}
