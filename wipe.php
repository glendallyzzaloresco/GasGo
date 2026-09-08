<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

try {
    echo "=== STARTING DATABASE WIPE ===\n";
    
    // Disable foreign keys for clean drop
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    $tables = DB::select('SHOW TABLES');
    foreach ($tables as $row) {
        $table = array_values((array)$row)[0];
        echo "Dropping table: $table\n";
        DB::statement("DROP TABLE IF EXISTS `$table`");
    }
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "=== ALL TABLES DROPPED CLEANLY ===\n";

    echo "Running migrations and seeder...\n";
    Artisan::call('migrate', ['--force' => true, '--seed' => true]);
    echo Artisan::output();
    
    echo "=== WIPE AND RESEED SUCCESSFUL ===\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    // Ensure we don't block container if run with try
    exit(1);
}
