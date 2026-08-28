<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Rider;
use App\Models\HomepageSetting;
use App\Models\SiteTheme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users
        // Admin Account
        $admin = User::firstOrCreate(
            ['email' => 'admin@gasgo.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                'phone' => '09123456789',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Customer Account
        $customer = User::firstOrCreate(
            ['email' => 'customer@gasgo.com'],
            [
                'name' => 'Juan Dela Cruz',
                'password' => Hash::make('password'),
                'phone' => '09987654321',
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );

        // Rider Account
        $riderUser = User::firstOrCreate(
            ['email' => 'rider@gasgo.com'],
            [
                'name' => 'Pedro Penduko',
                'password' => Hash::make('password'),
                'phone' => '09112233445',
                'role' => 'rider',
                'email_verified_at' => now(),
            ]
        );

        // Rider Profile
        Rider::firstOrCreate(
            ['user_id' => $riderUser->id],
            [
                'vehicle_type' => 'Motorcycle',
                'plate_number' => 'ABC-1234',
                'license_number' => 'N01-12-345678',
                'availability' => 'available',
                'current_latitude' => 14.5995,
                'current_longitude' => 120.9842,
            ]
        );

        // 2. Categories
        $lpgCategory = Category::firstOrCreate(
            ['slug' => 'lpg-tanks'],
            [
                'name' => 'LPG Tanks',
                'description' => 'Main LPG tanks and refill cylinders',
                'icon_class' => 'fas fa-fire',
                'color_code' => '#1a6db0',
                'is_active' => true,
            ]
        );

        $accessoriesCategory = Category::firstOrCreate(
            ['slug' => 'accessories'],
            [
                'name' => 'Accessories',
                'description' => 'Hoses, regulators, clamps, and fittings',
                'icon_class' => 'fas fa-tools',
                'color_code' => '#f7941d',
                'is_active' => true,
            ]
        );

        $appliancesCategory = Category::firstOrCreate(
            ['slug' => 'appliances'],
            [
                'name' => 'Appliances',
                'description' => 'Gas stoves, burners, and kitchen equipment',
                'icon_class' => 'fas fa-store',
                'color_code' => '#27ae60',
                'is_active' => true,
            ]
        );

        // 3. Products & Inventories
        $products = [
            // LPG Tanks
            [
                'name' => '11kg LPG Cylinder Refill',
                'category_id' => $lpgCategory->id,
                'description' => 'Standard 11kg household LPG tank refill. High efficiency and safety tested.',
                'price' => 950.00,
                'cost_price' => 820.00,
                'selling_price' => 950.00,
                'weight' => '11.00',
                'image' => 'images/11kg.jpg',
                'requires_exchange' => true,
                'is_active' => true,
                'stock_qty' => 50,
            ],
            [
                'name' => '22kg LPG Cylinder Refill',
                'category_id' => $lpgCategory->id,
                'description' => 'Commercial size 22kg LPG cylinder for restaurants and heavy kitchen use.',
                'price' => 1850.00,
                'cost_price' => 1600.00,
                'selling_price' => 1850.00,
                'weight' => '22.00',
                'image' => 'images/22kg.jpg',
                'requires_exchange' => true,
                'is_active' => true,
                'stock_qty' => 30,
            ],
            [
                'name' => '5kg LPG Cylinder Refill',
                'category_id' => $lpgCategory->id,
                'description' => 'Compact 5kg LPG tank for small households, condos, and outdoor cooking.',
                'price' => 480.00,
                'cost_price' => 400.00,
                'selling_price' => 480.00,
                'weight' => '5.00',
                'image' => 'images/5kg.png',
                'requires_exchange' => true,
                'is_active' => true,
                'stock_qty' => 40,
            ],
            [
                'name' => '2.7kg LPG Cylinder Refill',
                'category_id' => $lpgCategory->id,
                'description' => 'Mini portable 2.7kg cylinder for camping and portable stoves.',
                'price' => 320.00,
                'cost_price' => 260.00,
                'selling_price' => 320.00,
                'weight' => '2.70',
                'image' => 'images/2kg.jpg',
                'requires_exchange' => true,
                'is_active' => true,
                'stock_qty' => 35,
            ],

            // Accessories
            [
                'name' => 'LPG Heavy Duty Hose (1.5m)',
                'category_id' => $accessoriesCategory->id,
                'description' => 'Reinforced rubber safety gas hose with heavy-duty metal clamps.',
                'price' => 250.00,
                'cost_price' => 160.00,
                'selling_price' => 250.00,
                'weight' => '0.50',
                'image' => 'images/default-product.png',
                'requires_exchange' => false,
                'is_active' => true,
                'stock_qty' => 60,
            ],
            [
                'name' => 'Low Pressure LPG Regulator with Gauge',
                'category_id' => $accessoriesCategory->id,
                'description' => 'Safety auto-shutoff LPG regulator with built-in pressure meter.',
                'price' => 650.00,
                'cost_price' => 450.00,
                'selling_price' => 650.00,
                'weight' => '0.60',
                'image' => 'images/default-product.png',
                'requires_exchange' => false,
                'is_active' => true,
                'stock_qty' => 35,
            ],
            [
                'name' => 'High Pressure LPG Regulator',
                'category_id' => $accessoriesCategory->id,
                'description' => 'Industrial high pressure regulator for high output burners.',
                'price' => 450.00,
                'cost_price' => 300.00,
                'selling_price' => 450.00,
                'weight' => '0.40',
                'image' => 'images/default-product.png',
                'requires_exchange' => false,
                'is_active' => true,
                'stock_qty' => 40,
            ],

            // Appliances
            [
                'name' => 'Single Burner Gas Stove',
                'category_id' => $appliancesCategory->id,
                'description' => 'Stainless steel tabletop single burner gas stove with auto ignition.',
                'price' => 850.00,
                'cost_price' => 600.00,
                'selling_price' => 850.00,
                'weight' => '1.80',
                'image' => 'images/default-product.png',
                'requires_exchange' => false,
                'is_active' => true,
                'stock_qty' => 25,
            ],
            [
                'name' => 'Double Burner Gas Stove',
                'category_id' => $appliancesCategory->id,
                'description' => 'Heavy duty double burner cooking stove with tempered glass top.',
                'price' => 1650.00,
                'cost_price' => 1200.00,
                'selling_price' => 1650.00,
                'weight' => '3.20',
                'image' => 'images/default-product.png',
                'requires_exchange' => false,
                'is_active' => true,
                'stock_qty' => 20,
            ],
            [
                'name' => 'Portable Camping Butane Stove',
                'category_id' => $appliancesCategory->id,
                'description' => 'Windproof portable cassette gas stove in carrying case.',
                'price' => 1200.00,
                'cost_price' => 850.00,
                'selling_price' => 1200.00,
                'weight' => '1.50',
                'image' => 'images/default-product.png',
                'requires_exchange' => false,
                'is_active' => true,
                'stock_qty' => 15,
            ],
        ];

        foreach ($products as $item) {
            $stockQty = $item['stock_qty'];
            unset($item['stock_qty']);

            $product = Product::updateOrCreate(
                ['name' => $item['name']],
                $item
            );

            Inventory::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity_on_hand' => $stockQty,
                    'status' => 'active',
                    'supplier' => 'GasGo Direct Supply',
                    'expiry_date' => now()->addYears(2),
                    'last_restocked' => now(),
                ]
            );
        }

        // 4. Homepage Settings
        HomepageSetting::singleton();

        // 5. Site Theme
        SiteTheme::firstOrCreate(
            ['id' => 1],
            [
                'primaryColor' => '#1a6db0',
                'accentColor' => '#f7941d',
                'backgroundColor' => '#f8fafc',
                'sidebarBackground' => '#0f172a',
                'logoUrl' => 'images/logo-gasgo.png',
                'footerDescription' => 'GasGo - Reliable LPG and Gas Appliance Delivery',
                'contactAddress' => 'Metro Manila, Philippines',
                'contactPhone' => '09123456789',
            ]
        );
    }
}
