<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShippingCalculateRequest;
use App\Models\ProfileStore;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingController extends Controller
{
    public function calculate(ShippingCalculateRequest $request, ShippingService $shippingService)
    {
        $validated = $request->validated();
        $profile = ProfileStore::first();

        $origin = $validated['origin']
            ?? $profile?->district_id
            ?? config('services.rajaongkir.origin');
        $destination = $validated['destination'];
        $weight = (int) $validated['weight'];

        if (! empty($validated['courier'])) {
            $couriers = [$validated['courier']];
        } else {
            $couriers = $profile ? $profile->getEnabledCouriers() : ['jne', 'pos', 'tiki'];
        }

        try {
            $rates = $shippingService->cost($origin, $destination, $weight, $couriers);

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
            Log::error('Shipping calculation error: '.$e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menghitung ongkos kirim: '.$e->getMessage(),
                    'rates' => [],
                ], 500);
            }

            return back()->withErrors([
                'shipping' => 'Gagal menghitung ongkos kirim: '.$e->getMessage(),
            ]);
        }
    }
}
