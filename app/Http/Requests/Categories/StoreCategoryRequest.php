<?php

namespace App\Http\Requests\Categories;

use App\Enums\CategoryType;
use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('book') instanceof Book && $this->user()?->can('view', $this->route('book')) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $book = $this->route('book');

        if (! $book instanceof Book) {
            return [];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(CategoryType::class)],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('book_id', $book->id),
            ],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
