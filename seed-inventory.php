<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Inventory;

// First, check how many products exist
$totalProducts = Product::count();
echo "Total products in database: $totalProducts\n";

// Get all products (not just active ones)
$products = Product::limit(5)->get();
echo "Creating inventory for " . count($products) . " products...\n";

$count = 0;
foreach ($products as $product) {
    $exists = Inventory::where('product_id', $product->id)->exists();
    if (!$exists) {
        Inventory::create([
            'product_id' => $product->id,
            'quantity_on_hand' => rand(10, 50),
            'reorder_level' => rand(5, 15),
            'supplier' => 'Premium Gas Co.',
            'status' => 'active',
            'expiry_date' => now()->addMonths(rand(3, 12)),
            'batch_number' => 'BATCH' . date('YmdHi'),
            'last_restocked' => now()->subDays(rand(1, 30))
        ]);
        $count++;
        echo "✓ Created inventory for: " . $product->name . "\n";
    }
}

echo "\nTotal newly created: $count\n";
