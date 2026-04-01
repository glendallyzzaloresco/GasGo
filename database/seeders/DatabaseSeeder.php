<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin account
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gasgo.com',
            'password' => 'admin123',
            'role' => 'admin',
        ]);

        // Rider account
        User::factory()->create([
            'name' => 'Rider',
            'email' => 'rider@gasgo.com',
            'password' => 'rider123',
            'role' => 'rider',
        ]);

        // Customer account
        User::factory()->create([
            'name' => 'Customer',
            'email' => 'customer@gasgo.com',
            'password' => 'customer123',
            'role' => 'customer',
        ]);

        // Products
        Product::create([
            'name' => 'LPG Tank 11kg',
            'description' => '11 Kilogram LPG Tank',
            'price' => 850.00,
            'stock' => 100,
            'weight' => '11kg',
            'image' => 'images/11kg.jpg',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'LPG Tank 22kg',
            'description' => '22 Kilogram LPG Tank',
            'price' => 1600.00,
            'stock' => 50,
            'weight' => '22kg',
            'image' => 'images/22kg.jpg',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'LPG Tank 2kg',
            'description' => '2 Kilogram LPG Tank',
            'price' => 350.00,
            'stock' => 200,
            'weight' => '2kg',
            'image' => 'images/2kg.jpg',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'LPG Regulator',
            'category' => 'accessory',
            'description' => 'Safety LPG Regulator',
            'price' => 450.00,
            'stock' => 150,
            'weight' => '0.5kg',
            'image' => null,
            'is_active' => true,
        ]);

        // Freebie Products for Loyalty Rewards
        Product::create([
            'name' => 'Free LPG Tank (Reward)',
            'description' => 'Complimentary LPG Tank - Loyalty Reward for Bulk Orders',
            'price' => 0.00,
            'stock' => 999,
            'weight' => '11kg',
            'image' => 'images/11kg.jpg',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Dish Washer Paste (Freebie)',
            'description' => 'Free Dish Washer Paste - Small Order Loyalty Reward',
            'price' => 0.00,
            'stock' => 999,
            'weight' => '0.2kg',
            'image' => null,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Cloth Hanger Set (Freebie)',
            'description' => 'Free Cloth Hanger Set - Small Order Loyalty Reward',
            'price' => 0.00,
            'stock' => 999,
            'weight' => '0.1kg',
            'image' => null,
            'is_active' => true,
        ]);
    }
}
