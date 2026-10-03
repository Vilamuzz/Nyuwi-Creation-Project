<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;
use RuntimeException;

class PaymentService
{
    public function __construct()
    {
        Config::$serverKey = config("services.midtrans.server_key");
        Config::$isProduction = config("services.midtrans.is_production");
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createSnapToken(Order $order): array
    {
        $order->loadMissing("orderItems.product");

        $existing = $order
            ->paymentTransactions()
            ->where("status", "pending")
            ->whereNotNull("snap_token")
            ->latest()
            ->first();

        $isTokenFresh = $existing &&
            (!$existing->expired_at || $existing->expired_at->isFuture()) &&
            $existing->created_at->gt(now()->subHours(24));

        if ($isTokenFresh) {
            return [
                "token" => $existing->snap_token,
                "redirect_url" => $existing->snap_redirect_url,
                "order_custom_id" => $existing->gateway_order_id,
                "transaction_id" => $existing->id,
            ];
        }

        $transaction = $order->paymentTransactions()->create([
            "gateway" => "midtrans",
            "amount" => $order->total_price,
            "net_amount" => $order->total_price,
            "status" => "pending",
        ]);

        $grossAmount = (int) round((float) $order->total_price);
        // Use timestamp/uniqid in order_id sent to Midtrans so retries don't fail with 400 'order_id already used'
        $orderCustomId = "ORD-{$order->id}-{$transaction->id}-" . time();

        $itemDetails = [];
        foreach ($order->orderItems as $item) {
            $itemDetails[] = [
                "id" => (string) $item->product_id,
                "price" => (int) round((float) $item->price),
                "quantity" => (int) $item->quantity,
                "name" => mb_substr($item->product?->name ?? "Produk", 0, 50),
            ];
        }

        $itemsTotal = array_reduce(
            $itemDetails,
            fn($carry, $i) => $carry + $i["price"] * $i["quantity"],
            0,
        );
        $diff = $grossAmount - $itemsTotal;

        if ($diff > 0) {
            $itemDetails[] = [
                "id" => "SHIPPING",
                "price" => $diff,
                "quantity" => 1,
                "name" => mb_substr(
                    "Ongkos Kirim (" .
                    ($order->shipping_service ?? "Ekspedisi") .
                    ")",
                    0,
                    50,
                ),
            ];
        } elseif ($diff < 0) {
            // discount, rounding overshoot, etc — item_details MUST sum to gross_amount
            $itemDetails[] = [
                "id" => "ADJUSTMENT",
                "price" => $diff, // negative
                "quantity" => 1,
                "name" => "Penyesuaian",
            ];
        }

        $payload = [
            "transaction_details" => [
                "order_id" => $orderCustomId,
                "gross_amount" => $grossAmount,
            ],
            "customer_details" => [
                "first_name" => $order->recipient_name ?: "Pelanggan",
                "phone" => $order->phone ?: "08123456789",
                "email" => $order->email ?: ($order->user?->email ?: "customer@nyuwi.com"),
                "shipping_address" => [
                    "first_name" => $order->recipient_name ?: "Pelanggan",
                    "phone" => $order->phone ?: "08123456789",
                    "address" => $order->address ?: "-",
                    "city" => $order->city_name ?: "-",
                ],
            ],
            "item_details" => $itemDetails,
        ];

        try {
            $result = Snap::createTransaction($payload);
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Failed to create Midtrans Snap transaction: " .
                $e->getMessage(),
            );
        }

        if (empty($result->token)) {
            throw new RuntimeException("Midtrans response missing token.");
        }

        $transaction->update([
            "gateway_order_id" => $orderCustomId,
            "snap_token" => $result->token,
            "snap_redirect_url" => $result->redirect_url ?? null,
            "expired_at" => now()->addHours(24),
        ]);

        return [
            "token" => $result->token,
            "redirect_url" => $result->redirect_url ?? null,
            "order_custom_id" => $orderCustomId,
            "transaction_id" => $transaction->id,
        ];
    }
}
