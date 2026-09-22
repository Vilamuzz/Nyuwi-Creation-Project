<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\CartService;
use Symfony\Component\HttpFoundation\Response;

class EnsureCartNotEmpty
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hasItems = (new CartService())->items($request) !== [];

        if (!$hasItems) {
            return redirect()->route('cart.show')
                ->with('error', 'Keranjang belanja kosong. Silakan tambahkan produk terlebih dahulu.');
        }

        return $next($request);
    }
}
