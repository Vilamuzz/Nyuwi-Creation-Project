<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        if (!$this->isValidSignature($payload)) {
            Log::warning('Midtrans webhook: invalid signature', ['payload' => $payload]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Look up by gateway_order_id first, then fallback to parsing parts
        $midtransOrderId = $payload['order_id'] ?? null;
        $tx = PaymentTransaction::where('gateway_order_id', $midtransOrderId)->first();

        if (!$tx && $midtransOrderId) {
            $parts = explode('-', $midtransOrderId);
            if (count($parts) >= 4 && is_numeric($parts[2])) {
                $tx = PaymentTransaction::find($parts[2]);
            }
        }

        if (!$tx && $midtransOrderId) {
            $txId = Str::afterLast($midtransOrderId, '-');
            if (is_numeric($txId)) {
                $tx = PaymentTransaction::find($txId);
            }
        }

        if (!$tx && isset($parts[1]) && is_numeric($parts[1])) {
            $tx = PaymentTransaction::where('order_id', $parts[1])->latest()->first();
        }

        if (!$tx) {
            Log::error('Midtrans webhook: transaction not found', ['order_id' => $midtransOrderId]);
            // Still return 200 so Midtrans stops retrying a webhook we can never resolve.
            return response()->json(['message' => 'Transaction not found'], 200);
        }

        // Idempotency: already finalized, don't reprocess
        if (in_array($tx->status, ['settlement', 'deny', 'cancel', 'expire', 'refund'])) {
            return response()->json(['message' => 'Already processed']);
        }

        $status = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? null;

        // 'capture' (credit card) needs fraud_status check too; 'settlement' (VA/e-wallet/QRIS) doesn't
        $resolvedStatus = match (true) {
            $status === 'capture' && $fraudStatus === 'accept' => 'settlement',
            $status === 'capture' && $fraudStatus === 'challenge' => 'pending',
            default => $status, // settlement, pending, deny, cancel, expire
        };

        $tx->update([
            'gateway_transaction_id' => $payload['transaction_id'] ?? null,
            'payment_channel' => $payload['payment_type'] ?? $tx->payment_channel,
            'status' => $resolvedStatus,
            'raw_response' => $payload,
            'signature_verified' => true,
            'paid_at' => $resolvedStatus === 'settlement' ? now() : null,
            'expired_at' => $resolvedStatus === 'expire' ? now() : null,
        ]);

        $this->syncOrderStatus($tx->order, $resolvedStatus);

        return response()->json(['message' => 'OK']);
    }

    private function isValidSignature(array $payload): bool
    {
        if (!isset($payload['order_id'], $payload['status_code'], $payload['gross_amount'], $payload['signature_key'])) {
            return false;
        }

        $expected = hash(
            'sha512',
            $payload['order_id']
            . $payload['status_code']
            . $payload['gross_amount']
            . config('services.midtrans.server_key')
        );

        return hash_equals($expected, $payload['signature_key']);
    }

    private function syncOrderStatus(Order $order, string $txStatus): void
    {
        match ($txStatus) {
            'settlement' => $order->update([
                'payment_status' => 'paid',
                'status' => $order->status === 'waiting' ? 'checking' : $order->status,
            ]),
            'expire' => $this->handleFailedOrExpiredPayment($order, 'expired'),
            'deny', 'cancel' => $this->handleFailedOrExpiredPayment($order, 'failed'),
            'pending' => $order->update(['payment_status' => 'pending']),
            default => null,
        };
    }

    private function handleFailedOrExpiredPayment(Order $order, string $paymentStatus): void
    {
        $wasCancelled = $order->status === 'cancelled';

        $order->update([
            'payment_status' => $paymentStatus,
            'status' => 'cancelled',
        ]);

        if (! $wasCancelled) {
            $order->restoreStock();
        }
    }
}
