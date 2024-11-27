<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller
{
    public function getUserReviews()
    {
        $reviews = ProductReview::with(['product', 'order'])
            ->where('user_id', Auth::id())
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'product' => [
                        'id' => $review->product->id,
                        'name' => $review->product->name,
                        'image' => $review->product->image
                    ],
                    'order_id' => $review->order_id,
                    'rating' => $review->rating, // This is the numeric rating value
                    'created_at' => $review->created_at
                ];
            });

        return response()->json($reviews);
    }
}
