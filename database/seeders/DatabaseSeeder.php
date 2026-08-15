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
        User::firstOrCreate(
            ['email' => 'admin@gasgo.com'],
            [
                'name'     => 'Admin',
                'password' => bcrypt('admin123'),
                'role'     => 'admin',
            ]
        );

        // Rider account
        $riderUser = User::firstOrCreate(
            ['email' => 'rider@gasgo.com'],
            [
                'name'     => 'Rider',
                'password' => bcrypt('rider123'),
                'role'     => 'rider',
            ]
        );

        \App\Models\Rider::firstOrCreate(
            ['user_id' => $riderUser->id],
            [
                'availability' => 'available',
                'vehicle_type' => 'Motorcycle',
                'plate_number' => 'ABC-1234',
            ]
        );

        // Customer account
        User::firstOrCreate(
            ['email' => 'customer@gasgo.com'],
            [
                'name'     => 'Customer',
                'password' => bcrypt('customer123'),
                'role'     => 'customer',
            ]
        );

        // Products
        Product::updateOrCreate(
            ['name' => 'LPG Tank 11kg'],
            [
                'category'    => 'tank',
                'description' => '11 Kilogram LPG Tank',
                'price'       => 850.00,
                'stock'       => 100,
                'weight'      => '11kg',
                'image'       => 'images/11kg.jpg',
                'is_active'   => true,
            ]
        );

        Product::updateOrCreate(
            ['name' => 'LPG Tank 22kg'],
            [
                'category'    => 'tank',
                'description' => '22 Kilogram LPG Tank',
                'price'       => 1600.00,
                'stock'       => 50,
                'weight'      => '22kg',
                'image'       => 'images/22kg.jpg',
                'is_active'   => true,
            ]
        );

        Product::updateOrCreate(
            ['name' => 'LPG Tank 2kg'],
            [
                'category'    => 'tank',
                'description' => '2 Kilogram LPG Tank',
                'price'       => 350.00,
                'stock'       => 200,
                'weight'      => '2kg',
                'image'       => 'images/2kg.jpg',
                'is_active'   => true,
            ]
        );

        Product::updateOrCreate(
            ['name' => 'LPG Regulator'],
            [
                'category'    => 'accessories',
                'description' => 'Safety LPG Regulator',
                'price'       => 450.00,
                'stock'       => 150,
                'weight'      => '0.5kg',
                'image'       => null,
                'is_active'   => true,
            ]
        );

        // Freebie Products for Loyalty Rewards
        Product::updateOrCreate(
            ['name' => 'Free LPG Tank (Reward)'],
            [
                'category'    => 'freebie',
                'description' => 'Complimentary LPG Tank - Loyalty Reward for Bulk Orders',
                'price'       => 0.00,
                'stock'       => 999,
                'weight'      => '11kg',
                'image'       => 'images/11kg.jpg',
                'is_active'   => true,
            ]
        );

        Product::updateOrCreate(
            ['name' => 'Dish Washer Paste (Freebie)'],
            [
                'category'    => 'freebie',
                'description' => 'Free Dish Washer Paste - Small Order Loyalty Reward',
                'price'       => 0.00,
                'stock'       => 999,
                'weight'      => '0.2kg',
                'image'       => null,
                'is_active'   => true,
            ]
        );

        Product::updateOrCreate(
            ['name' => 'Cloth Hanger Set (Freebie)'],
            [
                'category'    => 'freebie',
                'description' => 'Free Cloth Hanger Set - Small Order Loyalty Reward',
                'price'       => 0.00,
                'stock'       => 999,
                'weight'      => '0.1kg',
                'image'       => null,
                'is_active'   => true,
            ]
        );
    }
}
