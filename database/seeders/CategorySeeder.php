<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Aksesoris'],
            ['name' => 'Buket'],
            ['name' => 'Dekorasi']
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
