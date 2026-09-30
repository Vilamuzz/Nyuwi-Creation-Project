<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileStoreController;
use App\Http\Controllers\ShippingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::prefix('locations')->group(function () {
    Route::get('provinces', [LocationController::class, 'provinces']);
    Route::get('provinces/{id}/cities', [LocationController::class, 'cities']);
    Route::get('cities/{id}/districts', [LocationController::class, 'districts']);
    Route::post('shipping-cost', [LocationController::class, 'cost']);
});

// Shipping calculation & helpers
Route::post('/shipping/calculate', [ShippingController::class, 'calculate'])->name('shipping.calculate');
Route::get('/shipping/cities', [ShippingController::class, 'cities'])->name('shipping.cities');
Route::get('/shipping/couriers', [ShippingController::class, 'couriers'])->name('shipping.couriers');

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
Route::post('/orders/{order}/payment', [PaymentController::class, 'createTransaction'])->name('orders.payment');

// Midtrans Webhook Notification
Route::post('/webhooks/midtrans', [MidtransWebhookController::class, 'handle'])->name('webhooks.midtrans');

// Authenticated Customer Routes
Route::middleware(['auth', 'customer'])->group(function () {
    // Customer Profile / Dashboard
    Route::get('/customer/profile', [OrderController::class, 'orderUser'])->name('customer.profile');

    // Customer Order Management
    Route::controller(OrderController::class)->prefix('customer/orders')->name('customer.orders.')->group(function () {
        Route::get('/', 'userOrders')->name('index');
        Route::post('/complete', 'complete')->name('complete');
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

require __DIR__.'/auth.php';
