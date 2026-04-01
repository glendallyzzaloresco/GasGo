<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

// Get all tank inventories with empty_on_hand > 0
$emptyTanks = Inventory::whereHas('product', function($q) {
    $q->where('category', 'Tank');
})
->get();

echo "All Tank Inventories:\n";
echo str_repeat("=", 60) . "\n";

foreach ($emptyTanks as $inv) {
    echo "Product: {$inv->product->name}\n";
    echo "  Quantity on Hand: {$inv->quantity_on_hand}\n";
    echo "  Empty on Hand: {$inv->empty_on_hand}\n";
    echo "  Reorder Level: {$inv->reorder_level}\n";
    echo "---\n";
}

// Check delivered deliveries count
$deliveredDeliveries = DB::table('deliveries')->where('status', 'delivered')->count();
echo "\nTotal Delivered Deliveries: {$deliveredDeliveries}\n";

// Check if there are order items for tank products in delivered orders
$deliveredOrderItems = DB::table('order_items')
    ->join('orders', 'order_items.order_id', '=', 'orders.id')
    ->join('deliveries', 'orders.id', '=', 'deliveries.order_id')
    ->join('products', 'order_items.product_id', '=', 'products.id')
    ->where('deliveries.status', 'delivered')
    ->where('products.category', 'Tank')
    ->selectRaw('products.name, SUM(order_items.quantity) as total_quantity, COUNT(*) as order_count')
    ->groupBy('products.id', 'products.name')
    ->get();

echo "\nTank Products in Delivered Orders:\n";
echo str_repeat("=", 60) . "\n";
foreach ($deliveredOrderItems as $item) {
    echo "Product: {$item->name}\n";
    echo "  Total Quantity Delivered: {$item->total_quantity}\n";
    echo "  Order Count: {$item->order_count}\n";
}
