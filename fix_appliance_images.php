<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

// Update appliances to use actual image files instead of placeholder URLs
$updates = [
    'Single Burner Stove' => 'images/2kg.jpg',
    'Double Burner Stove' => 'images/22kg.jpg',
    'Portable Gas Heater' => 'images/5kg.png'
];

foreach ($updates as $name => $imagePath) {
    $product = Product::where('name', $name)->first();
    if ($product) {
        $product->update(['image' => $imagePath]);
        echo "✓ Updated {$product->name} with image: {$imagePath}\n";
    }
}

echo "\nDone!\n";
