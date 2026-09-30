<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function createTransaction(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id && $order->user_id !== $request->user()?->id, 403, 'Unauthorized access to order');

        abort_unless(
            in_array($order->payment_status, ['unpaid', 'pending'], true),
            400,
            'Order is not in pending payment status.'
        );

        try {
            $snap = $this->payments->createSnapToken($order);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => 'Failed to generate payment token: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'snap_token' => $snap['token'],
            'redirect_url' => $snap['redirect_url'],
        ]);
    }
}
