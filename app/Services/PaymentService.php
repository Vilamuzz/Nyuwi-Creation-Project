<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
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

        // idempotency: reuse a pending transaction if one already exists for this order
        $transaction = $order
            ->paymentTransactions()
            ->where("status", "pending")
            ->latest()
            ->first();

        if (!$transaction) {
            $transaction = $order->paymentTransactions()->create([
                "gateway" => "midtrans",
                "amount" => $order->total_price,
                "status" => "pending",
            ]);
        }

        $grossAmount = (int) round((float) $order->total_price);
        $orderCustomId = "ORD-{$order->id}-{$transaction->id}"; // stable, tied to a real DB row

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
                "first_name" => $order->recipient_name,
                "phone" => $order->phone,
                "email" => $order->user?->email,
                "shipping_address" => [
                    "first_name" => $order->recipient_name,
                    "phone" => $order->phone,
                    "address" => $order->address,
                    "city" => $order->city_name,
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

        return [
            "token" => $result->token,
            "redirect_url" => $result->redirect_url ?? null,
            "order_custom_id" => $orderCustomId,
            "transaction_id" => $transaction->id,
        ];
    }
}
