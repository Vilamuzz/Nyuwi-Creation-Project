<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CartController extends Controller
{
    /**
     * Show the cart page.
     */
    public function showCart()
    {
        $cartItems = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        return Inertia::render('Customer/Cart', [
            'cartItems' => $cartItems,
            'summary' => $this->summarize($cartItems),
        ]);
    }

    /**
     * Show the checkout page.
     */
    public function showCheckout(Request $request)
    {
        $cartItems = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        return Inertia::render('Customer/Checkout', [
            'cartItems' => $cartItems,
            'summary' => $this->summarize($cartItems),
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function store(CartStoreRequest $request)
    {
        $product = Product::findOrFail($request->validated('product_id'));

        $existingCartItem = Cart::where([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'size' => $request->validated('size'),
            'color' => $request->validated('color'),
        ])->first();

        $totalQuantity = $request->validated('quantity');
        if ($existingCartItem) {
            $totalQuantity += $existingCartItem->quantity;
        }

        if ($totalQuantity > $product->stock) {
            return back()->withErrors([
                'quantity' => "Jumlah melebihi stok yang tersedia (stok: {$product->stock}).",
            ]);
        }

        DB::transaction(function () use ($request, $existingCartItem, $product, $totalQuantity) {
            if ($existingCartItem) {
                $existingCartItem->update([
                    'quantity' => $totalQuantity,
                    'price' => $product->price,
                ]);

                return;
            }

            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'quantity' => $request->validated('quantity'),
                'price' => $product->price,
                'size' => $request->validated('size'),
                'color' => $request->validated('color'),
            ]);
        });

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    /**
     * Update a cart item's quantity.
     */
    public function update(CartUpdateRequest $request, $id)
    {
        $cartItem = Cart::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $product = Product::findOrFail($cartItem->product_id);

        if ($request->validated('quantity') > $product->stock) {
            return back()->withErrors([
                'quantity' => "Jumlah melebihi stok yang tersedia (stok: {$product->stock}).",
            ]);
        }

        $cartItem->update([
            'quantity' => $request->validated('quantity'),
        ]);

        return back()->with('success', 'Keranjang berhasil diperbarui.');
    }

    /**
     * Remove a cart item.
     */
    public function destroy($id)
    {
        $cartItem = Cart::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cartItem->delete();

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    /**
     * Build a summary payload for a set of cart items.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Cart>  $cartItems
     * @return array{subtotal: float|int, totalItems: int, itemCount: int}
     */
    private function summarize($cartItems): array
    {
        return [
            'subtotal' => $cartItems->sum(fn ($item) => $item->price * $item->quantity),
            'totalItems' => $cartItems->sum('quantity'),
            'itemCount' => $cartItems->count(),
        ];
    }
}
