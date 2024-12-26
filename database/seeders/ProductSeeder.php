<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'id' => 1,
                'name' => 'Garden',
                'category_id' => 1,
                'stock' => 10,
                'price' => '5000.00',
                'description' => 'Cincin manik yang dirangkai untuk menghiasi jari jarimu! Cocok untuk beraktifitas harian di dalam ruangan pilihan warna yang tersedia beragam bisa didesuaikan dengan gaya mu.',
                'image' => '1735005876.jpg',
                'created_at' => '2024-12-23 19:04:37',
                'updated_at' => '2024-12-23 19:04:37'
            ],
            [
                'id' => 2,
                'name' => 'Mutiara',
                'category_id' => 1,
                'stock' => 10,
                'price' => '3000.00',
                'description' => 'Gelang mutiara imitasi yang ringan dan cocok untuk segala warna kulit eksotis, dengan pengait yang bisa disesuaikan jangan takut apabila kamu suka gelang yang longgar di tangan.',
                'image' => '1735006004.jpg',
                'created_at' => '2024-12-23 19:06:45',
                'updated_at' => '2024-12-23 19:07:02'
            ],
            [
                'id' => 3,
                'name' => 'Buket Kopi',
                'category_id' => 2,
                'stock' => 10,
                'price' => '55000.00',
                'description' => 'isi 20 pc kopi nescafe, tanpa bunga, bisa pilih warna wrapping',
                'image' => '1735006401.jpg',
                'created_at' => '2024-12-23 19:13:21',
                'updated_at' => '2024-12-23 19:13:21'
            ],
            [
                'id' => 4,
                'name' => 'Buket Snack',
                'category_id' => 2,
                'stock' => 10,
                'price' => '35000.00',
                'description' => 'isi 8 pc jajanan 2rban, tanpa bunga, bisa pilih wrapping',
                'image' => '1735006520.jpg',
                'created_at' => '2024-12-23 19:15:20',
                'updated_at' => '2024-12-23 19:15:20'
            ],
            [
                'id' => 5,
                'name' => 'Buket Bunga Boneka',
                'category_id' => 2,
                'stock' => 10,
                'price' => '80000.00',
                'description' => 'Buket graduation ini bisa di request tanpa topi wisuda, buket sudah dilengkapi dengan bunga yang disusun penuh dan hiasan kupu kupu, warna wrapping dan bunga bisa direquest sesuai keinginan',
                'image' => '1735006868.jpg',
                'created_at' => '2024-12-23 19:21:08',
                'updated_at' => '2024-12-23 19:21:08'
            ],
            [
                'id' => 6,
                'name' => 'Bucket Graduation',
                'category_id' => 2,
                'stock' => 10,
                'price' => '75000.00',
                'description' => 'Buket graduation ini bisa di request tanpa topi wisuda, buket sudah dilengkapi dengan bunga yang disusun penuh dan hiasan kupu kupu, warna wrapping dan bunga bisa direquest sesuai keinginan',
                'image' => '1735007132.jpg',
                'created_at' => '2024-12-23 19:25:32',
                'updated_at' => '2024-12-23 19:25:32'
            ],
            [
                'id' => 7,
                'name' => 'Hexagon Frame',
                'category_id' => 3,
                'stock' => 10,
                'price' => '40000.00',
                'description' => 'Frame terbuat dari stick eskrim akasia kualitas terbaik, dilengkapi lampu tumbler dan buket bunga kering mini. isian frame bisa di request sesuai kebutuhan maximal 3 foto dan 1 paragraph kata kata bisa ditambah Judul, Nama, Tanggal',
                'image' => '1735007208.jpg',
                'created_at' => '2024-12-23 19:26:48',
                'updated_at' => '2024-12-23 19:26:48'
            ],
            [
                'id' => 8,
                'name' => 'Lukisan kecil',
                'category_id' => 3,
                'stock' => 10,
                'price' => '50000.00',
                'description' => 'Lukisan kecil untuk dekorasi ruangan',
                'image' => '1735007324.jpg',
                'created_at' => '2024-12-23 19:28:44',
                'updated_at' => '2024-12-23 19:28:44'
            ],
            [
                'id' => 9,
                'name' => 'Buket Bunga Couple',
                'category_id' => 2,
                'stock' => 10,
                'price' => '55000.00',
                'description' => 'Buket bunga yang dapat diberikan untuk sepasang kekasih',
                'image' => '1735007406.jpg',
                'created_at' => '2024-12-23 19:30:06',
                'updated_at' => '2024-12-23 19:30:06'
            ],
            [
                'id' => 10,
                'name' => 'Buket Bunga Dua Warna',
                'category_id' => 2,
                'stock' => 5,
                'price' => '60000.00',
                'description' => 'Buket dua warna',
                'image' => '1735007534.jpg',
                'created_at' => '2024-12-23 19:32:14',
                'updated_at' => '2024-12-23 19:32:14'
            ],
            [
                'id' => 11,
                'name' => 'Kalung Mutiara Replika',
                'category_id' => 1,
                'stock' => 10,
                'price' => '20000.00',
                'description' => 'Kalung dengan mutiara replika',
                'image' => '1735007625.jpg',
                'created_at' => '2024-12-23 19:33:45',
                'updated_at' => '2024-12-23 19:33:45'
            ],
            [
                'id' => 12,
                'name' => 'Kalung Kupu-Kupu Silver',
                'category_id' => 1,
                'stock' => 15,
                'price' => '20000.00',
                'description' => 'Kalung kupu kupu berwarna silver',
                'image' => '1735007707.jpg',
                'created_at' => '2024-12-23 19:35:07',
                'updated_at' => '2024-12-23 19:35:07'
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
