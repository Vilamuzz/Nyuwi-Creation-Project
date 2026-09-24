<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer User',
                'role' => 'pelanggan',
                'password' => Hash::make('password123'),
            ]
        );
    }
}
