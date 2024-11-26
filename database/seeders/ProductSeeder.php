<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'Gelang Manik Crystal',
                'category_id' => 1,
                'stock' => 10,
                'price' => 50000.00,
                'description' => 'Gelang manik dengan bahan crystal berkualitas tinggi',
                'image' => 'gelang-crystal.jpg'
            ],
            [
                'name' => 'Kalung Mutiara Premium',
                'category_id' => 2,
                'stock' => 50,
                'price' => 75000.00,
                'description' => 'Kalung mutiara dengan design elegan',
                'image' => 'kalung-mutiara.jpg'
            ],
            [
                'name' => 'Cincin Couple Silver',
                'category_id' => 3,
                'stock' => 30,
                'price' => 50000.00,
                'description' => 'Cincin couple dengan bahan silver 925',
                'image' => 'cincin-couple.jpg'
            ],
            [
                'name' => 'Bunga Preserved Rose',
                'category_id' => 4,
                'stock' => 100,
                'price' => 20000.00,
                'description' => 'Bunga mawar preserved yang tahan lama',
                'image' => 'bunga-preserved.jpg'
            ],
            [
                'name' => 'Anting Tusuk Gold',
                'category_id' => 5,
                'stock' => 5,
                'price' => 150000.00,
                'description' => 'Anting tusuk dengan lapisan emas 24k',
                'image' => 'anting-gold.jpg'
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
