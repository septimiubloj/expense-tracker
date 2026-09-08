<?php

namespace Database\Factories;

use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetAllocation>
 */
class BudgetAllocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'budget_period_id' => BudgetPeriod::factory(),
            'category_id' => Category::factory(),
            'planned_amount_minor' => fake()->numberBetween(0, 500_000),
        ];
    }
}
