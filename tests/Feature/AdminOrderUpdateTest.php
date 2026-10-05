<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock = 5): Product
    {
        $category = Category::create(['name' => 'Flowers ' . uniqid()]);

        return Product::create([
            'name' => 'Bouquet',
            'slug' => 'bouquet-' . uniqid(),
            'category_id' => $category->id,
            'stock' => $stock,
            'price' => 100000,
            'weight' => 200,
            'description' => 'Test bouquet',
            'images' => ['bouquet.jpg'],
            'sizes' => [],
            'colors' => [],
        ]);
    }

    private function makeOrder(User $user, Product $product, array $overrides = []): Order
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
            'email' => 'customer@test.com',
            'courier' => 'jne',
            'shipping_service' => 'REG',
            'weight' => 200,
            'shipping_cost' => 15000,
            'subtotal' => 100000,
            'total_price' => 115000,
            'payment_status' => 'paid',
            'status' => 'processing',
        ], $overrides));

        $order->orderItems()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => $product->price,
            'total_price' => $product->price * 2,
        ]);

        return $order;
    }

    public function test_admin_can_mark_order_as_shipped_and_does_not_double_reduce_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $product = $this->makeProduct(stock: 3); // Stock was already decremented upon checkout
        $order = $this->makeOrder($customer, $product);

        $this->actingAs($admin)
            ->put(route('admin.orders.update', $order->id), [
                'status' => 'shipped',
                'tracking_number' => 'TRK123456',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $order->refresh();

        $this->assertEquals('shipped', $order->status);
        $this->assertEquals('TRK123456', $order->tracking_number);
        // Stock remains 3 (not decremented again on shipping)
        $this->assertEquals(3, $product->fresh()->stock);
    }

    public function test_admin_can_cancel_order_and_restores_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $product = $this->makeProduct(stock: 3);
        $order = $this->makeOrder($customer, $product, ['status' => 'processing']);

        $this->actingAs($admin)
            ->put(route('admin.orders.update', $order->id), [
                'status' => 'cancelled',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $order->refresh();

        $this->assertEquals('cancelled', $order->status);
        // Stock restored from 3 to 5 (2 items in makeOrder)
        $this->assertEquals(5, $product->fresh()->stock);
    }

    public function test_admin_cannot_cancel_an_order_that_has_shipped(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $product = $this->makeProduct();
        $order = $this->makeOrder($customer, $product, ['status' => 'shipped']);

        $this->actingAs($admin)
            ->put(route('admin.orders.update', $order->id), ['status' => 'cancelled'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertEquals('shipped', $order->fresh()->status);
    }

    public function test_admin_can_move_order_through_fulfillment_statuses(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $product = $this->makeProduct();
        $order = $this->makeOrder($customer, $product, ['status' => 'waiting']);

        foreach (['checking', 'processing', 'completed'] as $status) {
            $this->actingAs($admin)
                ->put(route('admin.orders.update', $order->id), ['status' => $status])
                ->assertRedirect()
                ->assertSessionHas('success');

            $this->assertEquals($status, $order->fresh()->status);
        }
    }

    public function test_update_rejects_status_outside_the_allowed_enum(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $product = $this->makeProduct();
        $order = $this->makeOrder($customer, $product);

        $this->actingAs($admin)
            ->put(route('admin.orders.update', $order->id), ['status' => 'shiping'])
            ->assertSessionHasErrors('status');

        $this->assertEquals('processing', $order->fresh()->status);
    }

    public function test_customer_cannot_update_order_status(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $product = $this->makeProduct();
        $order = $this->makeOrder($customer, $product);

        $this->actingAs($customer)
            ->put(route('admin.orders.update', $order->id), ['status' => 'completed'])
            ->assertForbidden();

        $this->assertEquals('processing', $order->fresh()->status);
    }
}
