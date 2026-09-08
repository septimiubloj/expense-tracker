<?php

namespace App\Http\Requests\Books;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('books', 'name')->where('user_id', $this->user()->id)],
            'currency_code' => ['required', 'string', 'size:3', 'alpha', 'uppercase'],
            'timezone' => ['required', 'string', 'timezone:all'],
        ];
    }
}
