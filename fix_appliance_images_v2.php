<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

// Update appliances to use public asset URLs
$updates = [
    'Single Burner Stove' => asset('images/2kg.jpg'),
    'Double Burner Stove' => asset('images/22kg.jpg'),
    'Portable Gas Heater' => asset('images/5kg.png')
];

foreach ($updates as $name => $imagePath) {
    $product = Product::where('name', $name)->first();
    if ($product) {
        $product->update(['image' => $imagePath]);
        echo "✓ Updated {$product->name} with image: {$imagePath}\n";
    }
}

echo "\nDone!\n";
