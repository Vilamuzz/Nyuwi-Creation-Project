<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShippingCalculateRequest;
use App\Models\ProfileStore;
use App\Services\Shipping\ShippingManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingController extends Controller
{
    /**
     * Calculate shipping rates for checkout.
     */
    public function calculate(ShippingCalculateRequest $request, ShippingManager $shippingManager)
    {
        $validated = $request->validated();
        $profile = ProfileStore::first();

        $origin = $validated['origin']
            ?? $profile?->shipping_origin_district_id
            ?? $profile?->shipping_origin_city_id
            ?? $profile?->city
            ?? 'Surabaya';
        $destination = $validated['destination'];
        $weight = (int) $validated['weight'];

        if (!empty($validated['courier'])) {
            $couriers = [$validated['courier']];
        } else {
            $couriers = $profile ? $profile->getEnabledCouriers() : ['jne', 'pos', 'tiki'];
        }

        try {
            $rates = $shippingManager->calculate($origin, $destination, $weight, $couriers);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'rates' => $rates,
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                ]);
            }

            return back()
                ->with('shippingResult', $rates)
                ->with('shippingRates', $rates);
        } catch (\Throwable $e) {
            Log::error('Shipping calculation error: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menghitung ongkos kirim: ' . $e->getMessage(),
                    'rates' => [],
                ], 500);
            }

            return back()->withErrors([
                'shipping' => 'Gagal menghitung ongkos kirim: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get available shipping cities/locations.
     */
    public function cities(Request $request, ShippingManager $shippingManager): JsonResponse
    {
        $cities = $shippingManager->getCities($request->query('q'));

        return response()->json([
            'status' => 'success',
            'data' => $cities,
        ]);
    }

    /**
     * Get supported couriers by the active provider.
     */
    public function couriers(ShippingManager $shippingManager): JsonResponse
    {
        $couriers = $shippingManager->getSupportedCouriers();

        return response()->json([
            'status' => 'success',
            'data' => $couriers,
        ]);
    }
}
