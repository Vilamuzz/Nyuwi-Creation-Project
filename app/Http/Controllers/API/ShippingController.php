<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ShippingController extends Controller
{
    public function calculateShipping(Request $request)
    {
        $request->validate([
            'courier' => 'required|string',
            'origin' => 'required|string',
            'destination' => 'required|string',
            'weight' => 'required|numeric',
        ]);

        try {
            $response = Http::get('https://api.binderbyte.com/v1/cost', [
                'api_key' => env('BINDERBYTE_API_KEY'),
                'courier' => $request->courier,
                'origin' => $request->origin,
                'destination' => $request->destination,
                'weight' => $request->weight,
                'volume' => $request->volume ?? '100x100x100',
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch shipping rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
