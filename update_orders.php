<?php
require_once 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Update all non-delivered orders for user 1 (or any logged-in user) to delivered
$updated = \App\Models\Order::where('user_id', 1)
    ->whereIn('status', ['pending', 'approved', 'assigned', 'out_for_delivery'])
    ->update([
        'status' => 'delivered',
        'delivered_at' => now()
    ]);

echo "Updated $updated orders to delivered status.\n";

// Show the updated orders
$orders = \App\Models\Order::where('user_id', 1)->get(['id', 'order_number', 'status', 'created_at']);
echo "\nAll orders for user 1:\n";
foreach ($orders as $order) {
    echo "- Order {$order->order_number}: {$order->status} (Created: {$order->created_at})\n";
}

$deliveredCount = \App\Models\Order::where('user_id', 1)->where('status', 'delivered')->count();
echo "\nTotal delivered orders: $deliveredCount\n";
?>
