<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "DEBUG: Stock Received Today\n";
echo str_repeat("=", 60) . "\n\n";

$today = Carbon::now()->startOfDay();
$todayEnd = Carbon::now()->endOfDay();

echo "Date Range:\n";
echo "Start: " . $today->format('Y-m-d H:i:s') . "\n";
echo "End: " . $todayEnd->format('Y-m-d H:i:s') . "\n";
echo "Current Time: " . now()->format('Y-m-d H:i:s') . "\n\n";

// Check for stock_in movements today
echo "STOCK IN Movements Today:\n";
$stockIn = StockMovement::where('type', 'stock_in')
    ->whereBetween('movement_date', [$today, $todayEnd])
    ->with('inventory.product')
    ->orderBy('movement_date', 'desc')
    ->get();

echo "Count: " . $stockIn->count() . "\n";
foreach ($stockIn as $m) {
    echo "  • {$m->inventory->product->name}: +{$m->quantity_change} units at " . $m->movement_date->format('H:i:s') . "\n";
}

// Check for all movements today (any type)
echo "\n\nALL Movements Today (any type):\n";
$allMovements = StockMovement::whereBetween('movement_date', [$today, $todayEnd])
    ->with('inventory.product')
    ->orderBy('movement_date', 'desc')
    ->get();

echo "Count: " . $allMovements->count() . "\n";
foreach ($allMovements as $m) {
    echo "  • {$m->inventory->product->name} [{$m->type}]: " . ($m->quantity_change > 0 ? '+' : '') . "{$m->quantity_change} units\n";
}

// Check LATEST movements (regardless of date)
echo "\n\nLATEST 10 Movements (any date, any type):\n";
$latest = StockMovement::with('inventory.product')
    ->orderBy('movement_date', 'desc')
    ->limit(10)
    ->get();

foreach ($latest as $m) {
    echo "  • {$m->inventory->product->name} [{$m->type}]: " . ($m->quantity_change > 0 ? '+' : '') . "{$m->quantity_change} units at " . ($m->movement_date ? $m->movement_date->format('Y-m-d H:i:s') : 'NULL') . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "If you see movements above, the query is working correctly.\n";
echo "Make sure to select 'Stock In (Restock/Refill)' when adding stock!\n";
