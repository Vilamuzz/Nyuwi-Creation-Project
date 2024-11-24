<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::get();
        $categories = Category::all();
        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => $categories
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
            'new_category' => 'nullable|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Buat kategori baru jika 'new_category' diisi
        if ($request->filled('new_category')) {
            $category = Category::create(['name' => $request->new_category]);
            $validatedData['category_id'] = $category->id;
        }

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::exists("public/{$product->image}")) {
                Storage::delete("public/{$product->image}");
            }

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('products', $imageName, 'public');
            $validatedData['image'] = $imageName;
        }

        $product->update([
            'name' => $validatedData['name'],
            'stock' => $validatedData['stock'],
            'price' => $validatedData['price'],
            'category_id' => $validatedData['category_id'],
            'description' => $request->description,
            'image' => $validatedData['image'] ?? $product->image
        ]);

        // Update colors
        $product->colors()->delete(); // Remove old colors
        if ($request->colors) {
            foreach ($request->colors as $color) {
                $product->colors()->create(['color' => $color]);
            }
        }

        // Update sizes
        $product->sizes()->delete(); // Remove old sizes
        if ($request->sizes) {
            foreach ($request->sizes as $size) {
                $product->sizes()->create(['size' => $size]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function shop()
    {
        $products = Product::get();
        $categories = Category::all();
        return Inertia::render('Customer/ShopingPage', [
            'products' => $products,
            'categories' => $categories
        ]);
    }
    public function product($id)
    {
        $product = Product::with(['sizes', 'colors'])->findOrFail($id);
        $categories = Category::all();

        return Inertia::render('Customer/Product', [
            'product' => $product,
            'categories' => $categories
        ]);
    }
}
