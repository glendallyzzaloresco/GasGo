<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Check stock_movements table
echo "Stock Movements (IN type):\n";
$stockMovements = DB::table('stock_movements')
    ->where('type', 'IN')
    ->limit(5)
    ->get();

foreach ($stockMovements as $m) {
    echo "Inventory ID: {$m->inventory_id}, Type: {$m->type}, Quantity: {$m->quantity_change}\n";
    echo "Movement Date: {$m->movement_date}\n";
    echo "Notes: {$m->notes}\n";
    echo "---\n";
}

// Check deliveries table
echo "\n\nDeliveries with dates:\n";
$deliveries = DB::table('deliveries')
    ->join('orders', 'deliveries.order_id', '=', 'orders.id')
    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
    ->join('products', 'order_items.product_id', '=', 'products.id')
    ->where('deliveries.status', 'delivered')
    ->where('products.category', 'Tank')
    ->selectRaw('products.id as product_id, SUM(order_items.quantity) as qty, MAX(deliveries.updated_at) as latest_delivery_date')
    ->groupBy('products.id')
    ->get();

foreach ($deliveries as $d) {
    echo "Product ID: {$d->product_id}\n";
    echo "Qty: {$d->qty}, Latest Delivery: {$d->latest_delivery_date}\n";
    echo "---\n";
}
