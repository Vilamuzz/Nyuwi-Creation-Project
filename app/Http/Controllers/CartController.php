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

        // Cek apakah produk dengan size dan color yang sama sudah ada di cart
        $cartItem = Cart::where([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'size' => $request->size,
            'color' => $request->color
        ])->first();

        if ($cartItem) {
            // Update quantity jika item sudah ada
            $cartItem->update([
                'quantity' => $cartItem->quantity + $request->quantity
            ]);
        } else {
            // Buat item baru jika belum ada
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

        return Inertia::render('Customer/Checkout', [
            'cartItems' => $cartItems
        ]);
    }
}
