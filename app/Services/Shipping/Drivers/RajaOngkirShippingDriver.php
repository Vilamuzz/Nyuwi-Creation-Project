<?php

namespace App\Services\Shipping\Drivers;

use App\Models\Regency;
use App\Services\Shipping\Contracts\ShippingCalculatorInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirShippingDriver implements ShippingCalculatorInterface
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

    
}
