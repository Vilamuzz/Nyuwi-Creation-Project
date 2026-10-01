<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ProfileStore;
use App\Models\User;
use App\Services\ShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShippingCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipping_controller_validates_required_fields(): void
    {
        $response = $this->postJson(route('shipping.calculate'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination', 'weight']);
    }

    public function test_shipping_controller_uses_store_profile_couriers_and_origin(): void
    {
        Config::set('services.binderbyte.api_key', 'mock-binderbyte-key');

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost*' => Http::response([
                'status' => 'success',
                'data' => [
                    [
                        'name' => 'TIKI',
                        'code' => 'tiki',
                        'service' => 'REG',
                        'description' => 'Layanan Reguler',
                        'cost' => 19000,
                        'etd' => '2-3 hari',
                    ],
                ],
            ], 200),
        ]);

        // Create store profile with specific allowed couriers (only TIKI)
        ProfileStore::create([
            'name' => 'Nyuwi Store',
            'logo' => '',
            'address' => 'Jl. Mawar No. 10',
            'embedded_map' => '',
            'district_id' => '3866',
            'district_name' => 'Peterongan',
            'shipping_couriers' => ['tiki'],
            'phone' => '08123456789',
        ]);

        $response = $this->postJson(route('shipping.calculate'), [
            'destination' => 'Bandung',
            'weight' => 1000,
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'origin' => '3866',
                'destination' => 'Bandung',
            ]);

        $rates = $response->json('rates');
        $this->assertNotEmpty($rates);
        foreach ($rates as $rate) {
            $this->assertEquals('tiki', $rate['courier']);
        }
    }

    public function test_shipping_controller_uses_store_profile_district_as_origin(): void
    {
        Config::set('services.binderbyte.api_key', 'mock-binderbyte-key');

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost*' => Http::response([
                'status' => 'success',
                'data' => [
                    [
                        'name' => 'TIKI',
                        'code' => 'tiki',
                        'service' => 'REG',
                        'description' => 'Layanan Reguler',
                        'cost' => 19000,
                        'etd' => '2-3 hari',
                    ],
                ],
            ], 200),
        ]);

        ProfileStore::create([
            'name' => 'Nyuwi Store',
            'logo' => '',
            'address' => 'Jl. Mawar No. 10',
            'embedded_map' => '',
            'district_id' => '3517110',
            'district_name' => 'Diwek',
            'shipping_couriers' => ['jne'],
            'phone' => '08123456789',
        ]);

        $response = $this->postJson(route('shipping.calculate'), [
            'destination' => 'Bandung',
            'weight' => 1000,
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'origin' => '3517110',
                'destination' => 'Bandung',
            ]);
    }

    public function test_shipping_cities_and_couriers_endpoints_return_data(): void
    {
        Config::set('services.binderbyte.api_key', 'mock-binderbyte-key');

        Http::fake([
            'https://api.binderbyte.com/v1/list_city*' => Http::response([
                'status' => 200,
                'message' => 'Success',
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Surabaya',
                        'type' => 'Kota',
                        'province' => 'Jawa Timur',
                        'postal_code' => '60181',
                    ],
                ],
            ], 200),
        ]);

        $citiesResponse = $this->getJson('/locations/provinces');
        $citiesResponse->assertOk();
    }

    public function test_shipping_service_calculates_rates_via_komerce(): void
    {
        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost*' => Http::response([
                'status' => 'success',
                'data' => [
                    [
                        'name' => 'Jalur Nugraha Ekakurir (JNE)',
                        'code' => 'jne',
                        'service' => 'REG',
                        'description' => 'Layanan Reguler',
                        'cost' => 19000,
                        'etd' => '2-3 hari',
                    ],
                ],
            ], 200),
        ]);

        $rates = app(ShippingService::class)->cost(1, 2, 1000, ['jne']);

        $this->assertCount(1, $rates);
        $this->assertEquals('jne', $rates[0]['courier']);
        $this->assertEquals('REG', $rates[0]['service']);
        $this->assertEquals(19000, $rates[0]['cost']);
        $this->assertEquals('2-3 hari', $rates[0]['etd']);
    }

    public function test_shipping_service_tracks_package_via_binderbyte(): void
    {
        Config::set('services.binderbyte.api_key', 'mock-binderbyte-key');

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

        $result = app(ShippingService::class)->track('JNE - REG', 'JN123456789ID');

        $this->assertEquals(200, $result['status']);
        $this->assertEquals('DELIVERED', $result['data']['summary']['status']);
    }

    public function test_shipping_service_track_requires_api_key(): void
    {
        Config::set('services.binderbyte.api_key', null);

        $result = app(ShippingService::class)->track('jne', 'JN123456789ID');

        $this->assertEquals(400, $result['status']);
        $this->assertNull($result['data']);
    }

    public function test_order_tracking_uses_shipping_service(): void
    {
        Config::set('services.binderbyte.api_key', 'mock-binderbyte-key');

        Http::fake([
            'https://api.binderbyte.com/v1/track*' => Http::response([
                'status' => 200,
                'message' => 'Successfully tracked package',
                'data' => [
                    'summary' => [
                        'courier' => 'JNE',
                        'waybill_number' => 'TRK123456',
                        'status' => 'DELIVERED',
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create(['role' => 'pelanggan']);
        $order = Order::create([
            'user_id' => $user->id,
            'recipient_name' => 'Test User',
            'address' => 'Jl. Test No. 1',
            'province_id' => '5',
            'province_name' => 'Jawa Timur',
            'city_id' => '15',
            'city_name' => 'Surabaya',
            'district_id' => '357803',
            'district_name' => 'Gubeng',
            'postal_code' => '60181',
            'phone' => '081234567890',
            'courier' => 'jne',
            'shipping_service' => 'REG',
            'weight' => 1000,
            'shipping_cost' => 18000,
            'subtotal' => 82000,
            'total_price' => 100000,
            'tracking_number' => 'TRK123456',
        ]);

        $response = $this->actingAs($user)->get(route('customer.orders.tracking', $order->tracking_number));

        $response->assertStatus(302)
            ->assertSessionHas('trackingData');

        $trackingData = session('trackingData');
        $this->assertIsArray($trackingData);
        $this->assertEquals(200, $trackingData['status']);
        $this->assertEquals('DELIVERED', $trackingData['data']['summary']['status']);
    }
}