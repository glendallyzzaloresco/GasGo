<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `transaction_type` ENUM('exchange', 'new_cylinder', 'refill', 'not_tank') NOT NULL DEFAULT 'exchange'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `transaction_type` ENUM('exchange', 'new_cylinder', 'refill') NOT NULL DEFAULT 'exchange'");
        }
    }
};
