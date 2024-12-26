<?php

namespace Database\Seeders;

use App\Models\ProductSize;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSizeSeeder extends Seeder
{
    public function run()
    {
        $productSizes = [
            6 => ['Besar', 'Sedang', 'Kecil'], // Sizes for Bucket Graduation
            // Add more product sizes as needed
        ];

        foreach ($productSizes as $productId => $sizes) {
            foreach ($sizes as $size) {
                ProductSize::create([
                    'product_id' => $productId,
                    'size' => $size
                ]);
            }
        }
    }
}
