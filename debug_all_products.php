<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

$products = Product::all()->sortBy('category');
$grouped = $products->groupBy(function($p) { 
    return strtolower($p->category ?? 'uncategorized');
});

echo "=== ALL PRODUCTS BY CATEGORY ===\n";
foreach($grouped as $cat => $items) {
    echo "\n" . strtoupper($cat) . ": " . count($items) . " products\n";
    foreach($items as $p) {
        $active = $p->is_active ? 'ACTIVE' : 'INACTIVE';
        echo "  [{$active}] {$p->name} (price: {$p->price})\n";
    }
}

echo "\n=== ACTIVE PRODUCTS WITH PRICE > 0 ===\n";
$activeProducts = Product::where('is_active', true)->where('price', '>', 0)->get();
echo "Total: " . count($activeProducts) . "\n";
foreach($activeProducts as $p) {
    echo "  - {$p->name} ({$p->category}) ₱{$p->price}\n";
}
