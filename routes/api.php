<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegionController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\ShippingController;

// Public routes
Route::get('/provinces', [RegionController::class, 'getProvinces']);
Route::get('/regencies/{provinceId}', [RegionController::class, 'getRegencies']);
Route::get('/districts/{regencyId}', [RegionController::class, 'getDistricts']);
Route::get('/villages/{districtId}', [RegionController::class, 'getVillages']);

// Search regions
Route::get('/search/regions', [RegionController::class, 'searchRegions']);

// Product API routes
Route::get('/home', [ProductController::class, 'home']);
Route::get('/shop', [ProductController::class, 'shop']);
Route::get('/product/{slug}', [ProductController::class, 'product']);

// Protected cart routes (require authentication)
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

    // Shipping calculation
    Route::get('/shipping/calculate', [ShippingController::class, 'calculateShipping']);
});
