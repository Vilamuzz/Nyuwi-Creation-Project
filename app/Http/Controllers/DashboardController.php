<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Inertia\Inertia;

class DashboardController extends Controller
{
    //

    public function index()
    {
        // Get top selling products
        $topSelling = Product::withCount(['orderItems as total_sold' => function ($query) {
            $query->whereHas('order', function ($q) {
                $q->whereNotIn('status', ['cancelled', 'waiting', 'checking']);
            });
        }])
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        // Get most rated products
        $mostRated = Product::withCount('reviews as total_reviews')
            ->withAvg('reviews as average_rating', 'rating')
            ->orderByDesc('average_rating')
            ->take(5)
            ->get();

        // Get low stock products (less than 10 items)
        $lowStock = Product::where('stock', '<', 10)
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'topSelling' => $topSelling,
            'mostRated' => $mostRated,
            'lowStock' => $lowStock
        ]);
    }
}
