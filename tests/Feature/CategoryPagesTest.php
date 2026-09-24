<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

class CategoryPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $aksesoris = Category::create(['id' => 1, 'name' => 'Aksesoris']);
        $buket = Category::create(['id' => 2, 'name' => 'Buket']);
        $bunga = Category::create(['id' => 4, 'name' => 'Bunga']);
        $tas = Category::create(['id' => 5, 'name' => 'Tas']);

        Product::create([
            'name' => 'Cincin Manik',
            'slug' => 'cincin-manik',
            'category_id' => $aksesoris->id,
            'stock' => 10,
            'price' => 5000.00,
            'description' => 'Aksesoris cincin cantik',
            'images' => ['cincin.jpg'],
        ]);

        Product::create([
            'name' => 'Buket Wisuda',
            'slug' => 'buket-wisuda',
            'category_id' => $buket->id,
            'stock' => 5,
            'price' => 50000.00,
            'description' => 'Buket hadiah wisuda',
            'images' => ['buket.jpg'],
        ]);

        Product::create([
            'name' => 'Bunga Mawar Rajut',
            'slug' => 'bunga-mawar-rajut',
            'category_id' => $bunga->id,
            'stock' => 8,
            'price' => 25000.00,
            'description' => 'Bunga mawar cantik rajutan',
            'images' => ['bunga.jpg'],
        ]);

        Product::create([
            'name' => 'Tas Rajut Bahu',
            'slug' => 'tas-rajut-bahu',
            'category_id' => $tas->id,
            'stock' => 6,
            'price' => 85000.00,
            'description' => 'Tas rajut handmade',
            'images' => ['tas.jpg'],
        ]);
    }

    public function test_new_featured_page_returns_expected_component_and_products(): void
    {
        $response = $this->get('/new-featured');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/NewFeaturedPage')
                ->has('products.data', 4)
                ->has('categories')
                ->has('filters'));
    }

    public function test_boquets_page_returns_only_buket_products(): void
    {
        $response = $this->get('/boquets');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/BoquetsPage')
                ->has('products.data', 1)
                ->where('products.data.0.slug', 'buket-wisuda')
                ->has('categories')
                ->has('filters'));
    }

    public function test_flowers_page_returns_only_flowers_products(): void
    {
        $response = $this->get('/flowers');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/FlowersPage')
                ->has('products.data', 1)
                ->where('products.data.0.slug', 'bunga-mawar-rajut')
                ->has('categories')
                ->has('filters'));
    }

    public function test_accessories_page_returns_only_accessories_products(): void
    {
        $response = $this->get('/accessories');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/AccessoriesPage')
                ->has('products.data', 1)
                ->where('products.data.0.slug', 'cincin-manik')
                ->has('categories')
                ->has('filters'));
    }

    public function test_bags_page_returns_only_bags_products(): void
    {
        $response = $this->get('/bags');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/BagsPage')
                ->has('products.data', 1)
                ->where('products.data.0.slug', 'tas-rajut-bahu')
                ->has('categories')
                ->has('filters'));
    }

    public function test_sale_page_returns_all_categories_products(): void
    {
        $response = $this->get('/sale');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/SalePage')
                ->has('products.data', 4)
                ->has('categories')
                ->has('filters'));
    }
}
