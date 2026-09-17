<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:1',
            'price' => 'required|numeric|min:1',
            'weight' => 'required|numeric|min:1',
            'category_id' => 'nullable|integer|exists:categories,id',
            'new_category' => 'nullable|string|max:255',
            'description' => 'required|string',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'sizes' => 'nullable|array',
            'sizes.*' => 'string|max:50',
            'colors' => 'nullable|array',
            'colors.*' => 'string|max:50',
            'is_featured' => 'boolean',
        ];
    }
}
