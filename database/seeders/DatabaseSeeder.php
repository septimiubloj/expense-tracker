<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//         \App\Models\User::factory(10)->create();
        Category::truncate();
        Book::truncate();

        $book = Book::create([
            'title' => 'Personal',
            'currency' => 'RON',
            'user_id' => 1
        ]);

        Category::create([
            'title' => 'Groceries',
            'type' => 'expense',
            'budget' => 0,
            'book_id' => $book->id
        ]);

        Category::create([
            'title' => 'Travel',
            'type' => 'expense',
            'budget' => 0,
            'book_id' => $book->id
        ]);

        Category::create([
            'title' => 'Refund',
            'type' => 'income',
            'budget' => 0,
            'book_id' => $book->id
        ]);
    }
}
