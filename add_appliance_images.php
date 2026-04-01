<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

$appliances = [
    'Single Burner Stove' => 'https://via.placeholder.com/150?text=Single+Burner+Stove',
    'Double Burner Stove' => 'https://via.placeholder.com/150?text=Double+Burner+Stove',
    'Portable Gas Heater' => 'https://via.placeholder.com/150?text=Gas+Heater'
];

foreach ($appliances as $name => $imageUrl) {
    $product = Product::where('name', $name)->first();
    if ($product) {
        $product->update(['image' => $imageUrl]);
        echo "✓ Updated image for: {$product->name}\n";
    }
}

echo "\nImages added successfully!\n";
