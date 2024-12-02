<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'city',
        'district',
        'village',
        'province',
        'phone',
        'total_price',
        'payment_method',
        'payment_proof',
        'note',
        'status',
        'shipping_method',
        'tracking_number'
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
