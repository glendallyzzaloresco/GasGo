<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

// Update appliances to use the proper images
$updates = [
    'Single Burner Stove' => 'products/fXDU1GfjOHem9L8MxMpuHi9DpZgMPbpGAQl4LeSW.webp',
    'Double Burner Stove' => 'products/X6DWDzdk5FWBXDtCZvfkXIqP2xxU0ZkxoZtfEBnI.webp',
    'Portable Gas Heater' => 'products/X6DWDzdk5FWBXDtCZvfkXIqP2xxU0ZkxoZtfEBnI.webp' // Using double burner as placeholder
];

foreach ($updates as $name => $imagePath) {
    $product = Product::where('name', $name)->first();
    if ($product) {
        $product->update(['image' => $imagePath]);
        echo "✓ Updated {$product->name}\n";
        echo "  Image: {$product->image}\n";
        echo "  Image URL: {$product->image_url}\n\n";
    }
}

echo "Done!\n";
