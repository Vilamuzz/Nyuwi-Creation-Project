<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Fix the logic order - check role first
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            // Return 403 status code for non-admin users
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
