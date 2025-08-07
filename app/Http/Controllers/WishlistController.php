<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WishlistController extends Controller
{
    public function index()
    {
        return Inertia::render('Customer/Wishlist');
    }
}
