<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Add returned_at column
            $table->timestamp('returned_at')->nullable()->after('delivered_at');
        });

        // Update the enum to include 'returning_to_store'
        // Note: For MySQL, we need to modify the column definition
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE deliveries MODIFY COLUMN status ENUM('assigned', 'picked_up', 'out_for_delivery', 'delivered', 'returning_to_store', 'failed') DEFAULT 'assigned'");
        }
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('returned_at');
        });

        // Revert the enum
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE deliveries MODIFY COLUMN status ENUM('assigned', 'picked_up', 'out_for_delivery', 'delivered', 'failed') DEFAULT 'assigned'");
        }
    }
};
