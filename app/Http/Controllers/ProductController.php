<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\ProductReview;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

        $products = $query->paginate(10);

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
        $rules = [
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:1',
            'price' => 'required|numeric|min:1',
            'weight' => 'required|numeric|min:1',
            'category_id' => 'nullable|integer|exists:categories,id',
            'new_category' => 'nullable|string|max:255',
            'description' => 'required|string',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'sizes' => 'nullable|array',
            'sizes.*' => 'string|max:50',
            'colors' => 'nullable|array',
            'colors.*' => 'string|max:50',
        ];

        $validatedData = $request->validate($rules);

        // Create new category if 'new_category' is filled
        if ($request->filled('new_category')) {
            $category = Category::create(['name' => $request->new_category]);
            $validatedData['category_id'] = $category->id;
        }

        // Handle multiple image uploads
        $imageNames = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('products', $imageName, 'public');
                $imageNames[] = $imageName;
            }
        }

        $productData = [
            'name' => $validatedData['name'],
            'slug' => Str::slug($validatedData['name']),
            'stock' => $validatedData['stock'],
            'price' => $validatedData['price'],
            'weight' => $validatedData['weight'],
            'category_id' => $validatedData['category_id'],
            'description' => $validatedData['description'],
            'images' => $imageNames,
            'sizes' => $request->sizes,
            'colors' => $request->colors,
        ];

        $product = Product::create($productData);

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
        $product = Product::findOrFail($id);
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

        $rules = [
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:1',
            'price' => 'required|numeric|min:1',
            'weight' => 'required|numeric|min:1',
            'category_id' => 'nullable|integer|exists:categories,id',
            'new_category' => 'nullable|string|max:255',
            'description' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'colors' => 'nullable|array',
            'colors.*' => 'string|max:50',
            'sizes' => 'nullable|array',
            'sizes.*' => 'string|max:50',
        ];

        $validatedData = $request->validate($rules);

        // Create new category if 'new_category' is filled
        if ($request->filled('new_category')) {
            $category = Category::create(['name' => $request->new_category]);
            $validatedData['category_id'] = $category->id;
        }

        $updateData = [
            'name' => $validatedData['name'],
            'slug' => Str::slug($validatedData['name']),
            'stock' => $validatedData['stock'],
            'price' => $validatedData['price'],
            'weight' => $validatedData['weight'],
            'category_id' => $validatedData['category_id'],
            'description' => $validatedData['description'],
            'sizes' => $request->sizes ?? [],
            'colors' => $request->colors ?? [],
        ];

        // Handle image updates - only if new images are uploaded
        if ($request->hasFile('images')) {
            // Delete old images
            if ($product->images && is_array($product->images)) {
                foreach ($product->images as $oldImage) {
                    if (Storage::disk('public')->exists("products/{$oldImage}")) {
                        Storage::disk('public')->delete("products/{$oldImage}");
                    }
                }
            }

            // Store new images
            $imageNames = [];
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('products', $imageName, 'public');
                $imageNames[] = $imageName;
            }
            $updateData['images'] = $imageNames;
        }

        $product->update($updateData);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Check if product has any pending or active orders
        $hasActiveOrders = OrderItem::whereHas('order', function ($query) {
            $query->whereNotIn('status', ['completed', 'cancelled']);
        })->where('product_id', $id)->exists();

        if ($hasActiveOrders) {
            return redirect()->route('products.index')
                ->with('error', 'Cannot delete product that has active orders');
        }

        // Store images before deletion
        $images = $product->images;

        // Delete the product from database
        $product->delete();

        // Delete images after successful database deletion
        if ($images) {
            foreach ($images as $imageName) {
                $imagePath = "products/{$imageName}";
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully');
    }

    public function landingPage()
    {
        return Inertia::render('Customer/LandingPage', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    /**
     * Show the shop page
     */
    public function shopPage()
    {
        return Inertia::render('Customer/ShopingPage');
    }

    public function product()
    {
        return Inertia::render('Customer/Product');
    }
}
