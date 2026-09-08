<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Models\Account;
use App\Models\Book;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
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
            'account_id' => Account::factory(),
            'category_id' => null,
            'occurred_on' => fake()->dateTimeBetween('-1 year', 'today')->format('Y-m-d'),
            'payee' => fake()->company(),
            'reference' => fake()->optional()->bothify('REF-####'),
            'memo' => fake()->optional()->sentence(),
            'amount_minor' => fake()->numberBetween(-100_000, 100_000) ?: 1,
            'status' => TransactionStatus::Cleared->value,
            'transfer_group_id' => null,
        ];
    }
}
