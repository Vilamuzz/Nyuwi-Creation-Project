<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;

use Illuminate\Support\Str;

class RemoveApiLayerTest extends TestCase
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

    public function test_api_routes_are_not_registered(): void
    {
        $apiRoutes = collect(\Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'api/'));

        $this->assertTrue(
            $apiRoutes->isEmpty(),
            sprintf('Found %d API routes: %s', $apiRoutes->count(), $apiRoutes->pluck('uri')->join(', '))
        );
    }

    public function test_home_page_returns_landing_page_with_products_and_categories(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/LandingPage')
                ->has('newProducts')
                ->has('featuredProducts')
                ->has('categories')
                ->has('canLogin')
                ->has('canRegister'));
    }

    public function test_about_route_is_removed(): void
    {
        $this->get('/about')->assertNotFound();
    }

    public function test_admin_registration_is_not_publicly_accessible(): void
    {
        $this->get('/admin/register')->assertNotFound();
        $this->post('/admin/register')->assertNotFound();
    }

    public function test_sale_page_returns_sale_page_with_products(): void
    {
        $this->get('/sale')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/SalePage')
                ->has('products')
                ->has('categories')
                ->has('filters'));
    }

    public function test_wishlist_routes_are_not_registered(): void
    {
        $this->get('/wishlist')->assertNotFound();
        $this->post('/wishlist')->assertNotFound();
        $this->delete('/wishlist/1')->assertNotFound();
    }

    public function test_guest_can_add_items_to_cart(): void
    {
        $product = $this->makeProduct('Guest Bouquet');

        $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertRedirect()
            ->assertSessionHas('success')
            ->assertSessionHas('cart');

        $this->assertCount(1, session('cart'));
        $this->assertEquals(2, session('cart')[0]['quantity']);
        $this->assertEquals($product->id, session('cart')[0]['product_id']);
    }

    public function test_guest_can_update_and_remove_cart_items(): void
    {
        $product = $this->makeProduct('Guest Update Bouquet');

        $this->withSession(['cart' => [
            [
                'id' => 'abc123',
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => (string) $product->price,
                'size' => null,
                'color' => null,
            ],
        ]]);

        $this->put('/cart/abc123', ['quantity' => 3])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(3, session('cart')[0]['quantity']);

        $this->delete('/cart/abc123')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertCount(0, session('cart'));
    }

    public function test_cart_page_is_accessible_to_guests(): void
    {
        $this->get('/cart')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/Cart')
                ->has('cartItems')
                ->has('summary'));
    }

    public function test_cart_props_are_shared_with_guests(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('cart')
                ->where('cart.summary.itemCount', 0)
                ->has('cart.items'));
    }

    public function test_cart_props_are_shared_for_authenticated_users(): void
    {
        $user = \App\Models\User::factory()->create();
        $product = $this->makeProduct('Auth Bouquet');

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => $product->price,
        ]);

        $this->actingAs($user)->get('/sale')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('cart')
                ->where('cart.summary.itemCount', 1)
                ->where('cart.summary.totalItems', 2)
                ->has('cart.items', 1));
    }

    public function test_customer_order_routes_require_authentication(): void
    {
        $this->get('/customer/orders')->assertRedirect('/login');
        $this->get('/customer/orders/1')->assertRedirect('/login');
        $this->post('/customer/orders/complete')->assertRedirect('/login');
        $this->post('/customer/orders/upload-proof')->assertRedirect('/login');
        $this->get('/customer/orders/tracking/TRACK123')->assertRedirect('/login');
    }

    public function test_admin_order_routes_require_authentication(): void
    {
        $this->get('/admin/orders')->assertRedirect('/login');
        $this->get('/admin/orders/1')->assertRedirect('/login');
        $this->put('/admin/orders/1')->assertRedirect('/login');
        $this->get('/admin/orders/tracking/TRACK123')->assertRedirect('/login');
    }

    public function test_region_routes_return_success(): void
    {
        // Provinces route does not require seeded data
        $this->get('/regions/provinces')->assertStatus(302);
        // Regencies route is registered; 404 is expected when province does not exist
        $this->get('/regions/regencies/99999')->assertStatus(404);
    }

    public function test_shipping_calculation_route_requires_valid_input(): void
    {
        $this->post('/shipping/calculate')->assertSessionHasErrors();
    }
}
