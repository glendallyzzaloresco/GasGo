<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\InventoryMovement;

// Get distinct types of inventory movements
$types = InventoryMovement::distinct()->pluck('type')->toArray();

echo "Inventory Movement Types in Database:\n";
foreach ($types as $type) {
    echo "- " . $type . "\n";
}

echo "\n\nSample movements:\n";
$movements = InventoryMovement::with('product')->limit(5)->orderBy('movement_date', 'desc')->get();
foreach ($movements as $m) {
    echo "Type: {$m->type}, Quantity: {$m->quantity}, Reference: {$m->reference_type}, Date: " . $m->movement_date->format('Y-m-d H:i:s') . "\n";
}
