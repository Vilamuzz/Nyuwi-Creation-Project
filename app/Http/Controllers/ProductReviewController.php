<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProductReviewController extends Controller
{
    public function getUserReviews()
    {
        $reviews = ProductReview::with(['product', 'order'])
            ->where('user_id', Auth::id())
            ->get();

        return Inertia::render('Customer/Reviews', [
            'reviews' => $reviews
        ]);
    }

    public function getProductReviews($productId)
    {
        $reviews = ProductReview::where('product_id', $productId)
            ->select('rating')
            ->get();

        $avgRating = $reviews->avg('rating');
        $totalReviews = $reviews->count();

        return response()->json([
            'average_rating' => round($avgRating, 1),
            'total_reviews' => $totalReviews,
            'ratings' => $reviews->pluck('rating')
        ]);
    }
}
