<?php

namespace Database\Seeders;

use App\Models\ProfileStore;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProfileStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProfileStore::updateOrCreate(
            ['name' => 'Nyuwi Creation'],
            [
                'logo' => '/storage/logo/logo.svg',
                'address' => 'Jl. Mawar No. 123, Kecamatan Peterongan',
                'embedded_map' => '',
                'district_id' => '3866',
                'district_name' => 'Peterongan',
                'shipping_couriers' => ['jne', 'pos', 'tiki'],
                'phone' => '081234567890',
                'instagram' => 'nyuwi_creation',
                'facebook' => 'nyuwicreation',
                'tiktok' => '@nyuwicreation',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
