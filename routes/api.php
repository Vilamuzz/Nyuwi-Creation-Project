<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegionController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrderController;

// Public routes
Route::get('/provinces', [RegionController::class, 'getProvinces']);
Route::get('/regencies/{provinceId}', [RegionController::class, 'getRegencies']);
Route::get('/districts/{regencyId}', [RegionController::class, 'getDistricts']);
Route::get('/villages/{districtId}', [RegionController::class, 'getVillages']);

// Alternative routes for better naming
Route::get('/cities/{provinceId}', [RegionController::class, 'getRegencies']);

// Complete region data
Route::get('/regions', [RegionController::class, 'getCompleteRegion']);
Route::get('/regions/{provinceId}', [RegionController::class, 'getCompleteRegion']);
Route::get('/regions/{provinceId}/{regencyId}', [RegionController::class, 'getCompleteRegion']);
Route::get('/regions/{provinceId}/{regencyId}/{districtId}', [RegionController::class, 'getCompleteRegion']);

// Search regions
Route::get('/search/regions', [RegionController::class, 'searchRegions']);

// Product API routes
Route::get('/home', [ProductController::class, 'home']);
Route::get('/shop', [ProductController::class, 'shop']);
Route::get('/product/{slug}', [ProductController::class, 'product']);

// Protected cart routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Cart management
    Route::controller(OrderController::class)->prefix('cart')->group(function () {
        Route::get('/', 'cart'); // GET /api/cart
        Route::post('/add', 'addToCart'); // POST /api/cart/add
        Route::put('/{id}', 'updateCartItem'); // PUT /api/cart/{id}
        Route::delete('/{id}', 'removeFromCart'); // DELETE /api/cart/{id}
        Route::delete('/', 'clearCart'); // DELETE /api/cart
        Route::get('/count', 'cartCount'); // GET /api/cart/count
        Route::get('/validate', 'validateCart'); // GET /api/cart/validate
    });
});
