<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ProfileStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_profile_store_index(): void
    {
        $response = $this->get(route('profile-store.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_profile_store_index(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);

        $response = $this->actingAs($customer)->get(route('profile-store.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_access_profile_store_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        ProfileStore::create([
            'name' => 'Toko Test',
            'logo' => 'logo/test.png',
            'address' => 'Jl. Test No. 1',
            'embedded_map' => 'https://maps.example.com/embed',
            'district_id' => '3517120',
            'district_name' => 'Peterongan',
            'phone' => '081234567890',
            'instagram' => 'tokotest',
            'facebook' => 'tokotest',
            'tiktok' => '@tokotest',
        ]);

        $response = $this->actingAs($admin)->get(route('profile-store.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/ProfileStore')
                ->has('profile')
                ->where('profile.name', 'Toko Test')
            );
    }

    public function test_admin_can_access_profile_store_index_without_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('profile-store.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/ProfileStore')
                ->where('profile', null)
            );
    }

    public function test_admin_can_update_profile_store(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $profile = ProfileStore::create([
            'name' => 'Toko Lama',
            'logo' => 'logo/lama.png',
            'address' => 'Jl. Lama No. 1',
            'district_id' => '3517120',
            'district_name' => 'Peterongan',
            'phone' => '081112223333',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('profile-store.update'), [
                'name' => 'Toko Baru',
                'address' => 'Jl. Baru No. 2',
                'embedded_map' => 'https://maps.example.com/embed/baru',
                'district_id' => '3517110',
                'district_name' => 'Diwek',
                'phone' => '081555666777',
                'instagram' => 'tokobaru',
                'facebook' => 'tokobaru',
                'tiktok' => '@tokobaru',
            ]);

        $response->assertSessionHas('success');

        $profile->refresh();
        $this->assertSame('Toko Baru', $profile->name);
        $this->assertSame('Jl. Baru No. 2', $profile->address);
        $this->assertSame('https://maps.example.com/embed/baru', $profile->embedded_map);
        $this->assertSame('3517110', $profile->district_id);
        $this->assertSame('Diwek', $profile->district_name);
        $this->assertSame('081555666777', $profile->phone);
        $this->assertSame('tokobaru', $profile->instagram);
        $this->assertSame('tokobaru', $profile->facebook);
        $this->assertSame('@tokobaru', $profile->tiktok);
    }

    public function test_update_profile_store_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ProfileStore::create([
            'name' => 'Toko Validate',
            'logo' => 'logo/validate.png',
            'address' => 'Jl. Validate No. 1',
            'district_id' => '3517120',
            'district_name' => 'Peterongan',
            'phone' => '081999888777',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('profile-store.update'), [
                'name' => '',
                'address' => '',
                'phone' => '',
            ]);

        $response->assertSessionHasErrors([
            'name',
            'address',
            'phone',
        ]);
    }

    public function test_update_profile_store_allows_nullable_social_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $profile = ProfileStore::create([
            'name' => 'Toko Social',
            'logo' => 'logo/social.png',
            'address' => 'Jl. Social No. 1',
            'district_id' => '3517120',
            'district_name' => 'Peterongan',
            'phone' => '081222333444',
            'instagram' => 'tokosocial',
            'facebook' => 'tokosocial',
            'tiktok' => '@tokosocial',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('profile-store.update'), [
                'name' => 'Toko Social',
                'address' => 'Jl. Social No. 1',
                'district_id' => '3517120',
                'district_name' => 'Peterongan',
                'phone' => '081222333444',
                'instagram' => '',
                'facebook' => '',
                'tiktok' => '',
            ]);

        $response->assertSessionHasNoErrors();

        $profile->refresh();
        $this->assertNull($profile->instagram);
        $this->assertNull($profile->facebook);
        $this->assertNull($profile->tiktok);
    }

    public function test_update_profile_store_creates_profile_if_none_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertDatabaseCount('profile_stores', 0);

        $response = $this->actingAs($admin)
            ->put(route('profile-store.update'), [
                'name' => 'Toko Inisial',
                'address' => 'Jl. Toko No. 10',
                'district_id' => '3517120',
                'district_name' => 'Peterongan',
                'phone' => '081234567890',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseCount('profile_stores', 1);

        $created = ProfileStore::first();
        $this->assertSame('Toko Inisial', $created->name);
        $this->assertSame('3517120', $created->district_id);
        $this->assertSame('Peterongan', $created->district_name);
    }

    public function test_update_profile_store_invalidates_cache(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $profile = ProfileStore::create([
            'name' => 'Toko Cache',
            'logo' => 'logo/cache.png',
            'address' => 'Jl. Cache',
            'district_id' => '3517120',
            'district_name' => 'Peterongan',
            'phone' => '081234567890',
        ]);

        Cache::put('store_profile', $profile);
        $this->assertTrue(Cache::has('store_profile'));

        $this->actingAs($admin)
            ->put(route('profile-store.update'), [
                'name' => 'Toko Cache Baru',
                'address' => 'Jl. Cache Baru',
                'district_id' => '3517120',
                'district_name' => 'Peterongan',
                'phone' => '081234567890',
            ]);

        $this->assertFalse(Cache::has('store_profile'));
    }
}