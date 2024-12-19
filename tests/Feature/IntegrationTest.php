<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create customer
        $this->user = User::factory()->create([
            'role' => 'pelanggan',
            'email_verified_at' => now()
        ]);

        // Create admin
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now()
        ]);

        // Create category and product
        $category = Category::create(['name' => 'Test Category']);
        $this->product = Product::create([
            'name' => 'Test Product',
            'category_id' => $category->id,
            'stock' => 10,
            'price' => 100000,
            'description' => 'Test Description',
            'image' => 'test.jpg'
        ]);
    }

    public function test_complete_order_flow()
    {
        // Initial stock is 10
        $initialStock = $this->product->stock;
        $orderQuantity = 2;

        // 1. Customer adds product to cart
        $response = $this->actingAs($this->user)
            ->post(route('cart.add'), [
                'product_id' => $this->product->id,
                'quantity' => $orderQuantity,
                'price' => $this->product->price
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        // 2. Customer places order
        $response = $this->actingAs($this->user)
            ->post(route('order.store'), [
                'name' => 'Test User',
                'address' => 'Test Address',
                'city' => 'Test City',
                'district' => 'Test District',
                'village' => 'Test Village',
                'province' => 'Test Province',
                'phone' => '08123456789',
                'payment_method' => 'digital_wallet',
                'shipping_method' => 'JNE',
                'shipping_cost' => 10000
            ]);

        $response->assertRedirect();

        $order = Order::first();
        $this->assertEquals('waiting', $order->status);
        $this->assertDatabaseEmpty('carts'); // Cart should be cleared

        // 3. Customer uploads payment proof
        Storage::fake('public');
        $image = UploadedFile::fake()->image('payment.jpg');

        $response = $this->actingAs($this->user)
            ->post(route('orders.upload-proof'), [
                'order_id' => $order->id,
                'payment_proof' => $image
            ]);

        $response->assertRedirect();
        $this->assertNotNull($order->fresh()->payment_proof);

        // 4. Admin processes the order
        $response = $this->actingAs($this->admin)
            ->put(route('orders.update', $order->id), [
                'status' => 'processing'
            ]);

        $response->assertRedirect();
        $this->assertEquals('processing', $order->fresh()->status);

        // 5. Admin adds tracking number - This should trigger stock reduction
        $response = $this->actingAs($this->admin)
            ->put(route('orders.update', $order->id), [
                'tracking_number' => '12345678',
                'status' => 'shiping'  // Add status explicitly
            ]);

        // Refresh both order and product
        $order->refresh();
        $this->product->refresh();

        // Verify order status and tracking
        $this->assertEquals('shiping', $order->status);
        $this->assertEquals('12345678', $order->tracking_number);

        // Verify stock reduction
        $expectedStock = $initialStock - $orderQuantity;
        $this->assertEquals(
            $expectedStock,
            $this->product->fresh()->stock,
            "Stock should be reduced from {$initialStock} to {$expectedStock} after order completion"
        );

        // 6. Customer completes order and adds review
        $response = $this->actingAs($this->user)
            ->post(route('orders.complete'), [
                'order_id' => $order->id,
                'reviews' => [
                    [
                        'product_id' => $this->product->id,
                        'rating' => 5
                    ]
                ]
            ]);

        // Verify final state
        $order->refresh();
        $this->product->refresh();

        // Check order status
        $this->assertEquals('completed', $order->status);

        // Check stock reduction
        $expectedStock = $initialStock - $orderQuantity;
        $this->assertEquals($expectedStock, $this->product->stock);

        // Verify review creation
        $this->assertDatabaseHas('product_reviews', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'order_id' => $order->id,
            'rating' => 5
        ]);
    }

    public function test_wishlist_to_cart_flow()
    {
        // 1. Add to wishlist
        $response = $this->actingAs($this->user)
            ->post(route('wishlist.store'), [
                'product_id' => $this->product->id
            ]);

        $response->assertRedirect();

        // 2. Move from wishlist to cart
        $response = $this->actingAs($this->user)
            ->post(route('cart.add'), [
                'product_id' => $this->product->id,
                'quantity' => 1,
                'price' => $this->product->price
            ]);

        $response->assertRedirect();

        // 3. Verify item is in cart
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);
    }
}
