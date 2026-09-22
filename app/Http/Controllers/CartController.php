<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    /**
     * Show the cart page.
     */
    public function showCart(Request $request)
    {
        $cart = new CartService();
        $cartItems = $cart->items($request);

        return Inertia::render('Customer/Cart', [
            'cartItems' => $cartItems,
            'summary' => $cart->summarize($cartItems),
        ]);
    }

    /**
     * Show the checkout page.
     */
    public function showCheckout(Request $request)
    {
        $cart = new CartService();
        $cartItems = $cart->items($request);

        return Inertia::render('Customer/Checkout', [
            'cartItems' => $cartItems,
            'summary' => $cart->summarize($cartItems),
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function store(CartStoreRequest $request)
    {
        $product = Product::findOrFail($request->validated('product_id'));

        $error = (new CartService())->add(
            $request,
            $product,
            $request->validated('quantity'),
            $request->validated('size'),
            $request->validated('color'),
        );

        if ($error !== null) {
            return back()->withErrors([
                'quantity' => $error,
            ]);
        }

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    /**
     * Update a cart item's quantity.
     */
    public function update(CartUpdateRequest $request, $id)
    {
        $error = (new CartService())->update(
            $request,
            $id,
            $request->validated('quantity'),
        );

        if ($error !== null) {
            return back()->withErrors([
                'quantity' => $error,
            ]);
        }

        return back()->with('success', 'Keranjang berhasil diperbarui.');
    }

    /**
     * Remove a cart item.
     */
    public function destroy(Request $request, $id)
    {
        (new CartService())->destroy($request, $id);

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }
}