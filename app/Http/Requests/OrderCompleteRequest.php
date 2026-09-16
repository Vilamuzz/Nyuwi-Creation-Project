<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderCompleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'reviews' => ['required', 'array', 'min:1'],
            'reviews.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'reviews.*.rating' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
