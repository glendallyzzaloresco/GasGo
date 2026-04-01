<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

// Get all columns in the inventory table
$columns = DB::getSchemaBuilder()->getColumnListing('inventory');

echo "Inventory Table Columns:\n";
foreach ($columns as $column) {
    echo "- " . $column . "\n";
}
