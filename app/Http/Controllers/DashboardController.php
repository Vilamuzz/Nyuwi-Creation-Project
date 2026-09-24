<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Store Key Performance Indicators
        $totalRevenue = (int) Order::whereNotIn('status', ['cancelled', 'waiting'])->sum('total_price');
        $pendingOrdersCount = Order::whereIn('status', ['waiting', 'checking', 'processing'])->count();
        $totalProductsCount = Product::count();
        $lowStockCount = Product::where('stock', '<', 10)->count();

        // Recent Orders
        $recentOrders = Order::latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'name' => $order->name,
                    'total_price' => $order->total_price,
                    'status' => $order->status,
                    'payment_method' => $order->payment_method,
                    'created_at' => $order->created_at?->toISOString() ?? now()->toISOString(),
                ];
            });

        // Get top selling products
        $topSelling = Product::withCount(['orderItems as total_sold' => function ($query) {
            $query->whereHas('order', function ($q) {
                $q->whereNotIn('status', ['cancelled', 'waiting', 'checking']);
            });
        }])
            ->orderByDesc('total_sold')
            ->take(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'total_sold' => $product->total_sold,
                    'image' => $product->images && is_array($product->images) && count($product->images) > 0
                        ? $product->images[0]
                        : null
                ];
            });

        // Get most rated products
        $mostRated = Product::withCount('reviews as total_reviews')
            ->withAvg('reviews as average_rating', 'rating')
            ->orderByDesc('average_rating')
            ->take(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'total_reviews' => $product->total_reviews,
                    'average_rating' => round($product->average_rating ?? 0, 1),
                    'image' => $product->images && is_array($product->images) && count($product->images) > 0
                        ? $product->images[0]
                        : null
                ];
            });

        // Get low stock products (less than 10 items)
        $lowStock = Product::where('stock', '<', 10)
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image' => $product->images && is_array($product->images) && count($product->images) > 0
                        ? $product->images[0]
                        : null
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalRevenue' => $totalRevenue,
                'pendingOrdersCount' => $pendingOrdersCount,
                'totalProductsCount' => $totalProductsCount,
                'lowStockCount' => $lowStockCount,
            ],
            'recentOrders' => $recentOrders,
            'topSelling' => $topSelling,
            'mostRated' => $mostRated,
            'lowStock' => $lowStock
        ]);
    }
}
