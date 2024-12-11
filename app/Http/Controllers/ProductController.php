<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use App\Models\ProductReview;
use App\Models\OrderItem;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Apply search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Apply sorting
        if ($request->has('sortField') && $request->has('sortDirection')) {
            $query->orderBy(
                $request->sortField,
                $request->sortDirection
            );
        } else {
            // Default sorting
            $query->latest();
        }

        $products = $query->get();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => Category::all(),
            'filters' => $request->only(['search', 'sortField', 'sortDirection'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return Inertia::render('Admin/Products/Create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:1',
            'price' => 'required|numeric|min:1',
            'category_id' => 'nullable|integer|exists:categories,id',
            'new_category' => 'nullable|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Buat kategori baru jika 'new_category' diisi
        if ($request->filled('new_category')) {
            $category = Category::create(['name' => $request->new_category]);
            $validatedData['category_id'] = $category->id;
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('products', $imageName, 'public');
            $validatedData['image'] =  $imageName;
        }

        $product = Product::create([
            'name' => $validatedData['name'],
            'stock' => $validatedData['stock'],
            'price' => $validatedData['price'],
            'category_id' => $validatedData['category_id'],
            'description' => $request->description,
            'image' => $validatedData['image']
        ]);

        // Add colors if provided
        if ($request->colors) {
            foreach ($request->colors as $color) {
                $product->colors()->create(['color' => $color]);
            }
        }

        // Add sizes if provided
        if ($request->sizes) {
            foreach ($request->sizes as $size) {
                $product->sizes()->create(['size' => $size]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $product = Product::with(['colors', 'sizes'])->findOrFail($id);
        $categories = Category::all();
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:1',
            'price' => 'required|numeric|min:1',
            'category_id' => 'nullable|integer|exists:categories,id',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'colors' => 'nullable|array',
            'sizes' => 'nullable|array',
        ]);

        $updateData = [
            'name' => $validatedData['name'],
            'stock' => $validatedData['stock'],
            'price' => $validatedData['price'],
            'category_id' => $validatedData['category_id'],
            'description' => $request->description,
        ];

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image && Storage::exists("public/products/{$product->image}")) {
                Storage::delete("public/products/{$product->image}");
            }

            // Store new image
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('products', $imageName, 'public');
            $updateData['image'] = $imageName;
        }

        $product->update($updateData);

        // Update colors
        if ($request->has('colors')) {
            $product->colors()->delete();
            foreach ($request->colors as $color) {
                $product->colors()->create(['color' => $color]);
            }
        }

        // Update sizes
        if ($request->has('sizes')) {
            $product->sizes()->delete();
            foreach ($request->sizes as $size) {
                $product->sizes()->create(['size' => $size]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Check if product is in any active orders
        $hasActiveOrders = OrderItem::whereHas('order', function ($query) {
            $query->whereNotIn('status', ['completed', 'cancelled']);
        })->where('product_id', $id)->exists();

        if ($hasActiveOrders) {
            return redirect()->back()
                ->with('error', 'Cannot delete product that is part of an active order');
        }

        // Delete product if no active orders
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function landingPage()
    {
        $products = Product::query()
            ->withCount('reviews as total_reviews')
            ->withAvg('reviews as average_rating', 'rating')
            ->take(8)
            ->get();

        $categories = Category::all();

        return Inertia::render('Customer/LandingPage', [
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => $product->image,
                    'category_id' => $product->category_id,
                    'average_rating' => round($product->average_rating ?? 0, 1),
                    'total_reviews' => $product->total_reviews ?? 0
                ];
            }),
            'categories' => $categories,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function shop(Request $request)
    {
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

        $products = $query->get();
        $categories = Category::all();

        return Inertia::render('Customer/ShopingPage', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'sortField', 'sortDirection', 'category'])
        ]);
    }

    public function product($id)
    {
        $product = Product::with(['sizes', 'colors'])->findOrFail($id);
        $categories = Category::all();

        // Get reviews directly
        $reviews = ProductReview::where('product_id', $id)
            ->select('rating')
            ->get();

        $avgRating = $reviews->avg('rating');
        $totalReviews = $reviews->count();

        return Inertia::render('Customer/Product', [
            'product' => $product,
            'categories' => $categories,
            'productRating' => [
                'average_rating' => round($avgRating, 1),
                'total_reviews' => $totalReviews,
                'ratings' => $reviews->pluck('rating')
            ]
        ]);
    }
}
