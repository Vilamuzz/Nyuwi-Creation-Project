<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrderStoreRequest extends FormRequest
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
            'recipient_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'province_id' => ['required', 'string', 'max:255'],
            'province_name' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'string', 'max:255'],
            'city_name' => ['required', 'string', 'max:255'],
            'district_id' => ['required', 'string', 'max:255'],
            'district_name' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['required', 'string', 'max:15'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::requiredIf(! Auth::check())],
            'courier' => ['required', 'string', 'max:50'],
            'shipping_service' => ['required', 'string', 'max:100'],
        ];
    }
}
