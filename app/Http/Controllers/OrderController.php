<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Inertia\Inertia;

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
        $order = Order::findOrFail($id);

        // Validate request
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        // Update order status
        $order->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Order status updated successfully');
    }

    public function store(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'payment_method' => 'required|in:E-Wallet,cash_on_delivery',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = Auth::user();
                $cartItems = Cart::where('user_id', $user->id)->get();

                if ($cartItems->isEmpty()) {
                    throw new \Exception('Cart is empty');
                }

                // Calculate total price from cart items
                $totalPrice = $cartItems->sum(function ($item) {
                    return $item->price * $item->quantity;
                });

                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'name' => $request->name,
                    'address' => $request->address,
                    'city' => $request->city,
                    'province' => $request->province,
                    'phone' => $request->phone,
                    'total_price' => $totalPrice,
                    'payment_method' => $request->payment_method,
                    'note' => $request->note,
                ]);

                // Create order items
                foreach ($cartItems as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->price,
                        'total_price' => $cartItem->price * $cartItem->quantity,
                        'size' => $cartItem->size, // Add size from cart
                        'color' => $cartItem->color // Add color from cart
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
}
