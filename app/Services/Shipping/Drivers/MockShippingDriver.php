<?php

namespace App\Services\Shipping\Drivers;

use App\Services\Shipping\Contracts\ShippingCalculatorInterface;

class MockShippingDriver implements ShippingCalculatorInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function calculate(string|int $origin, string|int $destination, int $weight, array $couriers = []): array
    {
        if (empty($couriers)) {
            $couriers = ['jne', 'pos', 'tiki'];
        }

        $couriers = array_map('strtolower', $couriers);
        $weightMultiplier = max(1, (int) ceil($weight / 1000));

        $allRates = [
            [
                'courier' => 'jne',
                'service' => 'REG',
                'name' => 'JNE - REG (Layanan Reguler)',
                'description' => 'Layanan Reguler',
                'cost' => 18000 * $weightMultiplier,
                'etd' => '2-3 hari',
            ],
            [
                'courier' => 'jne',
                'service' => 'YES',
                'name' => 'JNE - YES (Yakin Esok Sampai)',
                'description' => 'Yakin Esok Sampai',
                'cost' => 32000 * $weightMultiplier,
                'etd' => '1 hari',
            ],
            [
                'courier' => 'pos',
                'service' => 'Pos Reguler',
                'name' => 'POS - Pos Reguler',
                'description' => 'Pos Reguler',
                'cost' => 15000 * $weightMultiplier,
                'etd' => '2-4 hari',
            ],
            [
                'courier' => 'tiki',
                'service' => 'REG',
                'name' => 'TIKI - REG (Regular Service)',
                'description' => 'Regular Service',
                'cost' => 19000 * $weightMultiplier,
                'etd' => '2-3 hari',
            ],
        ];

        return array_values(array_filter($allRates, function ($rate) use ($couriers) {
            return in_array($rate['courier'], $couriers, true);
        }));
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCities(?string $search = null): array
    {
        $cities = [
            ['id' => '444', 'name' => 'Kota Surabaya', 'city_name' => 'Surabaya', 'type' => 'Kota', 'province' => 'Jawa Timur', 'postal_code' => '60119'],
            ['id' => '152', 'name' => 'Kota Jakarta Pusat', 'city_name' => 'Jakarta Pusat', 'type' => 'Kota', 'province' => 'DKI Jakarta', 'postal_code' => '10110'],
            ['id' => '23', 'name' => 'Kota Bandung', 'city_name' => 'Bandung', 'type' => 'Kota', 'province' => 'Jawa Barat', 'postal_code' => '40111'],
            ['id' => '501', 'name' => 'Kota Yogyakarta', 'city_name' => 'Yogyakarta', 'type' => 'Kota', 'province' => 'DI Yogyakarta', 'postal_code' => '55111'],
            ['id' => '399', 'name' => 'Kota Semarang', 'city_name' => 'Semarang', 'type' => 'Kota', 'province' => 'Jawa Tengah', 'postal_code' => '50135'],
            ['id' => '256', 'name' => 'Kota Malang', 'city_name' => 'Malang', 'type' => 'Kota', 'province' => 'Jawa Timur', 'postal_code' => '65111'],
            ['id' => '114', 'name' => 'Kota Denpasar', 'city_name' => 'Denpasar', 'type' => 'Kota', 'province' => 'Bali', 'postal_code' => '80227'],
            ['id' => '278', 'name' => 'Kota Medan', 'city_name' => 'Medan', 'type' => 'Kota', 'province' => 'Sumatera Utara', 'postal_code' => '20111'],
        ];

        if (empty($search)) {
            return $cities;
        }

        $needle = strtolower(trim($search));

        return array_values(array_filter($cities, function ($city) use ($needle) {
            return str_contains(strtolower($city['name']), $needle)
                || str_contains(strtolower($city['city_name']), $needle)
                || str_contains(strtolower($city['province']), $needle);
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function track(string $courier, string $trackingNumber): array
    {
        return [
            'status' => 200,
            'message' => 'Successfully tracked package (mock)',
            'data' => [
                'summary' => [
                    'courier' => $courier,
                    'waybill_number' => $trackingNumber,
                    'service' => 'REG',
                    'status' => 'ON_PROCESS',
                    'date' => date('Y-m-d H:i:s'),
                    'weight' => '1.0',
                ],
                'detail' => [
                    'origin' => 'SURABAYA',
                    'destination' => 'JAKARTA',
                    'shipper' => 'Nyuwi Creation',
                    'receiver' => 'Customer',
                ],
                'history' => [
                    [
                        'date' => date('Y-m-d H:i:s'),
                        'desc' => 'Paket sedang dalam perjalanan menuju kota tujuan.',
                        'location' => 'Transit Hub Surabaya',
                    ],
                ],
            ],
        ];
    }
}
