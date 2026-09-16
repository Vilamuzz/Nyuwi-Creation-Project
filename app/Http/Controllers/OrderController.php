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
use Inertia\Inertia;
use App\Models\ProductReview;
use App\Models\Wishlist;
use Illuminate\Contracts\Auth\MustVerifyEmail;

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

        // Prevent accepting digital wallet orders without payment proof
        if (
            ($validated['status'] ?? null) === 'processing' &&
            $order->payment_method === 'digital_wallet' &&
            $order->status === 'waiting'
        ) {
            return back()->with('error', 'Cannot accept order without payment proof verification');
        }

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

        try {
            DB::transaction(function () use ($validated) {
                $user = Auth::user();
                $cartItems = Cart::with('product')
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->get();

                if ($cartItems->isEmpty()) {
                    throw new \Exception('Cart is empty');
                }

                foreach ($cartItems as $cartItem) {
                    if (!$cartItem->product || $cartItem->quantity > $cartItem->product->stock) {
                        throw new \RuntimeException("Insufficient stock for {$cartItem->product?->name}");
                    }
                }

                // Calculate total price
                $subtotal = $cartItems->sum(fn ($item) => $item->price * $item->quantity);
                $totalPrice = $subtotal + $validated['shipping_cost'];

                // Manual payment starts in a waiting state.
                $initialStatus = 'waiting';

                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'name' => $validated['name'],
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                    'district' => $validated['district'],
                    'village' => $validated['village'],
                    'province' => $validated['province'],
                    'phone' => $validated['phone'],
                    'total_price' => $totalPrice,
                    'payment_method' => $validated['payment_method'],
                    'shipping_method' => $validated['shipping_method'],
                    'note' => $validated['note'] ?? null,
                    'status' => $initialStatus // Use dynamic initial status
                ]);

                // Create order items and clear cart
                foreach ($cartItems as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->price,
                        'total_price' => $cartItem->price * $cartItem->quantity,
                        'size' => $cartItem->size,
                        'color' => $cartItem->color
                    ]);
                }

                // Clear cart
                Cart::where('user_id', $user->id)->delete();
            });

            return redirect()->route('cart.show')->with('success', 'Order placed successfully!');
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

        $wishlistItems = Wishlist::with('product')
            ->where('user_id', Auth::id())
            ->get();

        return Inertia::render('Customer/Dashboard', [
            'orders' => $orders,
            'reviews' => $reviews,
            'wishlistItems' => $wishlistItems,
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

    public function tracking(string $trackingNumber)
    {
        $order = Order::where('tracking_number', $trackingNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($order->shipping_method === 'GoSend') {
            return back()->withErrors(['tracking' => 'GoSend orders do not have tracking information.']);
        }

        $trackingResponse = Http::get('https://api.binderbyte.com/v1/track', [
            'api_key' => config('services.binderbyte.api_key'),
            'courier' => strtolower($order->shipping_method),
            'awb' => $trackingNumber,
        ]);

        return back()->with('trackingData', $trackingResponse->json());
    }

    public function adminTracking(string $trackingNumber)
    {
        $order = Order::where('tracking_number', $trackingNumber)->firstOrFail();

        $trackingResponse = Http::get('https://api.binderbyte.com/v1/track', [
            'api_key' => config('services.binderbyte.api_key'),
            'courier' => strtolower($order->shipping_method),
            'awb' => $trackingNumber,
        ]);

        return back()->with('trackingData', $trackingResponse->json());
    }

    public function uploadPaymentProof(PaymentProofRequest $request)
    {
        $validated = $request->validated();
        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($request->hasFile('payment_proof')) {
            $proof = $request->file('payment_proof');
            $proofName = time() . '.' . $proof->getClientOriginalExtension();
            $proof->storeAs('payment_proofs', $proofName, 'public');

            $order->update([
                'payment_proof' => $proofName,
                'status' => 'checking'
            ]);
        }

        return back()->with('success', 'Bukti pembayaran berhasil diunggah');
    }
}
