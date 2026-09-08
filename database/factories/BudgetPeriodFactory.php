<?php

namespace Database\Factories;

use App\Enums\BudgetPeriodStatus;
use App\Models\Book;
use App\Models\BudgetPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetPeriod>
 */
class BudgetPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'name' => now()->format('F Y'),
            'starts_on' => now()->startOfMonth()->toDateString(),
            'ends_on' => now()->endOfMonth()->toDateString(),
            'status' => BudgetPeriodStatus::Open->value,
        ];
    }
}
