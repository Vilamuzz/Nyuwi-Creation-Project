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
            ['name' => 'Produk 1', 'category_id' => 1, 'stock' => 10, 'price' => 5000000.00],
            ['name' => 'Produk 2', 'category_id' => 2, 'stock' => 50, 'price' => 75000.00],
            ['name' => 'Produk 3', 'category_id' => 3, 'stock' => 30, 'price' => 50000.00],
            ['name' => 'Produk 4', 'category_id' => 4, 'stock' => 100, 'price' => 20000.00],
            ['name' => 'Produk 5', 'category_id' => 5, 'stock' => 5, 'price' => 150000.00],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
