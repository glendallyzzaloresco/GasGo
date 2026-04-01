<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\StockMovement;
use Carbon\Carbon;

// Get today's movements
$today = Carbon::now()->startOfDay();
$todayEnd = Carbon::now()->endOfDay();

$movements = StockMovement::whereBetween('movement_date', [$today, $todayEnd])
    ->with('inventory.product')
    ->orderBy('movement_date', 'desc')
    ->get();

echo "Stock Movements Today:\n";
echo "Start: " . $today->format('Y-m-d H:i:s') . "\n";
echo "End: " . $todayEnd->format('Y-m-d H:i:s') . "\n";
echo "Current Time: " . now()->format('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 60) . "\n";
echo "Total movements: " . $movements->count() . "\n\n";

foreach ($movements as $m) {
    echo "Product: {$m->inventory->product->name}\n";
    echo "  Type: {$m->type}\n";
    echo "  Quantity Change: {$m->quantity_change}\n";
    echo "  Date: {$m->movement_date}\n";
    echo "  Notes: {$m->notes}\n";
    echo "---\n";
}

// Also check stock_in only
echo "\n\nStock IN Only:\n";
$stockIn = StockMovement::where('type', 'stock_in')
    ->whereBetween('movement_date', [$today, $todayEnd])
    ->with('inventory.product')
    ->get();

echo "Total: " . $stockIn->count() . "\n";
