<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['id' => 1, 'name' => 'Aksesoris'],
            ['id' => 2, 'name' => 'Buket'],
            ['id' => 3, 'name' => 'Dekorasi'],
            ['id' => 4, 'name' => 'Bunga'],
            ['id' => 5, 'name' => 'Tas'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], ['id' => $category['id']]);
        }
    }
}
