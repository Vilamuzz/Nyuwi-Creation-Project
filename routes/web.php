<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Auth\AdminRegistrationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Routes
Route::middleware(['customer'])->group(function () {
    Route::get('/', [ProductController::class, 'landingPage'])->name('home');
    Route::get('/about', function () {
        return Inertia::render('Customer/About');
    })->name('about');
    Route::get('/shop', function () {
        return Inertia::render('Customer/ShopingPage');
    })->name('shop');
    Route::get('/product/{slug}', [ProductController::class, 'product'])->name('product');
});

// Authenticated Customer Routes
Route::middleware(['auth', 'customer'])->group(function () {
    // Customer Profile
    Route::get('/customer/profile', [OrderController::class, 'orderUser'])->name('customer.profile');

    // Cart Management
    Route::controller(CartController::class)->prefix('cart')->name('cart.')->group(function () {
        Route::get('/', 'showCart')->name('show');
        Route::post('/add', 'addToCart')->name('add');
        Route::put('/{id}', 'updateCart')->name('update');
        Route::delete('/{id}', 'removeFromCart')->name('remove');
    });

    // Checkout
    Route::controller(OrderController::class)->group(function () {
        Route::get('/checkout', 'showCheckout')->middleware('check.cart')->name('checkout');
        Route::post('/checkout', 'store')->name('order.store');
    });

    // Wishlist Management
    Route::controller(WishlistController::class)->prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // Order Management
    Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
        Route::get('/info', 'getInfo')->name('info');
        Route::put('/{id}', 'update')->name('update');
        Route::post('/complete', 'complete')->name('complete');
        Route::post('/upload-proof', 'uploadPaymentProof')->name('upload-proof');
    });

    // Product Reviews
    Route::controller(ProductReviewController::class)->group(function () {
        Route::get('/user/reviews', 'getUserReviews')->name('user.reviews');
        Route::get('/products/{id}/reviews', 'getProductReviews')->name('products.reviews');
    });
});

// Admin Routes
Route::middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Product Management (Inventory)
    Route::resource('/inventory', ProductController::class)->names([
        'index' => 'products.index',
        'create' => 'products.create',
        'store' => 'products.store',
        'show' => 'products.show',
        'edit' => 'products.edit',
        'update' => 'products.update',
        'destroy' => 'products.destroy',
    ]);

    // Order Management (Admin)
    Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
        Route::get('/', 'show')->name('show');
        Route::get('/{id}', 'detail')->name('detail');
    });

    // Profile Management
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::patch('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });

    // Database Management
    Route::get('/data', function () {
        return Inertia::render('Admin/Database');
    })->name('data');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('admin/register', [AdminRegistrationController::class, 'create'])->name('admin.register');
    Route::post('admin/register', [AdminRegistrationController::class, 'store']);
});

require __DIR__ . '/auth.php';
