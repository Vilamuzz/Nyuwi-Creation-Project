<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;

Route::get('/', function () {
    $products = Product::all();
    $categories = Category::all();
    return Inertia::render('Customer/LandingPage', [
        'products' => $products,
        'categories' => $categories,
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});



Route::get('/shop', [ProductController::class, 'shop'])->name('shop');

Route::get('/product/{id}', [ProductController::class, 'product'])->name('product');

Route::get('cart', function () {
    return Inertia::render('Customer/Cart');
});

Route::get('dashboard', function () {
    return Inertia::render('Admin/Dashboard');
})->name('dashboard');

Route::resource('inventory', ProductController::class)->names([
    'index' => 'products.index',
    'create' => 'products.create',
    'store' => 'products.store',
    'show' => 'products.show',
    'edit' => 'products.edit',
    'update' => 'products.update',
    'destroy' => 'products.destroy',
]);

Route::get('/checkout', function () {
    return Inertia::render('Customer/Checkout');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

use App\Http\Controllers\CartController;

// Cart routes
Route::middleware(['auth'])->group(function () {
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'showCart'])->name('cart.show');
});

require __DIR__ . '/auth.php';
