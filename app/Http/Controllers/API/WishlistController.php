<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Get wishlist items for authenticated user
     */
    public function index()
    {
        try {
            $wishlistItems = Wishlist::with('product')
                ->where('user_id', Auth::id())
                ->paginate(9);

            return response()->json([
                'success' => true,
                'data' => $wishlistItems
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add product to wishlist
     */
    public function store(Request $request)
    {
        try {
            // Validate request - now we accept either product_id or slug
            $request->validate([
                'slug' => 'nullable|string'
            ]);

            // Determine product ID - either directly provided or looked up by slug
            $productId = null;

            if ($request->has('slug')) {
                $product = Product::where('slug', $request->slug)->first();
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found'
                    ], 404);
                }
                $productId = $product->id;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Either product_id or slug is required'
                ], 422);
            }

            // Check if item already exists in wishlist
            $exists = Wishlist::where([
                'user_id' => Auth::id(),
                'product_id' => $productId
            ])->exists();

            if ($exists) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product is already in your wishlist'
                ]);
            }

            // Create wishlist item
            $wishlist = Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $productId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist',
                'data' => $wishlist
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from wishlist
     */
    public function destroy($id)
    {
        try {
            $wishlistItem = Wishlist::where([
                'id' => $id,
                'user_id' => Auth::id()
            ])->firstOrFail();

            $wishlistItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove product from wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wishlist item count
     */
    public function count()
    {
        try {
            $count = Wishlist::where('user_id', Auth::id())->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get wishlist count',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
