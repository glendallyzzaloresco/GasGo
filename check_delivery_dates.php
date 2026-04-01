<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Check if inventory_movements table exists and has data
$movements = DB::table('inventory_movements')
    ->where('type', 'IN')
    ->where('notes', 'like', '%Empty tank%')
    ->limit(5)
    ->get();

echo "Inventory Movements with Empty Tank:\n";
echo count($movements) . " records found\n\n";
foreach ($movements as $m) {
    echo "Product ID: {$m->product_id}, Type: {$m->type}, Quantity: {$m->quantity}\n";
    echo "Movement Date: {$m->movement_date}\n";
    echo "Notes: {$m->notes}\n";
    echo "---\n";
}

// Check deliveries table for delivered orders
echo "\n\nDelivered Orders with Dates:\n";
$deliveries = DB::table('deliveries')
    ->join('orders', 'deliveries.order_id', '=', 'orders.id')
    ->where('deliveries.status', 'delivered')
    ->selectRaw('deliveries.id, deliveries.order_id, deliveries.status, orders.created_at as order_date, deliveries.updated_at as delivery_date')
    ->orderBy('deliveries.updated_at', 'desc')
    ->limit(5)
    ->get();

foreach ($deliveries as $d) {
    echo "Delivery ID: {$d->id}, Order ID: {$d->order_id}\n";
    echo "Delivery Date: {$d->delivery_date}\n";
    echo "---\n";
}
