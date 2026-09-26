<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ProfileStore;
use App\Models\User;
use App\Services\Shipping\Contracts\ShippingCalculatorInterface;
use App\Services\Shipping\Drivers\BinderByteShippingDriver;
use App\Services\Shipping\Drivers\MockShippingDriver;
use App\Services\Shipping\ShippingManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShippingCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipping_manager_resolves_configured_driver(): void
    {
        Config::set('shipping.default', 'mock');
        $manager = app(ShippingManager::class);

        $this->assertInstanceOf(ShippingCalculatorInterface::class, $manager);
        $this->assertInstanceOf(MockShippingDriver::class, $manager->driver());

        Config::set('shipping.default', 'binderbyte');
        Config::set('shipping.drivers.binderbyte.api_key', 'test-binderbyte-key');
        $manager = new ShippingManager(app());
        $this->assertInstanceOf(ShippingDriver::class, $manager->driver());

        Config::set('shipping.default', 'rajaongkir');
        Config::set('shipping.drivers.rajaongkir.api_key', 'test-key');
        $manager = new ShippingManager(app());
        $this->assertInstanceOf(BinderByteShippingDriver::class, $manager->driver());
    }

    public function test_mock_driver_calculates_rates_and_filters_by_courier(): void
    {
        $driver = new MockShippingDriver();

        // Calculate for only JNE and POS
        $rates = $driver->calculate('Surabaya', 'Jakarta', 1500, ['jne', 'pos']);

        $this->assertNotEmpty($rates);
        $couriers = array_unique(array_column($rates, 'courier'));
        $this->assertContains('jne', $couriers);
        $this->assertContains('pos', $couriers);
        $this->assertNotContains('tiki', $couriers);

        // Rates are standardized
        $first = $rates[0];
        $this->assertArrayHasKey('courier', $first);
        $this->assertArrayHasKey('service', $first);
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('cost', $first);
        $this->assertArrayHasKey('etd', $first);
        $this->assertGreaterThan(0, $first['cost']);
    }

    public function test_rajaongkir_driver_formats_api_response(): void
    {
        Config::set('services.rajaongkir.api_key', 'mock-rajaongkir-key');

        Http::fake([
            'https://api.rajaongkir.com/starter/city' => Http::response([
                'rajaongkir' => [
                    'status' => ['code' => 200, 'description' => 'OK'],
                    'results' => [
                        [
                            'city_id' => '444',
                            'province_id' => '11',
                            'province' => 'Jawa Timur',
                            'type' => 'Kota',
                            'city_name' => 'Surabaya',
                            'postal_code' => '60119',
                        ],
                        [
                            'city_id' => '152',
                            'province_id' => '6',
                            'province' => 'DKI Jakarta',
                            'type' => 'Kota',
                            'city_name' => 'Jakarta Pusat',
                            'postal_code' => '10110',
                        ],
                    ],
                ],
            ], 200),
            'https://api.rajaongkir.com/starter/cost' => Http::response([
                'rajaongkir' => [
                    'status' => ['code' => 200, 'description' => 'OK'],
                    'results' => [
                        [
                            'code' => 'jne',
                            'name' => 'Jalur Nugraha Ekakurir (JNE)',
                            'costs' => [
                                [
                                    'service' => 'REG',
                                    'description' => 'Layanan Reguler',
                                    'cost' => [
                                        [
                                            'value' => 22000,
                                            'etd' => '2-3',
                                            'note' => '',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $driver = new ShippingDriver([
            'api_key' => 'mock-rajaongkir-key',
            'base_url' => 'https://api.rajaongkir.com/starter',
        ]);

        $rates = $driver->calculate('Surabaya', 'Jakarta Pusat', 1000, ['jne']);

        $this->assertCount(1, $rates);
        $this->assertEquals('jne', $rates[0]['courier']);
        $this->assertEquals('REG', $rates[0]['service']);
        $this->assertEquals(22000, $rates[0]['cost']);
        $this->assertEquals('2-3 hari', $rates[0]['etd']);
    }

    public function test_shipping_controller_validates_required_fields(): void
    {
        $response = $this->postJson(route('shipping.calculate'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination', 'weight']);
    }

    public function test_shipping_controller_uses_store_profile_couriers_and_origin(): void
    {
        Config::set('shipping.default', 'mock');

        // Create store profile with specific allowed couriers (only TIKI)
        ProfileStore::create([
            'name' => 'Nyuwi Store',
            'logo' => '',
            'address' => 'Jl. Mawar No. 10',
            'city' => 'Surabaya',
            'shipping_origin_city_id' => '444',
            'shipping_couriers' => ['tiki'],
            'phone' => '08123456789',
            'qris' => '',
        ]);

        $response = $this->postJson(route('shipping.calculate'), [
            'destination' => 'Bandung',
            'weight' => 1000,
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'origin' => '444',
                'destination' => 'Bandung',
            ]);

        $rates = $response->json('rates');
        $this->assertNotEmpty($rates);
        foreach ($rates as $rate) {
            $this->assertEquals('tiki', $rate['courier']);
        }
    }

    public function test_shipping_cities_and_couriers_endpoints_return_data(): void
    {
        Config::set('shipping.default', 'mock');

        $citiesResponse = $this->getJson(route('shipping.cities'));
        $citiesResponse->assertOk()
            ->assertJsonStructure(['status', 'data']);

        $couriersResponse = $this->getJson(route('shipping.couriers'));
        $couriersResponse->assertOk()
            ->assertJsonStructure(['status', 'data']);
    }

    public function test_binderbyte_driver_formats_cost_and_calculates_rates(): void
    {
        Http::fake([
            'https://api.binderbyte.com/v1/cost*' => Http::response([
                'status' => 200,
                'message' => 'Success',
                'data' => [
                    'costs' => [
                        [
                            'service' => 'REG',
                            'description' => 'Layanan Reguler',
                            'cost' => 19000,
                            'etd' => '2-3 hari',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $driver = new ShippingDriver([
            'api_key' => 'mock-binderbyte-key',
            'base_url' => 'https://api.binderbyte.com/v1',
        ]);

        $rates = $driver->calculate('Surabaya', 'Bandung', 1000, ['jne']);

        $this->assertCount(1, $rates);
        $this->assertEquals('jne', $rates[0]['courier']);
        $this->assertEquals('REG', $rates[0]['service']);
        $this->assertEquals(19000, $rates[0]['cost']);
        $this->assertEquals('2-3 hari', $rates[0]['etd']);
    }

    public function test_binderbyte_driver_tracks_package(): void
    {
        Http::fake([
            'https://api.binderbyte.com/v1/track*' => Http::response([
                'status' => 200,
                'message' => 'Successfully tracked package',
                'data' => [
                    'summary' => [
                        'courier' => 'JNE',
                        'waybill_number' => 'JN123456789ID',
                        'status' => 'DELIVERED',
                    ],
                ],
            ], 200),
        ]);

        $driver = new ShippingDriver([
            'api_key' => 'mock-binderbyte-key',
            'base_url' => 'https://api.binderbyte.com/v1',
        ]);

        $result = $driver->track('JNE - REG', 'JN123456789ID');

        $this->assertEquals(200, $result['status']);
        $this->assertEquals('DELIVERED', $result['data']['summary']['status']);
    }

    public function test_order_tracking_uses_shipping_manager(): void
    {
        Config::set('shipping.default', 'mock');

        $user = User::factory()->create(['role' => 'pelanggan']);
        $order = Order::create([
            'user_id' => $user->id,
            'name' => 'Test User',
            'address' => 'Jl. Test No. 1',
            'city' => 'Surabaya',
            'district' => 'Gubeng',
            'village' => 'Airlangga',
            'province' => 'Jawa Timur',
            'phone' => '081234567890',
            'total_price' => 100000,
            'payment_method' => 'qris',
            'shipping_method' => 'JNE - REG',
            'shipping_cost' => 18000,
            'tracking_number' => 'TRK123456',
        ]);

        $response = $this->actingAs($user)->get(route('customer.orders.tracking', $order->tracking_number));

        $response->assertStatus(302)
            ->assertSessionHas('trackingData');

        $trackingData = session('trackingData');
        $this->assertIsArray($trackingData);
        $this->assertEquals(200, $trackingData['status']);
    }
}
