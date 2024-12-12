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

// Public Routes (Add customer middleware to prevent admin access)
Route::middleware(['customer'])->group(function () {
    Route::get('/', [ProductController::class, 'landingPage']);
    Route::get('/about', function () {
        return Inertia::render('Customer/About');
    })->name('about');
    Route::get('/shop', [ProductController::class, 'shop'])->name('shop');
    Route::get('/product/{id}', [ProductController::class, 'product'])->name('product');
});

// Customer Only Routes
Route::middleware(['auth', 'customer'])->group(function () {
    // Customer Profile
    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('profile', [OrderController::class, 'orderUser'])->name('profile');
    });

    // Cart Routes
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'showCart'])->name('show');
        Route::post('/add', [CartController::class, 'addToCart'])->name('add');
        Route::put('/{id}', [CartController::class, 'updateCart'])->name('update');
        Route::delete('/{id}', [CartController::class, 'removeFromCart'])->name('remove');
    });

    // Checkout Route
    Route::get('/checkout', [CartController::class, 'showCheckout'])
        ->middleware('check.cart')
        ->name('checkout');

    // Wishlist Routes
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/', [WishlistController::class, 'store'])->name('store');
        Route::delete('/{id}', [WishlistController::class, 'destroy'])->name('destroy');
    });
});

// Admin Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('inventory', ProductController::class)->names([
        'index' => 'products.index',
        'create' => 'products.create',
        'store' => 'products.store',
        'show' => 'products.show',
        'edit' => 'products.edit',
        'update' => 'products.update',
        'destroy' => 'products.destroy',
    ]);
    Route::get('/orders', [OrderController::class, 'show'])->name('orders.show');
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('orders/info', [OrderController::class, 'getInfo'])->name('orders.info');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Protected routes that require email verification
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Add other routes that need verification
});

Route::post('/checkout', [OrderController::class, 'store'])->name('order.store');

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

Route::middleware('guest')->group(function () {
    // Add new admin registration route
    Route::get('admin/register', [AdminRegistrationController::class, 'create'])
        ->name('admin.register');
    Route::post('admin/register', [AdminRegistrationController::class, 'store']);
});

require __DIR__ . '/auth.php';
