<?php

namespace App\Services\Shipping\Drivers;

use App\Models\Regency;
use App\Services\Shipping\Contracts\ShippingCalculatorInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingDriver implements ShippingCalculatorInterface
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? config('services.binderbyte.api_key');
        $this->baseUrl = rtrim($config['base_url'] ?? config('services.binderbyte.base_url', 'https://api.binderbyte.com/v1'), '/');
        $this->timeout = (int) ($config['timeout'] ?? 10);
    }

    /**
     * {@inheritdoc}
     */
    public function calculate(string|int $origin, string|int $destination, int $weight, array $couriers = []): array
    {
        if (empty($this->apiKey)) {
            Log::warning('BinderByte API key is not configured.');
            return [];
        }

        if (empty($couriers)) {
            $couriers = ['jne', 'pos', 'tiki'];
        }

        $rates = [];
        $weightInKg = max(1, (int) ceil($weight / 1000));

        foreach ($couriers as $courier) {
            $courierCode = $this->normalizeCourierCode($courier);
            if (!array_key_exists($courierCode, $this->getSupportedCouriers())) {
                continue;
            }

            try {
                $response = Http::timeout($this->timeout)->get("{$this->baseUrl}/cost", [
                    'api_key' => $this->apiKey,
                    'courier' => $courierCode,
                    'origin' => (string) $origin,
                    'destination' => (string) $destination,
                    'weight' => $weightInKg,
                ]);

                if (!$response->successful()) {
                    Log::warning("BinderByte cost calculation failed for {$courierCode}", [
                        'status' => $response->status(),
                        'body' => $response->json(),
                    ]);
                    continue;
                }

                $data = $response->json();
                $costs = $data['data']['costs'] ?? $data['costs'] ?? [];

                foreach ($costs as $costItem) {
                    $service = $costItem['service'] ?? '';
                    $description = $costItem['description'] ?? '';
                    $costValue = (int) ($costItem['cost'] ?? $costItem['tarif'] ?? $costItem['price'] ?? 0);
                    $etd = !empty($costItem['etd']) ? trim((string) $costItem['etd']) : '-';

                    if (!str_contains(strtolower($etd), 'hari') && $etd !== '-') {
                        $etd .= ' hari';
                    }

                    $courierUpper = strtoupper($courierCode);

                    $rates[] = [
                        'courier' => $courierCode,
                        'service' => $service,
                        'name' => "{$courierUpper} - {$service}" . ($description ? " ({$description})" : ''),
                        'description' => $description,
                        'cost' => $costValue,
                        'etd' => $etd,
                    ];
                }
            } catch (\Throwable $e) {
                Log::error("BinderByte calculation exception for {$courierCode}: " . $e->getMessage());
            }
        }

        return $rates;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedCouriers(): array
    {
        return [
            'jne' => 'JNE Express',
            'pos' => 'POS Indonesia',
            'tiki' => 'TIKI',
            'sicepat' => 'SiCepat Ekspres',
            'jnt' => 'J&T Express',
            'wahana' => 'Wahana Prestasi Logistik',
            'anteraja' => 'Anteraja',
            'ninja' => 'Ninja Xpress',
            'lion' => 'Lion Parcel',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCities(?string $search = null): array
    {
        return Cache::remember('binderbyte_cities_' . md5($search ?? 'all'), 86400, function () use ($search) {
            // First check if BinderByte list_city endpoint is available
            if (!empty($this->apiKey)) {
                try {
                    $response = Http::timeout($this->timeout)->get("{$this->baseUrl}/list_city", [
                        'api_key' => $this->apiKey,
                    ]);

                    if ($response->successful()) {
                        $results = $response->json()['data'] ?? [];
                        if (!empty($results)) {
                            $mapped = array_map(function ($item) {
                                return [
                                    'id' => (string) ($item['id'] ?? $item['city_id'] ?? $item['name'] ?? ''),
                                    'name' => (string) ($item['name'] ?? $item['city_name'] ?? ''),
                                    'city_name' => (string) ($item['name'] ?? $item['city_name'] ?? ''),
                                    'type' => $item['type'] ?? 'Kota',
                                    'province' => $item['province'] ?? '',
                                    'postal_code' => $item['postal_code'] ?? null,
                                ];
                            }, $results);

                            if (!empty($search)) {
                                $needle = strtolower(trim($search));
                                return array_values(array_filter($mapped, fn($c) => str_contains(strtolower($c['name']), $needle)));
                            }

                            return $mapped;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::debug('BinderByte list_city not available, falling back to local regencies: ' . $e->getMessage());
                }
            }

            // Fallback to local Indonesian regencies
            $query = Regency::query()->with('province');
            if (!empty($search)) {
                $query->where('name', 'LIKE', "%{$search}%");
            }

            return $query->limit(50)->get()->map(function ($regency) {
                return [
                    'id' => (string) $regency->id,
                    'name' => $regency->name,
                    'city_name' => $regency->name,
                    'type' => str_starts_with(strtoupper($regency->name), 'KOTA') ? 'Kota' : 'Kabupaten',
                    'province' => $regency->province?->name ?? '',
                    'postal_code' => null,
                ];
            })->all();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function track(string $courier, string $trackingNumber): array
    {
        if (empty($this->apiKey)) {
            return [
                'status' => 400,
                'message' => 'BinderByte API Key belum dikonfigurasi.',
                'data' => null,
            ];
        }

        $cleanCourier = $this->normalizeCourierCode($courier);

        try {
            $response = Http::timeout($this->timeout)->get("{$this->baseUrl}/track", [
                'api_key' => $this->apiKey,
                'courier' => $cleanCourier,
                'awb' => $trackingNumber,
            ]);

            return $response->json() ?? [
                'status' => $response->status(),
                'message' => 'Respon tidak valid dari server pelacakan.',
            ];
        } catch (\Throwable $e) {
            Log::error("BinderByte tracking error: " . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'Gagal melacak resi pengiriman: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Extract standardized courier code.
     */
    public function normalizeCourierCode(string $courier): string
    {
        $code = strtolower(trim($courier));
        if (str_contains($code, ' - ')) {
            $parts = explode(' - ', $code);
            $code = trim($parts[0]);
        }
        return preg_replace('/[^a-z0-9]/', '', $code);
    }
}
