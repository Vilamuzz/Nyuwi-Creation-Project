<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(string $name = 'Review Bouquet'): Product
    {
        $category = Category::create(['name' => 'Category ' . uniqid()]);

        return Product::create([
            'name' => $name,
            'slug' => 'slug-' . uniqid(),
            'category_id' => $category->id,
            'stock' => 10,
            'price' => 100000,
            'weight' => 200,
            'description' => 'Test bouquet',
            'images' => ['bouquet.jpg'],
            'sizes' => [],
            'colors' => [],
        ]);
    }

    private function makeOrder(User $user, Product $product, array $attributes = []): Order
    {
        $order = Order::create(array_merge([
            'user_id' => $user->id,
            'recipient_name' => 'Customer Tester',
            'address' => 'Jl. Test No. 1',
            'province_id' => '6',
            'province_name' => 'Jawa Barat',
            'city_id' => '22',
            'city_name' => 'Bandung',
            'district_id' => '220108',
            'district_name' => 'Coblong',
            'postal_code' => '40135',
            'phone' => '08123456789',
            'email' => $user->email,
            'courier' => 'jne',
            'shipping_service' => 'REG',
            'weight' => 200,
            'shipping_cost' => 18000,
            'subtotal' => 100000,
            'total_price' => 118000,
            'payment_status' => 'paid',
            'status' => 'shipped',
        ], $attributes));

        $order->orderItems()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
            'total_price' => $product->price,
        ]);

        return $order;
    }

    public function test_user_cannot_complete_or_review_order_belonging_to_another_user(): void
    {
        $owner = User::factory()->create(['role' => 'pelanggan']);
        $attacker = User::factory()->create(['role' => 'pelanggan']);
        $product = $this->makeProduct();
        $order = $this->makeOrder($owner, $product);

        $response = $this->actingAs($attacker)->post(route('customer.orders.complete'), [
            'order_id' => $order->id,
            'reviews' => [
                ['product_id' => $product->id, 'rating' => 5],
            ],
        ]);

        $response->assertForbidden();
        $this->assertEquals('shipped', $order->fresh()->status);
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_user_cannot_complete_or_review_pre_shipped_or_unpaid_order(): void
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        $product = $this->makeProduct();
        $order = $this->makeOrder($user, $product, [
            'status' => 'waiting',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($user)->post(route('customer.orders.complete'), [
            'order_id' => $order->id,
            'reviews' => [
                ['product_id' => $product->id, 'rating' => 5],
            ],
        ]);

        $response->assertSessionHasErrors('order_id');
        $this->assertEquals('waiting', $order->fresh()->status);
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_user_cannot_review_products_not_in_order(): void
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        $productInOrder = $this->makeProduct('Bouquet in order');
        $unpurchasedProduct = $this->makeProduct('Bouquet not in order');
        $order = $this->makeOrder($user, $productInOrder);

        $response = $this->actingAs($user)->post(route('customer.orders.complete'), [
            'order_id' => $order->id,
            'reviews' => [
                ['product_id' => $unpurchasedProduct->id, 'rating' => 5],
            ],
        ]);

        $response->assertSessionHasErrors('reviews.0.product_id');
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_user_cannot_submit_duplicate_products_in_same_request(): void
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        $product = $this->makeProduct();
        $order = $this->makeOrder($user, $product);

        $response = $this->actingAs($user)->post(route('customer.orders.complete'), [
            'order_id' => $order->id,
            'reviews' => [
                ['product_id' => $product->id, 'rating' => 4],
                ['product_id' => $product->id, 'rating' => 5],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_submitting_reviews_multiple_times_does_not_create_duplicate_rows(): void
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        $product = $this->makeProduct();
        $order = $this->makeOrder($user, $product);

        // First submission
        $firstResponse = $this->actingAs($user)->post(route('customer.orders.complete'), [
            'order_id' => $order->id,
            'reviews' => [
                ['product_id' => $product->id, 'rating' => 4],
            ],
        ]);
        $firstResponse->assertRedirect()->assertSessionHas('success');

        // Second submission (update rating)
        $secondResponse = $this->actingAs($user)->post(route('customer.orders.complete'), [
            'order_id' => $order->id,
            'reviews' => [
                ['product_id' => $product->id, 'rating' => 5],
            ],
        ]);
        $secondResponse->assertRedirect()->assertSessionHas('success');

        // Only one review row exists with updated rating
        $this->assertDatabaseCount('product_reviews', 1);
        $this->assertDatabaseHas('product_reviews', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'rating' => 5,
        ]);
    }

    public function test_user_can_complete_and_review_shipped_order(): void
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        $product = $this->makeProduct();
        $order = $this->makeOrder($user, $product);

        $response = $this->actingAs($user)->post(route('customer.orders.complete'), [
            'order_id' => $order->id,
            'reviews' => [
                ['product_id' => $product->id, 'rating' => 5],
            ],
        ]);

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertEquals('completed', $order->fresh()->status);
        $this->assertDatabaseHas('product_reviews', [
            'user_id' => $user->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'rating' => 5,
        ]);
    }
}
