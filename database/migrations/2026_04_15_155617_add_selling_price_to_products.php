<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add selling_price column if it doesn't exist
            if (!Schema::hasColumn('products', 'selling_price')) {
                $table->decimal('selling_price', 8, 2)->nullable()->after('cost_price');
            }
        });

        // Copy existing price values to selling_price
        \DB::table('products')->whereNull('selling_price')->update([
            'selling_price' => \DB::raw('price')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'selling_price')) {
                $table->dropColumn('selling_price');
            }
        });
    }
};
