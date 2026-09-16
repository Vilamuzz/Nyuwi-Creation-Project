<?php

namespace App\Http\Controllers;

use App\Http\Requests\WishlistStoreRequest;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WishlistController extends Controller
{
    /**
     * Show the wishlist page.
     */
    public function index()
    {
        $wishlistItems = Wishlist::with('product')
            ->where('user_id', Auth::id())
            ->paginate(9);

        return Inertia::render('Customer/Wishlist', [
            'wishlistItems' => $wishlistItems,
        ]);
    }

    /**
     * Add a product to the wishlist.
     */
    public function store(WishlistStoreRequest $request)
    {
        $product = Product::where('slug', $request->validated('slug'))->firstOrFail();

        $exists = Wishlist::where([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
        ])->exists();

        if ($exists) {
            return back()->with('info', 'Produk sudah ada di daftar favorit.');
        }

        Wishlist::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
        ]);

        return back()->with('success', 'Produk berhasil ditambahkan ke favorit.');
    }

    /**
     * Remove a product from the wishlist.
     */
    public function destroy($id)
    {
        $wishlistItem = Wishlist::where([
            'id' => $id,
            'user_id' => Auth::id(),
        ])->firstOrFail();

        $wishlistItem->delete();

        return back()->with('success', 'Produk berhasil dihapus dari favorit.');
    }
}
