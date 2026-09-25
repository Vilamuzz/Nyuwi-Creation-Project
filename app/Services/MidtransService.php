<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransService
{
    protected string $serverKey;
    protected string $snapUrl;

    public function __construct(?string $serverKey = null, ?string $snapUrl = null)
    {
        $this->serverKey = $serverKey ?? (string) config('services.midtrans.server_key', '');
        $this->snapUrl = $snapUrl ?? (string) config('services.midtrans.snap_url', 'https://app.sandbox.midtrans.com/snap/v1/transactions');
    }

    /**
     * Create Midtrans Snap transaction token and redirect URL.
     *
     * @param Order $order
     * @return array{token: string, redirect_url: string, order_custom_id: string}
     * @throws RuntimeException
     */
    public function createSnapToken(Order $order): array
    {
        $order->loadMissing('orderItems.product');

        $grossAmount = (int) round((float) $order->total_price);
        $orderCustomId = 'ORD-' . $order->id . '-' . time();

        $itemDetails = [];
        foreach ($order->orderItems as $item) {
            $itemDetails[] = [
                'id' => (string) $item->product_id,
                'price' => (int) round((float) $item->price),
                'quantity' => (int) $item->quantity,
                'name' => mb_substr($item->product?->name ?? 'Produk', 0, 50),
            ];
        }

        // Calculate sum and add shipping cost if needed to match gross_amount
        $itemsTotal = array_reduce($itemDetails, fn ($carry, $i) => $carry + ($i['price'] * $i['quantity']), 0);
        $diff = $grossAmount - $itemsTotal;
        if ($diff > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => $diff,
                'quantity' => 1,
                'name' => mb_substr('Ongkos Kirim (' . ($order->shipping_method ?? 'Ekspedisi') . ')', 0, 50),
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $orderCustomId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $order->name,
                'email' => $order->email,
                'phone' => $order->phone,
                'shipping_address' => [
                    'first_name' => $order->name,
                    'phone' => $order->phone,
                    'address' => $order->address,
                    'city' => $order->city,
                ],
            ],
            'item_details' => $itemDetails,
        ];

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->asJson()
            ->post($this->snapUrl, $payload);

        if (!$response->successful()) {
            throw new RuntimeException('Failed to create Midtrans Snap transaction: ' . $response->body());
        }

        $token = $response->json('token');
        $redirectUrl = $response->json('redirect_url');

        if (!$token) {
            throw new RuntimeException('Midtrans response missing token: ' . $response->body());
        }

        return [
            'token' => $token,
            'redirect_url' => $redirectUrl,
            'order_custom_id' => $orderCustomId,
        ];
    }

    /**
     * Verify Midtrans notification SHA-512 signature.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        return hash_equals($expected, $signatureKey);
    }

    /**
     * Extract database Order ID from Midtrans custom order_id string.
     */
    public function extractOrderId(string $orderCustomId): ?int
    {
        if (preg_match('/^ORD-(\d+)(?:-\d+)?$/', $orderCustomId, $matches)) {
            return (int) $matches[1];
        }

        if (is_numeric($orderCustomId)) {
            return (int) $orderCustomId;
        }

        return null;
    }
}
