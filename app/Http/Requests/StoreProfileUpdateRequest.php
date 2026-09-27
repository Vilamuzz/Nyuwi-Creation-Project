<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'shipping_origin_city_id' => ['required', 'string', 'max:50'],
            'shipping_origin_district_id' => ['required', 'string', 'max:50'],
            'shipping_couriers' => ['nullable', 'array'],
            'shipping_couriers.*' => ['string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'tiktok' => ['nullable', 'string', 'max:255'],
            'logo' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
            'qris' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
        ];
    }
}
