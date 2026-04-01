<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

$appliances = Product::where('category', 'Appliance')->get();

foreach ($appliances as $product) {
    echo "Product: {$product->name}\n";
    echo "  Image: {$product->image}\n";
    echo "  Image URL: {$product->image_url}\n";
    echo "\n";
}
