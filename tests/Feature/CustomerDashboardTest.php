<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_customer_profile(): void
    {
        $response = $this->get('/customer/profile');

        $response->assertRedirect('/login');
    }

    public function test_customer_can_view_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'pelanggan']);

        $response = $this
            ->actingAs($user)
            ->get('/customer/profile');

        $response->assertOk();
    }

    public function test_customer_dashboard_renders_with_orders_and_reviews(): void
    {
        $user = User::factory()->create(['role' => 'pelanggan']);

        $order = Order::create([
            'user_id' => $user->id,
            'name' => 'Customer Tester',
            'address' => 'Jl. Test No. 123',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'village' => 'Dago',
            'province' => 'Jawa Barat',
            'phone' => '08123456789',
            'email' => 'customer@test.com',
            'total_price' => 115000,
            'payment_method' => 'qris',
            'shipping_method' => 'JNE',
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        $category = \App\Models\Category::create(['name' => 'Flowers']);

        $product = Product::create([
            'name' => 'Rose Bouquet',
            'slug' => 'rose-bouquet-' . uniqid(),
            'category_id' => $category->id,
            'stock' => 10,
            'price' => 100000,
            'weight' => 200,
            'description' => 'Test bouquet',
            'images' => ['rose.jpg'],
            'sizes' => [],
            'colors' => [],
        ]);

        ProductReview::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 5,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/customer/profile');

        $response->assertOk();
    }
}
