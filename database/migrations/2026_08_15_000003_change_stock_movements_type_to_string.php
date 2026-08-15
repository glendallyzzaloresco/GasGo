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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stock_movements MODIFY type VARCHAR(50) NOT NULL DEFAULT 'adjustment'");
        } else {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('type', 50)->default('adjustment')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
