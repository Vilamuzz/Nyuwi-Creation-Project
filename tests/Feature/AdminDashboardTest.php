<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);

        $response = $this->actingAs($customer)->get(route('dashboard'));

        $response->assertForbidden();
    }

    public function test_admin_can_access_dashboard_with_all_props(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = \App\Models\Category::create(['name' => 'Flowers']);

        // Create sample product and order
        $product = Product::create([
            'name' => 'Rose Bouquet',
            'slug' => 'rose-bouquet',
            'category_id' => $category->id,
            'stock' => 8,
            'price' => 75000,
            'weight' => 200,
            'description' => 'Test bouquet',
            'images' => ['rose.jpg'],
            'sizes' => [],
            'colors' => [],
        ]);

        Order::create([
            'user_id' => $admin->id,
            'name' => 'Customer Tester',
            'address' => 'Jl. Test No. 123',
            'city' => 'Jakarta',
            'district' => 'Kebayoran',
            'village' => 'Senayan',
            'province' => 'DKI Jakarta',
            'phone' => '08123456789',
            'email' => 'customer@test.com',
            'total_price' => 150000,
            'payment_method' => 'digital_wallet',
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->has('stats')
                ->has('stats.totalRevenue')
                ->has('stats.pendingOrdersCount')
                ->has('stats.totalProductsCount')
                ->has('stats.lowStockCount')
                ->has('recentOrders')
                ->has('topSelling')
                ->has('mostRated')
                ->has('lowStock')
                ->where('stats.pendingOrdersCount', 1)
                ->where('stats.totalProductsCount', 1)
                ->where('stats.lowStockCount', 1)
            );
    }
}
