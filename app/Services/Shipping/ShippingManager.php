<?php

namespace App\Services\Shipping;

use App\Services\Shipping\Contracts\ShippingCalculatorInterface;
use App\Services\Shipping\Drivers\BinderByteShippingDriver;
use App\Services\Shipping\Drivers\MockShippingDriver;
use Illuminate\Support\Manager;

class ShippingManager extends Manager implements ShippingCalculatorInterface
{
    /**
     * Get the default shipping driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('shipping.default', 'binderbyte');
    }

    /**
     * Create an instance of the BinderByte shipping driver.
     */
    protected function createBinderbyteDriver(): ShippingCalculatorInterface
    {
        $config = $this->config->get('shipping.drivers.binderbyte', []);
        return new BinderByteShippingDriver($config);
    }

    /**
     * Create an instance of the RajaOngkir shipping driver.
     */
    protected function createRajaongkirDriver(): ShippingCalculatorInterface
    {
        $config = $this->config->get('shipping.drivers.rajaongkir', []);
        return new BinderByteShippingDriver($config);
    }

    /**
     * Create an instance of the Mock shipping driver.
     */
    protected function createMockDriver(): ShippingCalculatorInterface
    {
        $config = $this->config->get('shipping.drivers.mock', []);
        return new MockShippingDriver($config);
    }

    /**
     * {@inheritdoc}
     */
    public function calculate(string|int $origin, string|int $destination, int $weight, array $couriers = []): array
    {
        return $this->driver()->calculate($origin, $destination, $weight, $couriers);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedCouriers(): array
    {
        return $this->driver()->getSupportedCouriers();
    }

    /**
     * {@inheritdoc}
     */
    public function getCities(?string $search = null): array
    {
        return $this->driver()->getCities($search);
    }

    /**
     * {@inheritdoc}
     */
    public function track(string $courier, string $trackingNumber): array
    {
        return $this->driver()->track($courier, $trackingNumber);
    }
}
