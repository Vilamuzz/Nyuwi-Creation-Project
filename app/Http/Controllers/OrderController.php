<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderCompleteRequest;
use App\Http\Requests\OrderStatusUpdateRequest;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\PaymentProofRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Cart;
use App\Services\CartService;
use Inertia\Inertia;
use App\Models\ProductReview;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\MidtransService;
use App\Services\Shipping\ShippingManager;

class OrderController extends Controller
{
    public function show()
    {
        $orders = Order::all();
        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders
        ]);
    }

    public function detail($id)
    {
        $order = Order::with(['orderItems.product'])
            ->findOrFail($id);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order
        ]);
    }

    public function update(OrderStatusUpdateRequest $request, $id)
    {
        $validated = $request->validated();

        $order = Order::findOrFail($id);

        // Don't allow cancellation if order is shipping
        if (($validated['status'] ?? null) === 'cancelled' && $order->status === 'shiping') {
            return back()->with('error', 'Cannot cancel order that is already being shipped');
        }

        DB::transaction(function () use ($validated, $order) {
            if (!empty($validated['tracking_number'])) {
                // Update order to shipping status
                $order->update([
                    'tracking_number' => $validated['tracking_number'],
                    'status' => 'shiping'
                ]);

                // Reduce product stock for each order item
                foreach ($order->orderItems as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->update([
                            'stock' => $product->stock - $item->quantity
                        ]);
                    }
                }
            } else if (!empty($validated['status'])) {
                if ($validated['status'] === 'shiping') {
                    // Reduce product stock for each order item
                    foreach ($order->orderItems as $item) {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $product->update([
                                'stock' => $product->stock - $item->quantity
                            ]);
                        }
                    }
                }
                $order->update([
                    'status' => $validated['status']
                ]);
            }
        });

        return redirect()->back()->with('success', 'Order updated successfully');
    }

    public function store(OrderStoreRequest $request)
    {
        $validated = $request->validated();

        $placedOrderId = null;

        try {
            DB::transaction(function () use ($request, $validated, &$placedOrderId) {
                $user = Auth::user();
                $cartService = new CartService();

                $cartItems = $user
                    ? Cart::with('product')->where('user_id', $user->id)->lockForUpdate()->get()
                    : collect($cartService->items($request));

                if ($cartItems->isEmpty()) {
                    throw new \Exception('Cart is empty');
                }

                foreach ($cartItems as $cartItem) {
                    $product = $user ? $cartItem->product : $cartItem['product'];
                    $quantity = $user ? $cartItem->quantity : $cartItem['quantity'];

                    if (!$product || $quantity > $product->stock) {
                        throw new \RuntimeException("Insufficient stock for {$product?->name}");
                    }
                }

                // Calculate total price
                $subtotal = $cartItems->sum(fn ($item) => $user
                    ? $item->price * $item->quantity
                    : (float) $item['price'] * $item['quantity']);
                $totalPrice = $subtotal + $validated['shipping_cost'];

                $order = Order::create([
                    'user_id' => $user?->id,
                    'name' => $validated['name'],
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                    'district' => $validated['district'],
                    'village' => $validated['village'],
                    'province' => $validated['province'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? $user?->email,
                    'total_price' => $totalPrice,
                    'payment_method' => $validated['payment_method'],
                    'shipping_method' => $validated['shipping_method'],
                    'note' => $validated['note'] ?? null,
                    'payment_status' => 'pending',
                    'status' => 'processing',
                ]);

                $placedOrderId = $order->id;

                // Create order items and clear cart
                foreach ($cartItems as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $user ? $cartItem->product_id : $cartItem['product_id'],
                        'quantity' => $user ? $cartItem->quantity : $cartItem['quantity'],
                        'price' => $user ? $cartItem->price : $cartItem['price'],
                        'total_price' => $user
                            ? $cartItem->price * $cartItem->quantity
                            : (float) $cartItem['price'] * $cartItem['quantity'],
                        'size' => $user ? $cartItem->size : $cartItem['size'],
                        'color' => $user ? $cartItem->color : $cartItem['color']
                    ]);
                }

                if ($user) {
                    Cart::where('user_id', $user->id)->delete();
                } else {
                    $cartService->clear($request);
                }
            });

            return redirect()->route('cart.show')->with([
                'success' => 'Order placed successfully!',
                'placedOrderId' => $placedOrderId,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to place order: ' . $e->getMessage());
        }
    }

    public function complete(OrderCompleteRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $order = Order::where('id', $validated['order_id'])
                ->where('user_id', Auth::id())
                ->firstOrFail();
            $order->update(['status' => 'completed']);

            // Save reviews
            foreach ($validated['reviews'] as $review) {
                ProductReview::create([
                    'user_id' => Auth::id(),
                    'product_id' => $review['product_id'],
                    'order_id' => $validated['order_id'],
                    'rating' => $review['rating']
                ]);
            }
        });

        return redirect()->back()->with('success', 'Order completed and reviews submitted');
    }

    public function orderUser(Request $request)
    {
        $orders = Order::with(['orderItems.product'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $reviews = ProductReview::with('product')
            ->where('user_id', Auth::id())
            ->get();


        return Inertia::render('Customer/Dashboard', [
            'orders' => $orders,
            'reviews' => $reviews,

            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function userOrders()
    {
        return Inertia::render('Customer/Orders/Index', [
            'orders' => Order::with(['orderItems.product'])
                ->where('user_id', Auth::id())
                ->latest()
                ->get(),
        ]);
    }

    public function userOrderDetail($id)
    {
        return Inertia::render('Customer/Orders/Show', [
            'order' => Order::with(['orderItems.product'])
                ->where('user_id', Auth::id())
                ->findOrFail($id),
        ]);
    }

    public function tracking(string $trackingNumber, ShippingManager $shippingManager)
    {
        $order = Order::where('tracking_number', $trackingNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (strcasecmp($order->shipping_method, 'GoSend') === 0) {
            return back()->withErrors(['tracking' => 'GoSend orders do not have tracking information.']);
        }

        $trackingData = $shippingManager->track($order->shipping_method, $trackingNumber);

        return back()->with('trackingData', $trackingData);
    }

    public function adminTracking(string $trackingNumber, ShippingManager $shippingManager)
    {
        $order = Order::where('tracking_number', $trackingNumber)->firstOrFail();

        $trackingData = $shippingManager->track($order->shipping_method, $trackingNumber);

        return back()->with('trackingData', $trackingData);
    }

    public function uploadPaymentProof(PaymentProofRequest $request)
    {
        return back()->with('error', 'Unggah bukti pembayaran manual sudah tidak didukung.');
    }

    public function paymentToken($id, MidtransService $midtransService)
    {
        $order = Order::with('orderItems.product')->findOrFail($id);

        if ($order->user_id && $order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to order');
        }

        if ($order->payment_status !== 'pending') {
            return response()->json([
                'message' => 'Order is not in pending payment status.',
                'payment_status' => $order->payment_status,
            ], 400);
        }

        try {
            $snapData = $midtransService->createSnapToken($order);

            return response()->json([
                'snap_token' => $snapData['token'],
                'redirect_url' => $snapData['redirect_url'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate payment token: ' . $e->getMessage(),
            ], 500);
        }
    }
}
