<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Inventory;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

// Get all tank products that have been delivered
$deliveredOrderItems = DB::table('order_items')
    ->join('orders', 'order_items.order_id', '=', 'orders.id')
    ->join('deliveries', 'orders.id', '=', 'deliveries.order_id')
    ->join('products', 'order_items.product_id', '=', 'products.id')
    ->where('deliveries.status', 'delivered')
    ->where('products.category', 'Tank')
    ->selectRaw('products.id, SUM(order_items.quantity) as total_quantity')
    ->groupBy('products.id')
    ->get();

echo "Updating empty tanks for delivered orders:\n";
echo str_repeat("=", 60) . "\n";

foreach ($deliveredOrderItems as $item) {
    $inventory = Inventory::where('product_id', $item->id)->first();
    
    if ($inventory) {
        $product = $inventory->product;
        $oldEmpty = $inventory->empty_on_hand;
        
        // Set empty_on_hand to the total delivered
        $inventory->update(['empty_on_hand' => $item->total_quantity]);
        
        echo "Product: {$product->name}\n";
        echo "  Previous Empty: {$oldEmpty}\n";
        echo "  Updated Empty: {$item->total_quantity}\n";
        echo "---\n";
    }
}

echo "\nEmpty tanks updated successfully!\n";
