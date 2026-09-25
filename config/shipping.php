<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Shipping Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "binderbyte", "rajaongkir", "mock"
    |
    */

    'default' => env('SHIPPING_DRIVER', 'binderbyte'),

    /*
    |--------------------------------------------------------------------------
    | Shipping Drivers Configuration
    |--------------------------------------------------------------------------
    */

    'drivers' => [
        'binderbyte' => [
            'api_key' => env('BINDERBYTE_API_KEY'),
            'base_url' => env('BINDERBYTE_BASE_URL', 'https://api.binderbyte.com/v1'),
            'timeout' => (int) env('SHIPPING_TIMEOUT', 10),
        ],

        'rajaongkir' => [
            'api_key' => env('RAJAONGKIR_API_KEY'),
            'account_type' => env('RAJAONGKIR_ACCOUNT_TYPE', 'starter'),
            'base_url' => env('RAJAONGKIR_BASE_URL', 'https://api.rajaongkir.com/starter'),
            'timeout' => (int) env('SHIPPING_TIMEOUT', 10),
        ],

        'mock' => [
            'rates' => [
                [
                    'courier' => 'jne',
                    'service' => 'REG',
                    'name' => 'JNE - Layanan Reguler',
                    'description' => 'Layanan Reguler',
                    'cost' => 18000,
                    'etd' => '2-3',
                ],
                [
                    'courier' => 'jne',
                    'service' => 'YES',
                    'name' => 'JNE - Yakin Esok Sampai',
                    'description' => 'Yakin Esok Sampai',
                    'cost' => 32000,
                    'etd' => '1-1',
                ],
                [
                    'courier' => 'pos',
                    'service' => 'Pos Reguler',
                    'name' => 'POS - Pos Reguler',
                    'description' => 'Pos Reguler',
                    'cost' => 15000,
                    'etd' => '2-4',
                ],
                [
                    'courier' => 'tiki',
                    'service' => 'REG',
                    'name' => 'TIKI - Regular Service',
                    'description' => 'Regular Service',
                    'cost' => 19000,
                    'etd' => '2-3',
                ],
            ],
        ],
    ],
];
