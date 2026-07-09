<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM orders LIKE 'transaction_type'");
print_r($rows);
$values = Illuminate\Support\Facades\DB::select('SELECT DISTINCT transaction_type FROM orders');
print_r($values);
