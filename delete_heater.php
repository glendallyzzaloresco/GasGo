<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Inventory;

$product = Product::where('name', 'Portable Gas Heater')->first();

if ($product) {
    // Delete associated inventory records
    Inventory::where('product_id', $product->id)->delete();
    
    // Delete the product
    $product->delete();
    
    echo "✓ Deleted: Portable Gas Heater\n";
} else {
    echo "Product not found\n";
}
