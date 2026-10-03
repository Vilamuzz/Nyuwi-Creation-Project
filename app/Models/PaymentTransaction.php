<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'gateway',
        'gateway_order_id',
        'gateway_transaction_id',
        'snap_token',
        'snap_redirect_url',
        'payment_channel',
        'amount',
        'gateway_fee',
        'fee_borne_by',
        'net_amount',
        'status',
        'paid_at',
        'expired_at',
        'raw_response',
        'signature_verified',
    ];

    protected $attributes = [
        'gateway' => 'midtrans',
        'gateway_fee' => 0,
        'fee_borne_by' => 'merchant',
        'net_amount' => 0,
        'status' => 'pending',
        'signature_verified' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'signature_verified' => 'boolean',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'raw_response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
