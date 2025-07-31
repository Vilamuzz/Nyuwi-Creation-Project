<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }

    /**
     * Get cart items for authenticated user
     */
    public function cart()
    {
        try {
            $cartItems = Cart::with('product')
                ->where('user_id', Auth::id())
                ->get();

            // Calculate totals
            $subtotal = $cartItems->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            $totalItems = $cartItems->sum('quantity');

            return response()->json([
                'success' => true,
                'data' => [
                    'cartItems' => $cartItems,
                    'summary' => [
                        'subtotal' => $subtotal,
                        'totalItems' => $totalItems,
                        'itemCount' => $cartItems->count()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cart data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add product to cart
     */
    public function addToCart(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'price' => 'required|numeric',
                'size' => 'nullable|string',
                'color' => 'nullable|string'
            ]);

            // Get product and check stock
            $product = Product::findOrFail($request->product_id);

            // Check if adding this quantity would exceed available stock
            $existingCartItem = Cart::where([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'size' => $request->size,
                'color' => $request->color
            ])->first();

            $totalQuantity = $request->quantity;
            if ($existingCartItem) {
                $totalQuantity += $existingCartItem->quantity;
            }

            // Validate against available stock
            if ($totalQuantity > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah melebihi stok yang tersedia',
                    'data' => [
                        'available_stock' => $product->stock,
                        'requested_quantity' => $totalQuantity
                    ]
                ], 400);
            }

            DB::transaction(function () use ($request, $existingCartItem, $totalQuantity) {
                if ($existingCartItem) {
                    // Update quantity if item exists
                    $existingCartItem->update([
                        'quantity' => $totalQuantity
                    ]);
                } else {
                    // Create new cart item
                    Cart::create([
                        'user_id' => Auth::id(),
                        'product_id' => $request->product_id,
                        'quantity' => $request->quantity,
                        'price' => $request->price,
                        'size' => $request->size,
                        'color' => $request->color
                    ]);
                }
            });

            // Return updated cart
            return $this->cart();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $cartItem = Cart::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $product = Product::findOrFail($cartItem->product_id);

            // Check if new quantity exceeds available stock
            if ($request->quantity > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah melebihi stok yang tersedia',
                    'data' => [
                        'available_stock' => $product->stock,
                        'requested_quantity' => $request->quantity
                    ]
                ], 400);
            }

            $cartItem->update([
                'quantity' => $request->quantity
            ]);

            // Return updated cart
            return $this->cart();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart($id)
    {
        try {
            $cartItem = Cart::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $cartItem->delete();

            // Return updated cart
            return $this->cart();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all cart items for authenticated user
     */
    public function clearCart()
    {
        try {
            Cart::where('user_id', Auth::id())->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully',
                'data' => [
                    'cartItems' => [],
                    'summary' => [
                        'subtotal' => 0,
                        'totalItems' => 0,
                        'itemCount' => 0
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cart item count (for header badge)
     */
    public function cartCount()
    {
        try {
            $count = Cart::where('user_id', Auth::id())->sum('quantity');

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cart count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate cart items before checkout
     */
    public function validateCart()
    {
        try {
            $cartItems = Cart::with('product')->where('user_id', Auth::id())->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty'
                ], 400);
            }

            $issues = [];
            $validItems = [];

            foreach ($cartItems as $item) {
                if (!$item->product) {
                    $issues[] = [
                        'item_id' => $item->id,
                        'issue' => 'Product no longer exists'
                    ];
                    continue;
                }

                if ($item->quantity > $item->product->stock) {
                    $issues[] = [
                        'item_id' => $item->id,
                        'product_name' => $item->product->name,
                        'issue' => 'Insufficient stock',
                        'available_stock' => $item->product->stock,
                        'requested_quantity' => $item->quantity
                    ];
                    continue;
                }

                $validItems[] = $item;
            }

            return response()->json([
                'success' => empty($issues),
                'data' => [
                    'valid_items' => $validItems,
                    'issues' => $issues,
                    'can_proceed' => empty($issues)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
