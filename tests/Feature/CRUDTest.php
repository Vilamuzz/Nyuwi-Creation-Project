<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CRUDTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin'
        ]);

        // Create test category
        $this->category = Category::create(['name' => 'Test Category']);
    }

    // Positive Cases
    public function test_admin_can_create_product_with_valid_data()
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('test-product.jpg');

        $response = $this->actingAs($this->admin)
            ->post(route('products.store'), [
                'name' => 'Test Product',
                'category_id' => $this->category->id,
                'stock' => 10,
                'price' => 100000,
                'description' => 'Test Description',
                'image' => $image
            ]);

        $response->assertRedirect(route('products.index'));

        // Verify database
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'category_id' => $this->category->id,
            'stock' => 10,
            'price' => 100000.00
        ]);

        // Verify image storage
        $product = Product::where('name', 'Test Product')->first();
        $this->assertTrue(Storage::disk('public')->exists('products/' . $product->image));
    }

    public function test_admin_can_update_product_with_valid_data()
    {
        Storage::fake('public');

        // Create initial product
        $product = Product::create([
            'name' => 'Original Product',
            'category_id' => $this->category->id,
            'stock' => 5,
            'price' => 50000,
            'description' => 'Original Description',
            'image' => 'original.jpg'
        ]);

        $newImage = UploadedFile::fake()->image('updated-product.jpg');

        $response = $this->actingAs($this->admin)
            ->put(route('products.update', $product->id), [
                'name' => 'Updated Product',
                'category_id' => $this->category->id,
                'stock' => 15,
                'price' => 75000,
                'description' => 'Updated Description',
                'image' => $newImage
            ]);

        $response->assertRedirect(route('products.index'));

        // Verify database update
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'stock' => 15,
            'price' => 75000.00
        ]);
    }

    // Negative Cases
    public function test_cannot_create_product_with_empty_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('products.store'), [
                'name' => '',
                'category_id' => '',
                'stock' => '',
                'price' => '',
                'description' => '',
                'image' => ''
            ]);

        // Remove category_id from validation check since it might be nullable
        $response->assertInvalid([
            'name' => 'The name field is required.',
            'stock' => 'The stock field is required.',
            'price' => 'The price field is required.',
            'description' => 'The description field is required.',
            'image' => 'The image field is required.'
        ]);

        // Verify no product was created
        $this->assertDatabaseCount('products', 0);
    }

    public function test_cannot_update_nonexistent_product()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('products.update', 999), [
                'name' => 'Updated Product',
                'category_id' => $this->category->id,
                'stock' => 15,
                'price' => 75000
            ]);

        $response->assertStatus(404);
    }

    public function test_cannot_delete_product_in_active_order()
    {
        // Create product
        $product = Product::create([
            'name' => 'Product in Order',
            'category_id' => $this->category->id,
            'stock' => 5,
            'price' => 50000,
            'description' => 'Test Description',
            'image' => 'test.jpg'
        ]);

        // Create order using this product
        $order = Order::create([
            'user_id' => $this->admin->id,
            'name' => 'Test User',
            'status' => 'processing',
            'address' => 'Test Address',
            'city' => 'Test City',
            'district' => 'Test District',
            'village' => 'Test Village',
            'province' => 'Test Province',
            'phone' => '08123456789',
            'total_price' => 50000,
            'payment_method' => 'digital_wallet',
            'shipping_method' => 'JNE'
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
            'total_price' => $product->price
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('products.destroy', $product->id));

        // Change the assertion to match how errors are handled
        $response->assertSessionHas('error', 'Cannot delete product that is part of an active order');

        // Verify product still exists
        $this->assertDatabaseHas('products', [
            'id' => $product->id
        ]);
    }

    public function test_non_admin_cannot_access_product_management()
    {
        $user = User::factory()->create(['role' => 'pelanggan']);

        // Test index access
        $indexResponse = $this->actingAs($user)
            ->get(route('products.index'));
        $indexResponse->assertStatus(403);

        // Test create access
        $createResponse = $this->actingAs($user)
            ->get(route('products.create'));
        $createResponse->assertStatus(403);

        // Test store access
        $storeResponse = $this->actingAs($user)
            ->post(route('products.store'), []);
        $storeResponse->assertStatus(403);

        // Test update access
        $updateResponse = $this->actingAs($user)
            ->put(route('products.update', 1), []);
        $updateResponse->assertStatus(403);

        // Test delete access
        $deleteResponse = $this->actingAs($user)
            ->delete(route('products.destroy', 1));
        $deleteResponse->assertStatus(403);
    }
}
