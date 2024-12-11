<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

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
    }

    public function test_user_can_view_cart()
    {
        $response = $this->actingAs($this->user)
            ->get(route('cart.show'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($assert) => $assert
                ->component('Customer/Cart')
                ->has('cartItems')
        );
    }

    public function test_user_can_add_product_to_cart()
    {
        $response = $this->actingAs($this->user)
            ->post(route('cart.add'), [
                'product_id' => $this->product->id,
                'quantity' => 1,
                'price' => $this->product->price
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price
        ]);
    }

    public function test_user_cannot_add_more_than_stock()
    {
        $response = $this->actingAs($this->user)
            ->post(route('cart.add'), [
                'product_id' => $this->product->id,
                'quantity' => 11, // More than available stock (10)
                'price' => $this->product->price
            ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseMissing('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);
    }

    public function test_user_can_update_cart_quantity()
    {
        // Add item to cart first
        $cart = Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('cart.update', $cart->id), [
                'quantity' => 2
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'quantity' => 2
        ]);
    }

    public function test_user_cannot_update_quantity_more_than_stock()
    {
        $cart = Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('cart.update', $cart->id), [
                'quantity' => 11 // More than stock
            ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'quantity' => 1 // Quantity should remain unchanged
        ]);
    }

    public function test_user_can_remove_product_from_cart()
    {
        $cart = Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('cart.remove', $cart->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    public function test_user_can_view_checkout_with_items()
    {
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('checkout'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($assert) => $assert
                ->component('Customer/Checkout')
                ->has('cartItems')
        );
    }

    public function test_empty_cart_redirects_from_checkout()
    {
        $response = $this->actingAs($this->user)
            ->get(route('checkout'));

        $response->assertRedirect(route('cart.show'));
        $response->assertSessionHas('error');
    }
}
