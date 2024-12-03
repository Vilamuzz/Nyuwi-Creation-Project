<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Auth\AdminRegistrationController;
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

Route::get('/', [ProductController::class, 'landingPage']);

Route::get('/shop', [ProductController::class, 'shop'])->name('shop');

Route::get('/product/{id}', [ProductController::class, 'product'])->name('product');

Route::get('customer/profile', [OrderController::class, 'orderUser'])->middleware('auth')->name('customer.profile');

Route::get('dashboard', function () {
    return Inertia::render('Admin/Dashboard');
})->name('dashboard');

Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/about', function () {
    return Inertia::render('Customer/About');
})->name('about');

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
    Route::get('orders/info', [OrderController::class, 'getInfo'])->name('orders.info');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
});

Route::post('/checkout', [OrderController::class, 'store'])->name('order.store');
Route::get('/orders', [OrderController::class, 'show'])->name('orders.show');
Route::get('/orders/{id}', [OrderController::class, 'detail'])->name('orders.detail');
Route::put('/orders/{id}', [OrderController::class, 'update'])->name('orders.update');
Route::post('/orders/complete', [OrderController::class, 'complete'])->name('orders.complete');
Route::post('orders/upload-proof', [OrderController::class, 'uploadPaymentProof'])->name('orders.upload-proof');

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

Route::middleware(['auth'])->group(function () {
    // Add new middleware to check cart
    Route::get('/checkout', [CartController::class, 'showCheckout'])
        ->middleware('check.cart')
        ->name('checkout');
});

Route::middleware('guest')->group(function () {
    // Add new admin registration route
    Route::get('admin/register', [AdminRegistrationController::class, 'create'])
        ->name('admin.register');
    Route::post('admin/register', [AdminRegistrationController::class, 'store']);
});

require __DIR__ . '/auth.php';
