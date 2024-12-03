<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Inertia\Inertia;
use App\Models\ProductReview;

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
        $order = Order::with(['orderItems.product']) // Eager load order items and products
            ->findOrFail($id);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required_if:tracking_number,null|in:pending,processing,shiping,completed,cancelled',
            'tracking_number' => 'required_if:shipping_method,!=,GoSend'
        ]);

        $order = Order::findOrFail($id);

        // Don't allow cancellation if order is shipping
        if ($request->status === 'cancelled' && $order->status === 'shiping') {
            return back()->with('error', 'Cannot cancel order that is already being shipped');
        }

        if ($request->has('tracking_number')) {
            $order->update([
                'tracking_number' => $request->tracking_number,
                'status' => 'shiping'
            ]);
        } else {
            $order->update([
                'status' => $request->status
            ]);
        }

        return redirect()->back()->with('success', 'Order updated successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'village' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'payment_method' => 'required|in:digital_wallet,cash_on_delivery',
            'shipping_method' => 'required|in:JNE,GoSend',
            'shipping_cost' => 'required|numeric|min:0', // Add validation for shipping cost
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = Auth::user();
                $cartItems = Cart::where('user_id', $user->id)->get();

                if ($cartItems->isEmpty()) {
                    throw new \Exception('Cart is empty');
                }

                // Calculate subtotal from cart items
                $subtotal = $cartItems->sum(function ($item) {
                    return $item->price * $item->quantity;
                });

                // Add shipping cost to total
                $totalPrice = $subtotal + $request->shipping_cost;

                // Create order with total including shipping
                $order = Order::create([
                    'user_id' => $user->id,
                    'name' => $request->name,
                    'address' => $request->address,
                    'city' => $request->city,
                    'district' => $request->district,
                    'village' => $request->village,
                    'province' => $request->province,
                    'phone' => $request->phone,
                    'total_price' => $totalPrice, // Total includes shipping cost
                    'payment_method' => $request->payment_method,
                    'shipping_method' => $request->shipping_method,
                    'note' => $request->note,
                    'status' => 'waiting'
                ]);

                // Create order items
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

    public function complete(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reviews' => 'required|array',
            'reviews.*.product_id' => 'required|exists:products,id',
            'reviews.*.rating' => 'required|integer|min:1|max:5',
        ]);

        DB::transaction(function () use ($request) {
            $order = Order::findOrFail($request->order_id);
            $order->update(['status' => 'completed']);

            // Save reviews
            foreach ($request->reviews as $review) {
                ProductReview::create([
                    'user_id' => Auth::id(),
                    'product_id' => $review['product_id'],
                    'order_id' => $request->order_id,
                    'rating' => $review['rating']
                ]);
            }
        });

        return redirect()->back()->with('success', 'Order completed and reviews submitted');
    }

    public function orderUser()
    {
        $orders = Order::with(['orderItems.product'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $reviews = ProductReview::with(['product', 'order'])
            ->where('user_id', Auth::id())
            ->get();

        return Inertia::render('Customer/Dashboard', [
            'orders' => $orders,
            'reviews' => $reviews
        ]);
    }

    public function getInfo()
    {
        $orders = Order::where('user_id', Auth::id())->get();
        return response()->json(['orders' => $orders]);
    }

    public function uploadPaymentProof(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $order = Order::findOrFail($request->order_id);

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
