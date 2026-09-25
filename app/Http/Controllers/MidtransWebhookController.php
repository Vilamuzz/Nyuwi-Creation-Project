<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request, MidtransService $midtransService): JsonResponse
    {
        $payload = $request->all();

        $orderCustomId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $transactionId = $payload['transaction_id'] ?? null;
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? 'accept');
        $paymentType = $payload['payment_type'] ?? null;

        // 1. Verify Midtrans SHA-512 signature
        if (!$midtransService->verifySignature($orderCustomId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans webhook signature mismatch', [
                'order_id' => $orderCustomId,
                'signature_key' => $signatureKey,
            ]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // 2. Extract database Order ID
        $orderId = $midtransService->extractOrderId($orderCustomId);
        if (!$orderId) {
            return response()->json(['message' => 'Malformed order ID format'], 422);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // 3. Webhook Idempotency Check using payment_transactions
        $alreadyLogged = PaymentTransaction::where('gateway', 'midtrans')
            ->where('gateway_transaction_id', $transactionId)
            ->where('raw_status', $transactionStatus)
            ->exists();

        if ($alreadyLogged) {
            return response()->json(['message' => 'Notification already processed.'], 200);
        }

        // 4. Map Midtrans Status -> orders.payment_status
        $paymentStatus = match ($transactionStatus) {
            'capture' => ($fraudStatus === 'challenge') ? 'pending' : (($fraudStatus === 'accept') ? 'paid' : 'failed'),
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny', 'cancel' => 'failed',
            'expire' => 'expired',
            'refund', 'partial_refund' => 'refunded',
            default => 'pending',
        };

        // 5. Atomic Update
        DB::transaction(function () use ($order, $payload, $transactionId, $paymentType, $grossAmount, $transactionStatus, $paymentStatus) {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => 'midtrans',
                'gateway_transaction_id' => $transactionId,
                'payment_channel' => $paymentType,
                'amount' => (float) $grossAmount,
                'raw_status' => $transactionStatus,
                'paid_at' => ($paymentStatus === 'paid') ? now() : null,
                'raw_payload' => $payload,
            ]);

            $order->update([
                'payment_status' => $paymentStatus,
            ]);
        });

        return response()->json([
            'message' => 'Payment status updated successfully',
            'payment_status' => $paymentStatus,
        ], 200);
    }
}
