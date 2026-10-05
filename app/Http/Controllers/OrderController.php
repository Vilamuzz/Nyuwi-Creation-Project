<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderCompleteRequest;
use App\Http\Requests\OrderStatusUpdateRequest;
use App\Http\Requests\OrderStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Cart;
use App\Models\ProfileStore;
use App\Services\CartService;
use Inertia\Inertia;
use App\Models\ProductReview;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\ShippingService;

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

        // Don't allow cancellation if order is already handed to the courier
        if (($validated['status'] ?? null) === 'cancelled' && $order->status === 'shipped') {
            return back()->with('error', 'Cannot cancel order that is already being shipped');
        }

        DB::transaction(function () use ($validated, $order) {
            $newStatus = $validated['status'] ?? null;

            if (!empty($validated['tracking_number'])) {
                $order->update([
                    'tracking_number' => $validated['tracking_number'],
                    'status' => 'shipped'
                ]);
            } else if (!empty($newStatus)) {
                // If transitioning to cancelled and was not already cancelled, restore reserved stock
                if ($newStatus === 'cancelled' && $order->status !== 'cancelled') {
                    $order->restoreStock();
                }

                $order->update([
                    'status' => $newStatus
                ]);
            }
        });

        return redirect()->back()->with('success', 'Order updated successfully');
    }

    private function calculateShippingCost(ShippingService $shippingService, array $validated, int $weight): float
    {
        $profile = ProfileStore::first();

        $origin = $profile?->district_id ?? config('services.rajaongkir.origin');

        $destination = $validated['district_id'] ?: $validated['city_id'];

        $rates = $shippingService->cost(
            (string) $origin,
            (string) $destination,
            $weight,
            $validated['courier'],
        );

        foreach ($rates as $rate) {
            if (
                strcasecmp((string) $rate['courier'], $validated['courier']) === 0
                && strcasecmp((string) $rate['service'], $validated['shipping_service']) === 0
            ) {
                return (float) $rate['cost'];
            }
        }

        throw new \RuntimeException(
            "Ongkos kirim tidak tersedia untuk {$validated['courier']} - {$validated['shipping_service']}."
        );
    }

    public function store(OrderStoreRequest $request, ShippingService $shippingService)
    {
        $validated = $request->validated();

        $placedOrderId = null;

        try {
            DB::transaction(function () use ($request, $validated, $shippingService, &$placedOrderId) {
                $user = Auth::user();
                $cartService = app(CartService::class);

                if ($user) {
                    Cart::where('user_id', $user->id)->lockForUpdate()->get();
                }

                $cartItems = $cartService->items($request);

                if ($cartItems === []) {
                    throw new \Exception('Cart is empty');
                }

                // Aggregate quantities by product_id
                $quantitiesByProduct = [];
                foreach ($cartItems as $cartItem) {
                    $productId = (int) $cartItem['product_id'];
                    $quantitiesByProduct[$productId] = ($quantitiesByProduct[$productId] ?? 0) + (int) $cartItem['quantity'];
                }

                // Sort product IDs to prevent deadlocks across concurrent transactions
                $productIds = array_keys($quantitiesByProduct);
                sort($productIds);

                // Lock product rows for update
                $lockedProducts = Product::whereIn('id', $productIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($quantitiesByProduct as $productId => $requiredQty) {
                    $product = $lockedProducts->get($productId);

                    if (! $product || $product->stock < $requiredQty) {
                        $name = $product?->name ?? "Product #{$productId}";
                        throw new \RuntimeException("Insufficient stock for {$name}");
                    }
                }

                // Atomically decrement reserved stock
                foreach ($quantitiesByProduct as $productId => $requiredQty) {
                    $product = $lockedProducts->get($productId);
                    $product->decrement('stock', $requiredQty);
                }

                $summary = $cartService->summarize($cartItems);
                $subtotal = $summary['subtotal'];
                $weight = $summary['totalWeight'];
                $shippingCost = $this->calculateShippingCost($shippingService, $validated, $weight);
                $totalPrice = $subtotal + $shippingCost;

                $order = Order::create([
                    'user_id' => $user?->id,
                    'recipient_name' => $validated['recipient_name'],
                    'address' => $validated['address'],
                    'province_id' => $validated['province_id'],
                    'province_name' => $validated['province_name'],
                    'city_id' => $validated['city_id'],
                    'city_name' => $validated['city_name'],
                    'district_id' => $validated['district_id'],
                    'district_name' => $validated['district_name'],
                    'postal_code' => $validated['postal_code'] ?? null,
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? $user?->email,
                    'courier' => $validated['courier'],
                    'shipping_service' => $validated['shipping_service'],
                    'weight' => $weight,
                    'shipping_cost' => $shippingCost,
                    'subtotal' => $subtotal,
                    'total_price' => $totalPrice,
                    'payment_status' => 'unpaid',
                    'status' => 'waiting',
                ]);

                $placedOrderId = $order->id;

                foreach ($cartItems as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem['product_id'],
                        'quantity' => $cartItem['quantity'],
                        'price' => $cartItem['price'],
                        'total_price' => (float) $cartItem['price'] * (int) $cartItem['quantity'],
                        'size' => $cartItem['size'],
                        'color' => $cartItem['color'],
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

    public function tracking(string $trackingNumber, ShippingService $shippingService)
    {
        $order = Order::where('tracking_number', $trackingNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (strcasecmp($order->courier, 'gosend') === 0) {
            return back()->withErrors(['tracking' => 'GoSend orders do not have tracking information.']);
        }

        $trackingData = $shippingService->track($order->courier, $trackingNumber);

        return back()->with('trackingData', $trackingData);
    }

    public function adminTracking(string $trackingNumber, ShippingService $shippingService)
    {
        $order = Order::where('tracking_number', $trackingNumber)->firstOrFail();

        $trackingData = $shippingService->track($order->courier, $trackingNumber);

        return back()->with('trackingData', $trackingData);
    }
}
