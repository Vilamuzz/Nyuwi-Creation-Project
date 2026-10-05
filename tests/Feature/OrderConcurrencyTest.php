<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.binderbyte.api_key', 'mock-binderbyte-key');

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost*' => function () {
                return Http::response([
                    'status' => 'success',
                    'data' => [
                        [
                            'name' => 'JNE',
                            'code' => 'jne',
                            'service' => 'REG',
                            'description' => 'Layanan Reguler',
                            'cost' => 18000,
                            'etd' => '2-3 hari',
                        ],
                    ],
                ], 200);
            },
        ]);
    }

    private function makeProduct(int $stock = 5): Product
    {
        $category = Category::create(['name' => 'Bouquets ' . uniqid()]);

        return Product::create([
            'name' => 'Test Bouquet',
            'slug' => 'test-bouquet-' . uniqid(),
            'category_id' => $category->id,
            'stock' => $stock,
            'price' => 100000,
            'weight' => 200,
            'description' => 'Test flower bouquet',
            'images' => ['bouquet.jpg'],
            'sizes' => [],
            'colors' => [],
        ]);
    }

    private function checkoutPayload(string $email = 'buyer@example.com'): array
    {
        return [
            'recipient_name' => 'Test Buyer',
            'address' => 'Jl. Mawar No. 10',
            'province_id' => '6',
            'province_name' => 'Jawa Barat',
            'city_id' => '22',
            'city_name' => 'Bandung',
            'district_id' => '220108',
            'district_name' => 'Coblong',
            'postal_code' => '40135',
            'phone' => '081234567890',
            'email' => $email,
            'courier' => 'jne',
            'shipping_service' => 'REG',
        ];
    }

    public function test_checkout_decrements_product_stock(): void
    {
        $product = $this->makeProduct(stock: 5);

        // Seed session cart with 2 units
        session([
            'cart' => [
                [
                    'id' => 'item-1',
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => (string) $product->price,
                    'size' => null,
                    'color' => null,
                ],
            ],
        ]);

        $response = $this->post('/customer/orders/checkout', $this->checkoutPayload());

        $response->assertRedirect(route('cart.show'));
        $response->assertSessionHas('success');

        $this->assertEquals(3, $product->fresh()->stock);
        $this->assertDatabaseHas('orders', [
            'email' => 'buyer@example.com',
            'status' => 'waiting',
        ]);
    }

    public function test_checkout_fails_when_product_has_insufficient_stock(): void
    {
        $product = $this->makeProduct(stock: 1);

        session([
            'cart' => [
                [
                    'id' => 'item-out-of-stock',
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => (string) $product->price,
                    'size' => null,
                    'color' => null,
                ],
            ],
        ]);

        $response = $this->post('/customer/orders/checkout', $this->checkoutPayload());

        $response->assertSessionHas('error');
        // Product stock should remain unchanged
        $this->assertEquals(1, $product->fresh()->stock);
        // No orders created
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_command_expires_unpaid_orders_and_restores_stock(): void
    {
        $product = $this->makeProduct(stock: 2);
        $customer = User::factory()->create(['role' => 'pelanggan']);

        $order = Order::create([
            'user_id' => $customer->id,
            'recipient_name' => 'Expired Customer',
            'address' => 'Jl. Test No. 99',
            'province_id' => '6',
            'province_name' => 'Jawa Barat',
            'city_id' => '22',
            'city_name' => 'Bandung',
            'district_id' => '220108',
            'district_name' => 'Coblong',
            'postal_code' => '40135',
            'phone' => '081234567890',
            'email' => 'expired@example.com',
            'courier' => 'jne',
            'shipping_service' => 'REG',
            'weight' => 200,
            'shipping_cost' => 18000,
            'subtotal' => 100000,
            'total_price' => 118000,
            'payment_status' => 'unpaid',
            'status' => 'waiting',
        ]);

        $order->orderItems()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => $product->price,
            'total_price' => $product->price * 3,
        ]);

        // Artificially age the order past 24 hours
        $order->update(['created_at' => now()->subHours(25)]);

        Artisan::call('orders:expire-unpaid', ['--hours' => 24]);

        $order->refresh();

        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('expired', $order->payment_status);
        // Stock restored from 2 to 5 (2 + 3)
        $this->assertEquals(5, $product->fresh()->stock);
    }
}
