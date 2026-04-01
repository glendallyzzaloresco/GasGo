<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

// Check some existing products to see how their images are stored
$products = Product::whereNotNull('image')->limit(3)->get();

foreach ($products as $product) {
    echo "Product: {$product->name}\n";
    echo "  Image field: {$product->image}\n";
    echo "  Image URL: {$product->image_url}\n";
    echo "\n";
}
