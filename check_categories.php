<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

$products = Product::all();
echo "=== PRODUCTS LIST ===\n";
foreach ($products as $p) {
    echo "ID: {$p->id} | Name: {$p->name} | Category: '{$p->category}'\n";
}
