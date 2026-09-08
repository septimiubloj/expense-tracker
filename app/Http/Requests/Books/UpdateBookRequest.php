<?php

namespace App\Http\Requests\Books;

use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $book = $this->route('book');

        return $book instanceof Book && $this->user()?->can('update', $book) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $book = $this->route('book');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('books', 'name')
                    ->where('user_id', $this->user()->id)
                    ->ignore($book),
            ],
            'currency_code' => ['required', 'string', 'size:3', 'alpha', 'uppercase'],
            'timezone' => ['required', 'string', 'timezone:all'],
        ];
    }
}
