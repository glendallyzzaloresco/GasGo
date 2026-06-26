<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            if (Schema::hasColumn('inventory', 'reorder_level')) {
                $table->dropColumn('reorder_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory', 'reorder_level')) {
                $table->integer('reorder_level')->default(5)->after('quantity_on_hand');
            }
        });
    }
};
