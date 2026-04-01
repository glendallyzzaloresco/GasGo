<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

// Check all appliance products
$appliances = Product::where('category', 'Appliance')->get();

echo "Appliance Products in Database:\n";
foreach ($appliances as $product) {
    echo "\nProduct: {$product->name}\n";
    echo "  Image: {$product->image}\n";
    echo "  Image URL: {$product->image_url}\n";
}

echo "\n\n";
echo "Checking for similar products:\n";
$similar = Product::where('name', 'like', '%Burner%')->orWhere('name', 'like', '%Stove%')->get();
foreach ($similar as $product) {
    echo "\nProduct: {$product->name} (Category: {$product->category})\n";
    echo "  Image: {$product->image}\n";
}
