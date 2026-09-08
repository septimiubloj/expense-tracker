<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use App\Enums\CategoryType;
use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    /** @return array<int, Closure(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $category = $this->route('category');

            if (! $category instanceof Category || $validator->errors()->isNotEmpty() || ! $this->filled('parent_id')) {
                return;
            }

            $parents = $category->book->categories()->pluck('parent_id', 'id');
            $parentId = $this->integer('parent_id');
            $visited = [$category->id => true];

            while ($parentId !== 0) {
                if (isset($visited[$parentId])) {
                    $validator->errors()->add('parent_id', 'Choose a parent that does not create a category cycle.');

                    return;
                }

                $visited[$parentId] = true;
                $parentId = (int) $parents->get($parentId);
            }
        }];
    }

    public function authorize(): bool
    {
        $category = $this->route('category');

        return $category instanceof Category && $this->user()?->can('update', $category) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = $this->route('category');

        if (! $category instanceof Category) {
            return [];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(CategoryType::class)],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('book_id', $category->book_id)
                    ->whereNot('id', $category->id),
            ],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
