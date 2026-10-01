<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    private function client()
    {
        return Http::baseUrl(config('services.rajaongkir.url'))
            ->withHeaders(['key' => config('services.rajaongkir.key')])
            ->acceptJson()->timeout(10);
    }

    private function cached(string $key, string $path)
    {
        return Cache::remember(
            $key,
            now()->addDays(30),
            fn() => $this->client()->get($path)->throw()->json('data')
        );
    }

    public function provinces()
    {
        return $this->map($this->cached('ro:prov', 'destination/province'), 'province_id', 'province_name');
    }

    public function cities($provinceId)
    {
        return $this->map($this->cached("ro:city:$provinceId", "destination/city/$provinceId"), 'city_id', 'city_name');
    }

    public function districts($cityId)
    {
        return $this->map($this->cached("ro:dist:$cityId", "destination/district/$cityId"), 'subdistrict_id', 'subdistrict_name');
    }

    private function map(array $rows, string $idKey, string $nameKey): array
    {
        return array_map(fn(array $row) => [
            'id' => (string) ($row[$idKey] ?? $row['id'] ?? ''),
            'name' => (string) ($row[$nameKey] ?? $row['name'] ?? ''),
        ], $rows);
    }

    public function cost(int|string $originDistrictId, int|string $destinationDistrictId, int $weightGram, array|string $couriers): array
    {
        $courierList = is_array($couriers) ? $couriers : [$couriers];
        $allRates = [];

        foreach ($courierList as $courierCode) {
            $code = strtolower(trim((string) $courierCode));
            if (empty($code)) continue;

            try {
                $response = $this->client()->asForm()->post('calculate/district/domestic-cost', [
                    'origin' => (int) $originDistrictId,
                    'destination' => (int) $destinationDistrictId,
                    'weight' => $weightGram,
                    'courier' => $code,
                    'price' => 'lowest',
                ]);

                if ($response->successful()) {
                    $items = $response->json('data') ?? [];
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            $allRates[] = [
                                'courier' => $item['code'] ?? $code,
                                'name' => $item['name'] ?? strtoupper($code),
                                'service' => $item['service'] ?? '',
                                'description' => $item['description'] ?? '',
                                'cost' => (float) ($item['cost'] ?? 0),
                                'etd' => $item['etd'] ?? '',
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to fetch shipping rate for courier {$code}: " . $e->getMessage());
            }
        }

        return $allRates;
    }

    public function track(string $courier, string $trackingNumber): array
    {
        $apiKey = config('services.binderbyte.api_key');

        if (empty($apiKey)) {
            return [
                'status' => 400,
                'message' => 'BinderByte API Key belum dikonfigurasi.',
                'data' => null,
            ];
        }

        $baseUrl = rtrim((string) config('services.binderbyte.base_url', 'https://api.binderbyte.com/v1'), '/');
        $timeout = (int) config('services.binderbyte.timeout', 10);
        $cleanCourier = $this->normalizeCourierCode($courier);

        try {
            $response = Http::timeout($timeout)->get("{$baseUrl}/track", [
                'api_key' => $apiKey,
                'courier' => $cleanCourier,
                'awb' => $trackingNumber,
            ]);

            return $response->json() ?? [
                'status' => $response->status(),
                'message' => 'Respon tidak valid dari server pelacakan.',
            ];
        } catch (\Throwable $e) {
            Log::error('BinderByte tracking error: ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'Gagal melacak resi pengiriman: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    private function normalizeCourierCode(string $courier): string
    {
        $code = strtolower(trim($courier));
        if (str_contains($code, ' - ')) {
            $parts = explode(' - ', $code);
            $code = trim($parts[0]);
        }

        return preg_replace('/[^a-z0-9]/', '', $code);
    }
}
