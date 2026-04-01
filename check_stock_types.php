<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\StockMovement;

// Get distinct types of stock movements
$types = StockMovement::distinct()->pluck('type')->toArray();

echo "Stock Movement Types in Database:\n";
foreach ($types as $type) {
    echo "- " . $type . "\n";
}
