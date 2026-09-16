<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveApiLayerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function api_routes_are_not_registered(): void
    {
        $apiRoutes = collect(\Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'api/'));

        $this->assertTrue(
            $apiRoutes->isEmpty(),
            sprintf('Found %d API routes: %s', $apiRoutes->count(), $apiRoutes->pluck('uri')->join(', '))
        );
    }

    /** @test */
    public function home_page_returns_landing_page_with_products_and_categories(): void
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

    /** @test */
    public function shop_page_returns_shop_page_with_products(): void
    {
        $this->get('/shop')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/ShopingPage')
                ->has('products')
                ->has('categories')
                ->has('filters'));
    }

    /** @test */
    public function cart_mutation_routes_require_authentication(): void
    {
        $this->post('/cart')->assertRedirect('/login');
        $this->put('/cart/1')->assertRedirect('/login');
        $this->delete('/cart/1')->assertRedirect('/login');
    }

    /** @test */
    public function customer_order_routes_require_authentication(): void
    {
        $this->get('/customer/orders')->assertRedirect('/login');
        $this->get('/customer/orders/1')->assertRedirect('/login');
        $this->post('/customer/orders/checkout')->assertRedirect('/login');
        $this->post('/customer/orders/complete')->assertRedirect('/login');
        $this->post('/customer/orders/upload-proof')->assertRedirect('/login');
        $this->get('/customer/orders/tracking/TRACK123')->assertRedirect('/login');
    }

    /** @test */
    public function admin_order_routes_require_authentication(): void
    {
        $this->get('/admin/orders')->assertRedirect('/login');
        $this->get('/admin/orders/1')->assertRedirect('/login');
        $this->put('/admin/orders/1')->assertRedirect('/login');
        $this->get('/admin/orders/tracking/TRACK123')->assertRedirect('/login');
    }

    /** @test */
    public function region_routes_return_success(): void
    {
        $this->get('/regions/provinces')->assertStatus(302);
        $this->get('/regions/regencies/1')->assertStatus(302);
    }

    /** @test */
    public function shipping_calculation_route_requires_valid_input(): void
    {
        $this->post('/shipping/calculate')->assertSessionHasErrors();
    }
}
