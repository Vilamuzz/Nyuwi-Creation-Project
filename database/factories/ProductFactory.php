<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'nama' => $this->faker->word,
            'jumlah_stok' => $this->faker->numberBetween(1, 100),
            'harga' => $this->faker->randomFloat(2, 10, 1000),
            'gambar' => $this->faker->imageUrl(640, 480, 'product', true),
        ];
    }
}
