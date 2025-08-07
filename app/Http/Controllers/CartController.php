<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CartController extends Controller
{



    /**
     * Menampilkan keranjang belanja.
     */
    public function showCart()
    {
        return Inertia::render('Customer/Cart');
    }

    public function showCheckout()
    {
        return Inertia::render('Customer/Checkout');
    }
}
