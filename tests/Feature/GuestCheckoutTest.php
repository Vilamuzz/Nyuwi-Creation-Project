<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GuestCheckoutTest extends TestCase
{
    use RefreshDatabase;

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
            'name' => 'Guest Customer',
            'address' => 'Jl. Test No. 1',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'village' => 'Dago',
            'province' => 'Jawa Barat',
            'phone' => '081234567890',
            'email' => $email,
            'payment_method' => 'qris',
            'shipping_method' => 'JNE',
            'shipping_cost' => 15000,
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
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertEquals(115000, $order->total_price);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

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