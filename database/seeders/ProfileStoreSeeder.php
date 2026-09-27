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
        ProfileStore::firstOrCreate(
            ['name' => 'Nyuwi Creation'],
            [
                'logo' => 'logo/logo.svg',
                'address' => 'Jl. Mawar No. 123, Kecamatan Peterongan',
                'city' => 'Jombang',
                'shipping_origin_city_id' => '3517',
                'shipping_origin_district_id' => '3517120',
                'shipping_couriers' => ['jne', 'pos', 'tiki'],
                'phone' => '081234567890',
                'qris' => 'barcode.jpg',
                'instagram' => 'nyuwi_creation',
                'facebook' => 'nyuwicreation',
                'tiktok' => '@nyuwicreation',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
