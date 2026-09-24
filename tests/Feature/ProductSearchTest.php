<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_search_returns_empty_array_when_query_is_empty(): void
    {
        $response = $this->getJson(route('products.api-search', ['q' => '']));

        $response->assertStatus(200)
            ->assertExactJson([]);
    }

    public function test_api_search_returns_matching_products(): void
    {
        $category = Category::create(['name' => 'Flowers']);
        
        $product1 = Product::create([
            'name' => 'Red Rose Bouquet',
            'slug' => 'red-rose-bouquet',
            'category_id' => $category->id,
            'stock' => 10,
            'price' => 150000,
            'weight' => 500,
            'description' => 'Beautiful red roses for romance',
            'images' => ['rose.jpg'],
        ]);

        $product2 = Product::create([
            'name' => 'Sunflower Box',
            'slug' => 'sunflower-box',
            'category_id' => $category->id,
            'stock' => 5,
            'price' => 200000,
            'weight' => 800,
            'description' => 'Bright yellow sunflowers',
            'images' => ['sunflower.jpg'],
        ]);

        $response = $this->getJson(route('products.api-search', ['q' => 'Rose']));

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id' => $product1->id,
                'name' => 'Red Rose Bouquet',
                'slug' => 'red-rose-bouquet',
                'category' => 'Flowers',
            ]);
    }
}
