<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Models\Product;

// Get all active products
$products = Product::where('is_active', true)
    ->where('price', '>', 0)
    ->get();

echo "=== ALL ACTIVE PRODUCTS ===\n";
echo "Total: " . count($products) . "\n\n";

foreach ($products as $product) {
    echo "ID: {$product->id} | Name: {$product->name} | Category: {$product->category} | Price: {$product->price}\n";
}

echo "\n=== PRODUCTS BY CATEGORY ===\n";
$grouped = $products->groupBy('category');
foreach ($grouped as $category => $items) {
    echo "$category: " . count($items) . " products\n";
    foreach ($items as $product) {
        echo "  - {$product->name}\n";
    }
}
