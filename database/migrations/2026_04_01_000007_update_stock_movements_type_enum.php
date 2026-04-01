<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include new stock_in and stock_out values
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stock_movements MODIFY type ENUM('purchase', 'sale', 'stock_in', 'stock_out', 'damage', 'return', 'adjustment') DEFAULT 'adjustment'");
        } elseif (DB::getDriverName() === 'pgsql') {
            // For PostgreSQL
            DB::statement("ALTER TABLE stock_movements DROP CONSTRAINT stock_movements_type_check");
            DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_type_check CHECK (type IN ('purchase', 'sale', 'stock_in', 'stock_out', 'damage', 'return', 'adjustment'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stock_movements MODIFY type ENUM('purchase', 'sale', 'adjustment', 'damage', 'return') DEFAULT 'adjustment'");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE stock_movements DROP CONSTRAINT stock_movements_type_check");
            DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_type_check CHECK (type IN ('purchase', 'sale', 'adjustment', 'damage', 'return'))");
        }
    }
};
