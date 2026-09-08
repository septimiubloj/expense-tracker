<?php

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
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
            'parent_id' => null,
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(CategoryType::cases())->value,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
