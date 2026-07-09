<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY payment_method VARCHAR(100) NOT NULL DEFAULT 'cash'");
            DB::statement("ALTER TABLE payments MODIFY payment_method VARCHAR(100) NOT NULL DEFAULT 'cash'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cash','gcash') NOT NULL DEFAULT 'cash'");
            DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','gcash') NOT NULL DEFAULT 'cash'");
        }
    }
};