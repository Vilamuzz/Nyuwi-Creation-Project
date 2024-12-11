<?php

namespace App\Http\Controllers;

use App\Models\Whislist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlistItems = Whislist::with('product')
            ->where('user_id', Auth::id())
            ->paginate(9); // Set 9 items per page

        return Inertia::render('Customer/Wishlist', [
            'wishlistItems' => $wishlistItems
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $wishlist = Whislist::firstOrCreate([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id
        ]);

        return redirect()->back()->with('success', 'Product added to wishlist');
    }

    public function destroy($id)
    {
        $wishlistItem = Whislist::findOrFail($id);

        if ($wishlistItem->user_id !== Auth::id()) {
            abort(403);
        }

        $wishlistItem->delete();

        return redirect()->back()->with('success', 'Product removed from wishlist');
    }
}
