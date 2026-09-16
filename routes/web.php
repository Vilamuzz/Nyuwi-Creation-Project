<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileStoreController;

use App\Http\Controllers\Auth\AdminRegistrationController;

// Public Routes
Route::middleware(['customer'])->group(function () {
    Route::get('/', [ProductController::class, 'landingPage'])->name('home');
    Route::get('/about', function () {
        return Inertia::render('Customer/About');
    })->name('about');
    Route::get('/shop', [ProductController::class, 'shopPage'])->name('shop');
    Route::get('/product/{slug}', [ProductController::class, 'product'])->name('product');

    // Region data (used by address forms)
    Route::controller(RegionController::class)->prefix('regions')->name('regions.')->group(function () {
        Route::get('/provinces', 'provinces')->name('provinces');
        Route::get('/regencies/{provinceId}', 'regencies')->name('regencies');
        Route::get('/districts/{regencyId}', 'districts')->name('districts');
        Route::get('/villages/{districtId}', 'villages')->name('villages');
        Route::get('/city/{cityName}', 'cityByName')->name('city');
        Route::get('/search', 'search')->name('search');
    });

    // Shipping calculation
    Route::post('/shipping/calculate', [ShippingController::class, 'calculate'])->name('shipping.calculate');
});

// Authenticated Customer Routes
Route::middleware(['auth', 'customer'])->group(function () {
    // Customer Profile / Dashboard
    Route::get('/customer/profile', [OrderController::class, 'orderUser'])->name('customer.profile');

    // Cart Management
    Route::controller(CartController::class)->prefix('cart')->name('cart.')->group(function () {
        Route::get('/', 'showCart')->name('show');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // Checkout
    Route::get('/checkout', [CartController::class, 'showCheckout'])->middleware('check.cart')->name('checkout');

    // Wishlist Management
    Route::controller(WishlistController::class)->prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // Customer Order Management
    Route::controller(OrderController::class)->prefix('customer/orders')->name('customer.orders.')->group(function () {
        Route::get('/', 'userOrders')->name('index');
        Route::post('/checkout', 'store')->name('store');
        Route::post('/complete', 'complete')->name('complete');
        Route::post('/upload-proof', 'uploadPaymentProof')->name('proof');
        Route::get('/tracking/{trackingNumber}', 'tracking')->name('tracking');
        Route::get('/{id}', 'userOrderDetail')->name('show');
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
    Route::controller(OrderController::class)->prefix('admin/orders')->name('admin.orders.')->group(function () {
        Route::get('/', 'show')->name('index');
        Route::get('/tracking/{trackingNumber}', 'adminTracking')->name('tracking');
        Route::get('/{id}', 'detail')->name('show');
        Route::put('/{id}', 'update')->name('update');
    });

    // Profile Store Setting
    Route::controller(ProfileStoreController::class)->prefix('profile-store')->name('profile-store.')->group(function () {
        Route::get('/{name}', 'edit')->name('edit');
        Route::put('/update/{name}', 'update')->name('update');
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
