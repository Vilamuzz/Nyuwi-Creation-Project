<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Order;

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

Route::get('/about', [ProductController::class, 'about'])->name('about');

Route::get('/shop', [ProductController::class, 'shop'])->name('shop');

Route::get('/product/{id}', [ProductController::class, 'product'])->name('product');

Route::get('cart', function () {
    return Inertia::render('Customer/Cart');
});
Route::get('customer/profile', function () {
    $orders = Order::with(['orderItems.product'])
        ->where('user_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->get();

    return Inertia::render('Customer/Dashboard', [
        'orders' => $orders
    ]);
})->middleware('auth');

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

Route::resource('inventory', ProductController::class)->names([
    'index' => 'products.index',
    'create' => 'products.create',
    'store' => 'products.store',
    'show' => 'products.show',
    'edit' => 'products.edit',
    'update' => 'products.update',
    'destroy' => 'products.destroy',
]);

Route::get('/checkout', [CartController::class, 'showCheckout'])->name('checkout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Cart routes
Route::middleware(['auth'])->group(function () {
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'showCart'])->name('cart.show');
    Route::delete('/cart/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::put('/cart/{id}', [CartController::class, 'updateCart'])->name('cart.update');
});

Route::post('/checkout', [OrderController::class, 'store'])->name('order.store');
Route::get('/orders', [OrderController::class, 'show'])->name('orders.show');
Route::get('/orders/{id}', [OrderController::class, 'detail'])->name('orders.detail');
Route::put('/orders/{id}', [OrderController::class, 'update'])->name('orders.update');
Route::post('/orders/complete', [OrderController::class, 'complete'])->name('orders.complete');

Route::get('/user/reviews', [ProductReviewController::class, 'getUserReviews'])
    ->middleware(['auth'])
    ->name('user.reviews');

Route::get('/products/{id}/reviews', [ProductReviewController::class, 'getProductReviews'])
    ->name('products.reviews');

Route::get('/api/provinces', function () {
    return Province::all();
});

Route::get('/api/cities/{provinceId}', function ($provinceId) {
    $province = Province::findOrFail($provinceId);
    return $province->regencies;
});

Route::get('/api/districts/{cityId}', function ($cityId) {
    $city = Regency::findOrFail($cityId);
    return $city->districts;
});

Route::get('/api/villages/{districtId}', function ($districtId) {
    $district = District::findOrFail($districtId);
    return $district->villages;
});

require __DIR__ . '/auth.php';
