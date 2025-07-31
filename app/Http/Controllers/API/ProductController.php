<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductReview;
use App\Http\Resources\Product\ProductPreview;
use App\Http\Resources\Category\CategoryWithImage;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function home()
    {
        try {
            // Get products with reviews and ratings
            $products = Product::query()
                ->withCount('reviews as total_reviews')
                ->withAvg('reviews as average_rating', 'rating')
                ->take(8)
                ->get();

            // Get categories
            $categories = Category::all();

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => ProductPreview::collection($products),
                    'categories' => CategoryWithImage::collection($categories)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch home data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function shop(Request $request)
    {
        try {
            $query = Product::query()
                ->withCount('reviews as total_reviews')
                ->withAvg('reviews as average_rating', 'rating');

            // Apply category filter
            if ($request->has('category') && !empty($request->category)) {
                $query->where('category_id', $request->category);
            }

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            // Apply sorting
            if ($request->has('sortField') && $request->has('sortDirection')) {
                $query->orderBy($request->sortField, $request->sortDirection);
            } else {
                $query->latest();
            }

            // Get products with pagination
            $products = $query->latest()->paginate(16);

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => ProductPreview::collection($products),
                    'categories' => Category::all(),
                    'filters' => $request->only(['search', 'sortField', 'sortDirection', 'category']),
                    'meta' => [
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'total' => $products->total(),
                        'per_page' => $products->perPage()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch shop data',
                'error' => $e->getMessage()
            ], 500);
        }
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
    public function show(Product $product) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }

    /**
     * Get product details by slug for customer view
     */
    public function product($slug)
    {
        try {
            $product = Product::where('slug', $slug)->firstOrFail();
            $categories = Category::all();

            // Get reviews directly
            $reviews = ProductReview::where('product_id', $product->id)
                ->select('rating')
                ->get();

            // Calculate average rating and total reviews
            $averageRating = $reviews->avg('rating') ?? 0;
            $totalReviews = $reviews->count();

            $productRating = [
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $totalReviews
            ];

            // Get related products from the same category
            $relatedProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id) // Exclude current product
                ->withCount('reviews as total_reviews')
                ->withAvg('reviews as average_rating', 'rating')
                ->take(4) // Limit to 4 related products
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $product,
                    'categories' => $categories,
                    'productRating' => $productRating,
                    'relatedProducts' => $relatedProducts
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
