<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Inventory;

$appliances = [
    [
        'name' => 'Single Burner Stove',
        'category' => 'Appliance',
        'price' => 1500,
        'description' => 'Single burner cooking stove',
        'is_active' => true
    ],
    [
        'name' => 'Double Burner Stove',
        'category' => 'Appliance',
        'price' => 2500,
        'description' => 'Double burner cooking stove',
        'is_active' => true
    ],
    [
        'name' => 'Portable Gas Heater',
        'category' => 'Appliance',
        'price' => 3500,
        'description' => 'Portable gas heating unit',
        'is_active' => true
    ]
];

foreach ($appliances as $data) {
    $product = Product::firstOrCreate(
        ['name' => $data['name']],
        $data
    );
    
    // Create inventory record
    Inventory::firstOrCreate(
        ['product_id' => $product->id],
        [
            'quantity_on_hand' => rand(5, 20),
            'reorder_level' => 5,
            'status' => 'active',
            'supplier' => 'Premium Gas Co.',
            'last_restocked' => now(),
        ]
    );
    
    echo "✓ Created: {$product->name}\n";
}

echo "\nAppliance products created successfully!\n";
