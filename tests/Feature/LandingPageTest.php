<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_renders_with_required_props(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customer/LandingPage')
            ->has('newProducts')
            ->has('featuredProducts')
            ->has('categories')
            ->has('canLogin')
            ->has('canRegister')
        );
    }

    public function test_landing_page_caches_data(): void
    {
        Cache::forget('landing_page_data');

        $this->get('/');
        $this->assertTrue(Cache::has('landing_page_data'));

        $cached = Cache::get('landing_page_data');
        $this->assertArrayHasKey('newProducts', $cached);
        $this->assertArrayHasKey('featuredProducts', $cached);
        $this->assertArrayHasKey('categories', $cached);
    }
}
