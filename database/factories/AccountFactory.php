<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
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
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(AccountType::cases())->value,
            'opening_balance_minor' => fake()->numberBetween(-100_000, 1_000_000),
            'opened_on' => fake()->dateTimeBetween('-2 years', 'today')->format('Y-m-d'),
        ];
    }
}
