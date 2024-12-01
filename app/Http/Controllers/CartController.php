<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CartController extends Controller
{

    /**
     * Menambah produk ke dalam keranjang.
     */
    public function addToCart(Request $request)
    {
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
            return back()->withErrors([
                'quantity' => 'Jumlah melebihi stok yang tersedia. Stok tersedia: ' . $product->stock
            ]);
        }

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

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    /**
     * Menampilkan keranjang belanja.
     */
    public function showCart()
    {
        $cartItems = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        return Inertia::render('Customer/Cart', [
            'cartItems' => $cartItems
        ]);
    }

    /**
     * Menghapus item dari keranjang.
     */
    public function removeFromCart($id)
    {
        $cartItem = Cart::findOrFail($id);
        $cartItem->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    public function updateCart(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = Cart::findOrFail($id);
        $product = Product::findOrFail($cartItem->product_id);

        // Check if new quantity exceeds available stock
        if ($request->quantity > $product->stock) {
            return back()->withErrors([
                'quantity' => 'Jumlah melebihi stok yang tersedia. Stok tersedia: ' . $product->stock
            ]);
        }

        $cartItem->update([
            'quantity' => $request->quantity
        ]);

        return redirect()->back();
    }

    public function showCheckout()
    {
        $cartItems = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        // Redirect back if cart is empty
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.show')->with('error', 'Keranjang belanja kosong. Silakan tambahkan produk terlebih dahulu.');
        }

        return Inertia::render('Customer/Checkout', [
            'cartItems' => $cartItems
        ]);
    }
}
