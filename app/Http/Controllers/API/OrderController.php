<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\ProductReview;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    /**
     * Get tracking information for an order
     */
    public function getTracking($trackingNumber)
    {
        try {
            // Get the order to determine shipping method
            $order = Order::where('tracking_number', $trackingNumber)
                ->where('user_id', Auth::id())
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Don't track GoSend orders
            if ($order->shipping_method === 'GoSend') {
                return response()->json([
                    'success' => false,
                    'message' => 'GoSend orders do not have tracking information'
                ], 400);
            }

            // Make request to external tracking API
            $response = Http::get('https://api.binderbyte.com/v1/track', [
                'api_key' => env('BINDERBYTE_API_KEY'),
                'courier' => strtolower($order->shipping_method),
                'awb' => $trackingNumber,
            ]);

            if ($response->successful() && $response->json('status') === 200) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json('data')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $response->json('message', 'Failed to fetch tracking information')
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching tracking information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete an order with product reviews
     */
    public function completeOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reviews' => 'required|array',
            'reviews.*.product_id' => 'required|exists:products,id',
            'reviews.*.rating' => 'required|integer|min:1|max:5',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Get the order and verify it belongs to the user
                $order = Order::where('id', $request->order_id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();

                // Check if order can be completed
                if (!in_array($order->status, ['shiping', 'delivered'])) {
                    throw new \Exception('Order cannot be completed at this stage');
                }

                // Update order status to completed
                $order->update(['status' => 'completed']);

                // Save reviews for each product
                foreach ($request->reviews as $review) {
                    // Check if review already exists
                    $existingReview = ProductReview::where([
                        'user_id' => Auth::id(),
                        'product_id' => $review['product_id'],
                        'order_id' => $request->order_id
                    ])->first();

                    if (!$existingReview) {
                        ProductReview::create([
                            'user_id' => Auth::id(),
                            'product_id' => $review['product_id'],
                            'order_id' => $request->order_id,
                            'rating' => $review['rating']
                        ]);
                    } else {
                        // Update existing review
                        $existingReview->update(['rating' => $review['rating']]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Order completed and reviews submitted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's orders with details
     */
    public function getUserOrders()
    {
        try {
            $orders = Order::with(['orderItems.product'])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order details by ID
     */
    public function getOrderDetails($orderId)
    {
        try {
            $order = Order::with(['orderItems.product'])
                ->where('id', $orderId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or access denied'
            ], 404);
        }
    }

    /**
     * Check if order can be reviewed/completed
     */
    public function checkOrderStatus($orderId)
    {
        try {
            $order = Order::where('id', $orderId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $canBeReviewed = false;
            $trackingStatus = null;

            // For GoSend orders, check if status is shipping
            if ($order->shipping_method === 'GoSend' && $order->status === 'shiping') {
                $canBeReviewed = true;
            }
            // For other couriers, check tracking status
            elseif ($order->tracking_number && $order->shipping_method !== 'GoSend') {
                $trackingResponse = $this->getTracking($order->tracking_number);
                $trackingData = $trackingResponse->getData();

                if ($trackingData->success && isset($trackingData->data->summary->status)) {
                    $trackingStatus = strtolower($trackingData->data->summary->status);
                    $canBeReviewed = $trackingStatus === 'delivered';
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'can_be_reviewed' => $canBeReviewed,
                    'tracking_status' => $trackingStatus,
                    'shipping_method' => $order->shipping_method
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or access denied'
            ], 404);
        }
    }
}
