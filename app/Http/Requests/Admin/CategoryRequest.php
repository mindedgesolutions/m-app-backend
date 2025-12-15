<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                if (Category::where('slug', $inputSlug)
                    ->when($this->route('category'), function ($query) {
                        $query->where('id', '!=', $this->route('category'));
                    })
                    ->exists()
                ) {
                    $fail('Category exists');
                }
            }],
            'description' => ['nullable', 'max:255']
        ];
    }
}
