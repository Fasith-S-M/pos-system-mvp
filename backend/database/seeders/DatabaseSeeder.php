<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create an admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@pos.com',
            'password' => Hash::make('password'),
        ]);

        // Create dummy products
        $products = [
            ['name' => 'Wireless Mouse', 'price' => 25.99, 'stock' => 50],
            ['name' => 'Mechanical Keyboard', 'price' => 89.99, 'stock' => 30],
            ['name' => '27-inch Monitor', 'price' => 199.99, 'stock' => 15],
            ['name' => 'USB-C Hub', 'price' => 45.00, 'stock' => 100],
            ['name' => 'Gaming Headset', 'price' => 59.50, 'stock' => 40],
            ['name' => 'Webcam 1080p', 'price' => 35.00, 'stock' => 25],
            ['name' => 'Mousepad Large', 'price' => 15.99, 'stock' => 200],
            ['name' => 'Laptop Stand', 'price' => 29.99, 'stock' => 60],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
