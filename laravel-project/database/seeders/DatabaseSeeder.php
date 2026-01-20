<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create regular user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Create products
        $categories = ['Electronics', 'Clothing', 'Books', 'Food', 'Toys'];
        
        for ($i = 1; $i <= 10; $i++) {
            Product::create([
                'name' => "Product {$i}",
                'description' => "Description for product {$i}",
                'price' => rand(1000, 10000) / 100, // Random price between 10 and 100
                'stock' => rand(0, 100),
                'category' => $categories[array_rand($categories)],
            ]);
        }

        // Create some orders for the regular user
        for ($i = 1; $i <= 3; $i++) {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => ['pending', 'processing', 'completed'][array_rand(['pending', 'processing', 'completed'])],
                'total_amount' => rand(5000, 50000) / 100,
            ]);
        }
    }
}

