<?php

namespace Database\Seeders;

use App\Models\ProductColor;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductColorSeeder extends Seeder
{
    public function run()
    {
        $productColors = [
            6 => ['#e72323', '#c49292', '#cfc9a5'], // Colors for Bucket Graduation
            // Add more product colors as needed
        ];

        foreach ($productColors as $productId => $colors) {
            foreach ($colors as $color) {
                ProductColor::create([
                    'product_id' => $productId,
                    'color' => $color
                ]);
            }
        }
    }
}
