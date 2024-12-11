<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $cart;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user
        $this->user = User::factory()->create([
            'role' => 'pelanggan'
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

        // Create cart item
        $this->cart = Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price
        ]);
    }

    public function test_user_can_place_order()
    {
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

        $response->assertRedirect(route('cart.show'));
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'name' => 'Test User',
            'status' => 'waiting'
        ]);
        $this->assertDatabaseEmpty('carts'); // Cart should be cleared
    }

    public function test_user_can_upload_payment_proof()
    {
        Storage::fake('public');

        $order = Order::create([
            'user_id' => $this->user->id,
            'name' => 'Test User',
            'address' => 'Test Address',
            'city' => 'Test City',
            'district' => 'Test District',
            'village' => 'Test Village',
            'province' => 'Test Province',
            'phone' => '08123456789',
            'total_price' => 110000,
            'payment_method' => 'digital_wallet',
            'shipping_method' => 'JNE',
            'status' => 'waiting'
        ]);

        $image = UploadedFile::fake()->image('payment.jpg');

        $response = $this->actingAs($this->user)
            ->post(route('orders.upload-proof'), [
                'order_id' => $order->id,
                'payment_proof' => $image
            ]);

        $response->assertRedirect();
        $this->assertNotNull($order->fresh()->payment_proof);
        $this->assertTrue(Storage::disk('public')->exists('payment_proofs/' . $order->fresh()->payment_proof));
    }

    public function test_user_can_complete_order_with_reviews()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'name' => 'Test User',
            'status' => 'shiping',
            'shipping_method' => 'GoSend',
            // Tambahkan field required lainnya
            'address' => 'Test Address',
            'city' => 'Test City',
            'district' => 'Test District',
            'village' => 'Test Village',
            'province' => 'Test Province',
            'phone' => '08123456789',
            'total_price' => 110000,
            'payment_method' => 'digital_wallet'
        ]);

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

        $response->assertRedirect();
        $this->assertEquals('completed', $order->fresh()->status);
        $this->assertDatabaseHas('product_reviews', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'order_id' => $order->id,
            'rating' => 5
        ]);
    }

    public function test_admin_can_update_order_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create order with all required fields
        $order = Order::create([
            'user_id' => $this->user->id,
            'name' => 'Test User',
            'status' => 'waiting',
            // Add all required fields from orders table
            'address' => 'Test Address',
            'city' => 'Test City',
            'district' => 'Test District',
            'village' => 'Test Village',
            'province' => 'Test Province',
            'phone' => '08123456789',
            'total_price' => 100000,
            'payment_method' => 'digital_wallet',
            'shipping_method' => 'JNE'
        ]);

        $response = $this->actingAs($admin)
            ->put(route('orders.update', $order->id), [
                'status' => 'processing'
            ]);

        $response->assertRedirect();
        $this->assertEquals('processing', $order->fresh()->status);
    }

    public function test_admin_can_add_tracking_number()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $order = Order::create([
            'user_id' => $this->user->id,
            'name' => 'Test User',
            'status' => 'processing',
            'shipping_method' => 'JNE',
            // Add required fields
            'address' => 'Test Address',
            'city' => 'Test City',
            'district' => 'Test District',
            'village' => 'Test Village',
            'province' => 'Test Province',
            'phone' => '08123456789',
            'total_price' => 100000,
            'payment_method' => 'digital_wallet'
        ]);

        $response = $this->actingAs($admin)
            ->put(route('orders.update', $order->id), [
                'tracking_number' => '12345678'
            ]);

        $response->assertRedirect();
        $this->assertEquals('shiping', $order->fresh()->status);
        $this->assertEquals('12345678', $order->fresh()->tracking_number);
    }
}
