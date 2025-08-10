<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegionController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\ShippingController;
use App\Http\Controllers\API\OrderController; // Add this import

// Public routes
Route::get('/provinces', [RegionController::class, 'getProvinces']);
Route::get('/regencies/{provinceId}', [RegionController::class, 'getRegencies']);
Route::get('/districts/{regencyId}', [RegionController::class, 'getDistricts']);
Route::get('/villages/{districtId}', [RegionController::class, 'getVillages']);
Route::get('/city/{cityName}', [RegionController::class, 'getCityByName']);
Route::get('/search/regions', [RegionController::class, 'searchRegions']);

// Product API routes
Route::get('/home', [ProductController::class, 'home']);
Route::get('/shop', [ProductController::class, 'shop']);
Route::get('/product/{slug}', [ProductController::class, 'product']);

// Protected routes (require authentication)
Route::middleware(['web', 'auth', 'customer'])->group(function () {
    // Cart management
    Route::controller(CartController::class)->prefix('cart')->group(function () {
        Route::get('/', 'cart');
        Route::post('/add', 'addToCart');
        Route::put('/{id}', 'updateCartItem');
        Route::delete('/{id}', 'removeFromCart');
        Route::get('/count', 'cartCount');
        Route::get('/validate', 'validateCart');
    });

    // Wishlist management
    Route::controller(WishlistController::class)->prefix('wishlist')->group(function () {
        Route::get('/', 'index');
        Route::post('/add', 'store');
        Route::delete('/{id}', 'destroy');
        Route::get('/count', 'count');
    });

    // Order management - Add these new routes
    Route::controller(OrderController::class)->prefix('orders')->group(function () {
        Route::get('/', 'getUserOrders');
        Route::get('/{orderId}', 'getOrderDetails');
        Route::post('/complete', 'completeOrder');
        Route::get('/{orderId}/status', 'checkOrderStatus');
    });

    // Tracking information
    Route::get('/tracking/{trackingNumber}', [OrderController::class, 'getTracking']);

    // Shipping calculation
    Route::get('/shipping/calculate', [ShippingController::class, 'calculateShipping']);
});
