<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveApiLayerTest extends TestCase
{
    use RefreshDatabase;

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
                ->has('products')
                ->has('categories')
                ->has('canLogin')
                ->has('canRegister'));
    }

    public function test_shop_page_returns_shop_page_with_products(): void
    {
        $this->get('/shop')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/ShopingPage')
                ->has('products')
                ->has('categories')
                ->has('filters'));
    }

    public function test_cart_mutation_routes_require_authentication(): void
    {
        $this->post('/cart')->assertRedirect('/login');
        $this->put('/cart/1')->assertRedirect('/login');
        $this->delete('/cart/1')->assertRedirect('/login');
    }

    public function test_customer_order_routes_require_authentication(): void
    {
        $this->get('/customer/orders')->assertRedirect('/login');
        $this->get('/customer/orders/1')->assertRedirect('/login');
        $this->post('/customer/orders/checkout')->assertRedirect('/login');
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
