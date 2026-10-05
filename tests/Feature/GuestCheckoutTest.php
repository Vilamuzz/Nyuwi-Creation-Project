<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GuestCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Keep checkout shipping server-side calculations deterministic (no external calls).
        Config::set('services.binderbyte.api_key', 'mock-binderbyte-key');

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost*' => function ($request) {
                $body = $request->body();
                // Couriers without an offering for the destination get no costs.
                if (str_contains((string) $body, 'courier=sicepat')) {
                    return Http::response([
                        'status' => 'success',
                        'data' => [],
                    ], 200);
                }

                $courier = str_contains((string) $body, 'courier=pos') ? 'pos' : 'jne';

                return Http::response([
                    'status' => 'success',
                    'data' => [
                        [
                            'name' => strtoupper($courier),
                            'code' => $courier,
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

    private function makeProduct(string $name): Product
    {
        $category = Category::create(['name' => $name . ' Category']);

        return Product::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(6),
            'category_id' => $category->id,
            'stock' => 5,
            'price' => 50000,
            'weight' => 100,
            'description' => 'A test product used by feature tests.',
            'images' => ['bouquet.jpg'],
            'sizes' => [],
            'colors' => [],
        ]);
    }

    private function seedGuestCart(Product $product): void
    {
        $this->withSession([
            'cart' => [
                [
                    'id' => 'guest-item-abc',
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => (string) $product->price,
                    'size' => null,
                    'color' => null,
                ],
            ],
        ]);
    }

    private function guestCheckoutPayload(string $email): array
    {
        return [
            'recipient_name' => 'Guest Customer',
            'address' => 'Jl. Test No. 1',
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

    public function test_guest_can_access_checkout_page_with_items_in_cart(): void
    {
        $product = $this->makeProduct('Guest Checkout Bouquet');
        $this->seedGuestCart($product);

        $this->get('/checkout')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/Checkout')
                ->has('cartItems', 1));
    }

    public function test_checkout_page_redirects_to_cart_when_cart_is_empty(): void
    {
        $this->get('/checkout')
            ->assertRedirect(route('cart.show'))
            ->assertSessionHas('error');
    }

    public function test_guest_can_place_an_order_without_authentication(): void
    {
        $product = $this->makeProduct('Guest Order Bouquet');
        $this->seedGuestCart($product);

        $this->post('/customer/orders/checkout', $this->guestCheckoutPayload('guest@example.com'))
            ->assertRedirect(route('cart.show'))
            ->assertSessionHas('success');

        $order = Order::whereNull('user_id')->first();

        $this->assertNotNull($order);
        $this->assertEquals('guest@example.com', $order->email);
        $this->assertEquals('unpaid', $order->payment_status);
        $this->assertEquals('waiting', $order->status);
        $this->assertEquals('Guest Customer', $order->recipient_name);
        $this->assertEquals('Coblong', $order->district_name);
        $this->assertEquals('40135', $order->postal_code);
        $this->assertEquals(100000, (float) $order->subtotal);
        // Mock driver: JNE REG = 18000 for a 200g parcel (1kg tier).
        $this->assertEquals(18000, (float) $order->shipping_cost);
        $this->assertEquals(118000, (float) $order->total_price);
        $this->assertEquals(200, $order->weight);
        $this->assertNotEmpty($order->order_number);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertEquals(3, $product->fresh()->stock);
        $this->assertNull(session('cart'));
    }

    public function test_guest_email_is_required_for_checkout(): void
    {
        $product = $this->makeProduct('Guest Email Bouquet');
        $this->seedGuestCart($product);

        $payload = $this->guestCheckoutPayload('guest@example.com');
        unset($payload['email']);

        $this->post('/customer/orders/checkout', $payload)
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_guest_cannot_place_order_with_empty_cart(): void
    {
        $this->post('/customer/orders/checkout', $this->guestCheckoutPayload('guest@example.com'))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_ignores_client_supplied_totals_and_computes_them_server_side(): void
    {
        $product = $this->makeProduct('Tampered Totals Bouquet');
        $this->seedGuestCart($product);

        $payload = $this->guestCheckoutPayload('guest@example.com');

        // A tampered client tries to force a zero shipping cost / total.
        $payload['shipping_cost'] = 0;
        $payload['total_price'] = 1;
        $payload['subtotal'] = 1;

        $this->post('/customer/orders/checkout', $payload)
            ->assertRedirect(route('cart.show'));

        $order = Order::whereNull('user_id')->first();

        $this->assertEquals(100000, (float) $order->subtotal);
        $this->assertEquals(18000, (float) $order->shipping_cost);
        $this->assertEquals(118000, (float) $order->total_price);
    }

    public function test_checkout_fails_when_courier_service_is_not_offered_for_the_destination(): void
    {
        $product = $this->makeProduct('Unknown Courier Bouquet');
        $this->seedGuestCart($product);

        $payload = $this->guestCheckoutPayload('guest@example.com');
        $payload['courier'] = 'sicepat';

        $this->post('/customer/orders/checkout', $payload)
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_requires_the_new_region_and_courier_fields(): void
    {
        $product = $this->makeProduct('Region Validation Bouquet');
        $this->seedGuestCart($product);

        $payload = $this->guestCheckoutPayload('guest@example.com');

        foreach (['province_id', 'province_name', 'city_id', 'city_name', 'district_id', 'district_name', 'courier', 'shipping_service'] as $field) {
            unset($payload[$field]);
        }

        $this->post('/customer/orders/checkout', $payload)
            ->assertSessionHasErrors(['province_id', 'province_name', 'city_id', 'city_name', 'district_id', 'district_name', 'courier', 'shipping_service']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_authenticated_user_checkout_links_order_to_user(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct('Auth Checkout Bouquet');

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        $this->actingAs($user)
            ->post('/customer/orders/checkout', $this->guestCheckoutPayload($user->email))
            ->assertRedirect(route('cart.show'))
            ->assertSessionHas('success');

        $order = Order::where('user_id', $user->id)->first();

        $this->assertNotNull($order);
        $this->assertEquals($user->email, $order->email);
        $this->assertDatabaseCount('carts', 0);
        $this->assertCount(1, OrderItem::where('order_id', $order->id)->get());
    }
}