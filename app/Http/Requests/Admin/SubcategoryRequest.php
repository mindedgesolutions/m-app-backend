<?php

namespace App\Http\Requests\Admin;

use App\Models\SubCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SubcategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoryId' => ['required', 'exists:categories,id'],
            'name' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                if (SubCategory::where('slug', $inputSlug)
                    ->when($this->id, function ($query) {
                        $query->where('id', '!=', $this->id);
                    })->exists()
                ) {
                    $fail('Sub-category exists');
                }
            }],
            'description' => ['nullable', 'max:255']
        ];
    }
}
