<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "recipient_name",
        "address",
        "province_id",
        "province_name",
        "city_id",
        "city_name",
        "district_id",
        "district_name",
        "postal_code",
        "phone",
        "email",
        "courier",
        "shipping_service",
        "weight",
        "shipping_cost",
        "tracking_number",
        "subtotal",
        "total_price",
        "payment_status",
        "status",
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "weight" => "integer",
            "shipping_cost" => "decimal:2",
            "subtotal" => "decimal:2",
            "total_price" => "decimal:2",
        ];
    }

    #[Override]
    protected static function booted()
    {
        static::creating(
            fn($order) => ($order->order_number ??=
                "ORD-" .
                now()->format("Ymd") .
                "-" .
                str_pad(
                    static::whereDate("created_at", today())->count() + 1,
                    4,
                    "0",
                    STR_PAD_LEFT,
                )),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
