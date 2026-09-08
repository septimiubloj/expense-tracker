<?php

declare(strict_types=1);

namespace App\Http\Requests\BudgetPeriods;

use App\Concerns\ValidatesBudgetPeriodDates;
use App\Enums\BudgetPeriodStatus;
use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetPeriodRequest extends FormRequest
{
    use ValidatesBudgetPeriodDates;

    public function authorize(): bool
    {
        return $this->route('book') instanceof Book && $this->user()?->can('view', $this->route('book')) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'status' => ['required', Rule::enum(BudgetPeriodStatus::class)],
        ];
    }
}
