<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Whislist;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WishlistTest extends TestCase
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

    public function test_user_can_view_wishlist()
    {
        $response = $this->actingAs($this->user)
            ->get(route('wishlist.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($assert) => $assert
                ->component('Customer/Wishlist')
                ->has('wishlistItems')
        );
    }

    public function test_user_can_add_to_wishlist()
    {
        $response = $this->actingAs($this->user)
            ->post(route('wishlist.store'), [
                'product_id' => $this->product->id
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('whislists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);
    }

    public function test_user_cannot_add_duplicate_product_to_wishlist()
    {
        // Add product to wishlist first
        Whislist::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);

        // Try to add same product again
        $response = $this->actingAs($this->user)
            ->post(route('wishlist.store'), [
                'product_id' => $this->product->id
            ]);

        // Should still succeed due to firstOrCreate logic
        $response->assertRedirect();

        // But should only have one entry
        $this->assertEquals(1, Whislist::where([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ])->count());
    }

    public function test_user_can_remove_from_wishlist()
    {
        // Add item to wishlist first
        $wishlist = Whislist::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('wishlist.destroy', $wishlist->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('whislists', [
            'id' => $wishlist->id
        ]);
    }

    public function test_user_cannot_remove_other_users_wishlist()
    {
        // Create another user
        $otherUser = User::factory()->create([
            'role' => 'pelanggan'
        ]);

        // Add item to other user's wishlist
        $wishlist = Whislist::create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id
        ]);

        // Try to delete as current user
        $response = $this->actingAs($this->user)
            ->delete(route('wishlist.destroy', $wishlist->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('whislists', [
            'id' => $wishlist->id
        ]);
    }

    public function test_guest_cannot_access_wishlist()
    {
        $response = $this->get(route('wishlist.index'));
        $response->assertRedirect(route('login'));
    }
}
