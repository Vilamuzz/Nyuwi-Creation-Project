<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileStore extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'address',
        'embedded_map',
        'district_id',
        'district_name',
        'phone',
        'shipping_couriers',
        'instagram',
        'facebook',
        'tiktok',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shipping_couriers' => 'array',
        ];
    }

    /**
     * Get enabled couriers or fallback defaults.
     *
     * @return array<int, string>
     */
    public function getEnabledCouriers(): array
    {
        return ! empty($this->shipping_couriers) && is_array($this->shipping_couriers)
            ? array_values($this->shipping_couriers)
            : ['jne', 'pos', 'tiki'];
    }
}
