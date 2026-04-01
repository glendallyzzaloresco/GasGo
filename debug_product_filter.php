<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

$products = Product::with('inventory')
    ->where('is_active', true)
    ->where('price', '>', 0)
    ->orderBy('name')
    ->get();

echo "=== PRODUCTS FOR FILTER DEBUG ===\n";
echo "Total products: " . count($products) . "\n\n";

$categoryMap = [
    'tank' => 'lpg',
    'lpg' => 'lpg',
    'tanks' => 'lpg',
    'appliance' => 'appliances',
    'appliances' => 'appliances',
    'accessory' => 'accessories',
    'accessories' => 'accessories',
    'freebie' => 'accessories'
];

$grouped = [];
foreach ($products as $product) {
    $productCategory = strtolower((string) ($product->category ?? 'accessory'));
    $category = $categoryMap[$productCategory] ?? 'accessories';
    
    if (!isset($grouped[$category])) {
        $grouped[$category] = [];
    }
    $grouped[$category][] = $product;
    
    echo "Product: {$product->name}\n";
    echo "  DB Category: {$product->category}\n";
    echo "  Normalized: $productCategory\n";
    echo "  Filter Category: $category\n";
    echo "  Price: {$product->price}\n\n";
}

echo "\n=== GROUPED BY FILTER CATEGORY ===\n";
foreach ($grouped as $cat => $items) {
    echo "$cat: " . count($items) . " items\n";
    foreach ($items as $item) {
        echo "  - {$item->name}\n";
    }
}
