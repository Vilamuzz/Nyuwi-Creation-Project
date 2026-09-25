<?php

namespace App\Services\Shipping\Contracts;

interface ShippingCalculatorInterface
{
    /**
     * Calculate shipping rates from origin to destination for the given couriers and weight.
     *
     * @param string|int $origin Origin city ID or name
     * @param string|int $destination Destination city ID or name
     * @param int $weight Weight in grams
     * @param array<int, string> $couriers List of courier codes to calculate (e.g. ['jne', 'pos', 'tiki'])
     * @return array<int, array{courier: string, service: string, name: string, description: string, cost: int, etd: string}>
     */
    public function calculate(string|int $origin, string|int $destination, int $weight, array $couriers = []): array;

    /**
     * Get the couriers supported by this provider.
     *
     * @return array<string, string> Key is courier code, value is display label
     */
    public function getSupportedCouriers(): array;

    /**
     * Get list of cities/locations supported by this provider.
     *
     * @param string|null $search Optional search keyword
     * @return array<int, array{id: string|int, name: string, type: string, province: string, postal_code: string|null}>
     */
    public function getCities(?string $search = null): array;

    /**
     * Track a shipment by courier code and airway bill / tracking number.
     *
     * @param string $courier Courier code (e.g. 'jne', 'sicepat', 'jnt')
     * @param string $trackingNumber Airway bill number (AWB) / Resi
     * @return array<string, mixed>
     */
    public function track(string $courier, string $trackingNumber): array;
}
