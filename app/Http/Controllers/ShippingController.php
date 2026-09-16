<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShippingCalculateRequest;
use Illuminate\Support\Facades\Http;

class ShippingController extends Controller
{
    public function calculate(ShippingCalculateRequest $request)
    {
        $validated = $request->validated();

        try {
            $response = Http::get('https://api.binderbyte.com/v1/cost', [
                'api_key' => config('services.binderbyte.api_key'),
                'courier' => $validated['courier'],
                'origin' => $validated['origin'],
                'destination' => $validated['destination'],
                'weight' => $validated['weight'],
                'volume' => $validated['volume'] ?? '100x100x100',
            ]);

            return back()->with('shippingResult', $response->json());

        } catch (\Exception $e) {
            return back()->withErrors([
                'shipping' => 'Gagal menghitung ongkos kirim: ' . $e->getMessage(),
            ]);
        }
    }
}
