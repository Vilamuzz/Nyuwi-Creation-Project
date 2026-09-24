<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShippingController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileStoreController;

// Public Routes

Route::get('/', [ProductController::class, 'landingPage'])->name('home');
Route::get('/new-featured', [ProductController::class, 'newFeaturedPage'])->name('new-featured');
Route::get('/boquets', [ProductController::class, 'boquetsPage'])->name('boquets');
Route::get('/flowers', [ProductController::class, 'flowersPage'])->name('flowers');
Route::get('/accessories', [ProductController::class, 'accessoriesPage'])->name('accessories');
Route::get('/bags', [ProductController::class, 'bagsPage'])->name('bags');
Route::get('/sale', [ProductController::class, 'salePage'])->name('sale');

Route::get('/product/{slug}', [ProductController::class, 'product'])->name('product');
Route::get('/api/products/search', [ProductController::class, 'apiSearch'])->name('products.api-search');

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

// Cart Management (available to guests via session-backed cart)
Route::controller(CartController::class)->prefix('cart')->name('cart.')->group(function () {
    Route::get('/', 'showCart')->name('show');
    Route::post('/', 'store')->name('store');
    Route::put('/{id}', 'update')->name('update');
    Route::delete('/{id}', 'destroy')->name('destroy');
});

// Checkout (available to guests and customers)
Route::get('/checkout', [CartController::class, 'showCheckout'])->middleware('check.cart')->name('checkout');
Route::post('/customer/orders/checkout', [OrderController::class, 'store'])->name('customer.orders.store');


// Authenticated Customer Routes
Route::middleware(['auth', 'customer'])->group(function () {
    // Customer Profile / Dashboard
    Route::get('/customer/profile', [OrderController::class, 'orderUser'])->name('customer.profile');

    // Customer Order Management
    Route::controller(OrderController::class)->prefix('customer/orders')->name('customer.orders.')->group(function () {
        Route::get('/', 'userOrders')->name('index');
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
        Route::get('/', 'index')->name('index');
        Route::put('/', 'update')->name('update');
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

require __DIR__ . '/auth.php';
