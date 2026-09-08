<?php

declare(strict_types=1);

namespace App\Http\Requests\BudgetAllocations;

use App\Actions\Money;
use App\Models\BudgetAllocation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBudgetAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $allocation = $this->route('allocation');

        return $allocation instanceof BudgetAllocation && $this->user()?->can('update', $allocation) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $allocation = $this->route('allocation');

        if (! $allocation instanceof BudgetAllocation) {
            return [];
        }

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('book_id', $allocation->budgetPeriod->book_id),
                Rule::unique('budget_allocations', 'category_id')
                    ->where('budget_period_id', $allocation->budget_period_id)
                    ->ignore($allocation),
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
